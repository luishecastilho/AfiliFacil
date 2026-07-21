<?php

namespace App\Nfse\Client;

use App\Enums\AmbienteEmissao;
use App\Models\Issuer;
use App\Nfse\Certificate\CertificateVault;
use Closure;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * REST client for the Sistema Nacional NFS-e (Sefin Nacional / ADN), authenticated by
 * mTLS with the issuer's A1 certificate (see .ai/nfse/pesquisa.md §2/§8).
 */
class SefinNacionalClient
{
    public function __construct(private readonly CertificateVault $vault) {}

    /**
     * POST a signed DPS. The document travels GZip+Base64 inside the JSON envelope.
     */
    public function enviarDps(Issuer $issuer, string $signedXml): Response
    {
        $endpoints = $this->endpoints($issuer);

        return $this->send($issuer, fn (PendingRequest $r) => $r->post($endpoints['sefin'].'/nfse', [
            'dpsXmlGZipB64' => base64_encode((string) gzencode($signedXml, 9)),
        ]));
    }

    public function consultarNfse(Issuer $issuer, string $chaveAcesso): Response
    {
        $endpoints = $this->endpoints($issuer);

        return $this->send($issuer, fn (PendingRequest $r) => $r->get($endpoints['sefin'].'/nfse/'.$chaveAcesso));
    }

    public function baixarDanfse(Issuer $issuer, string $chaveAcesso): Response
    {
        $endpoints = $this->endpoints($issuer);

        return $this->send($issuer, fn (PendingRequest $r) => $r->get($endpoints['danfse'].'/'.$chaveAcesso));
    }

    /**
     * Consulta parâmetros municipais — usado como prova de conectividade/prontidão (selo).
     */
    public function consultarParametros(Issuer $issuer): Response
    {
        $endpoints = $this->endpoints($issuer);
        $ibge = preg_replace('/\D/', '', (string) $issuer->address_ibge_code);

        return $this->send($issuer, fn (PendingRequest $r) => $r->get(
            $endpoints['parametrizacao'].'/parametros_municipais/'.$ibge.'/'.now()->format('Y-m-d')
        ));
    }

    /**
     * @param  Closure(PendingRequest): Response  $callback
     */
    private function send(Issuer $issuer, Closure $callback): Response
    {
        return $this->vault->withTempPem($issuer, function (string $certFile, string $keyFile) use ($callback): Response {
            $request = Http::timeout((int) config('afilifacil.nfse.timeout', 30))
                ->withOptions(['cert' => $certFile, 'ssl_key' => $keyFile])
                ->acceptJson();

            return $callback($request);
        });
    }

    /**
     * @return array{sefin: string, adn: string, danfse: string, parametrizacao: string}
     */
    private function endpoints(Issuer $issuer): array
    {
        $env = $issuer->ambiente === AmbienteEmissao::Producao ? 'producao' : 'producao_restrita';

        return config('afilifacil.nfse.endpoints.'.$env);
    }
}
