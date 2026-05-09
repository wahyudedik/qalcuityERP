<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Knowledge base articles
        if (! Schema::hasTable('kb_articles')) {
            Schema::create('kb_articles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->string('slug')->nullable();
                $table->string('category', 50)->default('general');
                $table->text('body');
                $table->boolean('is_published')->default(false);
                $table->unsignedInteger('views')->default(0);
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->index(['tenant_id', 'is_published']);
            });
        }

        // Support tickets
        if (! Schema::hasTable('helpdesk_tickets')) {
            Schema::create('helpdesk_tickets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('ticket_number', 30);
                $table->string('subject');
                $table->text('description');
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->string('contact_name')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('contact_phone', 20)->nullable();
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
                $table->string('category', 50)->default('general');
                $table->enum('status', ['open', 'in_progress', 'waiting', 'resolved', 'closed'])->default('open');
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                // SLA
                $table->timestamp('sla_response_due')->nullable();
                $table->timestamp('sla_resolve_due')->nullable();
                $table->timestamp('first_responded_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->boolean('sla_response_met')->nullable();
                $table->boolean('sla_resolve_met')->nullable();
                // References
                $table->string('reference_type', 30)->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->foreignId('contract_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('satisfaction_rating', 2, 1)->nullable(); // 1.0 - 5.0
                $table->text('tags')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'ticket_number']);
                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'assigned_to']);
            });
        }

        // Ticket replies / comments
        if (! Schema::hasTable('helpdesk_replies')) {
            Schema::create('helpdesk_replies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained('helpdesk_tickets')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->text('body');
                $table->boolean('is_internal')->default(false); // internal note vs customer-visible
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('helpdesk_replies');
        Schema::dropIfExists('helpdesk_tickets');
        Schema::dropIfExists('kb_articles');
    }
};
