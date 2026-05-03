<x-app-layout>
    <x-slot name="header">CCTV Monitoring</x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Total Kamera</p>
            <p class="text-2xl font-bold text-gray-900">{{ $cameras['total'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Online</p>
            <p class="text-2xl font-bold text-green-500">
                {{ collect($cameras['cameras'] ?? [])->filter(fn($c) => $c['status']['online'] ?? false)->count() }}
            </p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Offline</p>
            <p class="text-2xl font-bold text-red-500">
                {{ collect($cameras['cameras'] ?? [])->filter(fn($c) => !($c['status']['online'] ?? false))->count() }}
            </p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <a href="{{ route('cctv.recordings') }}"
            class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors text-center">
            📹 Rekaman
        </a>
        <a href="{{ route('security.dashboard') }}"
            class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors text-center">
            🔒 Dashboard Keamanan
        </a>
    </div>

    {{-- Camera Grid --}}
    @if (($cameras['success'] ?? false) && !empty($cameras['cameras']))
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($cameras['cameras'] as $camera)
                <div
                    class="bg-white rounded-2xl border border-gray-200 overflow-hidden group">
                    {{-- Camera Preview --}}
                    <div class="relative aspect-video bg-gray-900 flex items-center justify-center">
                        <div class="text-center">
                            <svg class="w-12 h-12 text-gray-600 mx-auto mb-2" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <p class="text-xs text-gray-500">Kamera {{ $camera['id'] ?? '-' }}</p>
                        </div>
                        {{-- Status Badge --}}
                        <div class="absolute top-2 right-2">
                            @if ($camera['status']['online'] ?? false)
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-500/90 text-white">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> Online
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-500/90 text-white">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span> Offline
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Camera Info --}}
                    <div class="p-4">
                        <h3 class="text-sm font-semibold text-gray-900 mb-1">
                            {{ $camera['name'] ?? 'Kamera ' . ($camera['id'] ?? '') }}
                        </h3>
                        @if ($camera['location'] ?? null)
                            <p class="text-xs text-gray-500 mb-3">
                                📍 {{ $camera['location'] }}
                            </p>
                        @endif

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('cctv.camera', $camera['id'] ?? 0) }}"
                                class="flex-1 min-w-[80px] px-3 py-2 text-xs text-center bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
                                Lihat
                            </a>
                            <form method="POST" action="{{ route('cctv.snapshot', $camera['id'] ?? 0) }}"
                                class="flex-1 min-w-[80px]">
                                @csrf
                                <button type="submit"
                                    class="w-full px-3 py-2 text-xs bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors">
                                    📸 Snapshot
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            <p class="text-gray-500 mb-2">Belum ada kamera yang dikonfigurasi.</p>
            <p class="text-xs text-gray-400">
                Konfigurasi kamera CCTV melalui menu Pengaturan &gt; Integrasi &gt; CCTV.
            </p>
        </div>
    @endif
</x-app-layout>
