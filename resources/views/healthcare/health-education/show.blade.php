<x-app-layout>
    <x-slot name="header">{{ __('Health Education Material') }} -
                {{ $healthEducation->title }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('healthcare.health-education.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-info-circle mr-2 text-blue-600"></i>Material Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Title</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $healthEducation->title }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Category</dt>
                            <dd class="mt-1"><span
                                    class="px-2 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">{{ ucfirst(str_replace('_', ' ', $healthEducation->category)) }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full {{ $healthEducation->status === 'published' ? 'bg-green-100 text-green-800' : ($healthEducation->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">{{ ucfirst($healthEducation->status) }}</span>
                            </dd>
                        </div>
                        @if ($healthEducation->target_audience)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Target Audience</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $healthEducation->target_audience }}</dd>
                            </div>
                        @endif
                        @if ($healthEducation->language)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Language</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $healthEducation->language }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-chart-line mr-2 text-green-600"></i>Statistics</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">View Count</dt>
                            <dd class="mt-1 text-2xl font-bold text-gray-900">{{ $healthEducation->view_count }}</dd>
                        </div>
                        @if ($healthEducation->published_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Published At</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $healthEducation->published_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created At</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $healthEducation->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $healthEducation->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>

                @if ($healthEducation->attachment_path)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                                class="fas fa-paperclip mr-2 text-purple-600"></i>Attachment</h3>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">File Path</dt>
                                <dd class="mt-1 text-sm text-gray-900 break-all">
                                    {{ $healthEducation->attachment_path }}</dd>
                            </div>
                            <div class="pt-4">
                                <a href="{{ $healthEducation->attachment_path }}" target="_blank"
                                    class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                                    <i class="fas fa-download mr-2"></i>Download Attachment
                                </a>
                            </div>
                        </dl>
                    </div>
                @endif
            </div>

            @if ($healthEducation->summary)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-align-left mr-2 text-orange-600"></i>Summary</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $healthEducation->summary }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                        class="fas fa-book-open mr-2 text-indigo-600"></i>Full Content</h3>
                <div class="prose max-w-none">
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $healthEducation->content }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
