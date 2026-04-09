<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run migrations for GDPR compliance tables.
     * Task 030: GDPR compliance features
     */
    public function up(): void
    {
        // GDPR Consent tracking
        Schema::create('gdpr_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('consent_type'); // privacy_policy, data_processing, marketing, etc.
            $table->ipAddress('ip_address');
            $table->text('user_agent');
            $table->timestamp('consented_at');
            $table->timestamp('withdrawn_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'consent_type']);
            $table->index(['user_id', 'is_active']);
        });

        // GDPR Data Export requests
        Schema::create('gdpr_data_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('export_type')->default('personal_data'); // personal_data, all_data, specific_module
            $table->json('modules')->nullable();
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'requested_at']);
        });

        // GDPR Deletion requests
        Schema::create('gdpr_deletion_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('reason');
            $table->string('status')->default('pending_approval'); // pending_approval, processing, completed, failed
            $table->timestamp('requested_at');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('anonymization_method')->nullable(); // pseudonymization, generalization, suppression
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'requested_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        if (Schema::hasTable('gdpr_deletion_requests')) {
            Schema::dropIfExists('gdpr_deletion_requests');
        }
        if (Schema::hasTable('gdpr_data_exports')) {
            Schema::dropIfExists('gdpr_data_exports');
        }
        if (Schema::hasTable('gdpr_consents')) {
            Schema::dropIfExists('gdpr_consents');
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
