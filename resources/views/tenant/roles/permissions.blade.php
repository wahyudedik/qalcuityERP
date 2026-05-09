<x-app-layout>
    <x-slot name="title">Izin Akses: {{ $role->name }} — Qalcuity ERP</x-slot>
    <x-slot name="header">Izin Akses: {{ $role->name }}</x-slot>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-green-50 border border-green-200 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('tenant.roles.permissions.save', $role) }}" x-data="permissionMatrix()">
        @csrf

        {{-- Toolbar --}}
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <a href="{{ route('tenant.roles.edit', $role) }}"
                    class="px-3 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                    ← Kembali ke Edit
                </a>
            </div>
            <button type="submit"
                class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 rounded-xl transition shadow-sm shadow-blue-200">
                Simpan Izin Akses
            </button>
        </div>

        {{-- Permission Matrix --}}
        @foreach ($categories as $categoryName => $categoryModules)
            @php
                $availableModules = collect($categoryModules)->filter(fn($m) => isset($modules[$m]));
            @endphp
            @if ($availableModules->isEmpty())
                @continue
            @endif

            <div class="mb-6 bg-white rounded-2xl border border-gray-200 overflow-hidden">
                {{-- Category Header --}}
                <div class="px-4 sm:px-6 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700">{{ $categoryName }}</h3>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="selectAllCategory('{{ $categoryName }}')"
                            class="text-xs text-blue-600 hover:text-blue-800 font-medium transition">
                            Pilih Semua
                        </button>
                        <span class="text-gray-300">|</span>
                        <button type="button" @click="clearAllCategory('{{ $categoryName }}')"
                            class="text-xs text-red-600 hover:text-red-800 font-medium transition">
                            Hapus Semua
                        </button>
                    </div>
                </div>

                {{-- Matrix Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full w-full">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th
                                    class="px-4 sm:px-6 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/3">
                                    Modul</th>
                                <th
                                    class="px-3 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Lihat</th>
                                <th
                                    class="px-3 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Tambah</th>
                                <th
                                    class="px-3 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Edit</th>
                                <th
                                    class="px-3 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Hapus</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($availableModules as $module)
                                @php
                                    $actions = $modules[$module];
                                    $allActions = ['view', 'create', 'edit', 'delete'];
                                @endphp
                                <tr class="hover:bg-gray-50 transition" data-category="{{ $categoryName }}">
                                    <td class="px-4 sm:px-6 py-3">
                                        <span
                                            class="text-sm text-gray-700">{{ \App\Services\PermissionService::moduleLabel($module) }}</span>
                                    </td>
                                    @foreach ($allActions as $action)
                                        <td class="px-3 py-3 text-center">
                                            @if (in_array($action, $actions))
                                                <label class="inline-flex items-center justify-center cursor-pointer">
                                                    <input type="checkbox"
                                                        name="perms[{{ $module }}.{{ $action }}]"
                                                        value="1"
                                                        {{ !empty($rolePermissions[$module][$action]) ? 'checked' : '' }}
                                                        data-category="{{ $categoryName }}"
                                                        data-module="{{ $module }}"
                                                        data-action="{{ $action }}"
                                                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0 transition">
                                                </label>
                                            @else
                                                <span class="text-gray-300">—</span>
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

        {{-- Bottom Save Button --}}
        <div class="flex items-center justify-end gap-3 mt-4">
            <a href="{{ route('tenant.roles.index') }}"
                class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                Batal
            </a>
            <button type="submit"
                class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 rounded-xl transition shadow-sm shadow-blue-200">
                Simpan Izin Akses
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            function permissionMatrix() {
                return {
                    selectAllCategory(category) {
                        document.querySelectorAll(`input[type="checkbox"][data-category="${category}"]`).forEach(cb => {
                            cb.checked = true;
                        });
                    },
                    clearAllCategory(category) {
                        document.querySelectorAll(`input[type="checkbox"][data-category="${category}"]`).forEach(cb => {
                            cb.checked = false;
                        });
                    }
                };
            }
        </script>
    @endpush
</x-app-layout>
