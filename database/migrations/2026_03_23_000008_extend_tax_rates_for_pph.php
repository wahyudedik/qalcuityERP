<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_rates', function (Blueprint $table) {
            // tax_type: ppn, pph21, pph23, pph4ayat2, custom
            $table->string('tax_type', 20)->default('ppn')->after('type');
            $table->boolean('is_withholding')->default(false)->after('tax_type'); // PPh = withholding
            $table->string('account_code', 20)->nullable()->after('is_withholding'); // GL account code
        });
    }

    public function down(): void
    {
        Schema::table('tax_rates', function (Blueprint $table) {
            $table->dropColumn(['tax_type', 'is_withholding', 'account_code']);
        });
    }
};
