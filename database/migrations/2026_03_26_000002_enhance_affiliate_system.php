<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add demo tenant to affiliates
        Schema::table('affiliates', function (Blueprint $table) {
            $table->foreignId('demo_tenant_id')->nullable()->after('user_id')
                  ->constrained('tenants')->nullOnDelete();
        });

        // Add withdraw request fields to payouts
        Schema::table('affiliate_payouts', function (Blueprint $table) {
            $table->foreignId('requested_by')->nullable()->after('affiliate_id')
                  ->constrained('users')->nullOnDelete();
            $table->timestamp('requested_at')->nullable()->after('status');
            $table->string('reject_reason')->nullable()->after('notes');
        });

        // Fraud monitoring / audit log
        Schema::create('affiliate_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->string('event');           // referral_created, commission_created, withdraw_requested, etc
            $table->string('severity', 10)->default('info'); // info, warning, fraud
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['affiliate_id', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_audit_logs');
        Schema::table('affiliate_payouts', function (Blueprint $table) {
            $table->dropForeign(['requested_by']);
            $table->dropColumn(['requested_by', 'requested_at', 'reject_reason']);
        });
        Schema::table('affiliates', function (Blueprint $table) {
            $table->dropForeign(['demo_tenant_id']);
            $table->dropColumn('demo_tenant_id');
        });
    }
};
