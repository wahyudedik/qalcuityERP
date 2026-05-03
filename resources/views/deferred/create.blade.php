<x-app-layout>
    <x-slot name="header">Buat Deferred Item Baru</x-slot>

    <div class="max-w-2xl">
        @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('deferred.store') }}" class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tipe *</label>
                <select name="type" required id="type-select" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Tipe --</option>
                    <option value="deferred_revenue" {{ old('type') === 'deferred_revenue' ? 'selected' : '' }}>Pendapatan Diterima di Muka (Deferred Revenue)</option>
                    <option value="prepaid_expense" {{ old('type') === 'prepaid_expense' ? 'selected' : '' }}>Biaya Dibayar di Muka (Prepaid Expense)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi *</label>
                <input type="text" name="description" value="{{ old('description') }}" required placeholder="Contoh: Sewa dibayar di muka Jan-Des 2026"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Total Jumlah (Rp) *</label>
                <input type="number" name="total_amount" value="{{ old('total_amount') }}" required min="1" step="1000"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Mulai *</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" required
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Selesai *</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" required
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Akun Deferred (Neraca) *</label>
                <p id="hint-deferred" class="text-xs text-gray-400 mb-1">Untuk Deferred Revenue: pilih akun Kewajiban (misal: Pendapatan Diterima di Muka). Untuk Prepaid: pilih akun Aset (misal: Biaya Dibayar di Muka).</p>
                <select name="deferred_account_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Akun --</option>
                    @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}" {{ old('deferred_account_id') == $acc->id ? 'selected' : '' }}>
                        {{ $acc->code }} - {{ $acc->name }} ({{ $acc->getTypeLabel() }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Akun Pengakuan (Laba Rugi) *</label>
                <p class="text-xs text-gray-400 mb-1">Untuk Deferred Revenue: akun Pendapatan. Untuk Prepaid: akun Beban.</p>
                <select name="recognition_account_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Akun --</option>
                    @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}" {{ old('recognition_account_id') == $acc->id ? 'selected' : '' }}>
                        {{ $acc->code }} - {{ $acc->name }} ({{ $acc->getTypeLabel() }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nomor Referensi (opsional)</label>
                <input type="text" name="reference_number" value="{{ old('reference_number') }}" placeholder="No. Invoice / Kontrak"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('deferred.index') }}" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</a>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat & Generate Jadwal</button>
            </div>
        </form>
    </div>
</x-app-layout>
