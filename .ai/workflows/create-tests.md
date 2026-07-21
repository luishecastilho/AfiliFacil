# Workflow: Create Tests

Step-by-step guide for writing and running tests in AfiliFacil.

---

## Test Infrastructure

| Setting | Value | File |
|---------|-------|------|
| Framework | PHPUnit 11 | `phpunit.xml` |
| Database | SQLite in-memory | `DB_DATABASE=:memory:` in phpunit.xml |
| Queue | Sync (inline) | `QUEUE_CONNECTION=sync` |
| Mail | Array (no send) | `MAIL_MAILER=array` |
| Suites | Unit, Feature | `tests/Unit/`, `tests/Feature/` |

Run all tests:

```bash
composer test
# or: php artisan test
```

---

## Step 1: Identify What to Test

Before writing, determine the test layer:

| Layer | When | Location |
|-------|------|----------|
| **Unit** | Pure logic (actions, services, DTOs) with mocked dependencies | `tests/Unit/` |
| **Feature** | HTTP endpoints, job dispatch, database interactions | `tests/Feature/` |

**Priority areas lacking tests** (see `.ai/backlog.md`):
- Import upload → parse → validate pipeline
- Invoice generation and subscription limits
- Seller upsert logic
- Duplicate detection

---

## Step 2: Create the Test File

### Feature Test Template

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }
}
```

### Unit Test Template

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;

class ExampleUnitTest extends TestCase
{
    public function test_something(): void
    {
        $this->assertTrue(true);
    }
}
```

**Naming convention:** `{Subject}Test.php` in the appropriate directory.

---

## Step 3: Use Factories

Check existing factories in `database/factories/`. Create new ones as needed:

```php
// database/factories/ImportFactory.php
namespace Database\Factories;

use App\Models\Import;
use App\Models\User;
use App\Models\Marketplace;
use App\Enums\ImportStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImportFactory extends Factory
{
    protected $model = Import::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'marketplace_id' => Marketplace::factory(),
            'original_filename' => 'report.csv',
            'storage_path' => 'imports/test/report.csv',
            'disk' => 'local',
            'file_hash' => hash('sha256', 'test'),
            'file_size' => 1024,
            'status' => ImportStatus::Pending,
            'imported_at' => now(),
        ];
    }
}
```

Ensure the model uses `HasFactory` trait and reference the factory.

---

## Step 4: Test Patterns for This Codebase

### Testing an Action (Unit)

```php
use App\Actions\Import\ValidateImportRowAction;
use App\Models\ImportRow;
use App\Enums\ImportRowStatus;

public function test_validates_row_with_missing_seller_name(): void
{
    $row = ImportRow::factory()->create(['seller_name' => '']);

    $action = app(ValidateImportRowAction::class);
    $status = $action->handle($row);

    $this->assertEquals(ImportRowStatus::Invalid, $status);
    $this->assertNotNull($row->fresh()->validation_errors);
}
```

### Testing a Controller (Feature)

```php
use App\Models\User;
use App\Models\Marketplace;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ParseImportJob;

public function test_user_can_upload_import(): void
{
    Queue::fake();

    $user = User::factory()->create();
    $marketplace = Marketplace::factory()->create();
    $file = UploadedFile::fake()->create('report.csv', 100, 'text/csv');

    $response = $this->actingAs($user)->post('/imports', [
        'marketplace_id' => $marketplace->id,
        'file' => $file,
    ]);

    $response->assertRedirect();
    Queue::assertPushed(ParseImportJob::class);
}
```

### Testing Subscription Limits (Feature)

```php
use App\Services\SubscriptionService;

public function test_free_plan_blocks_invoice_after_limit(): void
{
    $user = User::factory()->create(['plan' => 'free', 'nf_usage_this_month' => 5]);
    $service = app(SubscriptionService::class);

    $this->assertFalse($service->canIssueInvoice($user));
}
```

### Testing Job Dispatch (Feature)

```php
use Illuminate\Support\Facades\Queue;
use App\Jobs\GenerateInvoicesJob;

public function test_generate_invoices_dispatches_job(): void
{
    Queue::fake();

    $user = User::factory()->create();
    $import = Import::factory()->for($user)->create(['status' => ImportStatus::Validated]);

    $this->actingAs($user)->post("/imports/{$import->id}/invoices/generate");

    Queue::assertPushed(GenerateInvoicesJob::class);
}
```

### Testing Policies (Unit)

```php
use App\Policies\ImportPolicy;
use App\Models\User;
use App\Models\Import;

public function test_user_cannot_view_another_users_import(): void
{
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $import = Import::factory()->for($owner)->create();

    $policy = new ImportPolicy();

    $this->assertTrue($policy->view($owner, $import));
    $this->assertFalse($policy->view($other, $import));
}
```

---

## Step 5: Mocking External Services

### Storage (S3)

```php
use Illuminate\Support\Facades\Storage;

Storage::fake('s3');

// In test
Storage::disk('s3')->put('test.txt', 'content');
Storage::disk('s3')->assertExists('test.txt');
```

### Invoice Provider

```php
use App\InvoiceProvider\Contracts\InvoiceProviderInterface;

$this->mock(InvoiceProviderInterface::class, function ($mock) {
    $mock->shouldReceive('slug')->andReturn('test');
    $mock->shouldReceive('issue')->andReturn([
        'invoice_number' => '123456',
        'access_key' => str_repeat('0', 44),
        'reference' => 'ref-001',
        'raw' => ['status' => 'generated'],
    ]);
});
```

### Queue

```php
use Illuminate\Support\Facades\Queue;

Queue::fake();
// ... perform action ...
Queue::assertPushed(SomeJob::class);
Queue::assertPushed(SomeJob::class, fn ($job) => $job->import->id === $import->id);
```

### Mail / Notifications

```php
use Illuminate\Support\Facades\Notification;
use App\Notifications\ImportCompletedNotification;

Notification::fake();
// ... trigger notification ...
Notification::assertSentTo($user, ImportCompletedNotification::class);
```

---

## Step 6: Run and Verify

```bash
# Run all tests
composer test

# Run specific file
php artisan test tests/Feature/ImportUploadTest.php

# Run specific method
php artisan test --filter=test_user_can_upload_import

# Run with verbose output
php artisan test -v

# Stop on first failure
php artisan test --stop-on-failure
```

---

## Step 7: Seed Data in Tests

For tests needing marketplace data:

```php
use Database\Seeders\MarketplaceSeeder;

$this->seed(MarketplaceSeeder::class);
```

Or create inline:

```php
$marketplace = Marketplace::factory()->create([
    'slug' => 'shopee',
    'importer_class' => ShopeeImporter::class,
]);
```

---

## Checklist Before PR

- [ ] Test file in correct directory (`tests/Unit/` or `tests/Feature/`)
- [ ] Uses `RefreshDatabase` trait for tests touching DB
- [ ] Factories used instead of manual model creation where possible
- [ ] External services mocked (Storage, Queue, Provider, Mail)
- [ ] Tests pass: `composer test`
- [ ] No hardcoded IDs — use factories and relationships
- [ ] Assertions check both status codes and database state
- [ ] Edge cases covered (empty input, limit exceeded, unauthorized access)

---

## Frontend Tests

**Assumption:** No JavaScript test framework is configured. `package.json` has no test script.

If adding frontend tests in the future:
- Consider Vitest + React Testing Library
- Add `"test": "vitest"` to `package.json`
- Place tests alongside components: `ComponentName.test.jsx`

For now, frontend changes are verified manually via `npm run dev` and browser testing.
