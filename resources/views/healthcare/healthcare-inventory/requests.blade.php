<x-app-layout>
    <x-slot name="header">Permintaan Stok</x-slot>

    @php $tid = auth()->user()->tenant_id; @endphp

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @php
            $totalRequests = \App\Models\StockRequest::where('tenant_id', $tid)->count();
            $pendingRequests = \App\Models\StockRequest::where('tenant_id', $tid)->where('status', 'pending')->count();
            $approvedRequests = \App\Models\StockRequest::where('tenant_id', $tid)
                ->where('status', 'approved')
                ->count();
            $fulfilledToday = \App\Models\StockRequest::where('tenant_id', $tid)
                ->where('status', 'fulfilled')
                ->whereDate('fulfilled_at', today())
                ->count();
        @endphp
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Requests</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($totalRequests) }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Pending</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $pendingRequests }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Disetujui</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $approvedRequests }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Fulfilled Hari Ini</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $fulfilledToday }}</p>
        </div>
    </div>

    {{-- Requests Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Request ID</th>
                        <th class="px-4 py-3 text-left">Department</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Items</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Requested By</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Priority</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($requests ?? [] as $request)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3">
                                <span
                                    class="font-mono text-sm font-bold text-blue-600 dark:text-blue-400">{{ $request->request_number ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                                    {{ $request->department ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <p class="text-gray-900 dark:text-white">{{ count($request->items ?? []) }} items</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    {{ $request->items ? collect($request->items)->pluck('name')->take(2)->join(', ') : '-' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-slate-300 hidden lg:table-cell">
                                {{ $request->requestedBy ? $request->requestedBy->name : '-' }}</td>
                            <td class="px-4 py-3 hidden sm:table-cell">
                                <p class="text-gray-900 dark:text-white">
                                    {{ $request->request_date ? \Carbon\Carbon::parse($request->request_date)->format('d M Y') : '-' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    {{ $request->request_date ? \Carbon\Carbon::parse($request->request_date)->format('H:i') : '-' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                @if ($request->priority === 'urgent')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Urgent</span>
                                @elseif($request->priority === 'high')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">High</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Normal</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($request->status === 'pending')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Pending</span>
                                @elseif($request->status === 'approved')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Approved</span>
                                @elseif($request->status === 'fulfilled')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Fulfilled</span>
                                @elseif($request->status === 'rejected')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Rejected</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('healthcare.inventory.requests.show', $request) }}"
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
                                    @if ($request->status === 'pending')
                                        <button onclick="approveRequest({{ $request->id }})"
                                            class="p-1.5 text-green-600 hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/30 rounded-lg"
                                            title="Approve">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-slate-400">
                                <p>Belum ada permintaan stok</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (isset($requests) && $requests->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                {{ $requests->links() }}
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            function approveRequest(id) {
                if (confirm('Setujui permintaan ini?')) {
                    fetch(`/healthcare/inventory/requests/${id}/approve`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).then(() => location.reload());
                }
            }
        </script>
    @endpush
</x-app-layout>
