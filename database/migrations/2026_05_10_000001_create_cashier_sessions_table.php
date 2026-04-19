<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashier_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // kasir
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();

            // Identitas terminal/register
            $table->string('register_name')->nullable(); // e.g. "Kasir 1", "Terminal A"

            // Status sesi
            $table->enum('status', ['open', 'closed'])->default('open');

            // Saldo awal (modal kasir)
            $table->decimal('opening_balance', 18, 2)->default(0);
            $table->timestamp('opened_at');

            // Saldo & rekap penutupan
            $table->decimal('closing_balance', 18, 2)->nullable();   // kas aktual saat tutup
            $table->decimal('expected_balance', 18, 2)->nullable();  // kas yang seharusnya ada
            $table->decimal('balance_difference', 18, 2)->nullable(); // selisih (closing - expected)
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();

            // Rekap penjualan (dihitung saat tutup)
            $table->integer('total_transactions')->default(0);
            $table->decimal('total_sales', 18, 2)->default(0);
            $table->decimal('total_cash', 18, 2)->default(0);
            $table->decimal('total_card', 18, 2)->default(0);
            $table->decimal('total_qris', 18, 2)->default(0);
            $table->decimal('total_transfer', 18, 2)->default(0);
            $table->decimal('total_discount', 18, 2)->default(0);
            $table->decimal('total_tax', 18, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'user_id', 'opened_at']);
        });

        // Tambahkan kolom cashier_session_id ke sales_orders jika belum ada
        if (Schema::hasTable('sales_orders') && !Schema::hasColumn('sales_orders', 'cashier_session_id')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->foreignId('cashier_session_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('cashier_sessions')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales_orders') && Schema::hasColumn('sales_orders', 'cashier_session_id')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->dropForeignIdFor(\App\Models\CashierSession::class);
                $table->dropColumn('cashier_session_id');
            });
        }

        Schema::dropIfExists('cashier_sessions');
    }
};
