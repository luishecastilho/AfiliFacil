<?php

namespace App\InvoiceProvider\Contracts;

use App\DTOs\InvoicePayloadDTO;
use App\Exceptions\InvoiceProviderException;

interface InvoiceProviderInterface
{
    /**
     * Provider slug persisted on Invoice::provider (e.g. 'plugnotas', 'focusnfe', 'null').
     */
    public function slug(): string;

    /**
     * Issue an NF-e for the given payload. Returns the provider's raw response
     * payload, which the caller persists to Invoice::provider_payload and uses
     * to populate invoice_number / access_key / provider_reference.
     *
     * @return array{invoice_number: string, access_key: string, reference: string, raw: array}
     *
     * @throws InvoiceProviderException
     */
    public function issue(InvoicePayloadDTO $payload): array;

    /**
     * Fetch the DANFSE (PDF) bytes for an authorized document, or null if unavailable.
     */
    public function baixarDanfse(string $accessKey, int $issuerId): ?string;
}
