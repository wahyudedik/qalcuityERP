<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop existing tables if they exist
        Schema::dropIfExists('disaster_recovery_logs');
        Schema::dropIfExists('backup_logs');
        Schema::dropIfExists('compliance_reports');
        Schema::dropIfExists('data_anonymization_logs');
        Schema::dropIfExists('access_violations');
        Schema::dropIfExists('audit_trails');

        // Audit Trails (HIPAA Compliant)
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->string('audit_number')->unique(); // AUDIT-YYYYMMDD-XXXXXX

            // User Information
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('user_name')->nullable();
            $table->string('user_role')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            // Action Details
            $table->string('action'); // view, create, update, delete, export, print
            $table->string('action_category'); // patient_record, prescription, lab_result, billing, etc.
            $table->string('model_type'); // App\Models\Patient, App\Models\MedicalRecord, etc.
            $table->unsignedBigInteger('model_id');
            $table->string('record_identifier')->nullable(); // Patient number, record number, etc.

            // Data Changes
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('changed_fields')->nullable();

            // Access Context
            $table->string('access_reason')->nullable(); // treatment, billing, research, audit
            $table->string('department')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable(); // FK to patients

            // Compliance
            $table->boolean('is_hipaa_relevant')->default(true);
            $table->boolean('contains_phi')->default(false); // Protected Health Information
            $table->string('data_classification')->nullable(); // public, internal, confidential, restricted

            // Status
            $table->boolean('is_suspicious')->default(false);
            $table->string('risk_level')->nullable(); // low, medium, high, critical
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('audit_number');
            $table->index('user_id');
            $table->index('action');
            $table->index('action_category');
            $table->index('model_type');
            $table->index('model_id');
            $table->index('patient_id');
            $table->index('created_at');
            $table->index('is_hipaa_relevant');
            $table->index('contains_phi');
        });

        // Access Violations
        Schema::create('access_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('audit_id')->nullable()->constrained('audit_trails')->onDelete('set null');

            // Violation Details
            $table->string('violation_number')->unique(); // VIOLATION-YYYYMMDD-XXXX
            $table->enum('violation_type', [
                'unauthorized_access',
                'data_breach',
                'policy_violation',
                'excessive_access',
                'after_hours_access',
                'bulk_download',
                'unauthorized_export',
                'privilege_escalation'
            ]);
            $table->string('violation_description');
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);

            // Context
            $table->string('ip_address');
            $table->text('user_agent')->nullable();
            $table->datetime('violation_time');

            // Response
            $table->enum('status', ['detected', 'investigating', 'confirmed', 'resolved', 'false_positive'])->default('detected');
            $table->text('investigation_notes')->nullable();
            $table->foreignId('investigated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->datetime('investigated_at')->nullable();
            $table->text('resolution')->nullable();
            $table->datetime('resolved_at')->nullable();

            // Actions Taken
            $table->boolean('user_notified')->default(false);
            $table->boolean('access_revoked')->default(false);
            $table->boolean('reported_to_authority')->default(false);
            $table->text('corrective_actions')->nullable();

            $table->timestamps();

            $table->index('violation_number');
            $table->index('user_id');
            $table->index('violation_type');
            $table->index('severity');
            $table->index('status');
        });

        // Data Anonymization Logs
        Schema::create('data_anonymization_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requested_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');

            // Request Details
            $table->string('anonymization_number')->unique(); // ANON-YYYYMMDD-XXXX
            $table->string('purpose'); // research, training, testing, analytics
            $table->text('description');
            $table->date('request_date');
            $table->datetime('approved_at')->nullable();
            $table->datetime('completed_at')->nullable();

            // Data Scope
            $table->json('data_types'); // ['patients', 'diagnoses', 'treatments', etc.]
            $table->integer('total_records');
            $table->integer('anonymized_records')->default(0);

            // Anonymization Methods
            $table->json('anonymization_methods'); // ['pseudonymization', 'generalization', 'suppression', 'noise_addition']
            $table->json('fields_anonymized'); // ['name', 'phone', 'address', 'id_number', etc.]
            $table->boolean('is_reversible')->default(false);

            // Status
            $table->enum('status', ['requested', 'approved', 'in_progress', 'completed', 'failed', 'rejected'])->default('requested');
            $table->text('rejection_reason')->nullable();

            // Output
            $table->string('output_file_path')->nullable();
            $table->string('output_format')->nullable(); // CSV, JSON, SQL
            $table->text('data_usage_agreement')->nullable();

            // Compliance
            $table->boolean('ethics_approval')->default(false);
            $table->string('ethics_approval_number')->nullable();
            $table->text('compliance_notes')->nullable();

            $table->timestamps();

            $table->index('anonymization_number');
            $table->index('status');
            $table->index('purpose');
            $table->index('request_date');
        });

        // Compliance Reports
        Schema::create('compliance_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generated_by')->constrained('users')->onDelete('restrict');

            // Report Details
            $table->string('report_number')->unique(); // COMP-YYYYMM-XXXX
            $table->string('report_type'); // HIPAA, Permenkes, GDPR, ISO27001, internal
            $table->string('report_name');
            $table->date('period_start');
            $table->date('period_end');
            $table->datetime('generated_at');

            // Compliance Framework
            $table->json('compliance_frameworks'); // ['HIPAA', 'Permenkes 269/2008', 'GDPR']
            $table->json('requirements_checked');
            $table->json('compliance_status'); // JSON with pass/fail for each requirement

            // Metrics
            $table->integer('total_checks')->default(0);
            $table->integer('passed_checks')->default(0);
            $table->integer('failed_checks')->default(0);
            $table->integer('warning_checks')->default(0);
            $table->decimal('compliance_score', 5, 2)->nullable(); // percentage

            // Findings
            $table->json('findings')->nullable();
            $table->json('recommendations')->nullable();
            $table->json('corrective_actions')->nullable();

            // Status
            $table->enum('status', ['draft', 'completed', 'reviewed', 'submitted', 'approved'])->default('draft');
            $table->text('executive_summary')->nullable();
            $table->text('notes')->nullable();

            // Documentation
            $table->string('report_file_path')->nullable();
            $table->string('evidence_folder')->nullable();

            $table->timestamps();

            $table->index('report_number');
            $table->index('report_type');
            $table->index('status');
            $table->index('period_start');
            $table->index('compliance_score');
        });

        // Backup Logs
        Schema::create('backup_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('initiated_by')->constrained('users')->onDelete('restrict');

            // Backup Details
            $table->string('backup_number')->unique(); // BACKUP-YYYYMMDD-XXXX
            $table->datetime('backup_start');
            $table->datetime('backup_end')->nullable();
            $table->enum('backup_type', ['full', 'incremental', 'differential', 'medical_records_only']);
            $table->enum('backup_method', ['automated', 'manual', 'emergency']);

            // Scope
            $table->json('tables_included');
            $table->integer('total_records')->default(0);
            $table->decimal('backup_size_mb', 10, 2)->nullable();

            // Storage
            $table->string('storage_location'); // local, cloud, offsite
            $table->string('storage_path');
            $table->string('storage_provider')->nullable(); // AWS S3, Google Cloud, etc.
            $table->boolean('is_encrypted')->default(true);
            $table->string('encryption_algorithm')->nullable(); // AES-256

            // Status
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed', 'verified'])->default('pending');
            $table->text('error_message')->nullable();
            $table->boolean('verification_passed')->default(false);
            $table->datetime('verified_at')->nullable();

            // Retention
            $table->date('retention_until');
            $table->boolean('is_deleted')->default(false);
            $table->datetime('deleted_at')->nullable();

            // Compliance
            $table->boolean('hipaa_compliant')->default(true);
            $table->text('compliance_notes')->nullable();

            $table->timestamps();

            $table->index('backup_number');
            $table->index('backup_type');
            $table->index('status');
            $table->index('backup_start');
            $table->index('is_encrypted');
        });

        // Disaster Recovery Logs
        Schema::create('disaster_recovery_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('initiated_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');

            // DR Details
            $table->string('dr_number')->unique(); // DR-YYYYMMDD-XXXX
            $table->datetime('incident_start');
            $table->datetime('incident_end')->nullable();

            // Incident
            $table->enum('incident_type', ['system_failure', 'data_corruption', 'security_breach', 'natural_disaster', 'human_error']);
            $table->string('incident_description');
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);

            // Impact
            $table->json('affected_systems');
            $table->integer('affected_records')->default(0);
            $table->datetime('downtime_start')->nullable();
            $table->datetime('downtime_end')->nullable();
            $table->integer('downtime_minutes')->nullable();

            // Recovery
            $table->string('backup_used')->nullable(); // backup_number
            $table->datetime('recovery_start')->nullable();
            $table->datetime('recovery_end')->nullable();
            $table->integer('records_recovered')->default(0);
            $table->integer('records_lost')->default(0);

            // Status
            $table->enum('status', ['detected', 'investigating', 'recovering', 'recovered', 'resolved'])->default('detected');
            $table->text('recovery_notes')->nullable();
            $table->text('lessons_learned')->nullable();
            $table->text('preventive_measures')->nullable();

            // Compliance
            $table->boolean('reported_to_authority')->default(false);
            $table->text('regulatory_notifications')->nullable();

            $table->timestamps();

            $table->index('dr_number');
            $table->index('incident_type');
            $table->index('severity');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disaster_recovery_logs');
        Schema::dropIfExists('backup_logs');
        Schema::dropIfExists('compliance_reports');
        Schema::dropIfExists('data_anonymization_logs');
        Schema::dropIfExists('access_violations');
        Schema::dropIfExists('audit_trails');
    }
};
