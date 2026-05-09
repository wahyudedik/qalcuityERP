<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Multi-level BOM (replaces flat Recipe for complex products)
        if (! Schema::hasTable('boms')) {
            Schema::create('boms', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->decimal('batch_size', 12, 3)->default(1);
                $table->string('batch_unit', 20)->default('pcs');
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'product_id']);
            });
        }

        if (! Schema::hasTable('bom_lines')) {
            Schema::create('bom_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('bom_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete(); // raw material or sub-assembly
                $table->decimal('quantity_per_batch', 12, 3);
                $table->string('unit', 20);
                $table->foreignId('child_bom_id')->nullable()->constrained('boms')->nullOnDelete(); // sub-assembly BOM
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Work Centers (mesin / stasiun kerja)
        if (! Schema::hasTable('work_centers')) {
            Schema::create('work_centers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('code', 20);
                $table->string('name');
                $table->decimal('cost_per_hour', 12, 2)->default(0);
                $table->unsignedSmallInteger('capacity_per_day')->default(8); // jam
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'code']);
            });
        }

        // Work Order Operations (routing steps)
        if (! Schema::hasTable('work_order_operations')) {
            Schema::create('work_order_operations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('work_center_id')->nullable()->constrained()->nullOnDelete();
                $table->unsignedSmallInteger('sequence')->default(1);
                $table->string('name');
                $table->decimal('estimated_hours', 8, 2)->default(0);
                $table->decimal('actual_hours', 8, 2)->nullable();
                $table->enum('status', ['pending', 'in_progress', 'completed', 'skipped'])->default('pending');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['work_order_id', 'sequence']);
            });
        }

        // Add bom_id to work_orders + material_consumed flag
        Schema::table('work_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('work_orders', 'bom_id')) {
                $table->foreignId('bom_id')->nullable()->after('recipe_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('work_orders', 'materials_consumed')) {
                $table->boolean('materials_consumed')->default(false)->after('overhead_cost');
            }
            if (! Schema::hasColumn('work_orders', 'journal_entry_id')) {
                $table->foreignId('journal_entry_id')->nullable()->after('materials_consumed')
                    ->constrained('journal_entries')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropForeign(['bom_id']);
            $table->dropForeign(['journal_entry_id']);
            $table->dropColumn(['bom_id', 'materials_consumed', 'journal_entry_id']);
        });
        Schema::dropIfExists('work_order_operations');
        Schema::dropIfExists('work_centers');
        Schema::dropIfExists('bom_lines');
        Schema::dropIfExists('boms');
    }
};
