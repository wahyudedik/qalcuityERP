<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Consignment partners (toko/outlet penerima titipan)
        if (!Schema::hasTable('consignment_partners')) {
            Schema::create('consignment_partners', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('contact_person')->nullable();
                $table->string('phone', 20)->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->decimal('commission_pct', 5, 2)->default(0); // % komisi default
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'is_active']);
            });
        }

        // Consignment shipments (pengiriman stok titipan)
        if (!Schema::hasTable('consignment_shipments')) {
            Schema::create('consignment_shipments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('number', 30);
                $table->foreignId('partner_id')->constrained('consignment_partners')->cascadeOnDelete();
                $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
                $table->date('ship_date');
                $table->enum('status', ['draft', 'shipped', 'partial_sold', 'settled', 'returned'])->default('draft');
                $table->decimal('total_cost', 15, 2)->default(0);
                $table->decimal('total_retail', 15, 2)->default(0);
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->unique(['tenant_id', 'number']);
            });
        }

        // Consignment shipment items
        if (!Schema::hasTable('consignment_shipment_items')) {
            Schema::create('consignment_shipment_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('consignment_shipment_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->decimal('quantity_sent', 12, 3);
                $table->decimal('quantity_sold', 12, 3)->default(0);
                $table->decimal('quantity_returned', 12, 3)->default(0);
                $table->decimal('cost_price', 12, 2);       // HPP per unit
                $table->decimal('retail_price', 12, 2);      // harga jual di outlet
                $table->timestamps();
            });
        }

        // Consignment sales reports (laporan penjualan dari partner)
        if (!Schema::hasTable('consignment_sales_reports')) {
            Schema::create('consignment_sales_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('number', 30);
                $table->foreignId('partner_id')->constrained('consignment_partners')->cascadeOnDelete();
                $table->foreignId('consignment_shipment_id')->constrained()->cascadeOnDelete();
                $table->date('period_start');
                $table->date('period_end');
                $table->decimal('total_sales', 15, 2)->default(0);
                $table->decimal('commission_pct', 5, 2)->default(0);
                $table->decimal('commission_amount', 15, 2)->default(0);
                $table->decimal('net_receivable', 15, 2)->default(0); // sales - commission
                $table->enum('status', ['draft', 'confirmed', 'settled'])->default('draft');
                $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->unique(['tenant_id', 'number']);
            });
        }

        // Consignment settlements (pembayaran dari partner)
        if (!Schema::hasTable('consignment_settlements')) {
            Schema::create('consignment_settlements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sales_report_id')->constrained('consignment_sales_reports')->cascadeOnDelete();
                $table->date('settlement_date');
                $table->decimal('amount', 15, 2);
                $table->string('payment_method', 30)->default('transfer');
                $table->string('reference')->nullable();
                $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('consignment_settlements');
        Schema::dropIfExists('consignment_sales_reports');
        Schema::dropIfExists('consignment_shipment_items');
        Schema::dropIfExists('consignment_shipments');
        Schema::dropIfExists('consignment_partners');
    }
};
