# Project Structure

## Top-Level Layout
```
app/                  # PHP application code
config/               # Laravel config files
database/
  migrations/         # Database migrations
  seeders/            # Seeders (DatabaseSeeder, SuperAdminSeeder, TenantDemoSeeder)
  factories/          # Model factories
resources/
  views/              # Blade templates
  js/                 # Frontend JavaScript
  css/                # Stylesheets (Tailwind)
  lang/               # Translations
routes/
  web.php             # Main web routes
  api.php             # API routes
  auth.php            # Auth routes
  console.php         # Artisan console routes / scheduled commands
  healthcare.php      # Healthcare module routes
tests/                # PHPUnit tests
.kiro/specs/          # Kiro spec files (requirements, design, tasks)
```

## App Directory
```
app/
  Console/Commands/   # Artisan commands
  DTOs/               # Data Transfer Objects
  Events/             # Laravel events
  Exceptions/         # Custom exceptions
  Exports/            # Maatwebsite Excel export classes
  Http/
    Controllers/      # Controllers (see below)
    Middleware/        # HTTP middleware
    Requests/         # Form request validation classes
  Imports/            # Maatwebsite Excel import classes
  Jobs/               # Queued jobs
  Listeners/          # Event listeners
  Models/             # Eloquent models (~529 models)
  Notifications/      # Laravel notification classes
  Observers/          # Eloquent observers
  Policies/           # Authorization policies
  Providers/          # Service providers
  Rules/              # Custom validation rules
  Services/           # Business logic service classes
  Traits/             # Reusable model traits
  View/               # View composers / components
```

## Controllers Organization
Controllers are grouped by domain under `app/Http/Controllers/`:
- Top-level controllers for core modules (e.g. `AccountingController`, `InventoryController`, `HrmController`)
- Subdirectories for complex domains: `Admin/`, `AI/`, `Analytics/`, `Api/`, `Auth/`, `Healthcare/`, `Telecom/`, `SuperAdmin/`, etc.
- AI-specific controllers follow the pattern `{Module}AiController` (e.g. `AccountingAiController`, `SalesAiController`)

## Services Organization
`app/Services/` contains business logic, organized as:
- Flat service files for core features (e.g. `GeminiService`, `JournalService`, `PayrollCalculationService`)
- Subdirectories for complex domains: `AI/`, `Agent/`, `DemoData/`, `ERP/`, `Fisheries/`, `Healthcare/`, `Integrations/`, `Manufacturing/`, `Marketplace/`, `MultiCompany/`, `Security/`, `Telecom/`
- AI services follow the pattern `{Module}AiService` (e.g. `AccountingAiService`, `SalesAiService`, `HrmAiService`)

## Views Organization
`resources/views/` mirrors the module structure — one folder per domain (e.g. `accounting/`, `inventory/`, `hrm/`, `pos/`). Shared elements live in:
- `layouts/` — base layout templates
- `components/` — reusable Blade components
- `partials/` — shared partial views
- `pdf/` — PDF export templates

## Frontend JavaScript
`resources/js/`:
- `app.js` — main entry point
- `modules/` — per-feature JS modules (lazy-loaded)
- `chunks/` — code-split chunks
- Feature files: `chat.js`, `offline-manager.js`, `offline-pos.js`, `push-notification.js`, `sw.js` (service worker)

## Key Traits (apply to Models)
- `BelongsToTenant` — adds global scope to auto-filter queries by `tenant_id`; auto-sets `tenant_id` on create. **Use on every tenant-scoped model.**
- `AuditsChanges` — auto-logs created/updated/deleted events to `ActivityLog`
- `CacheableModel` — model-level caching helpers (also aliased as `CachableModel`)
- `DispatchesWebhooks` — fires webhook events on model changes
- `Filterable` — adds reusable query filter scopes
- `HasTransactionIsolation` — enforces DB transaction isolation for financial operations

## Multi-Tenancy Rules
- Every tenant-scoped model **must** use the `BelongsToTenant` trait
- `tenant_id` is the isolation key — never query tenant data without it
- SuperAdmin users bypass tenant scoping (checked via `$user->isSuperAdmin()`)
- `EnforceTenantIsolation` middleware validates route model bindings against the authenticated user's `tenant_id`
- Module access is gated by `CheckModulePlanAccess` middleware using `PlanModuleMap`
- Use `Model::withoutTenantScope()` or `Model::forTenant($id)` only in admin/superadmin contexts

## Naming Conventions
- Controllers: `PascalCase` + `Controller` suffix
- Services: `PascalCase` + `Service` suffix
- Jobs: descriptive verb phrase (e.g. `ProcessChatMessage`, `GenerateAiInsights`)
- Models: singular `PascalCase`
- Migrations: Laravel standard snake_case timestamps
- Blade views: `snake_case.blade.php` inside domain folder
- Routes: kebab-case slugs

## Configuration Files of Note
- `config/gemini.php` — Gemini AI settings
- `config/brand.php` — branding/white-label config
- `config/security.php` — security settings
- `config/healthcare.php` — healthcare module config
- `config/bank_formats.php` — bank statement format definitions
- `config/dashboard-templates.php` — dashboard widget templates
- `config/audit.php` — audit trail settings
- `config/data_retention.php` — data retention policies
- `config/pos_printer.php` — POS thermal printer config
- `config/iot_hardware.php` — IoT/hardware integration config
- `config/database_transactions.php` — DB transaction isolation settings
- `config/migration.php` — migration performance options
