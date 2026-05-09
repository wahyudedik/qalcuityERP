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
        if (! Schema::hasTable('patient_allergies')) {
            Schema::create('patient_allergies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');

                // Allergy Details
                $table->string('allergen'); // Penicillin, Peanuts, Latex, etc.
                $table->string('allergen_type'); // medication, food, environmental, other
                $table->enum('severity', ['mild', 'moderate', 'severe', 'life_threatening']);
                $table->text('reaction_description')->nullable(); // Symptoms when exposed
                $table->text('treatment_if_exposed')->nullable(); // What to do if exposed

                // Diagnosis & Verification
                $table->date('diagnosed_date')->nullable();
                $table->foreignId('diagnosed_by')->nullable()->constrained('users')->onDelete('set null');
                $table->string('diagnosis_method')->nullable(); // self_reported, skin_test, blood_test, clinical

                // Status
                $table->boolean('is_active')->default(true);
                $table->boolean('is_verified')->default(false);

                // Standard fields
                $table->text('notes')->nullable();
                $table->timestamps();

                // Indexes
                $table->index('patient_id');
                $table->index('allergen');
                $table->index('severity');
                $table->index('is_active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_allergies');
    }
};
