# Architectural Decisions

Decisions made during design and implementation. Each entry references the implementing code.

---

## ADR-001: Invoice Grouping — Many Rows to One Invoice

**Decision:** One NF-e is issued per `(seller_id, reference_month)` combination, not one per import row.

**Rationale:** Brazilian fiscal practice groups all commissions from one CNPJ in a given month into a single NF-e. The original spec implied one-to-one; this was corrected in `ARCHITECTURE.md` §1.

**Implementation:**
- `GroupRowsForInvoicingAction` groups valid/duplicate rows by `"{$seller_id}:{$reference_month}"`
- Creates one `Invoice` per group with summed `amount`
- Attaches rows via `invoice_import_rows` pivot
- **Files:** `app/Actions/Invoice/GroupRowsForInvoicingAction.php`, `app/Models/InvoiceImportRow.php`

---

## ADR-002: Interface-Driven Marketplace Importers

**Decision:** Each marketplace has a class implementing `MarketplaceImporterInterface`. Column mapping is stored in `marketplace.config`.

**Rationale:** Different marketplaces (Shopee, Mercado Livre, …) have different report formats. An interface + config-driven column map avoids hardcoding.

**Implementation:**
- Interface: `chunkSize()`, `readChunks()`, `mapToCommissionRow()`
- Shopee: `ShopeeImporter` (CSV via `League\Csv`)
- Registration: `marketplace.importer_class` column, resolved per-import in `ParseImportJob`
- Default binding: `MarketplaceServiceProvider` → `ShopeeImporter`
- Column map seeded in `MarketplaceSeeder`
- **Files:** `app/Marketplace/Contracts/MarketplaceImporterInterface.php`, `app/Marketplace/Importers/ShopeeImporter.php`, `database/seeders/MarketplaceSeeder.php`

---

## ADR-003: Interface-Driven Invoice Providers

**Decision:** NF-e issuance goes through `InvoiceProviderInterface`. The app binds one provider at a time.

**Rationale:** Different NF-e providers (PlugNotas, Focus NF-e, NFe.io) have different APIs. Swapping providers should not require changing job/action code.

**Implementation:**
- Interface: `slug()`, `issue(InvoicePayloadDTO): array`
- Current: `NullInvoiceProvider` (returns fake data for dev/test)
- Binding: `AppServiceProvider` → `NullInvoiceProvider`
- **Assumption:** No real NF-e is issued until a production provider is integrated.
- **Files:** `app/InvoiceProvider/Contracts/InvoiceProviderInterface.php`, `app/InvoiceProvider/Providers/NullInvoiceProvider.php`, `app/Providers/AppServiceProvider.php`

---

## ADR-004: Action Classes for Business Logic

**Decision:** Controllers are thin; business logic lives in `app/Actions/` single-responsibility classes.

**Rationale:** Keeps controllers as HTTP adapters. Actions are testable in isolation and reusable from jobs/listeners.

**Implementation:**
- Import domain: `CreateImportAction`, `ParseImportChunkAction`, `ValidateImportRowAction`, `DetectDuplicateImportAction`
- Invoice domain: `GroupRowsForInvoicingAction`, `IssueInvoiceAction`, `RetryInvoiceAction`, `BuildInvoiceZipAction`
- Seller domain: `UpsertSellerAction`
- **Files:** `app/Actions/`

---

## ADR-005: Queue-Heavy Async Processing

**Decision:** Import parsing and invoice issuance run as queued jobs with batch support, retries, and rate limiting.

**Rationale:** Commission reports can have tens of thousands of rows. Synchronous processing would timeout.

**Implementation:**
- Queue hierarchy: `high` (parse/validate), `default` (invoice), `low` (zip/upload)
- `ParseImportJob` dispatches `Bus::batch()` of `ParseChunkJob`s
- `IssueInvoiceJob` uses `Redis::throttle('invoice-provider')->allow(10)->every(60)`
- All jobs declare `$tries`, `$backoff`, `$maxExceptions`
- Unique jobs via `ShouldBeUnique` on parse, validate, generate, zip
- **Files:** `app/Jobs/`

---

## ADR-006: Seller Entity (Not Embedded in Rows)

**Decision:** Sellers are a first-class model keyed on `(user_id, tax_document)`. Import rows reference sellers via FK.

**Rationale:** Same seller appears across multiple imports. Centralizing seller data prevents drift and enables enrichment/correction.

**Implementation:**
- `UpsertSellerAction` creates/updates seller on each parsed row
- `Seller` model has split address columns (street, number, city, state, zip, ibge_code)
- User-scoped via `BelongsToUserScope`
- **Files:** `app/Models/Seller.php`, `app/Actions/Seller/UpsertSellerAction.php`

---

## ADR-007: User Scoping via Global Scope

**Decision:** Business models use `BelongsToUserScope` to auto-filter by authenticated user.

**Rationale:** Multi-tenant ready from day one without retrofitting. Prevents cross-user data leaks.

**Implementation:**
- Applied to: `Import`, `Seller` (via `#[ScopedBy]`)
- Invoice access checked via policy (`$invoice->import->user_id`)
- **Files:** `app/Models/Scopes/BelongsToUserScope.php`

---

## ADR-008: DTO Normalization Layer

**Decision:** Raw marketplace rows are mapped to typed DTOs before persistence.

**Rationale:** Decouples marketplace column quirks from domain logic. DTOs are plain PHP value objects with no logic.

