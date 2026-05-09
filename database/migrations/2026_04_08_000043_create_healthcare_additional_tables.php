<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Queue Tickets (new)
        if (! Schema::hasTable('queue_tickets')) {
            Schema::create('queue_tickets', function (Blueprint $table) {
                $table->id();
                $table->string('queue_number')->unique();
                $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
                $table->foreignId('patient_visit_id')->nullable()->constrained('patient_visits')->onDelete('set null');
                $table->unsignedBigInteger('department_id')->nullable();
                $table->foreignId('doctor_id')->nullable()->constrained('doctors')->onDelete('set null');
                $table->string('status')->default('waiting');
                $table->string('priority')->default('normal');
                $table->timestamp('issued_at')->nullable();
                $table->timestamp('called_at')->nullable();
                $table->timestamp('served_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['status', 'priority']);
                $table->index('issued_at');
            });
        }

        // Sterilization Cycles (new)
        if (! Schema::hasTable('sterilization_cycles')) {
            Schema::create('sterilization_cycles', function (Blueprint $table) {
                $table->id();
                $table->string('cycle_number')->unique();
                $table->foreignId('equipment_id')->constrained('medical_equipment')->onDelete('cascade');
                $table->string('method');
                $table->timestamp('start_time')->nullable();
                $table->timestamp('end_time')->nullable();
                $table->decimal('temperature', 5, 2)->nullable();
                $table->decimal('pressure', 5, 2)->nullable();
                $table->integer('duration_minutes')->nullable();
                $table->foreignId('operator_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('quality_check_type')->nullable();
                $table->string('quality_check_result')->nullable();
                $table->foreignId('quality_checked_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('quality_checked_at')->nullable();
                $table->string('status')->default('in_progress');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['equipment_id', 'status']);
                $table->index('start_time');
            });
        }

        // Medical Waste (new)
        if (! Schema::hasTable('medical_wastes')) {
            Schema::create('medical_wastes', function (Blueprint $table) {
                $table->id();
                $table->string('waste_code')->unique();
                $table->string('waste_type');
                $table->string('category');
                $table->decimal('weight_kg', 8, 2)->nullable();
                $table->decimal('volume_liters', 8, 2)->nullable();
                $table->string('collection_point');
                $table->foreignId('collected_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('collected_at')->nullable();
                $table->string('disposal_method')->nullable();
                $table->string('disposal_location')->nullable();
                $table->foreignId('disposed_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('disposed_at')->nullable();
                $table->string('tracking_number')->nullable();
                $table->string('status')->default('collected');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['waste_type', 'status']);
                $table->index('collected_at');
            });
        }

        // HL7 Messages (new)
        if (! Schema::hasTable('hl7_messages')) {
            Schema::create('hl7_messages', function (Blueprint $table) {
                $table->id();
                $table->string('message_id')->unique();
                $table->string('message_type');
                $table->string('direction');
                $table->string('source_system');
                $table->string('destination_system');
                $table->json('payload')->nullable();
                $table->string('status')->default('pending');
                $table->text('error_message')->nullable();
                $table->integer('retry_count')->default(0);
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('received_at')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->index(['message_type', 'status']);
                $table->index('sent_at');
            });
        }

        // Notification Rules (new)
        if (! Schema::hasTable('notification_rules')) {
            Schema::create('notification_rules', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('event_type');
                $table->json('conditions')->nullable();
                $table->json('channels');
                $table->string('priority')->default('normal');
                $table->json('recipients')->nullable();
                $table->text('template')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('cooldown_minutes')->default(0);
                $table->integer('max_notifications_per_day')->default(0);
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();

                $table->index(['event_type', 'is_active']);
            });
        }

        // Patient Messages (new)
        if (! Schema::hasTable('patient_messages')) {
            Schema::create('patient_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');
                $table->string('subject');
                $table->text('message');
                $table->string('priority')->default('normal');
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->foreignId('parent_id')->nullable()->constrained('patient_messages')->onDelete('cascade');
                $table->string('attachment_path')->nullable();
                $table->timestamps();

                $table->index(['recipient_id', 'is_read']);
                $table->index('parent_id');
            });
        }

        // Lab Equipment (new)
        if (! Schema::hasTable('lab_equipment')) {
            Schema::create('lab_equipment', function (Blueprint $table) {
                $table->id();
                $table->string('equipment_code')->unique();
                $table->string('name');
                $table->string('manufacturer')->nullable();
                $table->string('model')->nullable();
                $table->string('serial_number')->nullable();
                $table->string('connection_type');
                $table->json('connection_config')->nullable();
                $table->string('status')->default('active');
                $table->date('last_calibration')->nullable();
                $table->date('next_calibration')->nullable();
                $table->date('last_maintenance')->nullable();
                $table->date('next_maintenance')->nullable();
                $table->boolean('auto_poll_enabled')->default(false);
                $table->integer('poll_interval_minutes')->default(60);
                $table->string('location')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['status', 'connection_type']);
            });
        }

        // Health Education (new)
        if (! Schema::hasTable('health_education')) {
            Schema::create('health_education', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('category');
                $table->text('content');
                $table->text('summary')->nullable();
                $table->string('target_audience')->nullable();
                $table->string('language')->default('id');
                $table->foreignId('author_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('status')->default('draft');
                $table->timestamp('published_at')->nullable();
                $table->string('attachment_path')->nullable();
                $table->integer('view_count')->default(0);
                $table->timestamps();

                $table->index(['category', 'status']);
                $table->index('published_at');
            });
        }

        // Backup Logs (new)
        if (! Schema::hasTable('backup_logs')) {
            Schema::create('backup_logs', function (Blueprint $table) {
                $table->id();
                $table->string('backup_type');
                $table->string('status')->default('in_progress');
                $table->string('file_path')->nullable();
                $table->unsignedBigInteger('size_bytes')->nullable();
                $table->text('error_message')->nullable();
                $table->foreignId('initiated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->text('notes')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'started_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_logs');
        Schema::dropIfExists('health_education');
        Schema::dropIfExists('lab_equipment');
        Schema::dropIfExists('patient_messages');
        Schema::dropIfExists('notification_rules');
        Schema::dropIfExists('hl7_messages');
        Schema::dropIfExists('medical_wastes');
        Schema::dropIfExists('sterilization_cycles');
        Schema::dropIfExists('queue_tickets');
    }
};
