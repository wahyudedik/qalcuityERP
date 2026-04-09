<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Telemedicine Consultations') }}
            </h2>
            <a href="{{ route('healthcare.telemedicine.create') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>New Consultation
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                            <i class="fas fa-clock text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Scheduled</p>
                            <p class="text-2xl font-semibold text-yellow-600">{{ $statistics['scheduled'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <i class="fas fa-video text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">In Progress</p>
                            <p class="text-2xl font-semibold text-blue-600">{{ $statistics['in_progress'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <i class="fas fa-check-circle text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Completed</p>
                            <p class="text-2xl font-semibold text-green-600">{{ $statistics['completed'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                            <i class="fas fa-laptop-medical text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total</p>
                            <p class="text-2xl font-semibold text-purple-600">{{ $statistics['total'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Telemedicine Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Consultation ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Doctor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Scheduled</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($consultations as $consultation)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $consultation->consultation_id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $consultation->patient->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $consultation->doctor->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">{{ ucfirst($consultation->consultation_type) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $consultation->scheduled_at ? $consultation->scheduled_at->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if ($consultation->status === 'scheduled') bg-yellow-100 text-yellow-800
                                    @elseif($consultation->status === 'in_progress') bg-blue-100 text-blue-800
                                    @elseif($consultation->status === 'completed') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $consultation->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('healthcare.telemedicine.show', $consultation) }}"
                                        class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if ($consultation->status === 'scheduled')
                                        <a href="{{ route('healthcare.telemedicine.join', $consultation) }}"
                                            class="text-green-600 hover:text-green-900 mr-3" title="Join Call">
                                            <i class="fas fa-video"></i>
                                        </a>
                                    @endif
                                    <button onclick="deleteConsultation({{ $consultation->id }})"
                                        class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="delete-consultation-{{ $consultation->id }}"
                                        action="{{ route('healthcare.telemedicine.destroy', $consultation) }}"
                                        method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">No telemedicine
                                    consultations found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($consultations->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $consultations->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function deleteConsultation(id) {
                if (confirm('Are you sure you want to delete this consultation?')) {
                    document.getElementById(`delete-consultation-${id}`).submit();
                }
            }
        </script>
    @endpush
</x-app-layout>
