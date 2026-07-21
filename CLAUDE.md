# AfiliFacil — AI Entry Point

**Project:** AfiliFacil — Affiliate Invoice Manager  
**Purpose:** SaaS platform that automates Brazilian NF-e invoice generation from marketplace commission reports (Shopee first).

> Start here. All deeper docs live under `.ai/`. The original design spec is in `ARCHITECTURE.md` (920 lines); this file reflects what is actually implemented in code.

---

## Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.4, Laravel 12 |
| Frontend | React 19 (plain JS, no TypeScript), Inertia.js v2 |
| CSS | Tailwind CSS 3 + shadcn/ui conventions |
| Build | Vite 7 (`@` alias → `resources/js`) |
| Database | MySQL 8 (tests: SQLite in-memory) |
| Queue / Cache | Redis via phpredis |
| Queue dashboard | Laravel Horizon 5 |
| Storage | AWS S3 (`FILESYSTEM_DISK=s3`) |
| Billing | Stripe via Laravel Cashier 16 |
| Testing | PHPUnit 11 |
| Linting | Laravel Pint |

---

## Architecture at a Glance

```
User uploads CSV/XLSX → S3 → ParseImportJob (batch of ParseChunkJobs)
  → ValidateImportJob → UI preview → GenerateInvoicesJob
  → IssueInvoiceJob (per invoice, rate-limited) → UploadInvoiceFilesJob
  → GenerateZipJob → notification + download
```

**Key design choices (implemented):**
- Invoices are **grouped by `(seller_id, reference_month)`**, not one per row — see `GroupRowsForInvoicingAction`
- Marketplace importers are interface-driven (`MarketplaceImporterInterface`); Shopee is the only concrete importer
- Invoice providers are interface-driven (`InvoiceProviderInterface`); **`NullInvoiceProvider` is wired today** (fake data for dev/test)
- Multi-tenancy via `BelongsToUserScope` on `Import`, `Seller` models
- Subscription limits enforced in `IssueInvoiceJob` via `SubscriptionService`

---

## Folder Map

```
app/
├── Actions/          # Single-responsibility domain logic (Import, Invoice, Seller)
├── DTOs/             # Value objects (CommissionRowDTO, InvoicePayloadDTO, …)
├── Enums/            # Status enums (ImportStatus, InvoiceStatus, …)
├── Events/           # Import/*, Invoice/* domain events
├── Jobs/             # Async pipeline (ParseImportJob → IssueInvoiceJob → GenerateZipJob)
├── Listeners/        # Event handlers (auto-discovered by Laravel)
├── Marketplace/      # Importer interface + ShopeeImporter
├── InvoiceProvider/  # Provider interface + NullInvoiceProvider
├── Models/           # 11 Eloquent models + BelongsToUserScope
├── Notifications/    # Mail + database notifications
├── Observers/        # ImportObserver, InvoiceObserver, AuditObserver
├── Policies/         # ImportPolicy, InvoicePolicy, SellerPolicy
├── Services/         # StorageService, SubscriptionService, AuditService, …
└── Http/Controllers/ # Inertia controllers

resources/js/
├── Pages/            # Inertia pages (Imports, Invoices, Billing, …)
├── Components/       # UI (shadcn/ui in Components/ui/, domain in Components/App/)
├── hooks/            # useImportPoller, useInvoicePoller, useConfirm, useDarkMode
├── lib/              # cn, formatters, validators
└── constants/        # statuses.js, routes.js

config/
├── afilifacil.php  # chunk_size, max_retries, zip TTL, presigned URL TTL
└── plans.php           # free (5 NF), basic (50), advanced (unlimited)

routes/web.php        # All app routes (auth, imports, invoices, billing, sellers)
```

---

## Commands

```bash
# First-time setup (install deps, key, migrate, npm build)
composer setup

# Dev environment (server + queue + logs + Vite concurrently)
composer dev

# Run tests
composer test
# or: php artisan test

# Frontend only
npm run dev
npm run build

# Database
php artisan migrate
php artisan db:seed                    # seeds MarketplaceSeeder (Shopee)
php artisan db:seed --class=MarketplaceSeeder

# Queue (required for import/invoice pipeline)
php artisan queue:listen               # used by composer dev
php artisan horizon                    # production dashboard at /horizon

# Code style
./vendor/bin/pint
```

**Prerequisites:** PHP 8.4+, Composer, Node 20+, MySQL 8, Redis, S3 credentials (or local disk override for dev).

Copy `.env.example` → `.env` and configure: `DB_*`, `REDIS_*`, `AWS_*`, `STRIPE_*`, `AFILIFACIL_*`.

---

## Routes (authenticated)

