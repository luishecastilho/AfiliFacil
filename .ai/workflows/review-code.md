# Workflow: Review Code

Code review standards and checklist for AfiliFacil.

---

## Review Process

1. Read the PR description and linked task in `.ai/backlog.md`
2. Check `.ai/decisions.md` for architectural constraints
3. Walk through each changed file using the checklists below
4. Run locally: `composer test`, `./vendor/bin/pint --test`, `npm run build`
5. Leave actionable comments referencing specific files and lines

---

## Backend Review Checklist

### Architecture & Patterns

- [ ] Business logic is in `app/Actions/`, not controllers
- [ ] Controllers are thin (validate → action → redirect/render)
- [ ] Jobs dispatch from actions/listeners, not controllers (except `InvoiceController::generate`)
- [ ] DTOs used for data transfer between layers (no raw arrays in action signatures)
- [ ] PHP enums used for status fields (not string constants)
- [ ] New user-owned models have `#[ScopedBy([BelongsToUserScope::class])]`

### Jobs (`app/Jobs/`)

- [ ] Implements `ShouldQueue`
- [ ] `$tries` is set (typically 3–5)
- [ ] `$backoff()` returns exponential array
- [ ] `$queue` is set (`high`, `default`, or `low`)
- [ ] `$maxExceptions` set where appropriate
- [ ] `ShouldBeUnique` used for idempotent jobs (parse, validate, generate, zip)
- [ ] Dependencies injected via `handle()` method parameters, not constructor
- [ ] Failed job writes terminal state (updates model status, creates event)

Reference jobs:
- `ParseImportJob` — batch dispatch, `$queue = 'high'`
- `IssueInvoiceJob` — rate limiting, plan check, `$queue = 'default'`
- `GenerateZipJob` — `$queue = 'low'`

### Actions (`app/Actions/`)

- [ ] Single responsibility (one `handle()` method)
- [ ] Dependencies injected via constructor
- [ ] Returns typed values (model, collection, enum, DTO)
- [ ] No HTTP concerns (no `Request`, no `redirect()`)
- [ ] Bulk operations use `DB::table()->insertOrIgnore()` not Eloquent loops

### Models (`app/Models/`)

- [ ] `$fillable` defined (no `$guarded = []`)
- [ ] Enum casts in `casts()` method
- [ ] Relationships defined with return types
- [ ] Observers registered via `#[ObservedBy]` attribute if needed
- [ ] Factory exists in `database/factories/` for testability

### Policies (`app/Policies/`)

- [ ] Every controller method calling `$this->authorize()` has a matching policy method
- [ ] Ownership checked via `user_id` (direct or through relationship)
- [ ] Reference: `ImportPolicy`, `InvoicePolicy`, `SellerPolicy`

### Form Requests (`app/Http/Requests/`)

- [ ] `authorize()` checks policy
- [ ] Validation rules match model constraints
- [ ] File validation includes MIME type and max size from config

### Events & Listeners

- [ ] Events are simple data containers (model + optional metadata)
- [ ] Listeners handle one concern (notify, dispatch job, record event)
- [ ] Side effects not duplicated between observer and listener

### Security

- [ ] No raw SQL (use Eloquent or query builder)
- [ ] User input validated via Form Request
- [ ] Authorization checked before any data access
- [ ] No secrets in code (use `env()` / config files)
- [ ] File uploads validated server-side (not just extension)
- [ ] Mass assignment protected (`$fillable` whitelist)

### Database

- [ ] Migration has `up()` and `down()`
- [ ] Indexes on foreign keys and frequently queried columns
- [ ] Enum columns use string values matching PHP enums
- [ ] JSON columns have array casts on model

---

## Frontend Review Checklist

### Pages (`resources/js/Pages/`)

- [ ] Uses `AppLayout` (or appropriate layout)
- [ ] `<Head title="..." />` set for SEO
- [ ] Props destructured in function signature
- [ ] Mutations via Inertia `useForm()` (not raw axios)
- [ ] Loading states handled (`processing` from useForm)
- [ ] Error messages displayed from `errors` prop

### Components

- [ ] UI primitives from `@/Components/ui/*` (not custom styled divs)
- [ ] Uses `cn()` from `@/lib/cn` for conditional classes
- [ ] No inline styles (Tailwind classes only)
- [ ] `StatusBadge` used for status display with labels from `@/constants/statuses`
- [ ] Currency/dates formatted via `@/lib/formatters` (pt-BR)

### Hooks

