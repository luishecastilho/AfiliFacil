<?php

namespace App\InvoiceProvider\Providers;

use App\DTOs\InvoicePayloadDTO;
use App\Exceptions\InvoiceProviderException;
use App\InvoiceProvider\Contracts\InvoiceProviderInterface;
use App\Models\Issuer;
use App\Nfse\Certificate\CertificateVault;
use App\Nfse\Client\SefinNacionalClient;
use App\Nfse\Dps\DpsBuilder;
use App\Nfse\Dps\DpsSigner;

/**
 * Padrão Nacional NFS-e provider: build DPS → sign (XMLDSig) → transmit (mTLS) → parse.
 */
class NacionalNfseProvider implements InvoiceProviderInterface
{
    public function __construct(
        private readonly DpsBuilder $builder,
        private readonly DpsSigner $signer,
        private readonly SefinNacionalClient $client,
        private readonly CertificateVault $vault,
    ) {}

    public function slug(): string
    {
        return 'nacional';
    }

    public function issue(InvoicePayloadDTO $payload): array
    {
        $issuer = $this->resolveIssuer($payload->issuerId);

        $doc = $this->builder->build($issuer, $payload);
        $material = $this->vault->readMaterial($issuer);
        $this->signer->sign($doc, $material);

        $signedXml = (string) $doc->saveXML();

        $response = $this->client->enviarDps($issuer, $signedXml);

        if ($response->status() === 429) {
            throw new InvoiceProviderException('Limite de requisições atingido no portal nacional (429).');
        }

        if (! $response->successful()) {
            throw new InvoiceProviderException($this->rejectionMessage($response->status(), $response->json()));
        }

        $json = (array) $response->json();
        $chave = $this->extract($json, ['chaveAcesso', 'chNFSe', 'chave']);

        if ($chave === null) {
            throw new InvoiceProviderException('Resposta do portal sem chave de acesso da NFS-e.');
        }

        return [
            'invoice_number' => (string) ($this->extract($json, ['nNFSe', 'numero']) ?? ''),
            'access_key' => $chave,
            'reference' => $this->builder->dpsId($issuer, $payload),
            'raw' => [
                'nfse_xml' => $this->decodeGzipB64($this->extract($json, ['nfseXmlGZipB64'])),
                'dps_xml' => $signedXml,
                'status' => $response->status(),
                'response' => $json,
            ],
        ];
    }

    public function baixarDanfse(string $accessKey, int $issuerId): ?string
    {
        $issuer = $this->resolveIssuer($issuerId);

        $response = $this->client->baixarDanfse($issuer, $accessKey);

        return $response->successful() ? $response->body() : null;
    }

    private function resolveIssuer(int $issuerId): Issuer
    {
        // No auth context inside queued jobs — bypass the tenant scope by primary key.
        $issuer = Issuer::withoutGlobalScopes()->find($issuerId);

        if (! $issuer) {
            throw new InvoiceProviderException("Emitente {$issuerId} não encontrado.");
        }

        return $issuer;
    }

    /**
     * @param  array<string, mixed>  $json
     * @param  list<string>  $keys
     */
    private function extract(array $json, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($json[$key]) && is_scalar($json[$key])) {
                return (string) $json[$key];
            }
        }

        return null;
    }

    private function decodeGzipB64(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $decoded = @gzdecode((string) base64_decode($value, true));

        return $decoded === false ? null : $decoded;
    }

    private function rejectionMessage(int $status, mixed $json): string
    {
        if (is_array($json)) {
            $erros = $json['erros'] ?? $json['mensagens'] ?? $json['message'] ?? null;
            if (is_array($erros)) {
                $parts = array_map(
                    fn ($e) => is_array($e) ? ($e['descricao'] ?? $e['mensagem'] ?? json_encode($e)) : (string) $e,
                    $erros,
                );

                return "NFS-e rejeitada ({$status}): ".implode('; ', $parts);
            }
            if (is_string($erros)) {
                return "NFS-e rejeitada ({$status}): {$erros}";
            }
        }

        return "NFS-e rejeitada pelo portal nacional (HTTP {$status}).";
    }
}
