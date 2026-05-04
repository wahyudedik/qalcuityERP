<x-app-layout>
    <x-slot name="title">AI Model Monitor — Qalcuity ERP</x-slot>
    <x-slot name="header">AI Model Monitor</x-slot>

    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">
            {{ session('error') }}
        </div>
    @endif

    {{-- ═══ ACTIVE MODEL CARD ═══ --}}
    @php
        $activeAvailability = collect($modelAvailability)->firstWhere('model', $activeModel);
        $activeReason = $activeAvailability['reason'] ?? null;
        $activeAvailable = $activeAvailability['available'] ?? true;

        if (!$activeAvailable && $activeReason === 'quota_exceeded') {
            $statusLabel = 'Quota Exceeded';
            $statusColor = 'text-red-400 bg-red-500/15 border-red-500/30';
            $dotColor = 'bg-red-400';
        } elseif (!$activeAvailable && $activeReason === 'rate_limit') {
            $statusLabel = 'Rate Limited';
            $statusColor = 'text-yellow-400 bg-yellow-500/15 border-yellow-500/30';
            $dotColor = 'bg-yellow-400';
        } else {
            $statusLabel = 'Available';
            $statusColor = 'text-green-400 bg-green-500/15 border-green-500/30';
            $dotColor = 'bg-green-400';
        }
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="sm:col-span-2 bg-white border border-gray-200 rounded-2xl p-5 flex items-center gap-5">
            <div
                class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Model Aktif Saat Ini</p>
                <p class="text-xl font-bold text-gray-900 font-mono truncate">{{ $activeModel }}</p>
            </div>
            <div class="shrink-0">
                <span
                    class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full border {{ $statusColor }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
                    {{ $statusLabel }}
                </span>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-5 flex flex-col justify-between">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Force Reset</p>
                <p class="text-xs text-gray-500 leading-relaxed">Reset semua cooldown model AI secara manual.</p>
            </div>
            <form method="POST" action="{{ route('super-admin.ai-model.reset') }}"
                onsubmit="return confirm('Reset semua cooldown model AI? Tindakan ini tidak dapat dibatalkan.')">
                @csrf
                <button type="submit"
                    class="mt-4 w-full px-4 py-2.5 text-sm font-semibold bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 rounded-xl transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Force Reset Semua
                </button>
            </form>
        </div>
    </div>

    {{-- ═══ MODEL AVAILABILITY TABLE ═══ --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <p class="text-sm font-semibold text-gray-900">Status Availability Model</p>
            <span class="text-xs text-gray-400">{{ count($modelAvailability) }} model dalam fallback chain</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Model</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th
                            class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">
                            Alasan</th>
                        <th
                            class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                            Estimasi Recovery</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($modelAvailability as $item)
                        @php
                            if ($item['available']) {
                                $rowStatus = 'Available';
                                $rowStatusClass = 'text-green-600 bg-green-50';
                                $rowDot = 'bg-green-500';
                            } elseif ($item['reason'] === 'quota_exceeded') {
                                $rowStatus = 'Quota Exceeded';
                                $rowStatusClass = 'text-red-600 bg-red-50';
                                $rowDot = 'bg-red-500';
                            } else {
                                $rowStatus = 'Rate Limited';
                                $rowStatusClass = 'text-yellow-600 bg-yellow-50';
                                $rowDot = 'bg-yellow-500';
                            }
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-gray-700 text-xs">{{ $item['model'] }}</span>
                                    @if ($item['model'] === $activeModel)
                                        <span
                                            class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-600 border border-blue-200">AKTIF</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span
                                    class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $rowStatusClass }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $rowDot }}"></span>
                                    {{ $rowStatus }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 hidden md:table-cell">
                                @if ($item['reason'])
                                    <span class="text-xs text-gray-500 font-mono">{{ $item['reason'] }}</span>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 hidden lg:table-cell">
                                @if ($item['recovers_at'])
                                    <div>
                                        <p class="text-xs text-gray-700">
                                            {{ $item['recovers_at']->format('d M Y H:i:s') }}</p>
                                        <p class="text-[10px] text-gray-400 mt-0.5">
                                            {{ $item['recovers_at']->diffForHumans() }}</p>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-400 text-sm">
                                Tidak ada model dalam fallback chain.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ═══ SWITCH LOG TABLE ═══ --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <p class="text-sm font-semibold text-gray-900">Riwayat Switch Event</p>
            <span class="text-xs text-gray-400">{{ $switchLogs->total() }} total event</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Dari Model</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ke
                            Model</th>
                        <th
                            class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">
                            Alasan</th>
                        <th
                            class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                            Waktu Switch</th>
                        <th
                            class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden xl:table-cell">
                            Tenant ID</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($switchLogs as $log)
                        @php
                            $reasonColors = [
                                'rate_limit' => 'text-yellow-600 bg-yellow-50',
                                'quota_exceeded' => 'text-red-600 bg-red-50',
                                'service_unavailable' => 'text-orange-600 bg-orange-50',
                                'recovery' => 'text-green-600 bg-green-50',
                            ];
                            $reasonColor = $reasonColors[$log->reason] ?? 'text-gray-600 bg-gray-100';
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-gray-600 text-xs">{{ $log->from_model }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                    <span class="font-mono text-gray-900 text-xs">{{ $log->to_model }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 hidden md:table-cell">
                                <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $reasonColor }}">
                                    {{ str_replace('_', ' ', ucfirst($log->reason)) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 hidden lg:table-cell">
                                <p class="text-xs text-gray-700">{{ $log->switched_at->format('d M Y') }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ $log->switched_at->format('H:i:s') }}
                                </p>
                            </td>
                            <td class="px-5 py-3.5 hidden xl:table-cell">
                                @if ($log->triggered_by_tenant_id)
                                    <span
                                        class="text-xs font-mono text-gray-500">{{ $log->triggered_by_tenant_id }}</span>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-400 text-sm">
                                Belum ada switch event yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($switchLogs->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $switchLogs->links() }}
            </div>
        @endif
    </div>

</x-app-layout>
