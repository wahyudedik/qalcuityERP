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
        // 1. Voice Commands History
        if (!Schema::hasTable('voice_commands')) {
            Schema::create('voice_commands', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->text('audio_path')->nullable(); // Path to audio file
                $table->text('transcribed_text'); // Speech-to-text result
                $table->string('intent')->nullable(); // Detected intent
                $table->json('extracted_entities')->nullable(); // Extracted data
                $table->string('status')->default('processed'); // processed, executed, failed
                $table->json('execution_result')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'user_id']);
                $table->index('created_at');
            });
        }

        // 2. Image Recognition Results
        if (!Schema::hasTable('image_recognition_results')) {
            Schema::create('image_recognition_results', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('image_path'); // Path to analyzed image
                $table->string('recognition_type'); // product_detection, damage_assessment, ocr
                $table->json('detected_objects')->nullable(); // Detected items with confidence
                $table->json('labels')->nullable(); // Image labels/tags
                $table->decimal('confidence_score', 5, 4)->nullable(); // Overall confidence
                $table->json('metadata')->nullable(); // Additional analysis data
                $table->text('description')->nullable(); // AI-generated description
                $table->boolean('verified')->default(false); // User verified results
                $table->timestamps();

                $table->index(['tenant_id', 'recognition_type']);
                $table->index('created_at');
            });
        }

        // 3. Predictive Maintenance Records
        if (!Schema::hasTable('predictive_maintenance')) {
            Schema::create('predictive_maintenance', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
                $table->string('prediction_type'); // failure_probability, maintenance_due, lifespan
                $table->decimal('probability', 5, 4); // 0-1 probability
                $table->date('predicted_date')->nullable(); // When issue predicted
                $table->string('severity')->default('medium'); // low, medium, high, critical
                $table->json('contributing_factors')->nullable(); // What factors led to prediction
                $table->json('recommendations')->nullable(); // Suggested actions
                $table->string('status')->default('pending'); // pending, scheduled, completed, dismissed
                $table->date('scheduled_date')->nullable();
                $table->foreignId('scheduled_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'asset_id']);
                $table->index(['tenant_id', 'status']);
                $table->index('predicted_date');
            });
        }

        // 4. Dynamic Pricing Rules & History
        if (!Schema::hasTable('dynamic_pricing_rules')) {
            Schema::create('dynamic_pricing_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->text('description')->nullable();
                $table->json('conditions')->nullable(); // When to apply rule
                $table->json('pricing_formula')->nullable(); // How to calculate price
                $table->decimal('min_price_multiplier', 5, 2)->default(0.8); // Min 80% of base
                $table->decimal('max_price_multiplier', 5, 2)->default(1.5); // Max 150% of base
                $table->boolean('is_active')->default(true);
                $table->integer('priority')->default(0); // Higher priority rules applied first
                $table->timestamps();

                $table->index(['tenant_id', 'is_active']);
            });
        }

        if (!Schema::hasTable('dynamic_pricing_history')) {
            Schema::create('dynamic_pricing_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->foreignId('rule_id')->nullable()->constrained('dynamic_pricing_rules')->onDelete('set null');
                $table->decimal('original_price', 15, 2);
                $table->decimal('recommended_price', 15, 2);
                $table->decimal('applied_price', 15, 2);
                $table->json('factors')->nullable(); // Demand, competition, seasonality, etc
                $table->string('reason')->nullable(); // Why price changed
                $table->boolean('approved')->default(false); // Manual approval required
                $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'product_id']);
                $table->index('created_at');
            });
        }

        // 5. Sentiment Analysis Results
        if (!Schema::hasTable('sentiment_analysis')) {
            Schema::create('sentiment_analysis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('source_type'); // review, feedback, survey, social_media
                $table->unsignedBigInteger('source_id')->nullable(); // ID of source record
                $table->text('content'); // Text analyzed
                $table->string('sentiment'); // positive, negative, neutral
                $table->decimal('confidence', 5, 4); // Confidence score
                $table->decimal('polarity', 6, 4)->nullable(); // -1 to 1 scale
                $table->decimal('subjectivity', 6, 4)->nullable(); // 0 to 1 scale
                $table->json('emotions')->nullable(); // joy, anger, sadness, fear, surprise
                $table->json('key_phrases')->nullable(); // Important phrases extracted
                $table->json('topics')->nullable(); // Topics discussed
                $table->boolean('requires_attention')->default(false); // Negative sentiment flag
                $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('status')->default('new'); // new, reviewed, resolved
                $table->text('response_suggestion')->nullable(); // AI-suggested response
                $table->timestamps();

                $table->index(['tenant_id', 'sentiment']);
                $table->index(['tenant_id', 'requires_attention']);
                $table->index('created_at');
            });
        }

        // 6. Chatbot Training Data
        if (!Schema::hasTable('chatbot_training_data')) {
            Schema::create('chatbot_training_data', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('category'); // sales, support, inventory, finance, etc
                $table->text('question'); // User question
                $table->text('answer'); // Expected answer
                $table->json('context')->nullable(); // Additional context
                $table->json('keywords')->nullable(); // Extracted keywords
                $table->json('intents')->nullable(); // Detected intents
                $table->integer('usage_count')->default(0); // How many times used
                $table->decimal('effectiveness_score', 5, 4)->nullable(); // User rating
                $table->boolean('is_verified')->default(false); // Admin verified
                $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();

                $table->index(['tenant_id', 'category']);
                $table->index(['tenant_id', 'is_verified']);
                $table->fullText(['question', 'answer']);
            });
        }

        // 7. Chatbot Conversation Logs (for learning)
        if (!Schema::hasTable('chatbot_conversations')) {
            Schema::create('chatbot_conversations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->text('user_message');
                $table->text('bot_response');
                $table->string('intent_detected')->nullable();
                $table->decimal('confidence_score', 5, 4)->nullable();
                $table->boolean('was_helpful')->nullable(); // User feedback
                $table->text('feedback_notes')->nullable();
                $table->json('context')->nullable(); // Conversation context
                $table->timestamps();

                $table->index(['tenant_id', 'user_id']);
                $table->index('created_at');
            });
        }

        // 8. AI Model Performance Tracking
        if (!Schema::hasTable('ai_model_performance')) {
            Schema::create('ai_model_performance', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('model_name'); // voice_recognition, image_ai, pricing_engine, etc
                $table->string('metric_name'); // accuracy, precision, recall, f1_score, latency
                $table->decimal('metric_value', 10, 4);
                $table->json('metadata')->nullable(); // Additional metrics
                $table->date('measured_at');
                $table->timestamps();

                // Custom name to avoid MySQL 64 char limit
                $table->unique(['tenant_id', 'model_name', 'metric_name', 'measured_at'], 'ai_model_perf_unique');
                $table->index(['tenant_id', 'model_name']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_model_performance');
        Schema::dropIfExists('chatbot_conversations');
        Schema::dropIfExists('chatbot_training_data');
        Schema::dropIfExists('sentiment_analysis');
        Schema::dropIfExists('dynamic_pricing_history');
        Schema::dropIfExists('dynamic_pricing_rules');
        Schema::dropIfExists('predictive_maintenance');
        Schema::dropIfExists('image_recognition_results');
        Schema::dropIfExists('voice_commands');
    }
};
