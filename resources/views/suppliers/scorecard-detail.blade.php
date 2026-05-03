<x-app-layout>
    <x-slot name="header">{{ $report['supplier']->name }} - Performance Report</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('suppliers.scorecards.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
    </div>

    {{-- Current Rating Card --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $report['supplier']->name }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $report['supplier']->code ?? '' }} |
                    {{ $report['supplier']->email ?? '' }}</p>
            </div>
            @php
                $ratingColors = ['A' => 'green', 'B' => 'blue', 'C' => 'yellow', 'D' => 'orange', 'F' => 'red'];
                $color = $ratingColors[$report['current_rating']] ?? 'gray';
            @endphp
            <div class="text-right">
                <span
                    class="inline-block px-4 py-2 text-2xl font-bold rounded-full bg-{{ $color }}-100 text-{{ $color }}-700 $color }}-500/20 $color }}-400">
                    {{ $report['current_rating'] }}
                </span>
                <p class="text-xs text-gray-500 mt-2">Current Rating</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-gray-100">
            <div>
                <p class="text-xs text-gray-500">Overall Score</p>
                <p class="text-3xl font-bold text-gray-900">
                    {{ number_format($report['current_score'], 1) }}/100</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Trend</p>
                <p
                    class="text-lg font-medium {{ $report['trend'] === 'improving' ? 'text-green-600' : ($report['trend'] === 'declining' ? 'text-red-600' : 'text-yellow-600') }}">
                    @if ($report['trend'] === 'improving')
                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        Improving
                    @elseif($report['trend'] === 'declining')
                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                        </svg>
                        Declining
                    @else
                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14" />
                        </svg>
                        Stable
                    @endif
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Total Incidents</p>
                <p class="text-2xl font-bold text-gray-900">{{ $report['total_incidents'] }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Scorecards</p>
                <p class="text-2xl font-bold text-gray-900">{{ count($report['scorecards']) }}</p>
            </div>
        </div>
    </div>

    {{-- Performance Trend Chart --}}
    @if (count($report['scorecards']) > 1)
        <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Performance Trend
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
                        <span class="text-[10px] text-gray-500 mt-2 rotate-45 origin-left">
                            {{ $scorecard->period_end->format('M/y') }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Score History Table --}}
    <div
        class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-base font-semibold text-gray-900">Score History</h2>
        </div>

        @if (count($report['scorecards']) === 0)
            <div class="p-12 text-center">
                <svg class="mx-auto w-16 h-16 text-gray-300 mb-3" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-sm text-gray-500">Belum ada scorecard untuk supplier ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Period</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Overall</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Rating</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Quality</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Delivery</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Cost</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Service</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($report['scorecards'] as $scorecard)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                    {{ $scorecard->period_end->format('M Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2 w-20">
                                            <div class="bg-{{ $ratingColors[$scorecard->rating] ?? 'gray' }}-600 h-2 rounded-full"
                                                style="width: {{ $scorecard->overall_score }}%"></div>
                                        </div>
                                        <span
                                            class="font-bold">{{ number_format($scorecard->overall_score, 1) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 py-1 text-xs font-bold rounded-full bg-{{ $ratingColors[$scorecard->rating] ?? 'gray' }}-100 text-{{ $ratingColors[$scorecard->rating] ?? 'gray' }}-700 $ratingColors[$scorecard->rating] ?? 'gray' }}-500/20 $ratingColors[$scorecard->rating] ?? 'gray' }}-400">
                                        {{ $scorecard->rating }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    {{ number_format($scorecard->quality_score, 1) }}</td>
                                <td class="px-6 py-4 text-gray-700">
                                    {{ number_format($scorecard->delivery_score, 1) }}</td>
                                <td class="px-6 py-4 text-gray-700">
                                    {{ number_format($scorecard->cost_score, 1) }}</td>
                                <td class="px-6 py-4 text-gray-700">
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
            class="bg-white rounded-2xl border border-red-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-red-200 bg-red-50">
                <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Recent Incidents ({{ count($report['recent_incidents']) }})
                </h3>
            </div>

            <div class="divide-y divide-gray-200">
                @foreach ($report['recent_incidents'] as $incident)
                    <div class="px-6 py-4 hover:bg-gray-50 transition">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span
                                        class="px-2 py-0.5 text-xs rounded-full bg-{{ $incident->severity_color }}-100 text-{{ $incident->severity_color }}-700 $incident->severity_color }}-500/20 $incident->severity_color }}-400">
                                        {{ ucfirst($incident->severity) }}
                                    </span>
                                    <span
                                        class="text-xs text-gray-500">{{ $incident->incident_type }}</span>
                                </div>
                                <p class="text-sm text-gray-900">
                                    {{ Str::limit($incident->description, 150) }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Reported {{ $incident->reported_at->diffForHumans() }}
                                    @if ($incident->financial_impact > 0)
                                        | Impact: Rp {{ number_format($incident->financial_impact, 0, ',', '.') }}
                                    @endif
                                </p>
                            </div>
                            <span
                                class="px-2 py-1 text-xs rounded-full {{ $incident->status === 'resolved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
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
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Assessment Notes</h2>

            @if ($report['scorecards']->last()->strengths)
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-green-600 mb-2">Strengths</h4>
                    <p class="text-sm text-gray-700">
                        {{ $report['scorecards']->last()->strengths }}
                    </p>
                </div>
            @endif

            @if ($report['scorecards']->last()->areas_for_improvement)
                <div>
                    <h4 class="text-sm font-medium text-orange-600 mb-2">Areas for Improvement
                    </h4>
                    <p class="text-sm text-gray-700">
                        {{ $report['scorecards']->last()->areas_for_improvement }}</p>
                </div>
            @endif
        </div>
    @endif
</x-app-layout>
