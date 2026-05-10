<x-app-layout>
    <x-slot name="header">{{ __('Lab Test Catalog') }}</x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Test Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sample Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">TAT (hrs)
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($tests as $test)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-mono">{{ $test->test_code }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $test->test_name }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $test->category }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $test->sample_type }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $test->turnaround_time }}</td>
                                    <td class="px-6 py-4 text-sm">Rp {{ number_format($test->price, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No tests found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
