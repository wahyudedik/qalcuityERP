<x-app-layout>
    <x-slot name="header">Antrian Resep Farmasi</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Farmasi'],
    ]" />

    @php $tid = auth()->user()->tenant_id; @endphp

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @php
            $totalPrescriptions = \App\Models\Prescription::where('tenant_id', $tid)->count();
            $pendingPrescriptions = \App\Models\Prescription::where('tenant_id', $tid)
                ->where('status', 'pending')
                ->count();
            $processingPrescriptions = \App\Models\Prescription::where('tenant_id', $tid)
                ->where('status', 'processing')
                ->count();
            $completedToday = \App\Models\Prescription::where('tenant_id', $tid)
                ->where('status', 'completed')
                ->whereDate('completed_at', today())
                ->count();
        @endphp
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Resep</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalPrescriptions) }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Menunggu</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $pendingPrescriptions }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Diproses</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $processingPrescriptions }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Selesai Hari Ini</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $completedToday }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-gray-200 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari pasien / No. resep..."
                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Status</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="processing" @selected(request('status') === 'processing')>Processing</option>
                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                </select>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
            </form>
        </div>
    </div>

    {{-- Prescriptions Queue --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No. Resep</th>
                        <th class="px-4 py-3 text-left">Pasien</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Dokter</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($prescriptions ?? [] as $prescription)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span
                                    class="font-mono text-sm font-bold text-blue-600">{{ $prescription->prescription_number ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">
                                    {{ $prescription->patient ? $prescription->patient->full_name : '-' }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $prescription->patient ? $prescription->patient->medical_record_number : '-' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <p class="text-gray-900">
                                    {{ $prescription->doctor ? $prescription->doctor->name : '-' }}</p>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <p class="text-gray-900">
                                    {{ $prescription->created_at ? \Carbon\Carbon::parse($prescription->created_at)->format('d M Y') : '-' }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $prescription->created_at ? \Carbon\Carbon::parse($prescription->created_at)->format('H:i') : '-' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                @if ($prescription->status === 'pending')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700">Pending</span>
                                @elseif($prescription->status === 'processing')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700">Processing</span>
                                @elseif($prescription->status === 'completed')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Completed</span>
                                @elseif($prescription->status === 'cancelled')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700">Cancelled</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('healthcare.pharmacy.prescriptions.show', $prescription) }}"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"
                                        title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </a>
                                    @if ($prescription->status === 'pending')
                                        <button onclick="processPrescription({{ $prescription->id }})"
                                            class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg"
                                            title="Proses">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </button>
                                    @elseif($prescription->status === 'processing')
                                        <button onclick="completePrescription({{ $prescription->id }})"
                                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"
                                            title="Selesai">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                <p>Belum ada resep</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card View (< 768px) --}}
        <div class="md:hidden divide-y divide-gray-100">
            @forelse($prescriptions ?? [] as $prescription)
                <div class="p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex-1 min-w-0">
                            <p class="font-mono text-sm font-bold text-blue-600">
                                {{ $prescription->prescription_number ?? '-' }}</p>
                            <p class="font-semibold text-gray-900 truncate mt-0.5">
                                {{ $prescription->patient ? $prescription->patient->full_name : '-' }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $prescription->patient ? $prescription->patient->medical_record_number : '-' }}
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            @if ($prescription->status === 'pending')
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700">Pending</span>
                            @elseif($prescription->status === 'processing')
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700">Processing</span>
                            @elseif($prescription->status === 'completed')
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Completed</span>
                            @elseif($prescription->status === 'cancelled')
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700">Cancelled</span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                        <div>
                            <p class="text-gray-500">Dokter</p>
                            <p class="font-medium text-gray-900">
                                {{ $prescription->doctor ? $prescription->doctor->name : '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Tanggal</p>
                            <p class="font-medium text-gray-900">
                                {{ $prescription->created_at ? \Carbon\Carbon::parse($prescription->created_at)->format('d M Y') : '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                        <a href="{{ route('healthcare.pharmacy.prescriptions.show', $prescription) }}"
                            class="flex-1 px-3 py-2 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg text-center hover:bg-blue-100">
                            Detail
                        </a>
                        @if ($prescription->status === 'pending')
                            <button onclick="processPrescription({{ $prescription->id }})"
                                class="flex-1 px-3 py-2 text-xs font-medium text-amber-600 bg-amber-50 rounded-lg hover:bg-amber-100">
                                Proses
                            </button>
                        @elseif($prescription->status === 'processing')
                            <button onclick="completePrescription({{ $prescription->id }})"
                                class="flex-1 px-3 py-2 text-xs font-medium text-green-600 bg-green-50 rounded-lg hover:bg-green-100">
                                Selesai
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                    </svg>
                    <p>Belum ada resep</p>
                </div>
            @endforelse
        </div>

        @if (isset($prescriptions) && $prescriptions->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $prescriptions->links() }}
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            function processPrescription(id) {
                if (confirm('Proses resep ini?')) {
                    fetch(`/healthcare/pharmacy/prescriptions/${id}/process`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).then(() => location.reload());
                }
            }

            function completePrescription(id) {
                if (confirm('Tandai resep selesai?')) {
                    fetch(`/healthcare/pharmacy/prescriptions/${id}/complete`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).then(() => location.reload());
                }
            }
        </script>
    @endpush
</x-app-layout>
