<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Printing Job Dashboard</h1>
            <a href="{{ route('printing.create') }}"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium whitespace-nowrap">
                + New Print Job
            </a>
        </div>
    </x-slot>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Jobs</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total_jobs'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Active Jobs</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $stats['active_jobs'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Completed Today</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $stats['completed_today'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-red-200 dark:border-red-500/30 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Overdue Jobs</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $stats['overdue_jobs'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-orange-200 dark:border-orange-500/30 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Urgent Jobs</p>
            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400 mt-1">{{ $stats['urgent_jobs'] }}</p>
        </div>
    </div>

    {{-- Overdue Jobs Alert --}}
    @if ($overdue->count() > 0)
        <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-xl p-4 mb-6">
            <h3 class="text-sm font-semibold text-red-800 dark:text-red-400 mb-2">Overdue Jobs
                ({{ $overdue->count() }})</h3>
            <div class="space-y-2">
                @foreach ($overdue as $job)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-red-700 dark:text-red-300">{{ $job->job_number }} -
                            {{ $job->job_name }}</span>
                        <span class="text-red-600 dark:text-red-400">Due: {{ $job->due_date->format('d M Y') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Job Queue Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Active Print Jobs</h3>
            <div class="flex gap-2">
                <select
                    class="text-sm border border-gray-300 dark:border-gray-600 rounded px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    <option value="">All Status</option>
                    <option value="queued">Queued</option>
                    <option value="prepress">Pre-Press</option>
                    <option value="on_press">On Press</option>
                    <option value="finishing">Finishing</option>
                </select>
                <select
                    class="text-sm border border-gray-300 dark:border-gray-600 rounded px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    <option value="">All Priority</option>
                    <option value="urgent">Urgent</option>
                    <option value="high">High</option>
                    <option value="normal">Normal</option>
                    <option value="low">Low</option>
                </select>
            </div>
        </div>

        @if ($jobs->count() === 0)
            <x-empty-state icon="document" title="Belum ada print job"
                message="Belum ada print job aktif. Buat print job pertama Anda." actionText="Buat Print Job"
                actionUrl="{{ route('printing.create') }}" />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-[#0f172a]">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Job Number</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Customer</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Product</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Qty</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Status</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Priority</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Due Date</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @foreach ($jobs as $job)
                            <tr class="hover:bg-gray-50 dark:hover:bg-[#0f172a] transition">
                                <td class="px-6 py-4">
                                    <a href="{{ route('printing.show', $job) }}"
                                        class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                        {{ $job->job_number }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                    {{ $job->customer?->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                    {{ ucfirst(str_replace('_', ' ', $job->product_type)) }}
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                    {{ number_format($job->quantity) }}
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusColors = [
                                            'queued' => 'gray',
                                            'prepress' => 'blue',
                                            'platemaking' => 'indigo',
                                            'on_press' => 'purple',
                                            'finishing' => 'orange',
                                            'quality_check' => 'yellow',
                                            'completed' => 'green',
                                        ];
                                        $color = $statusColors[$job->status] ?? 'gray';
                                    @endphp
                                    <span
                                        class="px-2 py-1 text-xs rounded-full bg-{{ $color }}-100 text-{{ $color }}-700 dark:bg-{{ $color }}-500/20 dark:text-{{ $color }}-400">
                                        {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $priorityColors = [
                                            'low' => 'gray',
                                            'normal' => 'blue',
                                            'high' => 'orange',
                                            'urgent' => 'red',
                                        ];
                                        $pColor = $priorityColors[$job->priority] ?? 'blue';
                                    @endphp
                                    <span
                                        class="px-2 py-1 text-xs rounded-full bg-{{ $pColor }}-100 text-{{ $pColor }}-700 dark:bg-{{ $pColor }}-500/20 dark:text-{{ $pColor }}-400">
                                        {{ ucfirst($job->priority) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($job->due_date)
                                        @if ($job->due_date->isPast())
                                            <span
                                                class="text-red-600 dark:text-red-400 font-medium">{{ $job->due_date->format('d M Y') }}</span>
                                        @else
                                            <span
                                                class="text-gray-700 dark:text-slate-300">{{ $job->due_date->format('d M Y') }}</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <a href="{{ route('printing.show', $job) }}"
                                            class="text-indigo-600 dark:text-indigo-400 hover:underline text-xs">View</a>
                                        @if ($job->status === 'queued')
                                            <form action="{{ route('printing.status', $job) }}" method="POST"
                                                class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="prepress">
                                                <button type="submit"
                                                    class="text-blue-600 dark:text-blue-400 hover:underline text-xs">Start</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10">
                {{ $jobs->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
