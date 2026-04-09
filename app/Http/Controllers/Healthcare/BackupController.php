<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\BackupLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index(Request $request)
    {
        $query = BackupLog::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('backup_type')) {
            $query->where('backup_type', $request->backup_type);
        }

        $backups = $query->orderBy('created_at', 'desc')->paginate(20);

        $statistics = [
            'total' => BackupLog::count(),
            'completed' => BackupLog::where('status', 'completed')->count(),
            'failed' => BackupLog::where('status', 'failed')->count(),
            'total_size_mb' => round(BackupLog::sum('size_bytes') / 1048576, 2),
        ];

        return view('healthcare.backups.index', compact('backups', 'statistics'));
    }

    public function create()
    {
        return view('healthcare.backups.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'backup_type' => 'required|in:full,database,files',
            'notes' => 'nullable|string',
        ]);

        $backup = BackupLog::create([
            'backup_type' => $validated['backup_type'],
            'status' => 'in_progress',
            'started_at' => now(),
            'initiated_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        try {
            // Execute backup
            Artisan::call('db:backup', [
                '--type' => $validated['backup_type'],
                '--backup-id' => $backup->id,
            ]);

            $backup->update([
                'status' => 'completed',
                'completed_at' => now(),
                'size_bytes' => Storage::size('backups/' . $backup->id . '.sql'),
            ]);

            return redirect()->route('healthcare.backups.show', $backup)
                ->with('success', 'Backup completed successfully');
        } catch (\Exception $e) {
            $backup->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return redirect()->route('healthcare.backups.index')
                ->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function show(BackupLog $backup)
    {
        return view('healthcare.backups.show', compact('backup'));
    }

    public function restore(Request $request, BackupLog $backup)
    {
        if ($backup->status !== 'completed') {
            return response()->json(['success' => false, 'message' => 'Can only restore completed backups'], 400);
        }

        $validated = $request->validate([
            'confirm' => 'required|accepted',
        ]);

        try {
            Artisan::call('db:restore', [
                '--backup-id' => $backup->id,
            ]);

            return response()->json(['success' => true, 'message' => 'Database restored successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Restore failed: ' . $e->getMessage()], 500);
        }
    }

    public function download(BackupLog $backup)
    {
        if ($backup->status !== 'completed') {
            abort(404);
        }

        return Storage::download('backups/' . $backup->id . '.sql');
    }

    public function destroy(BackupLog $backup)
    {
        if (Storage::exists('backups/' . $backup->id . '.sql')) {
            Storage::delete('backups/' . $backup->id . '.sql');
        }

        $backup->delete();

        return response()->json(['success' => true, 'message' => 'Backup deleted']);
    }
}
