<x-app-layout>
    <x-slot name="header">Zero Input ERP</x-slot>

    <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if(session('success'))
            <div class="p-3 bg-green-100 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-3 bg-red-100 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
        @endif

        <!-- Input Methods -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Foto Nota -->
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-900 mb-1">📷 Foto Nota / Struk</h3>
                <p class="text-xs text-gray-500 mb-4">Upload foto nota, kwitansi, atau dokumen. AI akan mengekstrak data otomatis.</p>
                <form method="POST" action="{{ route('zero-input.photo') }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center cursor-pointer hover:border-blue-400 transition"
                         onclick="document.getElementById('photo-input').click()">
                        <input type="file" id="photo-input" name="photo" accept="image/*,.pdf" class="hidden"
                               onchange="this.closest('form').querySelector('.file-name').textContent = this.files[0]?.name || 'Pilih file'">
                        <p class="text-sm text-gray-500">📎 Klik untuk pilih foto</p>
                        <p class="file-name text-xs text-blue-500 mt-1">JPG, PNG, PDF — maks 10MB</p>
                    </div>
                    <button type="submit"
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                        Proses Foto
                    </button>
                </form>
            </div>

            <!-- Teks / Voice / WhatsApp -->
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-900 mb-1">💬 Teks / Voice / WhatsApp</h3>
                <p class="text-xs text-gray-500 mb-4">Ketik atau paste teks transaksi. AI akan mapping ke modul yang tepat.</p>
                <form method="POST" action="{{ route('zero-input.text') }}" class="space-y-3">
                    @csrf
                    <select name="channel" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="manual">Manual / Ketik</option>
                        <option value="voice">Voice Transcript</option>
                        <option value="whatsapp">WhatsApp</option>
                    </select>
                    <textarea name="text" rows="4" required
                              placeholder="Contoh: beli bahan baku tepung 50kg dari PT Maju seharga 500rb, bayar cash hari ini"
                              class="w-full rounded-lg border-gray-300 text-sm"></textarea>
                    <button type="submit"
                            class="w-full px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">
                        Proses Teks
                    </button>
                </form>
            </div>
        </div>

        <!-- Log History -->
        <div class="bg-white rounded-2xl border border-gray-200">
            <div class="p-5 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Riwayat Input</h3>
            </div>
            @if($logs->isEmpty())
                <div class="p-8 text-center text-gray-500 text-sm">Belum ada riwayat input.</div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($logs as $log)
                        @php
                            $channelIcon = match($log->channel) {
                                'photo' => '📷', 'voice' => '🎤', 'whatsapp' => '💬', default => '📝'
                            };
                            $statusClass = match($log->status) {
                                'mapped', 'created' => 'bg-green-100 text-green-700',
                                'failed' => 'bg-red-100 text-red-700',
                                default => 'bg-yellow-100 text-yellow-700',
                            };
                        @endphp
                        <div class="p-4 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <span class="text-xl">{{ $channelIcon }}</span>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">
                                        {{ $log->mapped_module ? ucfirst(str_replace('_', ' ', $log->mapped_module)) : 'Belum dipetakan' }}
                                    </p>
                                    <p class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($log->confidence_score)
                                <span class="text-xs text-gray-400 tabular-nums">{{ $log->confidence_score }}%</span>
                                @endif
                                @if($log->was_corrected)
                                <span class="text-xs px-1.5 py-0.5 rounded bg-amber-100 text-amber-600">✏️</span>
                                @endif
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $statusClass }}">{{ $log->status }}</span>
                                <a href="{{ route('zero-input.show', $log) }}"
                                   class="text-xs text-blue-500 hover:text-blue-700">Detail</a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="p-4">{{ $logs->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
