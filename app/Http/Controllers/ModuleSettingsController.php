<?php

namespace App\Http\Controllers;

use App\Services\ModuleRecommendationService;
use Illuminate\Http\Request;

class ModuleSettingsController extends Controller
{
    public function index()
    {
        $tenant  = auth()->user()->tenant;
        $enabled = $tenant->enabledModules();

        return view('settings.modules', [
            'tenant'   => $tenant,
            'enabled'  => $enabled,
            'meta'     => ModuleRecommendationService::MODULE_META,
            'all'      => ModuleRecommendationService::ALL_MODULES,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'modules'   => ['nullable', 'array'],
            'modules.*' => ['string', 'in:' . implode(',', ModuleRecommendationService::ALL_MODULES)],
        ]);

        $tenant = auth()->user()->tenant;
        $tenant->update(['enabled_modules' => $request->input('modules', [])]);

        return back()->with('success', 'Pengaturan modul berhasil disimpan.');
    }

    /** AJAX: get AI recommendation for an industry */
    public function recommend(Request $request)
    {
        $industry = $request->input('industry', 'other');
        $svc      = new ModuleRecommendationService();
        return response()->json($svc->recommend($industry));
    }
}
