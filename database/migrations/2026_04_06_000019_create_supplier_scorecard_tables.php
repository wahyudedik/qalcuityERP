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
        // Supplier Performance Scorecards
        if (! Schema::hasTable('supplier_scorecards')) {
            Schema::create('supplier_scorecards', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
                $table->string('period'); // monthly, quarterly, yearly
                $table->date('period_start');
                $table->date('period_end');

                // Quality Metrics
                $table->decimal('quality_score', 5, 2)->default(0); // 0-100
                $table->integer('total_deliveries')->default(0);
                $table->integer('defective_items')->default(0);
                $table->decimal('defect_rate', 5, 2)->default(0); // percentage

                // Delivery Performance
                $table->decimal('delivery_score', 5, 2)->default(0); // 0-100
                $table->integer('on_time_deliveries')->default(0);
                $table->integer('late_deliveries')->default(0);
                $table->decimal('on_time_percentage', 5, 2)->default(0);
                $table->decimal('avg_lead_time_days', 6, 2)->default(0);

                // Cost Performance
                $table->decimal('cost_score', 5, 2)->default(0); // 0-100
                $table->decimal('price_competitiveness', 5, 2)->default(0); // market comparison
                $table->integer('cost_savings_identified')->default(0);
                $table->decimal('total_spend', 15, 2)->default(0);

                // Service & Communication
                $table->decimal('service_score', 5, 2)->default(0); // 0-100
                $table->integer('response_time_hours_avg')->default(0);
                $table->integer('issues_resolved')->default(0);
                $table->integer('total_issues')->default(0);
                $table->decimal('issue_resolution_rate', 5, 2)->default(0);

                // Overall Score
                $table->decimal('overall_score', 5, 2)->default(0); // weighted average
                $table->string('rating')->default('C'); // A, B, C, D, F
                $table->string('status')->default('active'); // active, warning, critical

                // Notes
                $table->text('strengths')->nullable();
                $table->text('areas_for_improvement')->nullable();
                $table->text('action_items')->nullable();

                $table->timestamps();
                $table->index(['tenant_id', 'supplier_id', 'period'], 'sup_score_idx');
                $table->index(['overall_score', 'period_end'], 'sup_score_overall_idx');
            });
        }

        // Supplier Collaboration Portal Access
        if (! Schema::hasTable('supplier_portal_users')) {
            Schema::create('supplier_portal_users', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('role')->default('viewer'); // viewer, editor, admin
                $table->string('phone')->nullable();
                $table->string('position')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_login_at')->nullable();
                $table->rememberToken();
                $table->timestamps();

                $table->index(['tenant_id', 'supplier_id'], 'sup_portal_idx');
            });
        }

        // Supplier Documents & Certifications
        if (! Schema::hasTable('supplier_documents')) {
            Schema::create('supplier_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
                $table->string('document_type'); // certificate, license, insurance, quality_report
                $table->string('document_name');
                $table->string('file_path');
                $table->string('file_size')->nullable();
                $table->string('mime_type')->nullable();
                $table->date('issue_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->string('issuing_authority')->nullable();
                $table->string('certificate_number')->nullable();
                $table->boolean('is_verified')->default(false);
                $table->timestamp('verified_at')->nullable();
                $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'supplier_id', 'document_type'], 'sup_doc_idx');
                $table->index(['expiry_date', 'is_verified'], 'sup_doc_expiry_idx');
            });
        }

        // Supplier RFQ Responses
        if (! Schema::hasTable('supplier_rfq_responses')) {
            Schema::create('supplier_rfq_responses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('rfq_id')->constrained('purchase_requisitions')->onDelete('cascade');
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
                $table->decimal('quoted_price', 15, 2);
                $table->string('currency')->default('IDR');
                $table->integer('lead_time_days')->nullable();
                $table->integer('minimum_order_quantity')->nullable();
                $table->text('terms_and_conditions')->nullable();
                $table->text('additional_notes')->nullable();
                $table->date('valid_until')->nullable();
                $table->string('status')->default('submitted'); // submitted, accepted, rejected, expired
                $table->timestamp('submitted_at');
                $table->timestamp('accepted_at')->nullable();
                $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['tenant_id', 'rfq_id', 'supplier_id'], 'sup_rfq_idx');
                $table->index(['status', 'submitted_at'], 'sup_rfq_status_idx');
            });
        }

        // Supplier Performance Issues/Incidents
        if (! Schema::hasTable('supplier_incidents')) {
            Schema::create('supplier_incidents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
                $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
                $table->string('incident_type'); // quality_issue, late_delivery, communication, pricing_dispute
                $table->string('severity')->default('medium'); // low, medium, high, critical
                $table->text('description');
                $table->text('impact')->nullable();
                $table->decimal('financial_impact', 15, 2)->default(0);
                $table->string('status')->default('open'); // open, investigating, resolved, closed
                $table->timestamp('reported_at');
                $table->foreignId('reported_by')->constrained('users')->onDelete('cascade');
                $table->timestamp('resolved_at')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('resolution_notes')->nullable();
                $table->text('preventive_actions')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'supplier_id', 'status'], 'sup_incident_idx');
                $table->index(['severity', 'reported_at'], 'sup_incident_sev_idx');
            });
        }

        // Strategic Sourcing Opportunities
        if (! Schema::hasTable('sourcing_opportunities')) {
            Schema::create('sourcing_opportunities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('category')->nullable(); // product_category
                $table->decimal('estimated_annual_spend', 15, 2)->default(0);
                $table->integer('potential_suppliers_count')->default(0);
                $table->string('priority')->default('medium'); // low, medium, high, critical
                $table->string('status')->default('identified'); // identified, analyzing, rfq_sent, negotiated, implemented
                $table->decimal('potential_savings', 15, 2)->default(0);
                $table->decimal('savings_percentage', 5, 2)->default(0);
                $table->date('target_completion_date')->nullable();
                $table->date('actual_completion_date')->nullable();
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->text('strategy_notes')->nullable();
                $table->text('risks')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'status', 'priority'], 'sup_source_idx');
                $table->index(['estimated_annual_spend'], 'sup_source_spend_idx');
            });
        }

        // Supplier Market Intelligence
        if (! Schema::hasTable('supplier_market_intelligence')) {
            Schema::create('supplier_market_intelligence', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
                $table->string('intelligence_type'); // price_trend, capacity_change, financial_health, market_share
                $table->date('report_date');
                $table->text('summary');
                $table->text('details')->nullable();
                $table->string('source')->nullable();
                $table->string('reliability')->default('medium'); // low, medium, high
                $table->string('impact')->default('neutral'); // positive, negative, neutral
                $table->text('recommendations')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'supplier_id', 'report_date'], 'sup_market_idx');
                $table->index(['intelligence_type', 'report_date'], 'sup_market_type_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_market_intelligence');
        Schema::dropIfExists('sourcing_opportunities');
        Schema::dropIfExists('supplier_incidents');
        Schema::dropIfExists('supplier_rfq_responses');
        Schema::dropIfExists('supplier_documents');
        Schema::dropIfExists('supplier_portal_users');
        Schema::dropIfExists('supplier_scorecards');
    }
};
