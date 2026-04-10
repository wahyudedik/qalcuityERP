<?php

namespace App\Http\Controllers;

use App\Models\TenantStorageConfig;
use App\Services\CloudStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CloudStorageController extends Controller
{
    protected CloudStorageService $cloudStorageService;

    public function __construct(CloudStorageService $cloudStorageService)
    {
        $this->cloudStorageService = $cloudStorageService;
    }

    /**
     * Display cloud storage configuration
     */
    public function index()
    {
        $configs = TenantStorageConfig::where('tenant_id', Auth::user()->tenant_id)
            ->latest()
            ->get()
            ->map(function ($config) {
                return [
                    'id' => $config->id,
                    'provider' => $config->provider,
                    'bucket_name' => $config->bucket_name,
                    'region' => $config->region,
                    'is_active' => $config->is_active,
                    'is_default' => $config->is_default,
                    'created_at' => $config->created_at->format('d M Y H:i'),
                ];
            });

        $storageUsage = $this->cloudStorageService->getStorageUsage();

        return view('documents.cloud-storage-config', compact('configs', 'storageUsage'));
    }

    /**
     * Store new storage configuration
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|in:s3,gcs,azure',
            'bucket_name' => 'required|string|max:255',
            'region' => 'nullable|string|max:100',
            'access_key' => 'required|string',
            'secret_key' => 'required|string',
            'endpoint' => 'nullable|url',
            'additional_config' => 'nullable|array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        // If setting as default, unset other defaults
        if ($request->boolean('is_default')) {
            TenantStorageConfig::where('tenant_id', Auth::user()->tenant_id)
                ->update(['is_default' => false]);
        }

        $config = TenantStorageConfig::create([
            'tenant_id' => Auth::user()->tenant_id,
            'provider' => $validated['provider'],
            'bucket_name' => $validated['bucket_name'],
            'region' => $validated['region'] ?? null,
            'access_key' => $validated['access_key'],
            'secret_key' => $validated['secret_key'],
            'endpoint' => $validated['endpoint'] ?? null,
            'additional_config' => $validated['additional_config'] ?? [],
            'is_active' => $validated['is_active'] ?? true,
            'is_default' => $validated['is_default'] ?? false,
        ]);

        return redirect()->back()
            ->with('success', 'Cloud storage configuration added successfully');
    }

    /**
     * Update storage configuration
     */
    public function update(Request $request, TenantStorageConfig $config)
    {
        $this->authorize('update', $config);

        $validated = $request->validate([
            'bucket_name' => 'required|string|max:255',
            'region' => 'nullable|string|max:100',
            'access_key' => 'nullable|string',
            'secret_key' => 'nullable|string',
            'endpoint' => 'nullable|url',
            'additional_config' => 'nullable|array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        // If setting as default, unset other defaults
        if ($request->boolean('is_default') && !$config->is_default) {
            TenantStorageConfig::where('tenant_id', Auth::user()->tenant_id)
                ->where('id', '!=', $config->id)
                ->update(['is_default' => false]);
        }

        // Only update credentials if provided
        if (empty($validated['access_key'])) {
            unset($validated['access_key']);
        }
        if (empty($validated['secret_key'])) {
            unset($validated['secret_key']);
        }

        $config->update($validated);

        return redirect()->back()
            ->with('success', 'Cloud storage configuration updated successfully');
    }

    /**
     * Delete storage configuration
     */
    public function destroy(TenantStorageConfig $config)
    {
        $this->authorize('delete', $config);

        if ($config->is_default) {
            return redirect()->back()
                ->with('error', 'Cannot delete default storage configuration');
        }

        $config->delete();

        return redirect()->back()
            ->with('success', 'Cloud storage configuration deleted');
    }

    /**
     * Test connection
     */
    public function testConnection(Request $request)
    {
        $validated = $request->validate([
            'config_id' => 'required|exists:tenant_storage_configs,id',
        ]);

        $config = TenantStorageConfig::findOrFail($validated['config_id']);

        // Create service with specific config
        $service = new CloudStorageService(Auth::user()->tenant_id);
        $service->config = $config;

        $success = $service->testConnection();

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Connection successful',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Connection failed',
        ], 500);
    }

    /**
     * Set as default storage
     */
    public function setDefault(TenantStorageConfig $config)
    {
        $this->authorize('update', $config);

        // Unset other defaults
        TenantStorageConfig::where('tenant_id', Auth::user()->tenant_id)
            ->where('id', '!=', $config->id)
            ->update(['is_default' => false]);

        // Set this as default
        $config->update(['is_default' => true]);

        return redirect()->back()
            ->with('success', 'Default storage configuration updated');
    }

    /**
     * Get storage statistics
     */
    public function statistics()
    {
        $configs = TenantStorageConfig::where('tenant_id', Auth::user()->tenant_id)
            ->get()
            ->map(function ($config) {
                return [
                    'provider' => $config->provider,
                    'bucket' => $config->bucket_name,
                    'is_active' => $config->is_active,
                    'is_default' => $config->is_default,
                ];
            });

        return response()->json([
            'total_configs' => $configs->count(),
            'active_configs' => $configs->where('is_active', true)->count(),
            'providers' => $configs->pluck('provider')->unique()->values(),
            'configurations' => $configs,
        ]);
    }
}
