<x-app-layout>
    <x-slot name="header">{{ __('Lab Equipment') }}</x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Next
                                    Calibration</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($equipment ?? [] as $item)
                                <tr>
                                    <td class="px-6 py-4 text-sm">{{ $item->equipment_code ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $item->equipment_name ?? ($item->name ?? '-') }}</td>
                                    <td class="px-6 py-4 text-sm">{{ ucfirst($item->status ?? '-') }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $item->next_calibration_date ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No equipment found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
