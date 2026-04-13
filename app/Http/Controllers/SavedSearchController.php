<?php

namespace App\Http\Controllers;

use App\Models\SavedSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SavedSearchController extends Controller
{
    /**
     * Display listing of saved searches
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $sortBy = $request->get('sort', 'recent');

        $cacheKey = "saved_searches:user:{$user->id}:{$sortBy}";

        $savedSearches = Cache::remember($cacheKey, 300, function () use ($user, $sortBy) {
            $query = SavedSearch::forUser($user->id);

            switch ($sortBy) {
                case 'popular':
                    $query->mostUsed(20);
                    break;
                case 'recent':
                    $query->recentlyUsed(20);
                    break;
                default:
                    $query->orderByDesc('updated_at');
            }

            return $query->get()->map(fn($search) => [
                'id' => $search->id,
                'name' => $search->name,
                'query' => $search->query,
                'type' => $search->type,
                'filters' => $search->filters,
                'module' => $search->module,
                'use_count' => $search->use_count,
                'last_used_at' => $search->last_used_at?->diffForHumans(),
                'is_public' => $search->is_public,
                'created_at' => $search->created_at->diffForHumans(),
            ]);
        });

        return response()->json([
            'success' => true,
            'data' => $savedSearches,
            'total' => $savedSearches->count(),
        ]);
    }

    /**
     * Store a newly saved search
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'query' => 'required|string|max:200',
            'type' => 'nullable|string|max:50',
            'filters' => 'nullable|array',
            'module' => 'nullable|string|max:50',
            'is_public' => 'nullable|boolean',
        ]);

        $savedSearch = SavedSearch::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'query' => $validated['query'],
            'type' => $validated['type'] ?? 'all',
            'filters' => $validated['filters'] ?? null,
            'module' => $validated['module'] ?? null,
            'is_public' => $validated['is_public'] ?? false,
        ]);

        // Clear cache
        SavedSearch::clearUserCache($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Pencarian berhasil disimpan',
            'data' => [
                'id' => $savedSearch->id,
                'name' => $savedSearch->name,
                'query' => $savedSearch->query,
                'type' => $savedSearch->type,
                'filters' => $savedSearch->filters,
                'module' => $savedSearch->module,
                'is_public' => $savedSearch->is_public,
            ],
        ], 201);
    }

    /**
     * Display the specified saved search
     */
    public function show(Request $request, SavedSearch $savedSearch)
    {
        // Check ownership or if public
        if ($savedSearch->user_id !== $request->user()->id && !$savedSearch->is_public) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Mark as used
        $savedSearch->markAsUsed();
        SavedSearch::clearUserCache($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $savedSearch->id,
                'name' => $savedSearch->name,
                'query' => $savedSearch->query,
                'type' => $savedSearch->type,
                'filters' => $savedSearch->filters,
                'module' => $savedSearch->module,
                'use_count' => $savedSearch->use_count,
                'last_used_at' => $savedSearch->last_used_at?->diffForHumans(),
                'is_public' => $savedSearch->is_public,
            ],
        ]);
    }

    /**
     * Execute a saved search
     */
    public function execute(Request $request, SavedSearch $savedSearch)
    {
        // Check ownership or if public
        if ($savedSearch->user_id !== $request->user()->id && !$savedSearch->is_public) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Mark as used
        $savedSearch->markAsUsed();
        SavedSearch::clearUserCache($request->user()->id);

        // Execute search using QuickSearchController
        $searchRequest = new Request([
            'q' => $savedSearch->query,
            'type' => $savedSearch->type,
            'filters' => $savedSearch->filters,
        ]);

        $controller = app(QuickSearchController::class);
        $response = $controller->search($searchRequest);

        return $response;
    }

    /**
     * Update the specified saved search
     */
    public function update(Request $request, SavedSearch $savedSearch)
    {
        if ($savedSearch->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'query' => 'sometimes|required|string|max:200',
            'type' => 'sometimes|required|string|max:50',
            'filters' => 'nullable|array',
            'module' => 'nullable|string|max:50',
            'is_public' => 'nullable|boolean',
        ]);

        $savedSearch->update($validated);
        SavedSearch::clearUserCache($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Pencarian berhasil diperbarui',
            'data' => $savedSearch,
        ]);
    }

    /**
     * Remove the specified saved search
     */
    public function destroy(Request $request, SavedSearch $savedSearch)
    {
        if ($savedSearch->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $savedSearch->delete();
        SavedSearch::clearUserCache($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Pencarian berhasil dihapus',
        ]);
    }

    /**
     * Get search suggestions based on partial query
     */
    public function suggestions(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $query = $request->get('q', '');
        $user = $request->user();

        $suggestions = Cache::remember("search:suggestions:{$user->id}:{$query}", 120, function () use ($query, $user, $request) {
            $results = [];

            // Get matching saved searches
            $savedSearches = SavedSearch::forUser($user->id)
                ->where('query', 'like', "%{$query}%")
                ->orderByDesc('use_count')
                ->limit(5)
                ->get()
                ->map(fn($search) => [
                    'type' => 'saved_search',
                    'id' => "saved:{$search->id}",
                    'title' => $search->name,
                    'subtitle' => "Saved: {$search->query}",
                    'icon' => 'fas fa-bookmark',
                    'url' => "#",
                    'badge' => 'Saved',
                    'action' => 'execute-saved',
                    'saved_search_id' => $search->id,
                ]);

            $results = $savedSearches->toArray();

            // Get recent popular searches from history
            $recentSearches = json_decode($request->cookie('recent_searches') ?? '[]', true);
            if (is_array($recentSearches)) {
                $matching = array_filter($recentSearches, fn($s) => stripos($s, $query) !== false);
                foreach (array_slice($matching, 0, 3) as $search) {
                    $results[] = [
                        'type' => 'recent',
                        'id' => "recent:{$search}",
                        'title' => $search,
                        'subtitle' => 'Recent search',
                        'icon' => 'fas fa-history',
                        'url' => "#",
                        'badge' => 'Recent',
                        'action' => 'set-query',
                        'query' => $search,
                    ];
                }
            }

            return array_slice($results, 0, 8);
        });

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions,
        ]);
    }
}
