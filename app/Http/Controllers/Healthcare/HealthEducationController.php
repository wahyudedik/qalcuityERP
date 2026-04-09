<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\HealthEducation;
use App\Services\DashboardCacheService;
use Illuminate\Http\Request;

class HealthEducationController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = HealthEducation::where('tenant_id', $tenantId);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $materials = $query->orderBy('published_at', 'desc')->paginate(20)->withQueryString();

        // Statistics with caching
        $cacheKey = "stats:health_education:{$tenantId}";
        $statistics = DashboardCacheService::getStats($cacheKey, function () use ($tenantId) {
            $stats = HealthEducation::where('tenant_id', $tenantId)
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN status = \'published\' THEN 1 ELSE 0 END) as published,
                    SUM(CASE WHEN status = \'draft\' THEN 1 ELSE 0 END) as draft,
                    SUM(CASE WHEN status = \'archived\' THEN 1 ELSE 0 END) as archived
                ')
                ->first();

            return [
                'total' => $stats->total ?? 0,
                'published' => $stats->published ?? 0,
                'draft' => $stats->draft ?? 0,
                'archived' => $stats->archived ?? 0,
            ];
        }, 300);

        return view('healthcare.health-education.index', compact('materials', 'statistics'));
    }

    public function create()
    {
        return view('healthcare.health-education.create');
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'content' => 'required|string',
            'summary' => 'nullable|string',
            'target_audience' => 'nullable|string',
            'language' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
            'attachment_path' => 'nullable|string',
        ]);

        $validated['author_id'] = auth()->id();
        $validated['tenant_id'] = $tenantId;

        if ($request->status === 'published' && !$validated['published_at']) {
            $validated['published_at'] = now();
        }

        $material = HealthEducation::create($validated);

        // Clear cache
        DashboardCacheService::clearStats("stats:health_education:{$tenantId}");

        return redirect()->route('healthcare.health-education.show', $material)
            ->with('success', 'Health education material created');
    }

    public function show(HealthEducation $healthEducation)
    {
        $healthEducation->increment('view_count');
        return view('healthcare.health-education.show', compact('healthEducation'));
    }

    public function edit(HealthEducation $healthEducation)
    {
        return view('healthcare.health-education.edit', compact('healthEducation'));
    }

    public function update(Request $request, HealthEducation $healthEducation)
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'content' => 'required|string',
            'summary' => 'nullable|string',
            'target_audience' => 'nullable|string',
            'language' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
            'attachment_path' => 'nullable|string',
        ]);

        if ($request->status === 'published' && $healthEducation->status !== 'published') {
            $validated['published_at'] = now();
        }

        $healthEducation->update($validated);

        // Clear cache
        DashboardCacheService::clearStats("stats:health_education:{$tenantId}");

        return redirect()->route('healthcare.health-education.show', $healthEducation)
            ->with('success', 'Health education material updated');
    }

    public function destroy(HealthEducation $healthEducation)
    {
        $tenantId = auth()->user()->tenant_id;

        $healthEducation->delete();

        // Clear cache
        DashboardCacheService::clearStats("stats:health_education:{$tenantId}");

        return response()->json(['success' => true, 'message' => 'Material deleted']);
    }

    public function publish(HealthEducation $healthEducation)
    {
        $tenantId = auth()->user()->tenant_id;

        $healthEducation->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Clear cache
        DashboardCacheService::clearStats("stats:health_education:{$tenantId}");

        return response()->json(['success' => true, 'message' => 'Material published']);
    }
}
