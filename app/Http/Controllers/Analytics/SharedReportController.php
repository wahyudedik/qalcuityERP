<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\SharedReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SharedReportController extends Controller
{
    /**
     * View a shared report
     */
    public function view(Request $request, string $id)
    {
        $sharedReport = SharedReport::where('report_id', $id)
            ->with(['creator:id,name,email'])
            ->first();

        if (!$sharedReport) {
            abort(404, 'Report not found');
        }

        // Check if report is accessible
        if (!$sharedReport->isAccessible()) {
            if ($sharedReport->isExpired()) {
                return view('analytics.shared-report-expired', compact('sharedReport'));
            }

            abort(403, 'Report is no longer available');
        }

        // Record access
        $sharedReport->recordAccess();

        // Check access level permissions
        $accessLevel = $sharedReport->access_level;
        $canDownload = in_array($accessLevel, ['view', 'download']);
        $canEdit = $accessLevel === 'edit';

        // If user is authenticated, check tenant isolation
        $user = Auth::user();
        $isOwner = $user && $user->tenant_id === $sharedReport->tenant_id;

        return view('analytics.shared-report-view', compact(
            'sharedReport',
            'canDownload',
            'canEdit',
            'isOwner'
        ));
    }

    /**
     * Download shared report (if allowed)
     */
    public function download(Request $request, string $id, string $format = 'pdf')
    {
        $sharedReport = SharedReport::where('report_id', $id)->first();

        if (!$sharedReport || !$sharedReport->isAccessible()) {
            abort(403, 'Report is no longer available');
        }

        if (!in_array($sharedReport->access_level, ['view', 'download'])) {
            abort(403, 'Download not allowed for this report');
        }

        // Record access
        $sharedReport->recordAccess();

        $reportData = $sharedReport->report_data;

        // Generate download based on format
        return match ($format) {
            'pdf' => $this->downloadAsPdf($sharedReport, $reportData),
            'excel' => $this->downloadAsExcel($sharedReport, $reportData),
            'csv' => $this->downloadAsCsv($sharedReport, $reportData),
            default => abort(400, 'Invalid format'),
        };
    }

    /**
     * Download as PDF
     */
    protected function downloadAsPdf(SharedReport $sharedReport, array $reportData)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('analytics.exports.shared-report-pdf', [
            'sharedReport' => $sharedReport,
            'reportData' => $reportData,
        ]);

        return $pdf->download("{$sharedReport->name}.pdf");
    }

    /**
     * Download as Excel
     */
    protected function downloadAsExcel(SharedReport $sharedReport, array $reportData)
    {
        $excel = new class ($sharedReport, $reportData) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            private $sharedReport;
            private $reportData;

            public function __construct($sharedReport, $reportData)
            {
                $this->sharedReport = $sharedReport;
                $this->reportData = $reportData;
            }

            public function array(): array
            {
                $data = [];
                foreach ($this->reportData['data'] ?? [] as $metric => $value) {
                    $data[] = [$metric, is_array($value) ? json_encode($value) : $value];
                }
                return $data;
            }

            public function headings(): array
            {
                return ['Metric', 'Value'];
            }
        };

        return \Maatwebsite\Excel\Facades\Excel::download(
            $excel,
            "{$sharedReport->name}.xlsx"
        );
    }

    /**
     * Download as CSV
     */
    protected function downloadAsCsv(SharedReport $sharedReport, array $reportData)
    {
        $filename = "{$sharedReport->name}.csv";
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, ['Metric', 'Value']);
        foreach ($reportData['data'] ?? [] as $metric => $value) {
            fputcsv($handle, [
                $metric,
                is_array($value) ? json_encode($value) : $value
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }
}
