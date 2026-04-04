<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PopupAd;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PopupAdController extends Controller
{
    public function index(): View
    {
        $ads = PopupAd::latest()->paginate(20);
        return view('super-admin.popup-ads.index', compact('ads'));
    }

    public function create(): View
    {
        $tenants = Tenant::orderBy('name')->get(['id', 'name']);
        return view('super-admin.popup-ads.form', [
            'ad'      => new PopupAd(),
            'tenants' => $tenants,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('popup-ads', 'public');
        }

        PopupAd::create($data);

        return redirect()->route('super-admin.popup-ads.index')
            ->with('success', 'Popup iklan berhasil dibuat.');
    }

    public function edit(PopupAd $ad): View
    {
        $tenants = Tenant::orderBy('name')->get(['id', 'name']);
        return view('super-admin.popup-ads.form', compact('ad', 'tenants'));
    }

    public function update(Request $request, PopupAd $ad): RedirectResponse
    {
        $data = $this->validated($request, $ad);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($ad->image_path) {
                Storage::disk('public')->delete($ad->image_path);
            }
            $data['image_path'] = $request->file('image')->store('popup-ads', 'public');
        }

        $ad->update($data);

        return redirect()->route('super-admin.popup-ads.index')
            ->with('success', 'Popup iklan berhasil diperbarui.');
    }

    public function toggle(PopupAd $ad): RedirectResponse
    {
        $ad->update(['is_active' => !$ad->is_active]);
        $status = $ad->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Popup iklan berhasil {$status}.");
    }

    public function destroy(PopupAd $ad): RedirectResponse
    {
        if ($ad->image_path) {
            Storage::disk('public')->delete($ad->image_path);
        }
        $ad->delete();
        return back()->with('success', 'Popup iklan berhasil dihapus.');
    }

    private function validated(Request $request, ?PopupAd $ad = null): array
    {
        $data = $request->validate([
            'title'        => 'required|string|max:200',
            'body'         => 'nullable|string|max:1000',
            'image'        => 'nullable|image|max:2048',
            'button_label' => 'nullable|string|max:100',
            'button_url'   => 'nullable|url|max:500',
            'target'       => 'required|in:all,specific',
            'tenant_ids'   => 'required_if:target,specific|array',
            'tenant_ids.*' => 'integer|exists:tenants,id',
            'frequency'    => 'required|in:once,daily,always',
            'starts_at'    => 'nullable|date',
            'ends_at'      => 'nullable|date|after_or_equal:starts_at',
            'is_active'    => 'boolean',
        ]);

        $data['is_active']   = $request->boolean('is_active', true);
        $data['tenant_ids']  = $request->target === 'specific' ? ($request->tenant_ids ?? []) : null;

        return $data;
    }
}
