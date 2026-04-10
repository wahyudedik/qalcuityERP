<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Bulk Document Generation') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div
                        class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">📄 Generate Multiple
                            Documents</h3>
                        <p class="text-sm text-blue-700 dark:text-blue-300">Select a template and provide data to
                            generate multiple documents at once.</p>
                    </div>

                    <form method="POST" action="{{ route('documents.bulk-generate') }}" class="space-y-6">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Template
                                *</label>
                            <select name="template_id" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select a template</option>
                                <!-- Templates will be loaded here -->
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Output Format
                                *</label>
                            <select name="output_format" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="pdf">PDF</option>
                                <option value="docx">Word (DOCX)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data (JSON)
                                *</label>
                            <div class="mb-2 text-xs text-gray-500 dark:text-gray-400">
                                Provide an array of objects with template variables
                            </div>
                            <textarea name="data" required rows="15"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-sm"
                                placeholder='[
  {
    "title": "Invoice 001",
    "customer_name": "John Doe",
    "amount": "1000.00",
    "date": "2024-01-01"
  },
  {
    "title": "Invoice 002",
    "customer_name": "Jane Smith",
    "amount": "2000.00",
    "date": "2024-01-02"
  }
]'></textarea>
                        </div>

                        <div
                            class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-2">⚠️ Important
                                Notes:</h4>
                            <ul class="text-sm text-yellow-700 dark:text-yellow-300 space-y-1 list-disc list-inside">
                                <li>Each object in the array will generate one document</li>
                                <li>Variable names must match template placeholders</li>
                                <li>Maximum 100 documents per batch</li>
                                <li>Large batches may take several minutes</li>
                            </ul>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                onclick="return confirm('Generate documents? This may take a while.')">
                                Generate Documents
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recent Generations -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Recent Generations</h3>
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No recent bulk generations</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
