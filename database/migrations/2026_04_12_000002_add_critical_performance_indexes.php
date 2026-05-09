<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * TASK-009: Add Database Indexes - SAFE VERSION
     * Only adds indexes to tables/columns that are confirmed to exist
     */
    public function up(): void
    {
        Log::info('TASK-009: Starting safe database indexing');

        // Helper function to safely add index
        $addIndex = function ($table, $columns, $name) {
            if (! Schema::hasTable($table)) {
                return false;
            }

            // Normalize columns to array
            $columns = is_array($columns) ? $columns : [$columns];

            // Check if all columns exist
            foreach ($columns as $col) {
                if (! Schema::hasColumn($table, $col)) {
                    Log::warning("TASK-009: Column {$col} not found in {$table}, skipping index {$name}");

                    return false;
                }
            }

            // Check if index already exists
            $existingIndexes = DB::select("SHOW INDEX FROM {$table}");
            foreach ($existingIndexes as $index) {
                if ($index->Key_name === $name) {
                    Log::info("TASK-009: Index {$name} already exists in {$table}");

                    return false;
                }
            }

            // Add the index
            try {
                Schema::table($table, function (Blueprint $table) use ($columns, $name) {
                    if (count($columns) === 1) {
                        $table->index($columns[0], $name);
                    } else {
                        $table->index($columns, $name);
                    }
                });
                Log::info("TASK-009: Added index {$name} to {$table}(".implode(',', $columns).')');

                return true;
            } catch (Exception $e) {
                Log::error("TASK-009: Failed to add index {$name} to {$table}: ".$e->getMessage());

                return false;
            }
        };

        // ============================================
        // CRITICAL TABLES - SALES
        // ============================================

        $addIndex('sales_orders', ['tenant_id', 'created_at'], 'idx_so_tenant_created');
        $addIndex('sales_orders', ['tenant_id', 'status', 'created_at'], 'idx_so_tenant_status_created');
        $addIndex('sales_orders', ['customer_id', 'created_at'], 'idx_so_customer_created');
        $addIndex('sales_orders', 'order_number', 'idx_so_order_number');

        $addIndex('invoices', ['tenant_id', 'due_date'], 'idx_inv_tenant_due_date');
        $addIndex('invoices', ['tenant_id', 'status'], 'idx_inv_tenant_status');
        $addIndex('invoices', ['customer_id', 'status'], 'idx_inv_customer_status');
        $addIndex('invoices', 'invoice_number', 'idx_inv_number');
        $addIndex('invoices', ['tenant_id', 'created_at'], 'idx_inv_tenant_created');

        $addIndex('customers', ['tenant_id', 'is_active'], 'idx_cust_tenant_active');
        $addIndex('customers', ['tenant_id', 'email'], 'idx_cust_tenant_email');
        $addIndex('customers', ['tenant_id', 'customer_type'], 'idx_cust_tenant_type');

        $addIndex('quotations', ['tenant_id', 'status', 'created_at'], 'idx_quote_tenant_status_created');
        $addIndex('quotations', ['customer_id', 'created_at'], 'idx_quote_customer_created');

        // ============================================
        // CRITICAL TABLES - INVENTORY
        // ============================================

        $addIndex('products', ['tenant_id', 'sku'], 'idx_prod_tenant_sku');
        $addIndex('products', ['tenant_id', 'category_id'], 'idx_prod_tenant_category');
        $addIndex('products', ['tenant_id', 'is_active'], 'idx_prod_tenant_active');
        $addIndex('products', ['tenant_id', 'name'], 'idx_prod_tenant_name');

        $addIndex('inventory', ['tenant_id', 'product_id'], 'idx_inv_tenant_product');
        $addIndex('inventory', ['tenant_id', 'warehouse_id'], 'idx_inv_tenant_warehouse');

        $addIndex('stock_movements', ['tenant_id', 'product_id', 'created_at'], 'idx_sm_tenant_product_created');
        $addIndex('stock_movements', ['tenant_id', 'movement_type', 'created_at'], 'idx_sm_tenant_type_created');

        // ============================================
        // CRITICAL TABLES - PURCHASING
        // ============================================

        $addIndex('purchase_orders', ['tenant_id', 'status', 'created_at'], 'idx_po_tenant_status_created');
        $addIndex('purchase_orders', ['supplier_id', 'created_at'], 'idx_po_supplier_created');
        $addIndex('purchase_orders', 'po_number', 'idx_po_number');

        $addIndex('purchase_requisitions', ['tenant_id', 'status', 'created_at'], 'idx_pr_tenant_status_created');
        $addIndex('purchase_requisitions', 'requisition_number', 'idx_pr_number');

        $addIndex('suppliers', ['tenant_id', 'is_active'], 'idx_sup_tenant_active');

        // ============================================
        // CRITICAL TABLES - HRM & PAYROLL
        // ============================================

        $addIndex('employees', ['tenant_id', 'status'], 'idx_emp_tenant_status');
        $addIndex('employees', ['tenant_id', 'department'], 'idx_emp_tenant_department');
        $addIndex('employees', 'employee_code', 'idx_emp_code');

        $addIndex('attendances', ['tenant_id', 'employee_id', 'date'], 'idx_att_tenant_emp_date');
        $addIndex('attendances', ['tenant_id', 'date'], 'idx_att_tenant_date');

        $addIndex('payroll_runs', ['tenant_id', 'period', 'status'], 'idx_prun_tenant_period_status');

        $addIndex('leave_requests', ['tenant_id', 'employee_id', 'status'], 'idx_leave_tenant_emp_status');
        $addIndex('leave_requests', ['tenant_id', 'status', 'created_at'], 'idx_leave_tenant_status_created');

        $addIndex('overtime_requests', ['tenant_id', 'employee_id', 'status'], 'idx_ot_tenant_emp_status');
        $addIndex('overtime_requests', ['tenant_id', 'status', 'included_in_payroll'], 'idx_ot_tenant_status_payroll');

        // ============================================
        // CRITICAL TABLES - FINANCE
        // ============================================

        $addIndex('journal_entries', ['tenant_id', 'date'], 'idx_je_tenant_date');
        $addIndex('journal_entries', ['tenant_id', 'reference_type', 'reference_id'], 'idx_je_tenant_reference');

        $addIndex('payments', ['tenant_id', 'payment_date'], 'idx_pay_tenant_date');
        $addIndex('payments', 'payment_number', 'idx_pay_number');

        $addIndex('receivables', ['tenant_id', 'customer_id', 'status'], 'idx_rec_tenant_customer_status');
        $addIndex('receivables', ['tenant_id', 'due_date'], 'idx_rec_tenant_due_date');

        $addIndex('payables', ['tenant_id', 'supplier_id', 'status'], 'idx_payable_tenant_supplier_status');
        $addIndex('payables', ['tenant_id', 'due_date'], 'idx_payable_tenant_due_date');

        // ============================================
        // CRITICAL TABLES - MANUFACTURING
        // ============================================

        $addIndex('production_orders', ['tenant_id', 'status', 'created_at'], 'idx_prodord_tenant_status_created');
        $addIndex('production_orders', ['tenant_id', 'product_id'], 'idx_prodord_tenant_product');
        $addIndex('production_orders', 'order_number', 'idx_prodord_number');

        $addIndex('bills_of_materials', ['tenant_id', 'product_id'], 'idx_bom_tenant_product');
        $addIndex('bills_of_materials', ['tenant_id', 'is_active'], 'idx_bom_tenant_active');

        // ============================================
        // CRITICAL TABLES - HEALTHCARE
        // ============================================

        $addIndex('patients', ['tenant_id', 'medical_record_number'], 'idx_pat_tenant_mrn');
        $addIndex('patients', ['tenant_id', 'name'], 'idx_pat_tenant_name');

        $addIndex('appointments', ['tenant_id', 'appointment_date', 'status'], 'idx_appt_tenant_date_status');
        $addIndex('appointments', ['tenant_id', 'patient_id'], 'idx_appt_tenant_patient');
        $addIndex('appointments', ['tenant_id', 'doctor_id'], 'idx_appt_tenant_doctor');

        $addIndex('medical_records', ['tenant_id', 'patient_id', 'created_at'], 'idx_mr_tenant_patient_created');

        // ============================================
        // CRITICAL TABLES - PROJECTS
        // ============================================

        $addIndex('projects', ['tenant_id', 'status'], 'idx_proj_tenant_status');
        $addIndex('projects', 'project_code', 'idx_proj_code');

        $addIndex('daily_site_reports', ['tenant_id', 'project_id', 'report_date'], 'idx_dsr_tenant_project_date');

        // ============================================
        // CRITICAL TABLES - NOTIFICATIONS & LOGS
        // ============================================

        $addIndex('notifications', ['tenant_id', 'read_at'], 'idx_notif_tenant_read');
        $addIndex('notifications', ['tenant_id', 'created_at'], 'idx_notif_tenant_created');

        $addIndex('activity_logs', ['tenant_id', 'created_at'], 'idx_log_tenant_created');
        $addIndex('activity_logs', ['tenant_id', 'user_id'], 'idx_log_tenant_user');

        $addIndex('export_jobs', ['tenant_id', 'status'], 'idx_export_tenant_status');
        $addIndex('export_jobs', ['tenant_id', 'created_at'], 'idx_export_tenant_created');

        Log::info('TASK-009: Safe database indexing completed');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'sales_orders' => ['idx_so_tenant_created', 'idx_so_tenant_status_created', 'idx_so_customer_created', 'idx_so_order_number'],
            'invoices' => ['idx_inv_tenant_due_date', 'idx_inv_tenant_status', 'idx_inv_customer_status', 'idx_inv_number', 'idx_inv_tenant_created'],
            'customers' => ['idx_cust_tenant_active', 'idx_cust_tenant_email', 'idx_cust_tenant_type'],
            'quotations' => ['idx_quote_tenant_status_created', 'idx_quote_customer_created'],
            'products' => ['idx_prod_tenant_sku', 'idx_prod_tenant_category', 'idx_prod_tenant_active', 'idx_prod_tenant_name'],
            'inventory' => ['idx_inv_tenant_product', 'idx_inv_tenant_warehouse'],
            'stock_movements' => ['idx_sm_tenant_product_created', 'idx_sm_tenant_type_created'],
            'purchase_orders' => ['idx_po_tenant_status_created', 'idx_po_supplier_created', 'idx_po_number'],
            'purchase_requisitions' => ['idx_pr_tenant_status_created', 'idx_pr_number'],
            'suppliers' => ['idx_sup_tenant_active'],
            'employees' => ['idx_emp_tenant_status', 'idx_emp_tenant_department', 'idx_emp_code'],
            'attendances' => ['idx_att_tenant_emp_date', 'idx_att_tenant_date'],
            'payroll_runs' => ['idx_prun_tenant_period_status'],
            'leave_requests' => ['idx_leave_tenant_emp_status', 'idx_leave_tenant_status_created'],
            'overtime_requests' => ['idx_ot_tenant_emp_status', 'idx_ot_tenant_status_payroll'],
            'journal_entries' => ['idx_je_tenant_date', 'idx_je_tenant_reference'],
            'payments' => ['idx_pay_tenant_date', 'idx_pay_number'],
            'receivables' => ['idx_rec_tenant_customer_status', 'idx_rec_tenant_due_date'],
            'payables' => ['idx_payable_tenant_supplier_status', 'idx_payable_tenant_due_date'],
            'production_orders' => ['idx_prodord_tenant_status_created', 'idx_prodord_tenant_product', 'idx_prodord_number'],
            'bills_of_materials' => ['idx_bom_tenant_product', 'idx_bom_tenant_active'],
            'patients' => ['idx_pat_tenant_mrn', 'idx_pat_tenant_name'],
            'appointments' => ['idx_appt_tenant_date_status', 'idx_appt_tenant_patient', 'idx_appt_tenant_doctor'],
            'medical_records' => ['idx_mr_tenant_patient_created'],
            'projects' => ['idx_proj_tenant_status', 'idx_proj_code'],
            'daily_site_reports' => ['idx_dsr_tenant_project_date'],
            'notifications' => ['idx_notif_tenant_read', 'idx_notif_tenant_created'],
            'activity_logs' => ['idx_log_tenant_created', 'idx_log_tenant_user'],
            'export_jobs' => ['idx_export_tenant_status', 'idx_export_tenant_created'],
        ];

        foreach ($tables as $table => $indexes) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) use ($indexes) {
                    foreach ($indexes as $index) {
                        $table->dropIndex($index);
                    }
                });
            }
        }

        Log::info('TASK-009: Database indexes rolled back');
    }
};
