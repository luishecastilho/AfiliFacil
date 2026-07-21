# Workflow: Implement a Task

Step-by-step guide for implementing any task in AfiliFacil.

---

## Step 1: Understand the Task

1. Read the task description in `.ai/backlog.md` or the issue/ticket
2. Check `.ai/decisions.md` for relevant architectural constraints
3. Read `.ai/architecture.md` for data flow and file locations
4. If the task relates to the original design, consult `ARCHITECTURE.md`

Identify which domain layer the task touches:

| Domain | Backend | Frontend | Jobs |
|--------|---------|----------|------|
| Import | `Actions/Import/`, `Jobs/Parse*`, `Jobs/Validate*` | `Pages/Imports/` | ParseImportJob, ParseChunkJob, ValidateImportJob |
| Invoice | `Actions/Invoice/`, `Jobs/Generate*`, `Jobs/Issue*` | `Pages/Invoices/` | GenerateInvoicesJob, IssueInvoiceJob, GenerateZipJob |
| Seller | `Actions/Seller/` | `Pages/Sellers/` | — |
| Billing | `Services/SubscriptionService`, `BillingController` | `Pages/Billing/` | — |
| Marketplace | `Marketplace/Importers/` | — | — |
| Provider | `InvoiceProvider/Providers/` | — | IssueInvoiceJob |

---

## Step 2: Set Up Local Environment

```bash
# Ensure dependencies are installed
composer install
npm install

# Configure environment (if not done)
cp .env.example .env
php artisan key:generate

# Run migrations and seed
php artisan migrate
php artisan db:seed --class=MarketplaceSeeder

# Start dev environment
composer dev
```

Verify the app loads at `http://localhost:8000`.

---

## Step 3: Plan the Changes

Before writing code, list the files you will create or modify:

### Backend Checklist

- [ ] **Action** — business logic in `app/Actions/{Domain}/`
- [ ] **Job** — async work in `app/Jobs/` (with `$tries`, `$backoff`, `$queue`)
- [ ] **Controller** — thin HTTP adapter in `app/Http/Controllers/`
- [ ] **Form Request** — validation in `app/Http/Requests/`
- [ ] **Model** — Eloquent model + migration if new entity
- [ ] **Enum** — status enum if new states needed
- [ ] **DTO** — value object if new data shape
- [ ] **Policy** — authorization in `app/Policies/`
- [ ] **Event/Listener** — if side effects needed
- [ ] **Observer** — if model lifecycle hooks needed
- [ ] **Route** — in `routes/web.php`
- [ ] **Config** — if new env vars in `config/afilifacil.php`
- [ ] **Seeder/Factory** — if new model needs test data

### Frontend Checklist

- [ ] **Page** — Inertia page in `resources/js/Pages/{Domain}/`
- [ ] **Component** — reusable UI in `resources/js/Components/`
- [ ] **Hook** — custom hook in `resources/js/hooks/` (if polling/state needed)
- [ ] **Constants** — status labels in `resources/js/constants/statuses.js`
- [ ] **Formatters** — display helpers in `resources/js/lib/formatters.js`

---

## Step 4: Implement Backend

Follow project conventions:

### 4a. Create Migration (if needed)

```bash
php artisan make:migration create_example_table
```

Place in `database/migrations/`. Follow existing naming: `2026_MM_DD_HHMMSS_description.php`.

### 4b. Create Model

```php
// app/Models/Example.php
namespace App\Models;

use App\Models\Scopes\BelongsToUserScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([BelongsToUserScope::class])]  // if user-owned
class Example extends Model
{
    protected $fillable = [/* ... */];

    protected function casts(): array
    {
        return [/* enum casts */];
    }
}
```

### 4c. Create Action

```php
// app/Actions/Domain/DoSomethingAction.php
namespace App\Actions\Domain;

class DoSomethingAction
{
    public function __construct(private readonly SomeService $service) {}

    public function handle(/* params */): ReturnType
    {
        // Business logic here — no HTTP concerns
    }
}
```

### 4d. Create Controller Method

```php
// Keep controllers thin — delegate to actions
public function store(StoreExampleRequest $request, DoSomethingAction $action): RedirectResponse
{
    $result = $action->handle($request->validated());

    return redirect()->route('examples.show', $result);
}
```

### 4e. Create Job (if async)

```php
// app/Jobs/ProcessExampleJob.php
class ProcessExampleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public string $queue = 'default';

    public function __construct(public readonly Example $example) {}

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(DoSomethingAction $action): void
    {
        $action->handle($this->example);
    }
}
```

### 4f. Register Route

```php
// routes/web.php — inside auth + verified middleware group
Route::resource('examples', ExampleController::class);
```

---

## Step 5: Implement Frontend

