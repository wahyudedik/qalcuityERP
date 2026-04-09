<x-app-layout>
    <x-slot name="header">Manajemen Supplies Medis</x-slot>

    @php $tid = auth()->user()->tenant_id; @endphp

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-4 mb-6">
        @php
            $totalSupplies = \App\Models\MedicalSupply::where('tenant_id', $tid)->count();
            $lowStockSupplies = \App\Models\MedicalSupply::where('tenant_id', $tid)
                ->whereColumn('current_stock', '<=', 'reorder_level')
                ->count();
            $outOfStock = \App\Models\MedicalSupply::where('tenant_id', $tid)->where('current_stock', 0)->count();
            $criticalItems = \App\Models\MedicalSupply::where('tenant_id', $tid)
                ->where('is_critical', true)
                ->whereColumn('current_stock', '<=', 'reorder_level')
                ->count();
            $totalValue = \App\Models\MedicalSupply::where('tenant_id', $tid)
                ->get()
                ->sum(fn($item) => $item->current_stock * $item->unit_cost);
        @endphp
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Supplies</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($totalSupplies) }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Stok Menipis</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $lowStockSupplies }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Habis</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $outOfStock }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Critical Items</p>
            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400 mt-1">{{ $criticalItems }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Nilai Inventori</p>
            <p class="text-lg font-bold text-blue-600 dark:text-blue-400 mt-1">Rp
                {{ number_format($totalValue, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 mb-4">
        <div class="p-4">
            <form method="GET" class="flex flex-col lg:flex-row gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari supply / SKU..."
                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="category"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">Semua Kategori</option>
                    <option value="surgical" @selected(request('category') === 'surgical')>Surgical</option>
                    <option value="diagnostic" @selected(request('category') === 'diagnostic')>Diagnostic</option>
                    <option value="consumables" @selected(request('category') === 'consumables')>Consumables</option>
                    <option value="medications" @selected(request('category') === 'medications')>Medications</option>
                    <option value="equipment" @selected(request('category') === 'equipment')>Equipment</option>
                </select>
                <select name="stock_status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="available" @selected(request('stock_status') === 'available')>Available</option>
                    <option value="low" @selected(request('stock_status') === 'low')>Low Stock</option>
                    <option value="out" @selected(request('stock_status') === 'out')>Out of Stock</option>
                    <option value="critical" @selected(request('stock_status') === 'critical')>Critical</option>
                </select>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
            </form>
        </div>
    </div>

    {{-- Supplies Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">SKU</th>
                        <th class="px-4 py-3 text-left">Nama Supply</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Kategori</th>
                        <th class="px-4 py-3 text-center">Stok</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Reorder Level</th>
                        <th class="px-4 py-3 text-right hidden lg:table-cell">Unit Cost</th>
                        <th class="px-4 py-3 text-center hidden lg:table-cell">Critical</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($supplies ?? [] as $supply)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3">
                                <span
                                    class="font-mono text-xs text-gray-600 dark:text-slate-300">{{ $supply->sku ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $supply->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">{{ $supply->supplier ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                    {{ ucfirst($supply->category ?? '-') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="font-bold text-gray-900 dark:text-white">{{ $supply->current_stock }}</span>
                                <span class="text-xs text-gray-500 dark:text-slate-400">{{ $supply->unit }}</span>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                <span class="text-gray-600 dark:text-slate-300">{{ $supply->reorder_level }}</span>
                            </td>
                            <td class="px-4 py-3 text-right hidden lg:table-cell">
                                <span class="text-gray-900 dark:text-white">Rp
                                    {{ number_format($supply->unit_cost, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-4 py-3 text-center hidden lg:table-cell">
                                @if ($supply->is_critical)
                                    <svg class="w-5 h-5 mx-auto text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($supply->current_stock == 0)
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Out</span>
                                @elseif($supply->is_critical && $supply->current_stock <= $supply->reorder_level)
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">Critical</span>
                                @elseif($supply->current_stock <= $supply->reorder_level)
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Low</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Available</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('healthcare.inventory.supplies.show', $supply) }}"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30 rounded-lg"
                                        title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('healthcare.inventory.supplies.edit', $supply) }}"
                                        class="p-1.5 text-green-600 hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/30 rounded-lg"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500 dark:text-slate-400">
                                <p>Belum ada data supplies</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (isset($supplies) && $supplies->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                {{ $supplies->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
