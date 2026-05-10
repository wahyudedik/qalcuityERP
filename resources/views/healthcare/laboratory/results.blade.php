<x-app-layout>
    <x-slot name="header">Input Hasil Laboratorium</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Laboratorium', 'url' => route('healthcare.laboratory.orders')],
        ['label' => 'Input Hasil'],
    ]" />

    @php $tid = auth()->user()->tenant_id; @endphp

    @if (!isset($order))
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
                <div>
                    <p class="font-bold text-amber-800">Pilih Pesanan Lab</p>
                    <p class="text-sm text-amber-700 mt-1">Silakan pilih pesanan laboratorium dari
                        daftar untuk menginput hasil.</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Order Info --}}
    @if (isset($order))
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-purple-50">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Order:
                            {{ $order->order_number ?? '-' }}</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ $order->patient ? $order->patient?->full_name : '-' }} |
                            {{ $order->patient ? $order->patient?->medical_record_number : '-' }}</p>
                    </div>
                    <a href="{{ route('healthcare.laboratory.orders') }}"
                        class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50">
                        Kembali
                    </a>
                </div>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-500">Jenis Test</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">
                        {{ str_replace('_', ' ', ucfirst($order->test_type ?? '-')) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Dokter</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">
                        {{ $order->doctor ? $order->doctor?->name : '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Tanggal Order</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">
                        {{ $order->created_at ? $order->created_at->format('d M Y H:i') : '-' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Results Entry Form --}}
        <form action="{{ route('healthcare.laboratory.results.store', $order) }}" method="POST" class="space-y-6">
            @csrf

            {{-- Test Results --}}
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Hasil Test</h3>
                </div>
                <div class="p-6 space-y-4">
                    @if ($order->test_type === 'blood_test')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Hemoglobin
                                    (g/dL)</label>
                                <input type="number" step="0.1" name="results[hb]" value="{{ old('results.hb') }}"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Normal: 12-16 g/dL</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Hematocrit
                                    (%)</label>
                                <input type="number" step="0.1" name="results[hct]"
                                    value="{{ old('results.hct') }}"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Normal: 36-46%</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">WBC
                                    (10³/μL)</label>
                                <input type="number" step="0.1" name="results[wbc]"
                                    value="{{ old('results.wbc') }}"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Normal: 4.5-11.0</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Platelet
                                    (10³/μL)</label>
                                <input type="number" step="0.1" name="results[platelet]"
                                    value="{{ old('results.platelet') }}"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Normal: 150-400</p>
                            </div>
                        </div>
                    @elseif($order->test_type === 'liver_function')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">SGOT/AST
                                    (U/L)</label>
                                <input type="number" step="0.1" name="results[sgot]"
                                    value="{{ old('results.sgot') }}"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Normal: 10-40 U/L</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">SGPT/ALT
                                    (U/L)</label>
                                <input type="number" step="0.1" name="results[sgpt]"
                                    value="{{ old('results.sgpt') }}"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Normal: 7-56 U/L</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Bilirubin
                                    Total (mg/dL)</label>
                                <input type="number" step="0.01" name="results[bilirubin]"
                                    value="{{ old('results.bilirubin') }}"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Normal: 0.1-1.2 mg/dL</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Albumin
                                    (g/dL)</label>
                                <input type="number" step="0.1" name="results[albumin]"
                                    value="{{ old('results.albumin') }}"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Normal: 3.5-5.5 g/dL</p>
                            </div>
                        </div>
                    @else
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hasil Test
                                (JSON)</label>
                            <textarea name="results[custom]" rows="6"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder='{"parameter": "value"}'>{{ old('results.custom') }}</textarea>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Notes & Interpretation --}}
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Catatan & Interpretasi</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Interpretasi
                            Hasil</label>
                        <textarea name="interpretation" rows="3"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Interpretasi hasil test...">{{ old('interpretation') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan
                            Lab</label>
                        <textarea name="notes" rows="3"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('healthcare.laboratory.orders') }}"
                    class="px-6 py-2.5 text-sm border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-2.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium">
                    Simpan Hasil
                </button>
            </div>
        </form>
    @endif
</x-app-layout>
