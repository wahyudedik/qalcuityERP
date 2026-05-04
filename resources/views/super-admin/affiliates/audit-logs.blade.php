<x-app-layout>
    <x-slot name="header">Fraud Monitor — Affiliate Audit Log</x-slot>

    <div class="grid grid-cols-3 gap-3 mb-5">
        <div class="bg-white border border-gray-200 rounded-2xl p-4">
            <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Total Log</p>
            <p class="text-2xl font-black text-gray-900">{{ $logs->total() }}</p>
        </div>
        <div class="bg-white border border-red-200 rounded-2xl p-4">
            <p class="text-[10px] text-red-500 uppercase tracking-wider mb-1">🚨 Fraud Alerts</p>
            <p class="text-2xl font-black text-red-500">{{ $fraudCount }}</p>
        </div>
        <div class="bg-white border border-amber-200 rounded-2xl p-4">
            <p class="text-[10px] text-amber-500 uppercase tracking-wider mb-1">Warnings</p>
            <p class="text-2xl font-black text-amber-500">{{ $warningCount }}</p>
        </div>
    </div>

    <div class="flex gap-2 mb-4">
        @foreach (['' => 'Semua', 'info' => 'Info', 'warning' => 'Warning', 'fraud' => 'Fraud'] as $v => $l)
            @php
                $c = match ($v) {
                    'fraud' => 'bg-red-600',
                    'warning' => 'bg-amber-600',
                    default => '',
                };
            @endphp
            <a href="?severity={{ $v }}"
                class="px-3 py-1.5 text-xs rounded-xl {{ request('severity') === $v ? ($c ?: 'bg-blue-600') . ' text-white' : 'bg-gray-100 border border-gray-200 text-gray-600 hover:bg-gray-200' }}">{{ $l }}</a>
        @endforeach
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-center">Severity</th>
                        <th class="px-4 py-3 text-left">Affiliate</th>
                        <th class="px-4 py-3 text-left">Event</th>
                        <th class="px-4 py-3 text-left">Deskripsi</th>
                        <th class="px-4 py-3 text-left">IP</th>
                        <th class="px-4 py-3 text-center">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                        @php
                            $sc = match ($log->severity) {
                                'fraud' => 'red',
                                'warning' => 'amber',
                                default => 'blue',
                            };
                            $icon = match ($log->severity) {
                                'fraud' => '!',
                                'warning' => '!',
                                default => 'i',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 {{ $log->severity === 'fraud' ? 'bg-red-50' : '' }}">
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-{{ $sc  }}-100 text-{{ $sc }}-600">{{ $icon }}
                                    {{ strtoupper($log->severity) }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-900 text-xs">{{ $log->affiliate?->user->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs font-mono">{{ $log->event }}</td>
                            <td class="px-4 py-3 text-gray-700 text-xs">{{ Str::limit($log->description, 60) }}</td>
                            <td class="px-4 py-3 text-gray-400 text-xs font-mono">{{ $log->ip_address ?? '-' }}</td>
                            <td class="px-4 py-3 text-center text-gray-400 text-xs">
                                {{ $log->created_at->format('d/m H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-400">Belum ada audit log.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $logs->links() }}</div>
        @endif
    </div>
</x-app-layout>
