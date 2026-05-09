<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\FarmPlot;
use App\Models\LivestockFeedLog;
use App\Models\LivestockHealthRecord;
use App\Models\LivestockHerd;
use App\Models\LivestockMovement;
use App\Models\LivestockVaccination;
use App\Services\LivestockIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LivestockController extends Controller
{
    protected LivestockIntegrationService $integrationService;

    public function __construct(LivestockIntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $query = LivestockHerd::where('tenant_id', $this->tid())->with('plot');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->type) {
            $query->where('animal_type', $request->type);
        }
        if ($request->search) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('code', 'like', "%$s%")
                ->orWhere('name', 'like', "%$s%")
                ->orWhere('animal_type', 'like', "%$s%"));
        }

        $herds = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $plots = FarmPlot::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('code')->get();

        $stats = [
            'active_herds' => LivestockHerd::where('tenant_id', $this->tid())->where('status', 'active')->count(),
            'total_animals' => LivestockHerd::where('tenant_id', $this->tid())->where('status', 'active')->sum('current_count'),
            'total_mortality' => LivestockMovement::where('tenant_id', $this->tid())->whereIn('type', ['death', 'cull'])->sum('quantity'),
            'total_sold' => abs((int) LivestockMovement::where('tenant_id', $this->tid())->where('type', 'sold')->sum('quantity')),
        ];

        return view('farm.livestock', compact('herds', 'plots', 'stats'));
    }

    public function show(LivestockHerd $livestockHerd)
    {
        abort_if($livestockHerd->tenant_id !== $this->tid(), 403);
        $livestockHerd->load(['plot', 'movements.user', 'healthRecords.user', 'vaccinations']);

        $overdueVaccinations = $livestockHerd->vaccinations->filter(fn ($v) => $v->isOverdue());
        $upcomingVaccinations = $livestockHerd->vaccinations
            ->where('status', 'scheduled')
            ->filter(fn ($v) => $v->scheduled_date->isFuture() && $v->scheduled_date->diffInDays(now()) <= 7);

        return view('farm.livestock-show', compact('livestockHerd', 'overdueVaccinations', 'upcomingVaccinations'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'farm_plot_id' => 'nullable|exists:farm_plots,id',
            'name' => 'required|string|max:255',
            'animal_type' => 'required|string|max:50',
            'breed' => 'nullable|string|max:100',
            'initial_count' => 'required|integer|min:1',
            'entry_date' => 'required|date',
            'entry_age_days' => 'nullable|integer|min:0',
            'entry_weight_kg' => 'nullable|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'target_harvest_date' => 'nullable|date',
            'target_weight_kg' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $herd = LivestockHerd::create(array_merge($data, [
            'tenant_id' => $this->tid(),
            'code' => LivestockHerd::generateCode($this->tid(), $data['animal_type']),
            'current_count' => $data['initial_count'],
            'status' => 'active',
        ]));

        // Record initial purchase movement
        $movement = LivestockMovement::create([
            'livestock_herd_id' => $herd->id,
            'tenant_id' => $this->tid(),
            'user_id' => auth()->id(),
            'date' => $data['entry_date'],
            'type' => 'purchase',
            'quantity' => $data['initial_count'],
            'count_after' => $data['initial_count'],
            'weight_kg' => (float) ($data['entry_weight_kg'] ?? 0) * $data['initial_count'],
            'price_total' => (float) ($data['purchase_price'] ?? 0),
        ]);

        // Post journal entry for livestock purchase (integration with Accounting)
        if (($data['purchase_price'] ?? 0) > 0) {
            $paymentMethod = $request->input('payment_method', 'credit');
            $result = $this->integrationService->postLivestockPurchase(
                $this->tid(),
                auth()->id(),
                $herd,
                $paymentMethod
            );
            if ($result->isFailed()) {
                Log::warning('Livestock purchase journal failed: '.$result->reason);
            }
        }

        ActivityLog::record('livestock_created', "Ternak masuk: {$herd->code} — {$data['initial_count']} ekor {$data['animal_type']}");

        return redirect()->route('farm.livestock.show', $herd)->with('success', "Ternak {$herd->code} berhasil dicatat.");
    }

    public function recordMovement(Request $request, LivestockHerd $livestockHerd)
    {
        abort_if($livestockHerd->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'type' => 'required|in:purchase,birth,transfer_in,transfer_out,death,cull,sold,harvested,adjustment',
            'quantity' => 'required|integer|min:1',
            'date' => 'required|date',
            'weight_kg' => 'nullable|numeric|min:0',
            'price_total' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string|max:255',
            'destination' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $qty = (int) $data['quantity'];
        $isOutbound = in_array($data['type'], LivestockMovement::OUTBOUND_TYPES);

        // For outbound, make quantity negative
        $signedQty = $isOutbound ? -$qty : $qty;
        $newCount = $livestockHerd->current_count + $signedQty;

        if ($newCount < 0) {
            return back()->with('error', "Populasi tidak bisa negatif. Saat ini: {$livestockHerd->current_count}, dikurangi: {$qty}.");
        }

        $movement = LivestockMovement::create([
            'livestock_herd_id' => $livestockHerd->id,
            'tenant_id' => $this->tid(),
            'user_id' => auth()->id(),
            'date' => $data['date'],
            'type' => $data['type'],
            'quantity' => $signedQty,
            'count_after' => $newCount,
            'weight_kg' => (float) ($data['weight_kg'] ?? 0),
            'price_total' => (float) ($data['price_total'] ?? 0),
            'reason' => $data['reason'] ?? null,
            'destination' => $data['destination'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $livestockHerd->update(['current_count' => $newCount]);

        // Post journal entry for livestock sale (integration with Accounting)
        if (in_array($data['type'], ['sold', 'harvested']) && ($data['price_total'] ?? 0) > 0) {
            $paymentMethod = $request->input('payment_method', 'credit');
            $result = $this->integrationService->postLivestockSale(
                $this->tid(),
                auth()->id(),
                $movement,
                $paymentMethod
            );
            if ($result->isFailed()) {
                Log::warning('Livestock sale journal failed: '.$result->reason);
            }
        }

        // Auto-update status if all sold/harvested
        if ($newCount <= 0 && in_array($data['type'], ['sold', 'harvested'])) {
            $livestockHerd->update(['status' => $data['type'] === 'sold' ? 'sold' : 'harvested']);
        }

        $label = LivestockMovement::TYPE_LABELS[$data['type']] ?? $data['type'];
        ActivityLog::record('livestock_movement', "{$label}: {$qty} ekor di {$livestockHerd->code} → populasi: {$newCount}");

        return back()->with('success', "{$label}: {$qty} ekor. Populasi sekarang: {$newCount}.");
    }

    // ── Feed Logs ──────────────────────────────────────────────────

    public function storeFeedLog(Request $request, LivestockHerd $livestockHerd)
    {
        abort_if($livestockHerd->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'date' => 'required|date',
            'feed_type' => 'required|string|max:100',
            'quantity_kg' => 'required|numeric|min:0.001',
            'cost' => 'nullable|numeric|min:0',
            'avg_body_weight_kg' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $feedLog = LivestockFeedLog::create(array_merge($data, [
            'livestock_herd_id' => $livestockHerd->id,
            'tenant_id' => $this->tid(),
            'user_id' => auth()->id(),
            'population_at_feeding' => $livestockHerd->current_count,
        ]));

        // Post journal entry for feed consumption (integration with Accounting & Inventory)
        if (($data['cost'] ?? 0) > 0) {
            $productId = $request->input('product_id'); // Optional: link to inventory product
            $warehouseId = $request->input('warehouse_id'); // Optional: link to warehouse
            $result = $this->integrationService->postFeedConsumption(
                $this->tid(),
                auth()->id(),
                $feedLog,
                $productId,
                $warehouseId
            );
            if ($result->isFailed()) {
                Log::warning('Livestock feed journal failed: '.$result->reason);
            }
        }

        $fcr = $livestockHerd->fcr();
        $fcrMsg = $fcr ? " | FCR: {$fcr}" : '';

        return back()->with('success', "Pakan dicatat: {$data['quantity_kg']} kg {$data['feed_type']}{$fcrMsg}.");
    }

    // ── Health Records ─────────────────────────────────────────────

    public function storeHealthRecord(Request $request, LivestockHerd $livestockHerd)
    {
        abort_if($livestockHerd->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'type' => 'required|in:illness,treatment,observation,quarantine,recovery',
            'date' => 'required|date',
            'condition' => 'required|string|max:255',
            'affected_count' => 'nullable|integer|min:0',
            'death_count' => 'nullable|integer|min:0',
            'symptoms' => 'nullable|string|max:500',
            'medication' => 'nullable|string|max:255',
            'medication_cost' => 'nullable|numeric|min:0',
            'administered_by' => 'nullable|string|max:100',
            'severity' => 'nullable|in:low,medium,high,critical',
            'notes' => 'nullable|string',
        ]);

        $healthRecord = LivestockHealthRecord::create(array_merge($data, [
            'livestock_herd_id' => $livestockHerd->id,
            'tenant_id' => $this->tid(),
            'user_id' => auth()->id(),
            'status' => $data['type'] === 'recovery' ? 'resolved' : 'active',
        ]));

        // Post journal entry for medication cost (integration with Accounting)
        if (($data['medication_cost'] ?? 0) > 0) {
            $result = $this->integrationService->postVeterinaryExpense(
                $this->tid(),
                auth()->id(),
                $healthRecord->id,
                $livestockHerd->code,
                (float) $data['medication_cost'],
                $data['medication'] ?? $data['condition'],
                $data['date']
            );
            if ($result->isFailed()) {
                Log::warning('Livestock health journal failed: '.$result->reason);
            }
        }

        // Auto-record deaths if death_count > 0
        if (($data['death_count'] ?? 0) > 0) {
            $newCount = $livestockHerd->current_count - $data['death_count'];
            LivestockMovement::create([
                'livestock_herd_id' => $livestockHerd->id,
                'tenant_id' => $this->tid(),
                'user_id' => auth()->id(),
                'date' => $data['date'],
                'type' => 'death',
                'quantity' => -$data['death_count'],
                'count_after' => max(0, $newCount),
                'reason' => $data['condition'],
            ]);
            $livestockHerd->update(['current_count' => max(0, $newCount)]);
        }

        return back()->with('success', 'Catatan kesehatan berhasil disimpan.');
    }

    // ── Vaccinations ───────────────────────────────────────────────

    public function generateVaccinationSchedule(LivestockHerd $livestockHerd)
    {
        abort_if($livestockHerd->tenant_id !== $this->tid(), 403);

        $count = LivestockVaccination::generateSchedule($livestockHerd);

        if ($count === 0) {
            return back()->with('success', 'Jadwal vaksinasi sudah lengkap atau tidak tersedia untuk jenis ternak ini.');
        }

        return back()->with('success', "{$count} jadwal vaksinasi berhasil di-generate.");
    }

    public function recordVaccination(Request $request, LivestockVaccination $vaccination)
    {
        abort_if($vaccination->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'administered_date' => 'required|date',
            'vaccinated_count' => 'required|integer|min:1',
            'cost' => 'nullable|numeric|min:0',
            'administered_by' => 'nullable|string|max:100',
            'batch_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $vaccination->update(array_merge($data, [
            'status' => 'completed',
            'user_id' => auth()->id(),
        ]));

        // Post journal entry for vaccination cost (integration with Accounting)
        if (($data['cost'] ?? 0) > 0) {
            $herd = $vaccination->herd;
            $result = $this->integrationService->postVaccinationCost(
                $this->tid(),
                auth()->id(),
                $vaccination->id,
                $herd->code,
                $vaccination->vaccine_name,
                (float) $data['cost'],
                $data['administered_date']
            );
            if ($result->isFailed()) {
                Log::warning('Livestock vaccination journal failed: '.$result->reason);
            }
        }

        return back()->with('success', "Vaksinasi {$vaccination->vaccine_name} berhasil dicatat.");
    }
}
