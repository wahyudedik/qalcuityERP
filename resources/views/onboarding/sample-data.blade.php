@extends('layouts.app')

@section('title', 'Sample Data - Onboarding')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8"
        x-data="sampleDataWizard()">
        <div class="max-w-4xl w-full">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">🗂️ Load Sample Data</h1>
                <p class="text-lg text-gray-600">Jumpstart your workspace with pre-built data for your industry</p>
            </div>

            <!-- Progress Indicator -->
            <div class="mb-8">
                <div class="flex items-center justify-center space-x-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-green-500 text-white flex items-center justify-center font-bold">✓</div>
                        <span class="ml-2 text-sm font-medium text-green-600">Select Industry</span>
                    </div>
                    <div class="w-16 h-1 bg-blue-400"></div>
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">2</div>
                        <span class="ml-2 text-sm font-medium text-blue-600">Sample Data</span>
                    </div>
                    <div class="w-16 h-1 bg-gray-300"></div>
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold">3</div>
                        <span class="ml-2 text-sm font-medium text-gray-600">Ready!</span>
                    </div>
                </div>
            </div>

            <!-- Main Card -->
            <div x-show="!loading && !done" class="bg-white rounded-2xl shadow-xl p-8">
                <div class="mb-6">
                    <span class="inline-block bg-blue-100 text-blue-700 text-sm font-semibold px-3 py-1 rounded-full capitalize">
                        {{ $profile['industry'] ?? 'General' }}
                    </span>
                </div>

                @if(count($templates) > 0)
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Choose a template</h2>
                    <p class="text-gray-600 mb-8">Select a pre-built dataset to populate your workspace, or skip to start fresh.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        @foreach($templates ?? [] as $template)
                            <button @click="selectTemplate({{ $template['id'] }})"
                                :class="selectedTemplate === {{ $template['id'] }} ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300'"
                                class="border-2 rounded-xl p-6 text-left transition-all w-full">
                                <h3 class="font-bold text-lg mb-1">{{ $template['template_name'] }}</h3>
                                <p class="text-sm text-gray-600 mb-3">{{ $template['description'] }}</p>
                                @if(!empty($template['modules_included']))
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($template['modules_included'] as $module)
                                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">{{ $module }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 mb-8">
                        <div class="text-5xl mb-4">📭</div>
                        <p class="text-gray-500">No templates available for your industry yet.</p>
                    </div>
                @endif

                <div class="flex justify-between items-center">
                    <a href="{{ route('onboarding.wizard') }}" class="text-gray-500 hover:text-gray-700 text-sm">← Back</a>
                    <div class="flex gap-3">
                        <button @click="skip()" class="px-6 py-3 rounded-lg border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                            Skip for now
                        </button>
                        @if(count($templates) > 0)
                            <button @click="generate()" :disabled="!selectedTemplate"
                                :class="selectedTemplate ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-300 cursor-not-allowed'"
                                class="px-8 py-3 rounded-lg text-white font-semibold transition-colors">
                                Load Sample Data →
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div x-show="loading" class="bg-white rounded-2xl shadow-xl p-12 text-center">
                <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mx-auto mb-4"></div>
                <h3 class="text-xl font-semibold mb-2">Generating your sample data...</h3>
                <p class="text-gray-600">This may take a few seconds</p>
            </div>

            <!-- Done State -->
            <div x-show="done" class="bg-white rounded-2xl shadow-xl p-12 text-center">
                <div class="text-6xl mb-4">🎉</div>
                <h3 class="text-2xl font-bold mb-2">You're all set!</h3>
                <p class="text-gray-600 mb-8">Your workspace is ready to go.</p>
                <a href="{{ route('dashboard') }}" class="inline-block px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                    Go to Dashboard →
                </a>
            </div>
        </div>
    </div>

    <script>
        function sampleDataWizard() {
            return {
                selectedTemplate: null,
                loading: false,
                done: false,

                selectTemplate(id) {
                    this.selectedTemplate = id;
                },

                async generate() {
                    if (!this.selectedTemplate) return;
                    this.loading = true;
                    try {
                        const response = await fetch('/onboarding/generate-sample-data', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ template_id: this.selectedTemplate })
                        });
                        const result = await response.json();
                        if (result.success) {
                            this.loading = false;
                            this.done = true;
                        } else {
                            alert(result.message || 'Failed to generate sample data.');
                            this.loading = false;
                        }
                    } catch (e) {
                        console.error(e);
                        alert('An error occurred. Please try again.');
                        this.loading = false;
                    }
                },

                async skip() {
                    try {
                        await fetch('/onboarding/generate-sample-data', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ skip: true })
                        });
                    } catch (e) { /* silent */ }
                    window.location.href = '{{ route('dashboard') }}';
                }
            }
        }
    </script>
@endsection
