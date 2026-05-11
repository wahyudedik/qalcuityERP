<x-app-layout>
    <x-slot name="header">Rekening Bank</x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Rekening</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalAccounts }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Rekening Aktif</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $activeAccounts }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200 col-span-2 sm:col-span-1">
            <p class="text-xs text-gray-500">Total Saldo</p>
            <p class="text-xl font-bold text-blue-600 mt-1">Rp {{ number_format($totalBalance, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="bg-white rounded-2xl border border-gray-200 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama bank / nomor rekening..."
                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Status</option>
                    <option value="active"   @selected(request('status')==='active')>Aktif</option>
                    <option value="inactive" @selected(request('status')==='inactive')>Nonaktif</option>
                </select>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
            </form>
            <div class="flex gap-2">
                <a href="{{ route('bank.reconciliation') }}" class="px-3 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Rekonsiliasi</a>
                @canmodule('bank', 'create')
                <button onclick="document.getElementById('modal-add-bank').classList.remove('hidden')"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Rekening</button>
                @endcanmodule
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Bank</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">No. Rekening</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Atas Nama</th>
                        <th class="px-4 py-3 text-right">Saldo</th>
                        <th class="px-4 py-3 text-right hidden lg:table-cell">Mutasi</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($accounts as $acc)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $acc->bank_name }}</p>
                                    <p class="text-xs text-gray-500 sm:hidden">{{ $acc->account_number }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell font-mono text-xs text-gray-600">{{ $acc->account_number }}</td>
                        <td class="px-4 py-3 hidden md:table-cell text-gray-600">{{ $acc->account_name }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">
                            Rp {{ number_format($acc->balance, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right hidden lg:table-cell text-gray-500">
                            {{ number_format($acc->statements_count ?? 0) }} transaksi
                        </td>
                        <td class="px-4 py-3 text-center hidden sm:table-cell">
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $acc->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $acc->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                @canmodule('bank', 'edit')
                                <button onclick="openEditBank({{ $acc->id }}, @js($acc->bank_name), @js($acc->account_number), @js($acc->account_name), {{ $acc->balance }}, {{ $acc->is_active ? 'true' : 'false' }})"
                                    class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                @endcanmodule
                                @canmodule('bank', 'edit')
                                <form method="POST" action="{{ route('bank-accounts.toggle', $acc) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="p-1.5 rounded-lg {{ $acc->is_active ? 'text-yellow-500 hover:bg-yellow-50' : 'text-green-500 hover:bg-green-50' }}" title="{{ $acc->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    </button>
                                </form>
                                @endcanmodule
                                @canmodule('bank', 'delete')
                                <form method="POST" action="{{ route('bank-accounts.destroy', $acc) }}" data-confirm="Hapus rekening ini?" data-confirm-type="danger">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endcanmodule
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">Belum ada rekening bank. Klik "+ Rekening" untuk menambahkan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($accounts->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $accounts->links() }}</div>
        @endif
    </div>

    {{-- Modal Tambah --}}
    <div id="modal-add-bank" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Tambah Rekening Bank</h3>
                <button onclick="document.getElementById('modal-add-bank').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">?</button>
            </div>
            <form method="POST" action="{{ route('bank-accounts.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Bank *</label>
                    <input type="text" name="bank_name" required list="bank-list" placeholder="BCA, Mandiri, BNI..."
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <datalist id="bank-list">
                        <option value="BCA">
                        <option value="Bank Mandiri">
                        <option value="BNI">
                        <option value="BRI">
                        <option value="CIMB Niaga">
                        <option value="Danamon">
                        <option value="Permata Bank">
                        <option value="Bank Syariah Indonesia">
                        <option value="BTN">
                        <option value="Maybank">
                        <option value="OCBC NISP">
                        <option value="Panin Bank">
                    </datalist>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nomor Rekening *</label>
                    <input type="text" name="account_number" required placeholder="1234567890"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Atas Nama *</label>
                    <input type="text" name="account_name" required placeholder="PT Nama Perusahaan"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Saldo Awal (Rp)</label>
                    <input type="number" name="balance" min="0" step="1000" value="0"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-bank').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div id="modal-edit-bank" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Edit Rekening Bank</h3>
                <button onclick="document.getElementById('modal-edit-bank').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">?</button>
            </div>
            <form id="form-edit-bank" method="POST" class="p-6 space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Bank *</label>
                    <input type="text" id="edit-bank-name" name="bank_name" required list="bank-list"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nomor Rekening</label>
                    <input type="text" id="edit-account-number" disabled
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-100 text-gray-500 font-mono cursor-not-allowed">
                    <p class="text-xs text-gray-400 mt-1">Nomor rekening tidak dapat diubah.</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Atas Nama *</label>
                    <input type="text" id="edit-account-name" name="account_name" required
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Saldo (Rp)</label>
                    <input type="number" id="edit-balance" name="balance" min="0" step="1000"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Update saldo manual jika diperlukan untuk penyesuaian.</p>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-edit-bank').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openEditBank(id, bankName, accountNumber, accountName, balance, isActive) {
        const form = document.getElementById('form-edit-bank');
        form.action = '/bank-accounts/' + id;
        document.getElementById('edit-bank-name').value     = bankName;
        document.getElementById('edit-account-number').value = accountNumber;
        document.getElementById('edit-account-name').value  = accountName;
        document.getElementById('edit-balance').value       = balance;
        document.getElementById('modal-edit-bank').classList.remove('hidden');
    }
    </script>
    @endpush

</x-app-layout>
