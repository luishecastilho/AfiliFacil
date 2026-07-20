<?php

namespace App\Services;

use App\Enums\EmissionMode;
use App\Models\User;
use App\Support\TaxDocument;

class SubscriptionService
{
    /**
     * Fiscal readiness required to subscribe / emit (tier-aware).
     *
     * @return array{complete: bool, mode: ?string, missing: list<string>, portal_validated: bool, cert_expires_at: ?string}
     */
    public function fiscalReady(User $user): array
    {
        $issuer = $user->issuer;

        if (! $issuer) {
            return [
                'complete' => false,
                'mode' => null,
                'missing' => ['cadastro_fiscal'],
                'portal_validated' => false,
                'cert_expires_at' => null,
            ];
        }

        $missing = [];

        if (! $issuer->legal_name || ! $issuer->tax_document || ! TaxDocument::isValid($issuer->tax_document)) {
            $missing[] = 'dados_fiscais';
        }
        if (! $issuer->address_ibge_code) {
            $missing[] = 'endereco';
        }
        if (! $issuer->service_code) {
            $missing[] = 'codigo_servico';
        }

        if ($issuer->emission_mode === EmissionMode::Automated) {
            if (! $issuer->hasValidCertificate()) {
                $missing[] = 'certificado';
            }
            if (! $issuer->isPortalValidated()) {
                $missing[] = 'validacao_portal';
            }
        } elseif (! $issuer->isGovbrLinked()) {
            $missing[] = 'govbr';
        }

        return [
            'complete' => $missing === [],
            'mode' => $issuer->emission_mode->value,
            'missing' => $missing,
            'portal_validated' => $issuer->isPortalValidated(),
            'cert_expires_at' => $issuer->certificate_valid_until?->toIso8601String(),
        ];
    }

    public function currentPlan(User $user): string
    {
        return $user->plan ?? 'free';
    }

    public function nfLimit(User $user): ?int
    {
        return config('plans.'.$this->currentPlan($user).'.nf_limit');
    }

    public function nfUsedThisMonth(User $user): int
    {
        return $user->nf_usage_this_month;
    }

    public function canIssueInvoice(User $user): bool
    {
        $limit = $this->nfLimit($user);

        if ($limit === null) {
            return true;
        }

        return $this->nfUsedThisMonth($user) < $limit;
    }

    public function incrementUsage(User $user): void
    {
        $user->increment('nf_usage_this_month');
    }

    public function resetMonthlyUsage(): void
    {
        User::query()->update(['nf_usage_this_month' => 0]);
    }
}
