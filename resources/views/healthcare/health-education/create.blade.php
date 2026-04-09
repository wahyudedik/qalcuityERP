<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('New Health Education Material') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('healthcare.health-education.store') }}">
                    @csrf
                    <div class="space-y-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Title *</label>
                            <input type="text" name="title" required value="{{ old('title') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., Understanding Diabetes Management">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700">Category *</label>
                                <select name="category" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Category</option>
                                    <option value="nutrition" {{ old('category') === 'nutrition' ? 'selected' : '' }}>
                                        Nutrition & Diet</option>
                                    <option value="exercise" {{ old('category') === 'exercise' ? 'selected' : '' }}>
                                        Exercise & Fitness</option>
                                    <option value="mental_health"
                                        {{ old('category') === 'mental_health' ? 'selected' : '' }}>Mental Health
                                    </option>
                                    <option value="chronic_disease"
                                        {{ old('category') === 'chronic_disease' ? 'selected' : '' }}>Chronic Disease
                                        Management</option>
                                    <option value="preventive_care"
                                        {{ old('category') === 'preventive_care' ? 'selected' : '' }}>Preventive Care
                                    </option>
                                    <option value="maternal_health"
                                        {{ old('category') === 'maternal_health' ? 'selected' : '' }}>Maternal & Child
                                        Health</option>
                                    <option value="medication" {{ old('category') === 'medication' ? 'selected' : '' }}>
                                        Medication Safety</option>
                                    <option value="first_aid" {{ old('category') === 'first_aid' ? 'selected' : '' }}>
                                        First Aid & Emergency</option>
                                </select>
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                                <select name="status" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft
                                    </option>
                                    <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>
                                        Published</option>
                                    <option value="archived" {{ old('status') === 'archived' ? 'selected' : '' }}>
                                        Archived</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="target_audience" class="block text-sm font-medium text-gray-700">Target
                                    Audience</label>
                                <input type="text" name="target_audience" value="{{ old('target_audience') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="e.g., Adults, Seniors, Parents">
                            </div>
                            <div>
                                <label for="language" class="block text-sm font-medium text-gray-700">Language</label>
                                <input type="text" name="language" value="{{ old('language', 'Indonesian') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="summary" class="block text-sm font-medium text-gray-700">Summary</label>
                            <textarea name="summary" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Brief description of the material...">{{ old('summary') }}</textarea>
                        </div>

                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700">Content *</label>
                            <textarea name="content" required rows="10"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Full educational content...">{{ old('content') }}</textarea>
                        </div>

                        <div>
                            <label for="attachment_path" class="block text-sm font-medium text-gray-700">Attachment
                                Path</label>
                            <input type="text" name="attachment_path" value="{{ old('attachment_path') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., /storage/materials/diabetes-guide.pdf">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('healthcare.health-education.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Save Material</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
