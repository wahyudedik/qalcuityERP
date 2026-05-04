<x-app-layout>
    <x-slot name="header">🐟 Dashboard Perikanan</x-slot>

    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
            {{ session('success') }}</div>
    @endif

    {{-- Quick Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Unit Cold Storage</p>
            <p class="text-2xl font-bold text-blue-600" x-data="{ count: @js($stats['cold_storage_units'] ?? 0) }" x-text="count">0</p>
            <p class="text-xs mt-1" :class="{{ $stats['temp_alerts'] ?? 0 > 0 ? 'text-red-500' : 'text-green-500' }}">
                {{ $stats['temp_alerts'] ?? 0 }} alert suhu
            </p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Trip Aktif</p>
            <p class="text-2xl font-bold text-emerald-600">{{ $stats['active_trips'] ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $stats['total_catches'] ?? 0 }} total tangkapan</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Kolam Budidaya</p>
            <p class="text-2xl font-bold text-cyan-600">{{ $stats['ponds'] ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $stats['avg_pond_utilization'] ?? 0 }}% utilisasi</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Spesies Terdaftar</p>
            <p class="text-2xl font-bold text-purple-600">{{ $stats['species_count'] ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $stats['export_shipments'] ?? 0 }} pengiriman</p>
        </div>
    </div>

    {{-- Feature Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        {{-- Cold Chain Management --}}
        <a href="{{ route('fisheries.cold-chain.index') }}"
            class="block bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl border border-blue-200 p-6 hover:shadow-lg transition group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center text-2xl">❄️</div>
                <span class="text-xs px-2 py-1 bg-blue-200 text-blue-700 rounded-full">Monitoring</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Cold Chain Management</h3>
            <p class="text-sm text-gray-600 mb-3">Pantau suhu cold storage, kelola alert, dan
                pastikan kualitas produk tetap terjaga</p>
            <div class="flex items-center text-sm text-blue-600 font-medium group-hover:translate-x-1 transition">
                Lihat Detail →
            </div>
        </a>

        {{-- Fishing Operations --}}
        <a href="{{ route('fisheries.operations.index') }}"
            class="block bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-2xl border border-emerald-200 p-6 hover:shadow-lg transition group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-emerald-600 rounded-xl flex items-center justify-center text-2xl">⚓</div>
                <span class="text-xs px-2 py-1 bg-emerald-200 text-emerald-700 rounded-full">Operasional</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Fishing Operations</h3>
            <p class="text-sm text-gray-600 mb-3">Kelola trip penangkapan, catat hasil tangkapan,
                dan tracking armada kapal</p>
            <div class="flex items-center text-sm text-emerald-600 font-medium group-hover:translate-x-1 transition">
                Lihat Detail →
            </div>
        </a>

        {{-- Aquaculture --}}
        <a href="{{ route('fisheries.aquaculture.index') }}"
            class="block bg-gradient-to-br from-cyan-50 to-cyan-100 rounded-2xl border border-cyan-200 p-6 hover:shadow-lg transition group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-cyan-600 rounded-xl flex items-center justify-center text-2xl">🐠</div>
                <span class="text-xs px-2 py-1 bg-cyan-200 text-cyan-700 rounded-full">Budidaya</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Aquaculture Management</h3>
            <p class="text-sm text-gray-600 mb-3">Monitor kualitas air kolam, jadwal pemberian
                pakan, dan kesehatan ikan</p>
            <div class="flex items-center text-sm text-cyan-600 font-medium group-hover:translate-x-1 transition">
                Lihat Detail →
            </div>
        </a>

        {{-- Species Catalog --}}
        <a href="{{ route('fisheries.species.index') }}"
            class="block bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl border border-purple-200 p-6 hover:shadow-lg transition group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-purple-600 rounded-xl flex items-center justify-center text-2xl">📋</div>
                <span class="text-xs px-2 py-1 bg-purple-200 text-purple-700 rounded-full">Katalog</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Species & Grading</h3>
            <p class="text-sm text-gray-600 mb-3">Kelola katalog spesies ikan, sistem grading
                kualitas, dan penilaian kesegaran</p>
            <div class="flex items-center text-sm text-purple-600 font-medium group-hover:translate-x-1 transition">
                Lihat Detail →
            </div>
        </a>

        {{-- Export Documentation --}}
        <a href="{{ route('fisheries.export.index') }}"
            class="block bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl border border-orange-200 p-6 hover:shadow-lg transition group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-orange-600 rounded-xl flex items-center justify-center text-2xl">📦</div>
                <span class="text-xs px-2 py-1 bg-orange-200 text-orange-700 rounded-full">Ekspor</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Export Documentation</h3>
            <p class="text-sm text-gray-600 mb-3">Urus perizinan ekspor, sertifikat kesehatan, dan
                dokumen kepabeanan</p>
            <div class="flex items-center text-sm text-orange-600 font-medium group-hover:translate-x-1 transition">
                Lihat Detail →
            </div>
        </a>

        {{-- Analytics & Reports --}}
        <a href="{{ route('fisheries.analytics') }}"
            class="block bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-2xl border border-indigo-200 p-6 hover:shadow-lg transition group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center text-2xl">📊</div>
                <span class="text-xs px-2 py-1 bg-indigo-200 text-indigo-700 rounded-full">Analitik</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Analytics & Reports</h3>
            <p class="text-sm text-gray-600 mb-3">Laporan produksi, analisis efisiensi, dan insight
                bisnis perikanan</p>
            <div class="flex items-center text-sm text-indigo-600 font-medium group-hover:translate-x-1 transition">
                Lihat Detail →
            </div>
        </a>
    </div>

    {{-- Recent Activity --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-4">Aktivitas Terbaru</h3>

        @if (empty($recent_activities) || count($recent_activities) === 0)
            <div class="text-center py-8">
                <p class="text-3xl mb-2">🐟</p>
                <p class="text-sm text-gray-500">Belum ada aktivitas. Mulai dengan menambahkan data
                    perikanan Anda.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($recent_activities as $activity)
                    <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50">
                        <div
                            class="w-8 h-8 rounded-full bg-{{ $activity['color'] ?? 'blue'  }}-100 $activity['color'] ?? 'blue' }}-500/20 flex items-center justify-center text-sm">
                            {!! $activity['icon'] ?? '📌' !!}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $activity['title'] }}</p>
                            <p class="text-xs text-gray-500">{{ $activity['description'] }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $activity['time'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
