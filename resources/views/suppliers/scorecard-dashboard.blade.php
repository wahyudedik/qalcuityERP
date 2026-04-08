<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Supplier Scorecard Dashboard</h1>
            <button type="button" onclick="document.getElementById('generateModal').classList.remove('hidden')"
                class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition whitespace-nowrap">
                Generate Scorecards
            </button>
        </div>
    </x-slot>

    {{-- Period Selector --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4 mb-6">
        <form class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Supplier Performance Overview</h2>
            <div class="flex gap-3">
                <select name="period" onchange="this.form.submit()"
                    class="px-4 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="monthly" @selected(request('period', 'monthly') === 'monthly')>Monthly</option>
                    <option value="quarterly" @selected(request('period') === 'quarterly')>Quarterly</option>
                    <option value="yearly" @selected(request('period') === 'yearly')>Yearly</option>
                </select>
                <button type="button" onclick="document.getElementById('generateModal').classList.remove('hidden')"
                    class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                    Generate Scorecards
                </button>
            </div>
        </form>
    </div>

    {{-- Key Metrics Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div
            class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl border border-blue-200 dark:border-blue-500/30 p-5">
            <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Total Suppliers</p>
            <p class="text-3xl font-bold text-blue-700 dark:text-blue-300 mt-2">{{ $dashboard['total_suppliers'] }}</p>
            <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">Active scorecards</p>
        </div>

        <div
            class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 rounded-xl border border-emerald-200 dark:border-emerald-500/30 p-5">
            <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Average Score</p>
            <p class="text-3xl font-bold text-emerald-700 dark:text-emerald-300 mt-2">
                {{ number_format($dashboard['average_score'], 1) }}/100</p>
            <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">Overall performance</p>
        </div>

        <div
            class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-xl border border-green-200 dark:border-green-500/30 p-5">
            <p class="text-xs text-green-600 dark:text-green-400 font-medium">Top Performers</p>
            <p class="text-3xl font-bold text-green-700 dark:text-green-300 mt-2">{{ $dashboard['top_performers'] }}</p>
            <p class="text-xs text-green-600 dark:text-green-400 mt-1">Grade A suppliers</p>
        </div>

        <div
            class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-xl border border-red-200 dark:border-red-500/30 p-5">
            <p class="text-xs text-red-600 dark:text-red-400 font-medium">At Risk</p>
            <p class="text-3xl font-bold text-red-700 dark:text-red-300 mt-2">{{ $dashboard['at_risk'] }}</p>
            <p class="text-xs text-red-600 dark:text-red-400 mt-1">Grade D/F - Need attention</p>
        </div>
    </div>

    {{-- Performance by Category --}}
    @if (count($dashboard['by_category']) > 0)
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Performance by Category</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ($dashboard['by_category'] as $category => $stats)
                    <div class="p-4 bg-gray-50 dark:bg-[#0f172a] rounded-xl">
                        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">{{ $category }}</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['avg_score'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">{{ $stats['count'] }} suppliers</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Supplier Scorecards Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Supplier Scorecards</h2>
            <div class="flex gap-2">
                <input type="text" placeholder="Search supplier..."
                    class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white w-48">
            </div>
        </div>

        @if (count($dashboard['scorecards']) === 0)
            <div class="p-12 text-center">
                <p class="text-4xl mb-3">📊</p>
                <p class="text-sm text-gray-500 dark:text-slate-400">No scorecards generated yet. Click "Generate
                    Scorecards" to create them.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-[#0f172a]">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Supplier</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Overall Score</th>
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
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @foreach ($dashboard['scorecards'] as $scorecard)
                            <tr class="hover:bg-gray-50 dark:hover:bg-[#0f172a] transition">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ $scorecard->supplier->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-slate-400">
                                            {{ $scorecard->supplier->code ?? '' }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 w-20">
                                            <div class="bg-{{ $scorecard->rating_color }}-600 h-2 rounded-full"
                                                style="width: {{ $scorecard->overall_score }}%"></div>
                                        </div>
                                        <span
                                            class="font-bold text-gray-900 dark:text-white">{{ number_format($scorecard->overall_score, 1) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-3 py-1 text-sm font-bold rounded-full bg-{{ $scorecard->rating_color }}-100 text-{{ $scorecard->rating_color }}-700 dark:bg-{{ $scorecard->rating_color }}-500/20 dark:text-{{ $scorecard->rating_color }}-400">
                                        {{ $scorecard->rating }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                    {{ number_format($scorecard->quality_score, 1) }}
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                    {{ number_format($scorecard->delivery_score, 1) }}
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                    {{ number_format($scorecard->cost_score, 1) }}
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                    {{ number_format($scorecard->service_score, 1) }}
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('suppliers.scorecard.detail', $scorecard->supplier_id) }}"
                                        class="text-blue-600 dark:text-blue-400 hover:underline text-xs">
                                        View Detail →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Generate Scorecards Modal --}}
    <div id="generateModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Generate Scorecards</h2>
                <button onclick="document.getElementById('generateModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('suppliers.scorecard.generate') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Period Type</label>
                    <select name="period"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                <p class="text-xs text-gray-500 dark:text-slate-400">This will generate/update scorecards for all
                    active
                    suppliers based on their performance data.</p>
                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        ✅ Generate Now
                    </button>
                    <button type="button" onclick="document.getElementById('generateModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
