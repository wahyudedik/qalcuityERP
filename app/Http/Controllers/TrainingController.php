<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeCertification;
use App\Models\TrainingParticipant;
use App\Models\TrainingProgram;
use App\Models\TrainingSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrainingController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    // ── Main page ─────────────────────────────────────────────────

    public function index(Request $request)
    {
        $tid = $this->tid();
        $tab = $request->tab ?? 'sessions';

        $programs = TrainingProgram::where('tenant_id', $tid)
            ->where('is_active', true)->orderBy('name')->get();

        $employees = Employee::where('tenant_id', $tid)
            ->where('status', 'active')->orderBy('name')->get();

        // Sessions tab
        $sessions = TrainingSession::where('tenant_id', $tid)
            ->with('program')
            ->withCount('participants')
            ->orderByDesc('start_date')
            ->paginate(20, ['*'], 'sp')
            ->withQueryString();

        // Certifications tab — with expiry alerts
        $certQuery = EmployeeCertification::where('tenant_id', $tid)
            ->with('employee')
            ->orderBy('expiry_date');

        if ($request->cert_filter === 'expiring') {
            $certQuery->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now()->addDays(90))
                ->where('status', 'active');
        } elseif ($request->cert_filter === 'expired') {
            $certQuery->where('status', 'expired')
                ->orWhere(fn ($q) => $q->whereNotNull('expiry_date')->where('expiry_date', '<', today()));
        }

        $certifications = $certQuery->paginate(20, ['*'], 'cp')->withQueryString();

        // Summary stats
        $summary = [
            'total_programs' => TrainingProgram::where('tenant_id', $tid)->where('is_active', true)->count(),
            'sessions_this_year' => TrainingSession::where('tenant_id', $tid)->whereYear('start_date', now()->year)->count(),
            'certs_expiring' => EmployeeCertification::where('tenant_id', $tid)
                ->where('status', 'active')->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now()->addDays(90))->count(),
            'certs_expired' => EmployeeCertification::where('tenant_id', $tid)
                ->where(fn ($q) => $q->where('status', 'expired')
                    ->orWhere(fn ($q2) => $q2->whereNotNull('expiry_date')->where('expiry_date', '<', today()))
                )->count(),
        ];

        // Skill matrix: department → [program_category → count of passed participants]
        $matrix = $this->buildSkillMatrix($tid);

        return view('hrm.training', compact(
            'tab', 'programs', 'employees', 'sessions',
            'certifications', 'summary', 'matrix'
        ));
    }

    // ── Training Programs ─────────────────────────────────────────

    public function storeProgram(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'provider' => 'nullable|string|max:200',
            'duration_hours' => 'required|integer|min:1|max:9999',
            'cost' => 'required|numeric|min:0',
        ]);

        TrainingProgram::create(array_merge($data, ['tenant_id' => $this->tid()]));

        return back()->with('success', "Program \"{$data['name']}\" berhasil ditambahkan.");
    }

    public function updateProgram(Request $request, TrainingProgram $program)
    {
        abort_unless($program->tenant_id === $this->tid(), 403);
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'provider' => 'nullable|string|max:200',
            'duration_hours' => 'required|integer|min:1|max:9999',
            'cost' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);
        $program->update($data);

        return back()->with('success', 'Program pelatihan diperbarui.');
    }

    public function destroyProgram(TrainingProgram $program)
    {
        abort_unless($program->tenant_id === $this->tid(), 403);
        $program->update(['is_active' => false]);

        return back()->with('success', 'Program dinonaktifkan.');
    }

    // ── Training Sessions ─────────────────────────────────────────

    public function storeSession(Request $request)
    {
        $data = $request->validate([
            'training_program_id' => 'required|exists:training_programs,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:200',
            'trainer' => 'nullable|string|max:200',
            'max_participants' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $session = TrainingSession::create(array_merge($data, ['tenant_id' => $this->tid()]));

        return back()->with('success', 'Sesi pelatihan berhasil dijadwalkan.');
    }

    public function updateSessionStatus(Request $request, TrainingSession $session)
    {
        abort_unless($session->tenant_id === $this->tid(), 403);
        $data = $request->validate(['status' => 'required|in:scheduled,ongoing,completed,cancelled']);
        $session->update($data);

        return back()->with('success', 'Status sesi diperbarui.');
    }

    public function destroySession(TrainingSession $session)
    {
        abort_unless($session->tenant_id === $this->tid(), 403);
        $session->delete();

        return back()->with('success', 'Sesi pelatihan dihapus.');
    }

    // ── Participants ──────────────────────────────────────────────

    public function sessionDetail(TrainingSession $session)
    {
        abort_unless($session->tenant_id === $this->tid(), 403);
        $session->load('program', 'participants.employee');

        $enrolled = $session->participants->pluck('employee_id')->toArray();
        $employees = Employee::where('tenant_id', $this->tid())
            ->where('status', 'active')
            ->whereNotIn('id', $enrolled)
            ->orderBy('name')->get();

        return view('hrm.training-session', compact('session', 'employees'));
    }

    public function addParticipant(Request $request, TrainingSession $session)
    {
        abort_unless($session->tenant_id === $this->tid(), 403);
        $data = $request->validate(['employee_id' => 'required|exists:employees,id']);

        if ($session->isFull()) {
            return back()->withErrors(['employee_id' => 'Sesi sudah penuh.']);
        }

        TrainingParticipant::firstOrCreate([
            'training_session_id' => $session->id,
            'employee_id' => $data['employee_id'],
        ], ['tenant_id' => $this->tid(), 'status' => 'registered']);

        return back()->with('success', 'Peserta berhasil didaftarkan.');
    }

    public function updateParticipant(Request $request, TrainingParticipant $participant)
    {
        abort_unless($participant->tenant_id === $this->tid(), 403);
        $data = $request->validate([
            'status' => 'required|in:registered,attended,passed,failed,absent',
            'score' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string|max:255',
        ]);
        $participant->update($data);

        return back()->with('success', 'Data peserta diperbarui.');
    }

    public function removeParticipant(TrainingParticipant $participant)
    {
        abort_unless($participant->tenant_id === $this->tid(), 403);
        $participant->delete();

        return back()->with('success', 'Peserta dihapus dari sesi.');
    }

    // ── Certifications ────────────────────────────────────────────

    public function storeCertification(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'name' => 'required|string|max:200',
            'issuer' => 'nullable|string|max:200',
            'certificate_number' => 'nullable|string|max:100',
            'issued_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:issued_date',
            'notes' => 'nullable|string|max:500',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $tid = $this->tid();
        abort_unless(Employee::where('tenant_id', $tid)->where('id', $data['employee_id'])->exists(), 403);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store("certifications/{$tid}", 'public');
        }

        $expiry = $data['expiry_date'] ?? null;
        $status = ($expiry && $expiry < today()->format('Y-m-d')) ? 'expired' : 'active';

        EmployeeCertification::create(array_merge($data, [
            'tenant_id' => $tid,
            'file_path' => $filePath,
            'status' => $status,
        ]));

        return back()->with('success', "Sertifikat \"{$data['name']}\" berhasil ditambahkan.");
    }

    public function destroyCertification(EmployeeCertification $certification)
    {
        abort_unless($certification->tenant_id === $this->tid(), 403);
        if ($certification->file_path) {
            Storage::disk('public')->delete($certification->file_path);
        }
        $certification->delete();

        return back()->with('success', 'Sertifikat dihapus.');
    }

    // ── Skill Matrix ──────────────────────────────────────────────

    private function buildSkillMatrix(int $tid): array
    {
        // [department => [category => passed_count]]
        $rows = TrainingParticipant::where('training_participants.tenant_id', $tid)
            ->where('training_participants.status', 'passed')
            ->join('employees', 'employees.id', '=', 'training_participants.employee_id')
            ->join('training_sessions', 'training_sessions.id', '=', 'training_participants.training_session_id')
            ->join('training_programs', 'training_programs.id', '=', 'training_sessions.training_program_id')
            ->selectRaw('COALESCE(employees.department, "Umum") as dept, training_programs.category, COUNT(*) as cnt')
            ->whereNotNull('training_programs.category')
            ->groupBy('dept', 'training_programs.category')
            ->withoutGlobalScopes()
            ->get();

        $matrix = [];
        $categories = [];
        foreach ($rows as $row) {
            $matrix[$row->dept][$row->category] = $row->cnt;
            $categories[$row->category] = true;
        }

        return ['data' => $matrix, 'categories' => array_keys($categories)];
    }
}
