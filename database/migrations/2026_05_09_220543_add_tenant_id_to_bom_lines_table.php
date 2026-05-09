<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bom_lines', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        // Backfill tenant_id from the parent bom record
        DB::statement('UPDATE bom_lines INNER JOIN boms ON bom_lines.bom_id = boms.id SET bom_lines.tenant_id = boms.tenant_id');

        Schema::table('bom_lines', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable(false)->change();
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('bom_lines', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
