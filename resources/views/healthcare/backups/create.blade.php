<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Create New Backup') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('healthcare.backups.store') }}">
                    @csrf
                    <div class="space-y-6">
                        <div>
                            <label for="backup_type" class="block text-sm font-medium text-gray-700">Backup Type *</label>
                            <select name="backup_type" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Type</option>
                                <option value="full" {{ old('backup_type') === 'full' ? 'selected' : '' }}>Full Backup
                                    (Database + Files)</option>
                                <option value="database" {{ old('backup_type') === 'database' ? 'selected' : '' }}>
                                    Database Only</option>
                                <option value="files" {{ old('backup_type') === 'files' ? 'selected' : '' }}>Files Only
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Choose what to include in the backup</p>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Optional notes about this backup...">{{ old('notes') }}</textarea>
                        </div>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                            <div class="flex">
                                <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5 mr-3"></i>
                                <div class="text-sm text-yellow-800">
                                    <p class="font-semibold">Important:</p>
                                    <p class="mt-1">Backup process may take several minutes depending on database
                                        size. Please do not close this page during backup.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('healthcare.backups.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-database mr-2"></i>Start Backup</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
