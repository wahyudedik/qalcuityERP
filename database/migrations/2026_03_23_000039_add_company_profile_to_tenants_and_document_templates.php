<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tambah kolom company profile ke tenants
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('npwp', 30)->nullable()->after('email');
            $table->string('website')->nullable()->after('npwp');
            $table->string('city', 100)->nullable()->after('address');
            $table->string('province', 100)->nullable()->after('city');
            $table->string('postal_code', 10)->nullable()->after('province');
            $table->string('bank_name', 100)->nullable()->after('postal_code');
            $table->string('bank_account', 50)->nullable()->after('bank_name');
            $table->string('bank_account_name')->nullable()->after('bank_account');
            $table->string('tagline')->nullable()->after('bank_account_name');
            $table->string('stamp_image')->nullable()->after('logo');
            $table->string('director_signature')->nullable()->after('stamp_image');
            $table->text('invoice_footer_notes')->nullable()->after('director_signature');
            $table->string('invoice_payment_terms')->nullable()->after('invoice_footer_notes');
            $table->string('letter_head_color', 7)->default('#1d4ed8')->after('invoice_payment_terms');
            $table->string('doc_number_prefix', 20)->nullable()->after('letter_head_color');
        });

        // Add is_default column to document_templates if not exists
        if (!Schema::hasColumn('document_templates', 'is_default')) {
            Schema::table('document_templates', function (Blueprint $table) {
                $table->boolean('is_default')->default(false)->after('html_content');
            });
        }

        // Add tenant_id foreign key if not exists
        if (!Schema::hasColumn('document_templates', 'tenant_id')) {
            // This should not happen as table already has tenant_id, but just in case
            Schema::table('document_templates', function (Blueprint $table) {
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'npwp',
                'website',
                'city',
                'province',
                'postal_code',
                'bank_name',
                'bank_account',
                'bank_account_name',
                'tagline',
                'stamp_image',
                'director_signature',
                'invoice_footer_notes',
                'invoice_payment_terms',
                'letter_head_color',
                'doc_number_prefix',
            ]);
        });
        Schema::dropIfExists('document_templates');
    }
};
