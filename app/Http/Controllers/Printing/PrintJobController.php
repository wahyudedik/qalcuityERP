<?php

namespace App\Http\Controllers\Printing;

use App\Http\Controllers\Controller;
use App\Models\PressRun;
use App\Models\PrintEstimate;
use App\Models\PrintJob;
use App\Models\WebToPrintOrder;
use App\Services\JournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PrintJobController extends Controller
{
    /**
     * Display print job dashboard
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $stats = [
            'total_jobs' => PrintJob::where('tenant_id', $tenantId)->count(),
            'active_jobs' => PrintJob::where('tenant_id', $tenantId)->active()->count(),
            'completed_today' => PrintJob::where('tenant_id', $tenantId)
                ->whereDate('completed_at', today())->count(),
            'overdue_jobs' => PrintJob::where('tenant_id', $tenantId)->overdue()->count(),
            'urgent_jobs' => PrintJob::where('tenant_id', $tenantId)
                ->byPriority('urgent')->active()->count(),
        ];

        $jobs = PrintJob::where('tenant_id', $tenantId)
            ->with(['customer', 'assignedOperator'])
            ->active()
            ->whereNotNull('job_name')
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->paginate(20);

        $overdue = PrintJob::where('tenant_id', $tenantId)
            ->overdue()
            ->whereNotNull('job_name')
            ->with('customer')
            ->limit(5)
            ->get();

        return view('printing.dashboard', compact('stats', 'jobs', 'overdue'));
    }

    /**
     * Show create job form
     */
    public function create()
    {
        return view('printing.create-job');
    }

    /**
     * Store new print job
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_name' => 'required|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'product_type' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'due_date' => 'nullable|date',
            'paper_type' => 'nullable|string',
            'colors_front' => 'nullable|integer|min:0|max:6',
            'colors_back' => 'nullable|integer|min:0|max:6',
            'special_instructions' => 'nullable|string',
            'quoted_price' => 'nullable|numeric|min:0',
            'estimated_cost' => 'nullable|numeric|min:0',
        ]);

        try {
            $job = new PrintJob;
            $job->tenant_id = auth()->user()->tenant_id;
            $job->job_number = 'PJ'.now()->format('Ymd').str_pad(
                PrintJob::where('job_number', 'like', 'PJ'.now()->format('Ymd').'%')->count() + 1,
                4,
                '0',
                STR_PAD_LEFT
            );
            $job->fill($validated);
            $job->status = 'queued';
            $job->save();

            return redirect()->route('printing.show', $job)
                ->with('success', 'Print job berhasil dibuat!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal membuat print job: '.$e->getMessage());
        }
    }

    /**
     * Display job details
     */
    public function show($id)
    {
        $job = PrintJob::with([
            'customer',
            'prepressWorkflows.technician',
            'plates',
            'pressRuns.operator',
            'finishingOperations.operator',
            'assignedOperator',
            'estimates',
        ])->findOrFail($id);

        return view('printing.job-detail', compact('job'));
    }

    /**
     * Update job status
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:queued,prepress,platemaking,on_press,finishing,quality_check,completed,cancelled',
        ]);

        $job = PrintJob::findOrFail($id);
        $job->status = $validated['status'];

        if ($validated['status'] === 'completed') {
            $job->completed_at = now();
        }

        if ($validated['status'] === 'on_press' && ! $job->started_at) {
            $job->started_at = now();
        }

        $job->save();

        // Create journal entry when job is completed and has a quoted price
        if ($validated['status'] === 'completed' && $job->quoted_price > 0) {
            $this->createCompletionJournalEntry($job);
        }

        return back()->with('success', 'Status berhasil diperbarui!');
    }

    /**
     * Assign operator to job
     */
    public function assignOperator(Request $request, $id)
    {
        $validated = $request->validate([
            'operator_id' => 'required|exists:users,id',
        ]);

        $job = PrintJob::findOrFail($id);
        $job->assigned_operator = $validated['operator_id'];
        $job->save();

        return back()->with('success', 'Operator berhasil ditugaskan!');
    }

    /**
     * Approve proof
     */
    public function approveProof($id)
    {
        $job = PrintJob::findOrFail($id);
        $job->proof_approved = true;
        $job->proof_approved_at = now();
        $job->approved_by = auth()->id();
        $job->save();

        return back()->with('success', 'Proof berhasil disetujui!');
    }

    /**
     * Show press run tracking
     */
    public function trackPressRun($id)
    {
        $job = PrintJob::with(['pressRuns.operator'])->findOrFail($id);
        $currentRun = $job->pressRuns()->where('current_status', '!=', 'completed')->latest()->first();

        return view('printing.press-tracking', compact('job', 'currentRun'));
    }

    /**
     * Start press run
     */
    public function startPressRun(Request $request, $id)
    {
        $validated = $request->validate([
            'machine' => 'required|string',
            'operator_id' => 'required|exists:users,id',
        ]);

        $job = PrintJob::findOrFail($id);
        $job->status = 'on_press';
        $job->started_at = $job->started_at ?? now();
        $job->assigned_operator = $validated['operator_id'];
        $job->save();

        // Create press run record
        $job->pressRuns()->create([
            'tenant_id' => auth()->user()->tenant_id,
            'press_machine' => $validated['machine'],
            'target_quantity' => $job->quantity,
            'current_status' => 'setup',
            'operator_id' => $validated['operator_id'],
            'run_start' => now(),
        ]);

        return back()->with('success', 'Press run dimulai!');
    }

    /**
     * Update press run production
     */
    public function updateProduction(Request $request, $runId)
    {
        $validated = $request->validate([
            'produced_quantity' => 'required|integer|min:0',
            'waste_quantity' => 'nullable|integer|min:0',
        ]);

        $run = PressRun::findOrFail($runId);
        $run->produced_quantity = $validated['produced_quantity'];
        $run->waste_quantity = $validated['waste_quantity'] ?? 0;

        // Calculate speed
        if ($run->run_start) {
            $hoursElapsed = $run->run_start->diffInHours(now());
            if ($hoursElapsed > 0) {
                $run->production_speed = round($validated['produced_quantity'] / $hoursElapsed, 2);
            }
        }

        // Auto-complete if target reached
        if ($validated['produced_quantity'] >= $run->target_quantity) {
            $run->current_status = 'completed';
            $run->run_end = now();
            $run->printJob->status = 'finishing';
            $run->printJob->save();
        }

        $run->save();

        return back()->with('success', 'Produksi diperbarui!');
    }

    /**
     * Show finishing operations
     */
    public function finishingView($id)
    {
        $job = PrintJob::with(['finishingOperations.operator'])->findOrFail($id);

        return view('printing.finishing-operations', compact('job'));
    }

    /**
     * List estimates
     */
    public function estimates()
    {
        $estimates = PrintEstimate::where('tenant_id', auth()->user()->tenant_id)
            ->with(['customer', 'createdBy'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('printing.estimates', compact('estimates'));
    }

    /**
     * Generate new estimate
     */
    public function generateEstimate(Request $request)
    {
        $validated = $request->validate([
            'product_type' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'paper_type' => 'required|string',
            'paper_size_width' => 'required|numeric',
            'paper_size_height' => 'required|numeric',
            'colors_front' => 'nullable|integer|min:0|max:6',
            'colors_back' => 'nullable|integer|min:0|max:6',
            'markup_percentage' => 'nullable|numeric|min:0|max:100',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        // Cost calculation
        $quantity = $validated['quantity'];
        $paperCost = $quantity * 500;
        $plateCost = (($validated['colors_front'] ?? 4) + ($validated['colors_back'] ?? 0)) * 50000;
        $inkCost = $quantity * (($validated['colors_front'] ?? 4) + ($validated['colors_back'] ?? 0)) * 100;
        $laborCost = $quantity * 50;
        $machineCost = $quantity * 75;
        $finishingCost = 0;
        $overheadCost = ($paperCost + $plateCost + $inkCost + $laborCost + $machineCost) * 0.15;

        $totalCost = $paperCost + $plateCost + $inkCost + $laborCost + $machineCost + $finishingCost + $overheadCost;
        $markupPercentage = $validated['markup_percentage'] ?? 30;
        $quotedPrice = $totalCost * (1 + $markupPercentage / 100);
        $profitMargin = $quotedPrice - $totalCost;

        $estimate = PrintEstimate::create([
            'tenant_id' => auth()->user()->tenant_id,
            'estimate_number' => 'EST'.now()->format('Ymd').str_pad(
                PrintEstimate::where('estimate_number', 'like', 'EST'.now()->format('Ymd').'%')->count() + 1,
                4,
                '0',
                STR_PAD_LEFT
            ),
            'customer_id' => $validated['customer_id'] ?? null,
            'product_type' => $validated['product_type'],
            'quantity' => $quantity,
            'paper_type' => $validated['paper_type'],
            'paper_size_width' => $validated['paper_size_width'],
            'paper_size_height' => $validated['paper_size_height'],
            'colors_front' => $validated['colors_front'] ?? 4,
            'colors_back' => $validated['colors_back'] ?? 0,
            'paper_cost' => $paperCost,
            'plate_cost' => $plateCost,
            'ink_cost' => $inkCost,
            'labor_cost' => $laborCost,
            'machine_cost' => $machineCost,
            'finishing_cost' => $finishingCost,
            'overhead_cost' => $overheadCost,
            'total_cost' => $totalCost,
            'markup_percentage' => $markupPercentage,
            'quoted_price' => $quotedPrice,
            'profit_margin' => $profitMargin,
            'status' => 'draft',
            'valid_until' => now()->addDays(30),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('printing.estimates')
            ->with('success', 'Estimasi berhasil dibuat!');
    }

    /**
     * Web-to-print orders
     */
    public function webOrders()
    {
        $orders = WebToPrintOrder::where('tenant_id', auth()->user()->tenant_id)
            ->with(['customer', 'printJob'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('printing.web-orders', compact('orders'));
    }

    /**
     * Create journal entry when print job is completed (integration with Accounting)
     */
    protected function createCompletionJournalEntry(PrintJob $job): void
    {
        try {
            if (! class_exists(JournalService::class)) {
                return;
            }

            $journalService = app(JournalService::class);

            $journalService->createJournalEntry([
                'tenant_id' => $job->tenant_id,
                'date' => now()->toDateString(),
                'description' => "Pendapatan cetak - {$job->job_number}: {$job->job_name}",
                'reference_type' => 'print_job',
                'reference_id' => $job->id,
                'lines' => [
                    [
                        'account_code' => '1100', // Piutang Usaha
                        'debit' => $job->quoted_price,
                        'credit' => 0,
                        'description' => "Piutang cetak {$job->job_number}",
                    ],
                    [
                        'account_code' => '4100', // Pendapatan Jasa Cetak
                        'debit' => 0,
                        'credit' => $job->quoted_price,
                        'description' => "Pendapatan cetak {$job->job_number}",
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            // Log but don't fail the status update
            Log::warning('Gagal membuat jurnal untuk print job: '.$e->getMessage(), [
                'print_job_id' => $job->id,
            ]);
        }
    }
}
