<x-app-layout>
    <x-slot name="header">Invoice Saya</x-slot>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <select name="status"
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
                <option value="">Semua Status</option>
                @foreach (['draft' => 'Draft', 'sent' => 'Terkirim', 'partial_paid' => 'Sebagian', 'paid' => 'Lunas', 'overdue' => 'Jatuh Tempo', 'voided' => 'Void'] as $v => $l)
                    <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
            <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
        </form>
    </div>

    {{-- Invoices Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No. Invoice</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Sisa</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($invoices as $invoice)
                        @php
                            $ic = match ($invoice->status) {
                                'paid' => 'green',
                                'voided', 'cancelled' => 'red',
                                'overdue' => 'orange',
                                'partial_paid' => 'blue',
                                default => 'amber',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ $invoice->number ?? '#' . $invoice->id }}</td>
                            <td class="px-4 py-3 hidden sm:table-cell text-gray-500">
                                {{ $invoice->created_at?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right text-gray-900">Rp
                                {{ number_format($invoice->total_amount ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right hidden md:table-cell text-gray-900">Rp
                                {{ number_format($invoice->remaining_amount ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs bg-{{ $ic  }}-100 text-{{ $ic }}-700 $ic }}-500/20 $ic }}-400">{{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('customer-portal.invoices.show', $invoice) }}"
                                        class="text-blue-600 hover:underline text-xs">Detail</a>
                                    <a href="{{ route('customer-portal.invoices.download', $invoice) }}"
                                        class="text-green-600 hover:underline text-xs">PDF</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-400">Belum
                                ada invoice.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($invoices->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $invoices->links() }}</div>
        @endif
    </div>
</x-app-layout>
