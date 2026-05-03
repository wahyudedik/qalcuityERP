<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error 500 - Kesalahan Server | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="bg-gradient-to-br from-red-50 to-orange-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full">
        <!-- Error Icon -->
        <div class="text-center mb-8">
            <div
                class="inline-flex items-center justify-center w-32 h-32 bg-red-100 rounded-full mb-6">
                <svg class="w-20 h-20 text-red-600" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <h1 class="text-6xl font-bold text-red-600 mb-4">500</h1>
            <h2 class="text-3xl font-semibold text-gray-900 mb-4">
                Kesalahan Server
            </h2>
            <p class="text-lg text-gray-600 mb-6">
                {{ $message ?? 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi nanti.' }}
            </p>
        </div>

        <!-- Error ID (for support) -->
        @if (isset($error_id))
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <div class="flex items-start gap-4">
                    <svg class="w-6 h-6 text-blue-500 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-2">Referensi Error</h3>
                        <p class="text-sm text-gray-600 mb-2">
                            Jika Anda memerlukan bantuan, berikan ID error ini kepada tim support:
                        </p>
                        <code
                            class="bg-gray-100 px-3 py-2 rounded-md font-mono text-sm text-gray-800">
                            {{ $error_id }}
                        </code>
                    </div>
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ url('/') }}"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors min-h-[44px] min-w-[44px]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Kembali ke Beranda
            </a>

            <button onclick="window.location.reload()"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition-colors min-h-[44px] min-w-[44px]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.058h5V4.058A1.992 1.992 0 008 4H4a1 1 0 00-1 1v5a1 1 0 001 1h5a1 1 0 001-1V5a1 1 0 00-1-1H4zM4 20v-5h.058h5V20H4a1 1 0 01-1-1v-5a1 1 0 011-1h5a1 1 0 011 1v5a1 1 0 01-1 1H4z" />
                </svg>
                Coba Lagi
            </button>

            <a href="{{ route('contact') ?? '#' }}"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors min-h-[44px] min-w-[44px]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                Hubungi Support
            </button>
        </div>

        <!-- Technical Details (Development Only) -->
        @if (config('app.debug'))
            <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                    Informasi Debug
                </h3>
                <div class="space-y-2 text-sm">
                    <div>
                        <span class="font-medium text-gray-700">Exception:</span>
                        <code
                            class="ml-2 bg-gray-100 px-2 py-1 rounded">{{ $exception->getMessage() }}</code>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Lokasi:</span>
                        <code
                            class="ml-2 bg-gray-100 px-2 py-1 rounded">{{ $exception->getFile() }}:{{ $exception->getLine() }}</code>
                    </div>
                </div>
            </div>
        @endif
    </div>
</body>

</html>
