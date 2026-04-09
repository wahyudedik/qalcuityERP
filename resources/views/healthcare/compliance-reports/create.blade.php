<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('New Compliance Report') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('healthcare.compliance-reports.store') }}">
                    @csrf
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="report_type" class="block text-sm font-medium text-gray-700">Report Type
                                    *</label>
                                <select name="report_type" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Type</option>
                                    <option value="hipaa" {{ old('report_type') === 'hipaa' ? 'selected' : '' }}>HIPAA
                                    </option>
                                    <option value="jci" {{ old('report_type') === 'jci' ? 'selected' : '' }}>JCI
                                    </option>
                                    <option value="iso" {{ old('report_type') === 'iso' ? 'selected' : '' }}>ISO
                                    </option>
                                    <option value="regulatory"
                                        {{ old('report_type') === 'regulatory' ? 'selected' : '' }}>Regulatory</option>
                                    <option value="internal" {{ old('report_type') === 'internal' ? 'selected' : '' }}>
                                        Internal</option>
                                </select>
                            </div>
                            <div>
                                <label for="report_date" class="block text-sm font-medium text-gray-700">Report Date
                                    *</label>
                                <input type="date" name="report_date" required
                                    value="{{ old('report_date', today()->format('Y-m-d')) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="reporting_period_start"
                                    class="block text-sm font-medium text-gray-700">Reporting Period Start *</label>
                                <input type="date" name="reporting_period_start" required
                                    value="{{ old('reporting_period_start') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="reporting_period_end"
                                    class="block text-sm font-medium text-gray-700">Reporting Period End *</label>
                                <input type="date" name="reporting_period_end" required
                                    value="{{ old('reporting_period_end') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="findings" class="block text-sm font-medium text-gray-700">Findings</label>
                            <textarea name="findings[]" rows="6"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter findings (one per line)">{{ old('findings') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Enter one finding per line</p>
                        </div>

                        <div>
                            <label for="recommendations"
                                class="block text-sm font-medium text-gray-700">Recommendations</label>
                            <textarea name="recommendations" rows="4"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter recommendations...">{{ old('recommendations') }}</textarea>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Additional
                                Notes</label>
                            <textarea name="notes" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter additional notes...">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('healthcare.compliance-reports.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Create Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
