<?php

namespace App\DTOs;

final readonly class SellerDTO
{
    public function __construct(
        public string $taxDocument,
        public string $documentType,
        public string $name,
        public ?string $tradeName = null,
        public ?string $email = null,
        public ?string $addressStreet = null,
        public ?string $addressNumber = null,
        public ?string $addressComplement = null,
        public ?string $addressDistrict = null,
        public ?string $addressCity = null,
        public ?string $addressState = null,
        public ?string $addressZip = null,
        public ?string $addressIbgeCode = null,
    ) {
    }
}
