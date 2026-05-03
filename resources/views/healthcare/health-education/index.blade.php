<x-app-layout>
    <x-slot name="header">{{ __('Health Education Materials') }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('healthcare.health-education.create') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                    class="fas fa-plus mr-2"></i>New Material</a>
    </div>

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
                                class="fas fa-book-medical text-blue-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Materials</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($statistics['total']) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-md p-3"><i
                                class="fas fa-check-circle text-green-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Published</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ number_format($statistics['published']) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3"><i
                                class="fas fa-edit text-yellow-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Draft</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ number_format($statistics['draft']) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gray-100 rounded-md p-3"><i
                                class="fas fa-archive text-gray-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Archived</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ number_format($statistics['archived']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Education Materials</h3>
                </div>
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Title</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Category</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Published</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Views</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($materials as $material)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ Str::limit($material->title, 50) }}</div>
                                        @if ($material->summary)
                                            <div class="text-sm text-gray-500">{{ Str::limit($material->summary, 60) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">{{ ucfirst($material->category) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full {{ $material->status === 'published' ? 'bg-green-100 text-green-800' : ($material->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">{{ ucfirst($material->status) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $material->published_at ? $material->published_at->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $material->view_count }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('healthcare.health-education.show', $material) }}"
                                            class="text-blue-600 hover:text-blue-900 mr-3"><i
                                                class="fas fa-eye"></i></a>
                                        <a href="{{ route('healthcare.health-education.edit', $material) }}"
                                            class="text-yellow-600 hover:text-yellow-900 mr-3"><i
                                                class="fas fa-edit"></i></a>
                                        <form action="{{ route('healthcare.health-education.destroy', $material) }}"
                                            method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900"><i
                                                    class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No education
                                        materials found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $materials->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
