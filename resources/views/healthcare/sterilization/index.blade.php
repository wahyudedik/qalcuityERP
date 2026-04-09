<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Sterilization Records') }}</h2>
            <a href="{{ route('healthcare.sterilization.create') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                    class="fas fa-plus mr-2"></i>New Record</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}</div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-md p-3"><i
                                class="fas fa-shield-virus text-blue-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Records</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($statistics['total_cycles']) }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-md p-3"><i
                                class="fas fa-check-circle text-green-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Completed</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ number_format($statistics['completed_today']) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3"><i
                                class="fas fa-spinner text-yellow-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">In Progress</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ number_format($statistics['in_progress']) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-100 rounded-md p-3"><i
                                class="fas fa-exclamation-triangle text-red-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Failed</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ number_format($statistics['failed']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Sterilization Log</h3>
                </div>
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Record ID</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Equipment/Instruments</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Method</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Operator</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date/Time</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($sterilizations as $record)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                        {{ $record->record_id }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ Str::limit($record->items_description, 40) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ ucfirst($record->method) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $record->operator_name ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $record->sterilized_at ? $record->sterilized_at->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full {{ $record->status === 'completed' ? 'bg-green-100 text-green-800' : ($record->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">{{ ucfirst(str_replace('_', ' ', $record->status)) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('healthcare.sterilization.show', $record) }}"
                                            class="text-blue-600 hover:text-blue-900 mr-3"><i
                                                class="fas fa-eye"></i></a>
                                        <a href="{{ route('healthcare.sterilization.edit', $record) }}"
                                            class="text-yellow-600 hover:text-yellow-900 mr-3"><i
                                                class="fas fa-edit"></i></a>
                                        <form action="{{ route('healthcare.sterilization.destroy', $record) }}"
                                            method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900"><i
                                                    class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No sterilization
                                        records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
