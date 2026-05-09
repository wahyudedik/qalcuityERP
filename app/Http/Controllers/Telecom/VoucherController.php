<?php

namespace App\Http\Controllers\Telecom;

use App\Http\Controllers\Controller;
use App\Models\InternetPackage;
use App\Models\VoucherCode;
use App\Services\Telecom\VoucherGenerationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    protected VoucherGenerationService $voucherService;

    public function __construct()
    {
        $this->voucherService = new VoucherGenerationService;
    }

    /**
     * Display voucher management page.
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = VoucherCode::where('tenant_id', $tenantId)
            ->with(['package', 'customer', 'generatedBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by batch
        if ($request->filled('batch_number')) {
            $query->where('batch_number', $request->batch_number);
        }

        // Filter by package
        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        // Search by code
        if ($request->filled('search')) {
            $query->where('code', 'like', '%'.$request->search.'%');
        }

        $vouchers = $query->orderBy('created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        // Get batches for filter
        $batches = VoucherCode::where('tenant_id', $tenantId)
            ->distinct()
            ->pluck('batch_number')
            ->filter()
            ->sort()
            ->values();

        $packages = InternetPackage::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        // Stats
        $stats = [
            'total' => VoucherCode::where('tenant_id', $tenantId)->count(),
            'unused' => VoucherCode::where('tenant_id', $tenantId)->where('status', 'unused')->count(),
            'used' => VoucherCode::where('tenant_id', $tenantId)->where('status', 'used')->count(),
            'expired' => VoucherCode::where('tenant_id', $tenantId)->where('status', 'expired')->count(),
        ];

        return view('telecom.vouchers.index', compact('vouchers', 'batches', 'packages', 'stats'));
    }

    /**
     * Show form to generate vouchers.
     */
    public function create()
    {
        $packages = InternetPackage::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_active', true)
            ->get();

        return view('telecom.vouchers.create', compact('packages'));
    }

    /**
     * Generate vouchers.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:internet_packages,id',
            'quantity' => 'required|integer|min:1|max:1000',
            'code_length' => 'nullable|integer|min:6|max:16',
            'code_pattern' => 'nullable|string|in:numeric,alphabetic,alphanumeric',
            'validity_hours' => 'nullable|integer|min:1',
            'max_usage' => 'nullable|integer|min:1',
            'sale_price' => 'nullable|numeric|min:0',
            'batch_number' => 'nullable|string|max:255',
        ]);

        $package = InternetPackage::where('id', $validated['package_id'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        try {
            $options = [
                'code_length' => $validated['code_length'] ?? 8,
                'code_pattern' => $validated['code_pattern'] ?? 'alphanumeric',
                'validity_hours' => $validated['validity_hours'] ?? 24,
                'max_usage' => $validated['max_usage'] ?? 1,
                'sale_price' => $validated['sale_price'] ?? null,
                'batch_number' => $validated['batch_number'] ?? null,
                'generated_by' => auth()->id(),
                'valid_from' => now(),
                'valid_until' => now()->addHours($validated['validity_hours'] ?? 24),
            ];

            if ($validated['quantity'] == 1) {
                $vouchers = [$this->voucherService->generateSingle($package, $options)];
            } else {
                $vouchers = $this->voucherService->generateBatch($package, $validated['quantity'], $options);
            }

            return redirect()->route('telecom.vouchers.index')
                ->with('success', count($vouchers).' voucher berhasil dibuat.');

        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Gagal membuat voucher: '.$e->getMessage()]);
        }
    }

    /**
     * Print vouchers.
     */
    public function print(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = VoucherCode::where('tenant_id', $tenantId)
            ->with('package');

        // Print specific batch
        if ($request->filled('batch_number')) {
            $query->where('batch_number', $request->batch_number);
        }

        // Print specific IDs
        if ($request->filled('ids')) {
            $ids = explode(',', $request->ids);
            $query->whereIn('id', $ids);
        }

        $vouchers = $query->where('status', 'unused')
            ->orderBy('created_at')
            ->get();

        if ($vouchers->isEmpty()) {
            return back()->withErrors(['error' => 'Tidak ada voucher untuk dicetak.']);
        }

        $pdf = Pdf::loadView('telecom.vouchers.print', compact('vouchers'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('vouchers-'.now()->format('YmdHis').'.pdf');
    }

    /**
     * Show voucher statistics.
     */
    public function stats()
    {
        $tenantId = auth()->user()->tenant_id;

        $stats = $this->voucherService->getVoucherStats($tenantId);

        // Get top packages by voucher usage
        $topPackages = VoucherCode::where('tenant_id', $tenantId)
            ->where('status', 'used')
            ->join('internet_packages', 'voucher_codes.package_id', '=', 'internet_packages.id')
            ->selectRaw('
                internet_packages.name as package_name,
                COUNT(*) as usage_count,
                SUM(voucher_codes.sale_price) as total_revenue
            ')
            ->groupBy('internet_packages.id', 'internet_packages.name')
            ->orderBy('usage_count', 'desc')
            ->limit(5)
            ->get();

        // Recent activity
        $recentActivity = VoucherCode::where('tenant_id', $tenantId)
            ->with(['package', 'customer'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return view('telecom.vouchers.stats', compact('stats', 'topPackages', 'recentActivity'));
    }

    /**
     * Revoke a voucher.
     */
    public function revoke(VoucherCode $voucher)
    {
        if ($voucher->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        if ($voucher->status === 'used') {
            return back()->withErrors(['error' => 'Voucher sudah digunakan, tidak dapat dibatalkan.']);
        }

        $voucher->update(['status' => 'revoked']);

        return back()->with('success', 'Voucher berhasil dibatalkan.');
    }

    /**
     * Extend voucher validity.
     */
    public function extendValidity(Request $request, VoucherCode $voucher)
    {
        if ($voucher->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'hours' => 'required|integer|min:1|max:8760',
        ]);

        $newExpiry = now()->addHours($validated['hours']);
        $voucher->update([
            'valid_until' => $newExpiry,
            'status' => 'unused', // Reactivate if expired
        ]);

        return back()->with('success', 'Masa berlaku voucher diperpanjang hingga '.$newExpiry->format('d M Y H:i'));
    }
}
