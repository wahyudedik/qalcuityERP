<?php

namespace App\Http\Controllers\Cosmetic;

use App\Http\Controllers\Controller;
use App\Models\PackagingMaterial;
use App\Models\LabelVersion;
use App\Models\LabelComplianceCheck;
use App\Models\CosmeticFormula;
use App\Models\ProductRegistration;
use Illuminate\Http\Request;

class PackagingController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $stats = [
            'total_materials' => PackagingMaterial::where('tenant_id', $tenantId)->count(),
            'primary_packaging' => PackagingMaterial::where('tenant_id', $tenantId)->primary()->count(),
            'secondary_packaging' => PackagingMaterial::where('tenant_id', $tenantId)->secondary()->count(),
            'recyclable' => PackagingMaterial::where('tenant_id', $tenantId)->recyclable()->count(),
        ];
        $materials = PackagingMaterial::where('tenant_id', $tenantId)
            ->when($request->search, fn($q) => $q->where('material_name', 'like', '%' . $request->search . '%'))
            ->when($request->type, fn($q) => $q->where('material_type', $request->type))
            ->when($request->category, fn($q) => $q->where('material_category', $request->category))
            ->with('product')->latest()->paginate(15);
        $formulas = CosmeticFormula::where('tenant_id', $tenantId)->get();
        return view('cosmetic.packaging.index', compact('stats', 'materials', 'formulas'));
    }

    public function storeMaterial(Request $request)
    {
        $validated = $request->validate([
            'formula_id' => 'nullable|exists:cosmetic_formulas,id',
            'material_name' => 'required|string|max:255',
            'material_type' => 'required|in:primary,secondary,tertiary',
            'material_category' => 'required|in:bottle,tube,jar,box,carton,label,cap,pump',
            'sku' => 'nullable|unique:packaging_materials,sku',
            'supplier_name' => 'nullable|string|max:255',
            'unit_cost' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'material_composition' => 'nullable|string|max:255',
            'is_recyclable' => 'boolean',
            'is_active' => 'boolean',
        ]);
        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['sku'] = $validated['sku'] ?? PackagingMaterial::getNextSku();
        $validated['is_recyclable'] = $request->has('is_recyclable');
        $validated['is_active'] = $request->has('is_active');
        PackagingMaterial::create($validated);
        return back()->with('success', 'Packaging material created successfully!');
    }

    public function labelsIndex(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $stats = [
            'total_labels' => LabelVersion::where('tenant_id', $tenantId)->count(),
            'active_labels' => LabelVersion::where('tenant_id', $tenantId)->where('status', 'active')->count(),
            'in_review' => LabelVersion::where('tenant_id', $tenantId)->where('status', 'in_review')->count(),
            'draft' => LabelVersion::where('tenant_id', $tenantId)->where('status', 'draft')->count(),
        ];
        $labels = LabelVersion::where('tenant_id', $tenantId)
            ->when($request->search, fn($q) => $q->where('label_code', 'like', '%' . $request->search . '%'))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->type, fn($q) => $q->where('label_type', $request->type))
            ->with(['product', 'complianceChecks'])->withCount('compliance_checks')->latest()->paginate(15);
        $formulas = CosmeticFormula::where('tenant_id', $tenantId)->get();
        $registrations = ProductRegistration::where('tenant_id', $tenantId)->approved()->get();
        return view('cosmetic.packaging.labels', compact('stats', 'labels', 'formulas', 'registrations'));
    }

    public function storeLabel(Request $request)
    {
        $validated = $request->validate([
            'formula_id' => 'nullable|exists:cosmetic_formulas,id',
            'registration_id' => 'nullable|exists:product_registrations,id',
            'version_number' => 'required|string|max:50',
            'label_type' => 'required|in:primary,secondary,insert,outer',
            'label_content' => 'nullable|string',
            'barcode' => 'nullable|string|max:100',
            'qr_code' => 'nullable|string|max:255',
            'effective_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:effective_date',
        ]);
        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['label_code'] = LabelVersion::getNextLabelCode();
        $validated['status'] = 'draft';
        LabelVersion::create($validated);
        return back()->with('success', 'Label version created successfully!');
    }

    public function showLabel($id)
    {
        $tenantId = auth()->user()->tenant_id;
        $label = LabelVersion::where('tenant_id', $tenantId)->with(['product', 'registration', 'complianceChecks'])->findOrFail($id);
        return view('cosmetic.packaging.label-show', compact('label'));
    }

    public function submitLabel($id)
    {
        $tenantId = auth()->user()->tenant_id;
        $label = LabelVersion::where('tenant_id', $tenantId)->findOrFail($id);
        $label->status = 'in_review';
        $label->save();
        return back()->with('success', 'Label submitted for review!');
    }

    public function approveLabel(Request $request, $id)
    {
        $validated = $request->validate(['notes' => 'nullable|string']);
        $tenantId = auth()->user()->tenant_id;
        $label = LabelVersion::where('tenant_id', $tenantId)->findOrFail($id);
        $label->approve(auth()->id(), $validated['notes'] ?? '');
        return back()->with('success', 'Label approved successfully!');
    }

    public function activateLabel($id)
    {
        $tenantId = auth()->user()->tenant_id;
        $label = LabelVersion::where('tenant_id', $tenantId)->findOrFail($id);
        if ($label->status !== 'approved') {
            return back()->with('error', 'Label must be approved before activation!');
        }
        $label->activate();
        return back()->with('success', 'Label activated successfully!');
    }

    public function archiveLabel($id)
    {
        $tenantId = auth()->user()->tenant_id;
        $label = LabelVersion::where('tenant_id', $tenantId)->findOrFail($id);
        $label->archive();
        return back()->with('success', 'Label archived successfully!');
    }

    public function addComplianceCheck(Request $request, $labelId)
    {
        $validated = $request->validate([
            'check_name' => 'required|string|max:255',
            'check_category' => 'required|in:mandatory,optional,regulatory',
            'requirement' => 'required|string',
        ]);
        $tenantId = auth()->user()->tenant_id;
        $label = LabelVersion::where('tenant_id', $tenantId)->findOrFail($labelId);
        LabelComplianceCheck::create([
            'tenant_id' => $tenantId,
            'label_id' => $label->id,
            'check_name' => $validated['check_name'],
            'check_category' => $validated['check_category'],
            'requirement' => $validated['requirement'],
        ]);
        return back()->with('success', 'Compliance check added!');
    }

    public function updateComplianceCheck(Request $request, $checkId)
    {
        $validated = $request->validate([
            'is_compliant' => 'required|boolean',
            'findings' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);
        $tenantId = auth()->user()->tenant_id;
        $check = LabelComplianceCheck::where('tenant_id', $tenantId)->findOrFail($checkId);
        if ($validated['is_compliant']) {
            $check->markCompliant(auth()->id(), $validated['findings'] ?? '', $validated['remarks'] ?? '');
        } else {
            $check->markNonCompliant(auth()->id(), $validated['findings'] ?? '', $validated['remarks'] ?? '');
        }
        return back()->with('success', 'Compliance check updated!');
    }

    public function destroyMaterial($id)
    {
        $tenantId = auth()->user()->tenant_id;
        $material = PackagingMaterial::where('tenant_id', $tenantId)->findOrFail($id);
        $material->delete();
        return back()->with('success', 'Packaging material deleted!');
    }
}
