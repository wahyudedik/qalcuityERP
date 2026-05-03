<x-app-layout>
    <x-slot name="header">CCTV — Kamera {{ $cameraId }}</x-slot>

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <a href="{{ route('cctv.index') }}"
            class="hover:text-blue-600 transition-colors">CCTV</a>
        <span>/</span>
        <span class="text-gray-900">Kamera {{ $cameraId }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Stream Area --}}
        <div class="lg:col-span-2">
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                {{-- Video Player --}}
                <div class="relative aspect-video bg-gray-900 flex items-center justify-center">
                    @if (($stream['success'] ?? false) && ($stream['stream_url'] ?? null))
                        <div class="w-full h-full flex items-center justify-center">
                            <p class="text-white text-sm">Stream: {{ $stream['stream_url'] }}</p>
                        </div>
                        {{-- Status overlay --}}
                        <div class="absolute top-3 left-3">
                            <span
                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-red-600 text-white">
                                <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span> LIVE
                            </span>
                        </div>
                        <div class="absolute top-3 right-3 text-xs text-white/70">
                            {{ $stream['resolution'] ?? '1920x1080' }}
                        </div>
                    @else
                        <div class="text-center">
                            <svg class="w-16 h-16 text-gray-600 mx-auto mb-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            <p class="text-gray-400 text-sm">{{ $stream['message'] ?? 'Stream tidak tersedia' }}</p>
                        </div>
                    @endif
                </div>

                {{-- Camera Name & Location --}}
                <div class="p-4 border-t border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">
                        {{ $stream['camera_name'] ?? 'Kamera ' . $cameraId }}
                    </h3>
                    @if ($stream['location'] ?? null)
                        <p class="text-xs text-gray-500 mt-1">📍 {{ $stream['location'] }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar Info --}}
        <div class="space-y-4">
            {{-- Camera Status --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Status Kamera</h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Koneksi</span>
                        @if ($status['online'] ?? false)
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                Online
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                Offline
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Rekaman</span>
                        @if ($status['recording'] ?? false)
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span> Merekam
                            </span>
                        @else
                            <span class="text-xs text-gray-600">Tidak aktif</span>
                        @endif
                    </div>
                    @if ($status['storage_used'] ?? null)
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Penyimpanan</span>
                            <span class="text-xs text-gray-700">{{ $status['storage_used'] }}</span>
                        </div>
                    @endif
                    @if ($status['last_motion'] ?? null)
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Gerakan Terakhir</span>
                            <span class="text-xs text-gray-700">{{ $status['last_motion'] }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Aksi</h4>
                <div class="space-y-2">
                    <form method="POST" action="{{ route('cctv.snapshot', $cameraId) }}">
                        @csrf
                        <button type="submit"
                            class="w-full px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
                            📸 Ambil Snapshot
                        </button>
                    </form>
                    <form method="POST" action="{{ route('cctv.motion-detect', $cameraId) }}">
                        @csrf
                        <button type="submit"
                            class="w-full px-4 py-2 text-sm bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition-colors">
                            🔍 Deteksi Gerakan
                        </button>
                    </form>
                    <a href="{{ route('cctv.recordings', ['camera_id' => $cameraId]) }}"
                        class="block w-full px-4 py-2 text-sm text-center bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors">
                        📹 Lihat Rekaman
                    </a>
                </div>
            </div>

            {{-- Back --}}
            <a href="{{ route('cctv.index') }}"
                class="block w-full px-4 py-2 text-sm text-center bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors">
                ← Kembali ke Daftar Kamera
            </a>
        </div>
    </div>
</x-app-layout>
