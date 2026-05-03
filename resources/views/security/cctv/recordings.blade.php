<x-app-layout>
    <x-slot name="header">CCTV — Rekaman</x-slot>

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <a href="{{ route('cctv.index') }}"
            class="hover:text-blue-600 transition-colors">CCTV</a>
        <span>/</span>
        <span class="text-gray-900">Rekaman</span>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-4">
        <form method="GET" action="{{ route('cctv.recordings') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <label class="block text-xs text-gray-500 mb-1">Kamera</label>
                <input type="number" name="camera_id" value="{{ $cameraId ?? '' }}" placeholder="ID Kamera"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex-1">
                <label class="block text-xs text-gray-500 mb-1">Dari</label>
                <input type="datetime-local" name="start_time" value="{{ request('start_time') }}"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex-1">
                <label class="block text-xs text-gray-500 mb-1">Sampai</label>
                <input type="datetime-local" name="end_time" value="{{ request('end_time') }}"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex items-end">
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
                    Cari
                </button>
            </div>
        </form>
    </div>

    {{-- Recordings List --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Daftar Rekaman</h3>
            @if ($cameraId)
                <span class="text-xs text-gray-500">Kamera #{{ $cameraId }}</span>
            @endif
        </div>

        @if (($recordings['success'] ?? false) && !empty($recordings['recordings']))
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Waktu Mulai</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Waktu Selesai</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">Durasi</th>
                            <th class="px-4 py-3 text-left hidden lg:table-cell">Ukuran</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($recordings['recordings'] as $recording)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-gray-900 whitespace-nowrap">
                                    {{ $recording['start_time'] ?? '-' }}
                                </td>
                                <td
                                    class="px-4 py-3 text-gray-600 hidden sm:table-cell whitespace-nowrap">
                                    {{ $recording['end_time'] ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600 hidden md:table-cell">
                                    {{ $recording['duration'] ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600 hidden lg:table-cell">
                                    {{ $recording['file_size'] ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($recording['download_url'] ?? null)
                                        <a href="{{ $recording['download_url'] }}"
                                            class="inline-flex items-center px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                                            target="_blank">
                                            ⬇ Download
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-8 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                </svg>
                <p class="text-gray-500 text-sm mb-1">Tidak ada rekaman ditemukan.</p>
                <p class="text-xs text-gray-400">
                    @if (!$cameraId)
                        Pilih kamera dan rentang waktu untuk mencari rekaman.
                    @else
                        Coba ubah rentang waktu pencarian.
                    @endif
                </p>
            </div>
        @endif
    </div>

    {{-- Back --}}
    <div class="mt-4">
        <a href="{{ route('cctv.index') }}"
            class="inline-flex items-center px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors">
            ← Kembali ke Daftar Kamera
        </a>
    </div>
</x-app-layout>
