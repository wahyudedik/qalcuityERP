<x-app-layout>
    <x-slot name="header">Audit Trail</x-slot>

    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">

        {{-- Filters --}}
        <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-gray-700 dark:text-slate-300">Filter:</span>
                    {{-- AI filter toggle --}}
                    <a href="{{ request()->fullUrlWithQuery(['is_ai' => '1', 'page' => null]) }}"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition
                            {{ request('is_ai') === '1' ? 'bg-purple-600 text-white' : 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 hover:bg-purple-200 dark:hover:bg-purple-900/50' }}">
                        🤖 AI Actions
                        @if($aiCount > 0)
                            <span class="bg-purple-200 dark:bg-purple-800 text-purple-800 dark:text-purple-200 px-1.5 py-0.5 rounded-full text-xs">{{ $aiCount }} hari ini</span>
                        @endif
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['is_ai' => '0', 'page' => null]) }}"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition
                            {{ request('is_ai') === '0' ? 'bg-gray-600 text-white' : 'bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-slate-300 hover:bg-gray-200 dark:hover:bg-white/15' }}">
                        👤 Manual
                    </a>
                </div>
            </div>
            <form method="GET" class="flex flex-wrap gap-3">
                @if(request('is_ai') !== null)
                    <input type="hidden" name="is_ai" value="{{ request('is_ai') }}">
                @endif
                <select name="action" class="bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                    <option value="">Semua Aksi</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ $action }}</option>
                    @endforeach
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="w-full sm:w-auto bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="w-full sm:w-auto bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-500 transition">Filter</button>
                <a href="{{ route('audit.index') }}" class="px-4 py-2 border border-gray-200 dark:border-white/10 rounded-xl text-sm font-medium text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-white/5 transition">Reset</a>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-6 py-3 text-left">Waktu</th>
                        <th class="px-6 py-3 text-left">Dilakukan Oleh</th>
                        <th class="px-6 py-3 text-left">Aksi</th>
                        <th class="px-6 py-3 text-left">Deskripsi</th>
                        <th class="px-6 py-3 text-left hidden md:table-cell">Detail</th>
                        <th class="px-6 py-3 text-left hidden md:table-cell">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 {{ $log->is_ai_action ? 'bg-purple-50/30 dark:bg-purple-900/5' : '' }}">
                        <td class="px-6 py-3 text-gray-500 dark:text-slate-400 whitespace-nowrap text-xs">
                            {{ $log->created_at->format('d M Y') }}<br>
                            <span class="text-gray-400 dark:text-slate-500">{{ $log->created_at->format('H:i:s') }}</span>
                        </td>
                        <td class="px-6 py-3">
                            @if($log->is_ai_action)
                                <div class="flex items-center gap-1.5">
                                    <span class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center text-sm">🤖</span>
                                    <div>
                                        <div class="font-medium text-purple-700 dark:text-purple-300 text-xs">Qalcuity AI</div>
                                        <div class="text-gray-400 dark:text-slate-500 text-xs">atas nama {{ $log->user?->name ?? '—' }}</div>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center gap-1.5">
                                    <span class="w-6 h-6 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-sm">👤</span>
                                    <span class="font-medium text-gray-900 dark:text-white text-xs">{{ $log->user?->name ?? 'System' }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            @if($log->is_ai_action)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 rounded-full text-xs font-medium">
                                    🤖 {{ $log->ai_tool_name ?? $log->action }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 bg-blue-500/20 text-blue-600 dark:text-blue-400 rounded-full text-xs font-medium">{{ $log->action }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-gray-700 dark:text-slate-300 max-w-xs">
                            <span class="line-clamp-2 text-xs">{{ $log->description }}</span>
                        </td>
                        <td class="px-6 py-3 text-gray-400 dark:text-slate-500 text-xs hidden md:table-cell">
                            @if($log->is_ai_action && $log->old_values)
                                <button onclick="toggleDetail({{ $log->id }})"
                                    class="text-purple-500 hover:text-purple-700 dark:hover:text-purple-300 underline text-xs">
                                    Lihat args
                                </button>
                                <div id="detail-{{ $log->id }}" class="hidden mt-1 p-2 bg-gray-100 dark:bg-white/5 rounded text-xs font-mono max-w-xs overflow-auto max-h-32">
                                    {{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                                </div>
                            @elseif($log->model_type)
                                {{ class_basename($log->model_type) }} #{{ $log->model_id }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-3 text-gray-400 dark:text-slate-500 text-xs hidden md:table-cell">{{ $log->ip_address }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-400 dark:text-slate-500">Belum ada log aktivitas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10">
            {{ $logs->withQueryString()->links() }}
        </div>
    </div>

    <script>
    function toggleDetail(id) {
        const el = document.getElementById('detail-' + id);
        el.classList.toggle('hidden');
    }
    </script>
</x-app-layout>
