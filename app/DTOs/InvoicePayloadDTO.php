<?php

namespace App\DTOs;

/**
 * Normalized data passed to an InvoiceProviderInterface implementation to issue one NFS-e.
 *
 * The issuer (prestador) fiscal data and certificate are NOT carried here: the provider
 * loads the Issuer model by `issuerId` and pulls certificate material from the vault,
 * so no sensitive material ever travels on this DTO or a serialized job.
 */
final readonly class InvoicePayloadDTO
{
    public function __construct(
        public int $invoiceId,
        public string $referenceMonth,
        public float $amount,
        public SellerDTO $seller,
        public int $issuerId,
        public string $dpsSerie,
        public int $dpsNumero,
    ) {}
}
