<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('insight_reads')) {
            Schema::create('insight_reads', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('insight_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('status');                // read | dismissed | handled
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('insight_reads');
    }
};
