# Operations

How to install, run, build, test, and deploy NF-facilitator.

---

## Prerequisites

| Requirement | Version | Notes |
|------------|---------|-------|
| PHP | 8.4+ | Extensions: mbstring, xml, curl, redis, zip |
| Composer | 2.x | Dependency management |
| Node.js | 20+ | Frontend build |
| MySQL | 8.0+ | Primary database |
| Redis | 6+ | Queue, cache, sessions |
| AWS S3 | — | File storage (or use local disk for dev) |

---

## First-Time Setup

```bash
# 1. Clone repository
git clone <repo-url> nf-facilitator
cd nf-facilitator

# 2. Install dependencies and bootstrap
composer setup
# Runs: composer install, copy .env, key:generate, migrate, npm install, npm run build

# 3. Configure environment
cp .env.example .env   # skip if setup already copied it
# Edit .env — see "Environment Variables" below

# 4. Seed marketplace data
php artisan db:seed --class=MarketplaceSeeder

# 5. (Optional) Seed development data
php artisan db:seed    # includes DevelopmentSeeder in local env
```

---

## Environment Variables

### Required for Local Dev

```env
APP_NAME="NF-facilitator"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nf_facilitator
DB_USERNAME=root
DB_PASSWORD=

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
CACHE_STORE=redis
```

### Storage

```env
# Production (default)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=

# Local dev without AWS (override)
FILESYSTEM_DISK=local
```

**Assumption:** Without S3 credentials, import upload and invoice file storage will fail. Use `FILESYSTEM_DISK=local` for local-only testing.

### NF-facilitator Config

```env
NF_IMPORT_CHUNK_SIZE=10000
NF_IMPORT_MAX_FILE_SIZE_KB=51200       # 50 MB
NF_INVOICE_MAX_RETRIES=3
NF_INVOICE_ZIP_TTL_HOURS=24
NF_DOWNLOAD_URL_TTL_MINUTES=15
```

### Stripe (Billing)

```env
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=
STRIPE_PRICE_BASIC=
STRIPE_PRICE_ADVANCED=
```

### Mail

```env
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@nffacilitator.com.br
MAIL_FROM_NAME="NF Facilitator"
```

### Horizon

```env
HORIZON_PATH=horizon
```

---

## Running Locally

### All-in-One Dev Command

```bash
composer dev
```

Starts concurrently:
- `php artisan serve` — HTTP server on `:8000`
- `php artisan queue:listen --tries=1` — queue worker
- `php artisan pail` — log tail
- `npm run dev` — Vite HMR

### Individual Services

```bash
# HTTP server
php artisan serve

# Queue worker (required for import/invoice pipeline)
php artisan queue:listen --tries=1

# Vite dev server (frontend HMR)
npm run dev

# Log viewer
php artisan pail
```

### Horizon (Queue Dashboard)

```bash
php artisan horizon
```

Visit `http://localhost:8000/horizon`. Configure authorized emails in `app/Providers/HorizonServiceProvider.php`.

---

## Building for Production

```bash
# Frontend assets
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Run migrations
php artisan migrate --force
```

---

## Testing

```bash
# Run all tests
composer test
# Equivalent to: php artisan config:clear && php artisan test

# Run specific suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run specific file
php artisan test tests/Feature/Auth/AuthenticationTest.php

# With coverage (requires Xdebug/PCOV)
php artisan test --coverage
```

**Test environment** (from `phpunit.xml`):
- Database: SQLite in-memory (`DB_DATABASE=:memory:`)
- Queue: sync (jobs run inline)
- Mail: array (no actual sending)
- Cache/Session: array

**Current coverage:** Auth tests only (Breeze scaffold). Domain pipeline tests are pending (see `.ai/backlog.md`).

---

## Code Style

```bash
# Format PHP (PSR-12)
./vendor/bin/pint

# Check without fixing
./vendor/bin/pint --test
```

No frontend linter configured. Follow existing patterns in `resources/js/`.

---

## Database

```bash
# Run migrations
php artisan migrate

# Fresh migrate (drops all tables)
php artisan migrate:fresh

# Fresh migrate + seed
php artisan migrate:fresh --seed

# Seed specific seeder
php artisan db:seed --class=MarketplaceSeeder

# Rollback last batch
php artisan migrate:rollback
```

### Seeders

| Seeder | Purpose |
|--------|---------|
| `DatabaseSeeder` | Test user + MarketplaceSeeder (+ DevelopmentSeeder in local) |
| `MarketplaceSeeder` | Shopee marketplace with column map |
| `DevelopmentSeeder` | Additional dev data (local only) |

---

## Scheduled Tasks

Defined in `routes/console.php`:

| Schedule | Command | Purpose |
|----------|---------|---------|
| 1st of month, 00:00 | `SubscriptionService::resetMonthlyUsage()` | Reset `nf_usage_this_month` for all users |

Production requires a cron entry:

```cron
* * * * * cd /path/to/nf-facilitator && php artisan schedule:run >> /dev/null 2>&1
```

---

## Deployment

**Assumption:** No CI/CD, Dockerfile, or deployment scripts exist yet. Below is the recommended manual process.

### Production Checklist

1. Set `APP_ENV=production`, `APP_DEBUG=false`
2. Configure all env vars (DB, Redis, AWS, Stripe, Mail)
3. Run `composer install --no-dev --optimize-autoloader`
4. Run `npm ci && npm run build`
5. Run `php artisan migrate --force`
6. Run `php artisan config:cache && php artisan route:cache && php artisan view:cache`
7. Start Horizon: `php artisan horizon` (via supervisor)
8. Configure cron for `schedule:run`
9. Configure Stripe webhook to `https://yourdomain.com/stripe/webhook`
10. Set Horizon admin emails in `HorizonServiceProvider`

### Recommended Process Manager (Supervisor)

```ini
[program:nf-horizon]
process_name=%(program_name)s
command=php /path/to/nf-facilitator/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/horizon.log
stopwaitsecs=3600
```

### Health Check

Laravel 12 exposes `/up` (configured in `bootstrap/app.php`).

---

## Troubleshooting

| Problem | Likely Cause | Fix |
|---------|-------------|-----|
| Import stuck on "Parsing" | Queue worker not running | Start `php artisan queue:listen` or `composer dev` |
| "Class not found" after deploy | Autoload not optimized | `composer dump-autoload -o` |
| S3 upload fails | Missing AWS credentials | Set `AWS_*` env vars or use `FILESYSTEM_DISK=local` |
| Invoice generation fails silently | Plan limit reached | Check `users.nf_usage_this_month` vs plan limit |
| Horizon 403 | Email not in gate | Add email to `HorizonServiceProvider::gate()` |
| Frontend not updating | Vite not running | Start `npm run dev` or run `npm run build` |
| Stripe webhook fails | Wrong secret or CSRF | Verify `STRIPE_WEBHOOK_SECRET`; route is CSRF-exempt |
| Tests fail on migrate | Missing SQLite | Tests use in-memory SQLite; no setup needed |

---

## Useful Artisan Commands

```bash
php artisan route:list              # All registered routes
php artisan queue:failed            # List failed jobs
php artisan queue:retry all           # Retry all failed jobs
php artisan tinker                    # REPL for debugging
php artisan horizon:pause             # Pause queue processing
php artisan horizon:continue          # Resume queue processing
php artisan db:show                   # Database connection info
```
