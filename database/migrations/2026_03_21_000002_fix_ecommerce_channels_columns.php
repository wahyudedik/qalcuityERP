<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ecommerce_channels', function (Blueprint $table) {
            if (!Schema::hasColumn('ecommerce_channels', 'api_key')) {
                $table->string('api_key')->nullable()->after('shop_id');
            }
            if (!Schema::hasColumn('ecommerce_channels', 'api_secret')) {
                $table->string('api_secret')->nullable()->after('api_key');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ecommerce_channels', function (Blueprint $table) {
            $table->dropColumn(['api_key', 'api_secret']);
        });
    }
};
