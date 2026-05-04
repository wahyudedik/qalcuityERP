<x-app-layout>
    <x-slot name="header">Supplier Scorecard</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
        <form method="GET" id="period-form">
            <select name="period" onchange="document.getElementById('period-form').submit()"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="monthly"   @selected(request('period', 'monthly') === 'monthly')>Bulanan</option>
                <option value="quarterly" @selected(request('period') === 'quarterly')>Kuartalan</option>
                <option value="yearly"    @selected(request('period') === 'yearly')>Tahunan</option>
            </select>
        </form>
        <div class="flex items-center gap-2">
            <a href="{{ route('suppliers.scorecards.export') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export CSV
            </a>
            <button type="button" onclick="document.getElementById('generateModal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Generate Scorecard
            </button>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Total Supplier</p>
            <p class="text-2xl font-bold text-blue-600">{{ $dashboard['total_suppliers'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Scorecard aktif</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Rata-rata Skor</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($dashboard['average_score'], 1) }}<span class="text-sm font-normal text-gray-400">/100</span></p>
            <p class="text-xs text-gray-400 mt-1">Performa keseluruhan</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Top Performer</p>
            <p class="text-2xl font-bold text-green-600">{{ $dashboard['top_performers'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Grade A</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Perlu Perhatian</p>
            <p class="text-2xl font-bold text-red-500">{{ $dashboard['at_risk'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Grade D/F</p>
        </div>
    </div>

    {{-- Performance by Category --}}
    @if(count($dashboard['by_category']) > 0)
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-4">
        <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900">Performa per Kategori</h3>
        </div>
        <div class="p-4 grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach($dashboard['by_category'] as $category => $stats)
            <div class="bg-gray-50 rounded-xl p-3">
                <p class="text-xs text-gray-500 mb-1 truncate">{{ $category }}</p>
                <p class="text-xl font-bold text-gray-900">{{ $stats['avg_score'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $stats['count'] }} supplier</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Scorecards Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between gap-3">
            <h3 class="text-sm font-semibold text-gray-900">Daftar Scorecard</h3>
            <input type="text" placeholder="Cari supplier..."
                class="px-3 py-1.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 w-48">
        </div>

        @if(count($dashboard['scorecards']) === 0)
            <div class="py-16 text-center">
                <svg class="mx-auto w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm text-gray-500">Belum ada scorecard. Klik <strong>Generate Scorecard</strong> untuk membuatnya.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Supplier</th>
                            <th class="px-4 py-3 text-center">Rating</th>
                            <th class="px-4 py-3 text-right">Skor</th>
                            <th class="px-4 py-3 text-right hidden md:table-cell">Kualitas</th>
                            <th class="px-4 py-3 text-right hidden md:table-cell">Pengiriman</th>
                            <th class="px-4 py-3 text-right hidden lg:table-cell">Harga</th>
                            <th class="px-4 py-3 text-right hidden lg:table-cell">Layanan</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($dashboard['scorecards'] as $scorecard)
                        @php
                            $ratingColor = match(true) {
                                str_starts_with($scorecard->rating ?? '', 'A') => 'bg-green-100 text-green-700',
                                str_starts_with($scorecard->rating ?? '', 'B') => 'bg-blue-100 text-blue-700',
                                str_starts_with($scorecard->rating ?? '', 'C') => 'bg-amber-100 text-amber-700',
                                str_starts_with($scorecard->rating ?? '', 'D') => 'bg-orange-100 text-orange-700',
                                default => 'bg-red-100 text-red-700',
                            };
                            $barColor = match(true) {
                                $scorecard->overall_score >= 80 => 'bg-green-500',
                                $scorecard->overall_score >= 60 => 'bg-amber-500',
                                default => 'bg-red-500',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ $scorecard->supplier?->name }}</p>
                                @if($scorecard->supplier?->company)
                                    <p class="text-xs text-gray-400">{{ $scorecard->supplier?->company }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $ratingColor }}">
                                    {{ $scorecard->rating ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <div class="w-16 bg-gray-200 rounded-full h-1.5 hidden sm:block">
                                        <div class="{{ $barColor }} h-1.5 rounded-full" style="width: {{ min($scorecard->overall_score, 100) }}%"></div>
                                    </div>
                                    <span class="font-bold text-gray-900 text-sm">{{ number_format($scorecard->overall_score, 1) }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right hidden md:table-cell text-gray-500">{{ number_format($scorecard->quality_score, 1) }}</td>
                            <td class="px-4 py-3 text-right hidden md:table-cell text-gray-500">{{ number_format($scorecard->delivery_score, 1) }}</td>
                            <td class="px-4 py-3 text-right hidden lg:table-cell text-gray-500">{{ number_format($scorecard->cost_score, 1) }}</td>
                            <td class="px-4 py-3 text-right hidden lg:table-cell text-gray-500">{{ number_format($scorecard->service_score, 1) }}</td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('suppliers.scorecard.detail', $scorecard->supplier_id) }}"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Generate Modal --}}
    <div id="generateModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl border border-gray-200 w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Generate Scorecard</h3>
                <button onclick="document.getElementById('generateModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('suppliers.scorecard.generate') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tipe Periode</label>
                    <select name="period"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="monthly">Bulanan</option>
                        <option value="quarterly">Kuartalan</option>
                        <option value="yearly">Tahunan</option>
                    </select>
                </div>
                <p class="text-xs text-gray-500">Akan membuat atau memperbarui scorecard untuk semua supplier aktif berdasarkan data performa mereka.</p>
                <div class="flex gap-3 pt-1">
                    <button type="submit"
                        class="flex-1 px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                        Generate Sekarang
                    </button>
                    <button type="button" onclick="document.getElementById('generateModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
