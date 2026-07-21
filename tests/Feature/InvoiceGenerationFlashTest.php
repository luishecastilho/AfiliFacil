<?php

namespace Tests\Feature;

use App\Jobs\GenerateInvoicesJob;
use App\Models\Import;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class InvoiceGenerationFlashTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_user_over_quota_gets_upgrade_warning_flash(): void
    {
        Queue::fake();

        // free plan → limit 5; used 0 → remaining 5; import needs 40.
        $user = User::factory()->create(['plan' => 'free', 'nf_usage_this_month' => 0]);
        $import = Import::factory()->for($user)->validated()->create(['total_unique_tax_ids' => 40]);

        $this->actingAs($user)
            ->post(route('invoices.generate', $import))
            ->assertSessionHas('warning');

        Queue::assertPushed(GenerateInvoicesJob::class);
    }

    public function test_free_user_within_quota_gets_neutral_status_flash(): void
    {
        Queue::fake();

        // remaining 5, import needs 3 → no warning.
        $user = User::factory()->create(['plan' => 'free', 'nf_usage_this_month' => 0]);
        $import = Import::factory()->for($user)->validated()->create(['total_unique_tax_ids' => 3]);

        $this->actingAs($user)
            ->post(route('invoices.generate', $import))
            ->assertSessionMissing('warning')
            ->assertSessionHas('status');
    }

    public function test_unlimited_plan_never_gets_warning_flash(): void
    {
        Queue::fake();

        // advanced plan → nf_limit null (unlimited) → never warns, even for a large import.
        $user = User::factory()->create(['plan' => 'advanced', 'nf_usage_this_month' => 0]);
        $import = Import::factory()->for($user)->validated()->create(['total_unique_tax_ids' => 999]);

        $this->actingAs($user)
            ->post(route('invoices.generate', $import))
            ->assertSessionMissing('warning')
            ->assertSessionHas('status');
    }
}
