<?php

namespace App\Http\Controllers;

use App\Models\FingerprintDevice;
use App\Models\Employee;
use App\Services\FingerprintDeviceService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class FingerprintDeviceController extends Controller
{
    public function __construct(
        private FingerprintDeviceService $fingerprintService
    ) {
    }

    // tenantId() inherited from parent Controller

    /**
     * Display a listing of fingerprint devices
     */
    public function index(): View
    {
        $devices = FingerprintDevice::where('tenant_id', $this->tenantId())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('hrm.fingerprint.devices.index', compact('devices'));
    }

    /**
     * Show the form for creating a new device
     */
    public function create(): View
    {
        return view('hrm.fingerprint.devices.create');
    }

    /**
     * Store a newly created device
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'device_id' => 'required|string|unique:fingerprint_devices,device_id',
            'ip_address' => 'nullable|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'protocol' => 'required|in:tcp,udp,http,https',
            'vendor' => 'required|in:zkteco,suprema,generic',
            'model' => 'nullable|string|max:255',
            'api_key' => 'nullable|string|max:255',
            'secret_key' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            'config' => 'nullable|array',
        ]);

        $validated['tenant_id'] = $this->tenantId();
        $validated['is_active'] = $request->has('is_active');

        FingerprintDevice::create($validated);

        return redirect()->route('hrm.fingerprint.devices.index')
            ->with('success', 'Perangkat fingerprint berhasil ditambahkan.');
    }

    /**
     * Display the specified device
     */
    public function show(FingerprintDevice $device): View
    {
        $this->authorizeTenant($device);

        $status = $this->fingerprintService->getDeviceStatus($device);
        $recentLogs = $device->attendanceLogs()
            ->with('employee')
            ->orderBy('scan_time', 'desc')
            ->take(20)
            ->get();

        return view('hrm.fingerprint.devices.show', compact('device', 'status', 'recentLogs'));
    }

    /**
     * Show the form for editing the specified device
     */
    public function edit(FingerprintDevice $device): View
    {
        $this->authorizeTenant($device);

        return view('hrm.fingerprint.devices.edit', compact('device'));
    }

    /**
     * Update the specified device
     */
    public function update(Request $request, FingerprintDevice $device): RedirectResponse
    {
        $this->authorizeTenant($device);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'nullable|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'protocol' => 'required|in:tcp,udp,http,https',
            'vendor' => 'required|in:zkteco,suprema,generic',
            'model' => 'nullable|string|max:255',
            'api_key' => 'nullable|string|max:255',
            'secret_key' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            'config' => 'nullable|array',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $device->update($validated);

        return redirect()->route('hrm.fingerprint.devices.index')
            ->with('success', 'Perangkat fingerprint berhasil diperbarui.');
    }

    /**
     * Remove the specified device
     */
    public function destroy(FingerprintDevice $device): RedirectResponse
    {
        $this->authorizeTenant($device);

        $device->delete();

        return redirect()->route('hrm.fingerprint.devices.index')
            ->with('success', 'Perangkat fingerprint berhasil dihapus.');
    }

    /**
     * Test connection to device
     */
    public function testConnection(FingerprintDevice $device): JsonResponse
    {
        $this->authorizeTenant($device);

        $result = $this->fingerprintService->testConnection($device);

        return response()->json($result);
    }

    /**
     * Sync attendance logs from device
     */
    public function syncAttendance(FingerprintDevice $device): JsonResponse
    {
        $this->authorizeTenant($device);

        $result = $this->fingerprintService->syncAttendanceLogs($device);

        return response()->json($result);
    }

    /**
     * Show employee fingerprint registration page
     */
    public function registerEmployee(Employee $employee): View
    {
        $this->authorizeTenant($employee);

        $devices = FingerprintDevice::where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->get();

        return view('hrm.fingerprint.employees.register', compact('employee', 'devices'));
    }

    /**
     * Register employee fingerprint on device
     */
    public function storeEmployeeRegistration(Request $request, Employee $employee): JsonResponse
    {
        $this->authorizeTenant($employee);

        $validated = $request->validate([
            'device_id' => 'required|exists:fingerprint_devices,id',
            'uid' => 'required|string|max:255',
        ]);

        $device = FingerprintDevice::findOrFail($validated['device_id']);
        $this->authorizeTenant($device);

        $result = $this->fingerprintService->registerEmployeeFingerprint(
            $device,
            $employee,
            $validated['uid']
        );

        return response()->json($result);
    }

    /**
     * Remove employee fingerprint from device
     */
    public function removeEmployeeRegistration(Employee $employee): JsonResponse
    {
        $this->authorizeTenant($employee);

        if (!$employee->fingerprint_registered || !$employee->fingerprint_uid) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan belum terdaftar di perangkat fingerprint'
            ], 400);
        }

        // Get first active device
        $device = FingerprintDevice::where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada perangkat fingerprint aktif'
            ], 400);
        }

        $result = $this->fingerprintService->removeEmployeeFingerprint($device, $employee);

        return response()->json($result);
    }

    /**
     * List employees with fingerprint status
     */
    public function employeeList(): View
    {
        $employees = Employee::where('tenant_id', $this->tenantId())
            ->select('id', 'employee_id', 'name', 'position', 'department', 'fingerprint_uid', 'fingerprint_registered')
            ->orderBy('name')
            ->paginate(20);

        return view('hrm.fingerprint.employees.index', compact('employees'));
    }

    /**
     * Authorize that resource belongs to tenant
     */
    private function authorizeTenant($model): void
    {
        if ($model->tenant_id !== $this->tenantId()) {
            abort(403, 'Akses ditolak: data bukan milik tenant Anda.');
        }
    }
}