- [ ] Polling hooks (`useImportPoller`, `useInvoicePoller`) cleaned up on unmount
- [ ] No memory leaks (clear intervals/timeouts in useEffect cleanup)

### Accessibility

- [ ] Form inputs have associated `<Label>`
- [ ] Buttons have descriptive text (not just icons)
- [ ] Tables use semantic `<TableHead>` / `<TableCell>`
- [ ] Color not the only indicator of status (labels accompany badges)

### Performance

- [ ] No unnecessary re-renders (stable dependencies in useEffect)
- [ ] Large lists paginated server-side (not client-side filtering of all rows)
- [ ] Inertia `router.reload({ only: [...] })` used for polling (not full page reload)

---

## Testing Review Checklist

- [ ] Tests exist for new/changed functionality
- [ ] `RefreshDatabase` trait used for DB tests
- [ ] Factories used (not hardcoded model creation)
- [ ] External services mocked (Storage, Queue, Provider, Mail)
- [ ] Both happy path and edge cases tested
- [ ] Authorization tested (unauthorized user gets 403)
- [ ] `composer test` passes

See `.ai/workflows/create-tests.md` for patterns.

---

## Configuration Review

- [ ] New env vars added to `.env.example`
- [ ] Config values use `env()` with sensible defaults in config file
- [ ] No `env()` calls outside config files (use `config()` in app code)
- [ ] Plan limits updated in `config/plans.php` if billing changed

---

## Documentation Review

- [ ] `.ai/backlog.md` updated (task marked complete, new items added)
- [ ] `.ai/decisions.md` updated if new architectural decision made
- [ ] `CLAUDE.md` updated if routes, commands, or priorities changed
- [ ] Assumptions stated explicitly when behavior is ambiguous

---

## Common Issues to Flag

| Issue | Example | Fix |
|-------|---------|-----|
| Logic in controller | `$import->update(['status' => 'done'])` in controller | Move to Action |
| Missing authorization | Route without `$this->authorize()` | Add policy check |
| Eloquent in bulk loop | `ImportRow::create()` in foreach | Use `insertOrIgnore()` batch |
| Hardcoded queue | `dispatch(new Job())` without queue | Set `$queue` on job class |
| Missing retry config | Job without `$tries` / `$backoff` | Add retry configuration |
| Raw status strings | `'status' => 'pending'` | Use `ImportStatus::Pending` |
| Unscoped query | `Import::all()` without user filter | Use scoped model or `where('user_id')` |
| Missing enum cast | Status stored as string, not cast | Add to `casts()` method |
| Frontend raw fetch | `axios.post('/imports')` | Use Inertia `useForm().post()` |
| Missing error display | Form without `{errors.field && ...}` | Show validation errors |
| English-only UI | Hardcoded English strings | Acceptable for now; note for i18n backlog |
| Debug code | `dd()`, `console.log()`, `dump()` | Remove before merge |

---

## Review Severity Guide

| Severity | Action | Examples |
|----------|--------|---------|
| **Block** | Must fix before merge | Missing authorization, SQL injection, secrets in code, broken tests |
| **Request changes** | Should fix, can discuss | Missing retry config, logic in controller, no tests |
| **Suggest** | Nice to have, non-blocking | Better variable naming, additional edge case test, docs update |
| **Nit** | Optional style preference | Import ordering, comment wording |

---

## Files to Always Check

When reviewing changes in these areas, pay extra attention:

| Area | Key Files |
|------|-----------|
| Import pipeline | `CreateImportAction`, `ParseImportJob`, `ValidateImportJob`, `ShopeeImporter` |
| Invoice pipeline | `GroupRowsForInvoicingAction`, `IssueInvoiceJob`, `IssueInvoiceAction`, `NullInvoiceProvider` |
| Billing | `SubscriptionService`, `BillingController`, `IssueInvoiceJob` (plan check) |
| Auth/Access | `ImportPolicy`, `InvoicePolicy`, `SellerPolicy`, `BelongsToUserScope` |
| Frontend pages | `Imports/Show.jsx`, `Invoices/Show.jsx`, `AppLayout.jsx` |
| Config | `config/afilifacil.php`, `config/plans.php`, `.env.example` |

---

## Post-Review

After approving:

1. Verify CI passes (when CI is set up)
2. Confirm migration runs cleanly: `php artisan migrate:fresh --seed`
3. Confirm frontend builds: `npm run build`
4. Update `.ai/backlog.md` if the PR completes a backlog item
