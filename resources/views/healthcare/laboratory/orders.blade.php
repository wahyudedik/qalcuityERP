<x-app-layout>
    <x-slot name="header">Pesanan Laboratorium</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Laboratorium'],
    ]" />

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Pesanan</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($statistics['total_orders']) }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Pending</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $statistics['pending_orders'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Diproses</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $statistics['in_progress_orders'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Selesai Hari Ini</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $statistics['completed_today'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari pasien / No. order..."
                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                </select>
                <select name="test_type"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">Semua Jenis Test</option>
                    <option value="blood_test" @selected(request('test_type') === 'blood_test')>Blood Test</option>
                    <option value="urine_test" @selected(request('test_type') === 'urine_test')>Urine Test</option>
                    <option value="cbc" @selected(request('test_type') === 'cbc')>CBC</option>
                    <option value="liver_function" @selected(request('test_type') === 'liver_function')>Liver Function</option>
                    <option value="kidney_function" @selected(request('test_type') === 'kidney_function')>Kidney Function</option>
                </select>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
            </form>
        </div>
    </div>

    {{-- Orders Table - Desktop & Mobile --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        {{-- Desktop Table View (hidden on mobile <768px) --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No. Order</th>
                        <th class="px-4 py-3 text-left">Pasien</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Jenis Test</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Dokter</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Priority</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($orders ?? [] as $order)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3">
                                <span
                                    class="font-mono text-sm font-bold text-blue-600 dark:text-blue-400">{{ $order->order_number ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $order->patient ? $order->patient->full_name : '-' }}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    {{ $order->patient ? $order->patient->medical_record_number : '-' }}</p>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                                    {{ $order->labTest?->test_name ?? $order->labTest?->category ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-slate-300 hidden lg:table-cell">
                                {{ $order->doctor ? $order->doctor->name : '-' }}</td>
                            <td class="px-4 py-3 hidden sm:table-cell">
                                <p class="text-gray-900 dark:text-white">
                                    {{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d M Y') : '-' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    {{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('H:i') : '-' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                @if ($order->priority === 'urgent')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Urgent</span>
                                @elseif($order->priority === 'high')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">High</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Normal</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($order->status === 'pending')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Pending</span>
                                @elseif($order->status === 'in_progress')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">In
                                        Progress</span>
                                @elseif($order->status === 'completed')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Completed</span>
                                @elseif($order->status === 'cancelled')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Cancelled</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('healthcare.laboratory.orders.enter-results', $order) }}"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30 rounded-lg"
                                        title="Input Hasil">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('healthcare.laboratory.orders.show', $order) }}"
                                        class="p-1.5 text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700 rounded-lg"
                                        title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-slate-400">
                                <p>Belum ada pesanan lab</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card View (< 768px) --}}
        <div class="md:hidden divide-y divide-gray-100 dark:divide-white/5">
            @forelse($orders ?? [] as $order)
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex-1 min-w-0">
                            <p class="font-mono text-sm font-bold text-blue-600 dark:text-blue-400">
                                {{ $order->order_number ?? '-' }}</p>
                            <p class="font-semibold text-gray-900 dark:text-white truncate mt-0.5">
                                {{ $order->patient ? $order->patient->full_name : '-' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">
                                {{ $order->patient ? $order->patient->medical_record_number : '-' }}
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            @if ($order->status === 'pending')
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Pending</span>
                            @elseif($order->status === 'in_progress')
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">In
                                    Progress</span>
                            @elseif($order->status === 'completed')
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Completed</span>
                            @elseif($order->status === 'cancelled')
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Cancelled</span>
                            @endif

                            @if ($order->priority === 'urgent')
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Urgent</span>
                            @elseif($order->priority === 'high')
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">High</span>
                            @else
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Normal</span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                        <div>
                            <p class="text-gray-500 dark:text-slate-400">Jenis Test</p>
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ $order->labTest?->test_name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-slate-400">Tanggal</p>
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d M Y') : '-' }}
                            </p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-gray-500 dark:text-slate-400">Dokter</p>
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ $order->doctor ? $order->doctor->name : '-' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2 border-t border-gray-100 dark:border-white/5">
                        <a href="{{ route('healthcare.laboratory.orders.enter-results', $order) }}"
                            class="flex-1 px-3 py-2 text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-center hover:bg-blue-100 dark:hover:bg-blue-900/30">
                            Input Hasil
                        </a>
                        <a href="{{ route('healthcare.laboratory.orders.show', $order) }}"
                            class="flex-1 px-3 py-2 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-lg text-center hover:bg-gray-100 dark:hover:bg-gray-700">
                            Detail
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500 dark:text-slate-400">
                    <svg class="w-16 h-16 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <p>Belum ada pesanan lab</p>
                </div>
            @endforelse
        </div>

        @if (isset($orders) && $orders->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
