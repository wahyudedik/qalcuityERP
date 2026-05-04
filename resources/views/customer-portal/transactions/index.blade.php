<x-app-layout>
    <x-slot name="header">Riwayat Transaksi</x-slot>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Metode</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Referensi</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payments as $payment)
                        @php $pc = ($payment->status ?? 'pending') === 'confirmed' ? 'green' : (($payment->status ?? '') === 'failed' ? 'red' : 'amber'); @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-900">
                                {{ $payment->payment_date?->format('d/m/Y') ?? $payment->created_at?->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell text-gray-700">
                                {{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? '-')) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">Rp
                                {{ number_format($payment->amount ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 hidden md:table-cell text-gray-500">
                                {{ $payment->reference ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs bg-{{ $pc  }}-100 text-{{ $pc }}-700 $pc }}-500/20 $pc }}-400">{{ ucfirst($payment->status ?? 'pending') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-gray-400">Belum
                                ada transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($payments->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $payments->links() }}</div>
        @endif
    </div>
</x-app-layout>
