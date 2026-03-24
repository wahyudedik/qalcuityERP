<?php

namespace App\Http\Controllers;

use App\Models\DisciplinaryLetter;
use App\Models\Employee;
use App\Models\ErpNotification;
use App\Models\User;
use Illuminate\Http\Request;

class DisciplinaryController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    // ── Index ─────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $tid    = $this->tid();
        $status = $request->status ?? 'all';
        $level  = $request->level  ?? 'all';

        $query = DisciplinaryLetter::where('tenant_id', $tid)
            ->with('employee', 'issuer')
            ->orderByDesc('issued_date');

        if ($status !== 'all') $query->where('status', $status);
        if ($level  !== 'all') $query->where('level', $level);

        $letters   = $query->paginate(25)->withQueryString();
        $employees = Employee::where('tenant_id', $tid)->where('status', 'active')->orderBy('name')->get();
        $users     = User::where('tenant_id', $tid)->whereIn('role', ['admin', 'manager'])->orderBy('name')->get();

        // Summary
        $summary = [
            'active'  => DisciplinaryLetter::where('tenant_id', $tid)->whereIn('status', ['issued','acknowledged'])->count(),
            'sp1'     => DisciplinaryLetter::where('tenant_id', $tid)->where('level','sp1')->whereIn('status',['issued','acknowledged'])->count(),
            'sp2'     => DisciplinaryLetter::where('tenant_id', $tid)->where('level','sp2')->whereIn('status',['issued','acknowledged'])->count(),
            'sp3'     => DisciplinaryLetter::where('tenant_id', $tid)->where('level','sp3')->whereIn('status',['issued','acknowledged'])->count(),
        ];

        // Employees with active SP (for quick reference)
        $atRisk = DisciplinaryLetter::where('tenant_id', $tid)
            ->whereIn('status', ['issued','acknowledged'])
            ->with('employee')
            ->orderByRaw("FIELD(level,'sp3','sp2','sp1','memo')")
            ->get()
            ->unique('employee_id')
            ->take(10);

        return view('hrm.disciplinary', compact('letters','employees','users','status','level','summary','atRisk'));
    }

    // ── Store ─────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id'           => 'required|exists:employees,id',
            'level'                 => 'required|in:sp1,sp2,sp3,memo,termination',
            'issued_date'           => 'required|date',
            'valid_until'           => 'nullable|date|after:issued_date',
            'violation_type'        => 'required|string|max:200',
            'violation_description' => 'required|string|max:2000',
            'corrective_action'     => 'required|string|max:2000',
            'consequences'          => 'nullable|string|max:1000',
            'witnessed_by'          => 'nullable|exists:users,id',
        ]);

        $tid = $this->tid();
        abort_unless(Employee::where('tenant_id', $tid)->where('id', $data['employee_id'])->exists(), 403);

        $letter = DisciplinaryLetter::create(array_merge($data, [
            'tenant_id'     => $tid,
            'letter_number' => DisciplinaryLetter::generateNumber($tid, $data['level']),
            'status'        => 'issued',
            'issued_by'     => auth()->id(),
            'source'        => 'manual',
        ]));

        // Notifikasi ke karyawan (jika punya akun)
        $emp = Employee::find($data['employee_id']);
        if ($emp?->user_id) {
            ErpNotification::create([
                'tenant_id' => $tid,
                'user_id'   => $emp->user_id,
                'type'      => 'disciplinary_letter',
                'title'     => "⚠️ {$letter->levelLabel()} Diterbitkan",
                'body'      => "Anda menerima {$letter->levelLabel()} terkait: {$data['violation_type']}. Harap segera menindaklanjuti.",
                'data'      => ['letter_id' => $letter->id],
            ]);
        }

        return back()->with('success', "{$letter->levelLabel()} No. {$letter->letter_number} berhasil diterbitkan.");
    }

    // ── Show (detail + print) ─────────────────────────────────────

    public function show(DisciplinaryLetter $letter)
    {
        abort_unless($letter->tenant_id === $this->tid(), 403);
        $letter->load('employee', 'issuer', 'witness');

        // Ambil riwayat SP karyawan ini
        $history = DisciplinaryLetter::where('tenant_id', $this->tid())
            ->where('employee_id', $letter->employee_id)
            ->orderByDesc('issued_date')
            ->get();

        return view('hrm.disciplinary-show', compact('letter', 'history'));
    }

    // ── Acknowledge (karyawan tanda tangan/konfirmasi) ────────────

    public function acknowledge(Request $request, DisciplinaryLetter $letter)
    {
        abort_unless($letter->tenant_id === $this->tid(), 403);
        abort_unless(in_array($letter->status, ['issued']), 422);

        $data = $request->validate(['employee_response' => 'nullable|string|max:1000']);

        $letter->update([
            'status'           => 'acknowledged',
            'acknowledged_at'  => now(),
            'employee_response'=> $data['employee_response'] ?? null,
        ]);

        return back()->with('success', 'Surat peringatan telah dikonfirmasi oleh karyawan.');
    }

    // ── Expire ────────────────────────────────────────────────────

    public function expire(DisciplinaryLetter $letter)
    {
        abort_unless($letter->tenant_id === $this->tid(), 403);
        $letter->update(['status' => 'expired']);
        return back()->with('success', 'Status SP diubah menjadi expired.');
    }

    // ── Destroy ───────────────────────────────────────────────────

    public function destroy(DisciplinaryLetter $letter)
    {
        abort_unless($letter->tenant_id === $this->tid(), 403);
        abort_unless($letter->status === 'draft', 422);
        $letter->delete();
        return back()->with('success', 'Draft surat peringatan dihapus.');
    }

    // ── AI: draft SP dari anomali absensi ─────────────────────────

    public function aiDraft(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'anomalies'   => 'required|array',
        ]);

        $tid = $this->tid();
        abort_unless(Employee::where('tenant_id', $tid)->where('id', $data['employee_id'])->exists(), 403);

        $emp = Employee::find($data['employee_id']);

        // Tentukan level SP berdasarkan riwayat aktif
        $activeCount = DisciplinaryLetter::where('tenant_id', $tid)
            ->where('employee_id', $emp->id)
            ->whereIn('status', ['issued','acknowledged'])
            ->count();

        $level = match(true) {
            $activeCount >= 2 => 'sp3',
            $activeCount === 1 => 'sp2',
            default => 'sp1',
        };

        // Buat deskripsi dari data anomali
        $anomalyLines = collect($data['anomalies'])->map(fn($a) => "- {$a['message']}")->implode("\n");
        $description  = "Berdasarkan analisis sistem, karyawan {$emp->name} terdeteksi memiliki pola absensi tidak wajar:\n{$anomalyLines}";
        $corrective   = "Karyawan diharapkan memperbaiki kehadiran dan ketepatan waktu sesuai peraturan perusahaan. Wajib hadir tepat waktu dan memberikan keterangan resmi jika berhalangan.";

        return response()->json([
            'level'                 => $level,
            'violation_type'        => 'Pelanggaran Kehadiran & Kedisiplinan',
            'violation_description' => $description,
            'corrective_action'     => $corrective,
            'consequences'          => $level === 'sp3'
                ? 'Apabila tidak ada perbaikan, perusahaan berhak mengambil tindakan pemutusan hubungan kerja (PHK) sesuai ketentuan yang berlaku.'
                : 'Apabila pelanggaran terulang, akan diterbitkan surat peringatan dengan tingkat yang lebih tinggi.',
            'employee_name'         => $emp->name,
            'suggested_level_label' => match($level) { 'sp1'=>'SP I','sp2'=>'SP II','sp3'=>'SP III', default=>'SP I' },
        ]);
    }
}
