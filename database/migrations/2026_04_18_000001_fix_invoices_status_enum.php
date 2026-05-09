<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Perbaiki ENUM invoices.status dan payables.status:
     * Tambahkan nilai 'cancelled', 'voided', 'partial_paid', 'overdue'
     * yang digunakan di TransactionStateMachine, InvoiceController, dan kode lainnya.
     *
     * Non-destructive: ALTER MODIFY tidak menghapus data yang sudah ada.
     */
    public function up(): void
    {
        // Perbaiki invoices.status
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM(
            'unpaid',
            'partial',
            'partial_paid',
            'paid',
            'cancelled',
            'voided',
            'overdue'
        ) NOT NULL DEFAULT 'unpaid'");

        // Perbaiki payables.status
        DB::statement("ALTER TABLE payables MODIFY COLUMN status ENUM(
            'unpaid',
            'partial',
            'partial_paid',
            'paid',
            'cancelled',
            'voided'
        ) NOT NULL DEFAULT 'unpaid'");
    }

    public function down(): void
    {
        // Kembalikan ke definisi awal (hanya jika tidak ada data dengan nilai baru)
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM(
            'unpaid',
            'partial',
            'paid'
        ) NOT NULL DEFAULT 'unpaid'");

        DB::statement("ALTER TABLE payables MODIFY COLUMN status ENUM(
            'unpaid',
            'partial',
            'paid'
        ) NOT NULL DEFAULT 'unpaid'");
    }
};
