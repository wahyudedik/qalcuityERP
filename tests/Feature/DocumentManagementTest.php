<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_it_can_access_documents_index_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('documents.index'));

        $response->assertStatus(200);
        $response->assertViewIs('documents.index');
    }

    public function test_it_can_upload_a_document()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('test-document.pdf', 100);

        $response = $this->actingAs($this->user)
            ->post(route('documents.store'), [
                'title' => 'Test Document',
                'category' => 'invoice',
                'document_date' => now()->format('Y-m-d'),
                'file' => $file,
            ]);

        $response->assertRedirect(route('documents.index'));
        $this->assertDatabaseHas('documents', [
            'title' => 'Test Document',
            'category' => 'invoice',
        ]);
    }

    public function test_it_can_view_document_versions()
    {
        $document = Document::create([
            'tenant_id' => $this->user->tenant_id,
            'title' => 'Test Document',
            'category' => 'contract',
            'file_path' => 'documents/test.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('documents.versions.index', $document));

        $response->assertStatus(200);
        $response->assertViewIs('documents.versions');
    }

    public function test_it_can_access_approval_workflow_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('documents.workflows.index'));

        $response->assertStatus(200);
    }

    public function test_it_can_access_templates_index()
    {
        $response = $this->actingAs($this->user)
            ->get(route('documents.templates.index'));

        $response->assertStatus(200);
        $response->assertViewIs('documents.templates.index');
    }

    public function test_it_can_access_bulk_generate_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('documents.bulk-generate'));

        $response->assertStatus(200);
        $response->assertViewIs('documents.bulk-generate');
    }

    public function test_it_can_access_expired_documents_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('documents.expired'));

        $response->assertStatus(200);
        $response->assertViewIs('documents.expired-documents');
    }

    public function test_it_can_search_documents()
    {
        Document::create([
            'tenant_id' => $this->user->tenant_id,
            'title' => 'Unique Test Document',
            'category' => 'invoice',
            'file_path' => 'documents/test.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('documents.index', ['search' => 'Unique Test Document']));

        $response->assertStatus(200);
        $response->assertSee('Unique Test Document');
    }

    public function test_it_can_filter_documents_by_category()
    {
        Document::create([
            'tenant_id' => $this->user->tenant_id,
            'title' => 'Invoice Document',
            'category' => 'invoice',
            'file_path' => 'documents/invoice.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'created_by' => $this->user->id,
        ]);

        Document::create([
            'tenant_id' => $this->user->tenant_id,
            'title' => 'Contract Document',
            'category' => 'contract',
            'file_path' => 'documents/contract.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 2048,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('documents.index', ['category' => 'invoice']));

        $response->assertStatus(200);
        $response->assertSee('Invoice Document');
        $response->assertDontSee('Contract Document');
    }
}
