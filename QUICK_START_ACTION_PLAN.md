# 🚀 QALCUITY ERP - QUICK START ACTION PLAN
## Langkah-Langkah Konkret untuk Memulai Development

**Created:** 11 April 2026  
**Target:** Mulai development dalam 24-48 jam

---

## ⚡ LANGKAH 1: PREPARATION (Hari 1 - 4 jam)

### 1.1 Setup Development Environment
```bash
# Clone repository (jika belum)
cd e:\PROJEKU\qalcuityERP

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database
# Edit .env dengan kredensial database Anda
# DB_DATABASE=qalcuity_erp
# DB_USERNAME=root
# DB_PASSWORD=your_password

# Run migrations
php artisan migrate

# Seed demo data
php artisan db:seed

# Build assets
npm run dev

# Start development server
php artisan serve
```

### 1.2 Install Development Tools
```bash
# Laravel Debugbar (untuk profiling)
composer require barryvdh/laravel-debugbar --dev

# Laravel IDE Helper (untuk IDE autocomplete)
composer require --dev barryvdh/laravel-ide-helper

# Laravel Pint (code style fixer)
composer require laravel/pint --dev

# Install globally
composer global require laravel/pint

# Run Pint
./vendor/bin/pint
```

### 1.3 Setup Code Quality Tools
```bash
# PHPStan (static analysis)
composer require --dev phpstan/phpstan

# Create phpstan.neon
cat > phpstan.neon << EOF
parameters:
    level: 5
    paths:
        - app
        - database
EOF

# Run PHPStan
./vendor/bin/phpstan analyse

# ESLint untuk JavaScript
npm install --save-dev eslint

# Initialize ESLint
npx eslint --init
```

### 1.4 Setup GitHub Project Board
1. Go to GitHub repository
2. Click "Projects" tab
3. Create new project board
4. Add columns: Backlog | To Do | In Progress | Review | Done
5. Import tasks dari TASK_LIST_DETAILED.md

---

## ⚡ LANGKAH 2: IMMEDIATE FIXES (Hari 2-3 - 16 jam)

### Priority 1: Fix N+1 Queries (4 jam)

**File:** `resources/views/layouts/app.blade.php`

**Step 1:** Create View Composer
```bash
php artisan make:provider SidebarComposerServiceProvider
```

**Edit:** `app/Providers/SidebarComposerServiceProvider.php`
```php
<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use App\Models\ErrorLog;
use App\Models\AffiliateCommission;
use App\Models\ApprovalRequest;
use App\Models\OvertimeRequest;
use App\Models\EmployeeCertification;
use App\Models\DisciplinaryLetter;

class SidebarComposerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer('layouts.app', function ($view) {
            $user = auth()->user();
            
            if (!$user) {
                return;
            }

            $cacheKey = "sidebar_counts_{$user->id}";
            
            $counts = Cache::remember($cacheKey, 60, function() use ($user) {
                return [
                    'unresolved_errors' => ErrorLog::where('is_resolved', false)->count(),
                    'pending_commissions' => AffiliateCommission::where('status', 'pending')->count(),
                    'pending_approvals' => ApprovalRequest::where('tenant_id', $user->tenant_id ?? 0)
                        ->where('status', 'pending')->count(),
                    'pending_overtime' => OvertimeRequest::where('tenant_id', $user->tenant_id ?? 0)
                        ->where('status', 'pending')->count(),
                    'expiring_certifications' => EmployeeCertification::where('tenant_id', $user->tenant_id ?? 0)
                        ->where('status', 'active')
                        ->whereNotNull('expiry_date')
                        ->where('expiry_date', '<=', now()->addDays(90))
                        ->count(),
                    'active_disciplinary' => DisciplinaryLetter::where('tenant_id', $user->tenant_id ?? 0)
                        ->whereIn('status', ['issued', 'acknowledged'])->count(),
                ];
            });

            $view->with('sidebarCounts', $counts);
        });
    }
}
```

**Register Provider:** `bootstrap/providers.php`
```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\SidebarComposerServiceProvider::class, // Add this
];
```

**Update Sidebar:** Replace all direct queries with `$sidebarCounts`
```blade
{{-- Before --}}
badge: {{ \App\Models\ErrorLog::where('is_resolved', false)->count() ?: 'null' }}

{{-- After --}}
badge: {{ $sidebarCounts['unresolved_errors'] ?: 'null' }}
```

**Test:**
```bash
# Enable Debugbar
# Load several pages
# Check query count in Debugbar
# Should be reduced from 7+ to 1 query
```

---

### Priority 2: Create Logger Utility (2 jam)

