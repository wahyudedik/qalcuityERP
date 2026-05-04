<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Lab Order') }} -
            {{ $labOrder->order_number }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('healthcare.lab-orders.update', $labOrder) }}">
                    @csrf
                    @method('PUT')
                    <div class="space-y-6">
                        <div>
                            <label for="patient_visit_id" class="block text-sm font-medium text-gray-700">Patient Visit
                                *</label>
                            <select name="patient_visit_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Visit</option>
                                @foreach ($visits as $visit)
                                    <option value="{{ $visit->id }}"
                                        {{ old('patient_visit_id', $labOrder->patient_visit_id) == $visit->id ? 'selected' : '' }}>
                                        {{ $visit->patient?->name }} - {{ $visit->visit_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="lab_test_catalog_id" class="block text-sm font-medium text-gray-700">Test
                                *</label>
                            <select name="lab_test_catalog_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Test</option>
                                @foreach ($tests as $test)
                                    <option value="{{ $test->id }}"
                                        {{ old('lab_test_catalog_id', $labOrder->lab_test_catalog_id) == $test->id ? 'selected' : '' }}>
                                        {{ $test->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700">Priority *</label>
                            <select name="priority" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="routine"
                                    {{ old('priority', $labOrder->priority) === 'routine' ? 'selected' : '' }}>Routine
                                </option>
                                <option value="urgent"
                                    {{ old('priority', $labOrder->priority) === 'urgent' ? 'selected' : '' }}>Urgent
                                </option>
                                <option value="stat"
                                    {{ old('priority', $labOrder->priority) === 'stat' ? 'selected' : '' }}>STAT
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="pending"
                                    {{ old('status', $labOrder->status) === 'pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="in_progress"
                                    {{ old('status', $labOrder->status) === 'in_progress' ? 'selected' : '' }}>In
                                    Progress</option>
                                <option value="completed"
                                    {{ old('status', $labOrder->status) === 'completed' ? 'selected' : '' }}>Completed
                                </option>
                                <option value="cancelled"
                                    {{ old('status', $labOrder->status) === 'cancelled' ? 'selected' : '' }}>Cancelled
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="clinical_notes" class="block text-sm font-medium text-gray-700">Clinical
                                Notes</label>
                            <textarea name="clinical_notes" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('clinical_notes', $labOrder->clinical_notes) }}</textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('healthcare.lab-orders.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Update Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
