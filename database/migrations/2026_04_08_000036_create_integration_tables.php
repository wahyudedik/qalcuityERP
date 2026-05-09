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
        // Drop existing tables if they exist
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('pharmacy_integration_logs');
        Schema::dropIfExists('lab_equipment_integrations');
        Schema::dropIfExists('bpjs_claims');
        Schema::dropIfExists('hl7_messages');

        // HL7/FHIR Messages (Health Data Exchange)
        if (! Schema::hasTable('hl7_messages')) {
            Schema::create('hl7_messages', function (Blueprint $table) {
                $table->id();
                $table->string('message_id')->unique(); // HL7-YYYYMMDD-XXXXXX
                $table->string('message_type'); // ADT, ORM, ORU, SIU, etc.
                $table->string('message_version')->default('2.5'); // HL7 version
                $table->string('trigger_event'); // A01 (Admit), A03 (Discharge), etc.

                // Direction
                $table->enum('direction', ['inbound', 'outbound']);
                $table->string('source_system')->nullable();
                $table->string('destination_system')->nullable();

                // Message Content
                $table->text('raw_message')->nullable(); // Raw HL7 message
                $table->json('parsed_data')->nullable(); // Parsed HL7 segments
                $table->json('fhir_resource')->nullable(); // FHIR JSON representation

                // Patient Reference
                $table->unsignedBigInteger('patient_id')->nullable(); // FK to patients
                $table->string('patient_identifier')->nullable(); // MRN or external ID
                $table->string('encounter_id')->nullable();

                // Processing
                $table->enum('status', ['received', 'parsed', 'validated', 'processed', 'failed', 'acknowledged'])
                    ->default('received');
                $table->text('error_message')->nullable();
                $table->json('acknowledgment')->nullable(); // ACK message

                // Validation
                $table->boolean('is_valid')->default(false);
                $table->json('validation_errors')->nullable();

                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('message_id');
                $table->index('message_type');
                $table->index('direction');
                $table->index('status');
                $table->index('patient_id');
                $table->index('created_at');
            });
        }

        // BPJS Kesehatan Claims (Indonesia National Health Insurance)
        if (! Schema::hasTable('bpjs_claims')) {
            Schema::create('bpjs_claims', function (Blueprint $table) {
                $table->id();
                $table->string('claim_number')->unique(); // BPJS-YYYYMMDD-XXXX
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('admission_id')->nullable(); // FK to admissions
                $table->unsignedBigInteger('bill_id')->nullable(); // FK to medical_bills

                // Patient BPJS Info
                $table->string('bpjs_number'); // Nomor Kartu BPJS
                $table->string('bpjs_class')->nullable(); // Kelas 1, 2, 3
                $table->string('participant_type')->nullable(); // PBI, Non-PBI
                $table->date('eligibility_date')->nullable();

                // Claim Information
                $table->string('sep_number')->nullable(); // Surat Eligibilitas Peserta
                $table->date('admission_date');
                $table->date('discharge_date')->nullable();
                $table->string('diagnosis_code')->nullable(); // ICD-10
                $table->string('diagnosis_description')->nullable();
                $table->string('procedure_code')->nullable(); // ICD-9-CM
                $table->string('procedure_description')->nullable();

                // Financial
                $table->decimal('claimed_amount', 12, 2)->default(0);
                $table->decimal('approved_amount', 12, 2)->nullable();
                $table->decimal('paid_amount', 12, 2)->nullable();
                $table->decimal('rejected_amount', 12, 2)->nullable();

                // Status
                $table->enum('status', ['draft', 'submitted', 'verified', 'approved', 'paid', 'rejected', 'appeal'])
                    ->default('draft');
                $table->datetime('submitted_at')->nullable();
                $table->datetime('verified_at')->nullable();
                $table->datetime('approved_at')->nullable();
                $table->datetime('paid_at')->nullable();

                // Response
                $table->text('response_data')->nullable(); // JSON response from BPJS
                $table->text('rejection_reason')->nullable();
                $table->text('appeal_notes')->nullable();

                $table->timestamps();

                $table->index('claim_number');
                $table->index('bpjs_number');
                $table->index('status');
                $table->index('admission_date');
            });
        }

        // Laboratory Equipment Integrations
        if (! Schema::hasTable('lab_equipment_integrations')) {
            Schema::create('lab_equipment_integrations', function (Blueprint $table) {
                $table->id();
                $table->string('equipment_name');
                $table->string('equipment_model')->nullable();
                $table->string('manufacturer')->nullable();
                $table->string('serial_number')->nullable();

                // Integration Details
                $table->string('integration_type'); // ASTM, HL7, API, file_import
                $table->string('connection_type'); // serial, tcp_ip, http, file
                $table->string('connection_details')->nullable(); // JSON
                $table->string('api_endpoint')->nullable();
                $table->text('authentication')->nullable(); // JSON (encrypted)

                // Status
                $table->boolean('is_active')->default(true);
                $table->boolean('is_connected')->default(false);
                $table->datetime('last_connection')->nullable();
                $table->text('last_error')->nullable();

                // Data Mapping
                $table->json('test_code_mapping')->nullable(); // Equipment code → System code
                $table->json('unit_mapping')->nullable();
                $table->json('reference_ranges')->nullable();

                // Configuration
                $table->json('configuration')->nullable(); // Equipment-specific config
                $table->integer('polling_interval_seconds')->default(60);
                $table->boolean('auto_import_results')->default(true);

                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('equipment_name');
                $table->index('integration_type');
                $table->index('is_active');
                $table->index('is_connected');
            });
        }

        // Pharmacy Integration Logs
        if (! Schema::hasTable('pharmacy_integration_logs')) {
            Schema::create('pharmacy_integration_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pharmacy_id')->nullable(); // FK to pharmacies
                $table->unsignedBigInteger('prescription_id')->nullable(); // FK to prescriptions (will be created separately)

                // Integration Details
                $table->string('integration_type'); // POS, inventory, e-prescription
                $table->string('transaction_number')->nullable();
                $table->enum('direction', ['to_pharmacy', 'from_pharmacy']);

                // Data
                $table->json('request_data')->nullable();
                $table->json('response_data')->nullable();
                $table->enum('status', ['pending', 'sent', 'received', 'processed', 'failed']);
                $table->text('error_message')->nullable();

                // Timing
                $table->datetime('sent_at')->nullable();
                $table->datetime('received_at')->nullable();
                $table->integer('response_time_ms')->nullable();

                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('pharmacy_id');
                $table->index('prescription_id');
                $table->index('integration_type');
                $table->index('status');
            });
        }

        // Notification Logs (SMS/WhatsApp/Email)
        if (! Schema::hasTable('notification_logs')) {
            Schema::create('notification_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

                // Notification Details
                $table->string('notification_number')->unique(); // NOTIF-YYYYMMDD-XXXXXX
                $table->enum('channel', ['sms', 'whatsapp', 'email', 'push', 'voice']);
                $table->string('template_code')->nullable();
                $table->string('subject')->nullable();
                $table->text('message_body');
                $table->json('message_data')->nullable(); // Dynamic data for template

                // Recipients
                $table->string('recipient_name')->nullable();
                $table->string('recipient_phone')->nullable();
                $table->string('recipient_email')->nullable();

                // Gateway
                $table->string('gateway_provider'); // Twilio, WhatsApp Business, etc.
                $table->string('gateway_message_id')->nullable();
                $table->json('gateway_response')->nullable();

                // Status
                $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed', 'bounced'])
                    ->default('pending');
                $table->text('error_message')->nullable();
                $table->datetime('sent_at')->nullable();
                $table->datetime('delivered_at')->nullable();
                $table->datetime('read_at')->nullable();
                $table->integer('retry_count')->default(0);
                $table->datetime('next_retry_at')->nullable();

                // Metadata
                $table->string('category')->nullable(); // appointment, lab_result, prescription, billing
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->string('reference_type')->nullable();

                $table->timestamps();

                $table->index('notification_number');
                $table->index('channel');
                $table->index('status');
                $table->index('patient_id');
                $table->index('category');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('pharmacy_integration_logs');
        Schema::dropIfExists('lab_equipment_integrations');
        Schema::dropIfExists('bpjs_claims');
        Schema::dropIfExists('hl7_messages');
    }
};
