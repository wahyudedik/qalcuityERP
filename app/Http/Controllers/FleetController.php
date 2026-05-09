<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Employee;
use App\Models\FleetDriver;
use App\Models\FleetFuelLog;
use App\Models\FleetMaintenance;
use App\Models\FleetTrip;
use App\Models\FleetVehicle;
use App\Services\GlPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FleetController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    // ── Dashboard / Vehicles ──────────────────────────────────────

    public function index(Request $request)
    {
        $query = FleetVehicle::where('tenant_id', $this->tid());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('plate_number', 'like', "%$s%")
                ->orWhere('name', 'like', "%$s%"));
        }

        $vehicles = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total' => FleetVehicle::where('tenant_id', $this->tid())->where('is_active', true)->count(),
            'available' => FleetVehicle::where('tenant_id', $this->tid())->where('status', 'available')->count(),
            'in_use' => FleetVehicle::where('tenant_id', $this->tid())->where('status', 'in_use')->count(),
            'maintenance' => FleetVehicle::where('tenant_id', $this->tid())->where('status', 'maintenance')->count(),
            'fuel_month' => FleetFuelLog::where('tenant_id', $this->tid())
                ->whereMonth('date', now()->month)->whereYear('date', now()->year)
                ->sum('total_cost'),
        ];

        // Upcoming alerts
        $alerts = [];
        $expiring = FleetVehicle::where('tenant_id', $this->tid())->where('is_active', true)
            ->where(fn ($q) => $q->whereBetween('registration_expiry', [now(), now()->addDays(30)])
                ->orWhereBetween('insurance_expiry', [now(), now()->addDays(30)])
            )->get();
        foreach ($expiring as $v) {
            if ($v->isExpiringSoon('registration_expiry')) {
                $alerts[] = "STNK {$v->plate_number} expired ".$v->registration_expiry->format('d/m/Y');
            }
            if ($v->isExpiringSoon('insurance_expiry')) {
                $alerts[] = "Asuransi {$v->plate_number} expired ".$v->insurance_expiry->format('d/m/Y');
            }
        }

        $upcomingMaint = FleetMaintenance::where('tenant_id', $this->tid())
            ->where('status', 'scheduled')
            ->where('scheduled_date', '<=', now()->addDays(7))
            ->with('vehicle')->limit(5)->get();

        $assets = Asset::where('tenant_id', $this->tid())->where('category', 'vehicle')->orderBy('name')->get();

        return view('fleet.index', compact('vehicles', 'stats', 'alerts', 'upcomingMaint', 'assets'));
    }

    public function storeVehicle(Request $request)
    {
        $data = $request->validate([
            'plate_number' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'type' => 'required|in:car,truck,motorcycle,van',
            'brand' => 'nullable|string|max:50',
            'model' => 'nullable|string|max:50',
            'year' => 'nullable|integer|min:1990|max:2030',
            'color' => 'nullable|string|max:30',
            'vin' => 'nullable|string|max:50',
            'asset_id' => 'nullable|exists:assets,id',
            'registration_expiry' => 'nullable|date',
            'insurance_expiry' => 'nullable|date',
            'odometer' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        FleetVehicle::create(array_merge($data, [
            'tenant_id' => $this->tid(),
            'status' => 'available',
            'is_active' => true,
            'odometer' => $data['odometer'] ?? 0,
        ]));

        return back()->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    public function updateVehicle(Request $request, FleetVehicle $fleetVehicle)
    {
        abort_if($fleetVehicle->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'plate_number' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'type' => 'required|in:car,truck,motorcycle,van',
            'brand' => 'nullable|string|max:50',
            'model' => 'nullable|string|max:50',
            'year' => 'nullable|integer|min:1990|max:2030',
            'color' => 'nullable|string|max:30',
            'registration_expiry' => 'nullable|date',
            'insurance_expiry' => 'nullable|date',
            'odometer' => 'nullable|integer|min:0',
            'status' => 'nullable|in:available,in_use,maintenance,retired',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $fleetVehicle->update($data);

        return back()->with('success', 'Kendaraan berhasil diperbarui.');
    }

    public function destroyVehicle(FleetVehicle $fleetVehicle)
    {
        abort_if($fleetVehicle->tenant_id !== $this->tid(), 403);
        $fleetVehicle->delete();

        return back()->with('success', 'Kendaraan berhasil dihapus.');
    }

    // ── Drivers ───────────────────────────────────────────────────

    public function drivers(Request $request)
    {
        $drivers = FleetDriver::where('tenant_id', $this->tid())
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('license_number', 'like', "%$s%"))
            ->latest()->paginate(20)->withQueryString();

        $employees = Employee::where('tenant_id', $this->tid())->where('status', 'active')->orderBy('name')->get();

        return view('fleet.drivers', compact('drivers', 'employees'));
    }

    public function storeDriver(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'employee_id' => 'nullable|exists:employees,id',
            'license_number' => 'nullable|string|max:30',
            'license_type' => 'nullable|in:A,B1,B2,C',
            'license_expiry' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        FleetDriver::create(array_merge($data, [
            'tenant_id' => $this->tid(),
            'status' => 'active',
            'is_active' => true,
        ]));

        return back()->with('success', 'Driver berhasil ditambahkan.');
    }

    public function updateDriver(Request $request, FleetDriver $fleetDriver)
    {
        abort_if($fleetDriver->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'employee_id' => 'nullable|exists:employees,id',
            'license_number' => 'nullable|string|max:30',
            'license_type' => 'nullable|in:A,B1,B2,C',
            'license_expiry' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
            'status' => 'nullable|in:active,on_trip,off_duty,inactive',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $fleetDriver->update($data);

        return back()->with('success', 'Driver berhasil diperbarui.');
    }

    public function destroyDriver(FleetDriver $fleetDriver)
    {
        abort_if($fleetDriver->tenant_id !== $this->tid(), 403);
        $fleetDriver->delete();

        return back()->with('success', 'Driver berhasil dihapus.');
    }

    // ── Trips ─────────────────────────────────────────────────────

    public function trips(Request $request)
    {
        $query = FleetTrip::with(['vehicle', 'driver'])
            ->where('tenant_id', $this->tid());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('trip_number', 'like', "%$s%")->orWhere('purpose', 'like', "%$s%"));
        }

        $trips = $query->latest()->paginate(20)->withQueryString();
        $vehicles = FleetVehicle::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();
        $drivers = FleetDriver::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();

        return view('fleet.trips', compact('trips', 'vehicles', 'drivers'));
    }

    public function storeTrip(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => 'required|exists:fleet_vehicles,id',
            'driver_id' => 'nullable|exists:fleet_drivers,id',
            'purpose' => 'required|string|max:255',
            'origin' => 'nullable|string|max:255',
            'destination' => 'nullable|string|max:255',
            'departed_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($data) {
            $vehicle = FleetVehicle::findOrFail($data['vehicle_id']);

            FleetTrip::create(array_merge($data, [
                'tenant_id' => $this->tid(),
                'user_id' => auth()->id(),
                'trip_number' => 'TRP-'.date('Ymd').'-'.strtoupper(Str::random(4)),
                'odometer_start' => $vehicle->odometer,
                'status' => $data['departed_at'] ? 'in_progress' : 'planned',
            ]));

            // Update vehicle & driver status
            if ($data['departed_at']) {
                $vehicle->update(['status' => 'in_use']);
                if (! empty($data['driver_id'])) {
                    FleetDriver::where('id', $data['driver_id'])->update(['status' => 'on_trip']);
                }
            }
        });

        return back()->with('success', 'Trip berhasil dibuat.');
    }

    public function completeTrip(Request $request, FleetTrip $fleetTrip)
    {
        abort_if($fleetTrip->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'odometer_end' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($fleetTrip, $data) {
            $fleetTrip->update([
                'odometer_end' => $data['odometer_end'],
                'returned_at' => now(),
                'status' => 'completed',
                'notes' => $data['notes'] ?? $fleetTrip->notes,
            ]);

            // Update vehicle odometer & status
            $fleetTrip->vehicle->update([
                'odometer' => $data['odometer_end'],
                'status' => 'available',
            ]);

            // Release driver
            if ($fleetTrip->driver_id) {
                FleetDriver::where('id', $fleetTrip->driver_id)->update(['status' => 'active']);
            }
        });

        return back()->with('success', 'Trip selesai. Odometer diperbarui.');
    }

    // ── Fuel Logs ─────────────────────────────────────────────────

    public function fuelLogs(Request $request)
    {
        $query = FleetFuelLog::with(['vehicle', 'driver'])
            ->where('tenant_id', $this->tid());

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('month')) {
            [$y, $m] = explode('-', $request->month);
            $query->whereYear('date', $y)->whereMonth('date', $m);
        }

        $fuelLogs = $query->latest('date')->paginate(20)->withQueryString();
        $vehicles = FleetVehicle::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();
        $drivers = FleetDriver::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();

        // Monthly summary
        $monthlySummary = FleetFuelLog::where('tenant_id', $this->tid())
            ->whereMonth('date', now()->month)->whereYear('date', now()->year)
            ->selectRaw('SUM(total_cost) as total_cost, SUM(liters) as total_liters, COUNT(*) as count')
            ->first();

        return view('fleet.fuel-logs', compact('fuelLogs', 'vehicles', 'drivers', 'monthlySummary'));
    }

    public function storeFuelLog(Request $request, GlPostingService $glService)
    {
        $data = $request->validate([
            'vehicle_id' => 'required|exists:fleet_vehicles,id',
            'driver_id' => 'nullable|exists:fleet_drivers,id',
            'date' => 'required|date',
            'odometer' => 'required|integer|min:0',
            'fuel_type' => 'required|string|max:30',
            'liters' => 'required|numeric|min:0.01',
            'price_per_liter' => 'required|numeric|min:0',
            'station' => 'nullable|string|max:255',
            'receipt_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        $totalCost = round($data['liters'] * $data['price_per_liter'], 2);

        DB::transaction(function () use ($data, $totalCost, $glService) {
            $log = FleetFuelLog::create(array_merge($data, [
                'tenant_id' => $this->tid(),
                'user_id' => auth()->id(),
                'total_cost' => $totalCost,
            ]));

            // Update vehicle odometer
            FleetVehicle::where('id', $data['vehicle_id'])
                ->where('odometer', '<', $data['odometer'])
                ->update(['odometer' => $data['odometer']]);

            // GL: Dr Beban BBM (5203) / Cr Kas (1101)
            if ($totalCost > 0) {
                $vehicle = FleetVehicle::find($data['vehicle_id']);
                $ref = 'FUEL-'.($vehicle->plate_number ?? $log->id);
                $glResult = $glService->postFleetFuel(
                    $this->tid(), auth()->id(), $ref, $log->id, $totalCost, $data['date']
                );
                if ($glResult->isSuccess()) {
                    $log->update(['journal_entry_id' => $glResult->journal->id]);
                }
                if ($glResult->isFailed()) {
                    session()->flash('gl_warning', $glResult->warningMessage());
                }
            }
        });

        return back()->with('success', 'Log BBM berhasil dicatat. Total: Rp '.number_format($totalCost, 0, ',', '.'));
    }

    // ── Maintenance ───────────────────────────────────────────────

    public function maintenance(Request $request)
    {
        $query = FleetMaintenance::with('vehicle')
            ->where('tenant_id', $this->tid());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        $maintenances = $query->latest()->paginate(20)->withQueryString();
        $vehicles = FleetVehicle::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();

        return view('fleet.maintenance', compact('maintenances', 'vehicles'));
    }

    public function storeMaintenance(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => 'required|exists:fleet_vehicles,id',
            'type' => 'required|in:routine,repair,inspection,tire,oil_change',
            'description' => 'required|string|max:255',
            'scheduled_date' => 'nullable|date',
            'cost' => 'nullable|numeric|min:0',
            'vendor' => 'nullable|string|max:255',
            'next_km' => 'nullable|integer|min:0',
            'next_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        FleetMaintenance::create(array_merge($data, [
            'tenant_id' => $this->tid(),
            'status' => 'scheduled',
            'cost' => $data['cost'] ?? 0,
        ]));

        return back()->with('success', 'Jadwal maintenance berhasil dibuat.');
    }

    public function completeMaintenance(Request $request, FleetMaintenance $fleetMaintenance, GlPostingService $glService)
    {
        abort_if($fleetMaintenance->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'cost' => 'required|numeric|min:0',
            'completed_date' => 'required|date',
            'odometer_at' => 'nullable|integer|min:0',
            'vendor' => 'nullable|string|max:255',
            'next_km' => 'nullable|integer|min:0',
            'next_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($fleetMaintenance, $data, $glService) {
            $fleetMaintenance->update(array_merge($data, ['status' => 'completed']));

            // Release vehicle from maintenance
            $vehicle = $fleetMaintenance->vehicle;
            if ($vehicle && $vehicle->status === 'maintenance') {
                $vehicle->update(['status' => 'available']);
            }
            if (! empty($data['odometer_at']) && $vehicle) {
                $vehicle->update(['odometer' => max($vehicle->odometer, $data['odometer_at'])]);
            }

            // GL: Dr Beban Pemeliharaan (5207) / Cr Kas (1101)
            if ($data['cost'] > 0) {
                $ref = 'MAINT-'.($vehicle->plate_number ?? $fleetMaintenance->id);
                $glResult = $glService->postFleetMaintenance(
                    $this->tid(), auth()->id(), $ref, $fleetMaintenance->id,
                    $data['cost'], $data['completed_date']
                );
                if ($glResult->isSuccess()) {
                    $fleetMaintenance->update(['journal_entry_id' => $glResult->journal->id]);
                }
                if ($glResult->isFailed()) {
                    session()->flash('gl_warning', $glResult->warningMessage());
                }
            }
        });

        return back()->with('success', 'Maintenance selesai. Biaya: Rp '.number_format($data['cost'], 0, ',', '.'));
    }

    public function destroyMaintenance(FleetMaintenance $fleetMaintenance)
    {
        abort_if($fleetMaintenance->tenant_id !== $this->tid(), 403);
        $fleetMaintenance->delete();

        return back()->with('success', 'Jadwal maintenance berhasil dihapus.');
    }
}
