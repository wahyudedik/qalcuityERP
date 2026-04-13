<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('crm_leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('assigned_to')->nullable(); // user_id
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('source')->nullable(); // referral, website, cold_call, social_media, exhibition
            $table->string('stage')->default('new'); // new, contacted, qualified, proposal, negotiation, won, lost
            $table->decimal('estimated_value', 18, 2)->default(0);
            $table->string('product_interest')->nullable();
            $table->date('expected_close_date')->nullable();
            $table->integer('probability')->default(0); // 0-100%
            $table->text('notes')->nullable();
            $table->timestamp('last_contact_at')->nullable();
            $table->timestamps();
            $table->index('tenant_id');
        });

        Schema::create('crm_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('user_id');
            $table->string('type'); // call, email, meeting, whatsapp, demo, proposal
            $table->text('description');
            $table->string('outcome')->nullable(); // interested, not_interested, follow_up, closed
            $table->date('next_follow_up')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'lead_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_activities');
        Schema::dropIfExists('crm_leads');
    }
};
