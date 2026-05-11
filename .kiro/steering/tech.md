# Tech Stack & Build System

## Core Stack

- **PHP 8.3+** with Laravel 13
- **MySQL 8.0+** (primary database)
- **Redis** (cache, sessions, queues — required in production)
- **Vite 8** (frontend build tool)
- **Tailwind CSS 3** with `@tailwindcss/forms` plugin
- **Alpine.js 3** (frontend interactivity)
- **Chart.js 4** (data visualization)

## Backend

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 13 |
| Auth | Laravel Breeze, Google OAuth (Socialite), 2FA (Google2FA) |
| Queue | Redis-backed (Supervisor in production) |
| PDF | barryvdh/laravel-dompdf |
| Excel | maatwebsite/excel |
| AI | google-gemini-php/client |
| Cloud Storage | AWS S3, Google Cloud Storage, Azure Blob |
| Push Notifications | minishlink/web-push |
| POS Printing | mike42/escpos-php |
| Barcode/QR | picqer/php-barcode-generator, bacon/bacon-qr-code |
| Payment | midtrans/midtrans-php |

## Frontend

| Layer | Technology |
|-------|-----------|
| Build | Vite 8 + laravel-vite-plugin |
| CSS | Tailwind CSS 3 + PostCSS + Autoprefixer |
| JS Framework | Alpine.js 3 (with @alpinejs/collapse) |
| Charts | Chart.js 4 |
| Markdown | marked |
| Sanitization | DOMPurify |
| HTTP | Axios |

## Vite Entry Points

- `resources/css/app.css` — Main stylesheet
- `resources/js/app.js` — Main application JS
- `resources/js/pos-app.js` — POS module
- `resources/js/chat.js` — AI chat interface
- `resources/js/offline-manager.js` — Offline/PWA support
- `resources/js/conflict-resolution.js` — Offline sync conflicts
- `resources/js/topbar-offline-indicator.js` — Offline status UI

## Testing

| Tool | Purpose |
|------|---------|
| PHPUnit 12 | Unit and Feature tests |
| Eris | Property-based testing |
| Mockery | Mocking |
| Laravel Pint | Code style (PSR-12) |

Test database: `qalcuity_erp_test` (MySQL)

## Common Commands

```bash
# Full setup (install deps, generate key, migrate, build assets)
composer run setup

# Development (runs server, queue, logs, vite concurrently)
composer run dev

# Run tests
composer run test
# Or directly:
php artisan test

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Code formatting
./vendor/bin/pint

# Build frontend for production
npm run build

# Build with increased memory (large bundles)
npm run build:memory

# Clear all caches
php artisan optimize:clear

# Rebuild all caches
php artisan optimize

# Run migrations
php artisan migrate

# Queue worker (development)
php artisan queue:listen

# Real-time log viewer
php artisan pail
```

## Environment

- Development: `APP_ENV=local`, cache/queue/session use `database`
- Testing: `APP_ENV=testing`, cache uses `array`, queue uses `sync`
- Production: `APP_ENV=production`, cache/queue/session use `redis`
