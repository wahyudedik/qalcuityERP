<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('package_supplier_allocations', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->index('tenant_id');
        });

        // Backfill tenant_id from the parent tour_package
        DB::statement('
            UPDATE package_supplier_allocations psa
            JOIN tour_packages tp ON tp.id = psa.tour_package_id
            SET psa.tenant_id = tp.tenant_id
        ');

        // Now make it non-nullable
        Schema::table('package_supplier_allocations', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('package_supplier_allocations', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
