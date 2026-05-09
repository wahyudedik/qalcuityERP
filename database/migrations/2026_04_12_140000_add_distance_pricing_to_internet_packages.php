<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internet_packages', function (Blueprint $table) {
            if (! Schema::hasColumn('internet_packages', 'use_distance_pricing')) {
                $table->boolean('use_distance_pricing')->default(false)->after('price');
            }
            if (! Schema::hasColumn('internet_packages', 'base_distance_km')) {
                $table->decimal('base_distance_km', 8, 2)->nullable()->after('use_distance_pricing')
                    ->comment('Base distance included in package price (km)');
            }
            if (! Schema::hasColumn('internet_packages', 'price_per_km')) {
                $table->decimal('price_per_km', 10, 2)->nullable()->after('base_distance_km')
                    ->comment('Additional price per km beyond base distance');
            }
            if (! Schema::hasColumn('internet_packages', 'max_distance_km')) {
                $table->decimal('max_distance_km', 8, 2)->nullable()->after('price_per_km')
                    ->comment('Maximum coverage distance (km)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('internet_packages', function (Blueprint $table) {
            $table->dropColumn(['use_distance_pricing', 'base_distance_km', 'price_per_km', 'max_distance_km']);
        });
    }
};
