<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentVersioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentVersionController extends Controller
{
    protected DocumentVersioningService $versioningService;

    public function __construct(DocumentVersioningService $versioningService)
    {
        $this->versioningService = $versioningService;
    }

    /**
     * Display version history
     */
    public function index(Document $document)
    {
        $this->authorize('view', $document);

        $history = $this->versioningService->getVersionHistory($document);
        $statistics = $this->versioningService->getVersionStatistics($document);

        return view('documents.versions', compact('document', 'history', 'statistics'));
    }

    /**
     * Get version history via API
     */
    public function getVersions(Document $document)
    {
        $this->authorize('view', $document);

        $history = $this->versioningService->getVersionHistory($document);

        return response()->json($history);
    }

    /**
     * Create new version
     */
    public function store(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'file' => 'nullable|file|max:10240',
            'file_name' => 'nullable|string|max:255',
            'change_summary' => 'required|string|max:500',
        ]);

        $version = $this->versioningService->createVersion($document, $validated);

        return redirect()->back()
            ->with('success', 'Document version created successfully (v' . $version->version . ')');
    }

    /**
     * Rollback to specific version
     */
    public function rollback(Request $request, Document $document, int $versionNumber)
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'confirm' => 'required|accepted',
        ]);

        $this->versioningService->rollbackToVersion($document, $versionNumber);

        return redirect()->back()
            ->with('success', "Document rolled back to version {$versionNumber}");
    }

    /**
     * Compare two versions
     */
    public function compare(Document $document, int $version1, int $version2)
    {
        $this->authorize('view', $document);

        $comparison = $this->versioningService->compareVersions($document, $version1, $version2);

        return response()->json($comparison);
    }

    /**
     * Download specific version
     */
    public function download(Document $document, int $versionNumber)
    {
        $this->authorize('view', $document);

        $version = $this->versioningService->getVersion($document, $versionNumber);

        if (!$version) {
            abort(404, 'Version not found');
        }

        return $this->versioningService->downloadVersion($version);
    }

    /**
     * Get version statistics
     */
    public function statistics(Document $document)
    {
        $this->authorize('view', $document);

        $statistics = $this->versioningService->getVersionStatistics($document);

        return response()->json($statistics);
    }

    /**
     * Cleanup old versions
     */
    public function cleanup(Request $request, Document $document)
    {
        $this->authorize('delete', $document);

        $validated = $request->validate([
            'keep_versions' => 'required|integer|min:1|max:100',
        ]);

        $deleted = $this->versioningService->cleanupOldVersions(
            $document,
            $validated['keep_versions']
        );

        return redirect()->back()
            ->with('success', "Deleted {$deleted} old version(s)");
    }
}
