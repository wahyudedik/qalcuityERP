<x-app-layout>
    <x-slot name="header">Audit Trail</x-slot>

    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">

        {{-- Filters --}}
        <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10">
            <form method="GET" class="flex flex-wrap gap-3">
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
                <button type="submit" class="px-4 py-2 bg-blue-600 text-gray-900 dark:text-white rounded-xl text-sm font-medium hover:bg-blue-500 transition">Filter</button>
                <a href="{{ route('audit.index') }}" class="px-4 py-2 border border-gray-200 dark:border-white/10 rounded-xl text-sm font-medium text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-white/5 transition">Reset</a>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-6 py-3 text-left">Waktu</th>
                        <th class="px-6 py-3 text-left">Pengguna</th>
                        <th class="px-6 py-3 text-left">Aksi</th>
                        <th class="px-6 py-3 text-left">Deskripsi</th>
                        <th class="px-6 py-3 text-left hidden md:table-cell">Model</th>
                        <th class="px-6 py-3 text-left hidden md:table-cell">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-6 py-3 text-gray-500 dark:text-slate-400 whitespace-nowrap">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                        <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">{{ $log->user?->name ?? 'System' }}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-0.5 bg-blue-500/20 text-blue-400 rounded-full text-xs font-medium">{{ $log->action }}</span>
                        </td>
                        <td class="px-6 py-3 text-gray-700 dark:text-slate-300 max-w-xs truncate">{{ $log->description }}</td>
                        <td class="px-6 py-3 text-gray-400 dark:text-slate-500 text-xs hidden md:table-cell">
                            @if($log->model_type)
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
</x-app-layout>
