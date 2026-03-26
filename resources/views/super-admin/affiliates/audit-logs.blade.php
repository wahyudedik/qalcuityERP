<x-app-layout>
    <x-slot name="header">Fraud Monitor — Affiliate Audit Log</x-slot>

    <div class="grid grid-cols-3 gap-3 mb-5">
        <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-4">
            <p class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Total Log</p>
            <p class="text-2xl font-black text-white">{{ $logs->total() }}</p>
        </div>
        <div class="bg-[#1e293b] border border-red-500/30 rounded-2xl p-4">
            <p class="text-[10px] text-red-400 uppercase tracking-wider mb-1">🚨 Fraud Alerts</p>
            <p class="text-2xl font-black text-red-400">{{ $fraudCount }}</p>
        </div>
        <div class="bg-[#1e293b] border border-amber-500/30 rounded-2xl p-4">
            <p class="text-[10px] text-amber-400 uppercase tracking-wider mb-1">⚠ Warnings</p>
            <p class="text-2xl font-black text-amber-400">{{ $warningCount }}</p>
        </div>
    </div>

    <div class="flex gap-2 mb-4">
        @foreach([''=>'Semua','info'=>'Info','warning'=>'Warning','fraud'=>'Fraud'] as $v=>$l)
        @php $c = match($v) { 'fraud'=>'bg-red-600','warning'=>'bg-amber-600', default => '' }; @endphp
        <a href="?severity={{ $v }}" class="px-3 py-1.5 text-xs rounded-xl {{ request('severity')===$v ? ($c ?: 'bg-blue-600') . ' text-white' : 'bg-white/5 border border-white/10 text-slate-300 hover:bg-white/10' }}">{{ $l }}</a>
        @endforeach
    </div>

    <div class="bg-[#1e293b] border border-white/10 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-white/5 text-xs text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-center">Severity</th>
                        <th class="px-4 py-3 text-left">Affiliate</th>
                        <th class="px-4 py-3 text-left">Event</th>
                        <th class="px-4 py-3 text-left">Deskripsi</th>
                        <th class="px-4 py-3 text-left">IP</th>
                        <th class="px-4 py-3 text-center">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($logs as $log)
                    @php
                        $sc = match($log->severity) { 'fraud'=>'red', 'warning'=>'amber', default=>'blue' };
                        $icon = match($log->severity) { 'fraud'=>'🚨', 'warning'=>'⚠', default=>'ℹ' };
                    @endphp
                    <tr class="hover:bg-white/5 {{ $log->severity === 'fraud' ? 'bg-red-500/5' : '' }}">
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-{{ $sc }}-500/20 text-{{ $sc }}-400">{{ $icon }} {{ strtoupper($log->severity) }}</span>
                        </td>
                        <td class="px-4 py-3 text-white text-xs">{{ $log->affiliate->user->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-400 text-xs font-mono">{{ $log->event }}</td>
                        <td class="px-4 py-3 text-slate-300 text-xs">{{ Str::limit($log->description, 60) }}</td>
                        <td class="px-4 py-3 text-slate-500 text-xs font-mono">{{ $log->ip_address ?? '-' }}</td>
                        <td class="px-4 py-3 text-center text-slate-500 text-xs">{{ $log->created_at->format('d/m H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">Belum ada audit log.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())<div class="px-4 py-3 border-t border-white/5">{{ $logs->links() }}</div>@endif
    </div>
</x-app-layout>
