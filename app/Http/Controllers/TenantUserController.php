<?php

namespace App\Http\Controllers;

use App\Models\ErpNotification;
use App\Models\User;
use App\Notifications\NewUserAddedNotification;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class TenantUserController extends Controller
{
    public function __construct(private PermissionService $permissions) {}

    private function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

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
        return view('tenant.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'role'     => ['required', 'in:manager,staff,kasir,gudang'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $plainPassword = $request->password;

        $user = User::create([
            'tenant_id' => $this->tenantId(),
            'name'      => $request->name,
            'email'     => $request->email,
            'role'      => $request->role,
            'password'  => Hash::make($plainPassword),
            'is_active' => true,
        ]);

        // Kirim email kredensial ke user baru
        $user->notify(new NewUserAddedNotification($user->load('tenant'), $plainPassword));

        // In-app notification untuk admin
        ErpNotification::create([
            'tenant_id' => $this->tenantId(),
            'user_id'   => auth()->id(),
            'type'      => 'user_added',
            'title'     => '👤 Pengguna Baru Ditambahkan',
            'body'      => "Akun untuk {$request->name} ({$request->role}) berhasil dibuat dan email kredensial telah dikirim.",
            'data'      => ['user_id' => $user->id, 'role' => $request->role],
        ]);

        return redirect()->route('tenant.users.index')
            ->with('success', "Pengguna berhasil ditambahkan. Email kredensial dikirim ke {$request->email}.");
    }

    public function edit(User $user): View
    {
        abort_if($user->tenant_id !== $this->tenantId(), 403);

        return view('tenant.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($user->tenant_id !== $this->tenantId(), 403);

        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'role'  => ['required', 'in:manager,staff,kasir,gudang'],
        ]);

        $user->update([
            'name' => $request->name,
            'role' => $request->role,
        ]);

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

        $userPerms   = $this->permissions->getUserPermissions($user);
        $modules     = PermissionService::MODULES;
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
        $allPerms  = [];

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