### 5a. Create Inertia Page

```jsx
// resources/js/Pages/Examples/Index.jsx
import AppLayout from '@/Layouts/AppLayout';
import { Head } from '@inertiajs/react';

export default function Index({ examples }) {
    return (
        <AppLayout header={<h2 className="text-xl font-semibold">Examples</h2>}>
            <Head title="Examples" />
            {/* Page content using @/Components/ui/* */}
        </AppLayout>
    );
}
```

### 5b. Use Existing Patterns

- **Tables:** `@/Components/ui/Table` + `@/Components/Pagination`
- **Forms:** `useForm()` from `@inertiajs/react` + `@/Components/ui/Button`
- **Status:** `@/Components/StatusBadge` + `@/constants/statuses`
- **Formatting:** `@/lib/formatters` (pt-BR locale)
- **File upload:** `@/Components/FileUploadZone`
- **Polling:** `@/hooks/useImportPoller` or `@/hooks/useInvoicePoller`

### 5c. Add Navigation (if new section)

Update `NAV_ITEMS` in `resources/js/Layouts/AppLayout.jsx`.

---

## Step 6: Write Tests

Follow `.ai/workflows/create-tests.md`:

```bash
# Create test file
# tests/Feature/ExampleTest.php

# Run tests
composer test
```

Minimum coverage for any task:
- Feature test for the HTTP endpoint
- Unit test for the action logic
- Policy test if authorization is involved

---

## Step 7: Manual Verification

```bash
# Ensure dev environment is running
composer dev

# Test the happy path in browser:
# 1. Navigate to the new/changed page
# 2. Perform the primary action
# 3. Verify database state (php artisan tinker)
# 4. Check queue processed (Horizon at /horizon)
# 5. Verify notifications/emails (mail log or Mailtrap)
```

### Common Verification Commands

```bash
# Check database
php artisan tinker
>>> App\Models\Import::latest()->first()

# Check failed jobs
php artisan queue:failed

# Check routes
php artisan route:list --name=examples

# Format code
./vendor/bin/pint
```

---

## Step 8: Update Documentation

If the task changes architecture, adds config, or introduces new patterns:

- [ ] Update `.ai/backlog.md` — mark task complete, add new items if discovered
- [ ] Update `.ai/decisions.md` — if a new architectural decision was made
- [ ] Update `CLAUDE.md` — if commands, routes, or priorities changed
- [ ] Update `.ai/architecture.md` — if data flow or schema changed

---

## Step 9: Pre-PR Checklist

- [ ] `./vendor/bin/pint` passes (no formatting issues)
- [ ] `composer test` passes
- [ ] `npm run build` succeeds (no frontend errors)
- [ ] No debug statements (`dd()`, `console.log()`) left in code
- [ ] New env vars documented in `.env.example` and `.ai/OPERATIONS.md`
- [ ] Policies cover new endpoints
- [ ] Jobs have retry/backoff configured
- [ ] Frontend uses existing UI components (not raw HTML)

---

## Example: Adding a New Marketplace Importer

Concrete walkthrough for a common extensibility task:

1. **Create importer class:**
   `app/Marketplace/Importers/MercadoLivreImporter.php` implementing `MarketplaceImporterInterface`

2. **Implement three methods:**
   - `chunkSize()` — return config value
   - `readChunks($path)` — yield arrays of raw rows
   - `mapToCommissionRow($raw, $marketplace)` — use `ColumnMapper` with marketplace config

3. **Seed marketplace:**
   Add entry in `database/seeders/MarketplaceSeeder.php` with `importer_class` and `column_map`

4. **Test:**
   - Unit test for `mapToCommissionRow()` with sample raw data
   - Feature test uploading a file for the new marketplace

5. **No controller/job changes needed** — the pipeline resolves the importer dynamically via `marketplace.importer_class`.

---

## Example: Adding a Real Invoice Provider

1. **Create provider:**
   `app/InvoiceProvider/Providers/PlugNotasProvider.php` implementing `InvoiceProviderInterface`

2. **Implement:**
   - `slug()` — return `'plugnotas'`
   - `issue($payload)` — call provider API, return `['invoice_number', 'access_key', 'reference', 'raw']`
   - Include `pdf_url` and `xml_url` in `raw` for `UploadInvoiceFilesJob`

3. **Bind in AppServiceProvider:**
   ```php
   $this->app->bind(InvoiceProviderInterface::class, PlugNotasProvider::class);
   ```

4. **Add env vars:**
   `PLUGNOTAS_API_KEY=` in `.env.example`

5. **Test:**
   - Mock provider in feature test
   - Verify `IssueInvoiceAction` persists provider response
   - Verify `UploadInvoiceFilesJob` stores files on S3
