<?php

namespace App\Http\Controllers;

use App\Models\CustomRole;
use App\Services\CustomRoleService;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomRoleController extends Controller
{
    public function __construct(
        private CustomRoleService $roleService,
    ) {}

    /**
     * Daftar semua custom roles + hardcoded roles (read-only).
     */
    public function index(): View
    {
        $customRoles = CustomRole::where('tenant_id', $this->tenantId())
            ->orderBy('name')
            ->get();

        $hardcodedRoles = collect(PermissionService::ROLE_DEFAULTS)
            ->except(['admin', 'affiliate'])
            ->keys()
            ->map(fn(string $role) => (object) [
                'name' => ucfirst($role),
                'slug' => $role,
                'is_hardcoded' => true,
            ]);

        return view('tenant.roles.index', compact('customRoles', 'hardcodedRoles'));
    }

    /**
     * Form buat role baru.
     */
    public function create(): View
    {
        $hardcodedRoles = collect(PermissionService::ROLE_DEFAULTS)
            ->except(['admin', 'affiliate'])
            ->keys();

        $customRoles = CustomRole::where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('tenant.roles.create', compact('hardcodedRoles', 'customRoles'));
    }

    /**
     * Simpan role baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'clone_from' => ['nullable', 'string'],
        ]);

        $name = $request->input('name');

        // Validasi nama role
        if (! $this->roleService->validateRoleName($this->tenantId(), $name)) {
            return back()->withInput()->withErrors([
                'name' => 'Nama role tidak valid atau sudah digunakan.',
            ]);
        }

        $cloneFrom = $request->input('clone_from');

        if ($cloneFrom && str_starts_with($cloneFrom, 'hardcoded:')) {
            // Clone dari hardcoded role
            $hardcodedRole = str_replace('hardcoded:', '', $cloneFrom);
            $role = $this->roleService->cloneFromHardcodedRole(
                $this->tenantId(),
                $hardcodedRole,
                $name,
                $this->authenticatedUserId()
            );

            if ($request->filled('description')) {
                $this->roleService->updateRole($role, ['description' => $request->input('description')], $this->authenticatedUserId());
            }
        } elseif ($cloneFrom && str_starts_with($cloneFrom, 'custom:')) {
            // Clone dari custom role
            $sourceId = (int) str_replace('custom:', '', $cloneFrom);
            $source = CustomRole::where('tenant_id', $this->tenantId())->findOrFail($sourceId);

            $role = $this->roleService->cloneRole($source, $name, $this->authenticatedUserId());

            if ($request->filled('description')) {
                $this->roleService->updateRole($role, ['description' => $request->input('description')], $this->authenticatedUserId());
            }
        } else {
            // Buat role baru tanpa clone
            $role = $this->roleService->createRole(
                $this->tenantId(),
                $name,
                $request->input('description'),
                $this->authenticatedUserId()
            );
        }

        return redirect()->route('tenant.roles.permissions', $role)
            ->with('success', "Role \"{$role->name}\" berhasil dibuat.");
    }

    /**
     * Form edit role.
     */
    public function edit(CustomRole $role): View
    {
        abort_if($role->tenant_id !== $this->tenantId(), 403);

        return view('tenant.roles.edit', compact('role'));
    }

    /**
     * Update role.
     */
    public function update(Request $request, CustomRole $role): RedirectResponse
    {
        abort_if($role->tenant_id !== $this->tenantId(), 403);

        $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $name = $request->input('name');

        // Validasi nama role (exclude current role)
        if (! $this->roleService->validateRoleName($this->tenantId(), $name, $role->id)) {
            return back()->withInput()->withErrors([
                'name' => 'Nama role tidak valid atau sudah digunakan.',
            ]);
        }

        $this->roleService->updateRole($role, [
            'name' => $name,
            'description' => $request->input('description'),
        ], $this->authenticatedUserId());

        return redirect()->route('tenant.roles.index')
            ->with('success', "Role \"{$name}\" berhasil diperbarui.");
    }

    /**
     * Hapus role.
     */
    public function destroy(CustomRole $role): RedirectResponse
    {
        abort_if($role->tenant_id !== $this->tenantId(), 403);

        try {
            $this->roleService->deleteRole($role);
        } catch (\RuntimeException $e) {
            return redirect()->route('tenant.roles.index')
                ->with('error', $e->getMessage());
        }

        return redirect()->route('tenant.roles.index')
            ->with('success', "Role \"{$role->name}\" berhasil dihapus.");
    }

    /**
     * Halaman permission matrix untuk role.
     */
    public function permissions(CustomRole $role): View
    {
        abort_if($role->tenant_id !== $this->tenantId(), 403);

        $modules = PermissionService::MODULES;
        $categories = PermissionService::moduleCategories();
        $rolePermissions = $this->roleService->getRolePermissions($role);

        return view('tenant.roles.permissions', compact('role', 'modules', 'categories', 'rolePermissions'));
    }

    /**
     * Simpan permissions untuk role.
     */
    public function savePermissions(Request $request, CustomRole $role): RedirectResponse
    {
        abort_if($role->tenant_id !== $this->tenantId(), 403);

        $submitted = $request->input('perms', []);
        $permissions = [];

        foreach (PermissionService::MODULES as $module => $actions) {
            foreach ($actions as $action) {
                $key = "{$module}.{$action}";
                if (isset($submitted[$key])) {
                    $permissions[] = [
                        'module' => $module,
                        'action' => $action,
                        'granted' => true,
                    ];
                }
            }
        }

        $this->roleService->syncPermissions($role, $permissions);

        return redirect()->route('tenant.roles.permissions', $role)
            ->with('success', 'Izin akses role berhasil disimpan.');
    }

    /**
     * Clone role.
     */
    public function clone(CustomRole $role): RedirectResponse
    {
        abort_if($role->tenant_id !== $this->tenantId(), 403);

        $newName = $role->name . ' (Copy)';

        // Pastikan nama unik
        $counter = 1;
        while (! $this->roleService->validateRoleName($this->tenantId(), $newName)) {
            $counter++;
            $newName = $role->name . " (Copy {$counter})";
        }

        $newRole = $this->roleService->cloneRole($role, $newName, $this->authenticatedUserId());

        return redirect()->route('tenant.roles.edit', $newRole)
            ->with('success', "Role berhasil diduplikasi sebagai \"{$newName}\".");
    }
}
