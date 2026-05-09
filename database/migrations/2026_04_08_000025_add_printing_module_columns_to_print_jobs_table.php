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
        // Add missing columns to print_jobs table for printing module
        if (Schema::hasTable('print_jobs')) {
            Schema::table('print_jobs', function (Blueprint $table) {
                // Add customer_id if not exists
                if (! Schema::hasColumn('print_jobs', 'customer_id')) {
                    $table->foreignId('customer_id')->nullable()->after('tenant_id')->constrained('customers')->nullOnDelete();
                }

                // Add job_name if not exists
                if (! Schema::hasColumn('print_jobs', 'job_name')) {
                    $table->string('job_name')->nullable()->after('job_type');
                }

                // Add description if not exists
                if (! Schema::hasColumn('print_jobs', 'description')) {
                    $table->text('description')->nullable()->after('job_name');
                }

                // Add product_type if not exists
                if (! Schema::hasColumn('print_jobs', 'product_type')) {
                    $table->string('product_type')->nullable()->after('description');
                }

                // Add priority if not exists
                if (! Schema::hasColumn('print_jobs', 'priority')) {
                    $table->string('priority')->default('normal')->after('status');
                }

                // Add due_date if not exists
                if (! Schema::hasColumn('print_jobs', 'due_date')) {
                    $table->date('due_date')->nullable()->after('priority');
                }

                // Add quantity if not exists
                if (! Schema::hasColumn('print_jobs', 'quantity')) {
                    $table->integer('quantity')->default(0)->after('due_date');
                }

                // Add paper_type if not exists
                if (! Schema::hasColumn('print_jobs', 'paper_type')) {
                    $table->string('paper_type')->nullable()->after('quantity');
                }

                // Add paper_size_width if not exists
                if (! Schema::hasColumn('print_jobs', 'paper_size_width')) {
                    $table->decimal('paper_size_width', 8, 2)->nullable()->after('paper_type');
                }

                // Add paper_size_height if not exists
                if (! Schema::hasColumn('print_jobs', 'paper_size_height')) {
                    $table->decimal('paper_size_height', 8, 2)->nullable()->after('paper_size_width');
                }

                // Add colors_front if not exists
                if (! Schema::hasColumn('print_jobs', 'colors_front')) {
                    $table->integer('colors_front')->default(4)->after('paper_size_height');
                }

                // Add colors_back if not exists
                if (! Schema::hasColumn('print_jobs', 'colors_back')) {
                    $table->integer('colors_back')->default(0)->after('colors_front');
                }

                // Add finishing_type if not exists
                if (! Schema::hasColumn('print_jobs', 'finishing_type')) {
                    $table->string('finishing_type')->nullable()->after('colors_back');
                }

                // Add specifications if not exists
                if (! Schema::hasColumn('print_jobs', 'specifications')) {
                    $table->json('specifications')->nullable()->after('finishing_type');
                }

                // Add file_path if not exists
                if (! Schema::hasColumn('print_jobs', 'file_path')) {
                    $table->string('file_path')->nullable()->after('specifications');
                }

                // Add proof_path if not exists
                if (! Schema::hasColumn('print_jobs', 'proof_path')) {
                    $table->string('proof_path')->nullable()->after('file_path');
                }

                // Add proof_approved if not exists
                if (! Schema::hasColumn('print_jobs', 'proof_approved')) {
                    $table->boolean('proof_approved')->default(false)->after('proof_path');
                }

                // Add proof_approved_at if not exists
                if (! Schema::hasColumn('print_jobs', 'proof_approved_at')) {
                    $table->timestamp('proof_approved_at')->nullable()->after('proof_approved');
                }

                // Add approved_by if not exists
                if (! Schema::hasColumn('print_jobs', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->after('proof_approved_at')->constrained('users')->nullOnDelete();
                }

                // Add estimated_cost if not exists
                if (! Schema::hasColumn('print_jobs', 'estimated_cost')) {
                    $table->decimal('estimated_cost', 12, 2)->default(0)->after('approved_by');
                }

                // Add actual_cost if not exists
                if (! Schema::hasColumn('print_jobs', 'actual_cost')) {
                    $table->decimal('actual_cost', 12, 2)->default(0)->after('estimated_cost');
                }

                // Add quoted_price if not exists
                if (! Schema::hasColumn('print_jobs', 'quoted_price')) {
                    $table->decimal('quoted_price', 12, 2)->default(0)->after('actual_cost');
                }

                // Add started_at if not exists
                if (! Schema::hasColumn('print_jobs', 'started_at')) {
                    $table->timestamp('started_at')->nullable()->after('quoted_price');
                }

                // Add completed_at if not exists
                if (! Schema::hasColumn('print_jobs', 'completed_at')) {
                    $table->timestamp('completed_at')->nullable()->after('started_at');
                }

                // Add assigned_operator if not exists
                if (! Schema::hasColumn('print_jobs', 'assigned_operator')) {
                    $table->foreignId('assigned_operator')->nullable()->after('completed_at')->constrained('users')->nullOnDelete();
                }

                // Add special_instructions if not exists
                if (! Schema::hasColumn('print_jobs', 'special_instructions')) {
                    $table->text('special_instructions')->nullable()->after('assigned_operator');
                }

                // Add notes if not exists
                if (! Schema::hasColumn('print_jobs', 'notes')) {
                    $table->text('notes')->nullable()->after('special_instructions');
                }

                // Add job_number if not exists
                if (! Schema::hasColumn('print_jobs', 'job_number')) {
                    $table->string('job_number')->nullable()->unique()->after('tenant_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('print_jobs')) {
            Schema::table('print_jobs', function (Blueprint $table) {
                $columnsToRemove = [
                    'customer_id',
                    'job_name',
                    'description',
                    'product_type',
                    'priority',
                    'due_date',
                    'quantity',
                    'paper_type',
                    'paper_size_width',
                    'paper_size_height',
                    'colors_front',
                    'colors_back',
                    'finishing_type',
                    'specifications',
                    'file_path',
                    'proof_path',
                    'proof_approved',
                    'proof_approved_at',
                    'approved_by',
                    'estimated_cost',
                    'actual_cost',
                    'quoted_price',
                    'started_at',
                    'completed_at',
                    'assigned_operator',
                    'special_instructions',
                    'notes',
                    'job_number',
                ];

                foreach ($columnsToRemove as $column) {
                    if (Schema::hasColumn('print_jobs', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
