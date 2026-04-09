<x-app-layout>
    <x-slot name="header">Medical Records</x-slot>

    @php
        $patient = auth()->user()->patient;
        $tid = auth()->user()->tenant_id;
    @endphp

    @if (!$patient)
        <div
            class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/30 rounded-2xl p-6 text-center">
            <p class="text-sm text-red-700 dark:text-red-300">Patient profile not found. Please contact reception.</p>
        </div>
    @else
        {{-- Tabs --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 mb-6">
            <div class="border-b border-gray-200 dark:border-white/10">
                <nav class="flex -mb-px">
                    <button onclick="switchTab('visits')" id="tab-visits"
                        class="tab-btn active px-6 py-4 text-sm font-medium border-b-2 border-blue-600 text-blue-600 dark:text-blue-400">
                        Visit History
                    </button>
                    <button onclick="switchTab('diagnoses')" id="tab-diagnoses"
                        class="tab-btn px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-200">
                        Diagnoses
                    </button>
                    <button onclick="switchTab('prescriptions')" id="tab-prescriptions"
                        class="tab-btn px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-200">
                        Prescriptions
                    </button>
                    <button onclick="switchTab('lab-results')" id="tab-lab-results"
                        class="tab-btn px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-200">
                        Lab Results
                    </button>
                </nav>
            </div>
        </div>

        {{-- Visit History Tab --}}
        <div id="content-visits" class="tab-content">
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left hidden md:table-cell">Dokter</th>
                                <th class="px-4 py-3 text-left hidden lg:table-cell">Department</th>
                                <th class="px-4 py-3 text-left hidden sm:table-cell">Keluhan</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @php
                                $visits = \App\Models\OutpatientVisit::where('patient_id', $patient->id)
                                    ->orderBy('visit_date', 'desc')
                                    ->paginate(10);
                            @endphp
                            @forelse($visits as $visit)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="px-4 py-3">
                                        <p class="text-gray-900 dark:text-white">
                                            {{ $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') : '-' }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-slate-400">
                                            {{ $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('H:i') : '-' }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-slate-300 hidden md:table-cell">
                                        {{ $visit->doctor ? $visit->doctor->name : '-' }}</td>
                                    <td class="px-4 py-3 hidden lg:table-cell">
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                            {{ $visit->department ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-slate-300 hidden sm:table-cell">
                                        {{ Str::limit($visit->chief_complaint ?? '-', 50) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                            {{ ucfirst($visit->status ?? 'Completed') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-slate-400">
                                        <p>Belum ada riwayat kunjungan</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($visits->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                        {{ $visits->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Diagnoses Tab --}}
        <div id="content-diagnoses" class="tab-content hidden">
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left">Kode ICD-10</th>
                                <th class="px-4 py-3 text-left hidden md:table-cell">Diagnosa</th>
                                <th class="px-4 py-3 text-left hidden lg:table-cell">Dokter</th>
                                <th class="px-4 py-3 text-center">Tipe</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @php
                                $diagnoses = \App\Models\Diagnosis::where('patient_id', $patient->id)
                                    ->orderBy('diagnosis_date', 'desc')
                                    ->paginate(10);
                            @endphp
                            @forelse($diagnoses as $diagnosis)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="px-4 py-3 text-gray-900 dark:text-white">
                                        {{ $diagnosis->diagnosis_date ? \Carbon\Carbon::parse($diagnosis->diagnosis_date)->format('d M Y') : '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="font-mono text-sm font-bold text-blue-600 dark:text-blue-400">{{ $diagnosis->icd_code ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-white hidden md:table-cell">
                                        {{ $diagnosis->description ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-slate-300 hidden lg:table-cell">
                                        {{ $diagnosis->doctor ? $diagnosis->doctor->name : '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($diagnosis->diagnosis_type === 'primary')
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Primary</span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">Secondary</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-slate-400">
                                        <p>Belum ada diagnosa</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($diagnoses->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                        {{ $diagnoses->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Prescriptions Tab --}}
        <div id="content-prescriptions" class="tab-content hidden">
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left hidden md:table-cell">Dokter</th>
                                <th class="px-4 py-3 text-left">Obat</th>
                                <th class="px-4 py-3 text-center hidden sm:table-cell">Dosis</th>
                                <th class="px-4 py-3 text-center hidden lg:table-cell">Durasi</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @php
                                $prescriptions = \App\Models\Prescription::where('patient_id', $patient->id)
                                    ->orderBy('prescription_date', 'desc')
                                    ->paginate(10);
                            @endphp
                            @forelse($prescriptions as $prescription)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="px-4 py-3 text-gray-900 dark:text-white">
                                        {{ $prescription->prescription_date ? \Carbon\Carbon::parse($prescription->prescription_date)->format('d M Y') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-slate-300 hidden md:table-cell">
                                        {{ $prescription->doctor ? $prescription->doctor->name : '-' }}</td>
                                    <td class="px-4 py-3">
                                        <p class="text-gray-900 dark:text-white">
                                            {{ $prescription->medication_name ?? '-' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-slate-400">
                                            {{ $prescription->notes ?? '' }}</p>
                                    </td>
                                    <td
                                        class="px-4 py-3 text-center text-gray-600 dark:text-slate-300 hidden sm:table-cell">
                                        {{ $prescription->dosage ?? '-' }}</td>
                                    <td
                                        class="px-4 py-3 text-center text-gray-600 dark:text-slate-300 hidden lg:table-cell">
                                        {{ $prescription->duration ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($prescription->status === 'active')
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Active</span>
                                        @elseif($prescription->status === 'completed')
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">Completed</span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Cancelled</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-slate-400">
                                        <p>Belum ada resep</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($prescriptions->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                        {{ $prescriptions->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Lab Results Tab --}}
        <div id="content-lab-results" class="tab-content hidden">
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left">Test Name</th>
                                <th class="px-4 py-3 text-left hidden md:table-cell">Category</th>
                                <th class="px-4 py-3 text-center hidden sm:table-cell">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @php
                                $labResults = \App\Models\LabResult::where('patient_id', $patient->id)
                                    ->orderBy('result_date', 'desc')
                                    ->paginate(10);
                            @endphp
                            @forelse($labResults as $result)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="px-4 py-3 text-gray-900 dark:text-white">
                                        {{ $result->result_date ? \Carbon\Carbon::parse($result->result_date)->format('d M Y') : '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ $result->test_name ?? '-' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-slate-400">
                                            {{ $result->lab_order ? $result->lab_order->order_number : '-' }}</p>
                                    </td>
                                    <td class="px-4 py-3 hidden md:table-cell">
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                                            {{ $result->category ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center hidden sm:table-cell">
                                        @if ($result->status === 'completed')
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Completed</span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($result->status === 'completed')
                                            <button
                                                class="p-1.5 text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30 rounded-lg"
                                                title="View Results">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                    </path>
                                                </svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"
                                        class="px-4 py-8 text-center text-gray-500 dark:text-slate-400">
                                        <p>Belum ada hasil lab</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($labResults->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                        {{ $labResults->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            function switchTab(tabName) {
                // Hide all content
                document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.tab-btn').forEach(el => {
                    el.classList.remove('active', 'border-blue-600', 'text-blue-600', 'dark:text-blue-400');
                    el.classList.add('border-transparent', 'text-gray-500');
                });

                // Show selected content
                document.getElementById('content-' + tabName).classList.remove('hidden');
                const activeBtn = document.getElementById('tab-' + tabName);
                activeBtn.classList.add('active', 'border-blue-600', 'text-blue-600', 'dark:text-blue-400');
                activeBtn.classList.remove('border-transparent', 'text-gray-500');
            }
        </script>
    @endpush
</x-app-layout>
