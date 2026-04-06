@extends('layouts.app')

@section('title', 'Create Daily Site Report')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">New Daily Site Report</h1>
            <p class="text-sm text-gray-600 mt-1">Record daily construction activities and progress</p>
        </div>

        <form action="{{ route('construction.reports.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Basic Information Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Project <span
                                class="text-red-500">*</span></label>
                        <select name="project_id" required
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select Project</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }} ({{ $project->number }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Report Date <span
                                class="text-red-500">*</span></label>
                        <input type="date" name="report_date" value="{{ date('Y-m-d') }}" required
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Weather & Conditions Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Weather & Conditions</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Weather Condition</label>
                        <select name="weather_condition"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select Weather</option>
                            <option value="sunny">☀️ Sunny</option>
                            <option value="cloudy">☁️ Cloudy</option>
                            <option value="rainy">🌧️ Rainy</option>
                            <option value="windy">💨 Windy</option>
                            <option value="stormy">⛈️ Stormy</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Temperature (°C)</label>
                        <input type="number" name="temperature" step="0.1" placeholder="e.g., 28.5"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Manpower Count <span
                                class="text-red-500">*</span></label>
                        <input type="number" name="manpower_count" required min="0" placeholder="Number of workers"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Work Progress Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Work Progress</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Work Performed <span
                                class="text-red-500">*</span></label>
                        <textarea name="work_performed" rows="4" required placeholder="Describe the work completed today..."
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Progress Percentage <span
                                class="text-red-500">*</span></label>
                        <div class="flex items-center space-x-4">
                            <input type="range" name="progress_percentage" id="progressSlider" min="0"
                                max="100" step="0.1" value="0" class="flex-1"
                                oninput="document.getElementById('progressValue').textContent = this.value + '%'">
                            <span id="progressValue" class="text-lg font-bold text-blue-600 w-20 text-right">0%</span>
                        </div>
                        <input type="hidden" name="progress_percentage" id="progressInput" value="0">
                    </div>
                </div>
            </div>

            <!-- Equipment & Materials Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Equipment & Materials</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Equipment Used</label>
                        <textarea name="equipment_used" rows="3"
                            placeholder="List equipment used today (e.g., Excavator, Crane, Concrete Mixer)..."
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Materials Received</label>
                        <textarea name="materials_received" rows="3" placeholder="List materials received today..."
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                </div>
            </div>

            <!-- Issues & Safety Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Issues & Safety</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Issues Encountered</label>
                        <textarea name="issues_encountered" rows="3" placeholder="Describe any issues or challenges faced today..."
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Safety Incidents</label>
                        <input type="number" name="safety_incidents" min="0" value="0"
                            class="w-full md:w-1/3 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Enter 0 if no incidents occurred</p>
                    </div>
                </div>
            </div>

            <!-- Photo Upload Card - Mobile Optimized -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Site Photos</h2>
                <p class="text-sm text-gray-600 mb-4">Take photos directly from your mobile device camera</p>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Camera Button 1 -->
                    <div class="relative">
                        <label
                            class="block w-full aspect-square border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition-colors">
                            <input type="file" name="photos[]" accept="image/*" capture="environment" class="hidden"
                                onchange="previewImage(this, 'preview1')">
                            <div id="preview1" class="w-full h-full flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                        </label>
                        <p class="text-xs text-center text-gray-500 mt-1">Photo 1</p>
                    </div>

                    <!-- Camera Button 2 -->
                    <div class="relative">
                        <label
                            class="block w-full aspect-square border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition-colors">
                            <input type="file" name="photos[]" accept="image/*" capture="environment" class="hidden"
                                onchange="previewImage(this, 'preview2')">
                            <div id="preview2" class="w-full h-full flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                        </label>
                        <p class="text-xs text-center text-gray-500 mt-1">Photo 2</p>
                    </div>

                    <!-- Camera Button 3 -->
                    <div class="relative">
                        <label
                            class="block w-full aspect-square border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition-colors">
                            <input type="file" name="photos[]" accept="image/*" capture="environment" class="hidden"
                                onchange="previewImage(this, 'preview3')">
                            <div id="preview3" class="w-full h-full flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                        </label>
                        <p class="text-xs text-center text-gray-500 mt-1">Photo 3</p>
                    </div>

                    <!-- Camera Button 4 -->
                    <div class="relative">
                        <label
                            class="block w-full aspect-square border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition-colors">
                            <input type="file" name="photos[]" accept="image/*" capture="environment" class="hidden"
                                onchange="previewImage(this, 'preview4')">
                            <div id="preview4" class="w-full h-full flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                        </label>
                        <p class="text-xs text-center text-gray-500 mt-1">Photo 4</p>
                    </div>
                </div>
            </div>

            <!-- Notes Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Additional Notes</h2>
                <textarea name="notes" rows="3" placeholder="Any additional notes or observations..."
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('construction.reports.index') }}"
                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Save Report
                </button>
            </div>
        </form>
    </div>

    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover rounded-lg">`;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Sync range slider with hidden input
        document.getElementById('progressSlider').addEventListener('input', function() {
            document.getElementById('progressInput').value = this.value;
        });
    </script>
@endsection
