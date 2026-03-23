<x-app-layout>
    <x-slot name="header">
        Batch / Lot — {{ $product->name }}
        <span class="text-sm font-normal text-gray-400 ml-2">{{ $product->sku }}</span>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-5">

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Info produk --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5 flex items-center gap-4">
            <div class="w-10 h-10 bg-blue-500/20 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="font-semibold text-gray-900 dark:text-white">{{ $product->name }}</p>
                <p class="text-xs text-gray-500 dark:text-slate-400">
                    Alert expired: <strong class="text-yellow-400">{{ $product->expiry_alert_days }} hari sebelum</strong>
                    &bull; Total stok: {{ $product->totalStock() }} {{ $product->unit }}
                </p>
            </div>
            <a href="{{ route('inventory.index') }}"
                class="text-sm text-gray-400 hover:text-white transition">← Kembali</a>
        </div>

        {{-- Tabel batch --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-white">Daftar Batch</h2>
                <span class="text-xs text-gray-400">{{ $batches->total() }} batch</span>
            </div>

            @if($batches->isEmpty())
                <div class="px-6 py-12 text-center text-gray-400 dark:text-slate-500 text-sm">
                    Belum ada batch. Tambah stok dengan mengisi nomor batch dan tanggal expired.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-white/5 text-xs text-gray-500 dark:text-slate-400">
                                <th class="px-4 py-3 text-left">No. Batch</th>
                                <th class="px-4 py-3 text-left">Gudang</th>
                                <th class="px-4 py-3 text-right">Qty</th>
                                <th class="px-4 py-3 text-left">Tgl Produksi</th>
                                <th class="px-4 py-3 text-left">Tgl Expired</th>
                                <th class="px-4 py-3 text-left">Sisa Hari</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                            @foreach($batches as $batch)
                                @php
                                    $days = $batch->daysUntilExpiry();
                                    $alertDays = $product->expiry_alert_days;
                                    $rowClass = match(true) {
                                        $batch->status !== 'active'   => 'opacity-50',
                                        $days < 0                     => 'bg-red-500/5',
                                        $days <= $alertDays           => 'bg-yellow-500/5',
                                        default                       => '',
                                    };
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td class="px-4 py-3 font-mono text-xs text-gray-700 dark:text-slate-300">
                                        {{ $batch->batch_number }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-slate-400">
                                        {{ $batch->warehouse->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">
                                        {{ number_format($batch->quantity) }} {{ $product->unit }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-slate-400 text-xs">
                                        {{ $batch->manufacture_date?->format('d/m/Y') ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs font-medium
                                        {{ $days < 0 ? 'text-red-400' : ($days <= $alertDays ? 'text-yellow-400' : 'text-gray-600 dark:text-slate-300') }}">
                                        {{ $batch->expiry_date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        @if($batch->status !== 'active')
                                            <span class="text-gray-400">—</span>
                                        @elseif($days < 0)
                                            <span class="text-red-400 font-semibold">Expired {{ abs($days) }}h lalu</span>
                                        @elseif($days === 0)
                                            <span class="text-red-400 font-semibold">Expired hari ini</span>
                                        @elseif($days <= $alertDays)
                                            <span class="text-yellow-400 font-semibold">{{ $days }} hari lagi</span>
                                        @else
                                            <span class="text-green-400">{{ $days }} hari</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $statusColors = [
                                                'active'   => 'bg-green-500/20 text-green-400',
                                                'expired'  => 'bg-red-500/20 text-red-400',
                                                'recalled' => 'bg-orange-500/20 text-orange-400',
                                                'consumed' => 'bg-gray-500/20 text-gray-400',
                                            ];
                                            $statusLabels = [
                                                'active'   => 'Aktif',
                                                'expired'  => 'Expired',
                                                'recalled' => 'Ditarik',
                                                'consumed' => 'Habis',
                                            ];
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full text-xs {{ $statusColors[$batch->status] ?? '' }}">
                                            {{ $statusLabels[$batch->status] ?? $batch->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($batch->status === 'active')
                                            <form method="POST" action="{{ route('inventory.batches.status', $batch) }}">
                                                @csrf @method('PATCH')
                                                <select name="status" onchange="this.form.submit()"
                                                    class="text-xs bg-gray-100 dark:bg-white/10 border-0 rounded-lg px-2 py-1 text-gray-700 dark:text-slate-300 cursor-pointer">
                                                    <option value="">Ubah status...</option>
                                                    <option value="consumed">Tandai Habis</option>
                                                    <option value="recalled">Tarik (Recall)</option>
                                                    <option value="expired">Tandai Expired</option>
                                                </select>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($batches->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-white/5">
                        {{ $batches->links() }}
                    </div>
                @endif
            @endif
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap gap-3 text-xs text-gray-500 dark:text-slate-400">
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-red-500/20 inline-block"></span> Expired / akan expired hari ini
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-yellow-500/20 inline-block"></span> Dalam window alert ({{ $product->expiry_alert_days }} hari)
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-green-500/20 inline-block"></span> Aman
            </span>
        </div>

    </div>
</x-app-layout>
