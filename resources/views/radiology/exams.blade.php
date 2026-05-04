<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-x-ray text-blue-600"></i> Radiology Exams
            </h1>
            <p class="text-gray-500">Manage radiology examinations and imaging</p>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-blue-300">
                <div class="p-5 text-center">
                    <h3 class="text-sky-600">{{ $exams->where('status', 'scheduled')->count() }}</h3>
                    <small class="text-gray-500">Scheduled</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-amber-300">
                <div class="p-5 text-center">
                    <h3 class="text-amber-600">{{ $exams->where('status', 'in_progress')->count() }}</h3>
                    <small class="text-gray-500">In Progress</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-emerald-300">
                <div class="p-5 text-center">
                    <h3 class="text-emerald-600">{{ $exams->where('status', 'completed')->count() }}</h3>
                    <small class="text-gray-500">Completed</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-gray-300">
                <div class="p-5 text-center">
                    <h3 class="text-secondary">{{ $exams->where('status', 'reported')->count() }}</h3>
                    <small class="text-gray-500">Reported</small>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Exam #</th>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Exam Type</th>
                                    <th>Body Part</th>
                                    <th>Radiologist</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($exams as $exam)
                                    <tr>
                                        <td><code>{{ $exam->exam_number }}</code></td>
                                        <td>{{ $exam->exam_date?->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('healthcare.patients.show', $exam->patient) }}">
                                                {{ $exam->patient?->name ?? '-' }}
                                            </a>
                                        </td>
                                        <td>
                                            @php
                                                $icons = [
                                                    'X-Ray' => 'fa-x-ray',
                                                    'MRI' => 'fa-magnet',
                                                    'CT Scan' => 'fa-circle-notch',
                                                    'Ultrasound' => 'fa-wave-square',
                                                    'Mammography' => 'fa-radiation',
                                                ];
                                            @endphp
                                            <i
                                                class="fas {{ $icons[$exam->exam_type] ?? 'fa-x-ray' }} mr-1 text-blue-600"></i>
                                            {{ $exam->exam_type ?? '-' }}
                                        </td>
                                        <td>{{ $exam->body_part ?? '-' }}</td>
                                        <td>{{ $exam->radiologist?->name ?? '-' }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'scheduled' => 'info',
                                                    'in_progress' => 'warning',
                                                    'completed' => 'success',
                                                    'reported' => 'secondary',
                                                    'cancelled' => 'danger',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$exam->status] ?? 'secondary'  }}">
                                                {{ ucfirst(str_replace('_', ' ', $exam->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex gap-1">
                                                <a href="{{ route('healthcare.radiology.exams.show', $exam) }}"
                                                    class="px-3 py-1.5 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-xs transition">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if ($exam->status == 'completed')
                                                    <button class="px-3 py-1.5 border border-emerald-500 text-emerald-600 hover:bg-emerald-50 rounded-lg text-xs transition">
                                                        <i class="fas fa-file-medical"></i> Report
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-6 text-gray-400">No radiology exams found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $exams->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
