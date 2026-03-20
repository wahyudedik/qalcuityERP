<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class TenantUserController extends Controller
{
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
            'role'     => ['required', 'in:manager,staff'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'tenant_id' => $this->tenantId(),
            'name'      => $request->name,
            'email'     => $request->email,
            'role'      => $request->role,
            'password'  => Hash::make($request->password),
            'is_active' => true,
        ]);

        return redirect()->route('tenant.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
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
            'role'  => ['required', 'in:manager,staff'],
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
}
