<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_memories', function (Blueprint $table) {
            $table->timestamp('first_observed_at')->nullable()->after('last_seen_at');
            $table->float('confidence_score')->default(0.5)->after('first_observed_at');
            $table->json('metadata')->nullable()->after('confidence_score');
        });

        Schema::create('ai_learned_patterns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('pattern_type', 50);
            $table->string('entity_type', 50)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('pattern_data');
            $table->float('confidence')->default(0.5);
            $table->timestamp('analyzed_at')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'user_id', 'pattern_type'], 'ai_patterns_tenant_user_type_idx');
            $table->index(['tenant_id', 'entity_type', 'entity_id'], 'ai_patterns_tenant_entity_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_learned_patterns');
        Schema::table('ai_memories', function (Blueprint $table) {
            $table->dropColumn(['first_observed_at', 'confidence_score', 'metadata']);
        });
    }
};