| Route | Controller | Purpose |
|-------|-----------|---------|
| `/dashboard` | DashboardController | Summary stats |
| `/imports` | ImportController | CRUD imports |
| `/imports/{id}/rows` | ImportRowController | Paginated row preview |
| `/imports/{id}/invoices/generate` | InvoiceController | Trigger invoice generation |
| `/invoices` | InvoiceController | List/show invoices, retry |
| `/sellers` | SellerController | Seller registry |
| `/billing` | BillingController | Stripe plans + checkout |
| `/horizon` | Horizon | Queue dashboard |

Public: `/` (Landing page), `/stripe/webhook`.

---

## Current Priorities (see `.ai/backlog.md`)

1. **In-house NFS-e engine** — replace `NullInvoiceProvider` with our own Padrão Nacional NFS-e driver (no third-party providers). Research: [`.ai/nfse/pesquisa.md`](.ai/nfse/pesquisa.md); architecture: [`.ai/nfse/arquitetura.md`](.ai/nfse/arquitetura.md)
2. **XLSX/XLS support** — `ShopeeImporter` is CSV-only today (TODO in code)
3. **Streaming ZIP** — `BuildInvoiceZipAction` is a stub (creates DB record, no actual ZIP)
4. **CNPJ/CPF validation** — length check only; modulo-11 checksum TODO
5. **Domain tests** — only Breeze auth tests exist; no import/invoice pipeline tests

---

## Conventions for AI Agents

### Backend
- Put business logic in `app/Actions/`, not controllers
- Dispatch jobs from actions/listeners, not controllers (except `InvoiceController::generate`)
- Use PHP enums for all status fields (`ImportStatus`, `InvoiceStatus`, …)
- New models that belong to a user: add `#[ScopedBy([BelongsToUserScope::class])]`
- Observers fire domain events; listeners handle side effects (notifications, job dispatch)
- Queue names: `high` (parse/validate), `default` (invoice), `low` (zip/upload)

### Frontend
- Pages in `resources/js/Pages/{Domain}/{Action}.jsx`
- Use Inertia `useForm` for mutations, `router.reload({ only: [...] })` for polling
- UI primitives from `@/Components/ui/*` (shadcn/ui pattern)
- Status labels from `@/constants/statuses.js`
- Formatters from `@/lib/formatters.js` (pt-BR locale)
- Route names via global `route()` helper (Ziggy)

### Testing
- Feature tests in `tests/Feature/`, unit in `tests/Unit/`
- SQLite in-memory, sync queue (see `phpunit.xml`)
- Run: `composer test`

---

## Documentation Index

| File | Contents |
|------|----------|
| [`.ai/architecture.md`](.ai/architecture.md) | Stack, folder structure, data flow, schema |
| [`.ai/nfse/pesquisa.md`](.ai/nfse/pesquisa.md) | NFS-e Brazil research: Padrão Nacional, ABRASF, certificates, XMLDSig (pt-BR) |
| [`.ai/nfse/arquitetura.md`](.ai/nfse/arquitetura.md) | In-house NFS-e engine architecture: `app/Fiscal/`, drivers, onboarding gate (pt-BR) |
| [`.ai/backlog.md`](.ai/backlog.md) | Pending tasks with priorities |
| [`.ai/decisions.md`](.ai/decisions.md) | Architectural decisions and rationale |
| [`.ai/OPERATIONS.md`](.ai/OPERATIONS.md) | Run, build, test, deploy |
| [`.ai/DEPLOYMENT.md`](.ai/DEPLOYMENT.md) | Production launch runbook: domain, Forge, S3, Horizon, gov.br/NFS-e, email, Stripe |
| [`.ai/roadmap.md`](.ai/roadmap.md) | Short and long-term goals |
| [`.ai/workflows/create-task.md`](.ai/workflows/create-task.md) | How to create/retrieve tasks in Linear (AfiliFacil project) |
| [`.ai/workflows/create-tests.md`](.ai/workflows/create-tests.md) | How to write and run tests |
| [`.ai/workflows/implement-task.md`](.ai/workflows/implement-task.md) | Step-by-step task implementation |
| [`.ai/workflows/review-code.md`](.ai/workflows/review-code.md) | Code review checklist |
| [`ARCHITECTURE.md`](ARCHITECTURE.md) | Original design document (analysis phase) |

---

## Assumptions

- **Invoice provider:** `NullInvoiceProvider` returns fake invoice numbers; no real NF-e is issued until a provider is integrated.
- **Storage:** Production uses S3; local dev may need `FILESYSTEM_DISK=local` if AWS is unavailable.
- **Event listeners:** Laravel auto-discovers listeners in `app/Listeners/` (no `EventServiceProvider`).
- **Sellers nav:** Seller routes exist but are not in the main nav (`AppLayout` NAV_ITEMS); accessible via direct URL.
- **CI/CD:** No GitHub Actions, Dockerfile, or Makefile exists yet.
