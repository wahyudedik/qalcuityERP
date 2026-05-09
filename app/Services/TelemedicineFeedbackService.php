<?php

namespace App\Services;

use App\Models\TeleconsultationFeedback;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TelemedicineFeedbackService
{
    /**
     * Submit feedback for consultation.
     */
    public function submitFeedback(array $data): TeleconsultationFeedback
    {
        return DB::transaction(function () use ($data) {
            $feedback = TeleconsultationFeedback::create([
                'consultation_id' => $data['consultation_id'],
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'],
                'rating' => $data['rating'],
                'video_quality' => $data['video_quality'] ?? null,
                'audio_quality' => $data['audio_quality'] ?? null,
                'doctor_rating' => $data['doctor_rating'] ?? $data['rating'],
                'platform_rating' => $data['platform_rating'] ?? null,
                'feedback' => $data['feedback'] ?? null,
                'positive_feedback' => $data['positive_feedback'] ?? null,
                'negative_feedback' => $data['negative_feedback'] ?? null,
                'suggestions' => $data['suggestions'] ?? null,
                'feedback_tags' => $data['feedback_tags'] ?? null,
                'is_anonymous' => $data['is_anonymous'] ?? false,
                'is_public' => $data['is_public'] ?? false,
                'would_recommend' => $data['would_recommend'] ?? true,
                'would_use_again' => $data['would_use_again'] ?? true,
                'needs_followup' => $data['needs_followup'] ?? false,
                'followup_notes' => $data['followup_notes'] ?? null,
            ]);

            Log::info('Feedback submitted', [
                'feedback_id' => $feedback->id,
                'consultation_id' => $feedback->consultation_id,
                'rating' => $feedback->rating,
            ]);

            return $feedback;
        });
    }

    /**
     * Get feedback for consultation.
     */
    public function getConsultationFeedback(int $consultationId): ?TeleconsultationFeedback
    {
        return TeleconsultationFeedback::where('consultation_id', $consultationId)
            ->with(['patient', 'doctor'])
            ->first();
    }

    /**
     * Get doctor's average rating.
     */
    public function getDoctorAverageRating(int $doctorId): array
    {
        $stats = TeleconsultationFeedback::where('doctor_id', $doctorId)
            ->selectRaw('
                COUNT(*) as total_feedbacks,
                AVG(rating) as avg_rating,
                AVG(doctor_rating) as avg_doctor_rating,
                AVG(video_quality) as avg_video_quality,
                AVG(audio_quality) as avg_audio_quality,
                SUM(CASE WHEN would_recommend = 1 THEN 1 ELSE 0 END) as recommend_count
            ')
            ->first();

        if (! $stats || $stats->total_feedbacks == 0) {
            return [
                'total_feedbacks' => 0,
                'avg_rating' => 0,
                'avg_doctor_rating' => 0,
                'avg_video_quality' => 0,
                'avg_audio_quality' => 0,
                'recommendation_rate' => 0,
            ];
        }

        return [
            'total_feedbacks' => $stats->total_feedbacks,
            'avg_rating' => round($stats->avg_rating, 2),
            'avg_doctor_rating' => round($stats->avg_doctor_rating, 2),
            'avg_video_quality' => round($stats->avg_video_quality ?? 0, 2),
            'avg_audio_quality' => round($stats->avg_audio_quality ?? 0, 2),
            'recommendation_rate' => round(($stats->recommend_count / $stats->total_feedbacks) * 100, 2),
        ];
    }

    /**
     * Get tenant telemedicine statistics.
     */
    public function getTenantTelemedicineStats(int $tenantId): array
    {
        $feedbacks = TeleconsultationFeedback::whereHas('consultation', function ($query) use ($tenantId) {
            $query->whereHas('patient', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            });
        });

        return [
            'total_feedbacks' => $feedbacks->count(),
            'avg_rating' => round($feedbacks->avg('rating') ?? 0, 2),
            'avg_doctor_rating' => round($feedbacks->avg('doctor_rating') ?? 0, 2),
            'positive_feedbacks' => $feedbacks->where('rating', '>=', 4)->count(),
            'negative_feedbacks' => $feedbacks->where('rating', '<=', 2)->count(),
            'recommendation_rate' => $feedbacks->where('would_recommend', true)->count() > 0
                ? round(($feedbacks->where('would_recommend', true)->count() / $feedbacks->count()) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get recent feedback for doctor.
     */
    public function getDoctorRecentFeedback(int $doctorId, int $limit = 10): Collection
    {
        return TeleconsultationFeedback::where('doctor_id', $doctorId)
            ->with(['consultation', 'patient'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Check if consultation already has feedback.
     */
    public function hasFeedback(int $consultationId): bool
    {
        return TeleconsultationFeedback::where('consultation_id', $consultationId)->exists();
    }
}
