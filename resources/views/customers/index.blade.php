<x-app-layout>
    <x-slot name="header">Data Customer</x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-3 mb-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Total Customer</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4 text-center">
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['active'] }}</p>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Aktif</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4 text-center">
            <p class="text-2xl font-bold text-gray-400">{{ $stats['inactive'] }}</p>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Nonaktif</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-4">
        <form method="GET" class="flex gap-2 flex-1">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari nama, perusahaan, email..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" onchange="this.form.submit()"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        @canmodule('customers', 'create')
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 whitespace-nowrap">+
            Customer</button>
        @endcanmodule
    </div>

    {{-- Table - Desktop Only --}}
    <div
        class="hidden md:block bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Perusahaan</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Telepon</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Email</th>
                        <th class="px-4 py-3 text-right hidden lg:table-cell">Credit Limit</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($customers as $c)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 {{ !$c->is_active ? 'opacity-60' : '' }}">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $c->name }}</p>
                                @if ($c->npwp)
                                    <p class="text-xs text-gray-400 dark:text-slate-500">NPWP: {{ $c->npwp }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell text-gray-500 dark:text-slate-400">
                                {{ $c->company ?? '-' }}</td>
                            <td class="px-4 py-3 hidden md:table-cell text-gray-500 dark:text-slate-400">
                                {{ $c->phone ?? '-' }}</td>
                            <td class="px-4 py-3 hidden lg:table-cell text-gray-500 dark:text-slate-400">
                                {{ $c->email ?? '-' }}</td>
                            <td class="px-4 py-3 hidden lg:table-cell text-right text-gray-500 dark:text-slate-400">
                                {{ $c->credit_limit ? 'Rp ' . number_format($c->credit_limit, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs {{ $c->is_active ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400' }}">
                                    {{ $c->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    @canmodule('customers', 'edit')
                                    <button
                                        onclick="openEdit({{ $c->id }}, {{ json_encode($c->name) }}, {{ json_encode($c->company ?? '') }}, {{ json_encode($c->phone ?? '') }}, {{ json_encode($c->email ?? '') }}, {{ json_encode($c->address ?? '') }}, {{ json_encode($c->npwp ?? '') }}, {{ $c->credit_limit ?? 0 }}, {{ $c->is_active ? 'true' : 'false' }})"
                                        class="p-1.5 rounded-lg text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/10"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <form method="POST" action="{{ route('customers.toggle', $c) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                            class="p-1.5 rounded-lg text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/10"
                                            title="{{ $c->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            @if ($c->is_active)
                                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @endif
                                        </button>
                                    </form>
                                    @endcanmodule
                                    @canmodule('customers', 'delete')
                                    <form method="POST" action="{{ route('customers.destroy', $c) }}"
                                        onsubmit="return confirm('Hapus customer {{ addslashes($c->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10"
                                            title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                    @endcanmodule
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">
                                Belum ada customer.
                                @canmodule('customers', 'create')
                                <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
                                    class="text-blue-500 hover:underline ml-1">Tambah sekarang</button>
                                @endcanmodule
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($customers->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5">{{ $customers->links() }}</div>
        @endif
    </div>

    {{-- Mobile Card View --}}
    <x-mobile-card :data="$customers" :fields="[
        ['label' => 'Perusahaan', 'key' => 'company'],
        ['label' => 'NPWP', 'key' => 'npwp'],
        ['label' => 'Telepon', 'key' => 'phone', 'type' => 'tel'],
        ['label' => 'Email', 'key' => 'email', 'type' => 'email'],
        ['label' => 'Credit Limit', 'key' => 'credit_limit', 'type' => 'currency'],
        ['label' => 'Alamat', 'key' => 'address'],
    ]" titleField="name" subtitleField="company"
        statusField="is_active" emptyMessage="Belum ada customer." :actions='function ($item) {
            $buttons = "";
            if (auth()->user()?->hasPermission('customers', 'edit')) {
                $buttons .=
                    "<button onclick=\"openEdit(" .
                    $item->id .
                    ', ' .
                    json_encode($item->name) .
                    ', ' .
                    json_encode($item->company ?? '') .
                    ', ' .
                    json_encode($item->phone ?? '') .
                    ', ' .
                    json_encode($item->email ?? '') .
                    ', ' .
                    json_encode($item->address ?? '') .
                    ', ' .
                    json_encode($item->npwp ?? '') .
                    ', ' .
                    ($item->credit_limit ?? 0) .
                    ', ' .
                    ($item->is_active ? 'true' : 'false') .
                    ")\" class=\"min-h-[44px] px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700\">Edit</button>";
            }
            if (auth()->user()?->hasPermission('customers', 'edit')) {
                $buttons .=
                    "<form method=\"POST\" action=\"" .
                    route('customers.toggle', $item) .
                    "\" class=\"inline\"><input type=\"hidden\" name=\"_token\" value=\"" .
                    csrf_token() .
                    "\"><input type=\"hidden\" name=\"_method\" value=\"PATCH\"><button type=\"submit\" class=\"min-h-[44px] px-3 py-2 text-sm " .
                    ($item->is_active ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-600 hover:bg-gray-700') .
                    " text-white rounded-lg\">" .
                    ($item->is_active ? 'Aktif' : 'Nonaktif') .
                    '</button></form>';
            }
            if (auth()->user()?->hasPermission('customers', 'delete')) {
                $buttons .=
                    "<form method=\"POST\" action=\"" .
                    route('customers.destroy', $item) .
                    "\" class=\"inline\" onsubmit=\"return confirm(\"Hapus customer " .
                    addslashes($item->name) .
                    "?\")\"><input type=\"hidden\" name=\"_token\" value=\"" .
                    csrf_token() .
                    "\"><input type=\"hidden\" name=\"_method\" value=\"DELETE\"><button type=\"submit\" class=\"min-h-[44px] px-3 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700\">Hapus</button></form>";
            }
            return $buttons;
        }' />

    {{-- Modal Tambah --}}
    <div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Tambah Customer</h3>
                <button onclick="document.getElementById('modal-add').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('customers.store') }}" class="p-6 space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama <span
                                class="text-red-400">*</span></label>
                        <input type="text" name="name" required value="{{ old('name') }}"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Perusahaan</label>
                        <input type="text" name="company" value="{{ old('company') }}"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">NPWP</label>
                        <input type="text" name="npwp" value="{{ old('npwp') }}"
                            placeholder="00.000.000.0-000.000"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Telepon</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Alamat</label>
                        <textarea name="address" rows="2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('address') }}</textarea>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Credit Limit
                            (Rp)</label>
                        <input type="number" name="credit_limit" value="{{ old('credit_limit', 0) }}"
                            min="0" step="1000000"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Isi 0 untuk tanpa batas kredit</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div id="modal-edit" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Edit Customer</h3>
                <button onclick="document.getElementById('modal-edit').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-edit" method="POST" class="p-6 space-y-3">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama <span
                                class="text-red-400">*</span></label>
                        <input type="text" id="e-name" name="name" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Perusahaan</label>
                        <input type="text" id="e-company" name="company"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">NPWP</label>
                        <input type="text" id="e-npwp" name="npwp"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Telepon</label>
                        <input type="text" id="e-phone" name="phone"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Email</label>
                        <input type="email" id="e-email" name="email"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Alamat</label>
                        <textarea id="e-address" name="address" rows="2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Credit Limit
                            (Rp)</label>
                        <input type="number" id="e-credit" name="credit_limit" min="0" step="1000000"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-center gap-2 pt-4">
                        <input type="checkbox" id="e-active" name="is_active" value="1" class="rounded">
                        <label for="e-active" class="text-sm text-gray-700 dark:text-slate-300">Customer Aktif</label>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function openEdit(id, name, company, phone, email, address, npwp, creditLimit, isActive) {
                document.getElementById('form-edit').action = '/customers/' + id;
                document.getElementById('e-name').value = name;
                document.getElementById('e-company').value = company;
                document.getElementById('e-phone').value = phone;
                document.getElementById('e-email').value = email;
                document.getElementById('e-address').value = address;
                document.getElementById('e-npwp').value = npwp;
                document.getElementById('e-credit').value = creditLimit;
                document.getElementById('e-active').checked = isActive;
                document.getElementById('modal-edit').classList.remove('hidden');
            }
        </script>
    @endpush
</x-app-layout>
