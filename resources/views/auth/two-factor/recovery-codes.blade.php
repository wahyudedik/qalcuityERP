<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900">Recovery Codes 2FA</h2>
    </x-slot>

    <div class="py-6 max-w-lg mx-auto px-4">
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 mb-6">
            <p class="text-sm font-semibold text-yellow-800 mb-1">⚠️ Simpan kode ini sekarang!</p>
            <p class="text-sm text-yellow-700">
                Recovery codes hanya ditampilkan sekali. Simpan di tempat yang aman.
                Setiap kode hanya bisa digunakan satu kali.
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="grid grid-cols-2 gap-2 mb-5">
                @foreach ($recoveryCodes ?? [] as $code)
                    <code
                        class="font-mono text-sm bg-gray-100 px-3 py-2 rounded-lg text-center tracking-widest text-gray-900">
                        {{ $code }}
                    </code>
                @endforeach
            </div>

            <div class="flex gap-3">
                <button onclick="copyAll()"
                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">
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
            Dialog.success('Recovery codes disalin!');
        }
    </script>
</x-app-layout>
