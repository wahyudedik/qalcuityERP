<?php

namespace App\Services;

use App\Models\DocumentNumberSequence;
use Illuminate\Support\Facades\DB;

/**
 * DocumentNumberService — Task 37
 *
 * Numbering terpusat, sequential, tidak bisa reuse walau record dihapus.
 * Format bisa dikonfigurasi per tenant via doc_number_prefix di tabel tenants.
 *
 * Format default:
 *   INV-2026-0001   (invoice)
 *   PO-2026-0001    (purchase order)
 *   SO-2026-0001    (sales order)
 *   QUO-2026-0001   (quotation)
 *   JE-2026-0001    (journal entry)
 *   JRV-2026-0001   (journal reversal)
 *   PR-2026-0001    (purchase requisition)
 *   RFQ-2026-0001   (request for quotation)
 *   GR-2026-0001    (goods receipt)
 *   WT-2026-0001    (warehouse transfer)
 *   EXP-2026-0001   (expense)
 */
class DocumentNumberService
{
    /**
     * Generate nomor dokumen berikutnya.
     * Menggunakan DB lock untuk mencegah race condition.
     *
     * @param  int    $tenantId
     * @param  string $docType   Jenis dokumen: invoice, po, so, je, dll
     * @param  string $prefix    Override prefix (opsional, default dari $docType)
     * @param  string $periodKey YYYY atau YYYYMM (default: tahun sekarang)
     * @return string            Nomor dokumen, e.g. INV-2026-0001
     */
    public function generate(
        int    $tenantId,
        string $docType,
        string $prefix = '',
        string $periodKey = ''
    ): string {
        if (empty($periodKey)) {
            $periodKey = date('Y');
        }

        if (empty($prefix)) {
            $prefix = $this->defaultPrefix($docType);
        }

        $sequence = DB::transaction(function () use ($tenantId, $docType, $periodKey) {
            // Lock row untuk mencegah race condition
            $seq = DocumentNumberSequence::lockForUpdate()
                ->where('tenant_id', $tenantId)
                ->where('doc_type', $docType)
                ->where('period_key', $periodKey)
                ->first();

            if ($seq) {
                $seq->increment('last_number');
                return $seq->fresh()->last_number;
            } else {
                DocumentNumberSequence::create([
                    'tenant_id'   => $tenantId,
                    'doc_type'    => $docType,
                    'period_key'  => $periodKey,
                    'last_number' => 1,
                ]);
                return 1;
            }
        });

        return $prefix . '-' . $periodKey . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate dengan format bulan: INV-202601-0001
     */
    public function generateMonthly(
        int    $tenantId,
        string $docType,
        string $prefix = ''
    ): string {
        return $this->generate($tenantId, $docType, $prefix, date('Ym'));
    }

    /**
     * Peek nomor berikutnya tanpa increment (untuk preview).
     */
    public function peek(int $tenantId, string $docType, string $periodKey = ''): string
    {
        if (empty($periodKey)) {
            $periodKey = date('Y');
        }

        $prefix = $this->defaultPrefix($docType);

        $last = DocumentNumberSequence::where('tenant_id', $tenantId)
            ->where('doc_type', $docType)
            ->where('period_key', $periodKey)
            ->value('last_number') ?? 0;

        return $prefix . '-' . $periodKey . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Prefix default per jenis dokumen.
     */
    private function defaultPrefix(string $docType): string
    {
        return match ($docType) {
            'invoice'   => 'INV',
            'po'        => 'PO',
            'so'        => 'SO',
            'quotation' => 'QUO',
            'je'        => 'JE',
            'jrv'       => 'JRV',
            'pr'        => 'PR',
            'rfq'       => 'RFQ',
            'gr'        => 'GR',
            'wt'        => 'WT',
            'expense'   => 'EXP',
            'payroll'            => 'PAY',
            'writeoff'           => 'WO',
            'deferred_deferred_revenue' => 'DR',
            'deferred_prepaid_expense'  => 'PE',
            default              => strtoupper($docType),
        };
    }
}
