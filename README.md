# AfiliFacil

**Affiliate Invoice Manager** — SaaS platform that automates Brazilian NF-e invoice generation from marketplace commission reports.

Upload a Shopee commission report (CSV/XLSX), validate rows, group by seller/month, issue NF-e in batch, and download PDF/XML/ZIP.

---

## Stack

- **Backend:** Laravel 12, PHP 8.4
- **Frontend:** React 19, Inertia.js v2, Tailwind CSS, shadcn/ui
- **Infrastructure:** MySQL, Redis, AWS S3, Stripe (Cashier), Horizon

---

## Quick Start

```bash
# Clone and install
composer setup          # install deps, generate key, migrate, npm build

# Configure environment
cp .env.example .env    # edit DB, Redis, AWS, Stripe credentials

# Seed marketplace data
php artisan db:seed --class=MarketplaceSeeder

# Start dev environment (server + queue + logs + Vite)
composer dev
```

Visit `http://localhost:8000`. Register a user, upload a commission report, and follow the import → invoice pipeline.

---

## Documentation

| Document | Description |
|----------|-------------|
| **[CLAUDE.md](CLAUDE.md)** | AI entry point — architecture, commands, conventions, priorities |
| [`.ai/architecture.md`](.ai/architecture.md) | Detailed architecture, data flow, schema |
| [`.ai/OPERATIONS.md`](.ai/OPERATIONS.md) | Run, build, test, deploy |
| [`.ai/backlog.md`](.ai/backlog.md) | Pending tasks and priorities |
| [`.ai/decisions.md`](.ai/decisions.md) | Architectural decisions |
| [`.ai/roadmap.md`](.ai/roadmap.md) | Short and long-term goals |
| [`ARCHITECTURE.md`](ARCHITECTURE.md) | Original design document |

### AI Workflows

| Workflow | Purpose |
|----------|---------|
| [`.ai/workflows/implement-task.md`](.ai/workflows/implement-task.md) | Step-by-step task implementation |
| [`.ai/workflows/create-tests.md`](.ai/workflows/create-tests.md) | Writing and running tests |
| [`.ai/workflows/review-code.md`](.ai/workflows/review-code.md) | Code review checklist |

---

## Key Commands

```bash
composer dev          # Dev server + queue + logs + Vite
composer test         # Run PHPUnit tests
npm run dev           # Vite dev server only
npm run build         # Production frontend build
php artisan horizon   # Queue dashboard
./vendor/bin/pint     # Code formatting
```

---

## Subscription Plans

| Plan | Price | NF-e/month |
|------|-------|-----------|
| Gratuito | R$ 0 | 5 |
| Básico | R$ 39,90 | 50 |
| Avançado | R$ 169,90 | Unlimited |

Configured in `config/plans.php`. Stripe price IDs via `STRIPE_PRICE_BASIC` and `STRIPE_PRICE_ADVANCED` env vars.

---

## License

MIT
