<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom company profile ke tenants
        Schema::table('tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('tenants', 'npwp')) {
                $table->string('npwp', 30)->nullable()->after('email');
            }
            if (! Schema::hasColumn('tenants', 'website')) {
                $table->string('website')->nullable()->after('npwp');
            }
            if (! Schema::hasColumn('tenants', 'city')) {
                $table->string('city', 100)->nullable()->after('address');
            }
            if (! Schema::hasColumn('tenants', 'province')) {
                $table->string('province', 100)->nullable()->after('city');
            }
            if (! Schema::hasColumn('tenants', 'postal_code')) {
                $table->string('postal_code', 10)->nullable()->after('province');
            }
            if (! Schema::hasColumn('tenants', 'bank_name')) {
                $table->string('bank_name', 100)->nullable()->after('postal_code');
            }
            if (! Schema::hasColumn('tenants', 'bank_account')) {
                $table->string('bank_account', 50)->nullable()->after('bank_name');
            }
            if (! Schema::hasColumn('tenants', 'bank_account_name')) {
                $table->string('bank_account_name')->nullable()->after('bank_account');
            }
            if (! Schema::hasColumn('tenants', 'tagline')) {
                $table->string('tagline')->nullable()->after('bank_account_name');
            }
            if (! Schema::hasColumn('tenants', 'stamp_image')) {
                $table->string('stamp_image')->nullable()->after('logo');
            }
            if (! Schema::hasColumn('tenants', 'director_signature')) {
                $table->string('director_signature')->nullable()->after('stamp_image');
            }
            if (! Schema::hasColumn('tenants', 'invoice_footer_notes')) {
                $table->text('invoice_footer_notes')->nullable()->after('director_signature');
            }
            if (! Schema::hasColumn('tenants', 'invoice_payment_terms')) {
                $table->string('invoice_payment_terms')->nullable()->after('invoice_footer_notes');
            }
            if (! Schema::hasColumn('tenants', 'letter_head_color')) {
                $table->string('letter_head_color', 7)->default('#1d4ed8')->after('invoice_payment_terms');
            }
            if (! Schema::hasColumn('tenants', 'doc_number_prefix')) {
                $table->string('doc_number_prefix', 20)->nullable()->after('letter_head_color');
            }
        });

        // Add is_default column to document_templates if not exists
        if (! Schema::hasColumn('document_templates', 'is_default')) {
            Schema::table('document_templates', function (Blueprint $table) {
                if (! Schema::hasColumn('document_templates', 'is_default')) {
                    $table->boolean('is_default')->default(false)->after('html_content');
                }
            });
        }

        // Add tenant_id foreign key if not exists
        if (! Schema::hasColumn('document_templates', 'tenant_id')) {
            // This should not happen as table already has tenant_id, but just in case
            Schema::table('document_templates', function (Blueprint $table) {
                if (! Schema::hasColumn('document_templates', 'tenant_id')) {
                    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                }
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