**Implementation:**
- `CommissionRowDTO` — normalized row from any marketplace
- `InvoicePayloadDTO` — data passed to invoice provider
- `SellerDTO` — seller data for invoice issuance
- `ImportSummaryDTO` — aggregate import stats
- **Files:** `app/DTOs/`

---

## ADR-009: Subscription Limits via Plan Config

**Decision:** NF-e issuance is limited by plan tier. Usage tracked on `users.nf_usage_this_month`.

**Rationale:** SaaS monetization. Limits enforced at job level, not UI level.

**Implementation:**
- Plans: `config/plans.php` (free: 5, basic: 50, advanced: unlimited)
- Check: `SubscriptionService::canIssueInvoice()` in `IssueInvoiceJob`
- Increment: `SubscriptionService::incrementUsage()` after successful generation
- Reset: scheduled task on 1st of month (`routes/console.php`)
- Stripe: Cashier checkout + customer portal
- **Files:** `app/Services/SubscriptionService.php`, `config/plans.php`, `app/Http/Controllers/BillingController.php`

---

## ADR-010: Bulk Insert for Import Rows

**Decision:** Parsed rows are inserted via `DB::table()->insertOrIgnore()` in batches of 500, not Eloquent model-per-row.

**Rationale:** Performance — importing thousands of rows via Eloquent observers would be slow and memory-heavy.

**Implementation:**
- `ParseImportChunkAction` accumulates records, flushes every 500
- Observers reserved for status transitions, not initial bulk insert
- **Files:** `app/Actions/Import/ParseImportChunkAction.php`

---

## ADR-011: Event-Driven Side Effects

**Decision:** Domain events fire from observers; listeners handle notifications and job dispatch.

**Rationale:** Decouples core logic from side effects (emails, audit, downstream jobs).

**Implementation:**
- `ImportObserver` → fires `ImportParsed`, `ImportFailed`
- `InvoiceObserver` → fires `InvoiceGenerated`, `InvoiceFailed`
- `ImportUploaded` → `DispatchParseJob` listener
- Listeners auto-discovered by Laravel (no `EventServiceProvider`)
- **Files:** `app/Observers/`, `app/Events/`, `app/Listeners/`

---

## ADR-012: Inertia.js + React (No TypeScript)

**Decision:** Frontend uses Inertia.js v2 with React 19 in plain JavaScript (no TypeScript).

**Rationale:** Laravel Breeze React scaffold. Keeps frontend simple; server renders initial props, client handles interactivity.

**Implementation:**
- Entry: `resources/js/app.jsx` with `@inertiajs/react` + TanStack Query
- Pages: `resources/js/Pages/{Domain}/{Action}.jsx`
- UI: shadcn/ui components in `resources/js/Components/ui/`
- Vite alias: `@` → `resources/js`
- **Files:** `resources/js/`, `vite.config.js`

---

## ADR-013: Audit Logging via Observer

**Decision:** All create/update/delete on audited models are logged via `AuditObserver`.

**Rationale:** Compliance and support visibility for a product handling fiscal documents.

**Implementation:**
- Applied to: `Import`, `Invoice`, `Seller` (via `#[ObservedBy]`)
- Logs: user_id, action, old/new values, IP, user agent
- **Files:** `app/Observers/AuditObserver.php`, `app/Services/AuditService.php`, `app/Models/AuditLog.php`

---

## ADR-014: Duplicate Detection (Two Levels)

**Decision:** Duplicates detected at file level (SHA-256 hash) and row level (seller_document + reference_month within import).

**Rationale:** Same file re-uploaded is blocked; same seller/month appearing twice in one import is flagged but first occurrence is kept valid.

**Implementation:**
- File: `DetectDuplicateImportAction` (hash per user)
- Row: `ValidateImportJob::flagIntraImportDuplicates()` (keeps first, marks rest as `duplicate`)
- Duplicate rows are included in invoice grouping (status `valid` or `duplicate`)
- **Files:** `app/Actions/Import/DetectDuplicateImportAction.php`, `app/Jobs/ValidateImportJob.php`

---

## ADR-015: S3 Storage with Presigned Downloads

**Decision:** All files (imports, invoice PDFs/XMLs, ZIPs) stored on S3. Downloads use presigned URLs.

**Rationale:** Scalable file storage; no files served through the app server.

**Implementation:**
- Upload: `StorageService::put('s3', $path, $contents)`
- Download: `Storage::disk($file->disk)->temporaryUrl($path, $expiresAt)`
- TTL: configurable via `config/nf-facilitator.php` (15 min default)
- ZIP TTL: 24 hours (`expires_at` on `invoice_files`)
- **Assumption:** Local dev may need `FILESYSTEM_DISK=local` if AWS credentials are unavailable.
- **Files:** `app/Services/StorageService.php`, `app/Http/Controllers/InvoiceDownloadController.php`

---

## ADR-016: PHP Enums for Status Fields

**Decision:** All status fields use backed PHP enums, not string constants.

**Rationale:** Type safety, IDE autocompletion, centralized labels via `label()` method.

**Implementation:**
- `ImportStatus`, `ImportRowStatus`, `InvoiceStatus`, `InvoiceFileType`, `InvoiceEventType`, `JobExecutionStatus`, `NotificationChannel`
- Cast on models via `protected function casts()`
- Frontend mirrors in `resources/js/constants/statuses.js`
- **Files:** `app/Enums/`
