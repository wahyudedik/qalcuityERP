<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('coa_certificates')) {
            Schema::create('coa_certificates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('batch_id')->constrained('cosmetic_batch_records')->onDelete('cascade');
                $table->string('coa_number')->unique();
                $table->date('issue_date');
                $table->date('expiry_date')->nullable();
                $table->json('test_results');
                $table->text('conclusion')->nullable();
                $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status')->default('draft');
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'issue_date']);
            });
        }

        if (!Schema::hasTable('oos_investigations')) {
            Schema::create('oos_investigations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('test_result_id')->nullable()->constrained('qc_test_results')->nullOnDelete();
                $table->foreignId('batch_id')->nullable()->constrained('cosmetic_batch_records')->nullOnDelete();
                $table->string('oos_number')->unique();
                $table->string('oos_type');
                $table->text('description');
                $table->text('root_cause')->nullable();
                $table->text('corrective_action')->nullable();
                $table->text('preventive_action')->nullable();
                $table->string('severity')->default('medium');
                $table->string('status')->default('open');
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('investigated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('discovery_date');
                $table->timestamp('completion_date')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'severity']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('oos_investigations');
        Schema::dropIfExists('coa_certificates');
    }
};
