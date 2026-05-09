<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The qc_test_templates table was created by the cosmetic QC migration (04_07)
     * with columns: template_name, template_code, test_category, acceptance_criteria, procedure.
     *
     * The manufacturing QC migration (04_11) expected to create the table with
     * columns: name, product_type, stage, sample_size_formula, acceptance_quality_limit, instructions.
     * But its Schema::hasTable check skipped creation since the table already existed.
     *
     * This migration adds the missing manufacturing columns so both modules can coexist.
     */
    public function up(): void
    {
        Schema::table('qc_test_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('qc_test_templates', 'name')) {
                $table->string('name')->nullable()->after('tenant_id');
            }
            if (! Schema::hasColumn('qc_test_templates', 'product_type')) {
                $table->string('product_type')->nullable()->after('name');
            }
            if (! Schema::hasColumn('qc_test_templates', 'stage')) {
                $table->string('stage')->nullable()->after('product_type');
            }
            if (! Schema::hasColumn('qc_test_templates', 'sample_size_formula')) {
                $table->integer('sample_size_formula')->default(1)->after('test_parameters');
            }
            if (! Schema::hasColumn('qc_test_templates', 'acceptance_quality_limit')) {
                $table->decimal('acceptance_quality_limit', 5, 2)->default(2.5)->after('sample_size_formula');
            }
            if (! Schema::hasColumn('qc_test_templates', 'instructions')) {
                $table->text('instructions')->nullable()->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('qc_test_templates', function (Blueprint $table) {
            $columns = ['name', 'product_type', 'stage', 'sample_size_formula', 'acceptance_quality_limit', 'instructions'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('qc_test_templates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
