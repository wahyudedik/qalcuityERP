<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The cosmetic QC migration (04_07) created template_name, template_code,
     * and test_category as NOT NULL. The manufacturing QC module doesn't use
     * these columns, so inserts from that module fail.
     *
     * Make these columns nullable so both modules can coexist.
     */
    public function up(): void
    {
        Schema::table('qc_test_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('qc_test_templates', 'template_name')) {
                $table->string('template_name')->nullable()->change();
            }
            if (! Schema::hasColumn('qc_test_templates', 'template_code')) {
                $table->string('template_code')->nullable()->change();
            }
            if (! Schema::hasColumn('qc_test_templates', 'test_category')) {
                $table->string('test_category')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('qc_test_templates', function (Blueprint $table) {
            $table->string('template_name')->nullable(false)->change();
            $table->string('template_code')->nullable(false)->change();
            $table->string('test_category')->nullable(false)->change();
        });
    }
};