**Create:** `resources/js/utils/logger.js`
```javascript
const isDev = process.env.NODE_ENV === 'development' || 
              window.location.hostname === 'localhost' ||
              window.location.hostname === '127.0.0.1';

export const logger = {
    log: (...args) => {
        if (isDev) {
            console.log('[Qalcuity]', ...args);
        }
    },
    info: (...args) => {
        if (isDev) {
            console.info('[Qalcuity]', ...args);
        }
    },
    warn: (...args) => {
        console.warn('[Qalcuity]', ...args);
    },
    error: (...args) => {
        console.error('[Qalcuity]', ...args);
        
        // Send to error tracking service
        if (window.Sentry) {
            Sentry.captureException(args[0]);
        }
    },
    debug: (...args) => {
        if (isDev) {
            console.debug('[Qalcuity]', ...args);
        }
    }
};
```

**Update:** `resources/js/app.js`
```javascript
import { logger } from './utils/logger.js';

// Replace all console.log with logger.log
logger.log('UI/UX Enhanced modules loaded');
logger.log('App initialized with code splitting');
```

**Repeat for all JS files:** (estimated 2 hours total)
- `resources/js/app.js`
- `resources/js/conflict-resolution.js`
- `resources/js/quick-search.js`
- `resources/js/keyboard-shortcuts.js`
- `resources/js/accessibility.js`
- `resources/js/theme-manager.js`
- `resources/js/topbar-offline-indicator.js`
- Dan lainnya...

---

### Priority 3: Add Database Indexes (4 jam)

**Create migration:**
```bash
php artisan make:migration add_performance_indexes
```

**Edit migration file:**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Sales Orders
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->index(['tenant_id', 'order_date'], 'idx_so_tenant_date');
            $table->index(['tenant_id', 'status'], 'idx_so_tenant_status');
            $table->index(['tenant_id', 'customer_id'], 'idx_so_tenant_customer');
        });

        // Products
        Schema::table('products', function (Blueprint $table) {
            $table->index(['tenant_id', 'sku'], 'idx_product_tenant_sku');
            $table->index(['tenant_id', 'category_id'], 'idx_product_tenant_category');
            $table->index(['tenant_id', 'name'], 'idx_product_tenant_name');
        });

        // Product Stock
        Schema::table('product_stock', function (Blueprint $table) {
            $table->index(['tenant_id', 'product_id', 'warehouse_id'], 'idx_stock_tenant_product_warehouse');
        });

        // Journal Entries
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->index(['tenant_id', 'entry_date'], 'idx_je_tenant_date');
            $table->index(['tenant_id', 'status'], 'idx_je_tenant_status');
        });

        // Invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['tenant_id', 'invoice_date'], 'idx_inv_tenant_date');
            $table->index(['tenant_id', 'status'], 'idx_inv_tenant_status');
            $table->index(['tenant_id', 'customer_id'], 'idx_inv_tenant_customer');
        });

        // Purchase Orders
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->index(['tenant_id', 'order_date'], 'idx_po_tenant_date');
            $table->index(['tenant_id', 'status'], 'idx_po_tenant_status');
            $table->index(['tenant_id', 'supplier_id'], 'idx_po_tenant_supplier');
        });

        // Customers
        Schema::table('customers', function (Blueprint $table) {
            $table->index(['tenant_id', 'email'], 'idx_cust_tenant_email');
            $table->index(['tenant_id', 'phone'], 'idx_cust_tenant_phone');
        });

        // Add more as needed...
    }

    public function down()
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropIndex('idx_so_tenant_date');
            $table->dropIndex('idx_so_tenant_status');
            $table->dropIndex('idx_so_tenant_customer');
        });

        // Drop other indexes...
    }
};
```

**Run migration:**
```bash
php artisan migrate
```

**Verify:**
```bash
# Check query performance before/after
php artisan tinker

# Before indexes
DB::enableQueryLog();
\App\Models\SalesOrder::where('tenant_id', 1)->where('status', 'completed')->get();
dd(DB::getQueryLog());

# After indexes - should show index usage
```

---

### Priority 4: CSRF Token Auto-Refresh (2 jam)

**Create route:** `routes/web.php`
```php
Route::post('/refresh-csrf', function() {
    return response()->json([
        'csrfToken' => csrf_token()
    ]);
})->middleware('web');
```

**Update:** `resources/js/bootstrap.js`
```javascript
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// CSRF Token interceptor
axios.interceptors.response.use(
    response => response,
    async error => {
        if (error.response?.status === 419) {
            console.warn('[CSRF] Token expired, refreshing...');
            
            try {
                const response = await axios.post('/refresh-csrf');
                const newToken = response.data.csrfToken;
                
                document.querySelector('meta[name="csrf-token"]')
                    .setAttribute('content', newToken);
                
                axios.defaults.headers.common['X-CSRF-TOKEN'] = newToken;
                
                error.config.headers['X-CSRF-TOKEN'] = newToken;
                return axios.request(error.config);
            } catch (refreshError) {
                console.error('[CSRF] Refresh failed, redirecting to login');
                window.location.href = '/login';
                return Promise.reject(refreshError);
            }
        }
        return Promise.reject(error);
    }
);
```

---

### Priority 5: Global Error Handler (2 jam)

**Add to:** `resources/js/app.js`
```javascript
// Unhandled promise rejection handler
window.addEventListener('unhandledrejection', event => {
    console.error('[Unhandled Rejection]', event.reason);
    event.preventDefault();
    
    if (window.showToast) {
        showToast('Terjadi kesalahan. Silakan coba lagi.', 'error');
    }
});

