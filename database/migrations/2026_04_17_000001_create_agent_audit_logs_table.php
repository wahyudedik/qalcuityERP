<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('agent_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('session_id')->nullable()->index();
            $table->string('action_name');
            $table->string('action_type');           // read | write
            $table->json('parameters');
            $table->json('result');
            $table->string('status');                // success | failed | undone
            $table->text('error_message')->nullable();
            $table->boolean('is_undoable')->default(false);
            $table->timestamp('undoable_until')->nullable();
            $table->timestamps();
            // NO softDeletes - audit log tidak boleh dihapus (Requirement 9.4)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_audit_logs');
    }
};
