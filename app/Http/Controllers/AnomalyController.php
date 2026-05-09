<?php

namespace App\Http\Controllers;

use App\Models\AnomalyAlert;
use App\Services\AnomalyDetectionService;
use Illuminate\Http\Request;

class AnomalyController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    public function __construct(protected AnomalyDetectionService $service) {}

    public function index(Request $request)
    {
        $query = AnomalyAlert::where('tenant_id', $this->tid())
            ->latest();

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->severity) {
            $query->where('severity', $request->severity);
        }

        $anomalies = $query->paginate(20);
        $counts = AnomalyAlert::where('tenant_id', $this->tid())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('anomalies.index', compact('anomalies', 'counts'));
    }

    public function detect()
    {
        $count = $this->service->detectAndSave($this->tid());

        return back()->with('success', "Deteksi selesai. {$count} anomali baru ditemukan.");
    }

    public function acknowledge(AnomalyAlert $anomaly)
    {
        abort_if($anomaly->tenant_id !== $this->tid(), 403);
        $anomaly->update([
            'status' => 'acknowledged',
            'acknowledged_by' => auth()->id(),
            'acknowledged_at' => now(),
        ]);

        return back()->with('success', 'Anomali ditandai sudah ditinjau.');
    }

    public function resolve(AnomalyAlert $anomaly)
    {
        abort_if($anomaly->tenant_id !== $this->tid(), 403);
        $anomaly->update(['status' => 'resolved']);

        return back()->with('success', 'Anomali ditandai selesai.');
    }
}
