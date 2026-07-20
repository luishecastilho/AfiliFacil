<?php

namespace App\Nfse\Dps;

use App\DTOs\InvoicePayloadDTO;
use App\Models\Issuer;
use DOMDocument;
use DOMElement;

/**
 * Builds the DPS (Declaração de Prestação de Serviços) XML for the Padrão Nacional NFS-e.
 *
 * NOTE: the field structure follows the public documentation (Anexo I, leiaute v1.01,
 * see .ai/nfse/pesquisa.md §2/§5). Before production it MUST be validated against the
 * official XSD — drop the schemas into app/Nfse/schemas/ and enable validateAgainstSchema().
 */
class DpsBuilder
{
    private const XMLNS = 'http://www.sped.fazenda.gov.br/nfse';

    private const VERSAO = '1.00';

    public function __construct(private readonly string $verAplic) {}

    public function build(Issuer $issuer, InvoicePayloadDTO $payload): DOMDocument
    {
        $ibge = $this->digits($issuer->address_ibge_code, 7);
        $id = $this->dpsId($issuer, $payload, $ibge);

        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = false;

        $dps = $doc->createElementNS(self::XMLNS, 'DPS');
        $dps->setAttribute('versao', self::VERSAO);
        $doc->appendChild($dps);

        $inf = $doc->createElement('infDPS');
        $inf->setAttribute('Id', $id);
        $dps->appendChild($inf);

        $this->append($inf, 'tpAmb', (string) $issuer->ambiente->tpAmb());
        $this->append($inf, 'dhEmi', now()->toIso8601String());
        $this->append($inf, 'verAplic', $this->verAplic);
        $this->append($inf, 'serie', $payload->dpsSerie);
        $this->append($inf, 'nDPS', (string) $payload->dpsNumero);
        $this->append($inf, 'dCompet', $payload->referenceMonth.'-01');
        $this->append($inf, 'tpEmit', '1'); // 1 = prestador
        $this->append($inf, 'cLocEmi', $ibge);

        $this->appendPrestador($doc, $inf, $issuer);
        $this->appendTomador($doc, $inf, $payload);
        $this->appendServico($doc, $inf, $issuer, $ibge);
        $this->appendValores($doc, $inf, $issuer, $payload);

        return $doc;
    }

    /**
     * Identificador da DPS: "DPS" + cMunEmi(7) + tpInscFed(1) + inscFed(14) + serie(5) + nDPS(15).
     */
    public function dpsId(Issuer $issuer, InvoicePayloadDTO $payload, ?string $ibge = null): string
    {
        $ibge ??= $this->digits($issuer->address_ibge_code, 7);
        $tpInsc = $issuer->document_type === 'cnpj' ? '2' : '1';

        return 'DPS'
            .$ibge
            .$tpInsc
            .$this->digits($issuer->tax_document, 14)
            .str_pad($payload->dpsSerie, 5, '0', STR_PAD_LEFT)
            .str_pad((string) $payload->dpsNumero, 15, '0', STR_PAD_LEFT);
    }

    private function appendPrestador(DOMDocument $doc, DOMElement $inf, Issuer $issuer): void
    {
        $prest = $doc->createElement('prest');

        if ($issuer->document_type === 'cnpj') {
            $this->append($prest, 'CNPJ', $this->digits($issuer->tax_document, 14));
        } else {
            $this->append($prest, 'CPF', $this->digits($issuer->tax_document, 11));
        }

        if ($issuer->inscricao_municipal) {
            $this->append($prest, 'IM', $issuer->inscricao_municipal);
        }

        $regTrib = $doc->createElement('regTrib');
        $this->append($regTrib, 'opSimpNac', $issuer->regime_tributario->isSimplesNacional() ? '2' : '1');
        $this->append($regTrib, 'regEspTrib', '0');
        $prest->appendChild($regTrib);

        $inf->appendChild($prest);
    }

    private function appendTomador(DOMDocument $doc, DOMElement $inf, InvoicePayloadDTO $payload): void
    {
        $seller = $payload->seller;
        $toma = $doc->createElement('toma');

        if ($seller->documentType === 'cnpj') {
            $this->append($toma, 'CNPJ', $this->digits($seller->taxDocument, 14));
        } else {
            $this->append($toma, 'CPF', $this->digits($seller->taxDocument, 11));
        }

        $this->append($toma, 'xNome', $seller->name);

        if ($seller->addressIbgeCode || $seller->addressStreet) {
            $end = $doc->createElement('end');
            $this->appendIf($end, 'xLgr', $seller->addressStreet);
            $this->appendIf($end, 'nro', $seller->addressNumber);
            $this->appendIf($end, 'xBairro', $seller->addressDistrict);
            if ($seller->addressIbgeCode) {
                $endNac = $doc->createElement('endNac');
                $this->append($endNac, 'cMun', $this->digits($seller->addressIbgeCode, 7));
                $this->appendIf($endNac, 'CEP', $seller->addressZip ? $this->digits($seller->addressZip, 8) : null);
                $end->appendChild($endNac);
            }
            $toma->appendChild($end);
        }

        $inf->appendChild($toma);
    }

    private function appendServico(DOMDocument $doc, DOMElement $inf, Issuer $issuer, string $ibge): void
    {
        $serv = $doc->createElement('serv');

        $locPrest = $doc->createElement('locPrest');
        $this->append($locPrest, 'cLocPrestacao', $ibge);
        $serv->appendChild($locPrest);

        $cServ = $doc->createElement('cServ');
        $this->append($cServ, 'cTribNac', (string) $issuer->service_code);
        if ($issuer->municipal_service_code) {
            $this->append($cServ, 'cTribMun', $issuer->municipal_service_code);
        }
        $this->append($cServ, 'xDescServ', 'Serviço de intermediação/agenciamento (comissão de afiliado)');
        $serv->appendChild($cServ);

        $inf->appendChild($serv);
    }

    private function appendValores(DOMDocument $doc, DOMElement $inf, Issuer $issuer, InvoicePayloadDTO $payload): void
    {
        $valores = $doc->createElement('valores');

        $vServPrest = $doc->createElement('vServPrest');
        $this->append($vServPrest, 'vServ', number_format($payload->amount, 2, '.', ''));
        $valores->appendChild($vServPrest);

        $trib = $doc->createElement('trib');
        $tribMun = $doc->createElement('tribMun');
        $this->append($tribMun, 'tribISSQN', '1'); // 1 = operação tributável
        if ($issuer->iss_rate !== null) {
            $this->append($tribMun, 'pAliq', number_format((float) $issuer->iss_rate, 4, '.', ''));
        }
        $this->append($tribMun, 'tpRetISSQN', $issuer->iss_withheld ? '1' : '2');
        $trib->appendChild($tribMun);
        $valores->appendChild($trib);

        $inf->appendChild($valores);
    }

    private function append(DOMElement $parent, string $name, string $value): void
    {
        $el = $parent->ownerDocument->createElement($name);
        $el->appendChild($parent->ownerDocument->createTextNode($value));
        $parent->appendChild($el);
    }

    private function appendIf(DOMElement $parent, string $name, ?string $value): void
    {
        if ($value !== null && $value !== '') {
            $this->append($parent, $name, $value);
        }
    }

    private function digits(?string $value, int $length): string
    {
        $digits = preg_replace('/\D/', '', (string) $value) ?? '';

        return str_pad($digits, $length, '0', STR_PAD_LEFT);
    }
}
