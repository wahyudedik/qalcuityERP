<x-app-layout>
    <x-slot name="title">Edit Routing Rule — {{ $route->use_case }} — Qalcuity ERP</x-slot>
    <x-slot name="header">Edit Routing Rule — {{ $route->use_case }}</x-slot>

    @if (session('success'))
        <div
            class="mb-4 p-4 bg-green-50 border border-green-200 rounded-2xl text-sm text-green-700 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-2xl text-sm text-red-700 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            {{ session('error') }}
        </div>
    @endif

    @if (session('warning'))
        <div
            class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-2xl text-sm text-amber-700 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            {{ session('warning') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-2xl text-sm text-red-700">
            <p class="font-semibold mb-2">Terdapat kesalahan:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-4">
        <a href="{{ route('super-admin.ai.routing.index') }}"
            class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar Routing Rules
        </a>
    </div>

    <form method="POST" action="{{ route('super-admin.ai.routing.update', $route) }}" x-data="{
        selectedProvider: '{{ old('provider', $route->provider) }}',
        knownModels: {
            'gemini': ['gemini-2.5-flash', 'gemini-2.5-flash-lite', 'gemini-1.5-flash', 'gemini-1.5-pro'],
            'anthropic': ['claude-3-5-sonnet-20241022', 'claude-3-haiku-20240307', 'claude-3-opus-20240229']
        },
        modelInput: '{{ old('model', $route->model) }}',
        showModelWarning: false,
        checkModelValidity() {
            if (!this.modelInput) {
                this.showModelWarning = false;
                return;
            }
            const models = this.knownModels[this.selectedProvider] || [];
            this.showModelWarning = !models.includes(this.modelInput);
        }
    }"
        x-init="checkModelValidity()">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-6">
            {{-- Header --}}
            <div>
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    Konfigurasi Routing Rule
                </h2>
                <p class="text-xs text-gray-400 mt-1">
                    Edit routing rule untuk use case <span
                        class="font-semibold text-gray-600">{{ $route->use_case }}</span>
                </p>
            </div>

            {{-- Provider Status Info --}}
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">Status Provider</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ($providerStatus as $status)
                        <div class="flex items-center justify-between bg-white rounded-lg p-3 border border-gray-200">
                            <span class="text-sm font-medium text-gray-900">{{ $status['label'] }}</span>
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-2 h-2 rounded-full {{ $status['status_color'] === 'green' ? 'bg-green-500' : ($status['status_color'] === 'amber' ? 'bg-amber-500' : 'bg-gray-400') }}"></span>
                                <span
                                    class="text-xs font-medium {{ $status['status_color'] === 'green' ? 'text-green-600' : ($status['status_color'] === 'amber' ? 'text-amber-600' : 'text-gray-500') }}">
                                    {{ $status['status_label'] }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Form Fields --}}
            <div class="grid md:grid-cols-2 gap-4">
                {{-- Provider --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                        Provider <span class="text-red-400">*</span>
                    </label>
                    <select name="provider" x-model="selectedProvider" @change="checkModelValidity()" required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach ($availableProviders as $provider)
                            <option value="{{ $provider }}" @selected(old('provider', $route->provider) === $provider)>
                                {{ ucfirst($provider) }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Provider AI yang akan digunakan untuk use case ini</p>
                </div>

                {{-- Model --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                        Model
                    </label>
                    <input type="text" name="model" x-model="modelInput" @input="checkModelValidity()"
                        value="{{ old('model', $route->model) }}" placeholder="Kosongkan untuk model default provider"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Model spesifik yang akan digunakan (opsional)</p>

                    {{-- Model Warning --}}
                    <div x-show="showModelWarning" x-transition
                        class="mt-2 p-2 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-700 flex items-start gap-2">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Model ini tidak dikenal untuk provider yang dipilih. Pastikan model valid sebelum
                            menyimpan.</span>
                    </div>
                </div>

                {{-- Min Plan --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                        Min Plan
                    </label>
                    <select name="min_plan"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Plan</option>
                        @foreach ($availablePlans as $plan)
                            <option value="{{ $plan }}" @selected(old('min_plan', $route->min_plan) === $plan)>
                                {{ ucfirst($plan) }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Plan minimum yang diperlukan untuk menggunakan use case ini
                    </p>
                </div>

                {{-- Fallback Chain --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                        Fallback Chain
                    </label>
                    <input type="text" name="fallback_chain"
                        value="{{ old('fallback_chain', is_array($route->fallback_chain) ? implode(', ', $route->fallback_chain) : '') }}"
                        placeholder="gemini, anthropic"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Urutan provider fallback (comma-separated). Kosongkan untuk
                        default.</p>
                </div>
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                    Deskripsi
                </label>
                <textarea name="description" rows="3" placeholder="Deskripsi use case dan tujuan routing rule ini..."
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('description', $route->description) }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Deskripsi opsional untuk dokumentasi internal</p>
            </div>

            {{-- Is Active Toggle --}}
            <div class="border-t border-gray-100 pt-4">
                <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-4">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                        @checked(old('is_active', $route->is_active) == 1) class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                    <label for="is_active" class="text-sm text-gray-700">
                        <span class="font-semibold">Aktifkan routing rule</span>
                        <span class="block text-xs text-gray-400 mt-0.5">Jika dinonaktifkan, sistem akan menggunakan
                            routing rule global default</span>
                    </label>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <a href="{{ route('super-admin.ai.routing.index') }}"
                    class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>

    {{-- Info Box --}}
    <div class="mt-6 bg-blue-50 rounded-2xl border border-blue-200 p-5">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-sm text-blue-700">
                <p class="font-semibold mb-1">Catatan Penting:</p>
                <ul class="list-disc list-inside space-y-1 text-xs">
                    <li>Perubahan routing rule akan berlaku segera setelah disimpan</li>
                    <li>Cache routing rules akan di-invalidate otomatis</li>
                    <li>Model yang tidak dikenal akan menampilkan warning tetapi tetap dapat disimpan</li>
                    <li>Fallback chain digunakan ketika provider utama tidak tersedia</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
