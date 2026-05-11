# Project Structure

## Top-Level Layout

```
├── app/                  # Application code (PSR-4: App\)
├── bootstrap/            # Framework bootstrap
├── config/               # Configuration files
├── database/             # Migrations, seeders, factories
├── docs/                 # Documentation
├── public/               # Web root (index.php, compiled assets)
├── resources/            # Frontend source (views, CSS, JS, lang)
├── routes/               # Route definitions
├── scripts/              # Utility scripts
├── storage/              # Logs, cache, compiled views, uploads
├── tests/                # Test suites
└── vendor/               # Composer dependencies
```

## Application Layer (`app/`)

```
app/
├── Console/Commands/     # Artisan commands
├── Contracts/            # Interfaces (AiProvider, Widget, etc.)
├── DTOs/                 # Data Transfer Objects
├── Enums/                # PHP enums
├── Events/               # Event classes
├── Exceptions/           # Custom exception handlers
├── Exports/              # Excel export classes (Maatwebsite)
├── Helpers/              # Helper utilities
├── Http/
│   ├── Controllers/      # Route controllers (grouped by domain)
│   ├── Middleware/        # HTTP middleware
│   └── Requests/         # Form request validation
├── Imports/              # Excel import classes
├── Jobs/                 # Queue jobs
├── Listeners/            # Event listeners
├── Mail/                 # Mailable classes
├── Models/               # Eloquent models (~550 models)
├── Notifications/        # Notification classes
├── Observers/            # Model observers
├── Policies/             # Authorization policies
├── Providers/            # Service providers
├── Rules/                # Custom validation rules
├── Services/             # Business logic services
├── Traits/               # Reusable traits
├── View/                 # View composers/components
├── Widgets/              # Dashboard widget classes
└── helpers.php           # Global helper functions
```

## Controllers Organization

Controllers are grouped by domain in subdirectories:

- `Admin/` — Admin panel controllers
- `AI/` — AI-related endpoints
- `Analytics/` — Reporting and analytics
- `Api/` — API controllers
- `Auth/` — Authentication
- `Healthcare/`, `Hotel/`, `Telecom/`, `Construction/`, `Cosmetic/`, `Fisheries/`, `Fnb/`, `Livestock/`, `Manufacturing/`, `TourTravel/`, `Printing/` — Industry modules
- `Hrm/`, `Inventory/`, `Pos/`, `Marketplace/`, `SuperAdmin/` — Core module controllers

General-purpose controllers sit at the root of `Controllers/`.

## Services Organization

Services contain business logic, separated from controllers:

- `Agent/` — AI agent orchestration
- `AI/` — AI provider abstraction
- `Audit/` — Audit trail services
- `DemoData/` — Sample data generation
- `ERP/` — Core ERP business logic
- `Healthcare/`, `Fisheries/`, `Manufacturing/`, `Telecom/` — Industry services
- `Integrations/` — Third-party integration services
- `Layout/` — UI layout services
- `Marketplace/` — E-commerce marketplace sync
- `MultiCompany/` — Multi-company consolidation
- `Security/` — Security and access control
- `Widget/` — Dashboard widget services

## Frontend (`resources/`)

```
resources/
├── css/app.css           # Main Tailwind stylesheet
├── js/
│   ├── app.js            # Main application entry
│   ├── pos-app.js        # POS module entry
│   ├── chat.js           # AI chat entry
│   ├── offline-manager.js
│   ├── sw.js             # Service worker
│   └── modules/          # Lazy-loaded JS modules
├── lang/                 # Localization files
└── views/                # Blade templates
    ├── layouts/          # Base layouts
    ├── components/       # Reusable Blade components
    ├── partials/         # Shared partials
    └── [module]/         # Module-specific views
```

## Routes

- `routes/web.php` — Main web routes
- `routes/api.php` — API routes
- `routes/auth.php` — Authentication routes (Breeze)
- `routes/healthcare.php` — Healthcare module routes
- `routes/console.php` — Console/scheduler routes

## Tests

```
tests/
├── Unit/                 # Unit tests
├── Feature/              # Feature/integration tests
├── Property/             # Property-based tests (Eris)
└── TestCase.php          # Base test class
```

## Key Architectural Patterns

1. **Service Layer** — Business logic in `app/Services/`, controllers are thin
2. **Multi-Tenant Isolation** — Tenant scoping via middleware and model traits
3. **Domain Grouping** — Controllers, services, views, routes grouped by business domain
4. **Blade Components** — Reusable UI in `resources/views/components/`
5. **Queue Jobs** — Heavy processing dispatched to Redis queues
6. **Observer Pattern** — Model observers for side effects
7. **DTOs** — Data transfer objects for complex data passing
8. **Contracts** — Interfaces for swappable implementations
