<?php

namespace App\Actions\Issuer;

use App\Exceptions\PortalValidationException;
use App\Models\Issuer;
use App\Nfse\Client\SefinNacionalClient;

/**
 * Proves fiscal readiness by making a real mTLS call to the national portal
 * (consult municipal parameters). Success confirms: the certificate works over
 * mTLS + the município is adherent. Stamps portal_validated_at (the readiness seal).
 */
class ValidateWithPortalAction
{
    public function __construct(private readonly SefinNacionalClient $client) {}

    /**
     * @throws PortalValidationException
     */
    public function handle(Issuer $issuer): void
    {
        if (! $issuer->hasValidCertificate()) {
            throw PortalValidationException::certificateRequired();
        }

        $response = $this->client->consultarParametros($issuer);

        if (! $response->successful()) {
            throw PortalValidationException::fromStatus($response->status());
        }

        $issuer->forceFill(['portal_validated_at' => now()])->save();
    }
}
