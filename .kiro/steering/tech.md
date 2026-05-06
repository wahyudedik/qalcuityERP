# Tech Stack

## Backend
- **PHP 8.3+** with **Laravel 13**
- **MySQL 8.0+** — primary database (utf8mb4_unicode_ci)
- **Redis** — required for production; used for cache, sessions, and queues
- **Queue** — Laravel queues (Redis driver in production, database driver in dev)
- **Cache** — Laravel cache (Redis); heavily used for settings, dashboard, AI responses

## Frontend
- **Blade** — server-side templating
- **Alpine.js 3** — lightweight reactivity (no Vue/React); `@alpinejs/collapse` plugin included
- **Tailwind CSS 3** (with `@tailwindcss/forms`)
- **Chart.js 4** — data visualizations
- **Vite 8** — asset bundling via `laravel-vite-plugin`
- **Axios** — HTTP client
- **marked + DOMPurify** — Markdown rendering in AI chat

## Key Libraries
| Package | Purpose |
|---|---|
| `google-gemini-php/client` | AI assistant (Gemini) |
| `maatwebsite/excel` | Excel import/export |
| `barryvdh/laravel-dompdf` | PDF generation |
| `laravel/socialite` | Google OAuth |
| `midtrans/midtrans-php` | Payment gateway |
| `bacon/bacon-qr-code` | QR code generation |
| `picqer/php-barcode-generator` | Barcode generation |
| `minishlink/web-push` | Browser push notifications |
| `pragmarx/google2fa-laravel` | Two-factor authentication |
| `phpoffice/phpword` | Word document generation |
| `mike42/escpos-php` | ESC/POS thermal printer support |
| `aws/aws-sdk-php` | AWS S3/cloud storage |
| `google/cloud-storage` | Google Cloud Storage |
| `microsoft/azure-storage-blob` | Azure Blob Storage |
| `giorgiosironi/eris` | Property-based testing (dev) |
| `laravel/pint` | PHP code style fixer (dev) |
| `laravel/pail` | Real-time log tailing in terminal (dev) |
| `laravel/breeze` | Auth scaffolding (dev) |
| `barryvdh/laravel-debugbar` | Debug toolbar (dev) |

## Common Commands

```bash
# Initial setup
composer run setup

# Development (starts server + queue + logs + vite concurrently)
composer run dev

# Run tests
composer run test
# or
php artisan test

# Asset build
npm run build

# Asset build (if memory issues)
npm run build:memory

# Code style fix
./vendor/bin/pint

# Clear config cache
php artisan config:clear

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed
```

## Environment Notes
- `APP_ENV=local` enables sourcemaps, disables minification, skips compressed size reporting
- `APP_DEBUG=true` keeps `console.log` in JS builds
- `VITE_PORT` — configures Vite dev server port (default 5173)
- Vite HMR uses `APP_URL` hostname for WebSocket host
- Redis is **required** for production (cache, sessions, queues); database driver is acceptable for local dev
- All third-party API keys (Gemini, OAuth, payment gateways, etc.) are managed via the SuperAdmin settings panel, not hardcoded in `.env`

## Build Notes
- JS chunks are split: `vendor-alpine`, `vendor-charts`, `vendor`, per-module chunks, feature chunks (offline, notifications, POS)
- Service worker (`sw.js`) is copied to `public/` via `vite-plugin-static-copy`
- Use `npm run build:analyze` to inspect bundle sizes
