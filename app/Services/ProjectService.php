<?php

namespace App\Services;

use App\Models\ProjectTask;

/**
 * ProjectService — Layanan utama modul Proyek.
 *
 * Bug 1.18 Fix: Progress task dibatasi maksimum 100%.
 * Kalkulasi menggunakan min(100.0, ...) sehingga progress tidak
 * pernah melebihi 100% meskipun actualVolume > plannedVolume.
 */
class ProjectService
{
    /**
     * Update progress task berdasarkan volume aktual.
     *
     * Bug_Condition: module = 'proyek' AND progressExceeds100(input)
     * Expected_Behavior: progress selalu dalam range 0–100
     * Preservation: progress normal (actualVolume <= plannedVolume) tetap dihitung benar
     *
     * @param  ProjectTask  $task  Task yang akan diupdate
     * @param  float  $actualVolume  Volume aktual yang sudah dikerjakan
     *
     * @throws \DomainException jika planned_volume tidak valid (<= 0)
     */
    public function updateTaskProgress(ProjectTask $task, float $actualVolume): void
    {
        if ($task->target_volume <= 0) {
            throw new \DomainException('Volume rencana task tidak valid (harus > 0).');
        }

        // Cap progress maksimum 100% menggunakan min(100.0, ...)
        $progress = min(100.0, ($actualVolume / $task->target_volume) * 100);

        $task->update([
            'actual_volume' => $actualVolume,
            'progress' => $progress,
        ]);
    }
}
