<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cost Centers / Divisi
        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('code', 20);
            $table->string('name', 100);
            $table->string('type', 30)->default('department'); // department, branch, project, product_line
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'code']);
            $table->unique(['tenant_id', 'code']);
        });

        // Tambah cost_center_id ke journal_entry_lines (sudah ada kolom, pastikan ada FK)
        if (!Schema::hasColumn('journal_entry_lines', 'cost_center_id')) {
            Schema::table('journal_entry_lines', function (Blueprint $table) {
                $table->unsignedBigInteger('cost_center_id')->nullable()->after('description');
            });
        }

        // Tambah cost_center_id ke transaksi utama
        $tables = ['sales_orders', 'invoices', 'purchase_orders', 'expenses'];
        foreach ($tables as $tbl) {
            if (Schema::hasTable($tbl) && !Schema::hasColumn($tbl, 'cost_center_id')) {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->unsignedBigInteger('cost_center_id')->nullable()->after('tenant_id');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_centers');

        $tables = ['sales_orders', 'invoices', 'purchase_orders', 'expenses'];
        foreach ($tables as $tbl) {
            if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'cost_center_id')) {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->dropColumn('cost_center_id');
                });
            }
        }
    }
};
