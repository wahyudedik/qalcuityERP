<?php

namespace App\Services\ERP;

use App\Models\CrmActivity;
use App\Models\CrmLead;
use Carbon\Carbon;

class CrmTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name' => 'create_lead',
                'description' => 'Tambah prospek/lead baru ke pipeline CRM.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string', 'description' => 'Nama kontak/prospek'],
                        'company' => ['type' => 'string', 'description' => 'Nama perusahaan (opsional)'],
                        'phone' => ['type' => 'string', 'description' => 'Nomor telepon (opsional)'],
                        'email' => ['type' => 'string', 'description' => 'Email (opsional)'],
                        'source' => ['type' => 'string', 'description' => 'referral, website, cold_call, social_media, exhibition'],
                        'product_interest' => ['type' => 'string', 'description' => 'Produk/layanan yang diminati'],
                        'estimated_value' => ['type' => 'number', 'description' => 'Estimasi nilai deal (Rp)'],
                        'expected_close_date' => ['type' => 'string', 'description' => 'Target closing YYYY-MM-DD'],
                        'notes' => ['type' => 'string', 'description' => 'Catatan awal'],
                    ],
                    'required' => ['name'],
                ],
            ],
            [
                'name' => 'update_lead_stage',
                'description' => 'Update tahap/stage lead di pipeline CRM.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'lead_name' => ['type' => 'string', 'description' => 'Nama lead/prospek'],
                        'stage' => ['type' => 'string', 'description' => 'new, contacted, qualified, proposal, negotiation, won, lost'],
                        'probability' => ['type' => 'integer', 'description' => 'Probabilitas closing 0-100%'],
                        'notes' => ['type' => 'string', 'description' => 'Catatan update'],
                    ],
                    'required' => ['lead_name', 'stage'],
                ],
            ],
            [
                'name' => 'log_crm_activity',
                'description' => 'Catat aktivitas follow-up ke lead (telepon, email, meeting, WhatsApp, demo).',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'lead_name' => ['type' => 'string', 'description' => 'Nama lead/prospek'],
                        'type' => ['type' => 'string', 'description' => 'call, email, meeting, whatsapp, demo, proposal'],
                        'description' => ['type' => 'string', 'description' => 'Hasil/catatan aktivitas'],
                        'outcome' => ['type' => 'string', 'description' => 'interested, not_interested, follow_up, closed'],
                        'next_follow_up' => ['type' => 'string', 'description' => 'Tanggal follow-up berikutnya YYYY-MM-DD'],
                    ],
                    'required' => ['lead_name', 'type', 'description'],
                ],
            ],
            [
                'name' => 'get_pipeline',
                'description' => 'Tampilkan pipeline CRM — semua lead beserta stage dan nilai deal.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'stage' => ['type' => 'string', 'description' => 'Filter stage tertentu (opsional)'],
                        'source' => ['type' => 'string', 'description' => 'Filter sumber lead (opsional)'],
                    ],
                ],
            ],
            [
                'name' => 'get_follow_up_today',
                'description' => 'Tampilkan lead yang perlu di-follow-up hari ini atau yang sudah overdue.',
                'parameters' => ['type' => 'object', 'properties' => []],
            ],
        ];
    }

    public function createLead(array $args): array
    {
        $lead = CrmLead::create([
            'tenant_id' => $this->tenantId,
            'assigned_to' => $this->userId,
            'name' => $args['name'],
            'company' => $args['company'] ?? null,
            'phone' => $args['phone'] ?? null,
            'email' => $args['email'] ?? null,
            'source' => $args['source'] ?? null,
            'stage' => 'new',
            'estimated_value' => $args['estimated_value'] ?? 0,
            'product_interest' => $args['product_interest'] ?? null,
            'expected_close_date' => $args['expected_close_date'] ?? null,
            'probability' => 10,
            'notes' => $args['notes'] ?? null,
            'last_contact_at' => now(),
        ]);

        return [
            'status' => 'success',
            'message' => "Lead **{$lead->name}**".($lead->company ? " ({$lead->company})" : '').' berhasil ditambahkan ke pipeline.'
                .($lead->estimated_value > 0 ? ' Estimasi nilai: Rp '.number_format($lead->estimated_value, 0, ',', '.').'.' : ''),
        ];
    }

    public function updateLeadStage(array $args): array
    {
        $lead = CrmLead::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['lead_name']}%")
            ->first();

        if (! $lead) {
            return ['status' => 'error', 'message' => "Lead '{$args['lead_name']}' tidak ditemukan."];
        }

        $oldStage = $lead->stage;
        $lead->update([
            'stage' => $args['stage'],
            'probability' => $args['probability'] ?? $this->defaultProbability($args['stage']),
            'notes' => $args['notes'] ?? $lead->notes,
            'last_contact_at' => now(),
        ]);

        $emoji = match ($args['stage']) {
            'won' => '🎉',
            'lost' => '❌',
            default => '📊',
        };

        return [
            'status' => 'success',
            'message' => "{$emoji} Lead **{$lead->name}** dipindah dari **{$oldStage}** → **{$args['stage']}**."
                ." Probabilitas: {$lead->probability}%.",
        ];
    }

    public function logCrmActivity(array $args): array
    {
        $lead = CrmLead::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['lead_name']}%")
            ->first();

        if (! $lead) {
            return ['status' => 'error', 'message' => "Lead '{$args['lead_name']}' tidak ditemukan."];
        }

        CrmActivity::create([
            'tenant_id' => $this->tenantId,
            'lead_id' => $lead->id,
            'user_id' => $this->userId,
            'type' => $args['type'],
            'description' => $args['description'],
            'outcome' => $args['outcome'] ?? null,
            'next_follow_up' => $args['next_follow_up'] ?? null,
        ]);

        $lead->update(['last_contact_at' => now()]);

        $msg = "Aktivitas **{$args['type']}** dengan **{$lead->name}** berhasil dicatat.";
        if (! empty($args['next_follow_up'])) {
            $msg .= ' Follow-up berikutnya: '.Carbon::parse($args['next_follow_up'])->format('d M Y').'.';
        }

        return ['status' => 'success', 'message' => $msg];
    }

    public function getPipeline(array $args): array
    {
        $query = CrmLead::where('tenant_id', $this->tenantId)->whereNotIn('stage', ['won', 'lost']);

        if (! empty($args['stage'])) {
            $query->where('stage', $args['stage']);
        }
        if (! empty($args['source'])) {
            $query->where('source', $args['source']);
        }

        $leads = $query->orderByRaw("FIELD(stage,'new','contacted','qualified','proposal','negotiation')")->get();

        if ($leads->isEmpty()) {
            return ['status' => 'success', 'message' => 'Pipeline kosong. Tambahkan lead baru dengan create_lead.'];
        }

        $totalValue = $leads->sum('estimated_value');
        $weightedValue = $leads->sum(fn ($l) => $l->estimated_value * $l->probability / 100);

        return [
            'status' => 'success',
            'total_leads' => $leads->count(),
            'total_value' => 'Rp '.number_format($totalValue, 0, ',', '.'),
            'weighted_value' => 'Rp '.number_format($weightedValue, 0, ',', '.'),
            'data' => $leads->map(fn ($l) => [
                'nama' => $l->name,
                'perusahaan' => $l->company ?? '-',
                'stage' => $l->stage,
                'nilai' => 'Rp '.number_format($l->estimated_value, 0, ',', '.'),
                'probabilitas' => $l->probability.'%',
                'produk' => $l->product_interest ?? '-',
                'last_contact' => $l->last_contact_at?->diffForHumans() ?? '-',
            ])->toArray(),
        ];
    }

    public function getFollowUpToday(array $args): array
    {
        $leads = CrmLead::where('tenant_id', $this->tenantId)
            ->where(fn ($q) => $q
                ->whereHas('activities', fn ($aq) => $aq->where('next_follow_up', '<=', today()))
                ->orWhere(fn ($oq) => $oq
                    ->where('last_contact_at', '<', now()->subDays(7))
                    ->whereNotIn('stage', ['won', 'lost']))
            )
            ->with(['activities' => fn ($q) => $q->latest()->limit(1)])
            ->get();

        if ($leads->isEmpty()) {
            return ['status' => 'success', 'message' => 'Tidak ada lead yang perlu di-follow-up hari ini.'];
        }

        return [
            'status' => 'success',
            'data' => $leads->map(fn ($l) => [
                'nama' => $l->name,
                'stage' => $l->stage,
                'last_contact' => $l->last_contact_at?->format('d M Y') ?? 'Belum pernah',
                'phone' => $l->phone ?? '-',
            ])->toArray(),
        ];
    }

    private function defaultProbability(string $stage): int
    {
        return match ($stage) {
            'new' => 10,
            'contacted' => 20,
            'qualified' => 40,
            'proposal' => 60,
            'negotiation' => 80,
            'won' => 100,
            'lost' => 0,
            default => 10,
        };
    }
}
