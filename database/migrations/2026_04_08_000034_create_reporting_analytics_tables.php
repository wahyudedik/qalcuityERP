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
        Schema::dropIfExists('clinical_quality_indicators');
        Schema::dropIfExists('ministry_reports');
        Schema::dropIfExists('patient_satisfaction_surveys');

        // Patient Satisfaction Surveys
        Schema::create('patient_satisfaction_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('visit_id')->nullable(); // FK to patient_visits
            $table->unsignedBigInteger('admission_id')->nullable(); // FK to admissions
            $table->unsignedBigInteger('doctor_id')->nullable(); // FK to doctors
            $table->unsignedBigInteger('department_id')->nullable(); // FK to departments

            // Survey Information
            $table->string('survey_number')->unique(); // SURVEY-YYYYMMDD-XXXX
            $table->datetime('submitted_date');
            $table->enum('survey_type', ['outpatient', 'inpatient', 'emergency', 'telemedicine', 'general'])->default('general');

            // Overall Rating
            $table->tinyInteger('overall_rating')->unsigned(); // 1-5
            $table->tinyInteger('would_recommend')->nullable(); // 1-5

            // Specific Ratings
            $table->tinyInteger('admission_rating')->nullable()->unsigned(); // 1-5
            $table->tinyInteger('doctor_rating')->nullable()->unsigned(); // 1-5
            $table->tinyInteger('nurse_rating')->nullable()->unsigned(); // 1-5
            $table->tinyInteger('facility_rating')->nullable()->unsigned(); // 1-5
            $table->tinyInteger('food_rating')->nullable()->unsigned(); // 1-5
            $table->tinyInteger('cleanliness_rating')->nullable()->unsigned(); // 1-5
            $table->tinyInteger('wait_time_rating')->nullable()->unsigned(); // 1-5

            // NPS (Net Promoter Score)
            $table->tinyInteger('nps_score')->nullable(); // 0-10

            // Feedback
            $table->text('positive_feedback')->nullable();
            $table->text('negative_feedback')->nullable();
            $table->text('suggestions')->nullable();
            $table->text('complaints')->nullable();

            // Categories
            $table->json('feedback_categories')->nullable(); // ['staff', 'facility', 'process', etc.]
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_resolved')->default(false);
            $table->text('resolution_notes')->nullable();
            $table->datetime('resolved_at')->nullable();

            $table->timestamps();

            $table->index('survey_number');
            $table->index('patient_id');
            $table->index('submitted_date');
            $table->index('overall_rating');
            $table->index('survey_type');
        });

        // Ministry of Health Reports (SIRS/SIMRS)
        Schema::create('ministry_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generated_by')->constrained('users')->onDelete('restrict');

            // Report Information
            $table->string('report_number')->unique(); // KEMENKES-YYYYMM-XXXX
            $table->string('report_type'); // RL1, RL2, RL3, RL4a, RL4b, etc.
            $table->string('report_name');
            $table->date('report_period_start');
            $table->date('report_period_end');
            $table->datetime('generated_at');
            $table->datetime('submitted_at')->nullable();

            // Status
            $table->enum('status', ['draft', 'generated', 'validated', 'submitted', 'rejected', 'approved'])->default('draft');

            // Data
            $table->json('report_data'); // JSON report data
            $table->text('validation_notes')->nullable();
            $table->text('rejection_reason')->nullable();

            // Submission
            $table->string('submission_reference')->nullable();
            $table->string('submission_response')->nullable(); // JSON

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('report_number');
            $table->index('report_type');
            $table->index('status');
            $table->index('report_period_start');
        });

        // Clinical Quality Indicators
        Schema::create('clinical_quality_indicators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id')->nullable(); // FK to departments

            // Indicator Information
            $table->string('indicator_code')->unique(); // HAI-001, MOR-001, etc.
            $table->string('indicator_name');
            $table->string('category'); // HAI, Mortality, Readmission, etc.
            $table->text('description')->nullable();

            // Measurement
            $table->date('measurement_date');
            $table->decimal('numerator', 10, 2)->default(0);
            $table->decimal('denominator', 10, 2)->default(0);
            $table->decimal('rate', 8, 2)->default(0); // Percentage
            $table->string('unit')->nullable(); // %, per 1000 patient-days, etc.

            // Target & Benchmark
            $table->decimal('target_value', 8, 2)->nullable();
            $table->decimal('benchmark_value', 8, 2)->nullable();
            $table->boolean('meets_target')->default(false);

            // Trend
            $table->enum('trend', ['improving', 'stable', 'declining'])->default('stable');

            // Actions
            $table->text('corrective_actions')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('indicator_code');
            $table->index('category');
            $table->index('measurement_date');
        });

        // Hospital Analytics Daily (Aggregated KPIs)
        if (!Schema::hasTable('hospital_analytics_daily')) {
            Schema::create('hospital_analytics_daily', function (Blueprint $table) {
                $table->id();
                $table->date('analytics_date')->unique();

                // Bed Occupancy
                $table->decimal('bed_occupancy_rate', 5, 2)->nullable(); // BOR %
                $table->integer('total_beds')->default(0);
                $table->integer('occupied_beds')->default(0);
                $table->integer('available_beds')->default(0);

                // Length of Stay
                $table->decimal('average_length_of_stay', 5, 2)->nullable(); // ALOS in days
                $table->integer('total_discharges')->default(0);
                $table->integer('total_patient_days')->default(0);

                // Patient Turnover
                $table->decimal('patient_turnover_rate', 5, 2)->nullable(); // %
                $table->integer('total_admissions')->default(0);

                // Doctor Utilization
                $table->decimal('doctor_utilization_rate', 5, 2)->nullable(); // %
                $table->integer('active_doctors')->default(0);
                $table->integer('total_consultations')->default(0);

                // Financial
                $table->decimal('revenue_per_patient', 12, 2)->nullable();
                $table->decimal('total_revenue', 12, 2)->default(0);
                $table->decimal('total_patients', 8, 2)->default(0);

                // Quality Metrics
                $table->decimal('mortality_rate', 5, 2)->nullable(); // %
                $table->integer('total_deaths')->default(0);
                $table->decimal('infection_rate', 5, 2)->nullable(); // HAI %
                $table->integer('total_infections')->default(0);
                $table->decimal('readmission_rate', 5, 2)->nullable(); // %
                $table->integer('total_readmissions')->default(0);

                // Surgery Metrics
                $table->decimal('surgery_cancelation_rate', 5, 2)->nullable(); // %
                $table->integer('total_surgeries')->default(0);
                $table->integer('cancelled_surgeries')->default(0);

                // Patient Satisfaction
                $table->decimal('average_satisfaction_rating', 3, 2)->nullable(); // 1-5
                $table->integer('total_surveys')->default(0);
                $table->decimal('nps_score', 5, 2)->nullable(); // Net Promoter Score

                $table->timestamps();

                $table->index('analytics_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_analytics_daily');
        Schema::dropIfExists('clinical_quality_indicators');
        Schema::dropIfExists('ministry_reports');
        Schema::dropIfExists('patient_satisfaction_surveys');
    }
};
