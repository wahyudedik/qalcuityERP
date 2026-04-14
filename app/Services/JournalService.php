<?php

namespace App\Services;

use App\Models\AccountingPeriod;
use App\Models\JournalEntry;

class JournalService
{
    /**
     * Buat jurnal entry baru dengan validasi status accounting period.
     *
     * @param  array  $data  Harus mengandung 'tenant_id' dan 'date'.
     * @throws \DomainException  Jika periode dalam status locked atau closed.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException  Jika periode tidak ditemukan.
     */
    public function createJournalEntry(array $data): JournalEntry
    {
        $date = $data['date'] ?? now()->toDateString();

        $period = AccountingPeriod::where('tenant_id', $data['tenant_id'])
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->firstOrFail();

        if (in_array($period->status, ['locked', 'closed'])) {
            throw new \DomainException(
                "Tidak dapat membuat jurnal: periode {$period->name} dalam status {$period->status}."
            );
        }

        // Pastikan period_id terisi dari periode yang ditemukan
        $data['period_id'] = $data['period_id'] ?? $period->id;

        return JournalEntry::create($data);
    }
}
