<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('zero_input_logs')) {
            Schema::create('zero_input_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('channel');          // photo, voice, whatsapp
                $table->string('status');           // processing, mapped, failed
                $table->string('mapped_module')->nullable(); // invoice, expense, product, etc.
                $table->json('extracted_data')->nullable();  // data hasil OCR/AI
                $table->json('created_records')->nullable(); // record yang berhasil dibuat
                $table->text('raw_input')->nullable();       // teks mentah dari voice/WA
                $table->string('file_path')->nullable();     // path foto/file
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('zero_input_logs');
    }
};
