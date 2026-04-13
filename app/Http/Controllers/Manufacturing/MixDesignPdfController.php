<?php

namespace App\Http\Controllers\Manufacturing;

use App\Http\Controllers\Controller;
use App\Models\ConcreteMixDesign;
use App\Services\Manufacturing\MixDesignCalculatorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MixDesignPdfController extends Controller
{
    protected MixDesignCalculatorService $calculator;

    public function __construct(MixDesignCalculatorService $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Export Mix Design calculation to PDF
     */
    public function exportCalculation(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $request->validate([
            'mix_design_id' => 'required|exists:concrete_mix_designs,id',
            'volume' => 'required|numeric|min:0.1',
            'waste_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $mixDesign = ConcreteMixDesign::find($request->mix_design_id);
        abort_if($mixDesign->tenant_id !== $tenantId, 403);

        $volume = (float) $request->volume;
        $waste = (float) ($request->waste_percent ?? 5);

        $calculation = $this->calculator->calculateForVolume($mixDesign, $volume, $waste);
        $costAnalysis = $this->calculator->calculateCostAnalysis($mixDesign, $volume, $tenantId);
        $availability = $this->calculator->checkMaterialAvailability($mixDesign, $volume, $tenantId);

        $pdf = Pdf::loadView('pdf.mix-design-calculation', compact(
            'mixDesign',
            'calculation',
            'costAnalysis',
            'availability',
            'volume',
            'waste'
        ));

        $filename = 'MixDesign_' . $mixDesign->grade . '_' . date('Y-m-d_His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export MRP Planning Report to PDF
     */
    public function exportMrpReport(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $planningService = app(\App\Services\Manufacturing\MrpPlanningService::class);
        $mrpService = app(\App\Services\MrpService::class);

        $bomId = $request->bom_id;
        $quantity = (float) ($request->quantity ?? 1);

        // Get MRP results
        $mrpResults = $bomId
            ? $mrpService->calculate(\App\Models\Bom::find($bomId), $quantity, $tenantId)
            : $mrpService->runFullMrp($tenantId);

        // Get planning report
        $planningReport = $planningService->generatePlanningReport($tenantId, $bomId, $quantity);

        $pdf = Pdf::loadView('pdf.mrp-planning-report', compact(
            'mrpResults',
            'planningReport',
            'bomId',
            'quantity'
        ));

        $filename = 'MRP_Report_' . date('Y-m-d_His') . '.pdf';

        return $pdf->download($filename);
    }
}
