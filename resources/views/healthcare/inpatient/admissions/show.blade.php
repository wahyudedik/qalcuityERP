<x-app-layout>
    <x-slot name="header">Detail Rawat Inap - {{ $admission->patient?->full_name ?? 'Pasien' }}</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Rawat Inap', 'url' => route('healthcare.inpatient.admissions.index')],
        ['label' => 'Detail Rawat Inap'],
    ]" />

    {{-- Patient Info Banner --}}
    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl p-6 mb-6 text-white">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold">{{ $admission->patient?->full_name ?? '-' }}</h2>
                    <p class="text-sm text-white/80">
                        RM: {{ $admission->patient?->medical_record_number ?? '-' }} |
                        Kamar: {{ $admission->bed?->bed_number ?? '-' }} ({{ $admission->bed?->ward?->name ?? '-' }})
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                @if(in_array($admission->status, ['active', 'transferred']))
                    <a href="#" onclick="document.getElementById('modal-discharge').classList.remove('hidden')"
                        class="px-4 py-2 text-sm bg-white text-blue-600 rounded-xl hover:bg-white/90 font-medium">
                        Pulangkan Pasien
                    </a>
                @endif
                <a href="{{ route('healthcare.inpatient.admissions.index') }}"
                    class="px-4 py-2 text-sm bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl hover:bg-white/30">
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Admission Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Basic Info --}}
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Informasi Rawat Inap</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Tanggal Masuk</p>
                        <p class="text-sm font-medium text-gray-900 mt-1">
                            {{ $admission->admission_date ? \Carbon\Carbon::parse($admission->admission_date)->format('d M Y H:i') : '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Tipe Masuk</p>
                        <p class="text-sm font-medium text-gray-900 mt-1">
                            {{ ucfirst(str_replace('_', ' ', $admission->admission_type ?? '-')) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Dokter</p>
                        <p class="text-sm font-medium text-gray-900 mt-1">
                            {{ $admission->doctor?->name ?? '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Status</p>
                        <p class="mt-1">
                            @if($admission->status === 'active')
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Dirawat</span>
                            @elseif($admission->status === 'discharged')
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700">Dipulangkan</span>
                            @elseif($admission->status === 'transferred')
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700">Dipindahkan</span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700">{{ ucfirst($admission->status ?? '-') }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-500">Diagnosa Masuk</p>
                        <p class="text-sm text-gray-700 mt-1">
                            {{ $admission->admission_diagnosis ?? '-' }}
                        </p>
                    </div>
                    @if($admission->treatment_plan)
                        <div class="md:col-span-2">
                            <p class="text-xs text-gray-500">Rencana Perawatan</p>
                            <p class="text-sm text-gray-700 mt-1">{{ $admission->treatment_plan }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Ward Rounds --}}
            @if($admission->wardRounds && $admission->wardRounds->count() > 0)
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Visite Dokter</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @foreach($admission->wardRounds->take(5) as $round)
                            <div class="p-4">
                                <div class="flex items-start justify-between mb-2">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $round->round_date ? \Carbon\Carbon::parse($round->round_date)->format('d M Y H:i') : '-' }}
                                    </p>
                                </div>
                                <p class="text-sm text-gray-600">{{ $round->assessment ?? '-' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Bed Info --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Informasi Kamar</h4>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500">Ruang</p>
                        <p class="text-sm font-medium text-gray-900">{{ $admission->bed?->ward?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">No. Bed</p>
                        <p class="text-sm font-medium text-gray-900">{{ $admission->bed?->bed_number ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Kelas</p>
                        <p class="text-sm font-medium text-gray-900">{{ $admission->bed?->bed_class ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            @if(in_array($admission->status, ['active', 'transferred']))
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h4 class="text-sm font-semibold text-gray-900 mb-4">Aksi</h4>
                    <div class="space-y-2">
                        <form action="{{ route('healthcare.inpatient.admissions.transfer', $admission) }}" method="POST">
                            @csrf
                            <input type="hidden" name="new_bed_id" value="">
                            <input type="hidden" name="transfer_reason" value="Transfer request">
                            <button type="button"
                                onclick="document.getElementById('modal-transfer').classList.remove('hidden')"
                                class="w-full px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 text-left">
                                Pindah Kamar
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Discharge Modal --}}
    <div id="modal-discharge" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-lg">
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Pulangkan Pasien</h3>
                <button onclick="document.getElementById('modal-discharge').classList.add('hidden')"
                    class="p-2 hover:bg-gray-100 rounded-xl">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form action="{{ route('healthcare.inpatient.admissions.discharge', $admission) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Diagnosa Keluar <span class="text-red-500">*</span></label>
                    <textarea name="discharge_diagnosis" rows="2" required
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ringkasan Keluar <span class="text-red-500">*</span></label>
                    <textarea name="discharge_summary" rows="3" required
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status Keluar <span class="text-red-500">*</span></label>
                        <select name="discharge_status" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="recovered">Sembuh</option>
                            <option value="improved">Membaik</option>
                            <option value="unchanged">Tidak Berubah</option>
                            <option value="worsened">Memburuk</option>
                            <option value="referred">Dirujuk</option>
                            <option value="ama">Atas Permintaan Sendiri</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Keluar <span class="text-red-500">*</span></label>
                        <select name="discharge_type" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="normal">Normal</option>
                            <option value="transfer">Transfer</option>
                            <option value="against_medical_advice">APS</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-discharge').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Pulangkan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
