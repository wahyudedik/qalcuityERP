<x-app-layout>
    <x-slot name="header">{{ __('Expired Documents') }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('documents.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Documents
            </a>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div
                        class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center">
                            <svg class="h-6 w-6 text-red-600 mr-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                            <div>
                                <h3 class="text-sm font-medium text-red-800">Attention Required</h3>
                                <p class="text-sm text-red-700 mt-1">These documents have expired and
                                    may need renewal or archival.</p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Document</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Category</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Expired On</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Days Expired</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Owner</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($documents as $document)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $document->title }}</div>
                                            <div class="text-sm text-gray-500">
                                                {{ $document->file_name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ $document->category ?? 'General' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $document->expires_at->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                {{ $document->expires_at->diffInDays(now()) }} days ago
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $document->uploader?->name ?? 'Unknown' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <a href="{{ route('documents.download', $document) }}"
                                                class="text-blue-600 hover:text-blue-900">Download</a>
                                            <a href="{{ route('documents.index', ['status' => 'archived']) }}"
                                                class="text-gray-600 hover:text-gray-900">Archive</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center">
                                            <svg class="mx-auto h-12 w-12 text-green-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <h3 class="mt-2 text-sm font-medium text-gray-900">No
                                                expired documents</h3>
                                            <p class="mt-1 text-sm text-gray-500">All your documents
                                                are up to date!</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $documents->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
