<?php

namespace Tests\Feature;

use App\Models\Issuer;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesTestCertificate;
use Tests\TestCase;

class FiscalGateTest extends TestCase
{
    use CreatesTestCertificate, RefreshDatabase;

    public function test_checkout_is_blocked_without_fiscal_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('billing.checkout'), ['plan' => 'basic'])
            ->assertRedirect(route('issuer.edit'));
    }

    public function test_checkout_is_blocked_for_automated_without_portal_validation(): void
    {
        Storage::fake('s3');
        $user = User::factory()->create();
        // Fiscal data + certificate present, but portal not validated yet.
        $this->attachCertificate(Issuer::factory()->for($user)->create());

        $this->actingAs($user)
            ->post(route('billing.checkout'), ['plan' => 'basic'])
            ->assertRedirect(route('issuer.edit'));
    }

    public function test_automated_tier_is_ready_when_cert_and_portal_validated(): void
    {
        Storage::fake('s3');
        $user = User::factory()->create();
        $issuer = $this->attachCertificate(Issuer::factory()->for($user)->create());
        $issuer->forceFill(['portal_validated_at' => now()])->save();

        // Both the checkout guard and the fiscal.ready middleware gate on this result.
        $ready = app(SubscriptionService::class)->fiscalReady($user);

        $this->assertTrue($ready['complete']);
        $this->assertSame([], $ready['missing']);
        $this->assertSame('automated', $ready['mode']);
    }

    public function test_manual_tier_is_ready_when_govbr_linked(): void
    {
        $user = User::factory()->create();
        Issuer::factory()->for($user)->create([
            'emission_mode' => 'manual',
            'govbr_linked_at' => now(),
        ]);

        $ready = app(SubscriptionService::class)->fiscalReady($user);

        $this->assertTrue($ready['complete']);
        $this->assertSame('manual', $ready['mode']);
    }

    public function test_portal_validation_stamps_issuer_on_success(): void
    {
        Storage::fake('s3');
        config(['nf-facilitator.invoice.driver' => 'nacional']);
        $user = User::factory()->create();
        $issuer = $this->attachCertificate(Issuer::factory()->for($user)->create());

        Http::fake([
            'adn.producaorestrita.nfse.gov.br/*' => Http::response(['aliquota' => 2.5], 200),
        ]);

        $this->actingAs($user)
            ->post(route('issuer.validate'))
            ->assertRedirect(route('issuer.edit'));

        $this->assertNotNull($issuer->refresh()->portal_validated_at);
    }

    public function test_portal_validation_fails_for_non_adherent_municipality(): void
    {
        Storage::fake('s3');
        config(['nf-facilitator.invoice.driver' => 'nacional']);
        $user = User::factory()->create();
        $issuer = $this->attachCertificate(Issuer::factory()->for($user)->create());

        Http::fake([
            'adn.producaorestrita.nfse.gov.br/*' => Http::response([], 404),
        ]);

        $this->actingAs($user)
            ->post(route('issuer.validate'))
            ->assertSessionHasErrors('portal');

        $this->assertNull($issuer->refresh()->portal_validated_at);
    }
}
