<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Services\DocumentOcrService;
use App\Services\DocumentSignatureService;
use App\Services\DocumentBulkGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    protected DocumentOcrService $ocrService;
    protected DocumentSignatureService $signatureService;
    protected DocumentBulkGeneratorService $bulkGeneratorService;

    public function __construct(
        DocumentOcrService $ocrService,
        DocumentSignatureService $signatureService,
        DocumentBulkGeneratorService $bulkGeneratorService
    ) {
        $this->ocrService = $ocrService;
        $this->signatureService = $signatureService;
        $this->bulkGeneratorService = $bulkGeneratorService;
    }

    /**
     * Display enhanced documents list
     */
    public function index(Request $request)
    {
        $query = Document::where('tenant_id', Auth::user()->tenant_id)
            ->with(['uploader:id,name,email', 'approver:id,name,email']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Special filters
        if ($request->boolean('expiring')) {
            $query->expiringSoon();
        }

        if ($request->boolean('expired')) {
            $query->expired();
        }

        if ($request->boolean('pending_approval')) {
            $query->pendingApproval();
        }

        if ($request->boolean('signed')) {
            $query->signed();
        }

        if ($request->boolean('has_ocr')) {
            $query->withOcr();
        }

        if ($request->filled('search_ocr')) {
            $query->searchOcr($request->search_ocr);
        }

        $documents = $query->latest()->paginate(20);

        // Statistics
        $statistics = [
            'total' => Document::where('tenant_id', Auth::user()->tenant_id)->count(),
            'pending_approval' => Document::where('tenant_id', Auth::user()->tenant_id)->pendingApproval()->count(),
            'approved' => Document::where('tenant_id', Auth::user()->tenant_id)->approved()->count(),
            'expired' => Document::where('tenant_id', Auth::user()->tenant_id)->expired()->count(),
            'signed' => Document::where('tenant_id', Auth::user()->tenant_id)->signed()->count(),
            'with_ocr' => Document::where('tenant_id', Auth::user()->tenant_id)->withOcr()->count(),
        ];

        return view('documents.index', compact('documents', 'statistics'));
    }

    /**
     * Upload document with metadata
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:20480',
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'expires_at' => 'nullable|date|after:today',
            'requires_approval' => 'boolean',
            'auto_ocr' => 'boolean',
        ]);

        $file = $validated['file'];
        $path = $file->store('documents/' . Auth::user()->tenant_id, 'public');

        $document = Document::create([
            'tenant_id' => Auth::user()->tenant_id,
            'uploaded_by' => Auth::id(),
            'title' => $validated['title'],
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'category' => $validated['category'] ?? 'general',
            'description' => $validated['description'] ?? '',
            'tags' => $validated['tags'] ?? [],
            'version' => 1,
            'status' => 'draft',
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        // Auto OCR if requested
        if ($request->boolean('auto_ocr')) {
            $this->ocrService->processDocument($document);
        }

        // Auto submit for approval if requested
        if ($request->boolean('requires_approval')) {
            // Will be handled by approval service
        }

        return redirect()->route('documents.index')
            ->with('success', 'Document uploaded successfully');
    }

    /**
     * Process OCR for document
     */
    public function processOcr(Document $document)
    {
        $this->authorize('update', $document);

        $success = $this->ocrService->processDocument($document);

        if ($success) {
            return redirect()->back()
                ->with('success', 'OCR processing completed');
        }

        return redirect()->back()
            ->with('error', 'OCR processing failed');
    }

    /**
     * Sign document
     */
    public function sign(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'signature_type' => 'required|in:electronic,digital',
            'certificate_serial' => 'nullable|string|max:100',
        ]);

        $signature = $this->signatureService->signDocument(
            $document,
            $validated['signature_type'],
            [
                'certificate_serial' => $validated['certificate_serial'] ?? null,
            ]
        );

        return redirect()->back()
            ->with('success', 'Document signed successfully');
    }

    /**
     * Verify document signature
     */
    public function verifySignature(Document $document)
    {
        $this->authorize('view', $document);

        $results = $this->signatureService->verifyAllSignatures($document);

        return response()->json([
            'document' => $document->id,
            'is_signed' => $document->is_signed,
            'signatures' => $results,
        ]);
    }

    /**
     * Search documents by OCR content
     */
    public function searchOcr(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:3',
        ]);

        $results = $this->ocrService->searchByOcrContent(
            Auth::user()->tenant_id,
            $validated['q']
        );

        return response()->json($results);
    }

    /**
     * Get expired documents
     */
    public function expired(Request $request)
    {
        $documents = Document::where('tenant_id', Auth::user()->tenant_id)
            ->expired()
            ->with('uploader:id,name,email')
            ->latest('expires_at')
            ->paginate(20);

        return view('documents.expired-documents', compact('documents'));
    }

    /**
     * Get expiring soon documents
     */
    public function expiringSoon(Request $request)
    {
        $days = $request->get('days', 30);

        $documents = Document::where('tenant_id', Auth::user()->tenant_id)
            ->expiringSoon($days)
            ->with('uploader:id,name,email')
            ->orderBy('expires_at')
            ->paginate(20);

        return view('documents.expiring-soon', compact('documents', 'days'));
    }

    /**
     * Bulk sign documents
     */
    public function bulkSign(Request $request)
    {
        $validated = $request->validate([
            'document_ids' => 'required|array|min:1',
            'document_ids.*' => 'exists:documents,id',
            'signature_type' => 'required|in:electronic,digital',
        ]);

        // Verify all documents belong to tenant
        $documentIds = Document::whereIn('id', $validated['document_ids'])
            ->where('tenant_id', Auth::user()->tenant_id)
            ->pluck('id')
            ->toArray();

        $results = $this->signatureService->bulkSignDocuments(
            $documentIds,
            $validated['signature_type']
        );

        return redirect()->back()
            ->with('success', "Successfully signed {$results['success']} document(s)")
            ->with('error', $results['failed'] > 0 ? "Failed to sign {$results['failed']} document(s)" : null);
    }

    /**
     * Get OCR statistics
     */
    public function ocrStatistics()
    {
        $statistics = $this->ocrService->getOcrStatistics(Auth::user()->tenant_id);

        return response()->json($statistics);
    }

    /**
     * Get signature statistics
     */
    public function signatureStatistics()
    {
        $statistics = $this->signatureService->getSignatureStatistics(Auth::user()->tenant_id);

        return response()->json($statistics);
    }

    /**
     * Bulk generate documents from template
     */
    public function bulkGenerate(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:document_templates,id',
            'data' => 'required|array|min:1',
            'output_format' => 'required|in:pdf,docx',
        ]);

        $template = DocumentTemplate::findOrFail($validated['template_id']);

        $results = $this->bulkGeneratorService->generateFromTemplate(
            $template,
            $validated['data'],
            $validated['output_format']
        );

        return redirect()->back()
            ->with('success', "Generated {$results['success']} document(s)")
            ->with('error', $results['failed'] > 0 ? "Failed to generate {$results['failed']} document(s)" : null);
    }

    /**
     * Preview template with data
     */
    public function previewTemplate(Request $request, DocumentTemplate $template)
    {
        $validated = $request->validate([
            'data' => 'required|array',
        ]);

        $preview = $this->bulkGeneratorService->previewTemplate($template, $validated['data']);

        return response()->json(['preview' => $preview]);
    }

    /**
     * Download document file
     */
    public function download(Document $document)
    {
        $this->authorize('view', $document);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found');
        }

        $filePath = Storage::disk('public')->path($document->file_path);

        return response()->download($filePath, $document->file_name);
    }

    /**
     * Delete document
     */
    public function destroy(Document $document)
    {
        $this->authorize('delete', $document);

        // Delete file from storage
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Document deleted successfully');
    }

    /**
     * Get bulk generation statistics
     */
    public function bulkGenerationStats()
    {
        $statistics = $this->bulkGeneratorService->getBulkGenerationStats(Auth::user()->tenant_id);

        return response()->json($statistics);
    }
}
