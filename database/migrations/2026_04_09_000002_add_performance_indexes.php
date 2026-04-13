<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run performance optimization indexes.
     * Task 025: Optimize database queries - Add missing indexes
     */
    public function up(): void
    {
        // Helper function to safely add indexes
        $addIndex = function ($table, $columns, $indexName = null) {
            try {
                $columnsList = \Illuminate\Support\Facades\Schema::getColumnListing($table);

                // Check if all columns exist
                foreach ($columns as $col) {
                    if (!in_array($col, $columnsList)) {
                        return; // Skip if column doesn't exist
                    }
                }

                \Illuminate\Support\Facades\Schema::table($table, function (\Illuminate\Database\Schema\Blueprint $table) use ($columns, $indexName) {
                    $table->index($columns, $indexName);
                });
            } catch (\Exception $e) {
                // Silently skip if table doesn't exist or other errors
            }
        };

        // Healthcare Module Indexes
        if (\Illuminate\Support\Facades\Schema::hasTable('patients')) {
            $addIndex('patients', ['tenant_id', 'status']);
            $addIndex('patients', ['tenant_id', 'mrn']);
            $addIndex('patients', ['tenant_id', 'full_name']);
            $addIndex('patients', ['tenant_id', 'created_at']);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('appointments')) {
            $addIndex('appointments', ['tenant_id', 'status']);
            $addIndex('appointments', ['tenant_id', 'appointment_date']);
            $addIndex('appointments', ['tenant_id', 'doctor_id']);
            $addIndex('appointments', ['tenant_id', 'patient_id']);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('lab_results')) {
            $addIndex('lab_results', ['tenant_id', 'is_critical']);
            $addIndex('lab_results', ['tenant_id', 'result_date']);
        }

        // Hotel Module Indexes
        if (\Illuminate\Support\Facades\Schema::hasTable('rooms')) {
            $addIndex('rooms', ['tenant_id', 'status']);
            $addIndex('rooms', ['tenant_id', 'room_type_id']);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('reservations')) {
            $addIndex('reservations', ['tenant_id', 'status']);
            $addIndex('reservations', ['tenant_id', 'check_in_date']);
            $addIndex('reservations', ['tenant_id', 'guest_id']);
        }

        // Inventory Module Indexes
        if (\Illuminate\Support\Facades\Schema::hasTable('products')) {
            $addIndex('products', ['tenant_id', 'is_active']);
            $addIndex('products', ['tenant_id', 'warehouse_id']);
            $addIndex('products', ['tenant_id', 'stock']);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('stock_movements')) {
            $addIndex('stock_movements', ['tenant_id', 'product_id']);
            $addIndex('stock_movements', ['tenant_id', 'warehouse_id']);
            $addIndex('stock_movements', ['tenant_id', 'type']);
            $addIndex('stock_movements', ['tenant_id', 'created_at']);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('stock_transfers')) {
            $addIndex('stock_transfers', ['tenant_id', 'status']);
            $addIndex('stock_transfers', ['tenant_id', 'product_id']);
        }

        // HRM Module Indexes
        if (\Illuminate\Support\Facades\Schema::hasTable('employees')) {
            $addIndex('employees', ['tenant_id', 'status']);
            $addIndex('employees', ['tenant_id', 'department_id']);
            $addIndex('employees', ['tenant_id', 'employee_code']);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('attendances')) {
            $addIndex('attendances', ['tenant_id', 'employee_id']);
            $addIndex('attendances', ['tenant_id', 'check_in']);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('leave_requests')) {
            $addIndex('leave_requests', ['tenant_id', 'employee_id']);
            $addIndex('leave_requests', ['tenant_id', 'status']);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('payrolls')) {
            $addIndex('payrolls', ['tenant_id', 'employee_id']);
            $addIndex('payrolls', ['tenant_id', 'period']);
            $addIndex('payrolls', ['tenant_id', 'status']);
        }

        // Manufacturing Module Indexes
        if (\Illuminate\Support\Facades\Schema::hasTable('work_orders')) {
            $addIndex('work_orders', ['tenant_id', 'status']);
            $addIndex('work_orders', ['tenant_id', 'product_id']);
            $addIndex('work_orders', ['tenant_id', 'planned_start_date']);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('quality_checks')) {
            $addIndex('quality_checks', ['tenant_id', 'status']);
            $addIndex('quality_checks', ['tenant_id', 'work_order_id']);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('defect_records')) {
            $addIndex('defect_records', ['tenant_id', 'severity']);
            $addIndex('defect_records', ['tenant_id', 'status']);
            $addIndex('defect_records', ['tenant_id', 'work_order_id']);
        }

        // Agriculture Module Indexes
        if (\Illuminate\Support\Facades\Schema::hasTable('crops')) {
            $addIndex('crops', ['tenant_id', 'status']);
            $addIndex('crops', ['tenant_id', 'field_id']);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('fields')) {
            $addIndex('fields', ['tenant_id', 'status']);
        }

        // Fisheries Module Indexes
        if (\Illuminate\Support\Facades\Schema::hasTable('ponds')) {
            $addIndex('ponds', ['tenant_id', 'status']);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('fish_stocks')) {
            $addIndex('fish_stocks', ['tenant_id', 'pond_id']);
        }

        // Livestock Module Indexes
        if (\Illuminate\Support\Facades\Schema::hasTable('livestock')) {
            $addIndex('livestock', ['tenant_id', 'type']);
            $addIndex('livestock', ['tenant_id', 'status']);
        }

        // Cosmetics Module Indexes
        if (\Illuminate\Support\Facades\Schema::hasTable('cosmetic_formulations')) {
            $addIndex('cosmetic_formulations', ['tenant_id', 'category']);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('cosmetic_batches')) {
            $addIndex('cosmetic_batches', ['tenant_id', 'formulation_id']);
            $addIndex('cosmetic_batches', ['tenant_id', 'expiry_date']);
        }

        // Tour & Travel Module Indexes
        if (\Illuminate\Support\Facades\Schema::hasTable('tour_packages')) {
            $addIndex('tour_packages', ['tenant_id', 'destination']);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('tour_bookings')) {
            $addIndex('tour_bookings', ['tenant_id', 'status']);
            $addIndex('tour_bookings', ['tenant_id', 'package_id']);
            $addIndex('tour_bookings', ['tenant_id', 'departure_date']);
        }

        // General Performance Indexes
        if (\Illuminate\Support\Facades\Schema::hasTable('journal_entries')) {
            $addIndex('journal_entries', ['tenant_id', 'date']);
            $addIndex('journal_entries', ['tenant_id', 'status']);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('journal_entry_lines')) {
            $addIndex('journal_entry_lines', ['tenant_id', 'account_id']);
            $addIndex('journal_entry_lines', ['tenant_id', 'journal_entry_id']);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('invoices')) {
            $addIndex('invoices', ['tenant_id', 'status']);
            $addIndex('invoices', ['tenant_id', 'due_date']);
            $addIndex('invoices', ['tenant_id', 'customer_id']);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('sales_orders')) {
            $addIndex('sales_orders', ['tenant_id', 'status']);
            $addIndex('sales_orders', ['tenant_id', 'order_date']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove Healthcare indexes
        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropIndex(['tenant_id', 'mrn']);
            $table->dropIndex(['tenant_id', 'full_name']);
            $table->dropIndex(['tenant_id', 'created_at']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropIndex(['tenant_id', 'appointment_date']);
            $table->dropIndex(['tenant_id', 'doctor_id']);
            $table->dropIndex(['tenant_id', 'patient_id']);
        });

        Schema::table('lab_results', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'is_critical']);
            $table->dropIndex(['tenant_id', 'result_date']);
        });

        // Remove Hotel indexes
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropIndex(['tenant_id', 'room_type_id']);
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropIndex(['tenant_id', 'check_in_date']);
            $table->dropIndex(['tenant_id', 'guest_id']);
        });

        // Remove Inventory indexes
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'is_active']);
            $table->dropIndex(['tenant_id', 'warehouse_id']);
            $table->dropIndex(['tenant_id', 'stock']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'product_id']);
            $table->dropIndex(['tenant_id', 'warehouse_id']);
            $table->dropIndex(['tenant_id', 'type']);
            $table->dropIndex(['tenant_id', 'created_at']);
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropIndex(['tenant_id', 'product_id']);
        });

        // Remove HRM indexes
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropIndex(['tenant_id', 'department_id']);
            $table->dropIndex(['tenant_id', 'employee_code']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'employee_id']);
            $table->dropIndex(['tenant_id', 'check_in']);
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'employee_id']);
            $table->dropIndex(['tenant_id', 'status']);
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'employee_id']);
            $table->dropIndex(['tenant_id', 'period']);
            $table->dropIndex(['tenant_id', 'status']);
        });

        // Remove Manufacturing indexes
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropIndex(['tenant_id', 'product_id']);
            $table->dropIndex(['tenant_id', 'planned_start_date']);
        });

        Schema::table('quality_checks', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropIndex(['tenant_id', 'work_order_id']);
        });

        Schema::table('defect_records', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'severity']);
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropIndex(['tenant_id', 'work_order_id']);
        });

        // Remove Agriculture indexes
        Schema::table('crops', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropIndex(['tenant_id', 'field_id']);
        });

        Schema::table('fields', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'status']);
        });

        // Remove Fisheries indexes
        Schema::table('ponds', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'status']);
        });

        Schema::table('fish_stocks', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'pond_id']);
        });

        // Remove Livestock indexes
        Schema::table('livestock', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'type']);
            $table->dropIndex(['tenant_id', 'status']);
        });

        // Remove Cosmetics indexes
        Schema::table('cosmetic_formulations', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'category']);
        });

        Schema::table('cosmetic_batches', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'formulation_id']);
            $table->dropIndex(['tenant_id', 'expiry_date']);
        });

        // Remove Tour & Travel indexes
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'destination']);
        });

        Schema::table('tour_bookings', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropIndex(['tenant_id', 'package_id']);
            $table->dropIndex(['tenant_id', 'departure_date']);
        });

        // Remove General indexes
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'date']);
            $table->dropIndex(['tenant_id', 'status']);
        });

        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'account_id']);
            $table->dropIndex(['tenant_id', 'journal_entry_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropIndex(['tenant_id', 'due_date']);
            $table->dropIndex(['tenant_id', 'customer_id']);
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropIndex(['tenant_id', 'order_date']);
        });
    }
};
