@extends('layouts.app')

@section('title', 'QC Test Templates')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <a href="{{ route('cosmetic.qc.tests') }}" class="text-blue-600 hover:text-blue-900 mb-2 inline-block">
                        ← Back to QC Tests
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900">QC Test Templates</h1>
                    <p class="mt-1 text-sm text-gray-500">Standard test procedures and templates</p>
                </div>
                <button onclick="document.getElementById('add-template-modal').classList.remove('hidden')"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                    + New Template
                </button>
            </div>
        </div>

        <!-- Templates Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($templates as $template)
                <div class="bg-white rounded-lg shadow hover:shadow-lg transition">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $template->template_name }}</h3>
                                <p class="text-sm text-gray-500">{{ $template->template_code }}</p>
                            </div>
                            <span
                                class="px-2 py-1 text-xs font-medium rounded-full
                        @if ($template->is_active) bg-green-100 text-green-800
                        @else bg-gray-100 text-gray-800 @endif">
                                {{ $template->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <div class="mb-4">
                            <span class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800">
                                {{ $template->category_label }}
                            </span>
                        </div>

                        <div class="space-y-2 mb-4">
                            <div class="text-sm">
                                <strong class="text-gray-700">Parameters:</strong>
                                <span class="text-gray-900">{{ count($template->test_parameters ?? []) }}</span>
                            </div>
                            <div class="text-sm">
                                <strong class="text-gray-700">Tests Using:</strong>
                                <span class="text-gray-900">{{ $template->testResults->count() }}</span>
                            </div>
                        </div>

                        @if ($template->procedure)
                            <div class="p-3 bg-gray-50 rounded text-sm text-gray-700 mb-4">
                                <strong>Procedure:</strong>
                                <p class="mt-1 line-clamp-3">{{ Str::limit($template->procedure, 150) }}</p>
                            </div>
                        @endif

                        <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                            <span class="text-xs text-gray-500">
                                Created: {{ $template->created_at->format('d M Y') }}
                            </span>
                            <div class="flex gap-2">
                                <button class="text-blue-600 hover:text-blue-900 text-sm font-medium">View</button>
                                <button class="text-green-600 hover:text-green-900 text-sm font-medium">Use</button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 bg-white rounded-lg shadow p-12 text-center text-gray-400">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="mt-2 text-lg">No templates yet</p>
                    <p class="text-sm">Create your first QC test template</p>
                </div>
            @endforelse
        </div>

        @if ($templates->hasPages())
            <div class="mt-4">{{ $templates->links() }}</div>
        @endif
    </div>

    <!-- Add Template Modal -->
    <div id="add-template-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div
            class="relative top-10 mx-auto p-5 border w-[800px] shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-semibold mb-4">Create QC Test Template</h3>
            <form method="POST" action="{{ route('cosmetic.qc.templates.store') }}">
                @csrf
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Template Name *</label>
                            <input type="text" name="template_name" required placeholder="e.g., Microbial Test Standard"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                            <select name="test_category" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="microbial">Microbial Testing</option>
                                <option value="heavy_metal">Heavy Metal Testing</option>
                                <option value="preservative">Preservative Efficacy</option>
                                <option value="patch_test">Patch Test</option>
                                <option value="physical">Physical Testing</option>
                                <option value="chemical">Chemical Testing</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Test Parameters (JSON)</label>
                        <textarea name="test_parameters" rows="3"
                            placeholder='[{"name": "pH", "unit": "value"}, {"name": "Viscosity", "unit": "cP"}]'
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg font-mono text-sm"></textarea>
                        <p class="mt-1 text-xs text-gray-500">JSON array of parameter objects</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Acceptance Criteria (JSON)</label>
                        <textarea name="acceptance_criteria" rows="3" placeholder='{"pH": "5.5-6.5", "Viscosity": "1000-2000 cP"}'
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg font-mono text-sm"></textarea>
                        <p class="mt-1 text-xs text-gray-500">JSON object mapping parameters to acceptable ranges</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Procedure</label>
                        <textarea name="procedure" rows="5" placeholder="Step-by-step testing procedure..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Create Template
                    </button>
                    <button type="button" onclick="document.getElementById('add-template-modal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
