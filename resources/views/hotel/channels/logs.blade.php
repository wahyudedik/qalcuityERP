<x-app-layout>
    <x-slot name="header">Sync Logs</x-slot>

    @php
        $channelInfo = [
            'bookingcom' => 'Booking.com',
            'agoda' => 'Agoda',
            'expedia' => 'Expedia',
            'airbnb' => 'Airbnb',
            'tripadvisor' => 'TripAdvisor',
            'direct' => 'Direct Booking',
        ];
    @endphp

    <div x-data="syncLogs()" class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Sync Logs</h1>
                <p class="text-sm text-gray-500 dark:text-slate-400">View channel synchronization history</p>
            </div>
            <a href="{{ route('hotel.channels.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Channels
            </a>
        </div>

        {{-- Filter Bar --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <select name="channel"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Channels</option>
                    @foreach ($channels as $ch)
                        <option value="{{ $ch }}" @selected(request('channel') === $ch)>
                            {{ $channelInfo[$ch] ?? ucfirst($ch) }}</option>
                    @endforeach
                </select>

                <select name="status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>

                <select name="action"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Actions</option>
                    @foreach ($actions as $act)
                        <option value="{{ $act }}" @selected(request('action') === $act)>
                            {{ str_replace('_', ' ', ucfirst($act)) }}</option>
                    @endforeach
                </select>

                <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="From"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">

                <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="To"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">

                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>

                <a href="{{ route('hotel.channels.logs') }}"
                    class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Clear</a>
            </form>
        </div>

        {{-- Logs Table --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Timestamp</th>
                            <th class="px-4 py-3 text-left">Channel</th>
                            <th class="px-4 py-3 text-left">Action</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-left">Error Message</th>
                            <th class="px-4 py-3 text-center">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ $log->created_at->format('d M Y') }}</p>
                                    <p class="text-xs text-gray-500 dark:text-slate-400">
                                        {{ $log->created_at->format('H:i:s') }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="font-medium text-gray-900 dark:text-white">{{ $channelInfo[$log->channel] ?? ucfirst($log->channel) }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-slate-400">
                                    {{ str_replace('_', ' ', ucfirst($log->action)) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($log->status === 'success')
                                        <span
                                            class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-600 dark:bg-green-500/20 dark:text-green-400">Success</span>
                                    @elseif($log->status === 'failed')
                                        <span
                                            class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400">Failed</span>
                                    @else
                                        <span
                                            class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-600 dark:bg-yellow-500/20 dark:text-yellow-400">Partial</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($log->error_message)
                                        <span class="text-xs text-red-500" title="{{ $log->error_message }}">
                                            {{ Str::limit($log->error_message, 40) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($log->request_data || $log->response_data)
                                        <button @click="showDetails({{ $log->id }})"
                                            class="text-blue-600 dark:text-blue-400 hover:underline text-xs">
                                            View
                                        </button>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">
                                    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-slate-600 mb-3" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    No logs found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($logs->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 dark:border-white/10">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Details Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @click.self="showModal = false">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-hidden"
            @click.stop>
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Log Details</h3>
                <button @click="showModal = false"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <div x-show="selectedLog.request_data" class="mb-4">
                    <h4 class="text-xs font-medium text-gray-500 dark:text-slate-400 uppercase mb-2">Request Data</h4>
                    <pre class="bg-gray-50 dark:bg-[#0f172a] p-4 rounded-xl text-xs overflow-x-auto text-gray-900 dark:text-white"><code x-text="formatJson(selectedLog.request_data)"></code></pre>
                </div>
                <div x-show="selectedLog.response_data">
                    <h4 class="text-xs font-medium text-gray-500 dark:text-slate-400 uppercase mb-2">Response Data</h4>
                    <pre class="bg-gray-50 dark:bg-[#0f172a] p-4 rounded-xl text-xs overflow-x-auto text-gray-900 dark:text-white"><code x-text="formatJson(selectedLog.response_data)"></code></pre>
                </div>
            </div>
        </div>
    </div>

    {{-- Alpine.js Component --}}
    <script>
        @php
            $logsData = $logs
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'request_data' => $log->request_data,
                        'response_data' => $log->response_data,
                    ];
                })
                ->keyBy('id');
        @endphp
        window.syncLogs = function() {
            return {
                showModal: false,
                selectedLog: {},
                logsData: {{ \Illuminate\Support\Js::from($logsData) }},

                showDetails(logId) {
                    this.selectedLog = this.logsData[logId] || {};
                    this.showModal = true;
                },

                formatJson(data) {
                    if (!data) return '';
                    try {
                        return JSON.stringify(data, null, 2);
                    } catch (e) {
                        return data;
                    }
                },
            }
        };
    </script>
</x-app-layout>
