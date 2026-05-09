<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('tenants', 'business_type')) {
                $table->string('business_type')->nullable()->after('address')
                    ->comment('warung_makan, kafe, toko_retail, konveksi, distributor, jasa, lainnya');
            }
            if (! Schema::hasColumn('tenants', 'business_description')) {
                $table->string('business_description')->nullable()->after('business_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['business_type', 'business_description']);
        });
    }
};