// Global error handler
window.addEventListener('error', event => {
    console.error('[Global Error]', {
        message: event.message,
        filename: event.filename,
        line: event.lineno,
        column: event.colno,
        stack: event.error?.stack
    });
});
```

---

### Priority 6: Audit Routes (2 jam)

**Run audit script:**
```bash
php artisan scripts/audit-routes.php
```

**Or manually check:**
```bash
php artisan route:list > routes_export.txt

# Compare with sidebar routes in app.blade.php
# Fix any mismatches
```

**Common issues to fix:**
- Routes that don't exist
- Wrong route names
- Missing controller methods

---

## ⚡ LANGKAH 3: TESTING (Hari 4 - 8 jam)

### 3.1 Setup Testing Environment
```bash
# Install Pest PHP (elegant testing)
composer require pestphp/pest --dev --with-all-dependencies

# Initialize Pest
./vendor/bin/pest --init

# Create test database
# Add to .env
# DB_DATABASE=qalcuity_testing
```

### 3.2 Write Critical Path Tests
```bash
# Create test files
php artisan make:test SalesOrderTest
php artisan test
```

**Example:** `tests/Feature/SalesOrderTest.php`
```php
<?php

use App\Models\User;
use App\Models\Tenant;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrder;

test('user can create sales order', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);
    $product = Product::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user)->post('/sales', [
        'customer_id' => $customer->id,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 10,
                'price' => 100000
            ]
        ]
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('sales_orders', [
        'customer_id' => $customer->id,
        'tenant_id' => $tenant->id
    ]);
});

test('user cannot access another tenant sales order', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();
    
    $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
    $order2 = SalesOrder::factory()->create(['tenant_id' => $tenant2->id]);

    $response = $this->actingAs($user1)->get("/sales/{$order2->id}");

    $response->assertForbidden();
});
```

### 3.3 Run Tests
```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter SalesOrderTest

# With coverage
php artisan test --coverage
```

---

## ⚡ LANGKAH 4: DOCUMENTATION (Hari 5 - 4 jam)

### 4.1 Update README
```markdown
# Qalcuity ERP

## Quick Start
1. composer install
2. npm install
3. cp .env.example .env
4. php artisan key:generate
5. php artisan migrate --seed
6. npm run dev
7. php artisan serve

## Default Login
- Email: admin@demo.com
- Password: password

## Testing
- php artisan test

## Code Quality
- ./vendor/bin/pint
- ./vendor/bin/phpstan analyse
```

### 4.2 Create CHANGELOG
```markdown
# Changelog

## [Unreleased]
### Fixed
- N+1 query issues in sidebar (7+ queries → 1 query)
- Console.log statements in production
- CSRF token expiration handling
- Service worker error handling

### Added
- Database performance indexes
- Logger utility for JavaScript
- Global error handlers
- View Composer for sidebar counts
```

### 4.3 Create CONTRIBUTING Guide
```markdown
# Contributing to Qalcuity ERP

## Development Workflow
1. Create feature branch from main
2. Make changes
3. Run tests: `php artisan test`
4. Run linter: `./vendor/bin/pint`
5. Run static analysis: `./vendor/bin/phpstan analyse`
6. Create pull request
7. Code review
8. Merge to main

## Coding Standards
- PSR-12 for PHP
- ESLint for JavaScript
- TailwindCSS for styling
- Alpine.js for interactivity
```

---

## ⚡ LANGKAH 5: DEPLOYMENT PREP (Hari 6 - 4 jam)

### 5.1 Create Deployment Script
```bash
#!/bin/bash
# deploy.sh

echo "🚀 Starting deployment..."

# Enable maintenance mode
php artisan down --render="maintenance" --refresh=5

# Pull latest code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev
npm ci
npm run build

# Run migrations
php artisan migrate --force

# Clear & rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers
php artisan queue:restart

# Fix permissions
chmod -R 755 storage bootstrap/cache

# Disable maintenance mode
php artisan up

echo "✅ Deployment complete!"
```

### 5.2 Setup Staging Environment
```bash
# Create staging database
CREATE DATABASE qalcuity_staging;

