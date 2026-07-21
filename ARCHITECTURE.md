# Affiliate Invoice Manager — Architectural Proposal

**Project codename:** AfiliFacil  
**Stack:** Laravel 12 · PHP 8.4+ · React 19 (plain JS) · InertiaJS · MySQL · Redis  
**Date:** 2026-07-18  
**Status:** Analysis Phase — no code scaffolded yet

---

## Table of Contents

1. [Requirements Analysis & Suggested Improvements](#1-requirements-analysis--suggested-improvements)
2. [Scalability Concerns](#2-scalability-concerns)
3. [Recommended Additional Features](#3-recommended-additional-features)
4. [Full Folder Structure](#4-full-folder-structure)
5. [Full Database Schema](#5-full-database-schema)
6. [Model Relationships](#6-model-relationships)
7. [Complete Application Workflow](#7-complete-application-workflow)
8. [Project Structure Proposal](#8-project-structure-proposal)

---

## 1. Requirements Analysis & Suggested Improvements

### What the spec gets right

The spec's architecture already reflects solid SaaS thinking: interface-driven marketplace importers, interface-driven invoice providers, queue-heavy async processing, and a DTO normalization layer that decouples marketplace quirks from domain logic. These are the right decisions.

### Identified gaps and recommended changes

**Duplicate detection scope is too narrow.** The spec proposes SHA-256 hashing per upload to catch duplicate files. This is good, but not sufficient — the same commissions can appear across different files (partial re-exports, corrected reports). Add a `seller_document + reference_month` uniqueness constraint at the `import_rows` level, and surface a "possible duplicate rows" warning during the preview phase.

**The `ImportRow` address field should be split.** Storing `seller_address` as a single JSON blob makes filtering, validation, and future invoice field mapping fragile. Separate into typed columns: `address_street`, `address_number`, `address_complement`, `address_district`, `address_city`, `address_state`, `address_zip`. This aligns with Brazilian NF-e address fields from day one.

**No seller entity.** The current schema has seller data embedded directly in `ImportRow`. When the same seller appears across multiple imports, their data is duplicated and can drift (e.g., a name typo in one import). Introduce a `Seller` model keyed on `tax_document` (CNPJ/CPF). `ImportRow` then has a `seller_id` FK. Sellers can be reviewed, corrected, and enriched (e.g., via Receita Federal lookup) independently of individual rows.

**Invoices need a `reference_month` field.** The fiscal month of the commission is a first-class concern for the NF-e — it lives in `ImportRow` but is not propagated to `Invoice`. Add `reference_month` to `Invoice` so reporting and filtering at the invoice level are possible without joining back through `ImportRow`.

**Invoice generation is many-to-one per seller per month, not one-to-one per row.** In practice, a single NF-e is issued for the total commissions from one CNPJ in a given month, not one NF-e per row. The spec implies one invoice per `ImportRow`. Reconsider: `GenerateInvoicesJob` should group rows by `seller_document + reference_month` before dispatching `IssueInvoiceJob`, and an invoice's `amount` should represent the summed commission. The relationship is therefore `Invoice hasMany ImportRows` (through a pivot `invoice_import_rows`), not `Invoice belongsTo ImportRow`. This is the single most important architectural clarification in this document.

**Missing: retry budget and dead-letter strategy.** Jobs that fail indefinitely occupy queue capacity. Each job should declare `$tries`, `$backoff`, and `$maxExceptions`. Failed jobs that exhaust retries must write a terminal `InvoiceEvent` record with the error payload. Horizon's failed-job dashboard handles visibility, but the app needs first-class "retry" and "mark as unresolvable" actions in the UI.

**Missing: user notification channel preferences.** The spec mentions Notifications but not the channels. Add a `notification_preferences` JSON column to `users` covering: email, in-app (database channel), and optionally Slack webhook. Default: email + database.

**Missing: importable column mapping UI.** Different Shopee report versions (and future marketplaces) may shift column positions. The `ShopeeImporter` should support a configurable column map stored in `marketplace.config`. The UI should offer a "column preview" step before parsing commits, so users can fix mismatches without contacting support.

**Security: file validation must be server-side.** MIME type must be verified from file content (not extension or client-provided type). Use `finfo` / `League\MimeTypeDetection`. Uploaded files must be virus-scanned before parsing (ClamAV or cloud equivalent). The spec mentions "secure uploads" but does not detail this — make it explicit in implementation.

**Multi-tenancy scaffolding.** The spec says "multi-tenant ready" but defers it. Recommend using a `GlobalScope` (`BelongsToUserScope`) on every business model from day one, rather than retrofitting. If the future shape is "organizations with multiple users," introduce an `organizations` table now (even if each organization has exactly one user at launch) to avoid a painful migration later.

---

## 2. Scalability Concerns

### Large file parsing

Shopee commission reports can contain tens of thousands of rows. Loading the entire file into memory in a single job will cause timeouts and memory exhaustion. Mitigation:

- **Chunk-based parsing** using Laravel Excel's `WithChunkReading` (10,000 rows per chunk) or a streaming CSV reader.
- `ParseImportJob` should not parse inline — it should dispatch one `ParseChunkJob` per chunk, each operating within a 60-second window with a known memory ceiling.
- Use `Bus::batch()` to group chunk jobs, with a `then()` callback that fires `ValidateImportJob` only after all chunks complete.

### Invoice issuance at scale

Issuing 10,000 invoices against an external provider API will hit rate limits. The `IssueInvoiceJob` must implement:

- Provider-specific rate limiting via `Redis::throttle()`.
- Exponential backoff on 429/503 responses.
- Job chaining that pauses and resumes on provider downtime without losing state.

### Database write pressure

Importing thousands of rows in parallel chunk jobs hammers the `import_rows` table. Use `DB::table()->insertOrIgnore()` in bulk batches (500 rows per statement) rather than Eloquent model-per-row saves. Reserve Eloquent observers for the final status transition, not the initial bulk insert.

### ZIP file generation

Generating a ZIP of thousands of PDF/XML files by downloading them one-by-one from S3 then re-uploading will time out. Strategy:

- `GenerateZipJob` should use a streaming ZIP approach (`ZipStream-PHP` or equivalent) that pipes S3 object streams directly into the ZIP output without materializing all files on disk simultaneously.
- Generated ZIPs should have a TTL (e.g., 24 hours) and be regenerated on demand rather than kept forever.

### Queue prioritization

Not all jobs are equal. Recommended queue hierarchy:

| Queue | Jobs |
|---|---|
| `critical` | Auth jobs, password resets |
| `high` | `ParseImportJob`, `ValidateImportJob` |
| `default` | `GenerateInvoicesJob`, `IssueInvoiceJob` |
| `low` | `GenerateZipJob`, `UploadInvoiceFilesJob`, `AuditLog` writes |

### Read performance

The import preview table (`import_rows`) can have millions of rows across all users. Composite indexes on `(import_id, status)` and `(import_id, seller_document)` are mandatory from day one. Full-text search on seller name should use MySQL FULLTEXT rather than `LIKE '%...%'`.

### Caching

Dashboard aggregate queries (total commissions, invoice counts) must be cached in Redis with tagged cache invalidation on `Import` and `Invoice` model events. Cache TTL: 5 minutes for dashboard cards, 1 hour for historical charts.

---

## 3. Recommended Additional Features

**Seller registry with enrichment.** A dedicated `Sellers` section where users can review and correct seller data (name, CNPJ, address) before invoice generation. Optionally integrate with the Receita Federal CNPJ API to auto-fill missing address fields. Clean seller data = fewer invoice rejections.

**Invoice template / configuration per marketplace.** Different NF-e providers require different service codes, municipality codes, and tax descriptions. Store a `InvoiceConfiguration` per marketplace (or per user + marketplace combination) with CNAE, service description, ISS aliquot, and municipality code. This prevents hardcoding fiscal parameters in job code.

**Import validation rules editor.** Allow users to configure validation thresholds (e.g., "flag rows where commission > R$ 10,000" or "require email on all rows"). Stored in `marketplace.config`.

**Email delivery of invoices.** After successful generation, optionally email each seller their PDF/XML directly. Controlled by a per-import toggle. Uses Laravel Notification with a `SellerInvoiceNotification` mailable.

**Webhook outbound events.** Post-generation webhooks so partner systems can react to invoice issuance without polling. Standard SaaS expectation.

**Audit log viewer in UI.** The `AuditLog` table is in the spec, but a UI to search and filter audit events (by user, action, date range) is essential for support and compliance.

**Subscription/billing integration.** For SaaS monetization: Stripe billing via Laravel Cashier, tiered by invoice volume per month.

**API access.** A versioned REST API (under `/api/v1`) with Sanctum token auth, allowing programmatic report uploads and invoice status polling. This opens the product to integrations and power users.

**Two-factor authentication.** TOTP-based 2FA via `laravel-fortify`. For a product handling fiscal documents, this is a compliance expectation.

**Localization (i18n).** The NF-e domain is Brazil-specific, but the codebase should be i18n-ready from day one using Laravel's translation files and React's `react-i18next`.

---

## 4. Full Folder Structure

### Backend — Laravel 12

```
app/
├── Actions/                        # Single-responsibility action classes
│   ├── Import/
│   │   ├── CreateImportAction.php
│   │   ├── ParseImportChunkAction.php
│   │   ├── ValidateImportRowAction.php
│   │   └── DetectDuplicateImportAction.php
│   ├── Invoice/
│   │   ├── GroupRowsForInvoicingAction.php
│   │   ├── IssueInvoiceAction.php
│   │   ├── RetryInvoiceAction.php
│   │   └── BuildInvoiceZipAction.php
│   └── Seller/
│       └── UpsertSellerAction.php
│
├── Console/
│   └── Commands/                   # Artisan commands (cleanup, re-queue, etc.)
│
├── DTOs/                           # Plain PHP value objects with no logic
│   ├── CommissionRowDTO.php        # Normalized row from any marketplace
│   ├── ImportSummaryDTO.php
│   ├── InvoicePayloadDTO.php       # Normalized data passed to invoice provider
│   └── SellerDTO.php
│
├── Enums/
│   ├── ImportStatus.php            # pending | processing | done | failed | cancelled
│   ├── ImportRowStatus.php         # pending | valid | invalid | duplicate | invoiced
│   ├── InvoiceStatus.php           # queued | processing | generated | failed | cancelled
│   ├── InvoiceFileType.php         # pdf | xml | zip
│   ├── InvoiceEventType.php        # queued | processing | generated | failed | retried | downloaded
│   ├── JobExecutionStatus.php      # running | completed | failed
│   └── NotificationChannel.php    # email | database | slack
│
├── Events/
│   ├── Import/
│   │   ├── ImportUploaded.php
│   │   ├── ImportParsed.php
│   │   └── ImportFailed.php
│   └── Invoice/
│       ├── InvoiceGenerated.php
│       ├── InvoiceFailed.php
│       └── InvoiceDownloaded.php
│
├── Exceptions/
│   ├── DuplicateImportException.php
│   ├── InvalidFileException.php
│   ├── InvoiceProviderException.php
│   └── MarketplaceImporterException.php
│
├── Http/
│   ├── Controllers/
│   │   ├── Auth/                   # Standard Breeze/Fortify controllers
│   │   ├── DashboardController.php
│   │   ├── ImportController.php
│   │   ├── ImportRowController.php
│   │   ├── InvoiceController.php
│   │   ├── InvoiceDownloadController.php
│   │   ├── SellerController.php
│   │   └── ProfileController.php
│   │
│   ├── Middleware/
│   │   ├── EnsureEmailIsVerified.php
│   │   └── ScopeToUser.php         # Global user-scoping guard
│   │
│   └── Requests/
│       ├── Import/
│       │   ├── StoreImportRequest.php
│       │   └── OverrideDuplicateRequest.php
│       ├── Invoice/
│       │   ├── GenerateInvoicesRequest.php
│       │   └── RetryInvoiceRequest.php
│       └── Profile/
│           └── UpdateProfileRequest.php
│
├── Jobs/
│   ├── ParseImportJob.php          # Dispatches ParseChunkJobs via Bus::batch()
│   ├── ParseChunkJob.php           # Processes one chunk of rows
│   ├── ValidateImportJob.php       # Runs after all chunks complete
│   ├── GenerateInvoicesJob.php     # Groups rows, dispatches IssueInvoiceJobs
│   ├── IssueInvoiceJob.php         # Calls InvoiceProviderInterface for one invoice
│   ├── UploadInvoiceFilesJob.php   # Moves provider-returned files to S3
│   ├── GenerateZipJob.php          # Streams a ZIP of PDFs/XMLs to S3
│   └── SendInvoiceEmailJob.php     # Optional: emails invoice to seller
│
├── Listeners/
│   ├── Import/
│   │   ├── DispatchParseJob.php
│   │   └── NotifyUserOfImportResult.php
│   └── Invoice/
│       ├── RecordInvoiceEvent.php
│       └── NotifyUserOfInvoiceResult.php
│
├── Marketplace/
│   ├── Contracts/
│   │   └── MarketplaceImporterInterface.php
│   ├── Importers/
│   │   └── ShopeeImporter.php
│   └── Support/
│       └── ColumnMapper.php        # Maps raw header → DTO field using marketplace config
│
├── Models/
│   ├── AuditLog.php
│   ├── Import.php
│   ├── ImportRow.php
│   ├── Invoice.php
│   ├── InvoiceFile.php
│   ├── InvoiceEvent.php
│   ├── InvoiceImportRow.php        # Pivot: invoice ↔ import_rows
│   ├── JobExecution.php
│   ├── Marketplace.php
│   ├── Seller.php
│   └── User.php
│
├── Notifications/
│   ├── ImportCompletedNotification.php
│   ├── ImportFailedNotification.php
│   ├── InvoicesGeneratedNotification.php
│   └── SellerInvoiceNotification.php
│
├── Observers/
│   ├── ImportObserver.php
│   ├── InvoiceObserver.php
│   └── AuditObserver.php          # Generic observer for audit logging
│
├── Policies/
│   ├── ImportPolicy.php
│   ├── InvoicePolicy.php
│   └── SellerPolicy.php
│
├── Providers/
│   ├── AppServiceProvider.php
│   ├── AuthServiceProvider.php
│   └── MarketplaceServiceProvider.php  # Binds importer implementations
│
├── Services/
│   ├── FileValidationService.php   # MIME check, size, virus scan
│   ├── StorageService.php          # S3 upload/download/signed URL wrappers
│   ├── AuditService.php            # Writes AuditLog records
│   └── DashboardMetricsService.php # Cached aggregate queries
│
└── InvoiceProvider/
    ├── Contracts/
    │   └── InvoiceProviderInterface.php
    └── Providers/
        └── NullInvoiceProvider.php     # Stub that returns fake data (dev/test)

bootstrap/
config/
    ├── filesystems.php             # S3 disk configured here
    ├── horizon.php
    ├── queue.php
    └── afilifacil.php          # App-specific config (chunk size, rate limits, etc.)
database/
    ├── factories/
    ├── migrations/
    └── seeders/
routes/
    ├── web.php                     # Inertia routes
    └── auth.php
storage/
tests/
    ├── Feature/
    │   ├── Import/
    │   ├── Invoice/
    │   └── Auth/
    └── Unit/
        ├── Actions/
        ├── DTOs/
        └── Marketplace/
```

### Frontend — React 19 + InertiaJS

```
resources/
└── js/
    ├── app.jsx                     # Inertia bootstrap
    ├── bootstrap.js
    │
    ├── Components/                 # Reusable, stateless UI components
    │   ├── ui/                     # shadcn/ui re-exports & overrides
    │   │   ├── Button.jsx
    │   │   ├── Card.jsx
    │   │   ├── Dialog.jsx
    │   │   ├── Input.jsx
    │   │   ├── Select.jsx
    │   │   ├── Badge.jsx
    │   │   ├── Table.jsx
    │   │   └── ...
    │   ├── DataTable/
    │   │   ├── DataTable.jsx       # TanStack Table wrapper
    │   │   ├── DataTablePagination.jsx
    │   │   ├── DataTableToolbar.jsx
    │   │   └── DataTableColumnHeader.jsx
    │   ├── StatusBadge.jsx         # Renders ImportStatus / InvoiceStatus enums
    │   ├── SummaryCard.jsx         # Dashboard metric card
    │   ├── FileUploadZone.jsx      # Drag-and-drop uploader
    │   ├── InvoiceTimeline.jsx     # Event timeline component
    │   ├── ProgressBar.jsx
    │   ├── ConfirmDialog.jsx
    │   └── Pagination.jsx
    │
    ├── Layouts/
    │   ├── AppLayout.jsx           # Sidebar + topbar shell
    │   ├── AuthLayout.jsx          # Centered card for login/register
    │   └── GuestLayout.jsx
    │
    ├── Pages/                      # One file per Inertia page (maps to route)
    │   ├── Auth/
    │   │   ├── Login.jsx
    │   │   ├── Register.jsx
    │   │   ├── ForgotPassword.jsx
    │   │   ├── ResetPassword.jsx
    │   │   └── VerifyEmail.jsx
    │   ├── Dashboard/
    │   │   └── Index.jsx
    │   ├── Imports/
    │   │   ├── Index.jsx           # Import history list
    │   │   ├── Create.jsx          # Upload form
    │   │   ├── Show.jsx            # Import preview table
    │   │   └── DuplicateWarning.jsx # Modal: confirm override duplicate
    │   ├── Invoices/
    │   │   ├── Index.jsx           # Invoice list for an import
    │   │   └── Show.jsx            # Single invoice detail + timeline
    │   ├── Sellers/
    │   │   ├── Index.jsx
    │   │   └── Edit.jsx
    │   └── Profile/
    │       └── Edit.jsx
    │
    ├── hooks/                      # Custom React hooks
    │   ├── useImportPoller.js      # Polls import status until terminal state
    │   ├── useInvoicePoller.js     # Polls invoice job progress
    │   ├── useDarkMode.js
    │   └── useConfirm.js           # Imperative confirm dialog
    │
    ├── lib/                        # Pure utilities
    │   ├── formatters.js           # Currency, date, document formatters (BRL, pt-BR)
    │   ├── validators.js           # Zod schemas shared across forms
    │   └── cn.js                   # clsx + tailwind-merge helper
    │
    └── constants/
        ├── routes.js               # Named Ziggy route constants
        └── statuses.js             # Enum mirror for UI labels/colors
```

---

## 5. Full Database Schema

> Convention: all tables use `snake_case`. Primary keys are `BIGINT UNSIGNED AUTO_INCREMENT`. Timestamps are `TIMESTAMP` with `DEFAULT CURRENT_TIMESTAMP`. Foreign keys use `ON DELETE RESTRICT` unless noted.

---

### `users`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | NOT NULL | auto | PK |
| `name` | VARCHAR(255) | NOT NULL | — | |
| `email` | VARCHAR(255) | NOT NULL | — | UNIQUE |
| `email_verified_at` | TIMESTAMP | NULL | NULL | |
| `password` | VARCHAR(255) | NOT NULL | — | bcrypt hash |
| `remember_token` | VARCHAR(100) | NULL | NULL | |
| `two_factor_secret` | TEXT | NULL | NULL | TOTP secret (encrypted) |
| `two_factor_recovery_codes` | TEXT | NULL | NULL | JSON (encrypted) |
| `notification_preferences` | JSON | NOT NULL | `{}` | Channels per event type |
| `created_at` | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | |
| `updated_at` | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | |

**Indexes:** `UNIQUE(email)`

---

### `marketplaces`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | NOT NULL | auto | PK |
| `name` | VARCHAR(100) | NOT NULL | — | e.g. "Shopee" |
| `slug` | VARCHAR(100) | NOT NULL | — | e.g. "shopee" — UNIQUE |
| `importer_class` | VARCHAR(255) | NOT NULL | — | FQCN of importer |
| `active` | TINYINT(1) | NOT NULL | 1 | |
| `config` | JSON | NOT NULL | `{}` | Column maps, fiscal params |
| `created_at` | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | |
| `updated_at` | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | |

**Indexes:** `UNIQUE(slug)`

---

### `sellers`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | NOT NULL | auto | PK |
| `user_id` | BIGINT UNSIGNED | NOT NULL | — | FK → users.id |
| `tax_document` | VARCHAR(20) | NOT NULL | — | CNPJ or CPF (digits only) |
| `document_type` | ENUM('cpf','cnpj') | NOT NULL | 'cnpj' | |
| `name` | VARCHAR(255) | NOT NULL | — | Legal name |
| `trade_name` | VARCHAR(255) | NULL | NULL | Fantasy name |
| `email` | VARCHAR(255) | NULL | NULL | |
| `address_street` | VARCHAR(255) | NULL | NULL | |
| `address_number` | VARCHAR(20) | NULL | NULL | |
| `address_complement` | VARCHAR(100) | NULL | NULL | |
| `address_district` | VARCHAR(100) | NULL | NULL | |
| `address_city` | VARCHAR(100) | NULL | NULL | |
| `address_state` | CHAR(2) | NULL | NULL | UF code |
| `address_zip` | VARCHAR(10) | NULL | NULL | CEP (digits only) |
| `address_ibge_code` | VARCHAR(10) | NULL | NULL | For NF-e municipality code |
| `enriched_at` | TIMESTAMP | NULL | NULL | Last Receita Federal sync |
| `created_at` | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | |
| `updated_at` | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | |

**Indexes:** `UNIQUE(user_id, tax_document)`, `INDEX(user_id)`

---

### `imports`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | NOT NULL | auto | PK |
| `user_id` | BIGINT UNSIGNED | NOT NULL | — | FK → users.id |
| `marketplace_id` | BIGINT UNSIGNED | NOT NULL | — | FK → marketplaces.id |
| `original_filename` | VARCHAR(255) | NOT NULL | — | User-visible filename |
| `storage_path` | VARCHAR(500) | NOT NULL | — | S3 key |
| `disk` | VARCHAR(50) | NOT NULL | 's3' | |
| `file_hash` | CHAR(64) | NOT NULL | — | SHA-256 hex |
| `file_size` | BIGINT UNSIGNED | NOT NULL | — | Bytes |
| `status` | ENUM | NOT NULL | 'pending' | See `ImportStatus` enum |
| `total_rows` | INT UNSIGNED | NULL | NULL | Set after parsing |
| `valid_rows` | INT UNSIGNED | NULL | NULL | |
| `invalid_rows` | INT UNSIGNED | NULL | NULL | |
| `duplicate_rows` | INT UNSIGNED | NULL | NULL | |
| `total_amount` | DECIMAL(15,2) | NULL | NULL | Sum of commission amounts |
| `total_unique_tax_ids` | INT UNSIGNED | NULL | NULL | |
| `reference_month` | CHAR(7) | NULL | NULL | `YYYY-MM` inferred from data |
| `parsed_at` | TIMESTAMP | NULL | NULL | |
| `imported_at` | TIMESTAMP | NULL | NULL | Alias: when user triggered upload |
| `error_message` | TEXT | NULL | NULL | Top-level parse error if any |
| `created_at` | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | |
| `updated_at` | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | |
| `deleted_at` | TIMESTAMP | NULL | NULL | Soft delete |

**`ImportStatus` values:** `pending`, `uploading`, `parsing`, `parsed`, `validating`, `validated`, `done`, `failed`, `cancelled`

**Indexes:** `INDEX(user_id, created_at)`, `INDEX(user_id, status)`, `INDEX(file_hash)` (for duplicate detection)

---

### `import_rows`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | NOT NULL | auto | PK |
| `import_id` | BIGINT UNSIGNED | NOT NULL | — | FK → imports.id |
| `seller_id` | BIGINT UNSIGNED | NULL | NULL | FK → sellers.id (set after upsert) |
| `row_number` | INT UNSIGNED | NOT NULL | — | Source row index |
| `seller_name` | VARCHAR(255) | NOT NULL | — | Raw from file |
| `seller_document` | VARCHAR(20) | NOT NULL | — | Raw from file |
| `seller_email` | VARCHAR(255) | NULL | NULL | |
| `invoice_amount` | DECIMAL(15,2) | NOT NULL | — | Commission value |
| `reference_month` | CHAR(7) | NOT NULL | — | `YYYY-MM` |
| `status` | ENUM | NOT NULL | 'pending' | See `ImportRowStatus` enum |
| `validation_errors` | JSON | NULL | NULL | Array of error strings |
| `payload` | JSON | NOT NULL | `{}` | Full raw row for auditability |
| `created_at` | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | |
| `updated_at` | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | |

**`ImportRowStatus` values:** `pending`, `valid`, `invalid`, `duplicate`, `queued`, `invoiced`, `failed`

**Indexes:**
- `INDEX(import_id, status)`
- `INDEX(import_id, seller_document)`
- `INDEX(seller_id)`
- `UNIQUE(import_id, row_number)`
- `FULLTEXT(seller_name)` — for search
- `INDEX(seller_document, reference_month)` — for cross-import duplicate detection

---

### `invoices`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | NOT NULL | auto | PK |
| `import_id` | BIGINT UNSIGNED | NOT NULL | — | FK → imports.id |
| `seller_id` | BIGINT UNSIGNED | NOT NULL | — | FK → sellers.id |
| `status` | ENUM | NOT NULL | 'queued' | See `InvoiceStatus` enum |
| `reference_month` | CHAR(7) | NOT NULL | — | `YYYY-MM` |
| `amount` | DECIMAL(15,2) | NOT NULL | — | Sum of all linked import_rows |
| `invoice_number` | VARCHAR(50) | NULL | NULL | NF-e number |
| `access_key` | VARCHAR(50) | NULL | NULL | Chave de acesso (44 digits) |
| `issued_at` | TIMESTAMP | NULL | NULL | |
| `provider` | VARCHAR(100) | NULL | NULL | Provider slug (e.g. 'plugnotas') |
| `provider_reference` | VARCHAR(255) | NULL | NULL | External ID from provider |
| `provider_payload` | JSON | NULL | NULL | Full provider response |
| `retry_count` | TINYINT UNSIGNED | NOT NULL | 0 | |
| `created_at` | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | |
| `updated_at` | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | |
| `deleted_at` | TIMESTAMP | NULL | NULL | Soft delete |

**`InvoiceStatus` values:** `queued`, `processing`, `generated`, `failed`, `cancelled`, `retrying`

**Indexes:**
- `UNIQUE(import_id, seller_id, reference_month)` — prevents double-issuance
- `INDEX(import_id, status)`
- `INDEX(seller_id)`
- `INDEX(access_key)`

---

### `invoice_import_rows` (pivot)

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | NOT NULL | auto | PK |
| `invoice_id` | BIGINT UNSIGNED | NOT NULL | — | FK → invoices.id |
| `import_row_id` | BIGINT UNSIGNED | NOT NULL | — | FK → import_rows.id |
| `created_at` | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | |

**Indexes:** `UNIQUE(invoice_id, import_row_id)`, `INDEX(import_row_id)`

---

### `invoice_files`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | NOT NULL | auto | PK |
| `invoice_id` | BIGINT UNSIGNED | NOT NULL | — | FK → invoices.id |
| `type` | ENUM('pdf','xml','zip') | NOT NULL | — | |
| `disk` | VARCHAR(50) | NOT NULL | 's3' | |
| `storage_path` | VARCHAR(500) | NOT NULL | — | S3 key |
| `size` | BIGINT UNSIGNED | NULL | NULL | Bytes |
| `expires_at` | TIMESTAMP | NULL | NULL | For ZIP TTL |
| `created_at` | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | |
| `updated_at` | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | |

**Indexes:** `INDEX(invoice_id, type)`

---

### `invoice_events`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | NOT NULL | auto | PK |
| `invoice_id` | BIGINT UNSIGNED | NOT NULL | — | FK → invoices.id |
| `event` | ENUM | NOT NULL | — | See `InvoiceEventType` enum |
| `metadata` | JSON | NULL | NULL | Error details, provider response, etc. |
| `created_at` | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | |

**`InvoiceEventType` values:** `queued`, `processing`, `generated`, `failed`, `retried`, `downloaded`, `cancelled`

**Indexes:** `INDEX(invoice_id, created_at)`

---

### `job_executions`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | NOT NULL | auto | PK |
| `job_class` | VARCHAR(255) | NOT NULL | — | FQCN |
| `import_id` | BIGINT UNSIGNED | NULL | NULL | FK → imports.id |
| `invoice_id` | BIGINT UNSIGNED | NULL | NULL | FK → invoices.id |
| `status` | ENUM('running','completed','failed') | NOT NULL | 'running' | |
| `started_at` | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | |
| `finished_at` | TIMESTAMP | NULL | NULL | |
| `error_message` | TEXT | NULL | NULL | |
| `error_trace` | TEXT | NULL | NULL | Stack trace (truncated) |
| `created_at` | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | |
| `updated_at` | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | |

**Indexes:** `INDEX(import_id)`, `INDEX(invoice_id)`, `INDEX(status, created_at)`

---

### `audit_logs`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | NOT NULL | auto | PK |
| `user_id` | BIGINT UNSIGNED | NULL | NULL | FK → users.id (null for system) |
| `action` | VARCHAR(100) | NOT NULL | — | e.g. `import.created`, `invoice.retried` |
| `auditable_type` | VARCHAR(255) | NOT NULL | — | Morph type |
| `auditable_id` | BIGINT UNSIGNED | NOT NULL | — | Morph ID |
| `old_values` | JSON | NULL | NULL | |
| `new_values` | JSON | NULL | NULL | |
| `ip_address` | VARCHAR(45) | NULL | NULL | IPv4/IPv6 |
| `user_agent` | VARCHAR(500) | NULL | NULL | |
| `created_at` | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | |

**Indexes:** `INDEX(user_id, created_at)`, `INDEX(auditable_type, auditable_id)`, `INDEX(action)`

> Note: `audit_logs` has no `updated_at` — audit records are append-only and must never be modified.

---

## 6. Model Relationships

### User

- `hasMany(Import)` — a user owns many imports
- `hasMany(Seller)` — a user's seller registry
- `hasMany(AuditLog)` — all user actions

### Marketplace

- `hasMany(Import)` — imports belong to a marketplace

### Import

- `belongsTo(User)`
- `belongsTo(Marketplace)`
- `hasMany(ImportRow)` — all rows in this import
- `hasMany(Invoice)` — all invoices generated from this import
- `hasMany(JobExecution)` — tracking of async jobs

### Seller

- `belongsTo(User)` — scoped per user
- `hasMany(ImportRow)` — all rows linked to this seller (across imports)
- `hasMany(Invoice)` — all invoices ever generated for this seller

### ImportRow

- `belongsTo(Import)`
- `belongsTo(Seller)` — nullable until the seller is resolved/created
- `belongsToMany(Invoice)` through `invoice_import_rows` — one row can belong to one invoice; one invoice aggregates many rows

### Invoice

- `belongsTo(Import)` — the originating import
- `belongsTo(Seller)` — the seller being invoiced
- `belongsToMany(ImportRow)` through `invoice_import_rows` — the rows that make up this invoice
- `hasMany(InvoiceFile)` — PDF, XML, ZIP files
- `hasMany(InvoiceEvent)` — timeline events
- `hasMany(JobExecution)` — job tracking

### InvoiceFile

- `belongsTo(Invoice)`

### InvoiceEvent

- `belongsTo(Invoice)`

### AuditLog

- `belongsTo(User)` (nullable)
- `morphTo(auditable)` — polymorphic: Import, Invoice, Seller, etc.

---

### Cardinality summary

| Relationship | Cardinality |
|---|---|
| User → Imports | 1 : N |
| User → Sellers | 1 : N |
| Marketplace → Imports | 1 : N |
| Import → ImportRows | 1 : N |
| Import → Invoices | 1 : N |
| Seller → ImportRows | 1 : N (across imports) |
| Seller → Invoices | 1 : N (across imports) |
| Invoice ↔ ImportRows | N : M (via `invoice_import_rows`) |
| Invoice → InvoiceFiles | 1 : N |
| Invoice → InvoiceEvents | 1 : N |
| Invoice → JobExecutions | 1 : N |
| AuditLog → auditable | N : 1 (polymorphic) |

---

## 7. Complete Application Workflow

### Phase 0 — Onboarding

1. User registers with name, email, and password. Email verification is sent immediately via `SendEmailVerificationNotification`.
2. User verifies email and lands on the Dashboard. The dashboard is empty (zero-state UI with a call-to-action to create a first import).
3. User visits **Profile** to fill in their own company data (name, CNPJ, address, email). This data is used as the **issuer** on every NF-e.

---

### Phase 1 — Report Upload

4. User navigates to **Imports → New Import**.
5. User selects marketplace (Shopee) and uploads the commission report file (CSV, XLSX, XLS).
6. The `StoreImportRequest` validates: file presence, MIME type (server-side via `finfo`), max size (configurable, e.g. 50 MB), and allowed extensions.
7. `ImportController@store` calls `FileValidationService` (deep MIME check, virus scan hook).
8. `CreateImportAction` runs:
   - Uploads the file to S3 under `imports/{user_id}/{uuid}/{filename}`.
   - Computes SHA-256 of the file.
   - Checks `imports` table for a matching `file_hash` for this user.
   - If duplicate found: returns a `DuplicateImportException`, which the controller catches and renders as the `Imports/DuplicateWarning` Inertia page. User confirms override or cancels.
   - Creates an `Import` record with status `pending`.
   - Fires `ImportUploaded` event.
9. `DispatchParseJob` listener handles `ImportUploaded` and dispatches `ParseImportJob` to the `high` queue.
10. Controller redirects to the Import Show page (preview) with status `parsing`.

---

### Phase 2 — Asynchronous Parsing

11. `ParseImportJob` runs on a queue worker:
    - Updates `Import.status` → `parsing`.
    - Creates a `JobExecution` record.
    - Resolves the correct `MarketplaceImporterInterface` implementation from the service container (keyed by `marketplace.importer_class`).
    - The importer downloads the S3 file to a temporary stream and reads it in chunks (10,000 rows per chunk using `WithChunkReading`).
    - For each chunk, dispatches a `ParseChunkJob`.
    - All chunk jobs are grouped in a `Bus::batch()`. The batch's `then()` callback dispatches `ValidateImportJob`.

12. Each `ParseChunkJob`:
    - Uses `ColumnMapper` to translate raw headers to DTO fields using `marketplace.config`.
    - Transforms each raw row into a `CommissionRowDTO` (normalized: tax_document, name, email, amount, reference_month, raw_payload).
    - Calls `UpsertSellerAction`: finds or creates a `Seller` record by `(user_id, tax_document)`, updating name/email if changed.
    - Bulk-inserts `ImportRow` records (500 rows per `insertOrIgnore` batch).
    - Does NOT set final validation status yet — that is the next phase.

13. After all chunk jobs complete, `ValidateImportJob` runs:
    - Iterates all `ImportRow` records for this import in cursor batches.
    - Calls `ValidateImportRowAction` on each row: checks required fields, CNPJ format (modulo-11 validation), amount > 0, reference_month format.
    - Checks for intra-import duplicates: rows with the same `seller_document + reference_month` within the same import are flagged `duplicate` (keeping the first occurrence as `valid`).
    - Checks for cross-import duplicates: rows where `seller_document + reference_month` already exists in another import for the same user → flagged with a warning (not auto-rejected; user decides).
    - Sets each row's `status` to `valid`, `invalid`, or `duplicate`.
    - Updates `Import` aggregate columns: `total_rows`, `valid_rows`, `invalid_rows`, `duplicate_rows`, `total_amount`, `total_unique_tax_ids`.
    - Updates `Import.status` → `validated`.
    - Fires `ImportParsed` event.

14. `NotifyUserOfImportResult` listener handles `ImportParsed` → sends `ImportCompletedNotification` via user's preferred channels.

---

### Phase 3 — Import Preview & Review

15. User opens the Import Show page. The frontend polls `ImportController@show` (or uses a lightweight status endpoint) until `import.status === 'validated'`.
16. The preview table renders all `ImportRow` records for this import using TanStack Table with server-side pagination, sorting, and filtering.
17. Summary cards display: Total Rows, Valid, Invalid, Duplicate, Total Commission, Unique Sellers, Unique CNPJs, Average Commission.
18. User can:
    - Filter by status (valid/invalid/duplicate).
    - Search by seller name (FULLTEXT) or document.
    - Click a row to see its `validation_errors` and raw `payload`.
    - Navigate to the Seller record to correct address/email before invoicing.
19. If there are invalid rows, a warning banner explains that those rows will be skipped during invoice generation unless corrected.

---

### Phase 4 — Invoice Generation

20. User clicks **Generate All Invoices** (or selects rows and clicks **Generate Selected**).
21. `InvoiceController@generate` (protected by `InvoicePolicy`) calls `GenerateInvoicesRequest` validation.
22. `GenerateInvoicesJob` is dispatched to the `default` queue.
23. `GenerateInvoicesJob` runs:
    - Groups `valid` (and optionally `duplicate`) `ImportRow` records by `(seller_id, reference_month)`.
    - For each group, calls `GroupRowsForInvoicingAction`:
      - Checks whether an `Invoice` for `(import_id, seller_id, reference_month)` already exists (using the UNIQUE index). If it does and is not `failed`/`cancelled`, skip.
      - Creates an `Invoice` record with status `queued` and `amount` = sum of the group's `invoice_amount`.
      - Inserts `invoice_import_rows` pivot records.
      - Sets each `ImportRow.status` → `queued`.
      - Writes an `InvoiceEvent` with `event = 'queued'`.
    - Dispatches one `IssueInvoiceJob` per invoice.

24. Each `IssueInvoiceJob` runs:
    - Updates `Invoice.status` → `processing`. Writes `InvoiceEvent(processing)`.
    - Resolves `InvoiceProviderInterface` from the service container.
    - Builds an `InvoicePayloadDTO` from the `Invoice` + `Seller` + user issuer data.
    - Calls `provider->issue(InvoicePayloadDTO)`.
    - On success:
      - Updates `Invoice`: `status → generated`, `invoice_number`, `access_key`, `issued_at`, `provider_reference`, `provider_payload`.
      - Sets each linked `ImportRow.status` → `invoiced`.
      - Writes `InvoiceEvent(generated)`.
      - Dispatches `UploadInvoiceFilesJob`.
    - On failure (provider error):
      - Updates `Invoice.status` → `failed`. Increments `retry_count`.
      - Writes `InvoiceEvent(failed)` with error metadata.
      - If `retry_count < $maxRetries`, the job's `backoff()` schedules automatic retry.
      - If retries exhausted, the invoice stays `failed` for manual retry.

25. `UploadInvoiceFilesJob` downloads PDF and XML from the provider's CDN and uploads them to S3 under `invoices/{user_id}/{invoice_id}/`. Creates `InvoiceFile` records.

26. The frontend polls a progress endpoint (`/imports/{import}/invoices/progress`) which returns counts of invoices by status. The `ProgressBar` component updates in real time.

---

### Phase 5 — Downloads

27. User can download:
    - **Individual PDF / XML**: `InvoiceDownloadController@show` validates policy, generates a presigned S3 URL (15-minute TTL), redirects. Writes `InvoiceEvent(downloaded)`.
    - **ZIP of all invoices for this import**: User clicks **Download All as ZIP**. If a valid (non-expired) ZIP `InvoiceFile` exists, return its presigned URL immediately. Otherwise, dispatch `GenerateZipJob`.

28. `GenerateZipJob`:
    - Streams S3 objects for all `generated` invoices (both PDF and XML) using `ZipStream-PHP` into a new S3 object.
    - Creates an `InvoiceFile` record with `type = 'zip'` and `expires_at = now() + 24h`.
    - Notifies the user (database notification + optional email) that the ZIP is ready.

---

### Phase 6 — Import History & Re-use

29. **Imports Index** page lists all past uploads with status, row counts, commission total, and created date.
30. User can open any past import and:
    - Re-view the preview table.
    - Re-download previously generated invoices.
    - Retry failed invoices (triggers new `IssueInvoiceJob` instances).
    - See job execution logs via `JobExecution` records.

---

## 8. Project Structure Proposal

### How the pieces fit together

```
┌─────────────────────────────────────────────────────────────┐
│                        User's Browser                       │
│   React 19 + InertiaJS + TailwindCSS + shadcn/ui            │
└──────────────────────────┬──────────────────────────────────┘
                           │  HTTPS (Inertia XHR / full page)
┌──────────────────────────▼──────────────────────────────────┐
│                    Laravel 12 App                           │
│  ┌──────────────┐  ┌──────────────┐  ┌───────────────────┐  │
│  │  Controllers │  │   Actions /  │  │     Services      │  │
│  │  (thin)      │→ │   Services   │→ │  FileValidation   │  │
│  └──────────────┘  │   DTOs       │  │  StorageService   │  │
│                    └──────┬───────┘  │  AuditService     │  │
│  ┌─────────────────────── │ ─────────┴───────────────────┐  │
│  │   Eloquent Models + Observers + Policies + Events      │  │
│  └─────────────────────── │ ─────────────────────────────┘  │
│                    ┌──────▼───────┐                         │
│                    │   Job Queue   │                         │
│                    │  (Redis)      │                         │
│                    └──────┬───────┘                         │
└───────────────────────────│─────────────────────────────────┘
                            │
          ┌─────────────────┴──────────────────┐
          │                                    │
┌─────────▼──────────┐              ┌──────────▼──────────┐
│   MySQL Database   │              │   AWS S3 Storage    │
│   (primary data)   │              │   (files, ZIPs)     │
└────────────────────┘              └─────────────────────┘
          │
┌─────────▼──────────┐
│   Redis             │
│   (cache + queues) │
│   Laravel Horizon  │
└────────────────────┘
```

### Deployment topology (Forge-ready)

- **Web server:** Nginx + PHP-FPM (PHP 8.4). Laravel Forge provisions and manages.
- **Queue workers:** Supervised by Laravel Horizon. Separate worker pools per queue priority (`critical`, `high`, `default`, `low`).
- **Scheduler:** Single cron entry (`php artisan schedule:run`) every minute. Handles: cache warm-up, ZIP expiry cleanup, Horizon monitoring pings.
- **Storage:** S3 bucket with versioning enabled and server-side encryption (SSE-S3). Presigned URLs for all downloads — files are never publicly accessible.
- **Redis:** ElastiCache (AWS) or managed Redis. Single instance at launch; Sentinel/Cluster when scaling.
- **Database:** RDS MySQL 8.0+ with daily snapshots. Read replica can be added later with no code changes (Laravel's `read`/`write` connection config).
- **Environment config:** `.env` managed by Forge secrets. No secrets in version control. Docker used for local development parity.

### Interface boundaries (seam points for future extension)

| Seam | Interface | Current implementation | Future |
|---|---|---|---|
| Marketplace parsing | `MarketplaceImporterInterface` | `ShopeeImporter` | `AmazonImporter`, `MercadoLivreImporter` |
| Invoice issuance | `InvoiceProviderInterface` | `NullInvoiceProvider` | `PlugNotasProvider`, `FocusNFeProvider` |
| Notifications | Laravel Notification channels | Email + Database | SMS, Slack, Push |
| File storage | Laravel Filesystem disk | `s3` | Any Flysystem-compatible driver |

### Testing strategy

- **Unit tests** cover: Actions, DTOs, Importers (against fixture files), `ValidateImportRowAction` edge cases (invalid CNPJ, zero amount, future reference month), `GroupRowsForInvoicingAction` grouping logic.
- **Feature tests** cover: full HTTP request cycle for import upload, invoice generation endpoints, policy enforcement (user can't access another user's import), duplicate detection response.
- **Job tests** use `Queue::fake()` to assert correct jobs are dispatched, and concrete job tests run against in-memory SQLite and mocked S3.
- **Factories** for all models with realistic Brazilian data (CNPJ generated with valid checksum, `reference_month` within last 12 months).
- **Seeders**: `DevelopmentSeeder` creates a test user with 3 imports at various states (parsed, validated, partially invoiced, fully invoiced).

### Code quality guardrails

- `php-cs-fixer` with PSR-12 + Laravel preset, enforced in CI.
- `phpstan` at level 8.
- Husky pre-commit hook runs `eslint` + `prettier` on JS files.
- All enum values referenced in PHP must use the Enum class, never raw strings.
- All queue-dispatched jobs implement `ShouldBeUnique` where appropriate (e.g., `ParseImportJob` is unique per `import_id`).

---

*This document represents the full analysis and architectural design for AfiliFacil. Implementation should begin only after review and approval of the schema changes and workflow clarifications described in Section 1.*
