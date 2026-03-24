<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\EmployeeOnboarding;
use App\Models\EmployeeOnboardingTask;
use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Http\Request;

class RecruitmentController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    // ─── Lowongan ─────────────────────────────────────────────────

    public function index(Request $request)
    {
        $tid = $this->tid();

        $postings = JobPosting::where('tenant_id', $tid)
            ->withCount(['applications', 'applications as hired_count' => fn($q) => $q->where('stage', 'hired')])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15)->withQueryString();

        $stats = [
            'open'        => JobPosting::where('tenant_id', $tid)->where('status', 'open')->count(),
            'applications'=> JobApplication::where('tenant_id', $tid)->count(),
            'interview'   => JobApplication::where('tenant_id', $tid)->where('stage', 'interview')->count(),
            'hired_month' => JobApplication::where('tenant_id', $tid)->where('stage', 'hired')
                                ->whereMonth('updated_at', now()->month)->count(),
        ];

        $onboardings = EmployeeOnboarding::where('tenant_id', $tid)
            ->where('status', 'in_progress')
            ->with('employee')
            ->get();

        return view('hrm.recruitment', compact('postings', 'stats', 'onboardings'));
    }

    public function storePosting(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'department'  => 'nullable|string|max:100',
            'location'    => 'nullable|string|max:100',
            'type'        => 'required|in:full_time,part_time,contract,internship',
            'description' => 'nullable|string',
            'requirements'=> 'nullable|string',
            'salary_min'  => 'nullable|integer|min:0',
            'salary_max'  => 'nullable|integer|min:0',
            'quota'       => 'required|integer|min:1',
            'deadline'    => 'nullable|date',
            'status'      => 'required|in:draft,open,closed',
        ]);

        $posting = JobPosting::create(array_merge($data, [
            'tenant_id'  => $this->tid(),
            'created_by' => auth()->id(),
        ]));

        ActivityLog::record('job_posting_created', "Lowongan dibuat: {$posting->title}", $posting);
        return back()->with('success', "Lowongan \"{$posting->title}\" berhasil dibuat.");
    }

    public function updatePosting(Request $request, JobPosting $posting)
    {
        abort_unless($posting->tenant_id === $this->tid(), 403);

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'department'  => 'nullable|string|max:100',
            'location'    => 'nullable|string|max:100',
            'type'        => 'required|in:full_time,part_time,contract,internship',
            'description' => 'nullable|string',
            'requirements'=> 'nullable|string',
            'salary_min'  => 'nullable|integer|min:0',
            'salary_max'  => 'nullable|integer|min:0',
            'quota'       => 'required|integer|min:1',
            'deadline'    => 'nullable|date',
            'status'      => 'required|in:draft,open,closed',
        ]);

        $posting->update($data);
        return back()->with('success', "Lowongan diperbarui.");
    }

    public function destroyPosting(JobPosting $posting)
    {
        abort_unless($posting->tenant_id === $this->tid(), 403);
        $posting->delete();
        return back()->with('success', "Lowongan dihapus.");
    }

    // ─── Lamaran ──────────────────────────────────────────────────

    public function applications(Request $request, JobPosting $posting)
    {
        abort_unless($posting->tenant_id === $this->tid(), 403);

        $apps = JobApplication::where('job_posting_id', $posting->id)
            ->when($request->stage, fn($q) => $q->where('stage', $request->stage))
            ->latest()
            ->paginate(20)->withQueryString();

        $stageCounts = JobApplication::where('job_posting_id', $posting->id)
            ->selectRaw('stage, count(*) as cnt')
            ->groupBy('stage')
            ->pluck('cnt', 'stage');

        return view('hrm.applications', compact('posting', 'apps', 'stageCounts'));
    }

    public function storeApplication(Request $request, JobPosting $posting)
    {
        abort_unless($posting->tenant_id === $this->tid(), 403);

        $data = $request->validate([
            'applicant_name'  => 'required|string|max:255',
            'applicant_email' => 'nullable|email|max:255',
            'applicant_phone' => 'nullable|string|max:20',
            'cover_letter'    => 'nullable|string',
            'notes'           => 'nullable|string|max:500',
        ]);

        JobApplication::create(array_merge($data, [
            'tenant_id'      => $this->tid(),
            'job_posting_id' => $posting->id,
            'stage'          => 'applied',
            'reviewed_by'    => auth()->id(),
        ]));

        return back()->with('success', "Lamaran {$data['applicant_name']} berhasil ditambahkan.");
    }

    public function updateStage(Request $request, JobApplication $application)
    {
        abort_unless($application->tenant_id === $this->tid(), 403);

        $data = $request->validate([
            'stage'              => 'required|in:applied,screening,interview,offer,hired,rejected',
            'notes'              => 'nullable|string|max:1000',
            'interview_date'     => 'nullable|date',
            'interview_location' => 'nullable|string|max:255',
            'offered_salary'     => 'nullable|integer|min:0',
            'expected_join_date' => 'nullable|date',
        ]);

        $application->update(array_merge($data, ['reviewed_by' => auth()->id()]));

        // Jika diterima → buat karyawan baru + mulai onboarding
        if ($data['stage'] === 'hired' && !$application->employee_id) {
            $this->hireApplicant($application);
        }

        return back()->with('success', "Status lamaran diperbarui ke: {$application->fresh()->stageLabel()}");
    }

    /**
     * Konversi pelamar yang diterima menjadi karyawan + mulai onboarding.
     */
    private function hireApplicant(JobApplication $application): void
    {
        $tid     = $this->tid();
        $posting = $application->jobPosting;
        $count   = Employee::where('tenant_id', $tid)->count() + 1;

        $employee = Employee::create([
            'tenant_id'   => $tid,
            'employee_id' => 'EMP-' . now()->format('Ym') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT),
            'name'        => $application->applicant_name,
            'email'       => $application->applicant_email,
            'phone'       => $application->applicant_phone,
            'position'    => $posting?->title,
            'department'  => $posting?->department,
            'salary'      => $application->offered_salary,
            'join_date'   => $application->expected_join_date ?? today(),
            'status'      => 'active',
        ]);

        $application->update(['employee_id' => $employee->id]);

        // Buat onboarding dengan default tasks
        $onboarding = EmployeeOnboarding::create([
            'tenant_id'          => $tid,
            'employee_id'        => $employee->id,
            'job_application_id' => $application->id,
            'status'             => 'in_progress',
            'start_date'         => $employee->join_date,
        ]);

        $this->seedDefaultOnboardingTasks($onboarding);

        ActivityLog::record('employee_hired', "Pelamar diterima & karyawan dibuat: {$employee->name}", $employee);
    }

    /**
     * Seed default onboarding tasks untuk karyawan baru.
     */
    private function seedDefaultOnboardingTasks(EmployeeOnboarding $onboarding): void
    {
        $defaults = [
            // Hari 1 — Administrasi
            ['task' => 'Tanda tangan kontrak kerja',          'category' => 'Administrasi', 'due_day' => 1,  'required' => true],
            ['task' => 'Pengumpulan dokumen (KTP, NPWP, KK)', 'category' => 'Administrasi', 'due_day' => 1,  'required' => true],
            ['task' => 'Foto karyawan untuk ID card',         'category' => 'Administrasi', 'due_day' => 1,  'required' => false],
            ['task' => 'Pendaftaran BPJS Kesehatan',          'category' => 'Administrasi', 'due_day' => 3,  'required' => true],
            ['task' => 'Pendaftaran BPJS Ketenagakerjaan',    'category' => 'Administrasi', 'due_day' => 3,  'required' => true],
            ['task' => 'Data rekening bank untuk penggajian', 'category' => 'Administrasi', 'due_day' => 3,  'required' => true],
            // IT & Akses
            ['task' => 'Pembuatan akun email perusahaan',     'category' => 'IT & Akses',   'due_day' => 1,  'required' => true],
            ['task' => 'Akses sistem ERP / aplikasi kerja',   'category' => 'IT & Akses',   'due_day' => 1,  'required' => true],
            ['task' => 'Pemberian laptop / perangkat kerja',  'category' => 'IT & Akses',   'due_day' => 1,  'required' => false],
            // Orientasi
            ['task' => 'Perkenalan dengan tim & manajemen',   'category' => 'Orientasi',    'due_day' => 1,  'required' => true],
            ['task' => 'Penjelasan visi, misi & budaya kerja','category' => 'Orientasi',    'due_day' => 2,  'required' => true],
            ['task' => 'Penjelasan SOP & peraturan perusahaan','category' => 'Orientasi',   'due_day' => 2,  'required' => true],
            ['task' => 'Tour fasilitas kantor',               'category' => 'Orientasi',    'due_day' => 1,  'required' => false],
            // Pelatihan
            ['task' => 'Pelatihan penggunaan sistem ERP',     'category' => 'Pelatihan',    'due_day' => 5,  'required' => true],
            ['task' => 'Pelatihan K3 / keselamatan kerja',    'category' => 'Pelatihan',    'due_day' => 7,  'required' => false],
            // Evaluasi
            ['task' => 'Check-in minggu pertama dengan atasan','category' => 'Evaluasi',    'due_day' => 7,  'required' => true],
            ['task' => 'Evaluasi akhir masa probasi (30 hari)','category' => 'Evaluasi',    'due_day' => 30, 'required' => true],
        ];

        foreach ($defaults as $i => $task) {
            EmployeeOnboardingTask::create(array_merge($task, [
                'employee_onboarding_id' => $onboarding->id,
                'sort_order'             => $i,
            ]));
        }
    }

    // ─── Onboarding ───────────────────────────────────────────────

    public function onboarding(Request $request)
    {
        $tid = $this->tid();

        $onboardings = EmployeeOnboarding::where('tenant_id', $tid)
            ->with(['employee', 'tasks'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15)->withQueryString();

        return view('hrm.onboarding', compact('onboardings'));
    }

    public function onboardingDetail(EmployeeOnboarding $onboarding)
    {
        abort_unless($onboarding->tenant_id === $this->tid(), 403);
        $onboarding->load(['employee', 'tasks' => fn($q) => $q->orderBy('sort_order')]);
        return view('hrm.onboarding-detail', compact('onboarding'));
    }

    public function toggleTask(Request $request, EmployeeOnboardingTask $task)
    {
        abort_unless($task->onboarding->tenant_id === $this->tid(), 403);

        $isDone = !$task->is_done;
        $task->update([
            'is_done' => $isDone,
            'done_at' => $isDone ? now() : null,
            'done_by' => $isDone ? auth()->id() : null,
            'notes'   => $request->notes ?? $task->notes,
        ]);

        // Cek apakah semua required task selesai → auto complete onboarding
        $onboarding = $task->onboarding;
        if ($isDone && $onboarding->requiredPendingCount() === 0) {
            $onboarding->update(['status' => 'completed', 'completed_at' => now()]);
        } elseif (!$isDone && $onboarding->status === 'completed') {
            $onboarding->update(['status' => 'in_progress', 'completed_at' => null]);
        }

        return response()->json([
            'is_done'  => $isDone,
            'progress' => $onboarding->fresh()->progressPercent(),
            'status'   => $onboarding->fresh()->status,
        ]);
    }

    public function startOnboarding(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'start_date'  => 'required|date',
        ]);

        $tid = $this->tid();
        abort_unless(Employee::where('tenant_id', $tid)->where('id', $data['employee_id'])->exists(), 403);

        // Cegah duplikat
        if (EmployeeOnboarding::where('employee_id', $data['employee_id'])->exists()) {
            return back()->withErrors(['employee_id' => 'Karyawan ini sudah memiliki onboarding aktif.']);
        }

        $onboarding = EmployeeOnboarding::create([
            'tenant_id'  => $tid,
            'employee_id'=> $data['employee_id'],
            'status'     => 'in_progress',
            'start_date' => $data['start_date'],
        ]);

        $this->seedDefaultOnboardingTasks($onboarding);

        return back()->with('success', 'Onboarding berhasil dimulai.');
    }
}
