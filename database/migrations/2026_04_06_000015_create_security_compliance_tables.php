<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Two-Factor Authentication
        if (! Schema::hasTable('two_factor_auth')) {
            Schema::create('two_factor_auth', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('secret_key'); // Encrypted TOTP secret
                $table->text('recovery_codes')->nullable(); // Encrypted recovery codes
                $table->boolean('enabled')->default(false);
                $table->timestamp('enabled_at')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->string('method')->default('totp'); // totp, sms, email
                $table->timestamps();

                $table->unique('user_id');
                $table->index('enabled');
            });
        }

        // 2. Granular Permissions
        if (! Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique(); // e.g., 'invoices.create'
                $table->string('group'); // e.g., 'invoices', 'users', 'settings'
                $table->string('description')->nullable();
                $table->boolean('is_system')->default(false); // System permissions can't be deleted
                $table->timestamps();

                $table->index('group');
            });
        }

        if (! Schema::hasTable('role_permission')) {
            // Check if roles table exists before creating foreign key
            if (Schema::hasTable('roles')) {
                Schema::create('role_permission', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('role_id')->constrained()->onDelete('cascade');
                    $table->foreignId('permission_id')->constrained()->onDelete('cascade');
                    $table->timestamps();

                    $table->unique(['role_id', 'permission_id']);
                });
            } else {
                // Create table without foreign key if roles table doesn't exist
                if (! Schema::hasTable('role_permission')) {
                    Schema::create('role_permission', function (Blueprint $table) {
                        $table->id();
                        $table->unsignedBigInteger('role_id');
                        $table->foreignId('permission_id')->constrained()->onDelete('cascade');
                        $table->timestamps();

                        $table->unique(['role_id', 'permission_id']);
                        $table->index('role_id');
                    });
                }
            }
        }

        // 3. Data Encryption Keys
        if (! Schema::hasTable('encryption_keys')) {
            Schema::create('encryption_keys', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('key_name'); // e.g., 'customer_data', 'financial_data'
                $table->text('public_key'); // For encryption
                $table->text('private_key'); // Encrypted private key
                $table->string('algorithm')->default('AES-256-CBC');
                $table->boolean('is_active')->default(true);
                $table->timestamp('rotated_at')->nullable();
                $table->foreignId('rotated_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();

                $table->unique(['tenant_id', 'key_name']);
                $table->index('is_active');
            });
        }

        // 4. GDPR/PDP Consent & Data Requests
        if (! Schema::hasTable('data_consents')) {
            Schema::create('data_consents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('consent_type'); // data_processing, marketing, analytics
                $table->boolean('granted')->default(false);
                $table->text('consent_text')->nullable(); // What user consented to
                $table->ipAddress('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamp('granted_at');
                $table->timestamp('withdrawn_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'user_id']);
                $table->index('consent_type');
            });
        }

        if (! Schema::hasTable('data_requests')) {
            Schema::create('data_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('request_type'); // access, rectification, erasure, portability
                $table->text('details')->nullable();
                $table->string('status')->default('pending'); // pending, processing, completed, rejected
                $table->text('response_data')->nullable(); // For access/portability requests
                $table->text('rejection_reason')->nullable();
                $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('processed_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
                $table->index('request_type');
            });
        }

        // 5. Enhanced Audit Logs
        if (! Schema::hasTable('audit_logs_enhanced')) {
            Schema::create('audit_logs_enhanced', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->string('event_type'); // login, logout, create, update, delete, permission_change
                $table->string('model_type')->nullable(); // App\Models\Invoice
                $table->unsignedBigInteger('model_id')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->ipAddress('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->string('device_type')->nullable(); // desktop, mobile, tablet
                $table->string('location')->nullable(); // Geo location
                $table->boolean('success')->default(true);
                $table->text('failure_reason')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at');

                $table->index(['tenant_id', 'event_type']);
                $table->index(['tenant_id', 'user_id']);
                $table->index(['tenant_id', 'created_at']);
                $table->index(['model_type', 'model_id']);
            });
        }

        // 6. Session Management & Device Tracking
        if (! Schema::hasTable('user_sessions')) {
            Schema::create('user_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('session_id')->unique();
                $table->string('device_name')->nullable(); // e.g., "Chrome on Windows"
                $table->string('device_type')->nullable(); // desktop, mobile, tablet
                $table->string('browser')->nullable();
                $table->string('platform')->nullable(); // Windows, macOS, Android, iOS
                $table->ipAddress('ip_address')->nullable();
                $table->string('location')->nullable();
                $table->text('user_agent')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_current')->default(false);
                $table->timestamp('last_activity_at');
                $table->timestamp('expires_at');
                $table->timestamps();

                $table->index(['user_id', 'is_active']);
                $table->index(['tenant_id', 'is_active']);
                $table->index('session_id');
            });
        }

        // 7. IP Whitelisting
        if (! Schema::hasTable('ip_whitelist')) {
            Schema::create('ip_whitelist', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('ip_address'); // Single IP or CIDR range
                $table->string('description')->nullable();
                $table->string('scope')->default('admin'); // admin, api, all
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by_user_id')->constrained('users')->onDelete('cascade');
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'ip_address', 'scope']);
                $table->index(['tenant_id', 'is_active']);
            });
        }

        // 8. Security Events (Failed logins, suspicious activity)
        if (! Schema::hasTable('security_events')) {
            Schema::create('security_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->string('event_type'); // failed_login, brute_force, suspicious_ip, unauthorized_access
                $table->string('severity')->default('medium'); // low, medium, high, critical
                $table->ipAddress('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->json('details')->nullable();
                $table->boolean('resolved')->default(false);
                $table->text('resolution_notes')->nullable();
                $table->foreignId('resolved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('resolved_at')->nullable();
                $table->timestamp('created_at');

                $table->index(['tenant_id', 'event_type']);
                $table->index(['tenant_id', 'severity']);
                $table->index(['tenant_id', 'resolved']);
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_events');
        Schema::dropIfExists('ip_whitelist');
        Schema::dropIfExists('user_sessions');
        Schema::dropIfExists('audit_logs_enhanced');
        Schema::dropIfExists('data_requests');
        Schema::dropIfExists('data_consents');
        Schema::dropIfExists('encryption_keys');
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('two_factor_auth');
    }
};
