# Backlog

Pending tasks derived from code TODOs, stub implementations, and `ARCHITECTURE.md` recommendations.

Priority: **P0** (blocking production) → **P1** (important) → **P2** (nice-to-have) → **P3** (future)

---

## P0 — Blocking Production

### Build In-House NFS-e Engine (Padrão Nacional)
- **Status:** `NullInvoiceProvider` is the only implementation; decision made to build our own engine (no PlugNotas/NFE.io/Focus)
- **Docs:** research in `.ai/nfse/pesquisa.md`; architecture + phased roadmap (F1–F7) in `.ai/nfse/arquitetura.md`
- **Action:** Follow the roadmap: F1 issuer profile + certificate vault + fiscal onboarding gate → F2 DPS XML + XMLDSig signing → F3 mTLS transmission to Sefin Nacional (produção restrita) → F4 XML/DANFSE storage → F5 cancel/substitute → F6 ADN sync + production go-live
- **Acceptance:** real NFS-e authorized in produção restrita from a Shopee import (F3), then production (F6)

### Implement Streaming ZIP Generation
- **Status:** Stub — creates DB record, no actual ZIP content
- **Files:** `app/Actions/Invoice/BuildInvoiceZipAction.php` (TODO comment)
- **Action:** Stream each `InvoiceFile` (pdf/xml) from S3 into `ZipStream\ZipStream` output, upload result to S3
- **Acceptance:** Download ZIP route returns a valid archive with all generated invoice files

### XLSX/XLS Import Support
- **Status:** `ShopeeImporter` handles CSV only
- **Files:** `app/Marketplace/Importers/ShopeeImporter.php` (TODO comment)
- **Action:** Add `maatwebsite/excel` `WithChunkReading` path alongside existing CSV reader
- **Acceptance:** Upload `.xlsx`/`.xls` files and parse rows identically to CSV

---

## P1 — Important

### CNPJ/CPF Modulo-11 Validation
- **Status:** Length check only (11 or 14 digits)
- **Files:** `app/Actions/Import/ValidateImportRowAction.php` (TODO comment)
- **Action:** Implement checksum validation in `isValidTaxDocument()`
- **Acceptance:** Invalid checksum documents marked as `invalid` with appropriate error message

### Domain Test Suite
- **Status:** Only Breeze auth tests exist (`tests/Feature/Auth/*`)
- **Files:** `tests/Feature/`, `tests/Unit/`
- **Action:** Add feature tests for import upload → parse → validate pipeline, invoice generation, subscription limits
- **Acceptance:** `composer test` covers core business flows with factories

### Invoice Failure Notifications
- **Status:** Empty handler with TODO
- **Files:** `app/Listeners/Invoice/NotifyUserOfInvoiceResult.php` (TODO comment)
- **Action:** Notify user once retries are exhausted, not on every attempt
- **Acceptance:** User receives email/database notification when invoice permanently fails

### Horizon Gate Configuration
- **Status:** Empty email allowlist
- **Files:** `app/Providers/HorizonServiceProvider.php`
- **Action:** Add admin emails to `viewHorizon` gate
- **Acceptance:** Authorized users can access `/horizon` in staging/production

### Wire FileValidationService
- **Status:** Service exists but not called in upload pipeline
- **Files:** `app/Services/FileValidationService.php`, `app/Actions/Import/CreateImportAction.php`
- **Action:** Validate MIME type from file content (finfo), not just extension
- **Acceptance:** Non-CSV/XLSX files rejected even with spoofed extension

---

## P2 — Nice-to-Have

### Sellers in Main Navigation
- **Status:** Routes exist (`/sellers`) but not in `AppLayout` NAV_ITEMS
- **Files:** `resources/js/Layouts/AppLayout.jsx`
- **Action:** Add Sellers link to navigation

### Dashboard Metrics Caching
- **Status:** `DashboardMetricsService` exists but `DashboardController` uses inline queries
- **Files:** `app/Services/DashboardMetricsService.php`, `app/Http/Controllers/DashboardController.php`
- **Action:** Use cached aggregates with Redis tagged invalidation

### Duplicate Import Override UI
- **Status:** `DuplicateWarning.jsx` page exists; backend throws `DuplicateImportException`
- **Files:** `resources/js/Pages/Imports/DuplicateWarning.jsx`, `app/Http/Requests/Import/OverrideDuplicateRequest.php`
- **Action:** Wire override flow so users can re-import flagged files

### Import Row Search/Filter UI
- **Status:** Backend supports search/filter in `ImportRowController`; frontend may not expose all filters
- **Files:** `app/Http/Controllers/ImportRowController.php`, `resources/js/Pages/Imports/Show.jsx`
- **Action:** Add status filter and search input to import row table

### CI/CD Pipeline
- **Status:** No GitHub Actions, Dockerfile, or Makefile
- **Action:** Add workflow for `composer test`, `./vendor/bin/pint --test`, `npm run build`

### Customize composer.json Metadata
- **Status:** Still shows `laravel/laravel` skeleton name/description
- **Files:** `composer.json`

---

## P3 — Future (from ARCHITECTURE.md)

| Feature | Description | Reference |
|---------|-------------|-----------|
| Seller enrichment | Receita Federal CNPJ API lookup | `ARCHITECTURE.md` §3 |
| Invoice configuration | CNAE, service codes, ISS aliquot per marketplace | `ARCHITECTURE.md` §3 |
| Import validation rules editor | User-configurable thresholds | `ARCHITECTURE.md` §3 |
| Email delivery to sellers | Send PDF/XML directly to seller email | `SendInvoiceEmailJob` exists |
| Outbound webhooks | Post-generation events for integrations | `ARCHITECTURE.md` §3 |
| Audit log viewer UI | Search/filter audit events | `AuditLog` model exists |
| REST API | `/api/v1` with Sanctum token auth | `laravel/sanctum` installed |
| Two-factor authentication | TOTP via Fortify | User model has 2FA columns |
| Localization (i18n) | Laravel translations + react-i18next | UI is English; domain is Brazil-specific |
| Multi-organization tenancy | `organizations` table with multiple users | Currently single-user scoping |
| Column mapping UI | Preview/edit column map before parse commit | `marketplace.config.column_map` |
| Virus scanning | ClamAV or cloud scan before parsing | `ARCHITECTURE.md` §1 |

---

## Completed (Implemented)

- [x] Interface-driven marketplace importers (`MarketplaceImporterInterface` + `ShopeeImporter`)
- [x] Interface-driven invoice providers (`InvoiceProviderInterface` + `NullInvoiceProvider`)
- [x] Chunk-based parsing with `Bus::batch()` (`ParseImportJob` → `ParseChunkJob`)
- [x] Bulk row insert (500 rows/batch via `insertOrIgnore`)
- [x] Seller entity with split address columns (`Seller` model)
- [x] Invoice grouping by seller/month (`GroupRowsForInvoicingAction`)
- [x] Many-to-many invoice ↔ rows via `invoice_import_rows` pivot
- [x] Subscription limits (`SubscriptionService` + `config/plans.php`)
- [x] Stripe billing (Cashier checkout, portal, webhook)
- [x] Rate limiting on invoice provider (`Redis::throttle`)
- [x] Job retry/backoff configuration on all pipeline jobs
- [x] Audit logging via `AuditObserver` on Import, Invoice, Seller
- [x] User scoping via `BelongsToUserScope`
- [x] Inertia + React frontend with shadcn/ui
- [x] Landing page with pricing
- [x] Import/invoice polling hooks
- [x] Monthly NF usage reset (`routes/console.php` scheduled task)
