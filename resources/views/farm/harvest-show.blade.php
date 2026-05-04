<x-app-layout>
    <x-slot name="header">{{ $harvestLog->number }}</x-slot>

    <div class="mb-4">
        <a href="{{ route('farm.harvests') }}" class="text-sm text-blue-500 hover:text-blue-600">← Daftar Panen</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- KPI --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-2xl">🌾</span>
                    <div>
                        <p class="font-bold text-gray-900">{{ $harvestLog->crop_name }} — Lahan {{ $harvestLog->plot?->code }}</p>
                        <p class="text-xs text-gray-500">{{ $harvestLog->harvest_date->format('d M Y') }} · oleh {{ $harvestLog->user?->name }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                    <div><p class="text-xs text-gray-500">Total</p><p class="text-lg font-bold text-emerald-600">{{ number_format($harvestLog->total_qty, 0) }} {{ $harvestLog->unit }}</p></div>
                    <div><p class="text-xs text-gray-500">Bersih</p><p class="text-lg font-bold text-gray-900">{{ number_format($harvestLog->netQty(), 0) }} {{ $harvestLog->unit }}</p></div>
                    <div><p class="text-xs text-gray-500">Reject</p><p class="text-lg font-bold {{ $harvestLog->reject_qty > 0 ? 'text-red-500' : 'text-gray-400' }}">{{ number_format($harvestLog->reject_qty, 0) }} ({{ $harvestLog->rejectPercent() }}%)</p></div>
                    <div><p class="text-xs text-gray-500">Biaya</p><p class="text-lg font-bold text-gray-900">Rp {{ number_format($harvestLog->totalCost(), 0, ',', '.') }}</p></div>
                    <div><p class="text-xs text-gray-500">HPP/{{ $harvestLog->unit }}</p><p class="text-lg font-bold text-gray-900">{{ $harvestLog->costPerUnit() ? 'Rp '.number_format($harvestLog->costPerUnit(), 0, ',', '.') : '-' }}</p></div>
                </div>
                @if($harvestLog->moisture_pct || $harvestLog->weather || $harvestLog->storage_location)
                <div class="flex gap-4 mt-3 text-xs text-gray-500">
                    @if($harvestLog->moisture_pct)<span>💧 Kadar air: {{ $harvestLog->moisture_pct }}%</span>@endif
                    @if($harvestLog->weather)<span>☀️ {{ $harvestLog->weather }}</span>@endif
                    @if($harvestLog->storage_location)<span>🏭 {{ $harvestLog->storage_location }}</span>@endif
                </div>
                @endif
            </div>

            {{-- Grade Breakdown --}}
            @if($harvestLog->grades->isNotEmpty())
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Breakdown Grade</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr><th class="px-4 py-2 text-left">Grade</th><th class="px-4 py-2 text-right">Jumlah</th><th class="px-4 py-2 text-right">Harga/Unit</th><th class="px-4 py-2 text-right">Subtotal</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($harvestLog->grades as $g)
                        <tr>
                            <td class="px-4 py-2 font-medium text-gray-900">{{ $g->grade }}</td>
                            <td class="px-4 py-2 text-right font-mono">{{ number_format($g->quantity, 0) }} {{ $g->unit }}</td>
                            <td class="px-4 py-2 text-right font-mono text-gray-500">{{ $g->price_per_unit > 0 ? 'Rp '.number_format($g->price_per_unit, 0, ',', '.') : '-' }}</td>
                            <td class="px-4 py-2 text-right font-mono font-medium text-emerald-600">{{ $g->subtotal() > 0 ? 'Rp '.number_format($g->subtotal(), 0, ',', '.') : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    @if($harvestLog->estimatedRevenue() > 0)
                    <tfoot class="bg-gray-50">
                        <tr><td colspan="3" class="px-4 py-2 font-bold text-gray-900">Estimasi Pendapatan</td>
                            <td class="px-4 py-2 text-right font-bold text-emerald-600">Rp {{ number_format($harvestLog->estimatedRevenue(), 0, ',', '.') }}</td></tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            @endif

            {{-- Workers --}}
            @if($harvestLog->workers->isNotEmpty())
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Pekerja Panen ({{ $harvestLog->workers->count() }})</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr><th class="px-4 py-2 text-left">Nama</th><th class="px-4 py-2 text-right">Jumlah Petik</th><th class="px-4 py-2 text-right">Upah</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($harvestLog->workers as $w)
                        <tr>
                            <td class="px-4 py-2 text-gray-900">{{ $w->worker_name }}</td>
                            <td class="px-4 py-2 text-right font-mono">{{ $w->quantity_picked > 0 ? number_format($w->quantity_picked, 0).' '.$w->unit : '-' }}</td>
                            <td class="px-4 py-2 text-right font-mono">{{ $w->wage > 0 ? 'Rp '.number_format($w->wage, 0, ',', '.') : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-900 mb-3">Info</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Lahan</span><span class="font-medium">{{ $harvestLog->plot?->code }} — {{ $harvestLog->plot?->name }}</span></div>
                    @if($harvestLog->cropCycle)<div class="flex justify-between"><span class="text-gray-500">Siklus</span><span>{{ $harvestLog->cropCycle?->number }}</span></div>@endif
                    <div class="flex justify-between"><span class="text-gray-500">Upah Panen</span><span>Rp {{ number_format($harvestLog->labor_cost, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Biaya Angkut</span><span>Rp {{ number_format($harvestLog->transport_cost, 0, ',', '.') }}</span></div>
                    @if($harvestLog->notes)<div class="pt-2 border-t border-gray-100"><p class="text-xs text-gray-500">{{ $harvestLog->notes }}</p></div>@endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
