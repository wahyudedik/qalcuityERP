<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $query = Document::where('tenant_id', $tenantId)
            ->with('uploader')
            ->latest();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $documents = $query->paginate(20)->withQueryString();

        $categories = Document::where('tenant_id', $tenantId)
            ->distinct()->pluck('category')->filter()->sort()->values();

        return view('documents.index', compact('documents', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'category'    => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'file'        => 'required|file|max:20480', // 20MB
        ]);

        $file = $request->file('file');
        $path = $file->store('documents/' . auth()->user()->tenant_id, 'public');

        $doc = Document::create([
            'tenant_id'   => auth()->user()->tenant_id,
            'uploaded_by' => auth()->id(),
            'title'       => $data['title'],
            'category'    => $data['category'] ?? 'Umum',
            'description' => $data['description'] ?? null,
            'file_name'   => $file->getClientOriginalName(),
            'file_path'   => $path,
            'file_type'   => $file->getMimeType(),
            'file_size'   => $file->getSize(),
        ]);

        ActivityLog::record('document_upload', "Upload dokumen: {$doc->title}", $doc);

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function download(Document $document)
    {
        abort_if($document->tenant_id !== auth()->user()->tenant_id, 403);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        ActivityLog::record('document_download', "Download dokumen: {$document->title}", $document);

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    public function destroy(Document $document)
    {
        abort_if($document->tenant_id !== auth()->user()->tenant_id, 403);

        Storage::disk('public')->delete($document->file_path);
        ActivityLog::record('document_delete', "Hapus dokumen: {$document->title}", $document);
        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }
}
