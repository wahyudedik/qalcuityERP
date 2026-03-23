<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Recovery Codes 2FA</h2>
    </x-slot>

    <div class="py-6 max-w-lg mx-auto px-4">
        <div class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700 rounded-xl p-5 mb-6">
            <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-1">⚠️ Simpan kode ini sekarang!</p>
            <p class="text-sm text-yellow-700 dark:text-yellow-300">
                Recovery codes hanya ditampilkan sekali. Simpan di tempat yang aman.
                Setiap kode hanya bisa digunakan satu kali.
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
            <div class="grid grid-cols-2 gap-2 mb-5">
                @foreach($recoveryCodes as $code)
                    <code class="font-mono text-sm bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-lg text-center tracking-widest text-gray-800 dark:text-gray-200">
                        {{ $code }}
                    </code>
                @endforeach
            </div>

            <div class="flex gap-3">
                <button onclick="copyAll()"
                        class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                    📋 Salin Semua
                </button>
                <a href="{{ route('dashboard') }}"
                   class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm text-center font-medium hover:bg-blue-700">
                    Selesai
                </a>
            </div>
        </div>
    </div>

    <script>
        function copyAll() {
            const codes = @json($recoveryCodes);
            navigator.clipboard.writeText(codes.join('\n'));
            alert('Recovery codes disalin!');
        }
    </script>
</x-app-layout>
