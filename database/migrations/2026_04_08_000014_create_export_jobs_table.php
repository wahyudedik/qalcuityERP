<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * BUG-REP-002 FIX: Track queued export jobs for progress monitoring
     */
    public function up(): void
    {
        if (! Schema::hasTable('export_jobs')) {
            Schema::create('export_jobs', function (Blueprint $table) {
                $table->id();
                $table->string('job_id')->unique(); // UUID for tracking
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('export_type'); // e.g., 'SalesReportExport'
                $table->string('filename');
                $table->string('disk')->default('public');
                $table->string('file_path')->nullable();
                $table->string('status')->default('pending'); // pending, processing, completed, failed
                $table->unsignedBigInteger('total_rows')->default(0);
                $table->unsignedBigInteger('processed_rows')->default(0);
                $table->unsignedBigInteger('file_size')->nullable(); // in bytes
                $table->string('download_url')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->timestamps();

                // Indexes for performance
                $table->index(['tenant_id', 'status']);
                $table->index(['user_id', 'created_at']);
                $table->index('job_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_jobs');
    }
};
