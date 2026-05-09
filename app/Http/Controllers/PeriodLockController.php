<?php

namespace App\Http\Controllers;

use App\Models\AccountingPeriod;
use App\Models\FiscalYear;
use App\Models\PeriodBackup;
use App\Services\PeriodBackupService;
use App\Services\PeriodLockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PeriodLockController extends Controller
{
    public function __construct(
        private PeriodLockService $lockService,
        private PeriodBackupService $backupService,
    ) {}

    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    // ── Fiscal Years ──────────────────────────────────────────────

    public function index()
    {
        $fiscalYears = FiscalYear::where('tenant_id', $this->tid())
            ->with(['closedBy', 'lockedBy'])
            ->orderByDesc('start_date')
            ->get();

        $periods = AccountingPeriod::where('tenant_id', $this->tid())
            ->orderByDesc('start_date')
            ->get();

        $backups = PeriodBackup::where('tenant_id', $this->tid())
            ->with('createdBy')
            ->orderByDesc('created_at')
            ->get();

        return view('accounting.period-lock', compact('fiscalYears', 'periods', 'backups'));
    }

    public function storeFiscalYear(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:20',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'auto_periods' => 'boolean',
        ]);

        $tid = $this->tid();

        // Cek overlap
        $overlap = FiscalYear::where('tenant_id', $tid)
            ->where(fn ($q) => $q
                ->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
                ->orWhere(fn ($q2) => $q2->where('start_date', '<=', $data['start_date'])->where('end_date', '>=', $data['end_date']))
            )->exists();

        if ($overlap) {
            return back()->withErrors(['name' => 'Rentang tanggal tumpang tindih dengan tahun fiskal yang sudah ada.'])->withInput();
        }

        $fy = FiscalYear::create(array_merge($data, ['tenant_id' => $tid]));

        // Auto-generate periode bulanan
        if ($request->boolean('auto_periods')) {
            $count = $this->lockService->generateMonthlyPeriods($fy);

            return back()->with('success', "Tahun fiskal {$fy->name} dibuat dengan {$count} periode bulanan.");
        }

        return back()->with('success', "Tahun fiskal {$fy->name} berhasil dibuat.");
    }

    public function closeFiscalYear(FiscalYear $fiscalYear)
    {
        abort_if($fiscalYear->tenant_id !== $this->tid(), 403);
        abort_if(! $fiscalYear->isOpen(), 400, 'Tahun fiskal sudah ditutup atau dikunci.');

        $this->lockService->closeFiscalYear($fiscalYear, auth()->id());

        return back()->with('success', "Tahun fiskal {$fiscalYear->name} berhasil ditutup. Semua periode di dalamnya ikut ditutup.");
    }

    public function lockFiscalYear(FiscalYear $fiscalYear)
    {
        abort_if($fiscalYear->tenant_id !== $this->tid(), 403);
        abort_if($fiscalYear->isLocked(), 400, 'Tahun fiskal sudah dikunci.');

        $this->lockService->lockFiscalYear($fiscalYear, auth()->id());

        return back()->with('success', "Tahun fiskal {$fiscalYear->name} berhasil dikunci permanen. Data tidak dapat diubah.");
    }

    public function reopenFiscalYear(FiscalYear $fiscalYear)
    {
        abort_if($fiscalYear->tenant_id !== $this->tid(), 403);

        try {
            $this->lockService->reopenFiscalYear($fiscalYear);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Tahun fiskal {$fiscalYear->name} dibuka kembali.");
    }

    // ── Accounting Periods ────────────────────────────────────────

    public function lockPeriod(AccountingPeriod $period)
    {
        abort_if($period->tenant_id !== $this->tid(), 403);
        abort_if($period->isLocked(), 400, 'Periode sudah dikunci.');

        $this->lockService->lockPeriod($period, auth()->id());

        return back()->with('success', "Periode {$period->name} berhasil dikunci.");
    }

    // ── Backups ───────────────────────────────────────────────────

    public function createBackup(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:monthly,yearly,manual',
            'label' => 'required|string|max:50',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        $backup = PeriodBackup::create(array_merge($data, [
            'tenant_id' => $this->tid(),
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]));

        // Jalankan backup langsung (untuk data kecil-menengah)
        // Untuk produksi besar, dispatch ke queue: GeneratePeriodBackup::dispatch($backup)
        try {
            $this->backupService->generate($backup);

            return back()->with('success', "Backup \"{$backup->label}\" berhasil dibuat.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Backup gagal: '.$e->getMessage());
        }
    }

    public function downloadBackup(PeriodBackup $backup)
    {
        abort_if($backup->tenant_id !== $this->tid(), 403);
        abort_if(! $backup->isCompleted(), 404, 'Backup belum selesai.');
        abort_if(! Storage::exists($backup->file_path), 404, 'File backup tidak ditemukan.');

        $filename = "backup_{$backup->label}_{$backup->period_start->format('Y-m-d')}.json";

        return Storage::download($backup->file_path, $filename);
    }

    public function destroyBackup(PeriodBackup $backup)
    {
        abort_if($backup->tenant_id !== $this->tid(), 403);

        if ($backup->file_path && Storage::exists($backup->file_path)) {
            Storage::delete($backup->file_path);
        }

        $backup->delete();

        return back()->with('success', 'Backup berhasil dihapus.');
    }
}
