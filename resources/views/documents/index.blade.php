<x-app-layout>
    <x-slot name="header">{{ __('Document Management') }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('documents.pending-approvals') }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Pending Approvals
                </a>
        <button onclick="document.getElementById('uploadModal').showModal()"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                        </path>
                    </svg>
                    Upload Document
                </button>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Documents</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $statistics['total'] }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Pending Approval</p>
                            <p class="text-2xl font-semibold text-gray-900">
                                {{ $statistics['pending_approval'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Expired</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $statistics['expired'] }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Signed</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $statistics['signed'] }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">With OCR</p>
                            <p class="text-2xl font-semibold text-gray-900">
                                {{ $statistics['with_ocr'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-4">
                <form method="GET" action="{{ route('documents.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Search documents..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">All Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft
                                </option>
                                <option value="pending_approval"
                                    {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending Approval
                                </option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>
                                    Approved</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>
                                    Rejected</option>
                                <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>
                                    Archived</option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <input type="text" name="category" value="{{ request('category') }}"
                                placeholder="Category"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="flex items-end space-x-2">
                            <button type="submit"
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Filter
                            </button>
                            <a href="{{ route('documents.index') }}"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                Reset
                            </a>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="expiring" value="1"
                                {{ request('expiring') ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Expiring Soon</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="expired" value="1"
                                {{ request('expired') ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Expired</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="signed" value="1"
                                {{ request('signed') ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Signed</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="has_ocr" value="1"
                                {{ request('has_ocr') ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Has OCR</span>
                        </label>
                    </div>
                </form>
            </div>

            <!-- Documents Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Document</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Category</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Version</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Expires</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($documents as $document)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <svg class="h-10 w-10 text-gray-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $document->title }}</div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $document->file_name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ $document->category ?? 'General' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        v{{ $document->version }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                'draft' =>
                                                    'bg-gray-100 text-gray-800',
                                                'pending_approval' =>
                                                    'bg-yellow-100 text-yellow-800',
                                                'approved' =>
                                                    'bg-green-100 text-green-800',
                                                'rejected' =>
                                                    'bg-red-100 text-red-800',
                                                'archived' =>
                                                    'bg-blue-100 text-blue-800',
                                            ];
                                        @endphp
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$document->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst(str_replace('_', ' ', $document->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if ($document->expires_at)
                                            @if ($document->isExpired())
                                                <span class="text-red-600 font-semibold">Expired</span>
                                                <div class="text-xs text-gray-500">
                                                    {{ $document->expires_at->format('d M Y') }}</div>
                                            @else
                                                <div>{{ $document->expires_at->format('d M Y') }}</div>
                                                <div class="text-xs text-gray-500">{{ $document->daysUntilExpiry() }}
                                                    days left</div>
                                            @endif
                                        @else
                                            <span class="text-gray-400">No expiry</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <a href="{{ route('documents.download', $document) }}"
                                            class="text-blue-600 hover:text-blue-900">Download</a>
                                        <a href="{{ route('documents.versions.index', $document) }}"
                                            class="text-purple-600 hover:text-purple-900">Versions</a>
                                        @if ($document->status === 'pending_approval' || $document->isPendingApproval())
                                            <a href="{{ route('documents.approval.index', $document) }}"
                                                class="text-yellow-600 hover:text-yellow-900">Approval</a>
                                        @endif
                                        @if (!$document->has_ocr)
                                            <form method="POST"
                                                action="{{ route('documents.process-ocr', $document) }}"
                                                class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="text-green-600 hover:text-green-900">Process
                                                    OCR</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('documents.destroy', $document) }}"
                                            class="inline" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No documents
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500">Get started by
                                            uploading a new document.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4">
                    {{ $documents->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <dialog id="uploadModal" class="modal rounded-lg shadow-xl p-0">
        <div class="bg-white w-full max-w-2xl">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Upload Document</h3>
                <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data"
                    class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">File *</label>
                        <input type="file" name="file" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                        <input type="text" name="title" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <input type="text" name="category"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiry
                            Date</label>
                        <input type="datetime-local" name="expires_at"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="space-y-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="auto_ocr" value="1"
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Auto-process OCR after
                                upload</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="requires_approval" value="1"
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Requires approval
                                workflow</span>
                        </label>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="document.getElementById('uploadModal').close()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </dialog>
</x-app-layout>
