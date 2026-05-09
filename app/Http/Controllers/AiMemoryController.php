<?php

namespace App\Http\Controllers;

use App\Models\AiLearnedPattern;
use App\Models\AiMemory;
use App\Services\AiMemoryService;

class AiMemoryController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    public function __construct(protected AiMemoryService $service) {}

    public function index()
    {
        $memories = AiMemory::where('tenant_id', $this->tid())
            ->where('user_id', auth()->id())
            ->orderByDesc('frequency')
            ->get();

        // Load learned patterns
        $patterns = AiLearnedPattern::where('tenant_id', $this->tid())
            ->where('user_id', auth()->id())
            ->orderByDesc('confidence')
            ->get();

        // Group memories by category
        $categories = [
            'pelanggan' => ['frequent_customers', 'preferred_delivery_address'],
            'supplier' => ['frequent_suppliers', 'preferred_payment_terms'],
            'produk' => ['frequent_products', 'typical_order_quantity', 'preferred_discount'],
            'pembayaran' => ['preferred_payment_method', 'preferred_currency', 'tax_preference'],
            'umum' => ['default_warehouse', 'default_cost_center', 'skipped_steps', 'preferred_report_period'],
        ];

        $groupedMemories = [];
        foreach ($categories as $category => $keys) {
            $groupedMemories[$category] = $memories->filter(fn ($m) => in_array($m->key, $keys));
        }

        // Uncategorized memories
        $allKeys = array_merge(...array_values($categories));
        $groupedMemories['lainnya'] = $memories->filter(fn ($m) => ! in_array($m->key, $allKeys));

        $suggestions = $this->service->getSuggestions($this->tid(), auth()->id());

        return view('settings.ai-memory', compact('groupedMemories', 'patterns', 'suggestions', 'memories'));
    }

    public function reset()
    {
        $deleted = $this->service->resetMemory($this->tid(), auth()->id());

        return back()->with('success', "Memori AI direset. {$deleted} preferensi dihapus.");
    }

    public function destroy(AiMemory $aiMemory)
    {
        abort_if($aiMemory->tenant_id !== $this->tid() || $aiMemory->user_id !== auth()->id(), 403);
        $aiMemory->delete();

        return back()->with('success', 'Preferensi dihapus.');
    }

    public function lock(AiMemory $memory)
    {
        // Verify tenant ownership
        if ($memory->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }
        $memory->update(['confidence_score' => 1.0]);

        return back()->with('success', 'Preferensi dikonfirmasi.');
    }

    public function pruneStale()
    {
        $count = AiMemoryService::pruneStaleMemories(auth()->user()->tenant_id, auth()->id());

        return back()->with('success', "Berhasil menghapus {$count} memori usang.");
    }
}
