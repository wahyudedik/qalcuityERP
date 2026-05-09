<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentVersion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Document Versioning Service
 *
 * Handles document version control, version history, and rollback functionality.
 */
class DocumentVersioningService
{
    /**
     * Create a new version of a document
     */
    public function createVersion(Document $document, array $data): DocumentVersion
    {
        return DB::transaction(function () use ($document, $data) {
            // Store new file if provided
            $fileData = $this->handleFileUpload($document, $data['file'] ?? null);

            // Create version record
            $version = $document->versions()->create([
                'version' => $document->version + 1,
                'file_name' => $fileData['file_name'] ?? $data['file_name'] ?? $document->file_name,
                'file_path' => $fileData['file_path'] ?? $data['file_path'] ?? $document->file_path,
                'file_size' => $fileData['file_size'] ?? $data['file_size'] ?? $document->file_size,
                'changed_by' => $data['changed_by'] ?? Auth::id(),
                'change_summary' => $data['change_summary'] ?? '',
            ]);

            // Update current document
            $document->update([
                'version' => $version->version,
                'file_name' => $version->file_name,
                'file_path' => $version->file_path,
                'file_size' => $version->file_size,
            ]);

            return $version;
        });
    }

    /**
     * Rollback to a specific version
     */
    public function rollbackToVersion(Document $document, int $versionNumber): bool
    {
        return DB::transaction(function () use ($document, $versionNumber) {
            $targetVersion = $document->versions()
                ->where('version', $versionNumber)
                ->firstOrFail();

            // Create new version with old file data
            $newVersion = $this->createVersion($document, [
                'file_name' => $targetVersion->file_name,
                'file_path' => $targetVersion->file_path,
                'file_size' => $targetVersion->file_size,
                'change_summary' => "Rollback to version {$versionNumber}",
            ]);

            return true;
        });
    }

    /**
     * Get version history
     */
    public function getVersionHistory(Document $document, int $limit = 50): array
    {
        $versions = $document->versions()
            ->with('changer:id,name,email')
            ->latestFirst()
            ->limit($limit)
            ->get()
            ->map(function ($version) {
                return [
                    'version' => $version->version,
                    'file_name' => $version->file_name,
                    'file_size_human' => $version->file_size_human,
                    'changed_by' => $version->changer?->name ?? 'Unknown',
                    'change_summary' => $version->change_summary,
                    'created_at' => $version->created_at->format('d M Y H:i'),
                ];
            });

        return [
            'current_version' => $document->version,
            'total_versions' => $document->versions()->count(),
            'versions' => $versions,
        ];
    }

    /**
     * Compare two versions
     */
    public function compareVersions(Document $document, int $version1, int $version2): array
    {
        $v1 = $document->versions()->where('version', $version1)->firstOrFail();
        $v2 = $document->versions()->where('version', $version2)->firstOrFail();

        return [
            'version_1' => [
                'version' => $v1->version,
                'file_name' => $v1->file_name,
                'file_size' => $v1->file_size,
                'file_size_human' => $v1->file_size_human,
                'created_at' => $v1->created_at->format('d M Y H:i'),
                'changed_by' => $v1->changer?->name ?? 'Unknown',
            ],
            'version_2' => [
                'version' => $v2->version,
                'file_name' => $v2->file_name,
                'file_size' => $v2->file_size,
                'file_size_human' => $v2->file_size_human,
                'created_at' => $v2->created_at->format('d M Y H:i'),
                'changed_by' => $v2->changer?->name ?? 'Unknown',
            ],
            'differences' => [
                'file_name_changed' => $v1->file_name !== $v2->file_name,
                'file_size_changed' => $v1->file_size !== $v2->file_size,
                'size_difference' => $v2->file_size - $v1->file_size,
            ],
        ];
    }

    /**
     * Delete old versions (keep last N versions)
     */
    public function cleanupOldVersions(Document $document, int $keepVersions = 10): int
    {
        $versionsToDelete = $document->versions()
            ->latestFirst()
            ->offset($keepVersions)
            ->get();

        $count = 0;
        foreach ($versionsToDelete as $version) {
            // Delete file from storage
            if (Storage::exists($version->file_path)) {
                Storage::delete($version->file_path);
            }

            $version->delete();
            $count++;
        }

        return $count;
    }

    /**
     * Get version by number
     */
    public function getVersion(Document $document, int $versionNumber): ?DocumentVersion
    {
        return $document->versions()
            ->with('changer:id,name,email')
            ->where('version', $versionNumber)
            ->first();
    }

    /**
     * Download specific version
     */
    public function downloadVersion(DocumentVersion $version)
    {
        if (! Storage::exists($version->file_path)) {
            abort(404, 'Version file not found');
        }

        return Storage::download($version->file_path, $version->file_name);
    }

    /**
     * Handle file upload for new version
     */
    protected function handleFileUpload(Document $document, ?UploadedFile $file = null): array
    {
        if (! $file) {
            return [];
        }

        $path = $file->store('documents/'.$document->tenant_id, 'public');

        return [
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
        ];
    }

    /**
     * Get version statistics
     */
    public function getVersionStatistics(Document $document): array
    {
        $versions = $document->versions;

        if ($versions->isEmpty()) {
            return [
                'total_versions' => 0,
                'avg_file_size' => 0,
                'total_storage' => 0,
                'last_updated' => null,
            ];
        }

        return [
            'total_versions' => $versions->count(),
            'avg_file_size' => $versions->avg('file_size'),
            'total_storage' => $versions->sum('file_size'),
            'last_updated' => $versions->first()->created_at->format('d M Y H:i'),
            'most_active_editor' => $versions->groupBy('changed_by')
                ->sortByDesc(fn ($group) => $group->count())
                ->keys()
                ->first(),
        ];
    }
}
