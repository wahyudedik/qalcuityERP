@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Animal Pedigrees</h1>
            <p class="mt-2 text-gray-600">Manage animal genetics and pedigree records</p>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Pedigree Records</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Animal ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Breed</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Birth Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gender</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($pedigrees as $pedigree)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $pedigree->animal_id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $pedigree->animal_name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $pedigree->breed }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $pedigree->birth_date->format('d M Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ ucfirst($pedigree->gender) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if ($pedigree->is_active) bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                        {{ $pedigree->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No pedigrees found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $pedigrees->links() }}
            </div>
        </div>
    </div>
@endsection
