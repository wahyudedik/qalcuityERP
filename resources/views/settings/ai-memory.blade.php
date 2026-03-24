<x-app-layout>
    <x-slot name="header">Memori AI</x-slot>

    <div class="py-6 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if(session('success'))
            <div class="p-3 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Suggestions -->
        @if(!empty($suggestions))
            <div class="bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded-xl p-5">
                <h3 class="font-semibold text-indigo-800 dark:text-indigo-200 text-sm mb-3">💡 Saran Berdasarkan Kebiasaan Anda</h3>
                <ul class="space-y-2">
                    @foreach($suggestions as $s)
                        <li class="text-sm text-indigo-700 dark:text-indigo-300 flex items-start gap-2">
                            <span class="mt-0.5">→</span>
                            <span>{{ $s }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Memory List -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow">
            <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200">Preferensi yang Dipelajari</h3>
                <form method="POST" action="{{ route('ai-memory.reset') }}"
                      onsubmit="return confirm('Reset semua memori AI? Preferensi yang dipelajari akan dihapus.')">
                    @csrf
                    <button class="text-sm text-red-500 hover:text-red-700 dark:hover:text-red-400">
                        Reset Semua
                    </button>
                </form>
            </div>

            @if($memories->isEmpty())
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    <div class="text-4xl mb-3">🧠</div>
                    <p class="text-sm">AI belum mempelajari preferensi Anda.</p>
                    <p class="text-xs mt-1">Preferensi akan dipelajari secara otomatis saat Anda menggunakan sistem.</p>
                </div>
            @else
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($memories as $memory)
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 capitalize">
                                    {{ str_replace('_', ' ', $memory->key) }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    @if(is_array($memory->value))
                                        {{ implode(', ', array_slice($memory->value, 0, 3)) }}
                                    @else
                                        {{ $memory->value }}
                                    @endif
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Frekuensi: {{ $memory->frequency }}x ·
                                    Terakhir: {{ $memory->last_seen_at?->diffForHumans() ?? '-' }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route('ai-memory.destroy', $memory) }}">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-400 hover:text-red-600 ml-4">Hapus</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 text-xs text-gray-500 dark:text-gray-400">
            <p class="font-medium mb-1">Tentang Memori AI</p>
            <p>AI mempelajari kebiasaan Anda secara otomatis: metode pembayaran favorit, gudang default, customer yang sering digunakan, dan langkah yang sering dilewati. Data ini digunakan untuk memberikan saran yang lebih relevan dan mempercepat alur kerja Anda.</p>
        </div>
    </div>
</x-app-layout>
