<?php

namespace App\Http\Controllers;

use App\Services\UndoRollbackService;
use App\Services\AutomatedBackupService;
use App\Services\ConflictResolutionService;
use App\Services\RestorePointService;
use App\Services\ActionableErrorService;
use Illuminate\Http\Request;

class ErrorHandlingController extends Controller
{
    protected $undoService;
    protected $backupService;
    protected $conflictService;
    protected $restorePointService;
    protected $errorService;

    public function __construct(
        UndoRollbackService $undoService,
        AutomatedBackupService $backupService,
        ConflictResolutionService $conflictService,
        RestorePointService $restorePointService,
        ActionableErrorService $errorService
    ) {
        $this->undoService = $undoService;
        $this->backupService = $backupService;
        $this->conflictService = $conflictService;
        $this->restorePointService = $restorePointService;
        $this->errorService = $errorService;
    }

    /**
     * Dashboard overview
     */
    public function dashboard()
    {
        $stats = [
            'undo_actions' => count($this->undoService->getUndoableActions()),
            'pending_conflicts' => count($this->conflictService->getPendingConflicts()),
            'active_restore_points' => count($this->restorePointService->getActiveRestorePoints()),
            'error_stats' => $this->errorService->getErrorStats(),
            'recent_backups' => collect($this->backupService->getBackupHistory(5)),
        ];

        if (request()->expectsJson()) {
            return response()->json($stats);
        }

        return view('error-handling.dashboard', compact('stats'));
    }

    // ==================== UNDO/ROLLBACK ENDPOINTS ====================

    public function undoLastAction()
    {
        $result = $this->undoService->undoLastAction();
        return response()->json($result);
    }

    public function undoAction(int $actionId)
    {
        $result = $this->undoService->undoAction($actionId);
        return response()->json($result);
    }

    public function getUndoableActions()
    {
        $actions = $this->undoService->getUndoableActions();
        return response()->json(['success' => true, 'actions' => $actions]);
    }

    public function bulkUndo(Request $request)
    {
        $request->validate([
            'action_ids' => 'required|array',
            'action_ids.*' => 'integer|exists:action_logs,id'
        ]);

        $result = $this->undoService->bulkUndo($request->action_ids);
        return response()->json($result);
    }

    // ==================== BACKUP ENDPOINTS ====================

    public function createBackup(Request $request)
    {
        $request->validate([
            'type' => 'sometimes|in:daily,weekly,monthly,manual,pre_change',
            'tables' => 'sometimes|array'
        ]);

        $type = $request->input('type', 'manual');
        $tables = $request->input('tables', []);

        $result = $this->backupService->createBackup($type, $tables);
        return response()->json($result);
    }

    public function restoreBackup(int $backupId)
    {
        $result = $this->backupService->restoreFromBackup($backupId);
        return response()->json($result);
    }

    public function getBackupHistory()
    {
        $backups = $this->backupService->getBackupHistory();
        return response()->json(['success' => true, 'backups' => $backups]);
    }

    public function deleteBackup(int $backupId)
    {
        $backup = \App\Models\AutomatedBackup::find($backupId);

        if (!$backup) {
            return response()->json(['success' => false, 'error' => 'Backup not found'], 404);
        }

        $backup->deleteFile();
        return response()->json(['success' => true, 'message' => 'Backup deleted']);
    }

    // ==================== RESTORE POINT ENDPOINTS ====================

    public function createRestorePoint(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'affected_models' => 'sometimes|array'
        ]);

        $result = $this->restorePointService->createRestorePoint(
            $request->name,
            $request->description ?? '',
            'manual',
            $request->affected_models ?? []
        );

        return response()->json($result);
    }

    public function restoreFromPoint(int $pointId)
    {
        $result = $this->restorePointService->restoreFromPoint($pointId);
        return response()->json($result);
    }

    public function getRestorePoints()
    {
        $points = $this->restorePointService->getActiveRestorePoints();
        return response()->json(['success' => true, 'restore_points' => $points]);
    }

    // ==================== CONFLICT RESOLUTION ENDPOINTS ====================

    public function getPendingConflicts()
    {
        $conflicts = $this->conflictService->getPendingConflicts();
        return response()->json(['success' => true, 'conflicts' => $conflicts]);
    }

    public function resolveConflict(Request $request, int $conflictId)
    {
        $request->validate([
            'strategy' => 'required|in:first_wins,last_wins,merge',
            'merged_data' => 'required_if:strategy,merge|array'
        ]);

        $result = $this->conflictService->resolveConflict(
            $conflictId,
            $request->strategy,
            $request->merged_data ?? null
        );

        return response()->json($result);
    }

    public function discardConflict(int $conflictId)
    {
        $success = $this->conflictService->discardConflict($conflictId);
        return response()->json(['success' => $success]);
    }

    // ==================== ERROR LOG ENDPOINTS ====================

    public function getRecentErrors()
    {
        $errors = $this->errorService->getRecentErrors();
        return response()->json(['success' => true, 'errors' => $errors]);
    }

    public function getErrorStats()
    {
        $stats = $this->errorService->getErrorStats();
        return response()->json(['success' => true, 'stats' => $stats]);
    }

    public function resolveError(Request $request, int $errorId)
    {
        $success = $this->errorService->resolveError($errorId, $request->notes ?? '');
        return response()->json(['success' => $success]);
    }

    public function getUserFriendlyError(Request $request)
    {
        $request->validate([
            'error_type' => 'required|string',
            'context' => 'sometimes|array'
        ]);

        $error = $this->errorService->getUserFriendlyError(
            $request->error_type,
            $request->context ?? []
        );

        return response()->json(['success' => true, 'error' => $error]);
    }

    // ==================== VIEWS ====================

    public function backupsView()
    {
        $backups = $this->backupService->getBackupHistory();
        return view('error-handling.backups', compact('backups'));
    }

    public function conflictsView()
    {
        $conflicts = $this->conflictService->getPendingConflicts();
        return view('error-handling.conflicts', compact('conflicts'));
    }

    public function actionLogView()
    {
        $actions = $this->undoService->getUndoableActions(50);
        return view('error-handling.action-log', compact('actions'));
    }
}
