# Deployment Runbook — Launching AfiliFacil in Production

End-to-end checklist for taking AfiliFacil from an empty server to a live,
production SaaS. Follow it top to bottom on the first deploy; use the
[Redeploy](#12-redeploy-routine) section for subsequent releases.

> **Target platform:** Laravel Forge managing a single Ubuntu server (LEMP:
> Nginx + PHP 8.4-FPM + MySQL 8 + Redis). Anything Forge does not manage (DNS,
> AWS, Stripe, gov.br certificates) is called out explicitly below.
>
> **Stack facts** this runbook depends on live in [OPERATIONS.md](OPERATIONS.md)
> (env var reference) and [architecture.md](architecture.md) (data flow).

---

## 0. Pre-flight — accounts you must own before starting

Provision these first; several later steps block on their credentials.

- [ ] **Domain** registered (e.g. `afilifacil.com.br`) with DNS you control
- [ ] **Server / hosting** — Laravel Forge account + a connected cloud provider
      (DigitalOcean, Hetzner, AWS EC2, …)
- [ ] **AWS account** — for the S3 bucket (imports, invoice PDFs/XMLs, ZIPs)
- [ ] **Stripe account** — live mode enabled, business verified for payouts
- [ ] **Transactional email provider** (Amazon SES, Postmark, Mailgun, Resend…)
- [ ] **gov.br / NFS-e access** — see [§8](#8-gov-nfs-e-fiscal-config). Not
      required to launch the marketing site + billing; **required before real
      invoices can be issued.**

---

## 1. Domain & DNS

1. [ ] Point the apex + `www` records at the production server IP:
   - `A` `@` → `<server-ip>`
   - `CNAME` `www` → `afilifacil.com.br` (or a second `A` record)
2. [ ] (Optional) Decide whether Horizon gets its own subdomain
   (`horizon.afilifacil.com.br`) — if so, add an `A` record and set
   `HORIZON_DOMAIN` accordingly. Default is a path (`/horizon`) on the main app.
3. [ ] Add the DNS records your email provider requires (SPF, DKIM, DMARC) —
   see [§9](#9-email-sending).
4. [ ] Wait for propagation (`dig afilifacil.com.br +short`).

---

## 2. Laravel Forge site

1. [ ] **Create server** in Forge (PHP 8.4, MySQL 8, Redis). Forge installs
   Nginx, PHP-FPM, MySQL, Redis, and a `forge` deploy user by default.
2. [ ] **Create site** for `afilifacil.com.br`:
   - Web directory: `/public`
   - PHP version: **8.4**
3. [ ] **Connect the Git repository** and pick the deploy branch (`main`).
4. [ ] **Enable Quick Deploy** so pushes to `main` trigger the deploy script.
5. [ ] **Provision TLS** — Forge → Site → SSL → **Let's Encrypt** for the apex,
   `www`, and any Horizon subdomain. Force HTTPS.
6. [ ] **Database** — create the `afilifacil` MySQL database + a dedicated user
   in Forge; note the credentials for [§4](#4-environment-file).
7. [ ] Set the **deploy script** (Forge → Site → Deploy Script):

   ```bash
   cd /home/forge/afilifacil.com.br
   git pull origin main

   $FORGE_COMPOSER install --no-dev --optimize-autoloader --no-interaction

   npm ci
   npm run build

   ( flock -w 10 9 || exit 1
     $FORGE_PHP artisan migrate --force
   ) 9>/tmp/afilifacil-deploy.lock

   $FORGE_PHP artisan config:cache
   $FORGE_PHP artisan route:cache
   $FORGE_PHP artisan view:cache
   $FORGE_PHP artisan event:cache

   $FORGE_PHP artisan horizon:terminate   # Horizon restarts with fresh code
   ```

   > `horizon:terminate` gracefully stops workers so the daemon (see
   > [§6](#6-queue-worker-horizon)) respawns them running the new release.

---

## 3. Clone / first build

On the first deploy Forge runs the deploy script above automatically. If you are
bootstrapping by hand instead, from the site root:

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan key:generate            # only if APP_KEY is empty
php artisan migrate --force
php artisan db:seed --class=MarketplaceSeeder   # seeds Shopee marketplace
```

> `MarketplaceSeeder` is **required** — without it there is no marketplace to
> import against. Do **not** run the full `DatabaseSeeder` in production (it
> creates a test user + dev data).

---

## 4. Environment file

In Forge → Site → **Environment**, set the full production `.env`. Start from
[`.env.example`](../.env.example) and [OPERATIONS.md § Environment Variables](OPERATIONS.md#environment-variables).
Critical differences from local:

```env
APP_NAME="AfiliFacil"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://afilifacil.com.br
APP_KEY=                      # generate once: php artisan key:generate

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=afilifacil
DB_USERNAME=afilifacil
DB_PASSWORD=<forge-db-password>

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=<set-a-password-in-prod>
REDIS_PORT=6379

SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
CACHE_STORE=redis
```

- [ ] `APP_ENV=production`, `APP_DEBUG=false` (never expose stack traces)
- [ ] `APP_KEY` generated **once** and kept stable — rotating it breaks every
      encrypted value, including stored certificate passwords ([§8](#8-gov-nfs-e-fiscal-config))
- [ ] `APP_URL` uses `https://`
- [ ] Redis has a password in production

Remaining sections ([§5](#5-aws-s3)–[§9](#9-email-sending)) fill in the rest of
this file.

---

## 5. AWS (S3)

File storage backs imports, generated invoice PDFs/XMLs, and download ZIPs.

1. [ ] Create a **private** S3 bucket (e.g. `afilifacil-prod`) in your region.
2. [ ] Keep **Block all public access** ON — the app serves files via
   short-lived presigned URLs (`AFILIFACIL_DOWNLOAD_URL_TTL_MINUTES`), never
   public objects.
3. [ ] Create an **IAM user** with a least-privilege policy scoped to that bucket
   (`s3:GetObject`, `s3:PutObject`, `s3:DeleteObject`, `s3:ListBucket`).
4. [ ] Set env vars:

   ```env
   FILESYSTEM_DISK=s3
   AWS_ACCESS_KEY_ID=<iam-key>
   AWS_SECRET_ACCESS_KEY=<iam-secret>
   AWS_DEFAULT_REGION=us-east-1
   AWS_BUCKET=afilifacil-prod
   AWS_USE_PATH_STYLE_ENDPOINT=false
   ```

5. [ ] (Recommended) Add an S3 **lifecycle rule** to expire old ZIP exports
   (they are regenerable; TTL is `AFILIFACIL_INVOICE_ZIP_TTL_HOURS`, default 24h).
6. [ ] Verify: `php artisan tinker` → `Storage::disk('s3')->put('healthcheck.txt','ok')`.

> There is no single "gov.br key" stored here — S3 holds fiscal **output** and
> the per-user certificate vault. Certificates themselves are covered in [§8](#8-gov-nfs-e-fiscal-config).

---

## 6. Queue worker (Horizon)

The entire import → invoice → ZIP pipeline runs on Redis queues
(`critical`, `high`, `default`, `low`) processed by **Laravel Horizon**. Without
a running worker, imports hang on "Parsing" forever.

1. [ ] In Forge → Site → **Daemons**, add a daemon:
   - Command: `php8.4 /home/forge/afilifacil.com.br/artisan horizon`
   - User: `forge`
   - Processes: `1` (Horizon manages its own child workers — see
     `config/horizon.php` `environments.production`)

   > Prefer a Forge **daemon** over a hand-written Supervisor block; Forge
   > supervises and restarts it. The equivalent Supervisor config is documented
   > in [OPERATIONS.md § Deployment](OPERATIONS.md#deployment) if you deploy
   > outside Forge.

2. [ ] Confirm the deploy script ends with `php artisan horizon:terminate` (from
   [§2](#2-laravel-forge-site)) so each release restarts workers with new code.
3. [ ] **Horizon dashboard access** — Horizon is gated to a list of emails.
   Add authorized admins in
   [`app/Providers/HorizonServiceProvider.php`](../app/Providers/HorizonServiceProvider.php)
   `gate()` (currently empty → nobody can view it in production):

   ```php
   return in_array(optional($user)->email, [
       'luishecastilho@gmail.com',
   ]);
   ```

4. [ ] Visit `https://afilifacil.com.br/horizon` and confirm supervisors are
   **active** and queues are draining.

---

## 7. Scheduler (cron)

One scheduled task exists: monthly reset of each user's NF usage counter
(`routes/console.php` → `SubscriptionService::resetMonthlyUsage()`, runs 1st of
month 00:00). Without cron, plan limits never reset.

1. [ ] Forge → Server → **Scheduler**, add a job (Forge writes the crontab):
   - Command: `php8.4 /home/forge/afilifacil.com.br/artisan schedule:run`
   - Frequency: **Every Minute**

   Equivalent raw crontab:

   ```cron
   * * * * * cd /home/forge/afilifacil.com.br && php artisan schedule:run >> /dev/null 2>&1
   ```

2. [ ] Verify: `php artisan schedule:list` shows the monthly reset.

---

## 8. Gov (NFS-e) fiscal config

> **Launch gating:** the active invoice provider is `AFILIFACIL_INVOICE_DRIVER`,
> which defaults to **`null`** (`config/afilifacil.php`) — the `NullInvoiceProvider`
> returns fake invoice numbers. **You can launch the site, billing, and imports
> with the null driver.** Real NFS-e issuance is blocked until the in-house
> Padrão Nacional engine ships (see [.ai/nfse/arquitetura.md](nfse/arquitetura.md)
> and [.ai/backlog.md](backlog.md)). Do **not** flip the driver to `nacional`
> in production until that engine is built, tested, and validated in restricted
> production.

There is **no single platform-wide gov.br API key**. The Sistema Nacional NFS-e
(Sefin/ADN) authenticates each request with the **issuing company's own A1
e-CNPJ certificate over mTLS** — so credentials are **per user**, uploaded
through the app's fiscal onboarding, not set as a server env var.

**Platform-level config** (environment endpoints — already have safe defaults in
`config/afilifacil.php`; only override to switch environments):

```env
# Point at RESTRICTED PRODUCTION first for homologation, then real production.
NFSE_SEFIN_URL=https://sefin.producaorestrita.nfse.gov.br/SefinNacional
NFSE_ADN_URL=https://adn.producaorestrita.nfse.gov.br/contribuintes
NFSE_DANFSE_URL=https://adn.producaorestrita.nfse.gov.br/danfse
NFSE_PARAM_URL=https://adn.producaorestrita.nfse.gov.br/parametrizacao
NFSE_VER_APLIC=AfiliFacil-1.0
NFSE_HTTP_TIMEOUT=30
```

Pre-launch fiscal checklist (once the engine exists):

- [ ] Register AfiliFacil / obtain access to the **Ambiente de Dados Nacional
      (ADN)** and the **Emissor Nacional** portal via **gov.br**
      (see [.ai/nfse/pesquisa.md](nfse/pesquisa.md) §2 for URLs & credentials flow)
- [ ] Confirm the destination municipality(ies) are live on the Padrão Nacional
      (`MunicipalityResolver` must find an active driver)
- [ ] Validate an end-to-end emission in **produção restrita** (restricted prod)
      with a real test certificate before switching `NFSE_*` to the production URLs
- [ ] Ensure the **certificate vault** is configured: PFX files are encrypted by
      the app (envelope keyed off `APP_KEY`) **before** hitting S3 — so `APP_KEY`
      and the S3 bucket ([§5](#5-aws-s3)) must be stable and backed up
- [ ] Confirm the **fiscal onboarding gate** (`EnsureFiscalProfileComplete`) is
      active so users can't issue without a complete, certificate-backed profile

**Per-user (runtime, not deploy):** each customer uploads their A1 e-CNPJ
certificate + password and completes their fiscal profile in `Settings/Fiscal`.

---

## 9. Email sending

Transactional email delivers invoice-ready notifications, ZIP-download links,
and auth emails (verification, password reset).

1. [ ] Verify your sending **domain** with the provider (SES/Postmark/Mailgun…)
   and publish the **SPF, DKIM, and DMARC** DNS records ([§1](#1-domain--dns)).
2. [ ] (SES) Request **production access** to leave the sandbox, or verify
   recipients while testing.
3. [ ] Set env vars (SMTP example):

   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=<provider-smtp-host>
   MAIL_PORT=587
   MAIL_USERNAME=<smtp-user>
   MAIL_PASSWORD=<smtp-pass>
   MAIL_FROM_ADDRESS=noreply@afilifacil.com.br
   MAIL_FROM_NAME="AfiliFacil"
   ```

4. [ ] Send a test: `php artisan tinker` →
   `Mail::raw('deploy test', fn($m) => $m->to('you@example.com')->subject('AfiliFacil'));`
5. [ ] Confirm the `MAIL_FROM_ADDRESS` domain matches the DKIM-signed domain, or
   mail lands in spam.

---

## 10. Stripe (billing)

Plans: `free` (5 NF), `basic` (R$39,90 / 50 NF), `advanced` (R$169,90 /
unlimited) — see `config/plans.php`. Billing runs on Laravel Cashier.

1. [ ] In the Stripe **live** dashboard, create the recurring **Products/Prices**
   for Basic and Advanced; copy each `price_…` ID.
2. [ ] Set env vars (use **live** keys, `sk_live_…` / `pk_live_…`):

   ```env
   STRIPE_KEY=pk_live_...
   STRIPE_SECRET=sk_live_...
   STRIPE_WEBHOOK_SECRET=whsec_...
   STRIPE_PRICE_BASIC=price_...
   STRIPE_PRICE_ADVANCED=price_...
   ```

3. [ ] Create a **webhook endpoint** in Stripe pointing to
   `https://afilifacil.com.br/stripe/webhook` and subscribe to the Cashier
   events (`customer.subscription.*`, `invoice.*`, `checkout.session.completed`,
   …). Copy its signing secret into `STRIPE_WEBHOOK_SECRET`.
4. [ ] The webhook route is **CSRF-exempt** by design; if Stripe reports
   signature failures, the `STRIPE_WEBHOOK_SECRET` is wrong.
5. [ ] Test a live checkout with a real card (or Stripe test clock in test mode
   first) and confirm the subscription + plan limit apply.

---

## 11. Go-live verification

After the first successful deploy, walk the happy path end-to-end:

- [ ] `https://afilifacil.com.br/up` returns **200** (Laravel health check)
- [ ] Landing page (`/`) loads over HTTPS with built assets (no Vite dev server)
- [ ] Register a user → email verification arrives ([§9](#9-email-sending))
- [ ] `/horizon` reachable by a gated admin, supervisors **active** ([§6](#6-queue-worker-horizon))
- [ ] Upload a sample Shopee CSV → import parses & validates (queue is working)
- [ ] Generate invoices → files land in S3, ZIP downloads via presigned URL
- [ ] Subscribe to a paid plan → Stripe checkout completes, webhook fires,
      limit updates ([§10](#10-stripe-billing))
- [ ] `php artisan schedule:list` shows the monthly usage reset ([§7](#7-scheduler-cron))
- [ ] `php artisan queue:failed` is empty
- [ ] Logs clean: `APP_DEBUG=false`, no stack traces leaking

---

## 12. Redeploy routine

For every release after the first, a push to `main` (Quick Deploy) runs the
[§2 deploy script](#2-laravel-forge-site), which:

1. Pulls code, installs prod deps, rebuilds assets
2. Runs `migrate --force` (under a lock)
3. Rebuilds `config`/`route`/`view`/`event` caches
4. Runs `horizon:terminate` so workers restart on new code

Manual redeploy / cache refresh if needed:

```bash
php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache
php artisan horizon:terminate
```

> After **any** `.env` change, re-run `config:cache` (cached config ignores new
> `.env` values) and `horizon:terminate`.

---

## Quick reference — credentials to have on hand

| Area | Where set | Key items |
|------|-----------|-----------|
| Domain/DNS | Registrar | A/CNAME records, SPF/DKIM/DMARC |
| Server/site | Laravel Forge | site, TLS, DB, deploy script, daemon, scheduler |
| App | `.env` (Forge) | `APP_KEY`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` |
| Storage | AWS + `.env` | `AWS_*`, `FILESYSTEM_DISK=s3` |
| Queue | Forge daemon | `php artisan horizon` + gate emails |
| Fiscal | `.env` + per-user | `NFSE_*` endpoints; certificates uploaded by users |
| Email | Provider + `.env` | `MAIL_*`, verified domain |
| Billing | Stripe + `.env` | `STRIPE_*`, webhook to `/stripe/webhook` |

---

**Related docs:** [OPERATIONS.md](OPERATIONS.md) (env var reference,
troubleshooting) · [architecture.md](architecture.md) (data flow) ·
[nfse/arquitetura.md](nfse/arquitetura.md) (fiscal engine) ·
[backlog.md](backlog.md) (what's still pending).
