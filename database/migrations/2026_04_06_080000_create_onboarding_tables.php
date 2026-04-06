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
        // 1. Onboarding Profiles (User's industry selection & preferences)
        Schema::create('onboarding_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('industry'); // retail, restaurant, hotel, construction, agriculture, manufacturing, services
            $table->string('business_size'); // micro, small, medium, large
            $table->integer('employee_count')->nullable();
            $table->json('selected_modules')->nullable(); // Modules user wants to use
            $table->json('preferences')->nullable(); // User preferences
            $table->boolean('sample_data_generated')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'user_id']);
            $table->index('industry');
        });

        // 2. Onboarding Progress Tracker
        Schema::create('onboarding_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('step_key'); // unique identifier for step
            $table->string('step_name'); // display name
            $table->string('category'); // setup, configuration, first_action, etc
            $table->boolean('completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->integer('order')->default(0); // display order
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // additional data
            $table->timestamps();

            $table->unique(['tenant_id', 'user_id', 'step_key']);
            $table->index(['tenant_id', 'completed']);
        });

        // 3. Sample Data Templates
        Schema::create('sample_data_templates', function (Blueprint $table) {
            $table->id();
            $table->string('industry'); // which industry this template is for
            $table->string('template_name'); // e.g., "Basic Retail Setup"
            $table->text('description')->nullable();
            $table->json('modules_included')->nullable(); // which modules to populate
            $table->json('data_config')->nullable(); // configuration for data generation
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->timestamps();

            $table->index('industry');
        });

        // 4. Sample Data Generation Logs
        Schema::create('sample_data_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('template_id')->nullable()->constrained('sample_data_templates')->onDelete('set null');
            $table->string('status'); // pending, processing, completed, failed
            $table->json('generated_data')->nullable(); // what was generated
            $table->integer('records_created')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        // 5. AI Tour Sessions
        Schema::create('ai_tour_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('tour_type'); // general, module_specific, feature_highlight
            $table->string('current_step')->nullable(); // current tour step
            $table->json('completed_steps')->nullable(); // steps user has completed
            $table->boolean('is_active')->default(true);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
        });

        // 6. User Tips & Hints
        Schema::create('user_tips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('tip_category'); // quick_start, best_practice, shortcut, etc
            $table->string('tip_title');
            $table->text('tip_content');
            $table->string('related_module')->nullable();
            $table->boolean('dismissed')->default(false);
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamp('shown_at')->nullable();
            $table->integer('times_shown')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'dismissed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_tips');
        Schema::dropIfExists('ai_tour_sessions');
        Schema::dropIfExists('sample_data_logs');
        Schema::dropIfExists('sample_data_templates');
        Schema::dropIfExists('onboarding_progress');
        Schema::dropIfExists('onboarding_profiles');
    }
};
