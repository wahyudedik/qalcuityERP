<x-app-layout>
    <x-slot name="header">Detail Survei #{{ $survey->id }}</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Survei Kepuasan', 'url' => route('healthcare.patient-satisfaction.index')],
        ['label' => 'Detail Survei'],
    ]" />

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Patient Information</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Patient Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $survey->patient->name ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Doctor</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $survey->doctor->name ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Visit Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $survey->visit->visit_date ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Survey Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $survey->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ratings</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                    <div class="p-4 bg-blue-50 rounded text-center">
                        <p class="text-sm font-medium text-gray-700 mb-2">Overall</p>
                        <div class="flex items-center justify-center">
                            @for ($i = 1; $i <= 5; $i++)
                                <i
                                    class="fas fa-star {{ $i <= $survey->overall_rating ? 'text-yellow-400 text-2xl' : 'text-gray-300 text-2xl' }}"></i>
                            @endfor
                        </div>
                        <p class="text-lg font-bold text-gray-900 mt-2">{{ $survey->overall_rating }}/5</p>
                    </div>

                    <div class="p-4 bg-green-50 rounded text-center">
                        <p class="text-sm font-medium text-gray-700 mb-2">Doctor</p>
                        @if ($survey->doctor_rating)
                            <div class="flex items-center justify-center">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i
                                        class="fas fa-star {{ $i <= $survey->doctor_rating ? 'text-yellow-400 text-2xl' : 'text-gray-300 text-2xl' }}"></i>
                                @endfor
                            </div>
                            <p class="text-lg font-bold text-gray-900 mt-2">{{ $survey->doctor_rating }}/5</p>
                        @else
                            <p class="text-sm text-gray-500">Not rated</p>
                        @endif
                    </div>

                    <div class="p-4 bg-teal-50 rounded text-center">
                        <p class="text-sm font-medium text-gray-700 mb-2">Nurse</p>
                        @if ($survey->nurse_rating)
                            <div class="flex items-center justify-center">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i
                                        class="fas fa-star {{ $i <= $survey->nurse_rating ? 'text-yellow-400 text-2xl' : 'text-gray-300 text-2xl' }}"></i>
                                @endfor
                            </div>
                            <p class="text-lg font-bold text-gray-900 mt-2">{{ $survey->nurse_rating }}/5</p>
                        @else
                            <p class="text-sm text-gray-500">Not rated</p>
                        @endif
                    </div>

                    <div class="p-4 bg-purple-50 rounded text-center">
                        <p class="text-sm font-medium text-gray-700 mb-2">Facility</p>
                        @if ($survey->facility_rating)
                            <div class="flex items-center justify-center">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i
                                        class="fas fa-star {{ $i <= $survey->facility_rating ? 'text-yellow-400 text-2xl' : 'text-gray-300 text-2xl' }}"></i>
                                @endfor
                            </div>
                            <p class="text-lg font-bold text-gray-900 mt-2">{{ $survey->facility_rating }}/5</p>
                        @else
                            <p class="text-sm text-gray-500">Not rated</p>
                        @endif
                    </div>

                    <div class="p-4 bg-orange-50 rounded text-center">
                        <p class="text-sm font-medium text-gray-700 mb-2">Cleanliness</p>
                        @if ($survey->cleanliness_rating)
                            <div class="flex items-center justify-center">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i
                                        class="fas fa-star {{ $i <= $survey->cleanliness_rating ? 'text-yellow-400 text-2xl' : 'text-gray-300 text-2xl' }}"></i>
                                @endfor
                            </div>
                            <p class="text-lg font-bold text-gray-900 mt-2">{{ $survey->cleanliness_rating }}/5</p>
                        @else
                            <p class="text-sm text-gray-500">Not rated</p>
                        @endif
                    </div>
                </div>
            </div>

            @if ($survey->would_recommend !== null)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recommendation</h3>
                    <div class="flex items-center">
                        @if ($survey->would_recommend)
                            <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                            <span class="text-lg font-semibold text-gray-900">Patient would recommend this
                                facility</span>
                        @else
                            <i class="fas fa-times-circle text-red-600 text-2xl mr-3"></i>
                            <span class="text-lg font-semibold text-gray-900">Patient would NOT recommend this
                                facility</span>
                        @endif
                    </div>
                </div>
            @endif

            @if ($survey->comments)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Comments</h3>
                    <div class="bg-gray-50 p-4 rounded">
                        <p class="text-sm text-gray-700">{{ $survey->comments }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
