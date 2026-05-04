<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-file-medical text-blue-600"></i> Radiology Reports
            </h1>
            <p class="text-gray-500">Radiologist interpretation reports</p>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Report #</th>
                                    <th>Exam Date</th>
                                    <th>Patient</th>
                                    <th>Exam Type</th>
                                    <th>Radiologist</th>
                                    <th>Findings</th>
                                    <th>Impression</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports as $report)
                                    <tr>
                                        <td><code>{{ $report->report_number }}</code></td>
                                        <td>{{ $report->exam_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('healthcare.patients.show', $report->patient) }}">
                                                {{ $report->patient?->name ?? '-' }}
                                            </a>
                                        </td>
                                        <td>{{ $report->exam_type ?? '-' }}</td>
                                        <td>{{ $report->radiologist?->name ?? '-' }}</td>
                                        <td><small>{{ Str::limit($report->findings, 40) ?? '-' }}</small></td>
                                        <td><small>{{ Str::limit($report->impression, 40) ?? '-' }}</small></td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'draft' => 'secondary',
                                                    'preliminary' => 'warning',
                                                    'final' => 'success',
                                                    'amended' => 'info',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$report->status] ?? 'secondary'  }}">
                                                {{ ucfirst($report->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex gap-1">
                                                <button class="px-3 py-1.5 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-xs transition" data-bs-toggle="modal"
                                                    data-bs-target="#viewReportModal{{ $report->id }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if ($report->status == 'draft' || $report->status == 'preliminary')
                                                    <button class="px-3 py-1.5 border border-emerald-500 text-emerald-600 hover:bg-emerald-50 rounded-lg text-xs transition">
                                                        <i class="fas fa-check"></i> Finalize
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- View Report Modal -->
                                    <div class="modal fade" id="viewReportModal{{ $report->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-xl">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Radiology Report - {{ $report->report_number }}
                                                    </h5>
                                                    <button type="button" class="text-gray-400 hover:text-gray-600"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Patient:</strong> {{ $report->patient?->name ?? '-' }}
                                                            <br><strong>Exam Type:</strong> {{ $report->exam_type ?? '-' }}
                                                            <br><strong>Exam Date:</strong>
                                                            {{ $report->exam_date?->format('d/m/Y H:i') ?? '-' }}
                                                        </div>
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Radiologist:</strong>
                                                            {{ $report->radiologist?->name ?? '-' }}
                                                            <br><strong>Report Date:</strong>
                                                            {{ $report->created_at->format('d/m/Y H:i') }}
                                                            <br><strong>Status:</strong>
                                                            <span
                                                                class="badge bg-{{ $statusColors[$report->status] ?? 'secondary'  }}">
                                                                {{ ucfirst($report->status) }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <hr>

                                                    <div class="mb-3">
                                                        <h6 class="text-blue-600">Clinical History</h6>
                                                        <p class="bg-gray-50 p-3 rounded">
                                                            {{ $report->clinical_history ?? 'N/A' }}</p>
                                                    </div>

                                                    <div class="mb-3">
                                                        <h6 class="text-blue-600">Technique</h6>
                                                        <p class="bg-gray-50 p-3 rounded">{{ $report->technique ?? 'N/A' }}
                                                        </p>
                                                    </div>

                                                    <div class="mb-3">
                                                        <h6 class="text-blue-600">Findings</h6>
                                                        <p class="bg-gray-50 p-3 rounded" style="white-space: pre-wrap;">
                                                            {{ $report->findings ?? 'N/A' }}</p>
                                                    </div>

                                                    <div class="mb-3">
                                                        <h6 class="text-blue-600">Impression</h6>
                                                        <div class="alert alert-info">
                                                            <strong>{{ $report->impression ?? 'N/A' }}</strong>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <h6 class="text-blue-600">Recommendations</h6>
                                                        <p class="bg-gray-50 p-3 rounded">
                                                            {{ $report->recommendations ?? 'N/A' }}</p>
                                                    </div>

                                                    @if ($report->images && count($report->images) > 0)
                                                        <div class="mb-3">
                                                            <h6 class="text-blue-600">Images ({{ count($report->images) }})
                                                            </h6>
                                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                                @foreach ($report->images as $image)
                                                                    <div class="w-full md:w-1/4 mb-2">
                                                                        <img src="{{ $image['url'] ?? '#' }}"
                                                                            class="img-fluid rounded border"
                                                                            alt="Radiology image">
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition" onclick="window.print()">
                                                        <i class="fas fa-print"></i> Print
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-6 text-gray-400">No radiology reports found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $reports->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
