<?php

namespace App\Services;

use App\Models\CrmActivity;
use App\Models\CrmLead;
use Carbon\Carbon;

class CrmAiService
{
    /**
     * Score a lead 0-100 based on activity, recency, stage, value, source.
     */
    public function scoreLead(CrmLead $lead): array
    {
        $activities = $lead->activities()->orderByDesc('created_at')->get();
        $score = 0;
        $breakdown = [];

        // 1. Activity volume (max 20)
        $actCount = $activities->count();
        $actScore = min(20, $actCount * 4);
        $score += $actScore;
        $breakdown[] = ['label' => 'Jumlah aktivitas', 'value' => $actCount, 'points' => $actScore];

        // 2. Recency of last contact (max 20)
        $daysSince = $lead->last_contact_at ? now()->diffInDays($lead->last_contact_at) : 999;
        $recencyScore = $daysSince <= 3 ? 20 : ($daysSince <= 7 ? 15 : ($daysSince <= 14 ? 10 : ($daysSince <= 30 ? 5 : 0)));
        $score += $recencyScore;
        $breakdown[] = ['label' => 'Recency kontak', 'value' => $daysSince . ' hari lalu', 'points' => $recencyScore];

        // 3. Activity outcomes (max 20)
        $interested = $activities->where('outcome', 'interested')->count();
        $notInterested = $activities->where('outcome', 'not_interested')->count();
        $outcomeScore = min(20, max(0, ($interested * 5) - ($notInterested * 8)));
        $score += $outcomeScore;
        $breakdown[] = ['label' => 'Outcome positif', 'value' => $interested . ' tertarik, ' . $notInterested . ' tidak', 'points' => $outcomeScore];

        // 4. Stage progression (max 20)
        $stageScore = match ($lead->stage) {
            'new' => 2, 'contacted' => 6, 'qualified' => 10,
            'proposal' => 14, 'negotiation' => 20, 'won' => 20, 'lost' => 0, default => 0,
        };
        $score += $stageScore;
        $breakdown[] = ['label' => 'Stage', 'value' => $lead->stage, 'points' => $stageScore];

        // 5. Estimated value (max 10)
        $valueScore = $lead->estimated_value >= 100_000_000 ? 10 : ($lead->estimated_value >= 50_000_000 ? 7 : ($lead->estimated_value >= 10_000_000 ? 4 : ($lead->estimated_value > 0 ? 2 : 0)));
        $score += $valueScore;
        $breakdown[] = ['label' => 'Nilai estimasi', 'value' => 'Rp ' . number_format($lead->estimated_value, 0, ',', '.'), 'points' => $valueScore];

        // 6. Source quality (max 10)
        $sourceScore = match ($lead->source) {
            'referral' => 10, 'exhibition' => 7, 'website' => 5,
            'social_media' => 4, 'cold_call' => 2, default => 0,
        };
        $score += $sourceScore;
        $breakdown[] = ['label' => 'Sumber lead', 'value' => $lead->source ?? '-', 'points' => $sourceScore];

        $score = min(100, max(0, $score));
        $tier = $score >= 70 ? 'hot' : ($score >= 40 ? 'warm' : 'cold');
        $tierLabel = ['hot' => '🔥 Hot', 'warm' => '🌤 Warm', 'cold' => '❄️ Cold'][$tier];

        return [
            'score'     => $score,
            'tier'      => $tier,
            'tier_label' => $tierLabel,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Suggest next follow-up action based on history and stage.
     */
    public function suggestFollowUp(CrmLead $lead): array
    {
        $activities = $lead->activities()->orderByDesc('created_at')->get();
        $lastActivity = $activities->first();
        $daysSinceLast = $lastActivity ? now()->diffInDays($lastActivity->created_at) : null;

        $suggestions = [];
        $priority = 'normal';

        // No activity yet
        if ($activities->isEmpty()) {
            return [
                'action'    => 'call',
                'action_label' => 'Telepon',
                'message'   => 'Lead baru belum pernah dihubungi. Segera lakukan kontak pertama via telepon.',
                'priority'  => 'high',
                'suggestions' => ['Perkenalkan produk secara singkat', 'Identifikasi kebutuhan utama', 'Jadwalkan demo jika tertarik'],
            ];
        }

        // Overdue follow-up
        $overdueFollowUp = $activities->whereNotNull('next_follow_up')
            ->filter(fn($a) => $a->next_follow_up && $a->next_follow_up->isPast())
            ->first();

        if ($overdueFollowUp) {
            $priority = 'high';
            $suggestions[] = 'Follow-up sudah lewat jadwal — hubungi segera';
        }

        // Last outcome based suggestions
        $lastOutcome = $lastActivity?->outcome;
        $lastType = $lastActivity?->type;

        if ($lastOutcome === 'not_interested') {
            return [
                'action'    => 'email',
                'action_label' => 'Email',
                'message'   => 'Lead terakhir menyatakan tidak tertarik. Kirim email nurturing dengan value proposition berbeda.',
                'priority'  => 'low',
                'suggestions' => ['Kirim case study relevan', 'Tawarkan trial gratis atau demo singkat', 'Tanyakan keberatan spesifik'],
            ];
        }

        if ($lastOutcome === 'interested' || $lastOutcome === 'follow_up') {
            $priority = 'high';
        }

        // Stage-based action recommendation
        $action = match ($lead->stage) {
            'new'         => ['type' => 'call', 'label' => 'Telepon', 'msg' => 'Lakukan kontak awal untuk kualifikasi kebutuhan.'],
            'contacted'   => ['type' => 'meeting', 'label' => 'Meeting', 'msg' => 'Jadwalkan meeting untuk presentasi produk.'],
            'qualified'   => ['type' => 'demo', 'label' => 'Demo', 'msg' => 'Kirimkan demo atau proposal awal.'],
            'proposal'    => ['type' => 'whatsapp', 'label' => 'WhatsApp', 'msg' => 'Follow-up proposal yang sudah dikirim, tanyakan feedback.'],
            'negotiation' => ['type' => 'call', 'label' => 'Telepon', 'msg' => 'Hubungi untuk finalisasi negosiasi dan closing.'],
            default       => ['type' => 'call', 'label' => 'Telepon', 'msg' => 'Hubungi untuk update status.'],
        };

        // Stale lead warning
        if ($daysSinceLast !== null && $daysSinceLast > 14) {
            $priority = 'high';
            $suggestions[] = "Lead tidak aktif selama {$daysSinceLast} hari — perlu re-engagement";
        }

        // Successful pattern from same-stage leads in tenant
        $successPattern = CrmActivity::where('tenant_id', $lead->tenant_id)
            ->whereHas('lead', fn($q) => $q->where('stage', 'won'))
            ->where('type', $action['type'])
            ->where('outcome', 'interested')
            ->count();

        if ($successPattern > 0) {
            $suggestions[] = "Aktivitas {$action['label']} terbukti efektif di {$successPattern} lead yang berhasil closed";
        }

        if ($lastOutcome === 'interested') {
            $suggestions[] = 'Lead menunjukkan minat — jangan tunda follow-up lebih dari 2 hari';
        }

        $suggestions[] = $action['msg'];

        return [
            'action'       => $action['type'],
            'action_label' => $action['label'],
            'message'      => $action['msg'],
            'priority'     => $priority,
            'days_since_last' => $daysSinceLast,
            'last_outcome' => $lastOutcome,
            'suggestions'  => array_unique($suggestions),
        ];
    }

    /**
     * Batch score all active leads for a tenant.
     */
    public function scoreAll(int $tenantId): array
    {
        $leads = CrmLead::where('tenant_id', $tenantId)
            ->whereNotIn('stage', ['won', 'lost'])
            ->with('activities')
            ->get();

        $results = [];
        foreach ($leads as $lead) {
            $s = $this->scoreLead($lead);
            $results[$lead->id] = ['score' => $s['score'], 'tier' => $s['tier'], 'tier_label' => $s['tier_label']];
        }
        return $results;
    }
}
