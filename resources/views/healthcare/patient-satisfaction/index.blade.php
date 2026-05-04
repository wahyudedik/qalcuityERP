<x-app-layout>
    <x-slot name="header">Survei Kepuasan Pasien</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Survei Kepuasan'],
    ]" />

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-end mb-4">
                <a href="{{ route('healthcare.patient-satisfaction.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 text-sm font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Survei Baru
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Total Surveys</h3>
                        <i class="fas fa-poll text-blue-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($statistics['total_surveys']) }}</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Average Rating</h3>
                        <i class="fas fa-star text-yellow-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ number_format($statistics['average_rating'], 2) }}/5.0</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Excellent (5★)</h3>
                        <i class="fas fa-smile-beam text-green-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($statistics['excellent']) }}</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Good (4★)</h3>
                        <i class="fas fa-smile text-teal-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($statistics['good']) }}</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Poor (1-2★)</h3>
                        <i class="fas fa-frown text-red-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($statistics['poor']) }}</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" class="flex items-center space-x-4">
                        <label class="text-sm font-medium text-gray-700">Filter by Rating:</label>
                        <select name="rating" onchange="this.form.submit()"
                            class="border-gray-300 rounded-md shadow-sm">
                            <option value="">All Ratings</option>
                            <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 Stars</option>
                            <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 Stars</option>
                            <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 Stars</option>
                            <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 Stars</option>
                            <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 Star</option>
                        </select>
                    </form>
                </div>
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Doctor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Overall</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Doctor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nurse</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Facility
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recommend
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($surveys as $survey)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $survey->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $survey->patient?->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $survey->doctor?->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i
                                                    class="fas fa-star {{ $i <= $survey->overall_rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                            @endfor
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $survey->doctor_rating ? $survey->doctor_rating . '/5' : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $survey->nurse_rating ? $survey->nurse_rating . '/5' : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $survey->facility_rating ? $survey->facility_rating . '/5' : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($survey->would_recommend)
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Yes</span>
                                        @else
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">No</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('healthcare.patient-satisfaction.show', $survey) }}"
                                            class="text-blue-600 hover:text-blue-900"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-4 text-center text-gray-500">No satisfaction
                                        surveys found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $surveys->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
