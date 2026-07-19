<?php

namespace App\InvoiceProvider\Providers;

use App\DTOs\InvoicePayloadDTO;
use App\InvoiceProvider\Contracts\InvoiceProviderInterface;
use Illuminate\Support\Str;

/**
 * Placeholder implementation, not wired to any real NF-e provider.
 * Returns deterministic fake data so the invoice pipeline can be exercised
 * end-to-end in dev/test before a real provider (PlugNotas, Focus NF-e, ...) is integrated.
 */
class NullInvoiceProvider implements InvoiceProviderInterface
{
    public function slug(): string
    {
        return 'null';
    }

    public function issue(InvoicePayloadDTO $payload): array
    {
        return [
            'invoice_number' => (string) random_int(100000, 999999),
            'access_key' => Str::padLeft((string) $payload->invoiceId, 44, '0'),
            'reference' => (string) Str::uuid(),
            'raw' => [
                'status' => 'generated',
                'seller_document' => $payload->seller->taxDocument,
                'amount' => $payload->amount,
            ],
        ];
    }
}
