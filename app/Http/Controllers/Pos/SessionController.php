<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CashierSession;
use App\Models\User;
use App\Models\Warehouse;
use App\Notifications\CashierSessionClosedNotification;
use App\Notifications\CashierSessionOpenedNotification;
use App\Services\GlPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SessionController extends Controller
{
    /**
     * Daftar semua sesi kasir (untuk manajer/admin)
     */
    public function index()
    {
        $sessions = CashierSession::with(['cashier', 'warehouse'])
            ->latest('opened_at')
            ->paginate(20);

        return view('pos.sessions.index', compact('sessions'));
    }

    /**
     * Form buka sesi kasir baru
     */
    public function create()
    {
        $user = auth()->user();

        // Cek apakah kasir sudah punya sesi terbuka
        $activeSession = CashierSession::where('user_id', $user->id)
            ->where('status', CashierSession::STATUS_OPEN)
            ->first();

        if ($activeSession) {
            return redirect()->route('pos.sessions.show', $activeSession)
                ->with('info', 'Anda sudah memiliki sesi kasir yang sedang terbuka.');
        }

        $warehouses = Warehouse::orderBy('name')->get();

        return view('pos.sessions.create', compact('warehouses'));
    }

    /**
     * Buka sesi kasir baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'opening_balance' => 'required|numeric|min:0',
            'register_name' => 'nullable|string|max:100',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();

        // Cek apakah kasir sudah punya sesi terbuka
        $existing = CashierSession::where('user_id', $user->id)
            ->where('status', CashierSession::STATUS_OPEN)
            ->first();

        if ($existing) {
            return redirect()->route('pos.sessions.show', $existing)
                ->with('warning', 'Anda sudah memiliki sesi kasir yang sedang terbuka.');
        }

        $session = CashierSession::create([
            'user_id' => $user->id,
            'warehouse_id' => $request->warehouse_id,
            'register_name' => $request->register_name ?? 'Kasir Utama',
            'status' => CashierSession::STATUS_OPEN,
            'opening_balance' => $request->opening_balance,
            'opened_at' => now(),
            'notes' => $request->notes,
        ]);

        ActivityLog::record('pos_session_opened', "Sesi kasir dibuka oleh {$user->name}", $session);

        // Kirim notifikasi ke manager/admin
        $this->notifyManagers($user->tenant_id, new CashierSessionOpenedNotification($session));

        return redirect()->route('pos.index')
            ->with('success', 'Sesi kasir berhasil dibuka. Selamat bekerja!');
    }

    /**
     * Detail sesi kasir (termasuk rekap transaksi)
     */
    public function show(CashierSession $session)
    {
        $this->authorizeSession($session);

        $session->load(['cashier', 'warehouse', 'closedByUser']);

        // Ambil transaksi dengan eager loading untuk menghindari N+1
        $transactions = $session->transactions()
            ->with(['customer', 'items.product'])
            ->where('status', 'completed')
            ->latest()
            ->get();

        // Rekap per metode pembayaran
        $recap = $session->calculateRecap();

        return view('pos.sessions.show', compact('session', 'transactions', 'recap'));
    }

    /**
     * Form tutup sesi kasir
     */
    public function closeForm(CashierSession $session)
    {
        $this->authorizeSession($session);

        if ($session->isClosed()) {
            return redirect()->route('pos.sessions.show', $session)
                ->with('info', 'Sesi kasir ini sudah ditutup.');
        }

        $session->load(['cashier', 'warehouse']);

        // Hitung rekap real-time
        $recap = $session->calculateRecap();

        return view('pos.sessions.close', compact('session', 'recap'));
    }

    /**
     * Tutup sesi kasir dan simpan rekap
     */
    public function close(Request $request, CashierSession $session)
    {
        $this->authorizeSession($session);

        if ($session->isClosed()) {
            return redirect()->route('pos.sessions.show', $session)
                ->with('info', 'Sesi kasir ini sudah ditutup.');
        }

        $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();

        DB::transaction(function () use ($request, $session, $user) {
            // Hitung rekap dari transaksi aktual
            $recap = $session->calculateRecap();

            $closingBalance = (float) $request->closing_balance;
            $expectedBalance = $recap['expected_balance'];
            $difference = $closingBalance - $expectedBalance;

            $session->update([
                'status' => CashierSession::STATUS_CLOSED,
                'closing_balance' => $closingBalance,
                'expected_balance' => $expectedBalance,
                'balance_difference' => $difference,
                'closed_at' => now(),
                'closed_by' => $user->id,
                'notes' => $request->notes ?? $session->notes,

                // Simpan rekap
                'total_transactions' => $recap['total_transactions'],
                'total_sales' => $recap['total_sales'],
                'total_cash' => $recap['total_cash'],
                'total_card' => $recap['total_card'],
                'total_qris' => $recap['total_qris'],
                'total_transfer' => $recap['total_transfer'],
                'total_discount' => $recap['total_discount'],
                'total_tax' => $recap['total_tax'],
            ]);
        });

        $session->refresh();

        ActivityLog::record('pos_session_closed', "Sesi kasir ditutup oleh {$user->name}", $session);

        // Kirim notifikasi ke manager/admin
        $this->notifyManagers($user->tenant_id, new CashierSessionClosedNotification($session));

        // ── Auto-posting jurnal GL ──────────────────────────────────────────
        // Posting dilakukan di luar DB transaction utama agar tidak rollback
        // jika CoA belum dikonfigurasi (graceful degradation).
        if ($session->total_sales > 0) {
            try {
                $glService = app(GlPostingService::class);
                $sessionNumber = $session->id.'-'.$session->opened_at->format('Ymd');
                $totalNonCash = (float) $session->total_card
                               + (float) $session->total_qris
                               + (float) $session->total_transfer;

                $result = $glService->postPosSession(
                    tenantId: $user->tenant_id,
                    userId: $user->id,
                    sessionId: $session->id,
                    sessionNumber: $sessionNumber,
                    totalSales: (float) $session->total_sales,
                    totalCash: (float) $session->total_cash,
                    totalNonCash: $totalNonCash,
                    date: $session->closed_at->toDateString(),
                );

                if ($result->isFailed()) {
                    Log::warning("POS Session GL posting failed for session {$session->id}: ".$result->message);

                    return redirect()->route('pos.sessions.show', $session)
                        ->with('success', 'Sesi kasir berhasil ditutup. Rekap tersimpan.')
                        ->with('warning', 'Jurnal akuntansi tidak dapat diposting otomatis: '.$result->message.'. Silakan buat jurnal manual.');
                }

                ActivityLog::record('pos_gl_posted', "Jurnal GL POS sesi {$sessionNumber} diposting otomatis", $session);
            } catch (\Throwable $e) {
                Log::error("POS Session GL posting exception for session {$session->id}: ".$e->getMessage());

                return redirect()->route('pos.sessions.show', $session)
                    ->with('success', 'Sesi kasir berhasil ditutup. Rekap tersimpan.')
                    ->with('warning', 'Jurnal akuntansi tidak dapat diposting: '.$e->getMessage());
            }
        }

        return redirect()->route('pos.sessions.show', $session)
            ->with('success', 'Sesi kasir berhasil ditutup. Rekap tersimpan dan jurnal akuntansi diposting.');
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /**
     * Pastikan sesi milik tenant yang sama dengan user yang login.
     */
    private function authorizeSession(CashierSession $session): void
    {
        if ($session->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Akses ditolak.');
        }
    }

    /**
     * Kirim notifikasi ke semua user dengan role admin/manager di tenant ini.
     */
    private function notifyManagers(int $tenantId, $notification): void
    {
        try {
            User::where('tenant_id', $tenantId)
                ->whereIn('role', ['admin', 'manager'])
                ->get()
                ->each(fn ($u) => $u->notify($notification));
        } catch (\Throwable) {
            // Jangan gagalkan operasi utama jika notifikasi error
        }
    }
}
