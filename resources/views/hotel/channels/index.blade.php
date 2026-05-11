<x-app-layout>
    <x-slot name="header">Channel Manager</x-slot>

    @php
        $channelInfo = [
            'bookingcom' => ['name' => 'Booking.com', 'icon' => 'B', 'color' => 'bg-blue-600'],
            'agoda' => ['name' => 'Agoda', 'icon' => 'A', 'color' => 'bg-orange-500'],
            'expedia' => ['name' => 'Expedia', 'icon' => 'E', 'color' => 'bg-yellow-500'],
            'airbnb' => ['name' => 'Airbnb', 'icon' => 'Air', 'color' => 'bg-rose-500'],
            'tripadvisor' => ['name' => 'TripAdvisor', 'icon' => 'TA', 'color' => 'bg-green-600'],
            'direct' => ['name' => 'Direct Booking', 'icon' => 'DB', 'color' => 'bg-purple-600'],
        ];

        $recentLogs = \App\Models\ChannelManagerLog::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
    @endphp

    <div x-data="channelManager()" class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Channel Manager</h1>
                <p class="text-sm text-gray-500">Manage OTA channels and sync settings</p>
            </div>
            <a href="{{ route('hotel.channels.logs') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                View All Logs
            </a>
        </div>

        {{-- Channel Cards Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($channels as $channel)
                @php
                    $config = $configs->get($channel);
                    $info = $channelInfo[$channel] ?? [
                        'name' => ucfirst($channel),
                        'icon' => '?',
                        'color' => 'bg-gray-500',
                    ];
                    $isConnected = $config && $config->is_active;
                @endphp
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="{{ $info['color'] }} w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold text-sm">
                                {{ $info['icon'] }}
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $info['name'] }}</h3>
                                @if ($isConnected)
                                    <span class="inline-flex items-center gap-1 text-xs text-green-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                        Connected
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">Not Configured</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($config)
                        <div class="space-y-2 mb-4 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Property ID</span>
                                <span class="text-gray-900 font-mono">{{ $config->property_id ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Last Synced</span>
                                <span
                                    class="text-gray-900">{{ $config->last_synced_at?->diffForHumans() ?? 'Never' }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="flex gap-2">
                        <a href="{{ route('hotel.channels.configure', $channel) }}"
                            class="flex-1 px-3 py-2 text-sm text-center border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                            Configure
                        </a>
                        @if ($isConnected)
                            <button @click="syncChannel('{{ $channel }}')"
                                :disabled="syncing === '{{ $channel }}'"
                                class="flex-1 px-3 py-2 text-sm text-center bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white rounded-xl flex items-center justify-center gap-2">
                                <svg x-show="syncing !== '{{ $channel }}'" class="w-4 h-4" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <svg x-show="syncing === '{{ $channel }}'" class="w-4 h-4 animate-spin"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span x-text="syncing === '{{ $channel }}' ? 'Syncing...' : 'Sync Now'"></span>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Recent Sync Activity --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Recent Sync Activity</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Channel</th>
                            <th class="px-4 py-3 text-left">Action</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-left">Timestamp</th>
                            <th class="px-4 py-3 text-left">Error</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentLogs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <span
                                        class="font-medium text-gray-900">{{ $channelInfo[$log->channel]['name'] ?? ucfirst($log->channel) }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-500">
                                    {{ str_replace('_', ' ', ucfirst($log->action)) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($log->status === 'success')
                                        <span
                                            class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-600">Success</span>
                                    @elseif($log->status === 'failed')
                                        <span
                                            class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-600">Failed</span>
                                    @else
                                        <span
                                            class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-600">Partial</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500">
                                    {{ $log->created_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($log->error_message)
                                        <span class="text-xs text-red-500" title="{{ $log->error_message }}">
                                            {{ Str::limit($log->error_message, 30) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                    No sync activity yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Alpine.js Component --}}
    <script>
        window.channelManager = function() {
            return {
                syncing: null,

                async syncChannel(channel) {
                    this.syncing = channel;

                    try {
                        const response = await fetch('{{ url('hotel/channels') }}/' + channel + '/sync', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                        });

                        const data = await response.json();

                        if (data.success) {
                            Dialog.success(channel + ' synced successfully!');
                            window.location.reload();
                        } else {
                            Dialog.warning('Sync failed: ' + (data.message || 'Unknown error'));
                        }
                    } catch (error) {
                        Dialog.warning('Sync failed: ' + error.message);
                    } finally {
                        this.syncing = null;
                    }
                },
            }
        };
    </script>
</x-app-layout>
