<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Bed;
use App\Models\DailyAnalytics;
use App\Models\LabOrder;
use App\Models\MedicalBill;
use App\Models\OutpatientVisit;
use App\Models\Patient;
use App\Models\PatientAdmission;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\RadiologyExam;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecordDailyAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'healthcare:analytics:daily
                            {--tenant= : Specific tenant ID to process}
                            {--date= : Specific date (YYYY-MM-DD), defaults to yesterday}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record daily healthcare KPI snapshots for analytics';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('📊 Recording daily healthcare analytics...');

        $date = $this->option('date') ? now()->parse($this->option('date')) : now()->subDay();
        $tenantId = $this->option('tenant');

        $tenants = $tenantId
            ? [Tenant::find($tenantId)]
            : Tenant::whereHas('users', function ($q) {
                $q->whereHas('roles', function ($q) {
                    $q->where('name', 'admin');
                });
            })->get();

        $processedCount = 0;

        foreach ($tenants as $tenant) {
            try {
                $this->recordTenantAnalytics($tenant, $date);
                $processedCount++;
            } catch (\Exception $e) {
                $this->error("❌ Failed to record analytics for tenant {$tenant->id}: {$e->getMessage()}");
                Log::error('Daily analytics failed', [
                    'tenant_id' => $tenant->id,
                    'date' => $date->toDateString(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("✅ Successfully recorded analytics for {$processedCount} tenant(s)");

        return Command::SUCCESS;
    }

    /**
     * Record analytics for a specific tenant
     */
    protected function recordTenantAnalytics($tenant, $date): void
    {
        $tenantId = $tenant->id;
        $dateStr = $date->toDateString();
        $dayOfWeek = $date->format('l');

        $this->info("Processing tenant {$tenantId} for {$dateStr}...");

        // Patient Statistics
        $newPatients = Patient::where('tenant_id', $tenantId)
            ->whereDate('created_at', $dateStr)
            ->count();

        $totalPatients = Patient::where('tenant_id', $tenantId)->count();

        // Visit Statistics
        $totalVisits = OutpatientVisit::where('tenant_id', $tenantId)
            ->whereDate('visit_date', $dateStr)
            ->count();

        $completedVisits = OutpatientVisit::where('tenant_id', $tenantId)
            ->whereDate('visit_date', $dateStr)
            ->where('status', 'completed')
            ->count();

        // Admission Statistics
        $newAdmissions = PatientAdmission::where('tenant_id', $tenantId)
            ->whereDate('admission_date', $dateStr)
            ->count();

        $discharges = PatientAdmission::where('tenant_id', $tenantId)
            ->whereDate('discharge_date', $dateStr)
            ->count();

        // Appointment Statistics
        $scheduledAppointments = Appointment::where('tenant_id', $tenantId)
            ->whereDate('appointment_date', $dateStr)
            ->where('status', 'scheduled')
            ->count();

        $completedAppointments = Appointment::where('tenant_id', $tenantId)
            ->whereDate('appointment_date', $dateStr)
            ->where('status', 'completed')
            ->count();

        $noShows = Appointment::where('tenant_id', $tenantId)
            ->whereDate('appointment_date', $dateStr)
            ->where('status', 'no_show')
            ->count();

        // Revenue Statistics
        $dailyRevenue = MedicalBill::where('tenant_id', $tenantId)
            ->whereDate('bill_date', $dateStr)
            ->sum('total_amount');

        $paymentsReceived = Payment::where('tenant_id', $tenantId)
            ->whereDate('payment_date', $dateStr)
            ->sum('amount');

        // Department Statistics
        $departmentStats = OutpatientVisit::where('tenant_id', $tenantId)
            ->whereDate('visit_date', $dateStr)
            ->select('department', DB::raw('COUNT(*) as count'))
            ->groupBy('department')
            ->get()
            ->pluck('count', 'department')
            ->toArray();

        // Bed Occupancy
        $totalBeds = Bed::where('tenant_id', $tenantId)->count();
        $occupiedBeds = Bed::where('tenant_id', $tenantId)
            ->where('status', 'occupied')
            ->count();
        $occupancyRate = $totalBeds > 0 ? ($occupiedBeds / $totalBeds * 100) : 0;

        // Lab & Radiology
        $labOrders = LabOrder::where('tenant_id', $tenantId)
            ->whereDate('order_date', $dateStr)
            ->count();

        $radiologyExams = RadiologyExam::where('tenant_id', $tenantId)
            ->whereDate('exam_date', $dateStr)
            ->count();

        // Pharmacy
        $prescriptionsFilled = Prescription::where('tenant_id', $tenantId)
            ->whereDate('dispensed_date', $dateStr)
            ->where('status', 'dispensed')
            ->count();

        // Store snapshot
        DailyAnalytics::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'date' => $dateStr,
            ],
            [
                'day_of_week' => $dayOfWeek,
                'new_patients' => $newPatients,
                'total_patients' => $totalPatients,
                'total_visits' => $totalVisits,
                'completed_visits' => $completedVisits,
                'new_admissions' => $newAdmissions,
                'discharges' => $discharges,
                'scheduled_appointments' => $scheduledAppointments,
                'completed_appointments' => $completedAppointments,
                'no_shows' => $noShows,
                'daily_revenue' => $dailyRevenue,
                'payments_received' => $paymentsReceived,
                'total_beds' => $totalBeds,
                'occupied_beds' => $occupiedBeds,
                'occupancy_rate' => round($occupancyRate, 2),
                'lab_orders' => $labOrders,
                'radiology_exams' => $radiologyExams,
                'prescriptions_filled' => $prescriptionsFilled,
                'department_stats' => json_encode($departmentStats),
                'metadata' => json_encode([
                    'generated_at' => now()->toDateTimeString(),
                    'command' => 'healthcare:analytics:daily',
                ]),
            ]
        );

        $this->line("  ✓ New Patients: {$newPatients}");
        $this->line("  ✓ Total Visits: {$totalVisits}");
        $this->line('  ✓ Revenue: Rp '.number_format($dailyRevenue, 0, ',', '.'));
        $this->line("  ✓ Occupancy: {$occupancyRate}%");
    }
}
