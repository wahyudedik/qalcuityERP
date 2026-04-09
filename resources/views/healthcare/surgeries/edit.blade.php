<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Surgery Schedule') }} -
            {{ $surgery->surgery_id }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('healthcare.surgeries.update', $surgery) }}">
                    @csrf
                    @method('PUT')
                    <div class="space-y-6">
                        <div>
                            <label for="procedure_name" class="block text-sm font-medium text-gray-700">Procedure Name
                                *</label>
                            <input type="text" name="procedure_name" required
                                value="{{ old('procedure_name', $surgery->procedure_name) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="operating_room" class="block text-sm font-medium text-gray-700">Operating Room
                                *</label>
                            <input type="text" name="operating_room" required
                                value="{{ old('operating_room', $surgery->operating_room) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="scheduled"
                                    {{ old('status', $surgery->status) === 'scheduled' ? 'selected' : '' }}>Scheduled
                                </option>
                                <option value="in_progress"
                                    {{ old('status', $surgery->status) === 'in_progress' ? 'selected' : '' }}>In
                                    Progress</option>
                                <option value="completed"
                                    {{ old('status', $surgery->status) === 'completed' ? 'selected' : '' }}>Completed
                                </option>
                                <option value="cancelled"
                                    {{ old('status', $surgery->status) === 'cancelled' ? 'selected' : '' }}>Cancelled
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="estimated_duration" class="block text-sm font-medium text-gray-700">Estimated
                                Duration (minutes)</label>
                            <input type="number" name="estimated_duration"
                                value="{{ old('estimated_duration', $surgery->estimated_duration) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="actual_duration" class="block text-sm font-medium text-gray-700">Actual Duration
                                (minutes)</label>
                            <input type="number" name="actual_duration"
                                value="{{ old('actual_duration', $surgery->actual_duration) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="pre_op_diagnosis" class="block text-sm font-medium text-gray-700">Pre-Operative
                                Diagnosis</label>
                            <textarea name="pre_op_diagnosis" rows="2"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('pre_op_diagnosis', $surgery->pre_op_diagnosis) }}</textarea>
                        </div>

                        <div>
                            <label for="post_op_diagnosis"
                                class="block text-sm font-medium text-gray-700">Post-Operative Diagnosis</label>
                            <textarea name="post_op_diagnosis" rows="2"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('post_op_diagnosis', $surgery->post_op_diagnosis) }}</textarea>
                        </div>

                        <div>
                            <label for="surgical_findings" class="block text-sm font-medium text-gray-700">Surgical
                                Findings</label>
                            <textarea name="surgical_findings" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('surgical_findings', $surgery->surgical_findings) }}</textarea>
                        </div>

                        <div>
                            <label for="scheduled_at" class="block text-sm font-medium text-gray-700">Scheduled
                                Date/Time</label>
                            <input type="datetime-local" name="scheduled_at"
                                value="{{ old('scheduled_at', $surgery->scheduled_at ? $surgery->scheduled_at->format('Y-m-d\TH:i') : '') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Additional
                                Notes</label>
                            <textarea name="notes" rows="2"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes', $surgery->notes) }}</textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('healthcare.surgeries.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Update Surgery</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
