<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectBillingConfig;
use App\Models\ProjectInvoice;
use App\Models\ProjectMilestone;
use App\Models\Timesheet;
use App\Services\GlPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectBillingController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    // ── Billing Dashboard per Project ─────────────────────────────

    public function show(Project $project)
    {
        abort_if($project->tenant_id !== $this->tid(), 403);
        $project->load(['customer', 'billingConfig', 'milestones', 'projectInvoices.invoice']);

        $unbilledTimesheets = Timesheet::where('project_id', $project->id)
            ->where('billing_status', 'unbilled')
            ->with('user')->orderBy('date')->get();

        $unbilledHours = $unbilledTimesheets->sum('hours');
        $unbilledAmount = $unbilledTimesheets->sum(fn($t) => $t->laborCost());

        $totalBilled = $project->projectInvoices->sum('total_amount');
        $totalPaid = $project->projectInvoices->where('status', 'paid')->sum('total_amount');

        return view('project-billing.show', compact(
            'project', 'unbilledTimesheets', 'unbilledHours', 'unbilledAmount',
            'totalBilled', 'totalPaid'
        ));
    }

    // ── Billing Config ────────────────────────────────────────────

    public function saveConfig(Request $request, Project $project)
    {
        abort_if($project->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'billing_type'      => 'required|in:time_material,milestone,retainer,fixed_price',
            'hourly_rate'       => 'nullable|numeric|min:0',
            'retainer_amount'   => 'nullable|numeric|min:0',
            'retainer_cycle'    => 'nullable|in:monthly,quarterly',
            'fixed_price'       => 'nullable|numeric|min:0',
            'next_billing_date' => 'nullable|date',
            'notes'             => 'nullable|string|max:1000',
        ]);

        ProjectBillingConfig::updateOrCreate(
            ['project_id' => $project->id],
            array_merge($data, ['tenant_id' => $this->tid()])
        );

        return back()->with('success', 'Konfigurasi billing berhasil disimpan.');
    }

    // ── Milestones ────────────────────────────────────────────────

    public function storeMilestone(Request $request, Project $project)
    {
        abort_if($project->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'amount'     => 'required|numeric|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'due_date'   => 'nullable|date',
            'description'=> 'nullable|string|max:1000',
        ]);

        $maxSort = ProjectMilestone::where('project_id', $project->id)->max('sort_order') ?? 0;

        ProjectMilestone::create(array_merge($data, [
            'project_id' => $project->id,
            'tenant_id'  => $this->tid(),
            'status'     => 'pending',
            'sort_order' => $maxSort + 1,
        ]));

        return back()->with('success', 'Milestone berhasil ditambahkan.');
    }

    public function completeMilestone(ProjectMilestone $projectMilestone)
    {
        abort_if($projectMilestone->tenant_id !== $this->tid(), 403);
        if ($projectMilestone->status !== 'pending') return back()->with('error', 'Milestone sudah selesai/invoiced.');

        $projectMilestone->update([
            'status'       => 'completed',
            'completed_by' => auth()->id(),
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Milestone ditandai selesai.');
    }

    // ── Generate Invoice ──────────────────────────────────────────

    public function generateTimeMaterial(Request $request, Project $project, GlPostingService $glService)
    {
        abort_if($project->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'period_start' => 'required|date',
            'period_end'   => 'required|date|after_or_equal:period_start',
        ]);

        $config = $project->billingConfig;
        $rate = $config?->hourly_rate ?? 0;

        $timesheets = Timesheet::where('project_id', $project->id)
            ->where('billing_status', 'unbilled')
            ->whereBetween('date', [$data['period_start'], $data['period_end']])
            ->get();

        if ($timesheets->isEmpty()) return back()->with('error', 'Tidak ada timesheet unbilled di periode ini.');

        $totalHours = $timesheets->sum('hours');
        $laborAmount = $timesheets->sum(fn($t) => (float) $t->hours * ($t->hourly_rate > 0 ? (float) $t->hourly_rate : $rate));

        // Unbilled expenses in period
        $expenseAmount = $project->expenses()
            ->whereBetween('date', [$data['period_start'], $data['period_end']])
            ->sum('amount');

        $totalAmount = $laborAmount + $expenseAmount;

        DB::transaction(function () use ($project, $data, $timesheets, $totalHours, $rate, $laborAmount, $expenseAmount, $totalAmount, $glService) {
            // Create Invoice
            $invNumber = 'INV-PRJ-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4));
            $invoice = Invoice::create([
                'tenant_id'        => $this->tid(),
                'customer_id'      => $project->customer_id,
                'number'           => $invNumber,
                'subtotal_amount'  => $totalAmount,
                'tax_amount'       => 0,
                'total_amount'     => $totalAmount,
                'paid_amount'      => 0,
                'remaining_amount' => $totalAmount,
                'status'           => 'unpaid',
                'due_date'         => now()->addDays(30),
                'notes'            => "Project billing: {$project->name} ({$data['period_start']} - {$data['period_end']})",
            ]);

            // Create ProjectInvoice record
            $pi = ProjectInvoice::create([
                'project_id'     => $project->id,
                'tenant_id'      => $this->tid(),
                'invoice_id'     => $invoice->id,
                'billing_type'   => 'time_material',
                'period_start'   => $data['period_start'],
                'period_end'     => $data['period_end'],
                'hours'          => $totalHours,
                'hourly_rate'    => $rate,
                'labor_amount'   => $laborAmount,
                'expense_amount' => $expenseAmount,
                'total_amount'   => $totalAmount,
                'status'         => 'invoiced',
                'user_id'        => auth()->id(),
            ]);

            // Mark timesheets as billed
            Timesheet::whereIn('id', $timesheets->pluck('id'))
                ->update(['billing_status' => 'billed', 'project_invoice_id' => $pi->id]);

            // GL posting
            $glResult = $glService->postInvoiceCreated(
                $this->tid(), auth()->id(), $invNumber, $invoice->id,
                $totalAmount, 0, $totalAmount
            );
            if ($glResult->isFailed()) session()->flash('gl_warning', $glResult->warningMessage());
        });

        return back()->with('success', "Invoice T&M berhasil dibuat. {$totalHours}h × Rp " . number_format($rate, 0, ',', '.'));
    }

    public function generateMilestone(ProjectMilestone $projectMilestone, GlPostingService $glService)
    {
        abort_if($projectMilestone->tenant_id !== $this->tid(), 403);
        if ($projectMilestone->status !== 'completed') return back()->with('error', 'Milestone harus completed dulu.');

        $project = $projectMilestone->project;
        $amount = (float) $projectMilestone->amount;

        DB::transaction(function () use ($project, $projectMilestone, $amount, $glService) {
            $invNumber = 'INV-MS-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4));
            $invoice = Invoice::create([
                'tenant_id'        => $this->tid(),
                'customer_id'      => $project->customer_id,
                'number'           => $invNumber,
                'subtotal_amount'  => $amount,
                'tax_amount'       => 0,
                'total_amount'     => $amount,
                'paid_amount'      => 0,
                'remaining_amount' => $amount,
                'status'           => 'unpaid',
                'due_date'         => now()->addDays(30),
                'notes'            => "Milestone: {$projectMilestone->name} — {$project->name}",
            ]);

            ProjectInvoice::create([
                'project_id'   => $project->id,
                'tenant_id'    => $this->tid(),
                'invoice_id'   => $invoice->id,
                'billing_type' => 'milestone',
                'total_amount' => $amount,
                'milestone_id' => $projectMilestone->id,
                'status'       => 'invoiced',
                'user_id'      => auth()->id(),
            ]);

            $projectMilestone->update(['status' => 'invoiced']);

            $glResult = $glService->postInvoiceCreated(
                $this->tid(), auth()->id(), $invNumber, $invoice->id, $amount, 0, $amount
            );
            if ($glResult->isFailed()) session()->flash('gl_warning', $glResult->warningMessage());
        });

        return back()->with('success', 'Invoice milestone berhasil dibuat.');
    }

    public function generateRetainer(Project $project, GlPostingService $glService)
    {
        abort_if($project->tenant_id !== $this->tid(), 403);
        $config = $project->billingConfig;
        if (!$config || $config->billing_type !== 'retainer') return back()->with('error', 'Project bukan tipe retainer.');

        $amount = (float) $config->retainer_amount;
        if ($amount <= 0) return back()->with('error', 'Retainer amount = 0.');

        DB::transaction(function () use ($project, $config, $amount, $glService) {
            $invNumber = 'INV-RTN-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4));
            $periodStart = $config->next_billing_date ?? now();
            $periodEnd = $config->retainer_cycle === 'quarterly'
                ? $periodStart->copy()->addMonths(3)->subDay()
                : $periodStart->copy()->addMonth()->subDay();

            $invoice = Invoice::create([
                'tenant_id'        => $this->tid(),
                'customer_id'      => $project->customer_id,
                'number'           => $invNumber,
                'subtotal_amount'  => $amount,
                'tax_amount'       => 0,
                'total_amount'     => $amount,
                'paid_amount'      => 0,
                'remaining_amount' => $amount,
                'status'           => 'unpaid',
                'due_date'         => now()->addDays(14),
                'notes'            => "Retainer: {$project->name} ({$periodStart->format('d/m/Y')} - {$periodEnd->format('d/m/Y')})",
            ]);

            ProjectInvoice::create([
                'project_id'   => $project->id,
                'tenant_id'    => $this->tid(),
                'invoice_id'   => $invoice->id,
                'billing_type' => 'retainer',
                'period_start' => $periodStart,
                'period_end'   => $periodEnd,
                'total_amount' => $amount,
                'status'       => 'invoiced',
                'user_id'      => auth()->id(),
            ]);

            // Advance next billing date
            $nextDate = $config->retainer_cycle === 'quarterly'
                ? $periodStart->copy()->addMonths(3)
                : $periodStart->copy()->addMonth();
            $config->update(['next_billing_date' => $nextDate]);

            $glResult = $glService->postInvoiceCreated(
                $this->tid(), auth()->id(), $invNumber, $invoice->id, $amount, 0, $amount
            );
            if ($glResult->isFailed()) session()->flash('gl_warning', $glResult->warningMessage());
        });

        return back()->with('success', 'Invoice retainer berhasil dibuat.');
    }
}
