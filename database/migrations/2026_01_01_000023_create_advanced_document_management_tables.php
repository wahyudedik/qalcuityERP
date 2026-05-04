<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations for advanced document management features.
     */
    public function up(): void
    {
        // Add versioning & workflow columns to documents table
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'version')) {
                $table->integer('version')->default(1)->after('tags');
            }
            if (!Schema::hasColumn('documents', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('version')
                    ->comment('Parent document ID for versioning');
            }
            if (!Schema::hasColumn('documents', 'status')) {
                $table->string('status')->default('draft')->after('parent_id')
                    ->comment('draft, pending_approval, approved, rejected, archived');
            }
            if (!Schema::hasColumn('documents', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('status');
            }
            if (!Schema::hasColumn('documents', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('documents', 'approval_notes')) {
                $table->text('approval_notes')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('documents', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('approval_notes');
            }
            if (!Schema::hasColumn('documents', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('expires_at');
            }
            if (!Schema::hasColumn('documents', 'storage_provider')) {
                $table->string('storage_provider')->default('local')->after('archived_at')
                    ->comment('local, s3, gcs, azure');
            }
            if (!Schema::hasColumn('documents', 'storage_bucket')) {
                $table->string('storage_bucket')->nullable()->after('storage_provider');
            }
            if (!Schema::hasColumn('documents', 'ocr_text')) {
                $table->string('ocr_text')->nullable()->after('storage_bucket')
                    ->comment('Extracted text from OCR');
            }
            if (!Schema::hasColumn('documents', 'has_ocr')) {
                $table->boolean('has_ocr')->default(false)->after('ocr_text');
            }
            if (!Schema::hasColumn('documents', 'digital_signature')) {
                $table->string('digital_signature')->nullable()->after('has_ocr')->comment('Digital signature hash');
            }
            if (!Schema::hasColumn('documents', 'is_signed')) {
                $table->boolean('is_signed')->default(false)->after('digital_signature');
            }
            if (!Schema::hasColumn('documents', 'signed_at')) {
                $table->timestamp('signed_at')->nullable()->after('is_signed');
            }

            $table->foreign('parent_id')->references('id')->on('documents')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['status', 'tenant_id']);
            $table->index(['expires_at']);
            $table->index(['storage_provider']);
        });

        // Document versions history table
        if (!Schema::hasTable('document_versions')) {
            Schema::create('document_versions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('document_id');
                $table->integer('version');
                $table->string('file_name');
                $table->string('file_path');
                $table->unsignedBigInteger('file_size');
                $table->unsignedBigInteger('changed_by');
                $table->text('change_summary')->nullable();
                $table->timestamps();
    
                $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
                $table->foreign('changed_by')->references('id')->on('users')->onDelete('cascade');
                $table->unique(['document_id', 'version']);
                $table->index(['document_id', 'version']);
            });
        }

        // Document approval workflows
        if (!Schema::hasTable('document_approval_workflows')) {
            Schema::create('document_approval_workflows', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('document_type')->nullable()->comment('Specific document category');
                $table->json('approval_steps'); // [{order, user_id/role_id, required}]
                $table->boolean('is_active')->default(true);
                $table->timestamps();
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->index(['tenant_id', 'is_active']);
            });
        }

        // Document approval requests
        if (!Schema::hasTable('document_approval_requests')) {
            Schema::create('document_approval_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('document_id');
                $table->unsignedBigInteger('workflow_id');
                $table->integer('step_number');
                $table->unsignedBigInteger('approver_id')->nullable();
                $table->string('approver_role')->nullable();
                $table->string('status')->default('pending'); // pending, approved, rejected, skipped
                $table->text('comments')->nullable();
                $table->timestamp('actioned_at')->nullable();
                $table->timestamps();
    
                $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
                $table->foreign('workflow_id')->references('id')->on('document_approval_workflows')->onDelete('cascade');
                $table->foreign('approver_id')->references('id')->on('users')->onDelete('set null');
                $table->index(['document_id', 'status']);
                $table->index(['approver_id', 'status']);
            });
        }

        // Document templates enhanced
        Schema::table('document_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('document_templates', 'category')) {
                $table->string('category')->nullable()->after('doc_type');
            }
            if (!Schema::hasColumn('document_templates', 'variables')) {
                $table->json('variables')->nullable()->after('html_content')
                    ->comment('Available template variables');
            }
            if (!Schema::hasColumn('document_templates', 'sample_data')) {
                $table->json('sample_data')->nullable()->after('variables');
            }
            if (!Schema::hasColumn('document_templates', 'requires_approval')) {
                $table->boolean('requires_approval')->default(false)->after('sample_data');
            }
            if (!Schema::hasColumn('document_templates', 'approval_workflow_id')) {
                $table->unsignedBigInteger('approval_workflow_id')->nullable()->after('requires_approval');
            }
            if (!Schema::hasColumn('document_templates', 'validity_days')) {
                $table->integer('validity_days')->nullable()->after('approval_workflow_id')
                    ->comment('Document expiry after generation');
            }

            $table->foreign('approval_workflow_id')->references('id')->on('document_approval_workflows')->onDelete('set null');
        });

        // Cloud storage configurations
        if (!Schema::hasTable('tenant_storage_configs')) {
            Schema::create('tenant_storage_configs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('provider'); // s3, gcs, azure
                $table->string('bucket_name');
                $table->string('region')->nullable();
                $table->string('access_key')->nullable();
                $table->string('secret_key')->nullable();
                $table->string('endpoint')->nullable();
                $table->json('additional_config')->nullable();
                $table->boolean('is_active')->default(false);
                $table->boolean('is_default')->default(false);
                $table->timestamps();
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->unique(['tenant_id', 'provider']);
                $table->index(['tenant_id', 'is_active']);
            });
        }

        // Digital signatures
        if (!Schema::hasTable('document_signatures')) {
            Schema::create('document_signatures', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('document_id');
                $table->unsignedBigInteger('signer_id');
                $table->string('signature_type')->default('digital'); // digital, electronic, wet
                $table->string('signature_hash');
                $table->string('certificate_serial')->nullable();
                $table->text('signature_metadata')->nullable();
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamp('signed_at');
                $table->timestamps();
    
                $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
                $table->foreign('signer_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['document_id', 'signer_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_signatures');
        Schema::dropIfExists('tenant_storage_configs');
        Schema::table('document_templates', function (Blueprint $table) {
            $table->dropForeign(['approval_workflow_id']);
            $table->dropColumn(['category', 'variables', 'sample_data', 'requires_approval', 'approval_workflow_id', 'validity_days']);
        });
        Schema::dropIfExists('document_approval_requests');
        Schema::dropIfExists('document_approval_workflows');
        Schema::dropIfExists('document_versions');
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'version',
                'parent_id',
                'status',
                'approved_by',
                'approved_at',
                'approval_notes',
                'expires_at',
                'archived_at',
                'storage_provider',
                'storage_bucket',
                'ocr_text',
                'has_ocr',
                'digital_signature',
                'is_signed',
                'signed_at'
            ]);
        });
    }
};
