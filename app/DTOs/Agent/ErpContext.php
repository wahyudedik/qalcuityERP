<?php

namespace App\DTOs\Agent;

use Carbon\Carbon;

class ErpContext
{
    public function __construct(
        public readonly int $tenantId,
        public readonly array $kpiSummary,       // revenue, critical_stock, overdue_ar, active_employees
        public readonly array $activeModules,
        public readonly ?string $accountingPeriod,
        public readonly array $industrySkills,
        public readonly Carbon $builtAt,
    ) {}

    /**
     * Konversi ERP context ke format system prompt untuk AI.
     */
    public function toSystemPrompt(): string
    {
        $modules = implode(', ', $this->activeModules);
        $skills = implode(', ', $this->industrySkills);
        $period = $this->accountingPeriod ?? 'tidak tersedia';

        $kpi = $this->kpiSummary;
        $revenue = $kpi['revenue'] ?? 'N/A';
        $criticalStock = $kpi['critical_stock'] ?? 'N/A';
        $overdueAr = $kpi['overdue_ar'] ?? 'N/A';
        $activeEmployees = $kpi['active_employees'] ?? 'N/A';

        return <<<PROMPT
        [ERP CONTEXT - Tenant #{$this->tenantId}]
        Periode Akuntansi: {$period}
        Modul Aktif: {$modules}
        Industry Skills: {$skills}

        KPI Ringkasan:
        - Pendapatan Bulan Ini: {$revenue}
        - Stok Kritis: {$criticalStock}
        - Piutang Jatuh Tempo: {$overdueAr}
        - Karyawan Aktif: {$activeEmployees}

        Konteks dibangun pada: {$this->builtAt->toDateTimeString()}
        PROMPT;
    }

    /**
     * Cek apakah context sudah kadaluarsa.
     */
    public function isStale(int $maxAgeSeconds = 300): bool
    {
        return $this->builtAt->diffInSeconds(Carbon::now()) > $maxAgeSeconds;
    }
}
