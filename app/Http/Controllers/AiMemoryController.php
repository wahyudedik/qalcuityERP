<?php

namespace App\Http\Controllers;

use App\Models\AiMemory;
use App\Services\AiMemoryService;
use Illuminate\Http\Request;

class AiMemoryController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function __construct(protected AiMemoryService $service) {}

    public function index()
    {
        $memories = AiMemory::where('tenant_id', $this->tid())
            ->where('user_id', auth()->id())
            ->orderByDesc('frequency')
            ->get();

        $suggestions = $this->service->getSuggestions($this->tid(), auth()->id());

        return view('settings.ai-memory', compact('memories', 'suggestions'));
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
}
