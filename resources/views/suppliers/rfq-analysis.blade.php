<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>📋 RFQ Response Analysis</span>
            <a href="{{ url()->previous() }}"
                class="px-3 py-1.5 text-sm bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                ← Kembali
            </a>
        </div>
    </x-slot>

    {{-- Summary Card --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Total Responses</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $analysis['total_responses'] }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Price Range</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
                    Rp {{ number_format($analysis['price_range']['lowest'], 0, ',', '.') }} -
                    Rp {{ number_format($analysis['price_range']['highest'], 0, ',', '.') }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Avg Lead Time</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ round($analysis['avg_lead_time']) }}
                    days</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Recommended Supplier</p>
                <p class="text-lg font-semibold text-green-600 dark:text-green-400 mt-1">
                    {{ $analysis['recommended_supplier'] ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    {{-- Scored Responses Table --}}
    <div
        class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">🏆 Supplier Rankings (Scored)</h3>
        </div>

        @if (count($analysis['scored_responses']) === 0)
            <div class="p-12 text-center">
                <p class="text-4xl mb-3">📭</p>
                <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada response untuk RFQ ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-[#0f172a]">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Rank</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Supplier</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Quoted Price</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Lead Time</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Price Score</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Time Score</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Overall</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @foreach ($analysis['scored_responses'] as $index => $scoredResponse)
                            @php
                                $response = $scoredResponse['response'];
                                $isRecommended = $index === 0;
                            @endphp
                            <tr
                                class="hover:bg-gray-50 dark:hover:bg-[#0f172a] transition {{ $isRecommended ? 'bg-green-50 dark:bg-green-500/10' : '' }}">
                                <td class="px-6 py-4">
                                    @if ($isRecommended)
                                        <span
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-500 text-white font-bold">1</span>
                                    @else
                                        <span
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ $response->supplier->name }}</p>
                                        @if ($response->notes)
                                            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                                                {{ Str::limit($response->notes, 50) }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-semibold text-gray-900 dark:text-white">Rp
                                        {{ number_format($response->quoted_price, 0, ',', '.') }}</span>
                                    @if ($response->quoted_price === $analysis['price_range']['lowest'])
                                        <span
                                            class="ml-2 px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">Lowest</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">{{ $response->lead_time_days }}
                                    days</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 w-16">
                                            <div class="bg-blue-600 h-2 rounded-full"
                                                style="width: {{ $scoredResponse['price_score'] }}%"></div>
                                        </div>
                                        <span
                                            class="text-xs font-medium">{{ number_format($scoredResponse['price_score'], 0) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 w-16">
                                            <div class="bg-purple-600 h-2 rounded-full"
                                                style="width: {{ $scoredResponse['lead_time_score'] }}%"></div>
                                        </div>
                                        <span
                                            class="text-xs font-medium">{{ number_format($scoredResponse['lead_time_score'], 0) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 w-20">
                                            <div class="bg-green-600 h-2 rounded-full"
                                                style="width: {{ $scoredResponse['overall_score'] }}%"></div>
                                        </div>
                                        <span
                                            class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($scoredResponse['overall_score'], 1) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($isRecommended)
                                        <button
                                            class="px-3 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700 transition">Select
                                            Winner</button>
                                    @else
                                        <button
                                            class="px-3 py-1 text-xs border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-slate-300 rounded hover:bg-gray-50 dark:hover:bg-gray-700 transition">View
                                            Details</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Scoring Methodology --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">📊 Scoring Methodology</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 rounded-lg">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-2xl">💰</span>
                    <h4 class="font-medium text-gray-900 dark:text-white">Price Score (50%)</h4>
                </div>
                <p class="text-sm text-gray-600 dark:text-slate-400">
                    Lower prices receive higher scores. Calculated as ratio to lowest quoted price.
                </p>
            </div>

            <div
                class="p-4 bg-purple-50 dark:bg-purple-500/10 border border-purple-200 dark:border-purple-500/30 rounded-lg">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-2xl">⏱️</span>
                    <h4 class="font-medium text-gray-900 dark:text-white">Lead Time Score (30%)</h4>
                </div>
                <p class="text-sm text-gray-600 dark:text-slate-400">
                    Shorter lead times are better. Compared against average lead time of all responses.
                </p>
            </div>

            <div
                class="p-4 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 rounded-lg">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-2xl">✅</span>
                    <h4 class="font-medium text-gray-900 dark:text-white">Base Participation (20%)</h4>
                </div>
                <p class="text-sm text-gray-600 dark:text-slate-400">
                    All participating suppliers receive base score for responding to RFQ.
                </p>
            </div>
        </div>

        <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="text-xs text-gray-600 dark:text-slate-400 font-mono">
                Overall Score = (Price Score × 0.50) + (Lead Time Score × 0.30) + 20
            </p>
        </div>
    </div>

    {{-- Price Comparison Chart --}}
    @if (count($analysis['scored_responses']) > 1)
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">💵 Price Comparison</h3>

            <div class="space-y-3">
                @foreach ($analysis['scored_responses'] as $scoredResponse)
                    @php
                        $response = $scoredResponse['response'];
                        $maxPrice = $analysis['price_range']['highest'];
                        $barWidth = $maxPrice > 0 ? ($response->quoted_price / $maxPrice) * 100 : 0;
                    @endphp
                    <div class="flex items-center gap-4">
                        <div class="w-32 text-sm text-gray-700 dark:text-slate-300 truncate">
                            {{ $response->supplier->name }}
                        </div>
                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-6 relative">
                            <div class="bg-indigo-600 h-6 rounded-full flex items-center justify-end pr-2 transition-all"
                                style="width: {{ max($barWidth, 10) }}%">
                                <span class="text-xs text-white font-medium whitespace-nowrap">
                                    Rp {{ number_format($response->quoted_price, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        <div class="w-20 text-xs text-gray-500 dark:text-slate-400 text-right">
                            {{ $response->lead_time_days }} days
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-app-layout>
