<?php

namespace App\Nfse\Certificate;

use Carbon\CarbonImmutable;

/**
 * Transient holder for decrypted certificate material. Never persisted, never
 * serialized onto a queued job. Lives only in memory during signing / mTLS.
 */
final class CertificateMaterial
{
    public function __construct(
        public readonly string $certificatePem,
        public readonly string $privateKeyPem,
        /** @var list<string> intermediate/root chain in PEM */
        public readonly array $chainPem,
        public readonly ?string $subjectCn,
        public readonly ?string $document,
        public readonly ?CarbonImmutable $validFrom,
        public readonly ?CarbonImmutable $validUntil,
    ) {}

    public function isExpired(): bool
    {
        return $this->validUntil === null || $this->validUntil->isPast();
    }
}
