<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesOrder;
use App\Models\ScheduledReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessScheduledReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and send scheduled reports';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing scheduled reports...');

        $dueReports = ScheduledReport::due()->get();

        if ($dueReports->isEmpty()) {
            $this->info('No scheduled reports due.');

            return 0;
        }

        $this->info("Found {$dueReports->count()} report(s) to process.");

        $processed = 0;
        $failed = 0;

        foreach ($dueReports as $report) {
            try {
                $this->info("Processing: {$report->name}");

                // Generate report
                $reportData = $this->generateReport($report);

                // Send to recipients
                $this->sendReport($report, $reportData);

                // Mark as executed
                $report->markAsExecuted();

                $processed++;
                $this->info('✓ Report sent successfully');

            } catch (\Throwable $e) {
                $failed++;
                $report->markAsFailed($e->getMessage());
                Log::error("Scheduled report failed: {$report->name}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->error("✗ Failed: {$e->getMessage()}");
            }
        }

        $this->info("\nSummary:");
        $this->info("Processed: {$processed}");
        $this->error("Failed: {$failed}");

        return 0;
    }

    /**
     * Generate report data
     */
    protected function generateReport(ScheduledReport $report): array
    {
        $tenantId = $report->tenant_id;
        $metrics = $report->metrics;
        $filters = $report->filters ?? [];

        $data = [];

        foreach ($metrics as $metric) {
            $data[$metric] = match ($metric) {
                'revenue' => $this->getRevenueData($tenantId, $filters),
                'orders' => $this->getOrdersData($tenantId, $filters),
                'customers' => $this->getCustomersData($tenantId, $filters),
                'inventory' => $this->getInventoryData($tenantId, $filters),
                default => [],
            };
        }

        return [
            'report_name' => $report->name,
            'generated_at' => now(),
            'date_range' => $filters['date_range'] ?? 'Last 30 days',
            'metrics' => $data,
        ];
    }

    /**
     * Send report via email
     */
    protected function sendReport(ScheduledReport $report, array $reportData): void
    {
        foreach ($report->recipients as $email) {
            // TODO: Create ScheduledReportEmail mailable
            // For now, send simple notification
            Mail::raw("Report: {$report->name}\n\nGenerated at: ".now()->format('Y-m-d H:i:s'), function ($message) use ($email, $report) {
                $message->to($email)
                    ->subject("Scheduled Report: {$report->name}");
            });
        }
    }

    /**
     * Get revenue data
     */
    protected function getRevenueData(int $tenantId, array $filters): array
    {
        $startDate = $filters['start_date'] ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $filters['end_date'] ?? now()->format('Y-m-d');

        return [
            'total' => Invoice::where('tenant_id', $tenantId)
                ->whereBetween('invoice_date', [$startDate, $endDate])
                ->sum('total_amount'),
            'growth' => 0, // Calculate growth
            'by_day' => Invoice::where('tenant_id', $tenantId)
                ->whereBetween('invoice_date', [$startDate, $endDate])
                ->selectRaw('DATE(invoice_date) as date, SUM(total_amount) as total')
                ->groupBy('date')
                ->get()
                ->toArray(),
        ];
    }

    /**
     * Get orders data
     */
    protected function getOrdersData(int $tenantId, array $filters): array
    {
        $startDate = $filters['start_date'] ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $filters['end_date'] ?? now()->format('Y-m-d');

        return [
            'total' => SalesOrder::where('tenant_id', $tenantId)
                ->whereBetween('order_date', [$startDate, $endDate])
                ->count(),
            'completed' => SalesOrder::where('tenant_id', $tenantId)
                ->whereBetween('order_date', [$startDate, $endDate])
                ->where('status', 'completed')
                ->count(),
            'avg_value' => SalesOrder::where('tenant_id', $tenantId)
                ->whereBetween('order_date', [$startDate, $endDate])
                ->avg('total_amount') ?? 0,
        ];
    }

    /**
     * Get customers data
     */
    protected function getCustomersData(int $tenantId, array $filters): array
    {
        return [
            'total' => Customer::where('tenant_id', $tenantId)->count(),
            'new_this_month' => Customer::where('tenant_id', $tenantId)
                ->whereMonth('created_at', now()->month)
                ->count(),
            'active' => Customer::where('tenant_id', $tenantId)
                ->whereHas('salesOrders', function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId)
                        ->where('order_date', '>=', now()->subDays(30)->format('Y-m-d'));
                })
                ->count(),
        ];
    }

    /**
     * Get inventory data
     */
    protected function getInventoryData(int $tenantId, array $filters): array
    {
        return [
            'total_products' => Product::where('tenant_id', $tenantId)->count(),
            'in_stock' => ProductStock::where('tenant_id', $tenantId)
                ->where('quantity', '>', 0)
                ->count(),
            'low_stock' => ProductStock::where('tenant_id', $tenantId)
                ->where('quantity', '<=', DB::raw('reorder_level'))
                ->count(),
            'out_of_stock' => ProductStock::where('tenant_id', $tenantId)
                ->where('quantity', '<=', 0)
                ->count(),
        ];
    }
}
