<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Task 37: Document Number Registry ────────────────────────
        // Menyimpan counter per jenis dokumen per tenant.
        // Nomor tidak pernah di-reuse walau record dihapus.
        if (!Schema::hasTable('document_number_sequences')) {
            Schema::create('document_number_sequences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('doc_type', 30);   // invoice, po, pr, rfq, gr, je, jrv, so, quo, wt, etc.
                $table->string('period_key', 10);  // YYYY atau YYYYMM tergantung format
                $table->unsignedInteger('last_number')->default(0);
                $table->timestamps();
    
                $table->unique(['tenant_id', 'doc_type', 'period_key']);
                $table->index(['tenant_id', 'doc_type']);
            });
        }

        // ── Task 36: Transaction Revisions ───────────────────────────
        // Setiap kali transaksi yang sudah posted perlu diubah,
        // buat amendment baru. Versi lama tetap immutable.
        if (!Schema::hasTable('transaction_revisions')) {
            Schema::create('transaction_revisions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('model_type', 100);      // App\Models\Invoice, App\Models\PurchaseOrder, etc.
                $table->unsignedBigInteger('model_id');  // ID record asli
                $table->unsignedInteger('revision');     // 1, 2, 3, ...
                $table->string('reason')->nullable();    // alasan revisi
                $table->json('snapshot_before');         // data sebelum revisi
                $table->json('snapshot_after')->nullable(); // data setelah revisi (diisi saat selesai)
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamp('finalized_at')->nullable();
                $table->timestamps();
    
                $table->index(['model_type', 'model_id']);
                $table->index(['tenant_id', 'model_type']);
            });
        }

        // ── Task 35: Tambah kolom state machine ke invoices ──────────
        Schema::table('invoices', function (Blueprint $table) {
            // Status baru: draft → posted → partial_paid → paid → cancelled → voided
            // Kolom status lama (unpaid/partial/paid) diganti dengan posting_status
            if (!Schema::hasColumn('invoices', 'posting_status')) {
                $table->enum('posting_status', ['draft', 'posted', 'cancelled', 'voided'])
                      ->default('draft')->after('status');
            }
            if (!Schema::hasColumn('invoices', 'posted_by')) {
                $table->unsignedBigInteger('posted_by')->nullable()->after('posting_status');
            }
            if (!Schema::hasColumn('invoices', 'posted_at')) {
                $table->timestamp('posted_at')->nullable()->after('posted_by');
            }
            if (!Schema::hasColumn('invoices', 'cancelled_by')) {
                $table->unsignedBigInteger('cancelled_by')->nullable()->after('posted_at');
            }
            if (!Schema::hasColumn('invoices', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            }
            if (!Schema::hasColumn('invoices', 'cancel_reason')) {
                $table->string('cancel_reason')->nullable()->after('cancelled_at');
            }
            if (!Schema::hasColumn('invoices', 'revision_number')) {
                $table->unsignedInteger('revision_number')->default(0)->after('cancel_reason');
            }
            if (!Schema::hasColumn('invoices', 'original_invoice_id')) {
                $table->unsignedBigInteger('original_invoice_id')->nullable()->after('revision_number');
            }

            $table->index(['tenant_id', 'posting_status']);
        });

        // ── Task 35: Tambah kolom state machine ke purchase_orders ───
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Status PO sudah ada: draft, sent, partial, received, cancelled
            // Tambah posting_status untuk immutability
            if (!Schema::hasColumn('purchase_orders', 'posting_status')) {
                $table->enum('posting_status', ['draft', 'posted', 'cancelled'])
                      ->default('draft')->after('status');
            }
            if (!Schema::hasColumn('purchase_orders', 'posted_by')) {
                $table->unsignedBigInteger('posted_by')->nullable()->after('posting_status');
            }
            if (!Schema::hasColumn('purchase_orders', 'posted_at')) {
                $table->timestamp('posted_at')->nullable()->after('posted_by');
            }
            if (!Schema::hasColumn('purchase_orders', 'cancel_reason')) {
                $table->string('cancel_reason')->nullable()->after('posted_at');
            }
            if (!Schema::hasColumn('purchase_orders', 'revision_number')) {
                $table->unsignedInteger('revision_number')->default(0)->after('cancel_reason');
            }

            $table->index(['tenant_id', 'posting_status']);
        });

        // ── Task 35: Tambah kolom state machine ke sales_orders ──────
        Schema::table('sales_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_orders', 'posting_status')) {
                $table->enum('posting_status', ['draft', 'posted', 'cancelled'])
                      ->default('draft')->after('status');
            }
            if (!Schema::hasColumn('sales_orders', 'posted_by')) {
                $table->unsignedBigInteger('posted_by')->nullable()->after('posting_status');
            }
            if (!Schema::hasColumn('sales_orders', 'posted_at')) {
                $table->timestamp('posted_at')->nullable()->after('posted_by');
            }
            if (!Schema::hasColumn('sales_orders', 'cancel_reason')) {
                $table->string('cancel_reason')->nullable()->after('posted_at');
            }
            if (!Schema::hasColumn('sales_orders', 'revision_number')) {
                $table->unsignedInteger('revision_number')->default(0)->after('cancel_reason');
            }
        });

        // ── Task 37: Tambah kolom doc_number_sequence ke semua tabel ─
        // Kolom untuk menyimpan sequence number (integer) agar bisa di-sort
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'doc_sequence')) {
                $table->unsignedInteger('doc_sequence')->nullable()->after('number');
            }
            if (!Schema::hasColumn('invoices', 'doc_year')) {
                $table->string('doc_year', 4)->nullable()->after('doc_sequence');
            }
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'doc_sequence')) {
                $table->unsignedInteger('doc_sequence')->nullable()->after('number');
            }
            if (!Schema::hasColumn('purchase_orders', 'doc_year')) {
                $table->string('doc_year', 4)->nullable()->after('doc_sequence');
            }
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_orders', 'doc_sequence')) {
                $table->unsignedInteger('doc_sequence')->nullable()->after('number');
            }
            if (!Schema::hasColumn('sales_orders', 'doc_year')) {
                $table->string('doc_year', 4)->nullable()->after('doc_sequence');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['posting_status', 'posted_by', 'posted_at', 'cancel_reason', 'revision_number', 'doc_sequence', 'doc_year']);
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['posting_status', 'posted_by', 'posted_at', 'cancel_reason', 'revision_number', 'doc_sequence', 'doc_year']);
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['posting_status', 'posted_by', 'posted_at', 'cancelled_by', 'cancelled_at', 'cancel_reason', 'revision_number', 'original_invoice_id', 'doc_sequence', 'doc_year']);
        });
        Schema::dropIfExists('transaction_revisions');
        Schema::dropIfExists('document_number_sequences');
    }
};
