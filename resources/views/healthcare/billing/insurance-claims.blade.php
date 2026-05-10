<x-app-layout>
    <x-slot name="header">{{ __('Insurance Claims') }}</x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Claim #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($claims ?? [] as $claim)
                            <tr>
                                <td class="px-6 py-4 text-sm">{{ $claim->claim_number ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm">{{ $claim->patient?->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm">Rp
                                    {{ number_format($claim->claim_amount ?? 0, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm">{{ ucfirst($claim->status ?? '-') }}</td>
                                <td class="px-6 py-4 text-sm">{{ $claim->created_at?->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">No claims found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if (method_exists($claims, 'links'))
                    <div class="mt-4">{{ $claims->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
