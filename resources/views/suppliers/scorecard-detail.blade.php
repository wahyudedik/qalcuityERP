<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>📊 {{ $report['supplier']->name }} - Performance Report</span>
            <a href="{{ route('suppliers.scorecards.index') }}"
                class="px-3 py-1.5 text-sm bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                ← Kembali
            </a>
        </div>
    </x-slot>

    {{-- Current Rating Card --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $report['supplier']->name }}</h2>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">{{ $report['supplier']->code ?? '' }} |
                    {{ $report['supplier']->email ?? '' }}</p>
            </div>
            @php
                $ratingColors = ['A' => 'green', 'B' => 'blue', 'C' => 'yellow', 'D' => 'orange', 'F' => 'red'];
                $color = $ratingColors[$report['current_rating']] ?? 'gray';
            @endphp
            <div class="text-right">
                <span
                    class="inline-block px-4 py-2 text-2xl font-bold rounded-full bg-{{ $color }}-100 text-{{ $color }}-700 dark:bg-{{ $color }}-500/20 dark:text-{{ $color }}-400">
                    {{ $report['current_rating'] }}
                </span>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-2">Current Rating</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-gray-100 dark:border-white/5">
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Overall Score</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($report['current_score'], 1) }}/100</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Trend</p>
                <p
                    class="text-lg font-medium {{ $report['trend'] === 'improving' ? 'text-green-600' : ($report['trend'] === 'declining' ? 'text-red-600' : 'text-yellow-600') }}">
                    {{ $report['trend'] === 'improving' ? '📈 Improving' : ($report['trend'] === 'declining' ? '📉 Declining' : '➡️ Stable') }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Total Incidents</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $report['total_incidents'] }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Scorecards</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ count($report['scorecards']) }}</p>
            </div>
        </div>
    </div>

    {{-- Performance Trend Chart --}}
    @if (count($report['scorecards']) > 1)
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Performance Trend
                ({{ count($report['scorecards']) }} Months)</h2>

            <div class="h-64 flex items-end gap-2">
                @php
                    $maxScore = $report['scorecards']->max('overall_score') ?: 100;
                @endphp
                @foreach ($report['scorecards'] as $scorecard)
                    @php
                        $height = ($scorecard->overall_score / $maxScore) * 100;
                        $ratingColor = $ratingColors[$scorecard->rating] ?? 'gray';
                    @endphp
                    <div class="flex-1 min-w-[40px] flex flex-col items-center group">
                        <div class="w-full bg-{{ $ratingColor }}-500 hover:bg-{{ $ratingColor }}-600 rounded-t transition-all relative"
                            style="height: {{ max($height, 5) }}px">
                            <div
                                class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap z-10">
                                {{ number_format($scorecard->overall_score, 1) }} - {{ $scorecard->rating }}
                            </div>
                        </div>
                        <span class="text-[10px] text-gray-500 dark:text-slate-400 mt-2 rotate-45 origin-left">
                            {{ $scorecard->period_end->format('M/y') }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Score History Table --}}
    <div
        class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Score History</h2>
        </div>

        @if (count($report['scorecards']) === 0)
            <div class="p-12 text-center">
                <p class="text-4xl mb-3">📊</p>
                <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada scorecard untuk supplier ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-[#0f172a]">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Period</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Overall</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Rating</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Quality</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Delivery</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Cost</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Service</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @foreach ($report['scorecards'] as $scorecard)
                            <tr class="hover:bg-gray-50 dark:hover:bg-[#0f172a] transition">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-white">
                                    {{ $scorecard->period_end->format('M Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 w-20">
                                            <div class="bg-{{ $ratingColors[$scorecard->rating] ?? 'gray' }}-600 h-2 rounded-full"
                                                style="width: {{ $scorecard->overall_score }}%"></div>
                                        </div>
                                        <span
                                            class="font-bold">{{ number_format($scorecard->overall_score, 1) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 py-1 text-xs font-bold rounded-full bg-{{ $ratingColors[$scorecard->rating] ?? 'gray' }}-100 text-{{ $ratingColors[$scorecard->rating] ?? 'gray' }}-700 dark:bg-{{ $ratingColors[$scorecard->rating] ?? 'gray' }}-500/20 dark:text-{{ $ratingColors[$scorecard->rating] ?? 'gray' }}-400">
                                        {{ $scorecard->rating }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                    {{ number_format($scorecard->quality_score, 1) }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                    {{ number_format($scorecard->delivery_score, 1) }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                    {{ number_format($scorecard->cost_score, 1) }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                    {{ number_format($scorecard->service_score, 1) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Recent Incidents --}}
    @if (count($report['recent_incidents']) > 0)
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-red-200 dark:border-red-500/30 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <span>⚠️</span> Recent Incidents ({{ count($report['recent_incidents']) }})
                </h3>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-white/5">
                @foreach ($report['recent_incidents'] as $incident)
                    <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-[#0f172a] transition">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span
                                        class="px-2 py-0.5 text-xs rounded-full bg-{{ $incident->severity_color }}-100 text-{{ $incident->severity_color }}-700 dark:bg-{{ $incident->severity_color }}-500/20 dark:text-{{ $incident->severity_color }}-400">
                                        {{ ucfirst($incident->severity) }}
                                    </span>
                                    <span
                                        class="text-xs text-gray-500 dark:text-slate-400">{{ $incident->incident_type }}</span>
                                </div>
                                <p class="text-sm text-gray-900 dark:text-white">
                                    {{ Str::limit($incident->description, 150) }}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                                    Reported {{ $incident->reported_at->diffForHumans() }}
                                    @if ($incident->financial_impact > 0)
                                        | Impact: Rp {{ number_format($incident->financial_impact, 0, ',', '.') }}
                                    @endif
                                </p>
                            </div>
                            <span
                                class="px-2 py-1 text-xs rounded-full {{ $incident->status === 'resolved' ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400' }}">
                                {{ ucfirst($incident->status) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Latest Scorecard Notes --}}
    @if (
        $report['scorecards']->last() &&
            ($report['scorecards']->last()->strengths || $report['scorecards']->last()->areas_for_improvement))
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Assessment Notes</h2>

            @if ($report['scorecards']->last()->strengths)
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-green-600 dark:text-green-400 mb-2">Strengths</h4>
                    <p class="text-sm text-gray-700 dark:text-slate-300">{{ $report['scorecards']->last()->strengths }}
                    </p>
                </div>
            @endif

            @if ($report['scorecards']->last()->areas_for_improvement)
                <div>
                    <h4 class="text-sm font-medium text-orange-600 dark:text-orange-400 mb-2">Areas for Improvement
                    </h4>
                    <p class="text-sm text-gray-700 dark:text-slate-300">
                        {{ $report['scorecards']->last()->areas_for_improvement }}</p>
                </div>
            @endif
        </div>
    @endif
</x-app-layout>
