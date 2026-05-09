<?php

namespace App\Http\Controllers;

use App\Models\CustomRole;
use App\Models\ErpNotification;
use App\Models\User;
use App\Notifications\NewUserAddedNotification;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class TenantUserController extends Controller
{
    public function __construct(private PermissionService $permissions) {}

    // tenantId() inherited from parent Controller

    public function index(): View
    {
        $users = User::where('tenant_id', $this->tenantId())
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        return view('tenant.users.index', compact('users'));
    }

    public function create(): View
    {
        $customRoles = CustomRole::where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('tenant.users.create', compact('customRoles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'role' => ['required', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $role = $request->input('role');

        // Validate role value
        $validHardcoded = ['manager', 'staff', 'kasir', 'gudang'];
        if (! in_array($role, $validHardcoded) && ! str_starts_with($role, 'custom:')) {
            return back()->withInput()->withErrors(['role' => 'Role tidak valid.']);
        }

        // Validate custom role belongs to same tenant
        if (str_starts_with($role, 'custom:')) {
            $customRoleId = (int) str_replace('custom:', '', $role);
            $customRoleExists = CustomRole::where('id', $customRoleId)
                ->where('tenant_id', $this->tenantId())
                ->where('is_active', true)
                ->exists();

            if (! $customRoleExists) {
                return back()->withInput()->withErrors(['role' => 'Custom role tidak ditemukan atau tidak aktif.']);
            }
        }

        $plainPassword = $request->password;

        $user = User::create([
            'tenant_id' => $this->tenantId(),
            'name' => $request->name,
            'email' => $request->email,
            'role' => $role,
            'password' => Hash::make($plainPassword),
            'is_active' => true,
        ]);

        // Kirim email kredensial ke user baru
        $user->notify(new NewUserAddedNotification($user->load('tenant'), $plainPassword));

        // In-app notification untuk admin
        ErpNotification::create([
            'tenant_id' => $this->tenantId(),
            'user_id' => auth()->id(),
            'type' => 'user_added',
            'title' => '👤 Pengguna Baru Ditambahkan',
            'body' => "Akun untuk {$request->name} ({$role}) berhasil dibuat dan email kredensial telah dikirim.",
            'data' => ['user_id' => $user->id, 'role' => $role],
        ]);

        return redirect()->route('tenant.users.index')
            ->with('success', "Pengguna berhasil ditambahkan. Email kredensial dikirim ke {$request->email}.");
    }

    public function edit(User $user): View
    {
        abort_if($user->tenant_id !== $this->tenantId(), 403);

        $customRoles = CustomRole::where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('tenant.users.edit', compact('user', 'customRoles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($user->tenant_id !== $this->tenantId(), 403);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string'],
        ]);

        $role = $request->input('role');

        // Validate role value
        $validHardcoded = ['manager', 'staff', 'kasir', 'gudang'];
        if (! in_array($role, $validHardcoded) && ! str_starts_with($role, 'custom:')) {
            return back()->withInput()->withErrors(['role' => 'Role tidak valid.']);
        }

        // Validate custom role belongs to same tenant
        if (str_starts_with($role, 'custom:')) {
            $customRoleId = (int) str_replace('custom:', '', $role);
            $customRoleExists = CustomRole::where('id', $customRoleId)
                ->where('tenant_id', $this->tenantId())
                ->where('is_active', true)
                ->exists();

            if (! $customRoleExists) {
                return back()->withInput()->withErrors(['role' => 'Custom role tidak ditemukan atau tidak aktif.']);
            }
        }

        $oldRole = $user->role;

        $user->update([
            'name' => $request->name,
            'role' => $role,
        ]);

        // Invalidate user permission cache on role change
        if ($oldRole !== $role) {
            Cache::forget("user_perms_v3:{$user->id}");
            Cache::forget("user_perms_v2:{$user->id}");
            Cache::forget("user_overrides:{$user->id}");
        }

        if ($request->filled('password')) {
            $request->validate(['password' => ['confirmed', Rules\Password::defaults()]]);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('tenant.users.index')
            ->with('success', 'Data pengguna diperbarui.');
    }

    public function toggleActive(User $user): RedirectResponse
    {
        abort_if($user->tenant_id !== $this->tenantId(), 403);
        abort_if($user->isAdmin(), 403, 'Tidak dapat menonaktifkan admin utama.');

        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('tenant.users.index')
            ->with('success', "Pengguna berhasil {$status}.");
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->tenant_id !== $this->tenantId(), 403);
        abort_if($user->isAdmin(), 403, 'Tidak dapat menghapus admin utama.');

        $user->delete();

        return redirect()->route('tenant.users.index')
            ->with('success', 'Pengguna dihapus.');
    }

    // ─── Granular Permissions ─────────────────────────────────────

    public function permissions(User $user): View
    {
        abort_if($user->tenant_id !== $this->tenantId(), 403);
        abort_if($user->isAdmin() || $user->isSuperAdmin(), 403, 'Admin memiliki akses penuh.');

        $userPerms = $this->permissions->getUserPermissions($user);
        $modules = PermissionService::MODULES;
        $roleDefault = PermissionService::ROLE_DEFAULTS[$user->role] ?? [];

        return view('tenant.users.permissions', compact('user', 'userPerms', 'modules', 'roleDefault'));
    }

    public function savePermissions(Request $request, User $user): RedirectResponse
    {
        abort_if($user->tenant_id !== $this->tenantId(), 403);
        abort_if($user->isAdmin() || $user->isSuperAdmin(), 403);

        // Build permission map from checkboxes
        // Checkbox name format: perms[sales.view] = "1"
        $submitted = $request->input('perms', []);
        $allPerms = [];

        foreach (PermissionService::MODULES as $module => $actions) {
            foreach ($actions as $action) {
                $allPerms["{$module}.{$action}"] = isset($submitted["{$module}.{$action}"]);
            }
        }

        $this->permissions->saveUserPermissions($user, $allPerms);

        return redirect()->route('tenant.users.permissions', $user)
            ->with('success', 'Izin akses berhasil disimpan.');
    }

    public function resetPermissions(User $user): RedirectResponse
    {
        abort_if($user->tenant_id !== $this->tenantId(), 403);

        $this->permissions->resetUserPermissions($user);

        return redirect()->route('tenant.users.permissions', $user)
            ->with('success', 'Izin akses direset ke default role.');
    }
}
