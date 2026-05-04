<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('healthcare.patients.index') }}">Patients</a></li>
                    <li class="breadcrumb-item"><a
                            href="{{ route('healthcare.patients.show', $patient) }}">{{ $patient->name }}</a></li>
                    <li class="breadcrumb-item active">Medical History</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-history text-blue-600"></i> Medical History
            </h1>
            <p class="text-gray-500">Complete medical history for {{ $patient->name }}</p>
        </div>
        <div>
            <a href="{{ route('healthcare.patients.show', $patient) }}" class="px-4 py-2 border border-gray-400 text-gray-600 hover:bg-gray-50 rounded-xl text-sm transition">
                <i class="fas fa-argrid grid-cols-1 md:grid-cols-2 gap-6-left"></i> Back to Patient
            </a>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h5 class="mb-0">
                        <i class="fas fa-filter"></i> Filter Timeline
                    </h5>
                    <form method="GET" class="flex gap-2">
                        <select name="type" class="form-select form-select-sm" style="width: 180px;">
                            <option value="">All Types</option>
                            <option value="visit" {{ request('type') == 'visit' ? 'selected' : '' }}>Visits</option>
                            <option value="diagnosis" {{ request('type') == 'diagnosis' ? 'selected' : '' }}>Diagnoses
                            </option>
                            <option value="procedure" {{ request('type') == 'procedure' ? 'selected' : '' }}>Procedures
                            </option>
                            <option value="lab" {{ request('type') == 'lab' ? 'selected' : '' }}>Lab Results</option>
                            <option value="prescription" {{ request('type') == 'prescription' ? 'selected' : '' }}>
                                Prescriptions</option>
                        </select>
                        <input type="date" name="date_from" class="form-control form-control-sm" style="width: 150px;"
                            value="{{ request('date_from') }}">
                        <input type="date" name="date_to" class="form-control form-control-sm" style="width: 150px;"
                            value="{{ request('date_to') }}">
                        <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs transition">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">
                        <i class="fas fa-timeline"></i> Medical Timeline
                    </h5>
                </div>
                <div class="p-5">
                    @if ($timeline->count() > 0)
                        <div class="timeline">
                            @foreach ($timeline as $item)
                                <div class="timeline-item mb-4">
                                    <div class="flex">
                                        <div class="me-3">
                                            <div class="timeline-marker bg-{{ $item['type_color']  }}">
                                                <i class="fas {{ $item['type_icon'] }} text-white"></i>
                                            </div>
                                        </div>
                                        <div class="flex-ggrid grid-cols-1 md:grid-cols-2 gap-6-1">
                                            <div class="bg-white rounded-2xl border border-gray-200 border-{{ $item['type_color'] }}">
                                                <div class="px-5 py-2 border-b border-gray-200 bg-gray-50">
                                                    <div class="flex items-center justify-between">
                                                        <div>
                                                            <span
                                                                class="badge bg-{{ $item['type_color']  }} mr-2">{{ ucfirst($item['type']) }}</span>
                                                            <strong>{{ $item['title'] }}</strong>
                                                        </div>
                                                        <small class="text-gray-500">
                                                            <i class="fas fa-calendar"></i> {{ $item['date'] }}
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="p-5 py-2">
                                                    <p class="mb-1 text-sm">{{ $item['description'] }}</p>
                                                    @if ($item['provider'])
                                                        <small class="text-gray-500">
                                                            <i class="fas fa-user-md"></i> {{ $item['provider'] }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($timeline->hasPages())
                            <div class="flex justify-center mt-4">
                                {{ $timeline->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-10">
                            <i class="fas fa-history fa-3x text-gray-500 mb-3"></i>
                            <p class="text-gray-500">No medical history records found</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .timeline-marker {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 19px;
            top: 40px;
            bottom: -16px;
            width: 2px;
            background: #dee2e6;
        }
    </style>
</x-app-layout>
