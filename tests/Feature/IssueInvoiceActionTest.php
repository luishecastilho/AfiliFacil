<?php

namespace Tests\Feature;

use App\Actions\Invoice\IssueInvoiceAction;
use App\DTOs\InvoicePayloadDTO;
use App\Enums\InvoiceStatus;
use App\InvoiceProvider\Contracts\InvoiceProviderInterface;
use App\Models\Import;
use App\Models\Invoice;
use App\Models\Issuer;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesTestCertificate;
use Tests\TestCase;

class IssueInvoiceActionTest extends TestCase
{
    use CreatesTestCertificate, RefreshDatabase;

    private function bindFakeProvider(): void
    {
        $this->app->bind(InvoiceProviderInterface::class, fn () => new class implements InvoiceProviderInterface
        {
            public function slug(): string
            {
                return 'fake';
            }

            public function issue(InvoicePayloadDTO $payload): array
            {
                return [
                    'invoice_number' => '777',
                    'access_key' => str_repeat('9', 50),
                    'reference' => 'DPS-ref',
                    'raw' => ['ok' => true],
                ];
            }

            public function baixarDanfse(string $accessKey, int $issuerId): ?string
            {
                return null;
            }
        });
    }

    private function makeInvoice(User $user): Invoice
    {
        $import = Import::factory()->for($user)->create();
        $seller = Seller::factory()->for($user)->create();

        return Invoice::factory()->for($import)->for($seller)->create(['amount' => 1000]);
    }

    public function test_issue_persists_fields_and_allocates_dps_number(): void
    {
        Storage::fake('s3');
        $this->bindFakeProvider();

        $user = User::factory()->create();
        $issuer = $this->attachCertificate(Issuer::factory()->for($user)->create());

        $invoice = $this->makeInvoice($user);
        app(IssueInvoiceAction::class)->handle($invoice);

        $invoice->refresh();
        $this->assertSame(InvoiceStatus::Generated, $invoice->status);
        $this->assertSame('fake', $invoice->provider);
        $this->assertSame(str_repeat('9', 50), $invoice->access_key);
        $this->assertSame(1, (int) $invoice->dps_numero);
        $this->assertSame('00001', $invoice->dps_serie);
        $this->assertSame('10.05', $invoice->service_code);
        $this->assertSame('25.00', (string) $invoice->iss_amount); // 1000 * 2.5%
        $this->assertSame('producao_restrita', $invoice->ambiente);
        $this->assertSame($issuer->id, $invoice->issuer_id);

        $this->assertSame(2, (int) $issuer->refresh()->dps_proximo_numero);
    }

    public function test_dps_numbers_increment_across_invoices(): void
    {
        Storage::fake('s3');
        $this->bindFakeProvider();

        $user = User::factory()->create();
        $issuer = $this->attachCertificate(Issuer::factory()->for($user)->create());

        $first = $this->makeInvoice($user);
        $second = $this->makeInvoice($user);

        app(IssueInvoiceAction::class)->handle($first);
        app(IssueInvoiceAction::class)->handle($second);

        $this->assertSame(1, (int) $first->refresh()->dps_numero);
        $this->assertSame(2, (int) $second->refresh()->dps_numero);
        $this->assertSame(3, (int) $issuer->refresh()->dps_proximo_numero);
    }

    public function test_missing_certificate_fails_without_calling_provider(): void
    {
        $this->bindFakeProvider();

        $user = User::factory()->create();
        Issuer::factory()->for($user)->create(); // automated mode, no certificate

        $invoice = $this->makeInvoice($user);
        app(IssueInvoiceAction::class)->handle($invoice);

        $this->assertSame(InvoiceStatus::Failed, $invoice->refresh()->status);
    }
}
