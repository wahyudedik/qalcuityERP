<x-app-layout>
    <x-slot name="header">Pengaturan AI Routing</x-slot>

    <div class="max-w-6xl mx-auto space-y-4" x-data="aiRoutingSettings()">

        {{-- Flash success --}}
        @if (session('success'))
            <div
                class="flex items-center gap-3 bg-green-500/10 border border-green-500/30 text-green-400 rounded-2xl px-4 py-3 text-sm mb-4">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Errors --}}
        @if ($errors->any())
            <div
                class="flex items-start gap-3 bg-red-500/10 border border-red-500/30 text-red-400 rounded-2xl px-4 py-3 text-sm mb-4">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.5"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <div>
                    <p class="font-medium text-red-300">Terjadi kesalahan:</p>
                    <ul class="list-disc list-inside mt-1 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Info banner --}}
        <div
            class="flex items-start gap-3 bg-violet-500/10 border border-violet-500/20 rounded-2xl px-4 py-3 text-sm text-violet-400 mb-4">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.5"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
            </svg>
            <div>
                <p class="font-medium text-violet-300">Routing AI per Use Case</p>
                <p class="text-violet-400/80 mt-0.5">Anda dapat meng-override routing rule global untuk use case
                    tertentu. Override akan menggunakan provider dan model yang Anda pilih, berbeda dari konfigurasi
                    platform. Plan Anda saat ini: <strong class="text-violet-300">{{ ucfirst($tenantPlan) }}</strong>
                </p>
            </div>
        </div>

        {{-- Routing Rules Table --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-violet-100">
                        <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-gray-900 text-sm">Routing Rules yang Berlaku</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Global rules + override tenant Anda</p>
                    </div>
                </div>
                <button type="button" @click="showOverrideForm = !showOverrideForm"
                    class="px-4 py-2 bg-violet-600 hover:bg-violet-500 text-white rounded-xl text-sm font-medium transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span x-text="showOverrideForm ? 'Tutup Form' : 'Buat Override'"></span>
                </button>
            </div>

            {{-- Override Form (collapsible) --}}
            <div x-show="showOverrideForm" x-collapse style="display:none"
                class="px-6 py-5 bg-violet-50/50 border-b border-violet-100">
                <form method="POST" action="{{ route('settings.ai-routing.store') }}" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Use Case --}}
                        <div>
                            <label for="use_case" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Use Case
                            </label>
                            <select id="use_case" name="use_case" required
                                class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500/40 focus:border-violet-500 transition">
                                <option value="">Pilih use case...</option>
                                @foreach ($useCases as $useCase)
                                    <option value="{{ $useCase->value }}">
                                        {{ ucwords(str_replace('_', ' ', $useCase->value)) }}
                                        @if ($useCase->isHeavyweight())
                                            (Heavyweight)
                                        @else
                                            (Lightweight)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-400 mt-1.5">Pilih use case yang ingin Anda override</p>
                        </div>

                        {{-- Provider --}}
                        <div>
                            <label for="provider" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Provider
                            </label>
                            <select id="provider" name="provider" required
                                class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500/40 focus:border-violet-500 transition">
                                <option value="">Pilih provider...</option>
                                @foreach ($availableProviders as $provider)
                                    <option value="{{ $provider }}">
                                        {{ $provider === 'gemini' ? 'Gemini (Google)' : 'Anthropic (Claude)' }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-400 mt-1.5">Provider yang tersedia untuk plan Anda</p>
                        </div>
                    </div>

                    {{-- Model (optional) --}}
                    <div>
                        <label for="model" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Model (Opsional)
                        </label>
                        <input type="text" id="model" name="model" placeholder="Contoh: gemini-2.5-flash"
                            class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-violet-500/40 focus:border-violet-500 transition">
                        <p class="text-xs text-gray-400 mt-1.5">Kosongkan untuk menggunakan model default provider</p>
                    </div>

                    {{-- Description (optional) --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Deskripsi (Opsional)
                        </label>
                        <textarea id="description" name="description" rows="2" placeholder="Catatan tentang override ini..."
                            class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-violet-500/40 focus:border-violet-500 transition"></textarea>
                    </div>

                    {{-- Submit --}}
                    <div class="flex items-center justify-end gap-3 pt-2 border-t border-violet-100">
                        <button type="button" @click="showOverrideForm = false"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-medium transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-6 py-2 bg-violet-600 hover:bg-violet-500 text-white rounded-xl text-sm font-medium transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Simpan Override
                        </button>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Use Case
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Provider
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Model
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Biaya Bulan Ini
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($routingRules as $rule)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ ucwords(str_replace('_', ' ', $rule->use_case)) }}
                                        </span>
                                        @if (isset($rule->is_override) && $rule->is_override)
                                            <span
                                                class="px-2 py-0.5 bg-amber-100 text-amber-700 text-xs rounded-full font-medium">
                                                Override Aktif
                                            </span>
                                        @endif
                                    </div>
                                    @if ($rule->description)
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $rule->description }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-700">
                                        {{ $rule->provider === 'gemini' ? 'Gemini' : 'Anthropic' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-600 font-mono">
                                        {{ $rule->model ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $cost = $costByUseCase->get($rule->use_case);
                                    @endphp
                                    @if ($cost)
                                        <div class="text-sm">
                                            <span class="font-medium text-gray-900">
                                                Rp {{ number_format($cost->total_cost, 2, ',', '.') }}
                                            </span>
                                            <span class="text-xs text-gray-400 block">
                                                {{ number_format($cost->request_count) }} request
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($rule->is_active)
                                        <span
                                            class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full font-medium">
                                            Aktif
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-1 bg-gray-100 text-gray-500 text-xs rounded-full font-medium">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    @if (isset($rule->is_override) && $rule->is_override)
                                        <form method="POST"
                                            action="{{ route('settings.ai-routing.destroy', $rule->id) }}"
                                            class="inline"
                                            data-confirm="Hapus override untuk {{ $rule->use_case }}? Akan kembali menggunakan routing rule global." data-confirm-type="danger">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-500 text-sm font-medium transition">
                                                Hapus Override
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-400">Global Rule</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">
                                    Tidak ada routing rules yang tersedia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Info tentang plan --}}
        @if (!in_array($tenantPlan, ['professional', 'enterprise']))
            <div
                class="flex items-start gap-3 bg-blue-500/10 border border-blue-500/20 rounded-2xl px-4 py-3 text-sm text-blue-400">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.5"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <div>
                    <p class="font-medium text-blue-300">Upgrade untuk Akses Lebih Banyak Provider</p>
                    <p class="text-blue-400/80 mt-0.5">
                        Plan Anda saat ini ({{ ucfirst($tenantPlan) }}) hanya dapat menggunakan provider Gemini.
                        Upgrade ke plan <strong>Professional</strong> atau <strong>Enterprise</strong> untuk mengakses
                        Anthropic Claude dan fitur analitik berat lainnya.
                    </p>
                </div>
            </div>
        @endif

    </div>

    @push('scripts')
        <script>
            function aiRoutingSettings() {
                return {
                    showOverrideForm: false,
                };
            }
        </script>
    @endpush
</x-app-layout>