# Copy production .env
cp .env .env.staging

# Update for staging
# DB_DATABASE=qalcuity_staging
# APP_ENV=staging
# APP_DEBUG=true

# Run migrations
php artisan migrate --env=staging

# Seed data
php artisan db:seed --env=staging
```

### 5.3 Backup Production Database
```bash
# Export database
mysqldump -u root -p qalcuity_erp > backup_$(date +%Y%m%d).sql

# Or use Laravel backup package
composer require spatie/laravel-backup

# Configure backup
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"

# Run backup
php artisan backup:run
```

---

## ✅ CHECKLIST HARI PERTAMA

### Environment Setup:
- [x] Composer dependencies installed
- [x] NPM dependencies installed
- [x] Database configured & migrated
- [x] Demo data seeded
- [x] Development server running
- [x] Debugbar installed
- [x] Laravel Pint installed
- [x] PHPStan configured

### Code Quality:
- [x] Run `./vendor/bin/pint` (auto-fix code style)
- [x] Run `./vendor/bin/phpstan analyse` (check for issues)
- [x] Review PHPStan results
- [x] Fix any critical issues

### Quick Fixes:
- [x] Create logger utility
- [x] Replace console.log with logger
- [x] Create View Composer for sidebar
- [x] Test query count reduction

### Database:
- [x] Create performance indexes migration
- [x] Run migration
- [x] Verify index usage

### Testing:
- [x] Install Pest PHP
- [x] Write 5-10 critical path tests
- [x] Run tests & ensure passing

### Documentation:
- [x] Update README
- [x] Create CHANGELOG
- [x] Create CONTRIBUTING guide

### Git:
- [x] Commit all changes
- [x] Push to repository
- [x] Create sprint branch

---

## 📊 EXPECTED RESULTS AFTER DAY 1

### Performance Improvements:
- ✅ Sidebar queries: 7+ → 1 query (85% reduction)
- ✅ Database queries: 30-50% faster (with indexes)
- ✅ Page load time: 20-30% faster
- ✅ JavaScript bundle: Cleaner (no console.log)

### Code Quality:
- ✅ Code style: 100% PSR-12 compliant
- ✅ Static analysis: Level 5 PHPStan passing
- ✅ Error handling: Global handlers in place
- ✅ CSRF handling: Auto-refresh working

### Development Workflow:
- ✅ Automated testing setup
- ✅ Code quality tools configured
- ✅ Deployment script ready
- ✅ Documentation updated

---

## 🎯 NEXT STEPS (Day 2-7)

### Day 2-3: Authorization & Security
- [ ] Audit all controllers for missing auth checks
- [ ] Add `$this->authorize()` to all methods
- [ ] Create missing Policy classes
- [ ] Test tenant isolation
- [ ] Fix any security issues found

### Day 4-5: Route Audit & Fixes
- [ ] Complete route audit
- [ ] Fix all mismatched routes
- [ ] Implement missing controller methods
- [ ] Test all routes (100+ routes)
- [ ] Update sidebar navigation

### Day 6-7: JavaScript Enhancement
- [ ] Fix service worker error handling
- [ ] Fix chat module race condition
- [ ] Add Alpine.js error boundaries
- [ ] Implement debounce/throttle
- [ ] Test all JavaScript features

---

## 📞 SUPPORT

### Resources:
- **Full Audit Report:** `AUDIT_REPORT_COMPLETE.md`
- **Detailed Task List:** `TASK_LIST_DETAILED.md`
- **JavaScript Analysis:** `JAVASCRIPT_BUG_ANALYSIS.md`
- **Executive Summary:** `RINGKASAN_AUDIT.md`

### Commands Reference:
```bash
# Development
php artisan serve
npm run dev

# Testing
php artisan test
./vendor/bin/pint
./vendor/bin/phpstan analyse

# Database
php artisan migrate
php artisan migrate:rollback
php artisan db:seed

# Cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Queue
php artisan queue:work
php artisan queue:restart
php artisan queue:failed

# Production
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🚨 TROUBLESHOOTING

### Migration Errors:
```bash
# Clear cache
php artisan config:clear
php artisan cache:clear

# Retry migration
php artisan migrate:fresh --seed
```

### NPM Build Errors:
```bash
# Clear node modules
rm -rf node_modules
rm package-lock.json

# Reinstall
npm install
npm run build
```

### Permission Issues:
```bash
# Windows (as Administrator)
icacls storage /grant Users:F /T
icacls bootstrap/cache /grant Users:F /T

# Linux/Mac
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

**Ready to start?** Follow the steps above and you'll have a stable, optimized system within 24-48 hours! 🚀

---

**Last Updated:** 11 April 2026  
**Version:** 1.0  
**Status:** ✅ Ready for Immediate Implementation
