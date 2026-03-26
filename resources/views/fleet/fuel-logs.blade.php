<x-app-layout>
    <x-slot name="header">Log BBM</x-slot>

    {{-- Summary --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total BBM Bulan Ini</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($monthlySummary->total_cost ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Liter</p>
            <p class="text-xl font-bold text-blue-500">{{ number_format($monthlySummary->total_liters ?? 0, 1, ',', '.') }} L</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Transaksi</p>
            <p class="text-xl font-bold text-green-500">{{ $monthlySummary->count ?? 0 }}×</p>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex flex-wrap gap-2">
            <select name="vehicle_id" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Kendaraan</option>
                @foreach($vehicles as $v)<option value="{{ $v->id }}" @selected(request('vehicle_id')==$v->id)>{{ $v->plate_number }}</option>@endforeach
            </select>
            <input type="month" name="month" value="{{ request('month', now()->format('Y-m')) }}"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
        </form>
        @canmodule('fleet', 'create')
        <button onclick="document.getElementById('modal-add-fuel').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Catat BBM</button>
        @endcanmodule
    </div>

    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Kendaraan</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Jenis BBM</th>
                        <th class="px-4 py-3 text-right">Liter</th>
                        <th class="px-4 py-3 text-right">Harga/L</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Odometer</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($fuelLogs as $f)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400">{{ $f->date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $f->vehicle->plate_number ?? '-' }}</td>
                        <td class="px-4 py-3 hidden sm:table-cell text-gray-700 dark:text-slate-300">{{ ucfirst($f->fuel_type) }}</td>
                        <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ number_format($f->liters, 1, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-gray-500 dark:text-slate-400">Rp {{ number_format($f->price_per_liter, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp {{ number_format($f->total_cost, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-gray-500 dark:text-slate-400">{{ number_format($f->odometer, 0, ',', '.') }} km</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada log BBM.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($fuelLogs->hasPages())<div class="px-4 py-3 border-t border-gray-100 dark:border-white/5">{{ $fuelLogs->links() }}</div>@endif
    </div>

    {{-- Modal Add Fuel --}}
    <div id="modal-add-fuel" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Catat BBM</h3>
                <button onclick="document.getElementById('modal-add-fuel').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('fleet.fuel-logs.store') }}" class="p-6 space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kendaraan *</label>
                        <select name="vehicle_id" required class="{{ $cls }}"><option value="">-- Pilih --</option>
                            @foreach($vehicles as $v)<option value="{{ $v->id }}">{{ $v->plate_number }}</option>@endforeach
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal *</label><input type="date" name="date" required value="{{ date('Y-m-d') }}" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Odometer (km) *</label><input type="number" name="odometer" required min="0" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jenis BBM *</label>
                        <select name="fuel_type" required class="{{ $cls }}">
                            <option value="pertalite">Pertalite</option><option value="pertamax">Pertamax</option><option value="pertamax_turbo">Pertamax Turbo</option><option value="solar">Solar</option><option value="dexlite">Dexlite</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Liter *</label><input type="number" name="liters" required min="0.01" step="0.01" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Harga/Liter *</label><input type="number" name="price_per_liter" required min="0" step="100" value="13900" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">SPBU</label><input type="text" name="station" placeholder="SPBU 34.xxx" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">No. Struk</label><input type="text" name="receipt_number" class="{{ $cls }}"></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-fuel').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
