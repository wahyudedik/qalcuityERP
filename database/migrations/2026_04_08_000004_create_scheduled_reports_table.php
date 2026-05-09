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
        if (! Schema::hasTable('scheduled_reports')) {
            Schema::create('scheduled_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->string('name');
                $table->text('description')->nullable();
                $table->json('metrics'); // ['revenue', 'orders', 'customers', etc.]
                $table->string('frequency'); // daily, weekly, monthly
                $table->json('recipients'); // array of email addresses
                $table->string('format')->default('pdf'); // pdf, excel, csv
                $table->json('filters')->nullable(); // date range, module, etc.
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_run_at')->nullable();
                $table->timestamp('next_run')->nullable();
                $table->string('last_status')->nullable(); // success, failed
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'is_active']);
                $table->index(['next_run', 'is_active']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_reports');
    }
};
