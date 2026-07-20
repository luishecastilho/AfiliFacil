<?php

namespace App\Nfse\Dps;

use App\Exceptions\CertificateException;
use App\Nfse\Certificate\CertificateMaterial;
use DOMDocument;
use DOMElement;

/**
 * XMLDSig enveloped signature over the <infDPS> element, using native openssl + DOM C14N
 * (no external dependency). Algorithm: exclusive/inclusive C14N + RSA-SHA256, per the
 * Padrão Nacional NFS-e (see .ai/nfse/pesquisa.md §7).
 */
class DpsSigner
{
    private const DSIG = 'http://www.w3.org/2000/09/xmldsig#';

    private const C14N = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';

    private const SIG_METHOD = 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256';

    private const DIGEST_METHOD = 'http://www.w3.org/2001/04/xmlenc#sha256';

    private const ENVELOPED = 'http://www.w3.org/2000/09/xmldsig#enveloped-signature';

    /**
     * Appends a <Signature> to the DPS root, signing the <infDPS> element.
     *
     * @throws CertificateException on an unusable private key
     */
    public function sign(DOMDocument $doc, CertificateMaterial $material): DOMDocument
    {
        $inf = $doc->getElementsByTagName('infDPS')->item(0);
        if (! $inf instanceof DOMElement) {
            throw new CertificateException('Elemento infDPS não encontrado para assinatura.');
        }

        $id = $inf->getAttribute('Id');

        // 1) Reference digest over the canonicalized infDPS.
        $digestValue = base64_encode(hash('sha256', $inf->C14N(), true));

        // 2) Build SignedInfo and the Signature skeleton, appended to the root.
        $signature = $doc->createElementNS(self::DSIG, 'Signature');
        $doc->documentElement->appendChild($signature);

        $signedInfo = $doc->createElementNS(self::DSIG, 'SignedInfo');
        $signature->appendChild($signedInfo);

        $signedInfo->appendChild($this->algorithm($doc, 'CanonicalizationMethod', self::C14N));
        $signedInfo->appendChild($this->algorithm($doc, 'SignatureMethod', self::SIG_METHOD));

        $reference = $doc->createElementNS(self::DSIG, 'Reference');
        $reference->setAttribute('URI', '#'.$id);
        $signedInfo->appendChild($reference);

        $transforms = $doc->createElementNS(self::DSIG, 'Transforms');
        $transforms->appendChild($this->algorithm($doc, 'Transform', self::ENVELOPED));
        $transforms->appendChild($this->algorithm($doc, 'Transform', self::C14N));
        $reference->appendChild($transforms);

        $reference->appendChild($this->algorithm($doc, 'DigestMethod', self::DIGEST_METHOD));
        $this->appendText($reference, 'DigestValue', $digestValue);

        // 3) Sign the canonicalized SignedInfo.
        $privateKey = openssl_pkey_get_private($material->privateKeyPem);
        if ($privateKey === false) {
            throw new CertificateException('Chave privada do certificado inválida.');
        }

        $signatureValue = '';
        openssl_sign($signedInfo->C14N(), $signatureValue, $privateKey, OPENSSL_ALGO_SHA256);

        $this->appendText($signature, 'SignatureValue', base64_encode($signatureValue));

        // 4) KeyInfo with the X.509 certificate (base64 DER = PEM body without headers).
        $keyInfo = $doc->createElementNS(self::DSIG, 'KeyInfo');
        $x509Data = $doc->createElementNS(self::DSIG, 'X509Data');
        $this->appendText($x509Data, 'X509Certificate', $this->certificateDer($material->certificatePem));
        $keyInfo->appendChild($x509Data);
        $signature->appendChild($keyInfo);

        return $doc;
    }

    private function algorithm(DOMDocument $doc, string $name, string $algorithm): DOMElement
    {
        $el = $doc->createElementNS(self::DSIG, $name);
        $el->setAttribute('Algorithm', $algorithm);

        return $el;
    }

    private function appendText(DOMElement $parent, string $name, string $value): void
    {
        $el = $parent->ownerDocument->createElementNS(self::DSIG, $name);
        $el->appendChild($parent->ownerDocument->createTextNode($value));
        $parent->appendChild($el);
    }

    private function certificateDer(string $pem): string
    {
        return preg_replace('/-----(BEGIN|END) CERTIFICATE-----|\s+/', '', $pem) ?? '';
    }
}
