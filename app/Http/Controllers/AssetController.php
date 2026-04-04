<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\ActivityLog;
use App\Models\AssetDepreciation;
use App\Models\AssetMaintenance;
use App\Services\GlPostingService;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    private function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $tid = $this->tenantId();
        $query = Asset::where('tenant_id', $tid);

        if ($request->category)
            $query->where('category', $request->category);
        if ($request->status)
            $query->where('status', $request->status);
        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('asset_code', 'like', "%$s%"));
        }

        $assets = $query->orderBy('name')->paginate(20)->withQueryString();
        $totalValue = Asset::where('tenant_id', $tid)->where('status', 'active')->sum('current_value');
        $totalCost = Asset::where('tenant_id', $tid)->sum('purchase_price');

        return view('assets.index', compact('assets', 'totalValue', 'totalCost'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:vehicle,machine,equipment,furniture,building',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'purchase_date' => 'required|date',
            'purchase_price' => 'required|numeric|min:0',
            'salvage_value' => 'nullable|numeric|min:0',
            'useful_life_years' => 'required|integer|min:1|max:50',
            'depreciation_method' => 'required|in:straight_line,declining_balance',
        ]);

        $tid = $this->tenantId();
        $count = Asset::where('tenant_id', $tid)->count() + 1;

        Asset::create([
            'tenant_id' => $tid,
            'asset_code' => 'AST-' . now()->format('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT),
            'current_value' => $data['purchase_price'],
            'salvage_value' => $data['salvage_value'] ?? 0,
            'status' => 'active',
        ] + $data);

        return back()->with('success', "Aset {$data['name']} berhasil didaftarkan.");
    }

    public function update(Request $request, Asset $asset)
    {
        abort_unless($asset->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:active,maintenance,disposed,retired',
            'notes' => 'nullable|string',
        ]);

        $asset->update($data);

        return back()->with('success', "Aset {$asset->name} berhasil diperbarui.");
    }

    public function destroy(Asset $asset)
    {
        abort_unless($asset->tenant_id === $this->tenantId(), 403);

        $name = $asset->name;

        // Jika ada catatan depresiasi, hanya ubah status ke disposed
        if ($asset->depreciations()->exists()) {
            $asset->update(['status' => 'disposed']);
            return back()->with('success', "Aset {$name} ditandai sebagai disposed (memiliki riwayat depresiasi).");
        }

        $asset->maintenances()->delete();
        $asset->delete();

        ActivityLog::record('asset_deleted', "Aset dihapus: {$name}", null, ['name' => $name], []);

        return back()->with('success', "Aset {$name} berhasil dihapus.");
    }

    public function schedule(Asset $asset)
    {
        abort_unless($asset->tenant_id === $this->tenantId(), 403);

        $depreciations = $asset->depreciations()->with('journalEntry')->orderBy('period')->get();

        // Generate full projected schedule (remaining periods)
        $projected = [];
        if ($asset->status === 'active' && $asset->current_value > $asset->salvage_value) {
            $monthlyDep = $asset->monthlyDepreciation();
            $bookValue = (float) $asset->current_value;
            $salvage = (float) $asset->salvage_value;
            $lastPeriod = $depreciations->last()?->period ?? now()->subMonth()->format('Y-m');
            $maxMonths = ($asset->useful_life_years * 12) - $depreciations->count();

            for ($i = 1; $i <= min($maxMonths, 60); $i++) {
                $period = \Carbon\Carbon::parse($lastPeriod . '-01')->addMonths($i)->format('Y-m');
                $dep = min($monthlyDep, max(0, $bookValue - $salvage));
                $bookValue = max($salvage, $bookValue - $dep);
                $projected[] = ['period' => $period, 'amount' => $dep, 'book_value' => $bookValue];
                if ($bookValue <= $salvage)
                    break;
            }
        }

        return view('assets.schedule', compact('asset', 'depreciations', 'projected'));
    }

    public function depreciate(Request $request)
    {
        $data = $request->validate(['period' => 'required|date_format:Y-m']);

        $tid = $this->tenantId();
        $period = $data['period'];
        $assets = Asset::where('tenant_id', $tid)->where('status', 'active')->get();
        $total = 0;
        $count = 0;
        $assetLines = [];
        $depIds = [];

        foreach ($assets as $asset) {
            if (AssetDepreciation::where('asset_id', $asset->id)->where('period', $period)->exists())
                continue;

            $dep = $asset->monthlyDepreciation();
            if ($dep <= 0)
                continue;

            $newValue = max($asset->salvage_value, $asset->current_value - $dep);
            $actualDep = $asset->current_value - $newValue;
            if ($actualDep <= 0)
                continue;

            $depRecord = AssetDepreciation::create([
                'tenant_id' => $tid,
                'asset_id' => $asset->id,
                'period' => $period,
                'depreciation_amount' => $actualDep,
                'book_value_after' => $newValue,
            ]);

            $asset->update(['current_value' => $newValue]);
            $total += $actualDep;
            $assetLines[] = ['asset_name' => $asset->name, 'amount' => $actualDep];
            $depIds[] = $depRecord->id;
            $count++;
        }

        if ($count === 0) {
            return back()->with('info', "Tidak ada aset yang perlu didepresiasi untuk periode {$period}.");
        }

        // GL Auto-Posting: Dr Beban Penyusutan / Cr Akumulasi Penyusutan
        $glResult = app(GlPostingService::class)->postDepreciation(
            tenantId: $tid,
            userId: auth()->id(),
            period: $period,
            totalAmount: $total,
            assetLines: $assetLines,
        );

        // Link journal ke semua AssetDepreciation records
        if ($glResult->isSuccess() && $glResult->journal) {
            AssetDepreciation::whereIn('id', $depIds)
                ->update(['journal_entry_id' => $glResult->journal->id]);
        }

        $successMsg = "Depresiasi {$period} berhasil dihitung untuk {$count} aset. Total: Rp " . number_format($total, 0, ',', '.');

        if ($glResult->isFailed()) {
            return back()
                ->with('success', $successMsg)
                ->with('warning', $glResult->warningMessage());
        }

        return back()->with('success', $successMsg . " Jurnal GL: {$glResult->journal->number}.");
    }

    public function maintenance(Request $request)
    {
        $tid = $this->tenantId();
        $query = AssetMaintenance::where('tenant_id', $tid)->with('asset');

        if ($request->status)
            $query->where('status', $request->status);

        $maintenances = $query->orderBy('scheduled_date')->paginate(20)->withQueryString();
        $assets = Asset::where('tenant_id', $tid)->where('status', '!=', 'disposed')->orderBy('name')->get();

        return view('assets.maintenance', compact('maintenances', 'assets'));
    }

    public function storeMaintenance(Request $request)
    {
        $data = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'type' => 'required|in:scheduled,corrective,preventive',
            'description' => 'required|string',
            'scheduled_date' => 'required|date',
            'cost' => 'nullable|numeric|min:0',
            'vendor' => 'nullable|string|max:255',
        ]);

        $tid = $this->tenantId();
        $asset = Asset::where('tenant_id', $tid)->findOrFail($data['asset_id']);

        AssetMaintenance::create(['tenant_id' => $tid, 'status' => 'pending'] + $data);

        return back()->with('success', "Jadwal maintenance {$asset->name} berhasil ditambahkan.");
    }

    public function updateMaintenanceStatus(Request $request, AssetMaintenance $maintenance)
    {
        abort_unless($maintenance->tenant_id === $this->tenantId(), 403);

        $data = $request->validate(['status' => 'required|in:pending,in_progress,completed']);
        $maintenance->update(['status' => $data['status']]);

        if ($data['status'] === 'completed') {
            $maintenance->asset->update(['status' => 'active']);
        }

        return back()->with('success', 'Status maintenance diperbarui.');
    }

    /**
     * Show barcode preview for single asset
     */
    public function showBarcode(Asset $asset)
    {
        abort_if($asset->tenant_id !== $this->tenantId(), 403);

        $barcodeService = app(\App\Services\BarcodeService::class);
        $barcodeImage = $barcodeService->generate($asset->asset_code, 'code128', 'png');

        return view('assets.barcode-show', compact('asset', 'barcodeImage'));
    }

    /**
     * Print barcode labels for multiple assets (batch)
     */
    public function printBarcodes(Request $request)
    {
        $assetIds = $request->validate(['asset_ids' => 'required|array'])['asset_ids'];
        $assets = Asset::whereIn('id', $assetIds)
            ->where('tenant_id', $this->tenantId())
            ->orderBy('asset_code')
            ->get();

        $barcodeService = app(\App\Services\BarcodeService::class);
        $barcodes = $assets->map(fn($a) => [
            'asset' => $a,
            'image' => base64_encode($barcodeService->generate($a->asset_code, 'code128', 'png')),
            'value' => $a->asset_code,
        ])->toArray();

        // Pad to multiple of 4 for A4 grid layout
        while (count($barcodes) % 4 !== 0) {
            $barcodes[] = null;
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('assets.barcode-labels', compact('barcodes'));
        $pdf->setPaper('A4');
        $pdf->setOption('margin_top', 10);
        $pdf->setOption('margin_right', 10);
        $pdf->setOption('margin_bottom', 10);
        $pdf->setOption('margin_left', 10);

        return $pdf->stream('asset-barcodes.pdf');
    }

    /**
     * Scan asset for maintenance interface
     */
    public function scanForMaintenance()
    {
        return view('assets.scan-maintenance');
    }

    /**
     * AJAX lookup asset by barcode
     */
    public function lookupByBarcode(Request $request)
    {
        $barcode = $request->barcode;
        $asset = Asset::where('asset_code', $barcode)
            ->orWhere('serial_number', $barcode)
            ->where('tenant_id', $this->tenantId())
            ->first();

        return response()->json([
            'success' => (bool) $asset,
            'data' => $asset ? [
                'id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'name' => $asset->name,
                'category' => $asset->category,
                'location' => $asset->location,
                'status' => $asset->status,
                'brand' => $asset->brand ?? null,
                'model' => $asset->model ?? null,
                'serial_number' => $asset->serial_number ?? null,
            ] : null,
        ]);
    }
}
