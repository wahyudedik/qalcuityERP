<x-app-layout>
    <x-slot name="header">Inventori Farmasi</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Farmasi', 'url' => route('healthcare.pharmacy.prescriptions')],
        ['label' => 'Inventori'],
    ]" />

    @php $tid = auth()->user()->tenant_id; @endphp

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-4 mb-6">
        @php
            $totalItems = \App\Models\PharmacyItem::where('tenant_id', $tid)->count();
            $lowStock = \App\Models\PharmacyItem::where('tenant_id', $tid)
                ->whereColumn('current_stock', '<=', 'minimum_stock')
                ->count();
            $outOfStock = \App\Models\PharmacyItem::where('tenant_id', $tid)->where('current_stock', 0)->count();
            $expiringSoon = \App\Models\PharmacyItem::where('tenant_id', $tid)
                ->whereBetween('expiry_date', [now(), now()->addDays(30)])
                ->count();
            $expired = \App\Models\PharmacyItem::where('tenant_id', $tid)->where('expiry_date', '<', now())->count();
        @endphp
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Item</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($totalItems) }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Stok Menipis</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $lowStock }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Habis</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $outOfStock }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Segera Kadaluarsa</p>
            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400 mt-1">{{ $expiringSoon }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Kadaluarsa</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $expired }}</p>
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 mb-4">
        <div class="p-4">
            <form method="GET" class="flex flex-col lg:flex-row gap-3">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari obat / generic name..."
                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="category"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">Semua Kategori</option>
                    <option value="tablet" @selected(request('category') === 'tablet')>Tablet</option>
                    <option value="capsule" @selected(request('category') === 'capsule')>Capsule</option>
                    <option value="syrup" @selected(request('category') === 'syrup')>Syrup</option>
                    <option value="injection" @selected(request('category') === 'injection')>Injection</option>
                    <option value="topical" @selected(request('category') === 'topical')>Topical</option>
                </select>
                <select name="stock_status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="available" @selected(request('stock_status') === 'available')>Available</option>
                    <option value="low" @selected(request('stock_status') === 'low')>Low Stock</option>
                    <option value="out" @selected(request('stock_status') === 'out')>Out of Stock</option>
                    <option value="expired" @selected(request('stock_status') === 'expired')>Expired</option>
                </select>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
                <a href="{{ route('healthcare.pharmacy.inventory') }}"
                    class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 text-center">Reset</a>
            </form>
        </div>
    </div>

    {{-- Inventory Table - Desktop & Mobile --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        {{-- Desktop Table View --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-left">Nama Obat</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Generic Name</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Kategori</th>
                        <th class="px-4 py-3 text-center">Stok</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Harga</th>
                        <th class="px-4 py-3 text-center hidden lg:table-cell">Exp Date</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($items ?? [] as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3">
                                <span
                                    class="font-mono text-xs text-gray-600 dark:text-slate-300">{{ $item->item_code ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $item->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">{{ $item->manufacturer ?? '-' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-slate-300 hidden lg:table-cell">
                                {{ $item->generic_name ?? '-' }}</td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                    {{ ucfirst($item->category ?? '-') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="font-bold text-gray-900 dark:text-white">{{ $item->current_stock }}</span>
                                <span class="text-xs text-gray-500 dark:text-slate-400">{{ $item->unit }}</span>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                <span class="text-gray-900 dark:text-white">Rp
                                    {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-4 py-3 text-center hidden lg:table-cell">
                                @if ($item->expiry_date)
                                    @php
                                        $expiryDate = \Carbon\Carbon::parse($item->expiry_date);
                                        $daysUntilExpiry = $expiryDate->diffInDays(now(), false);
                                    @endphp
                                    <span
                                        class="{{ $daysUntilExpiry < 0 ? 'text-red-600 dark:text-red-400' : ($daysUntilExpiry < 30 ? 'text-orange-600 dark:text-orange-400' : 'text-gray-600 dark:text-slate-300') }}">
                                        {{ $expiryDate->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-gray-500 dark:text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($item->current_stock == 0)
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Out</span>
                                @elseif($item->current_stock <= $item->minimum_stock)
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Low</span>
                                @elseif($item->expiry_date && \Carbon\Carbon::parse($item->expiry_date)->isPast())
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Expired</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Available</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('healthcare.pharmacy.inventory.show', $item) }}"
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
                                    <a href="{{ route('healthcare.pharmacy.inventory.edit', $item) }}"
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
                                <p>Belum ada data inventori</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card View --}}
        <div class="md:hidden divide-y divide-gray-100 dark:divide-white/5">
            @forelse($items ?? [] as $item)
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex-1 min-w-0">
                            <p class="font-mono text-xs text-gray-600 dark:text-slate-300">
                                {{ $item->item_code ?? '-' }}</p>
                            <p class="font-semibold text-gray-900 dark:text-white truncate mt-0.5">{{ $item->name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">{{ $item->manufacturer ?? '-' }}</p>
                        </div>
                        <div class="text-right">
                            @if ($item->current_stock == 0)
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Out</span>
                            @elseif($item->current_stock <= $item->minimum_stock)
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Low</span>
                            @elseif($item->expiry_date && \Carbon\Carbon::parse($item->expiry_date)->isPast())
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Expired</span>
                            @else
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Available</span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                        <div>
                            <p class="text-gray-500 dark:text-slate-400">Kategori</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ ucfirst($item->category ?? '-') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-slate-400">Stok</p>
                            <p class="font-bold text-gray-900 dark:text-white">{{ $item->current_stock }}
                                {{ $item->unit }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-slate-400">Harga</p>
                            <p class="font-medium text-gray-900 dark:text-white">Rp
                                {{ number_format($item->unit_price, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-slate-400">Exp Date</p>
                            @if ($item->expiry_date)
                                @php
                                    $expiryDate = \Carbon\Carbon::parse($item->expiry_date);
                                    $daysUntilExpiry = $expiryDate->diffInDays(now(), false);
                                @endphp
                                <p
                                    class="font-medium {{ $daysUntilExpiry < 0 ? 'text-red-600 dark:text-red-400' : ($daysUntilExpiry < 30 ? 'text-orange-600 dark:text-orange-400' : 'text-gray-900 dark:text-white') }}">
                                    {{ $expiryDate->format('d M Y') }}
                                </p>
                            @else
                                <p class="font-medium text-gray-500 dark:text-slate-400">-</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2 border-t border-gray-100 dark:border-white/5">
                        <a href="{{ route('healthcare.pharmacy.inventory.show', $item) }}"
                            class="flex-1 px-3 py-2 text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-center hover:bg-blue-100 dark:hover:bg-blue-900/30">
                            Detail
                        </a>
                        <a href="{{ route('healthcare.pharmacy.inventory.edit', $item) }}"
                            class="flex-1 px-3 py-2 text-xs font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 rounded-lg text-center hover:bg-green-100 dark:hover:bg-green-900/30">
                            Edit
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500 dark:text-slate-400">
                    <svg class="w-16 h-16 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <p>Belum ada data inventori</p>
                </div>
            @endforelse
        </div>

        @if (isset($items) && $items->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
