<x-app-layout>
    <x-slot name="header">Detail Zero Input</x-slot>

    <div class="py-6 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
        @if(session('success'))
            <div class="p-3 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-3 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg text-sm">{{ session('error') }}</div>
        @endif

        <!-- Status Card -->
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-sm text-gray-500 dark:text-slate-400">Channel: <span class="font-medium capitalize text-gray-900 dark:text-white">{{ $log->channel }}</span></p>
                    <p class="text-sm text-gray-500 dark:text-slate-400">Modul: <span class="font-medium capitalize text-gray-900 dark:text-white">{{ str_replace('_', ' ', $log->mapped_module ?? '-') }}</span></p>
                </div>
                <div class="flex items-center gap-2">
                    @if($log->confidence_score)
                    @php
                        $confColor = $log->confidence_score >= 80 ? 'green' : ($log->confidence_score >= 50 ? 'amber' : 'red');
                    @endphp
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-{{ $confColor }}-100 text-{{ $confColor }}-700 dark:bg-{{ $confColor }}-500/20 dark:text-{{ $confColor }}-400">
                        AI {{ $log->confidence_score }}%
                    </span>
                    @endif
                    <span class="px-3 py-1 rounded-full text-sm font-medium
                        {{ in_array($log->status, ['mapped','created']) ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' :
                           ($log->status === 'failed' || $log->status === 'rejected' ? 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400') }}">
                        {{ ucfirst($log->status) }}
                    </span>
                </div>
            </div>

            @if($log->was_corrected && $log->feedback === 'corrected')
            <div class="mb-3 px-3 py-2 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl text-xs text-amber-700 dark:text-amber-400">
                ✏️ Data telah dikoreksi oleh user — feedback disimpan untuk meningkatkan akurasi AI.
            </div>
            @endif

            @if($log->file_path)
                <img src="{{ Storage::url($log->file_path) }}" alt="Nota" class="max-h-48 rounded-xl object-contain border border-gray-200 dark:border-white/10">
            @endif

            @if($log->raw_input)
                <div class="mt-3 p-3 bg-gray-50 dark:bg-white/5 rounded-xl text-sm text-gray-700 dark:text-slate-300 border border-gray-200 dark:border-white/10">
                    {{ $log->raw_input }}
                </div>
            @endif
        </div>

        <!-- Extracted Data -->
        @if($log->extracted_data && $log->status !== 'failed')
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Data yang Diekstrak</h3>

                <form method="POST" action="{{ route('zero-input.confirm', $log) }}" class="space-y-3">
                    @csrf

                    @foreach($log->extracted_data as $key => $value)
                        @if($key === 'module' || $key === 'items') @continue @endif
                        <div class="flex items-center gap-3">
                            <label class="w-32 text-xs text-gray-500 dark:text-slate-400 capitalize shrink-0">
                                {{ str_replace('_', ' ', $key) }}
                            </label>
                            <input type="text" name="extracted_data[{{ $key }}]"
                                   value="{{ is_array($value) ? json_encode($value) : $value }}"
                                   class="flex-1 rounded-lg border-gray-300 dark:border-white/10 dark:bg-[#0f172a] dark:text-white text-sm">
                        </div>
                    @endforeach

                    @if(!empty($log->extracted_data['items']))
                        <div class="mt-3">
                            <p class="text-xs font-medium text-gray-500 dark:text-slate-400 mb-2">Item:</p>
                            <div class="space-y-1">
                                @foreach($log->extracted_data['items'] as $i => $item)
                                    <div class="flex gap-2 text-xs">
                                        <input type="text" name="extracted_data[items][{{ $i }}][name]"
                                               value="{{ $item['name'] ?? '' }}" placeholder="Nama"
                                               class="flex-1 rounded-lg border-gray-300 dark:border-white/10 dark:bg-[#0f172a] dark:text-white text-xs">
                                        <input type="number" name="extracted_data[items][{{ $i }}][qty]"
                                               value="{{ $item['qty'] ?? 1 }}" placeholder="Qty"
                                               class="w-16 rounded-lg border-gray-300 dark:border-white/10 dark:bg-[#0f172a] dark:text-white text-xs">
                                        <input type="number" name="extracted_data[items][{{ $i }}][price]"
                                               value="{{ $item['price'] ?? 0 }}" placeholder="Harga"
                                               class="w-28 rounded-lg border-gray-300 dark:border-white/10 dark:bg-[#0f172a] dark:text-white text-xs">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($log->status !== 'created' && $log->status !== 'rejected')
                        <div class="flex gap-3 pt-3">
                            <form method="POST" action="{{ route('zero-input.reject', $log) }}" class="contents">
                                @csrf
                                <button type="submit" onclick="return confirm('Tolak hasil OCR ini? Feedback akan disimpan.')"
                                    class="px-4 py-2 border border-red-300 dark:border-red-500/30 text-red-600 dark:text-red-400 rounded-xl text-sm hover:bg-red-50 dark:hover:bg-red-500/10">
                                    ✕ Tolak
                                </button>
                            </form>
                            <button type="submit"
                                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700">
                                ✅ Konfirmasi & Buat Record ERP
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-2">
                            💡 Edit field di atas jika ada yang salah. Koreksi Anda akan disimpan untuk meningkatkan akurasi AI.
                        </p>
                    @elseif($log->status === 'rejected')
                        <div class="p-3 bg-red-50 dark:bg-red-500/10 rounded-xl text-sm text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/20">
                            ✕ Hasil OCR ditolak. {{ $log->error_message }}
                        </div>
                    @else
                        <div class="p-3 bg-green-50 dark:bg-green-500/10 rounded-xl text-sm text-green-700 dark:text-green-400 border border-green-200 dark:border-green-500/20">
                            ✅ Record ERP sudah dibuat.
                            @if(!empty($log->created_records))
                                <span class="text-xs ml-1">({{ count($log->created_records) }} record)</span>
                            @endif
                            @if($log->was_corrected)
                                <span class="text-xs ml-1 text-amber-500">— data dikoreksi user</span>
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
