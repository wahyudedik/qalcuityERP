<x-app-layout>
    <x-slot name="title">Izin Akses — {{ $user->name }}</x-slot>
    <x-slot name="header">Izin Akses: {{ $user->name }}</x-slot>
    <x-slot name="pageHeader">
        <a href="{{ route('tenant.users.index') }}"
            class="flex items-center gap-1.5 text-sm text-slate-400 hover:text-white transition px-3 py-1.5 rounded-lg hover:bg-white/10">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali
        </a>
    </x-slot>

    @php
        $categories = \App\Services\PermissionService::moduleCategories();
        $actionLabels = ['view' => 'Lihat', 'create' => 'Tambah', 'edit' => 'Edit', 'delete' => 'Hapus'];
    @endphp

    {{-- User info --}}
    <div class="mb-5 flex items-center gap-4 bg-white border border-gray-200 rounded-2xl px-5 py-4">
        <div
            class="w-11 h-11 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white font-bold text-lg shrink-0">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-900">{{ $user->name }}</p>
            <p class="text-xs text-slate-400">{{ $user->email }} &middot; Role: <span
                    class="text-blue-400 font-medium">{{ $user->roleLabel() }}</span></p>
        </div>
        <div class="text-xs text-slate-500 text-right hidden sm:block">
            <p>Centang = izinkan &nbsp;|&nbsp; Kosong = tolak</p>
            <p class="text-amber-400 mt-0.5">Override menggantikan default role</p>
        </div>
    </div>

    @if (session('success'))
        <div
            class="mb-4 flex items-center gap-3 bg-green-500/10 border border-green-500/20 text-green-400 text-sm px-4 py-3 rounded-xl">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('tenant.users.permissions.save', $user) }}">
        @csrf

        <div class="space-y-4">
            @foreach ($categories ?? [] as $catLabel => $catModules)
                @php
                    // Only show categories that have at least one module in MODULES
                    $visibleModules = array_filter($catModules, fn($m) => isset($modules[$m]));
                @endphp
                @if (count($visibleModules) === 0)
                    @continue
                @endif

                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                    {{-- Category header --}}
                    <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $catLabel }}</h3>
                    </div>

                    {{-- Table --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-500 w-52">Modul
                                    </th>
                                    @foreach ($actionLabels ?? [] as $act => $lbl)
                                        <th class="text-center px-4 py-2.5 text-xs font-semibold text-slate-500 w-20">
                                            {{ $lbl }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($visibleModules ?? [] as $module)
                                    @php $actions = $modules[$module]; @endphp
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-5 py-3 font-medium text-gray-800 text-sm">
                                            {{ \App\Services\PermissionService::moduleLabel($module) }}
                                            @php
                                                // Show if this row has any override vs role default
                                                $hasOverride = false;
                                                foreach ($actions as $act) {
                                                    $roleVal = is_array($roleDefault)
                                                        ? in_array($act, $roleDefault[$module] ?? [])
                                                        : $roleDefault === '*';
                                                    $curVal = $userPerms[$module][$act] ?? false;
                                                    if ($curVal !== $roleVal) {
                                                        $hasOverride = true;
                                                        break;
                                                    }
                                                }
                                            @endphp
                                            @if ($hasOverride)
                                                <span
                                                    class="ml-1.5 text-[10px] font-semibold px-1.5 py-0.5 rounded bg-amber-500/15 text-amber-400">override</span>
                                            @endif
                                        </td>
                                        @foreach ($actionLabels ?? [] as $action => $lbl)
                                            <td class="text-center px-4 py-3">
                                                @if (in_array($action, $actions))
                                                    @php
                                                        $key = "{$module}.{$action}";
                                                        $current = $userPerms[$module][$action] ?? false;
                                                        $isDefault = is_array($roleDefault)
                                                            ? in_array($action, $roleDefault[$module] ?? [])
                                                            : $roleDefault === '*';
                                                    @endphp
                                                    <input type="checkbox" name="perms[{{ $key }}]"
                                                        value="1" {{ $current ? 'checked' : '' }}
                                                        title="{{ $isDefault ? 'Default role: izin' : 'Default role: tolak' }}"
                                                        class="w-4 h-4 rounded border-gray-300 text-blue-500 bg-white focus:ring-blue-500 cursor-pointer accent-blue-500">
                                                @else
                                                    <span class="text-slate-700 text-xs">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3 mt-6 pb-6">
            <button type="submit"
                class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
                Simpan Izin
            </button>
            <button type="button"
                onclick="Dialog.confirm('Reset semua izin ke default role {{ $user->roleLabel() }}?').then(ok => { if(ok) document.getElementById('reset-form').submit() })"
                class="px-6 py-2.5 bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition">
                Reset ke Default Role
            </button>
        </div>
    </form>

    <form id="reset-form" method="POST" action="{{ route('tenant.users.permissions.reset', $user) }}" class="hidden">
        @csrf @method('DELETE')
    </form>
</x-app-layout>
