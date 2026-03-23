<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('zero-input.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">←</a>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Detail Zero Input</h2>
        </div>
    </x-slot>

    <div class="py-6 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
        @if(session('success'))
            <div class="p-3 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-3 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg text-sm">{{ session('error') }}</div>
        @endif

        <!-- Status Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Channel: <span class="font-medium capitalize">{{ $log->channel }}</span></p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Modul: <span class="font-medium capitalize">{{ str_replace('_', ' ', $log->mapped_module ?? '-') }}</span></p>
                </div>
                <span class="px-3 py-1 rounded-full text-sm font-medium
                    {{ $log->status === 'mapped' || $log->status === 'created' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' :
                       ($log->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                    {{ ucfirst($log->status) }}
                </span>
            </div>

            @if($log->file_path)
                <img src="{{ Storage::url($log->file_path) }}" alt="Nota" class="max-h-48 rounded-lg object-contain border border-gray-200 dark:border-gray-700">
            @endif

            @if($log->raw_input)
                <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg text-sm text-gray-700 dark:text-gray-300">
                    {{ $log->raw_input }}
                </div>
            @endif
        </div>

        <!-- Extracted Data -->
        @if($log->extracted_data && $log->status !== 'failed')
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">Data yang Diekstrak</h3>

                <form method="POST" action="{{ route('zero-input.confirm', $log) }}" class="space-y-3">
                    @csrf

                    @foreach($log->extracted_data as $key => $value)
                        @if($key === 'module' || $key === 'items') @continue @endif
                        <div class="flex items-center gap-3">
                            <label class="w-32 text-xs text-gray-500 dark:text-gray-400 capitalize shrink-0">
                                {{ str_replace('_', ' ', $key) }}
                            </label>
                            <input type="text" name="extracted_data[{{ $key }}]"
                                   value="{{ is_array($value) ? json_encode($value) : $value }}"
                                   class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                        </div>
                    @endforeach

                    @if(!empty($log->extracted_data['items']))
                        <div class="mt-3">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Item:</p>
                            <div class="space-y-1">
                                @foreach($log->extracted_data['items'] as $i => $item)
                                    <div class="flex gap-2 text-xs">
                                        <input type="text" name="extracted_data[items][{{ $i }}][name]"
                                               value="{{ $item['name'] ?? '' }}" placeholder="Nama"
                                               class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs">
                                        <input type="number" name="extracted_data[items][{{ $i }}][qty]"
                                               value="{{ $item['qty'] ?? 1 }}" placeholder="Qty"
                                               class="w-16 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs">
                                        <input type="number" name="extracted_data[items][{{ $i }}][price]"
                                               value="{{ $item['price'] ?? 0 }}" placeholder="Harga"
                                               class="w-28 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($log->status !== 'created')
                        <div class="flex gap-3 pt-3">
                            <a href="{{ route('zero-input.index') }}"
                               class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                                Batal
                            </a>
                            <button type="submit"
                                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                                ✅ Konfirmasi & Buat Record ERP
                            </button>
                        </div>
                    @else
                        <div class="p-3 bg-green-50 dark:bg-green-900/30 rounded-lg text-sm text-green-700 dark:text-green-300">
                            ✅ Record ERP sudah dibuat.
                            @if(!empty($log->created_records))
                                <span class="text-xs ml-1">({{ count($log->created_records) }} record)</span>
                            @endif
                        </div>
                    @endif
                </form>
            </div>
        @elseif($log->status === 'failed')
            <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl p-4 text-sm text-red-700 dark:text-red-300">
                ❌ Gagal memproses: {{ $log->error_message }}
            </div>
        @endif
    </div>
</x-app-layout>
