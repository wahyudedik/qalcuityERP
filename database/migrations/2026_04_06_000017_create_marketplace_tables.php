<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Marketplace Apps (Third-Party Plugins)
        if (!Schema::hasTable('marketplace_apps')) {
            Schema::create('marketplace_apps', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('version');
                $table->foreignId('developer_id')->constrained('users')->onDelete('cascade');
                $table->string('category'); // accounting, hr, inventory, crm, etc.
                $table->json('screenshots')->nullable();
                $table->string('icon_url')->nullable();
                $table->decimal('price', 10, 2)->default(0.00); // 0 = free
                $table->string('pricing_model')->default('one_time'); // one_time, subscription, freemium
                $table->decimal('subscription_price', 10, 2)->nullable();
                $table->string('subscription_period')->nullable(); // monthly, yearly
                $table->json('features')->nullable();
                $table->json('requirements')->nullable(); // PHP version, Laravel version, etc.
                $table->string('status')->default('pending'); // pending, approved, rejected, published
                $table->text('rejection_reason')->nullable();
                $table->integer('download_count')->default(0);
                $table->decimal('rating', 3, 2)->default(0.00);
                $table->integer('review_count')->default(0);
                $table->string('documentation_url')->nullable();
                $table->string('support_url')->nullable();
                $table->string('repository_url')->nullable();
                $table->timestamps();
                $table->timestamp('published_at')->nullable();
    
                $table->index(['category', 'status']);
                $table->index('developer_id');
                $table->index('rating');
            });
        }

        // 2. App Installations
        if (!Schema::hasTable('app_installations')) {
            Schema::create('app_installations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('marketplace_app_id')->constrained()->onDelete('cascade');
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('installation_id')->unique(); // Unique per installation
                $table->string('status')->default('active'); // active, inactive, uninstalled
                $table->json('configuration')->nullable(); // App-specific settings
                $table->json('permissions')->nullable(); // Granted permissions
                $table->date('installed_at');
                $table->date('expires_at')->nullable(); // For subscriptions
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamps();
    
                $table->unique(['marketplace_app_id', 'tenant_id']);
                $table->index(['tenant_id', 'status']);
            });
        }

        // 3. App Reviews & Ratings
        if (!Schema::hasTable('app_reviews')) {
            Schema::create('app_reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('marketplace_app_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->tinyInteger('rating'); // 1-5
                $table->text('review')->nullable();
                $table->json('pros')->nullable();
                $table->json('cons')->nullable();
                $table->boolean('verified_purchase')->default(false);
                $table->boolean('is_approved')->default(false);
                $table->timestamps();
    
                $table->unique(['marketplace_app_id', 'user_id']);
                $table->index('rating');
            });
        }

        // 4. Developer Accounts
        if (!Schema::hasTable('developer_accounts')) {
            Schema::create('developer_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('company_name')->nullable();
                $table->text('bio')->nullable();
                $table->string('website')->nullable();
                $table->string('github_profile')->nullable();
                $table->json('skills')->nullable(); // ['laravel', 'vue', 'api']
                $table->decimal('total_earnings', 12, 2)->default(0.00);
                $table->decimal('available_balance', 12, 2)->default(0.00);
                $table->string('payout_method')->nullable(); // bank_transfer, paypal
                $table->json('payout_details')->nullable();
                $table->string('status')->default('active'); // active, suspended
                $table->timestamps();
    
                $table->unique('user_id');
            });
        }

        // 5. Developer Earnings & Payouts
        if (!Schema::hasTable('developer_earnings')) {
            Schema::create('developer_earnings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('developer_account_id')->constrained()->onDelete('cascade');
                $table->foreignId('marketplace_app_id')->constrained()->onDelete('cascade');
                $table->foreignId('installation_id')->nullable()->constrained('app_installations')->onDelete('set null');
                $table->decimal('amount', 10, 2);
                $table->decimal('platform_fee', 10, 2); // Platform commission
                $table->decimal('net_earning', 10, 2);
                $table->string('currency')->default('IDR');
                $table->string('type'); // sale, subscription, renewal
                $table->date('earned_date');
                $table->string('status')->default('pending'); // pending, paid, refunded
                $table->timestamps();
    
                $table->index(['developer_account_id', 'status']);
                $table->index('earned_date');
            });
        }

        if (!Schema::hasTable('developer_payouts')) {
            Schema::create('developer_payouts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('developer_account_id')->constrained()->onDelete('cascade');
                $table->decimal('amount', 12, 2);
                $table->string('currency')->default('IDR');
                $table->string('status')->default('pending'); // pending, processing, completed, failed
                $table->string('payout_method');
                $table->json('payout_details');
                $table->text('reference_number')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->text('failure_reason')->nullable();
                $table->timestamps();
    
                $table->index(['developer_account_id', 'status']);
            });
        }

        // 6. Custom Modules (Module Builder)
        if (!Schema::hasTable('custom_modules')) {
            Schema::create('custom_modules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('version')->default('1.0.0');
                $table->json('schema'); // Module structure (fields, relationships)
                $table->json('ui_config')->nullable(); // UI configuration
                $table->json('permissions')->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by_user_id')->constrained('users')->onDelete('cascade');
                $table->timestamps();
    
                $table->index(['tenant_id', 'is_active']);
            });
        }

        if (!Schema::hasTable('custom_module_records')) {
            Schema::create('custom_module_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('custom_module_id')->constrained()->onDelete('cascade');
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->json('data'); // Dynamic field data
                $table->foreignId('created_by_user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
    
                $table->index('custom_module_id');
            });
        }

        // 7. Themes & Customizations
        if (!Schema::hasTable('themes')) {
            Schema::create('themes', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('version');
                $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
                $table->string('preview_image')->nullable();
                $table->json('screenshots')->nullable();
                $table->decimal('price', 10, 2)->default(0.00);
                $table->string('status')->default('pending'); // pending, approved, published
                $table->integer('install_count')->default(0);
                $table->decimal('rating', 3, 2)->default(0.00);
                $table->timestamps();
                $table->timestamp('published_at')->nullable();
    
                $table->index(['status', 'published_at']);
            });
        }

        if (!Schema::hasTable('theme_installations')) {
            Schema::create('theme_installations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('theme_id')->constrained()->onDelete('cascade');
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->boolean('is_active')->default(false);
                $table->json('customizations')->nullable(); // User customizations
                $table->timestamps();
    
                $table->unique(['theme_id', 'tenant_id']);
            });
        }

        // 8. API Keys & Monetization
        if (!Schema::hasTable('api_keys')) {
            Schema::create('api_keys', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('key')->unique();
                $table->string('name');
                $table->json('permissions')->nullable(); // Allowed endpoints
                $table->integer('rate_limit')->default(1000); // Requests per hour
                $table->integer('requests_used')->default(0);
                $table->date('expires_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'is_active']);
                $table->index('key');
            });
        }

        if (!Schema::hasTable('api_usage_logs')) {
            Schema::create('api_usage_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('api_key_id')->constrained()->onDelete('cascade');
                $table->string('endpoint');
                $table->string('method'); // GET, POST, PUT, DELETE
                $table->integer('response_code');
                $table->integer('response_time'); // milliseconds
                $table->ipAddress('ip_address')->nullable();
                $table->timestamp('created_at');
    
                $table->index(['api_key_id', 'created_at']);
                $table->index('endpoint');
            });
        }

        if (!Schema::hasTable('api_subscriptions')) {
            Schema::create('api_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('plan_name'); // free, basic, pro, enterprise
                $table->integer('rate_limit'); // Requests per hour
                $table->decimal('price', 10, 2)->default(0.00);
                $table->string('billing_period')->default('monthly'); // monthly, yearly
                $table->json('features')->nullable();
                $table->date('starts_at');
                $table->date('ends_at')->nullable();
                $table->string('status')->default('active'); // active, cancelled, expired
                $table->timestamps();
    
                $table->index(['tenant_id', 'status']);
            });
        }

        // 9. SDK Documentation
        if (!Schema::hasTable('sdk_documentation')) {
            Schema::create('sdk_documentation', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('content');
                $table->string('category'); // getting_started, authentication, endpoints, examples
                $table->integer('order')->default(0);
                $table->boolean('is_published')->default(false);
                $table->json('code_examples')->nullable();
                $table->timestamps();
    
                $table->index(['category', 'is_published']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdk_documentation');
        Schema::dropIfExists('api_subscriptions');
        Schema::dropIfExists('api_usage_logs');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('theme_installations');
        Schema::dropIfExists('themes');
        Schema::dropIfExists('custom_module_records');
        Schema::dropIfExists('custom_modules');
        Schema::dropIfExists('developer_payouts');
        Schema::dropIfExists('developer_earnings');
        Schema::dropIfExists('developer_accounts');
        Schema::dropIfExists('app_reviews');
        Schema::dropIfExists('app_installations');
        Schema::dropIfExists('marketplace_apps');
    }
};
