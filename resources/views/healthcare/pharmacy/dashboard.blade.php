<x-app-layout>
    <x-slot name="header">Dashboard Farmasi</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Farmasi Dashboard'],
    ]" />

    @php $tid = auth()->user()->tenant_id; @endphp

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
        @php
            $totalMedications = \App\Models\PharmacyItem::where('tenant_id', $tid)->count();
            $lowStockItems = \App\Models\PharmacyItem::where('tenant_id', $tid)
                ->whereColumn('current_stock', '<=', 'minimum_stock')
                ->count();
            $pendingPrescriptions = \App\Models\Prescription::where('tenant_id', $tid)
                ->where('status', 'pending')
                ->count();
            $completedToday = \App\Models\Prescription::where('tenant_id', $tid)
                ->where('status', 'completed')
                ->whereDate('completed_at', today())
                ->count();
            $expiredItems = \App\Models\PharmacyItem::where('tenant_id', $tid)
                ->where('expiry_date', '<', now())
                ->count();
            $totalValue = \App\Models\PharmacyItem::where('tenant_id', $tid)
                ->get()
                ->sum(function ($item) {
                    return $item->current_stock * $item->unit_price;
                });
        @endphp
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Obat</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($totalMedications) }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Stok Menipis</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $lowStockItems }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Resep Pending</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $pendingPrescriptions }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Selesai Hari Ini</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $completedToday }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Kadaluarsa</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $expiredItems }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Nilai Inventori</p>
            <p class="text-lg font-bold text-purple-600 dark:text-purple-400 mt-1">Rp
                {{ number_format($totalValue, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Pending Prescriptions Queue --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Prescriptions To Fill --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div
                class="px-6 py-4 border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Resep Menunggu</h3>
                <a href="{{ route('healthcare.pharmacy.prescriptions') }}"
                    class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Lihat Semua</a>
            </div>
            <div class="p-4 space-y-3">
                @forelse($pendingPrescriptionsList ?? [] as $prescription)
                    <div
                        class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <p class="font-bold text-gray-900 dark:text-white">
                                    {{ $prescription->patient ? $prescription->patient->full_name : '-' }}</p>
                                <p class="text-sm text-gray-600 dark:text-slate-400">
                                    {{ $prescription->prescription_number ?? '-' }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs font-bold bg-blue-500 text-white rounded-lg">Pending</span>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-slate-400 mb-2">
                            <p>Dokter: {{ $prescription->doctor ? $prescription->doctor->name : '-' }}</p>
                            <p>{{ $prescription->created_at ? \Carbon\Carbon::parse($prescription->created_at)->format('d M Y H:i') : '-' }}
                            </p>
                        </div>
                        <a href="{{ route('healthcare.pharmacy.prescriptions.show', $prescription) }}"
                            class="block w-full px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-center">
                            Proses Resep
                        </a>
                    </div>
                @empty
                    <p class="text-center text-gray-500 dark:text-slate-400 py-4">Tidak ada resep pending</p>
                @endforelse
            </div>
        </div>

        {{-- Low Stock Alerts --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div
                class="px-6 py-4 border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Stok Menipis</h3>
                <a href="{{ route('healthcare.pharmacy.inventory') }}"
                    class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Lihat Inventori</a>
            </div>
            <div class="p-4 space-y-3">
                @forelse($lowStockItemsList ?? [] as $item)
                    <div
                        class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <p class="font-bold text-gray-900 dark:text-white">{{ $item->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">{{ $item->generic_name ?? '-' }} |
                                    {{ $item->category ?? '-' }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs font-bold bg-amber-500 text-white rounded-lg">
                                {{ $item->current_stock }} {{ $item->unit }}
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-amber-500 h-2 rounded-full"
                                style="width: {{ min(100, ($item->current_stock / $item->minimum_stock) * 100) }}%">
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Minimum: {{ $item->minimum_stock }}
                            {{ $item->unit }}</p>
                    </div>
                @empty
                    <p class="text-center text-gray-500 dark:text-slate-400 py-4">Semua stok aman</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Aktivitas Terbaru</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Waktu</th>
                        <th class="px-4 py-3 text-left">Aktivitas</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Obat</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Petugas</th>
                        <th class="px-4 py-3 text-center">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($recentActivities ?? [] as $activity)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3 text-gray-600 dark:text-slate-300">
                                {{ $activity->created_at ? \Carbon\Carbon::parse($activity->created_at)->format('d M Y H:i') : '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg
                                    @if ($activity->type === 'dispensed') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
                                    @elseif($activity->type === 'received') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                                    @elseif($activity->type === 'returned') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400
                                    @else bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 @endif">
                                    {{ ucfirst($activity->type ?? '-') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white hidden md:table-cell">
                                {{ $activity->item ? $activity->item->name : '-' }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-slate-300 hidden lg:table-cell">
                                {{ $activity->user ? $activity->user->name : '-' }}</td>
                            <td class="px-4 py-3 text-center font-bold text-gray-900 dark:text-white">
                                {{ $activity->quantity ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-slate-400">
                                <p>Belum ada aktivitas</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
