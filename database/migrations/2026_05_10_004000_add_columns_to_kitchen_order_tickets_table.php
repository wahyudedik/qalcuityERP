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
        Schema::table('kitchen_order_tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->after('id');
            $table->unsignedBigInteger('fb_order_id')->nullable()->after('tenant_id');
            $table->string('ticket_number')->nullable()->after('fb_order_id');
            $table->string('station')->nullable()->after('ticket_number'); // grill, fry, salad, dessert, bar
            $table->string('status')->default('pending')->after('station'); // pending, preparing, ready, served, cancelled
            $table->string('priority')->default('normal')->after('status'); // normal, rush, vip
            $table->integer('estimated_time')->nullable()->after('priority'); // minutes
            $table->timestamp('started_at')->nullable()->after('estimated_time');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->text('chef_notes')->nullable()->after('completed_at');

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'station']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kitchen_order_tickets', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropIndex(['tenant_id', 'station']);
            $table->dropColumn([
                'tenant_id',
                'fb_order_id',
                'ticket_number',
                'station',
                'status',
                'priority',
                'estimated_time',
                'started_at',
                'completed_at',
                'chef_notes',
            ]);
        });
    }
};
