<?php

namespace App\Services;

use App\Models\CosmeticBatchRecord;
use App\Models\CosmeticFormula;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BatchPdfExportService
{
    /**
     * TASK-2.33: Create batch record PDF export
     */
    public function generateBatchRecordPdf(CosmeticBatchRecord $batch): string
    {
        $batch->load([
            'formula',
            'formula.ingredients',
            'qualityChecks',
            'reworkLogs',
            'producer',
            'qcInspector',
            'creator',
        ]);

        $yieldAnalysis = app(BatchProductionService::class)->analyzeYield($batch);

        $data = [
            'batch' => $batch,
            'yieldAnalysis' => $yieldAnalysis,
            'generated_at' => now()->format('d M Y H:i'),
            'generated_by' => Auth::check() ? Auth::user()->name : 'System',
        ];

        $pdf = Pdf::loadView('cosmetic.batches.pdf.batch-record', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf->stream("batch-record-{$batch->batch_number}.pdf");
    }

    /**
     * Generate and save batch PDF
     */
    public function saveBatchRecordPdf(CosmeticBatchRecord $batch): string
    {
        $batch->load([
            'formula',
            'formula.ingredients',
            'qualityChecks',
            'reworkLogs',
            'producer',
            'qcInspector',
            'creator',
        ]);

        $yieldAnalysis = app(BatchProductionService::class)->analyzeYield($batch);

        $data = [
            'batch' => $batch,
            'yieldAnalysis' => $yieldAnalysis,
            'generated_at' => now()->format('d M Y H:i'),
            'generated_by' => Auth::check() ? Auth::user()->name : 'System',
        ];

        $pdf = Pdf::loadView('cosmetic.batches.pdf.batch-record', $data)
            ->setPaper('a4', 'portrait');

        $filename = "batch-record-{$batch->batch_number}.pdf";
        $path = "cosmetic/batches/pdf/{$filename}";

        Storage::put($path, $pdf->output());

        return $path;
    }

    /**
     * Generate batch label PDF
     */
    public function generateBatchLabel(CosmeticBatchRecord $batch, int $copies = 1): string
    {
        $data = [
            'batch' => $batch,
            'copies' => $copies,
            'company_name' => config('brand.name', 'QalcuityERP'),
        ];

        $pdf = Pdf::loadView('cosmetic.batches.pdf.batch-label', $data)
            ->setPaper('a6', 'portrait');

        return $pdf->stream("batch-label-{$batch->batch_number}.pdf");
    }

    /**
     * Generate Certificate of Analysis (CoA)
     */
    public function generateCertificateOfAnalysis(CosmeticBatchRecord $batch): string
    {
        $batch->load([
            'formula',
            'qualityChecks',
            'qcInspector',
        ]);

        if (! $batch->isReleased()) {
            throw new \InvalidArgumentException('Batch must be released to generate CoA');
        }

        $data = [
            'batch' => $batch,
            'company_name' => config('brand.name', 'QalcuityERP'),
            'company_address' => config('brand.address', ''),
            'generated_at' => now()->format('d M Y H:i'),
        ];

        $pdf = Pdf::loadView('cosmetic.batches.pdf.certificate-of-analysis', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->stream("coa-{$batch->batch_number}.pdf");
    }

    /**
     * Generate yield report PDF
     */
    public function generateYieldReport(int $formulaId, int $months = 6): string
    {
        $service = app(BatchProductionService::class);
        $trends = $service->getYieldTrends($formulaId, $months);

        $formula = CosmeticFormula::findOrFail($formulaId);

        $data = [
            'formula' => $formula,
            'trends' => $trends,
            'period' => "{$months} months",
            'generated_at' => now()->format('d M Y H:i'),
        ];

        $pdf = Pdf::loadView('cosmetic.batches.pdf.yield-report', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->stream("yield-report-{$formula->formula_code}.pdf");
    }
}
