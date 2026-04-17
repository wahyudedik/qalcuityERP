<?php

namespace App\Services\DemoData\Generators;

use App\Services\DemoData\BaseIndustryGenerator;
use App\Services\DemoData\CoreDataContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ServicesGenerator extends BaseIndustryGenerator
{
    public function getIndustryName(): string
    {
        return 'services';
    }

    public function generate(CoreDataContext $ctx): array
    {
        $tenantId      = $ctx->tenantId;
        $employeeIds   = $ctx->employeeIds;
        $recordsCreated = 0;
        $generatedData  = [];

        // ── 1. Clients / Customers (10) ────────────────────────────────────
        $clientIds = [];
        try {
            $clientIds = $this->seedClients($tenantId);
            $recordsCreated += count($clientIds);
            $generatedData['clients'] = count($clientIds);
        } catch (\Throwable $e) {
            $this->logWarning('ServicesGenerator: failed to seed clients', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['clients'] = 0;
        }

        // ── 2. Projects (5 — planning, in_progress x2, completed x2) ──────
        $projectIds = [];
        try {
            if (!empty($clientIds)) {
                $projectIds = $this->seedProjects($tenantId, $clientIds);
                $recordsCreated += count($projectIds);
                $generatedData['projects'] = count($projectIds);
            } else {
                $this->logWarning('ServicesGenerator: skipping projects — no clients', [
                    'tenant_id' => $tenantId,
                ]);
                $generatedData['projects'] = 0;
            }
        } catch (\Throwable $e) {
            $this->logWarning('ServicesGenerator: failed to seed projects', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['projects'] = 0;
        }

        // ── 3. Timesheet Entries (10 — 2 per project) ─────────────────────
        try {
            if (!empty($projectIds)) {
                $tsCount = $this->seedTimesheets($tenantId, $projectIds, $employeeIds);
                $recordsCreated += $tsCount;
                $generatedData['timesheets'] = $tsCount;
            } else {
                $this->logWarning('ServicesGenerator: skipping timesheets — no projects', [
                    'tenant_id' => $tenantId,
                ]);
                $generatedData['timesheets'] = 0;
            }
        } catch (\Throwable $e) {
            $this->logWarning('ServicesGenerator: failed to seed timesheets', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['timesheets'] = 0;
        }

        // ── 4. Invoices (5 — 1 per project) ───────────────────────────────
        try {
            if (!empty($projectIds) && !empty($clientIds)) {
                $invCount = $this->seedInvoices($tenantId, $projectIds, $clientIds);
                $recordsCreated += $invCount;
                $generatedData['invoices'] = $invCount;
            } else {
                $this->logWarning('ServicesGenerator: skipping invoices — no projects or clients', [
                    'tenant_id' => $tenantId,
                ]);
                $generatedData['invoices'] = 0;
            }
        } catch (\Throwable $e) {
            $this->logWarning('ServicesGenerator: failed to seed invoices', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['invoices'] = 0;
        }

        // ── 5. CRM Leads (5 — different stages) ───────────────────────────
        try {
            $leadCount = $this->seedCrmLeads($tenantId, $employeeIds);
            $recordsCreated += $leadCount;
            $generatedData['crm_leads'] = $leadCount;
        } catch (\Throwable $e) {
            $this->logWarning('ServicesGenerator: failed to seed CRM leads', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['crm_leads'] = 0;
        }

        return [
            'records_created' => $recordsCreated,
            'generated_data'  => $generatedData,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  Clients — 10 customers with business type
    // ─────────────────────────────────────────────────────────────

    private function seedClients(int $tenantId): array
    {
        $clients = [
            ['name' => 'PT Maju Bersama Digital',    'email' => 'info@majubersamadigital.co.id',  'phone' => '021-5551001', 'company' => 'PT Maju Bersama Digital',    'address' => 'Jl. Sudirman No. 10, Jakarta',       'business_type' => 'technology'],
            ['name' => 'CV Karya Mandiri Konsultan',  'email' => 'admin@karyamandiri.co.id',       'phone' => '021-5551002', 'company' => 'CV Karya Mandiri Konsultan',  'address' => 'Jl. Gatot Subroto No. 25, Jakarta',  'business_type' => 'consulting'],
            ['name' => 'PT Solusi Inovasi Nusantara', 'email' => 'contact@solusiinovasi.co.id',    'phone' => '022-5551003', 'company' => 'PT Solusi Inovasi Nusantara', 'address' => 'Jl. Asia Afrika No. 5, Bandung',     'business_type' => 'technology'],
            ['name' => 'Yayasan Pendidikan Cerdas',   'email' => 'info@ypcerdas.org',              'phone' => '031-5551004', 'company' => 'Yayasan Pendidikan Cerdas',   'address' => 'Jl. Pemuda No. 15, Surabaya',        'business_type' => 'education'],
            ['name' => 'PT Bangun Properti Sejahtera','email' => 'admin@bangunproperti.co.id',     'phone' => '021-5551005', 'company' => 'PT Bangun Properti Sejahtera','address' => 'Jl. Kuningan No. 8, Jakarta',        'business_type' => 'property'],
            ['name' => 'CV Logistik Andalan Jaya',    'email' => 'ops@logistikandalan.co.id',      'phone' => '024-5551006', 'company' => 'CV Logistik Andalan Jaya',    'address' => 'Jl. Pemuda No. 30, Semarang',        'business_type' => 'logistics'],
            ['name' => 'PT Media Kreatif Indonesia',  'email' => 'hello@mediakreatif.co.id',       'phone' => '021-5551007', 'company' => 'PT Media Kreatif Indonesia',  'address' => 'Jl. Kemang Raya No. 12, Jakarta',    'business_type' => 'media'],
            ['name' => 'Klinik Sehat Bersama',        'email' => 'admin@kliniksehat.co.id',        'phone' => '022-5551008', 'company' => 'Klinik Sehat Bersama',        'address' => 'Jl. Dago No. 45, Bandung',           'business_type' => 'healthcare'],
            ['name' => 'PT Agro Nusantara Makmur',    'email' => 'info@agronusantara.co.id',       'phone' => '0274-5551009','company' => 'PT Agro Nusantara Makmur',    'address' => 'Jl. Malioboro No. 20, Yogyakarta',   'business_type' => 'agriculture'],
            ['name' => 'CV Retail Fashion Terkini',   'email' => 'cs@retailfashion.co.id',         'phone' => '031-5551010', 'company' => 'CV Retail Fashion Terkini',   'address' => 'Jl. Tunjungan No. 5, Surabaya',      'business_type' => 'retail'],
        ];

        $clientIds = [];

        foreach ($clients as $c) {
            $existing = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->where('email', $c['email'])
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                $clientIds[] = (int) $existing->id;
                continue;
            }

            $id = DB::table('customers')->insertGetId([
                'tenant_id'    => $tenantId,
                'name'         => $c['name'],
                'email'        => $c['email'],
                'phone'        => $c['phone'],
                'company'      => $c['company'],
                'address'      => $c['address'],
                'credit_limit' => rand(10, 50) * 1000000,
                'is_active'    => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            $clientIds[] = (int) $id;
        }

        return $clientIds;
    }

    // ─────────────────────────────────────────────────────────────
    //  Projects — 5 with statuses: planning(1), active(2), completed(2)
    // ─────────────────────────────────────────────────────────────

    private function seedProjects(int $tenantId, array $clientIds): array
    {
        // projects requires user_id — resolve any user for this tenant
        $userId = DB::table('users')->where('tenant_id', $tenantId)->value('id');
        if (!$userId) {
            $this->logWarning('ServicesGenerator: no user found for tenant, skipping projects', [
                'tenant_id' => $tenantId,
            ]);
            return [];
        }

        $projects = [
            [
                'number'      => 'SVC-PRJ-' . $tenantId . '-001',
                'name'        => 'Implementasi Sistem ERP PT Maju Bersama',
                'description' => 'Implementasi sistem ERP lengkap mencakup modul keuangan, HRM, dan inventory.',
                'type'        => 'general',
                'status'      => 'planning',
                'start_date'  => Carbon::now()->addDays(7)->format('Y-m-d'),
                'end_date'    => Carbon::now()->addMonths(6)->format('Y-m-d'),
                'budget'      => 150000000,
                'progress'    => 0,
                'client_idx'  => 0,
            ],
            [
                'number'      => 'SVC-PRJ-' . $tenantId . '-002',
                'name'        => 'Konsultasi Transformasi Digital CV Karya Mandiri',
                'description' => 'Konsultasi dan pendampingan transformasi digital proses bisnis.',
                'type'        => 'general',
                'status'      => 'active',
                'start_date'  => Carbon::now()->subDays(30)->format('Y-m-d'),
                'end_date'    => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'budget'      => 80000000,
                'progress'    => 35,
                'client_idx'  => 1,
            ],
            [
                'number'      => 'SVC-PRJ-' . $tenantId . '-003',
                'name'        => 'Pengembangan Aplikasi Mobile PT Solusi Inovasi',
                'description' => 'Pengembangan aplikasi mobile Android dan iOS untuk platform e-commerce.',
                'type'        => 'general',
                'status'      => 'active',
                'start_date'  => Carbon::now()->subDays(45)->format('Y-m-d'),
                'end_date'    => Carbon::now()->addMonths(2)->format('Y-m-d'),
                'budget'      => 200000000,
                'progress'    => 60,
                'client_idx'  => 2,
            ],
            [
                'number'      => 'SVC-PRJ-' . $tenantId . '-004',
                'name'        => 'Audit Sistem Informasi Yayasan Pendidikan Cerdas',
                'description' => 'Audit menyeluruh sistem informasi dan keamanan data yayasan.',
                'type'        => 'general',
                'status'      => 'completed',
                'start_date'  => Carbon::now()->subMonths(3)->format('Y-m-d'),
                'end_date'    => Carbon::now()->subDays(15)->format('Y-m-d'),
                'budget'      => 45000000,
                'progress'    => 100,
                'client_idx'  => 3,
            ],
            [
                'number'      => 'SVC-PRJ-' . $tenantId . '-005',
                'name'        => 'Desain & Branding PT Media Kreatif Indonesia',
                'description' => 'Proyek rebranding lengkap termasuk logo, panduan merek, dan materi pemasaran.',
                'type'        => 'general',
                'status'      => 'completed',
                'start_date'  => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'end_date'    => Carbon::now()->subDays(5)->format('Y-m-d'),
                'budget'      => 35000000,
                'progress'    => 100,
                'client_idx'  => 6,
            ],
        ];

        $projectIds = [];

        foreach ($projects as $p) {
            $existing = DB::table('projects')
                ->where('tenant_id', $tenantId)
                ->where('number', $p['number'])
                ->first();

            if ($existing) {
                $projectIds[] = (int) $existing->id;
                continue;
            }

            $customerId = $clientIds[$p['client_idx'] % count($clientIds)];

            $id = DB::table('projects')->insertGetId([
                'tenant_id'   => $tenantId,
                'user_id'     => $userId,
                'customer_id' => $customerId,
                'number'      => $p['number'],
                'name'        => $p['name'],
                'description' => $p['description'],
                'type'        => $p['type'],
                'status'      => $p['status'],
                'start_date'  => $p['start_date'],
                'end_date'    => $p['end_date'],
                'budget'      => $p['budget'],
                'actual_cost' => 0,
                'progress'    => $p['progress'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            $projectIds[] = (int) $id;
        }

        return $projectIds;
    }

    // ─────────────────────────────────────────────────────────────
    //  Timesheets — 2 entries per project (10 total)
    // ─────────────────────────────────────────────────────────────

    private function seedTimesheets(int $tenantId, array $projectIds, array $employeeIds): int
    {
        $descriptions = [
            ['Analisis kebutuhan dan dokumentasi requirement', 'Review dan finalisasi dokumen spesifikasi'],
            ['Kickoff meeting dan perencanaan proyek', 'Penyusunan timeline dan alokasi sumber daya'],
            ['Pengembangan fitur autentikasi dan otorisasi', 'Unit testing dan code review modul utama'],
            ['Presentasi hasil audit kepada stakeholder', 'Penyusunan laporan audit final'],
            ['Desain konsep visual dan mockup', 'Revisi desain berdasarkan feedback klien'],
        ];

        $rows = [];
        $userId = !empty($employeeIds) ? $employeeIds[0] : null;

        foreach ($projectIds as $idx => $projectId) {
            $descPair = $descriptions[$idx % count($descriptions)];
            $baseDate = Carbon::now()->subDays(rand(5, 60));

            foreach ($descPair as $descIdx => $desc) {
                $entryDate = $baseDate->copy()->addDays($descIdx * 3)->format('Y-m-d');

                // Idempotency: check by project_id + date + description
                $exists = DB::table('timesheets')
                    ->where('tenant_id', $tenantId)
                    ->where('project_id', $projectId)
                    ->where('date', $entryDate)
                    ->where('description', $desc)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $hours      = rand(4, 8);
                $hourlyRate = rand(150, 350) * 1000;

                $rows[] = [
                    'tenant_id'      => $tenantId,
                    'project_id'     => $projectId,
                    'user_id'        => $userId,
                    'date'           => $entryDate,
                    'hours'          => $hours,
                    'description'    => $desc,
                    'hourly_rate'    => $hourlyRate,
                    'billing_status' => 'unbilled',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }
        }

        if (!empty($rows)) {
            $this->bulkInsert('timesheets', $rows);
        }

        return count($rows);
    }

    // ─────────────────────────────────────────────────────────────
    //  Invoices — 1 per project (5 total), linked to project & client
    // ─────────────────────────────────────────────────────────────

    private function seedInvoices(int $tenantId, array $projectIds, array $clientIds): int
    {
        // project_invoices requires user_id
        $userId = DB::table('users')->where('tenant_id', $tenantId)->value('id');
        if (!$userId) {
            $this->logWarning('ServicesGenerator: no user found for tenant, skipping invoices', [
                'tenant_id' => $tenantId,
            ]);
            return 0;
        }

        $count = 0;

        foreach ($projectIds as $idx => $projectId) {
            $exists = DB::table('project_invoices')
                ->where('tenant_id', $tenantId)
                ->where('project_id', $projectId)
                ->exists();

            if ($exists) {
                continue;
            }

            $project    = DB::table('projects')->where('id', $projectId)->first();
            $amount     = $project ? (float) $project->budget * 0.3 : rand(10, 50) * 1000000;
            $periodStart = Carbon::now()->subDays(rand(30, 60))->format('Y-m-d');
            $periodEnd   = Carbon::now()->subDays(rand(5, 29))->format('Y-m-d');

            $status = match ($idx % 3) {
                0 => 'draft',
                1 => 'paid',
                default => 'invoiced',
            };

            DB::table('project_invoices')->insert([
                'tenant_id'      => $tenantId,
                'project_id'     => $projectId,
                'invoice_id'     => null,
                'billing_type'   => 'fixed_price',
                'period_start'   => $periodStart,
                'period_end'     => $periodEnd,
                'hours'          => 0,
                'hourly_rate'    => 0,
                'labor_amount'   => $amount,
                'expense_amount' => 0,
                'total_amount'   => $amount,
                'status'         => $status,
                'user_id'        => $userId,
                'notes'          => 'Invoice proyek jasa — ' . ($project->name ?? 'Demo Project'),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            $count++;
        }

        return $count;
    }

    // ─────────────────────────────────────────────────────────────
    //  CRM Leads — 5 leads with different stages
    // ─────────────────────────────────────────────────────────────

    private function seedCrmLeads(int $tenantId, array $employeeIds): int
    {
        $leads = [
            [
                'name'                => 'Budi Santoso',
                'company'             => 'PT Teknologi Masa Depan',
                'phone'               => '0812-1001-2001',
                'email'               => 'budi.santoso@teknologimasadepan.co.id',
                'source'              => 'website',
                'stage'               => 'new',
                'estimated_value'     => 75000000,
                'product_interest'    => 'Implementasi ERP & Konsultasi IT',
                'expected_close_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'probability'         => 20,
                'notes'               => 'Tertarik dengan modul keuangan dan HRM.',
            ],
            [
                'name'                => 'Siti Rahayu',
                'company'             => 'CV Distribusi Nusantara',
                'phone'               => '0813-2002-3002',
                'email'               => 'siti.rahayu@distribusinusantara.co.id',
                'source'              => 'referral',
                'stage'               => 'contacted',
                'estimated_value'     => 50000000,
                'product_interest'    => 'Sistem Manajemen Distribusi',
                'expected_close_date' => Carbon::now()->addMonths(2)->format('Y-m-d'),
                'probability'         => 40,
                'notes'               => 'Sudah dihubungi, menunggu jadwal demo.',
            ],
            [
                'name'                => 'Ahmad Fauzi',
                'company'             => 'PT Konstruksi Jaya Abadi',
                'phone'               => '0814-3003-4003',
                'email'               => 'ahmad.fauzi@konstruksijaya.co.id',
                'source'              => 'exhibition',
                'stage'               => 'qualified',
                'estimated_value'     => 120000000,
                'product_interest'    => 'Project Management & RAB System',
                'expected_close_date' => Carbon::now()->addMonths(2)->format('Y-m-d'),
                'probability'         => 60,
                'notes'               => 'Kebutuhan sudah teridentifikasi, siap presentasi proposal.',
            ],
            [
                'name'                => 'Dewi Lestari',
                'company'             => 'PT Retail Modern Indonesia',
                'phone'               => '0815-4004-5004',
                'email'               => 'dewi.lestari@retailmodern.co.id',
                'source'              => 'cold_call',
                'stage'               => 'proposal',
                'estimated_value'     => 90000000,
                'product_interest'    => 'POS & Inventory Management',
                'expected_close_date' => Carbon::now()->addMonths(1)->format('Y-m-d'),
                'probability'         => 75,
                'notes'               => 'Proposal sudah dikirim, menunggu keputusan direksi.',
            ],
            [
                'name'                => 'Hendra Wijaya',
                'company'             => 'PT Agribisnis Subur Makmur',
                'phone'               => '0816-5005-6005',
                'email'               => 'hendra.wijaya@agribisnis.co.id',
                'source'              => 'referral',
                'stage'               => 'closed_won',
                'estimated_value'     => 65000000,
                'product_interest'    => 'Sistem Manajemen Pertanian & Keuangan',
                'expected_close_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'probability'         => 100,
                'notes'               => 'Deal berhasil ditutup. Onboarding dijadwalkan minggu depan.',
            ],
        ];

        $assignedTo = !empty($employeeIds) ? $employeeIds[0] : null;
        $count      = 0;

        foreach ($leads as $lead) {
            $exists = DB::table('crm_leads')
                ->where('tenant_id', $tenantId)
                ->where('email', $lead['email'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('crm_leads')->insert([
                'tenant_id'            => $tenantId,
                'assigned_to'          => $assignedTo,
                'name'                 => $lead['name'],
                'company'              => $lead['company'],
                'phone'                => $lead['phone'],
                'email'                => $lead['email'],
                'source'               => $lead['source'],
                'stage'                => $lead['stage'],
                'estimated_value'      => $lead['estimated_value'],
                'product_interest'     => $lead['product_interest'],
                'expected_close_date'  => $lead['expected_close_date'],
                'probability'          => $lead['probability'],
                'notes'                => $lead['notes'],
                'last_contact_at'      => Carbon::now()->subDays(rand(1, 14))->toDateTimeString(),
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            $count++;
        }

        return $count;
    }
}
