<x-app-layout>
    <x-slot name="header">RFQ Response Analysis</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ url()->previous() }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
    </div>

    {{-- Summary Card --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <p class="text-xs text-gray-500">Total Responses</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $analysis['total_responses'] }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Price Range</p>
                <p class="text-lg font-semibold text-gray-900 mt-1">
                    Rp {{ number_format($analysis['price_range']['lowest'], 0, ',', '.') }} -
                    Rp {{ number_format($analysis['price_range']['highest'], 0, ',', '.') }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Avg Lead Time</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ round($analysis['avg_lead_time']) }}
                    days</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Recommended Supplier</p>
                <p class="text-lg font-semibold text-green-600 mt-1">
                    {{ $analysis['recommended_supplier'] ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    {{-- Scored Responses Table --}}
    <div
        class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900">Supplier Rankings (Scored)</h3>
        </div>

        @if (count($analysis['scored_responses']) === 0)
            <div class="py-16 text-center">
                <svg class="mx-auto w-10 h-10 text-gray-300 mb-3" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <p class="text-sm text-gray-500">Belum ada response untuk RFQ ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Rank</th>
                            <th class="px-4 py-3 text-left">Supplier</th>
                            <th class="px-4 py-3 text-left">Quoted Price</th>
                            <th class="px-4 py-3 text-left">Lead Time</th>
                            {{-- BUG-PO-003 FIX: Added new evaluation criteria columns --}}
                            <th class="px-4 py-3 text-left hidden lg:table-cell">Rating</th>
                            <th class="px-4 py-3 text-left hidden xl:table-cell">Delivery</th>
                            <th class="px-4 py-3 text-left hidden xl:table-cell">Payment</th>
                            {{-- End BUG-PO-003 FIX --}}
                            <th class="px-4 py-3 text-left">Price Score</th>
                            <th class="px-4 py-3 text-left">Time Score</th>
                            <th class="px-4 py-3 text-left">Overall</th>
                            <th class="px-4 py-3 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($analysis['scored_responses'] as $index => $scoredResponse)
                            @php
                                $response = $scoredResponse['response'];
                                $isRecommended = $index === 0;
                            @endphp
                            <tr
                                class="hover:bg-gray-50 transition {{ $isRecommended ? 'bg-green-50' : '' }}">
                                <td class="px-4 py-3">
                                    @if ($isRecommended)
                                        <span
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-500 text-white font-bold">1</span>
                                    @else
                                        <span
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-700 font-bold">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            {{ $response->supplier?->name }}</p>
                                        @if ($response->notes)
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ Str::limit($response->notes, 50) }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-gray-900">Rp
                                        {{ number_format($response->quoted_price, 0, ',', '.') }}</span>
                                    @if ($response->quoted_price === $analysis['price_range']['lowest'])
                                        <span
                                            class="ml-2 px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700">Lowest</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $response->lead_time_days }}
                                    days</td>
                                {{-- BUG-PO-003 FIX: Added new evaluation criteria cells --}}
                                <td class="px-4 py-3 hidden lg:table-cell">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2 w-12">
                                            <div class="bg-yellow-600 h-2 rounded-full"
                                                style="width: {{ $scoredResponse['supplier_rating_score'] }}%"></div>
                                        </div>
                                        <span
                                            class="text-xs font-medium">{{ number_format($scoredResponse['supplier_rating_score'], 0) }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 hidden xl:table-cell">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2 w-12">
                                            <div class="bg-orange-600 h-2 rounded-full"
                                                style="width: {{ $scoredResponse['delivery_performance_score'] }}%">
                                            </div>
                                        </div>
                                        <span
                                            class="text-xs font-medium">{{ number_format($scoredResponse['delivery_performance_score'], 0) }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 hidden xl:table-cell">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2 w-12">
                                            <div class="bg-teal-600 h-2 rounded-full"
                                                style="width: {{ $scoredResponse['payment_terms_score'] }}%"></div>
                                        </div>
                                        <span
                                            class="text-xs font-medium">{{ number_format($scoredResponse['payment_terms_score'], 0) }}</span>
                                    </div>
                                </td>
                                {{-- End BUG-PO-003 FIX --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2 w-16">
                                            <div class="bg-blue-600 h-2 rounded-full"
                                                style="width: {{ $scoredResponse['price_score'] }}%"></div>
                                        </div>
                                        <span
                                            class="text-xs font-medium">{{ number_format($scoredResponse['price_score'], 0) }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2 w-16">
                                            <div class="bg-purple-600 h-2 rounded-full"
                                                style="width: {{ $scoredResponse['lead_time_score'] }}%"></div>
                                        </div>
                                        <span
                                            class="text-xs font-medium">{{ number_format($scoredResponse['lead_time_score'], 0) }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2 w-20>
                                            <div class="bg-green-600
                                            h-2 rounded-full" style="width: {{ $scoredResponse['overall_score'] }}%">
                                        </div>
                                    </div>
                                    <span
                                        class="text-sm font-bold text-gray-900">{{ number_format($scoredResponse['overall_score'], 1) }}</span>
            </div>
            </td>
            <td class="px-4 py-3">
                @if ($isRecommended)
                    <button
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs bg-green-600 text-white rounded-xl hover:bg-green-700 transition">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        Select Winner
                    </button>
                @else
                    <button
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        View Details
                    </button>
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
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Scoring Methodology</h2>

        {{-- BUG-PO-003 FIX: Updated from 3 to 5 criteria with new weights --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h4 class="font-medium text-gray-900">Price Score (40%)</h4>
                </div>
                <p class="text-sm text-gray-600">
                    Lower prices receive higher scores. Calculated as ratio to lowest quoted price.
                </p>
            </div>

            <div
                class="p-4 bg-purple-50 border border-purple-200 rounded-xl">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h4 class="font-medium text-gray-900">Lead Time Score (25%)</h4>
                </div>
                <p class="text-sm text-gray-600">
                    Shorter lead times are better. Compared against average lead time of all responses.
                </p>
            </div>

            <div
                class="p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                    <h4 class="font-medium text-gray-900">Supplier Rating (15%)</h4>
                </div>
                <p class="text-sm text-gray-600">
                    Based on historical scorecard ratings (quality, delivery, cost, service metrics).
                </p>
            </div>

            <div
                class="p-4 bg-orange-50 border border-orange-200 rounded-xl">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16m-6 3v-1a1 1 0 00-1-1h-1m-1 0v1a1 1 0 001 1h1m4-3V9m0 0L9 12m0-3l3-3" />
                    </svg>
                    <h4 class="font-medium text-gray-900">Delivery Performance (10%)</h4>
                </div>
                <p class="text-sm text-gray-600">
                    On-time delivery track record from historical purchase order data.
                </p>
            </div>

            <div class="p-4 bg-teal-50 border border-teal-200 rounded-xl">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <h4 class="font-medium text-gray-900">Payment Terms (10%)</h4>
                </div>
                <p class="text-sm text-gray-600">
                    Better payment terms score higher (NET 60+ best, COD excellent).
                </p>
            </div>
        </div>

        <div class="mt-4 p-4 bg-gray-50 rounded-xl">
            <p class="text-xs text-gray-600 font-mono">
                Overall Score = (Price × 0.40) + (Lead Time × 0.25) + (Rating × 0.15) + (Delivery × 0.10) + (Payment ×
                0.10)
            </p>
        </div>
    </div>

    {{-- Price Comparison Chart --}}
    @if (count($analysis['scored_responses']) > 1)
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Price Comparison</h3>

            <div class="space-y-3">
                @foreach ($analysis['scored_responses'] as $scoredResponse)
                    @php
                        $response = $scoredResponse['response'];
                        $maxPrice = $analysis['price_range']['highest'];
                        $barWidth = $maxPrice > 0 ? ($response->quoted_price / $maxPrice) * 100 : 0;
                    @endphp
                    <div class="flex items-center gap-4">
                        <div class="w-32 text-sm text-gray-700 truncate">
                            {{ $response->supplier?->name }}
                        </div>
                        <div class="flex-1 bg-gray-200 rounded-full h-6 relative">
                            <div class="bg-indigo-600 h-6 rounded-full flex items-center justify-end pr-2 transition-all"
                                style="width: {{ max($barWidth, 10) }}%">
                                <span class="text-xs text-white font-medium whitespace-nowrap">
                                    Rp {{ number_format($response->quoted_price, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        <div class="w-20 text-xs text-gray-500 text-right">
                            {{ $response->lead_time_days }} days
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-app-layout>
