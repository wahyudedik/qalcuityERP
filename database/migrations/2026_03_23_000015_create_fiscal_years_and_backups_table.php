<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fiscal Year — lock per tahun fiskal
        if (! Schema::hasTable('fiscal_years')) {
            Schema::create('fiscal_years', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('name', 20);          // e.g. "2025", "2025/2026"
                $table->date('start_date');
                $table->date('end_date');
                $table->enum('status', ['open', 'closed', 'locked'])->default('open');
                $table->unsignedBigInteger('closed_by')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->unsignedBigInteger('locked_by')->nullable();
                $table->timestamp('locked_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'start_date', 'end_date']);
            });
        }

        // Period Backups — snapshot data per periode/tahun
        if (! Schema::hasTable('period_backups')) {
            Schema::create('period_backups', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->enum('type', ['monthly', 'yearly', 'manual']);
                $table->string('label', 50);         // e.g. "Maret 2025", "Tahun 2025"
                $table->date('period_start');
                $table->date('period_end');
                $table->string('file_path', 500)->nullable();  // path di storage
                $table->unsignedBigInteger('file_size')->default(0); // bytes
                $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
                $table->json('summary')->nullable();  // { transactions: 120, invoices: 45, ... }
                $table->text('error_message')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'type', 'period_start']);
            });
        }

        // Tambah kolom fiscal_year_id ke accounting_periods
        Schema::table('accounting_periods', function (Blueprint $table) {
            if (! Schema::hasColumn('accounting_periods', 'fiscal_year_id')) {
                $table->unsignedBigInteger('fiscal_year_id')->nullable()->after('tenant_id');
            }
            if (! Schema::hasColumn('accounting_periods', 'locked_by')) {
                $table->unsignedBigInteger('locked_by')->nullable()->after('closed_by');
            }
            if (! Schema::hasColumn('accounting_periods', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('closed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accounting_periods', function (Blueprint $table) {
            $table->dropColumn(['fiscal_year_id', 'locked_by', 'locked_at']);
        });
        Schema::dropIfExists('period_backups');
        Schema::dropIfExists('fiscal_years');
    }
};
