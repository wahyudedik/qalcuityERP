<x-app-layout>
    <x-slot name="header">{{ __('QC Test Templates') }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('qc.templates.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus mr-2"></i>New Template
            </a>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Templates Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Template Name</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Product Type</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Stage</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Parameters</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    AQL</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Inspections</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($templates as $template)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <span
                                            class="text-sm font-medium text-blue-600">{{ $template->name }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $template->product_type ?? 'All Types' }}</td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-700">
                                            {{ $template->stage_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ is_array($template->test_parameters) ? count($template->test_parameters) : 0 }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $template->acceptance_quality_limit }}%</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $template->inspections_count }}</td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded {{ $template->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $template->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <div class="flex gap-2">
                                            <a href="{{ route('qc.templates.show', $template) }}"
                                                class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('qc.templates.edit', $template) }}"
                                                class="text-green-600 hover:text-green-800">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('qc.templates.toggle', $template) }}" method="POST"
                                                class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="text-yellow-600 hover:text-yellow-800">
                                                    <i
                                                        class="fas fa-{{ $template->is_active ? 'pause' : 'play' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('qc.templates.destroy', $template) }}"
                                                method="POST" class="inline"
                                                onsubmit="return confirm('Delete this template?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-red-600 hover:text-red-800">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <i class="fas fa-file-alt text-6xl text-gray-300 mb-4"></i>
                                        <p class="text-gray-500">No QC test templates found</p>
                                        <a href="{{ route('qc.templates.create') }}"
                                            class="mt-4 inline-block text-blue-600 hover:text-blue-800">
                                            Create your first template
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $templates->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
