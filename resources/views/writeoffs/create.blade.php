<x-app-layout>
    <x-slot name="header">Write-off {{ $type === 'receivable' ? 'Piutang' : 'Hutang' }}</x-slot>

    <div class="max-w-2xl">
        @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('writeoffs.store') }}" class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">

            <div class="p-3 rounded-xl {{ $type === 'receivable' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }} text-sm font-medium">
                Write-off {{ $type === 'receivable' ? 'Piutang → Jurnal: Dr Bad Debt Expense / Cr Piutang Usaha' : 'Hutang → Jurnal: Dr Hutang Usaha / Cr Pendapatan Lain-lain' }}
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Pilih {{ $type === 'receivable' ? 'Invoice (Piutang)' : 'Hutang (Payable)' }} *
                </label>
                <select name="reference_id" required id="ref-select" onchange="updateAmount(this)"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih --</option>
                    @foreach($items as $item)
                    @php
                        $label = $type === 'receivable'
                            ? "{$item->number} - {$item->customer->name} (Sisa: Rp " . number_format($item->remaining_amount,0,',','.') . ")"
                            : "{$item->number} - {$item->supplier->name} (Sisa: Rp " . number_format($item->remaining_amount,0,',','.') . ")";
                    @endphp
                    <option value="{{ $item->id }}" data-remaining="{{ $item->remaining_amount }}" {{ old('reference_id') == $item->id ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sisa Tagihan</label>
                    <input type="text" id="remaining-display" readonly value="-"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-100 text-gray-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah Write-off (Rp) *</label>
                    <input type="number" name="writeoff_amount" id="writeoff-amount" value="{{ old('writeoff_amount') }}" required min="0.01" step="0.01"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Alasan Write-off *</label>
                <textarea name="reason" required rows="3" placeholder="Contoh: Nilai terlalu kecil untuk ditagih, pelanggan tidak dapat dihubungi..."
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('reason') }}</textarea>
            </div>

            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-700">
                ⚠️ Write-off memerlukan persetujuan admin/manager sebelum jurnal dapat diposting.
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('writeoffs.index') }}" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</a>
                <button type="submit" class="px-4 py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700">Ajukan Write-off</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    function updateAmount(sel) {
        const opt = sel.options[sel.selectedIndex];
        const remaining = opt.dataset.remaining || 0;
        document.getElementById('remaining-display').value = 'Rp ' + parseFloat(remaining).toLocaleString('id-ID');
        document.getElementById('writeoff-amount').value = remaining;
        document.getElementById('writeoff-amount').max = remaining;
    }
    </script>
    @endpush
</x-app-layout>
