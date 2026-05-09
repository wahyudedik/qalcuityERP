<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reminders')) {
            Schema::create('reminders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('user_id');
                $table->string('title');
                $table->text('notes')->nullable();
                $table->dateTime('remind_at');
                $table->enum('status', ['pending', 'sent', 'dismissed'])->default('pending');
                $table->enum('channel', ['in_app', 'email', 'both'])->default('both');
                $table->string('related_type')->nullable(); // e.g. 'payable', 'lead', 'customer'
                $table->unsignedBigInteger('related_id')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'status', 'remind_at']);
                $table->index(['user_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
