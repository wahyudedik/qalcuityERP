<x-app-layout>
    <x-slot name="header">Laporan Laboratorium</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Laboratorium', 'url' => route('healthcare.laboratory.orders')],
        ['label' => 'Laporan'],
    ]" />

    @php $tid = auth()->user()->tenant_id; @endphp

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-gray-200 mb-6">
        <div class="p-4">
            <form method="GET" class="flex flex-col lg:flex-row gap-3">
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                <select name="test_type"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Jenis Test</option>
                    <option value="blood_test" @selected(request('test_type') === 'blood_test')>Blood Test</option>
                    <option value="urine_test" @selected(request('test_type') === 'urine_test')>Urine Test</option>
                    <option value="cbc" @selected(request('test_type') === 'cbc')>CBC</option>
                    <option value="liver_function" @selected(request('test_type') === 'liver_function')>Liver Function</option>
                    <option value="kidney_function" @selected(request('test_type') === 'kidney_function')>Kidney Function</option>
                </select>
                <select name="status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Status</option>
                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                </select>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Generate</button>
            </form>
        </div>
    </div>

    {{-- Summary Stats --}}
    @if (request('start_date') || request('end_date'))
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            @php
                $query = \App\Models\LabOrder::where('tenant_id', $tid);
                if (request('start_date')) {
                    $query->whereDate('created_at', '>=', request('start_date'));
                }
                if (request('end_date')) {
                    $query->whereDate('created_at', '<=', request('end_date'));
                }
                if (request('test_type')) {
                    $query->where('test_type', request('test_type'));
                }
                if (request('status')) {
                    $query->where('status', request('status'));
                }
                $reports = $query->orderBy('created_at', 'desc')->get();

                $totalTests = $reports->count();
                $completedTests = $reports->where('status', 'completed')->count();
                $pendingTests = $reports->where('status', 'pending')->count();
                $abnormalResults = $reports
                    ->where('status', 'completed')
                    ->filter(function ($r) {
                        return isset($r->results['abnormal']) && $r->results['abnormal'];
                    })
                    ->count();
            @endphp
            <div class="bg-white rounded-2xl p-4 border border-gray-200">
                <p class="text-xs text-gray-500">Total Test</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalTests) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-200">
                <p class="text-xs text-gray-500">Selesai</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ $completedTests }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-200">
                <p class="text-xs text-gray-500">Pending</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $pendingTests }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-200">
                <p class="text-xs text-gray-500">Abnormal</p>
                <p class="text-2xl font-bold text-red-600 mt-1">{{ $abnormalResults }}</p>
            </div>
        </div>

        {{-- Reports Table --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Hasil Laporan</h3>
                <div class="flex items-center gap-2">
                    <x-disabled-button label="Export PDF" tooltip="Fitur akan segera tersedia"
                        class="px-4 py-2 text-sm rounded-xl" />
                    <x-disabled-button label="Export Excel" tooltip="Fitur akan segera tersedia"
                        class="px-4 py-2 text-sm rounded-xl" />
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">No. Order</th>
                            <th class="px-4 py-3 text-left">Pasien</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">Jenis Test</th>
                            <th class="px-4 py-3 text-left hidden lg:table-cell">Tanggal</th>
                            <th class="px-4 py-3 text-center hidden sm:table-cell">Hasil</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($reports ?? [] as $report)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <span
                                        class="font-mono text-sm font-bold text-blue-600">{{ $report->order_number ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900">
                                        {{ $report->patient ? $report->patient?->full_name : '-' }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $report->patient ? $report->patient?->medical_record_number : '-' }}</p>
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell">
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-purple-100 text-purple-700">
                                        {{ str_replace('_', ' ', ucfirst($report->test_type ?? '-')) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 hidden lg:table-cell">
                                    {{ $report->created_at ? $report->created_at->format('d M Y') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-center hidden sm:table-cell">
                                    @if ($report->status === 'completed' && isset($report->results['abnormal']))
                                        @if ($report->results['abnormal'])
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700">Abnormal</span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Normal</span>
                                        @endif
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($report->status === 'completed')
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Completed</span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700">Pending</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('healthcare.laboratory.reports.show', $report) }}"
                                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"
                                            title="Lihat Laporan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                        </a>
                                        <button onclick="printReport({{ $report->id }})"
                                            class="p-1.5 text-gray-600 hover:bg-gray-50 rounded-lg" title="Print">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    <p>Pilih filter dan klik Generate untuk melihat laporan</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            function printReport(id) {
                window.open(`/healthcare/laboratory/reports/${id}/print`, '_blank');
            }
        </script>
    @endpush
</x-app-layout>
