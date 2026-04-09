<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('New Message') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('healthcare.patient-messages.store') }}">
                    @csrf
                    <div class="space-y-6">
                        <div>
                            <label for="recipient_id" class="block text-sm font-medium text-gray-700">Recipient *</label>
                            <select name="recipient_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Recipient</option>
                                @foreach ($patients as $patient)
                                    <option value="{{ $patient->user_id }}"
                                        {{ old('recipient_id') == $patient->user_id ? 'selected' : '' }}>
                                        {{ $patient->full_name }} - {{ $patient->medical_record_number }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700">Category *</label>
                                <select name="category" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Category</option>
                                    <option value="general" {{ old('category') === 'general' ? 'selected' : '' }}>
                                        General</option>
                                    <option value="prescription"
                                        {{ old('category') === 'prescription' ? 'selected' : '' }}>Prescription</option>
                                    <option value="test_results"
                                        {{ old('category') === 'test_results' ? 'selected' : '' }}>Test Results</option>
                                    <option value="appointment"
                                        {{ old('category') === 'appointment' ? 'selected' : '' }}>Appointment</option>
                                    <option value="billing" {{ old('category') === 'billing' ? 'selected' : '' }}>
                                        Billing</option>
                                    <option value="symptoms" {{ old('category') === 'symptoms' ? 'selected' : '' }}>
                                        Symptoms</option>
                                    <option value="follow_up" {{ old('category') === 'follow_up' ? 'selected' : '' }}>
                                        Follow-up</option>
                                </select>
                            </div>
                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700">Priority *</label>
                                <select name="priority" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low
                                    </option>
                                    <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }}>Medium
                                    </option>
                                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High
                                    </option>
                                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700">Subject *</label>
                            <input type="text" name="subject" required value="{{ old('subject') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Message subject">
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700">Message *</label>
                            <textarea name="message" required rows="10"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Type your message here...">{{ old('message') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('healthcare.patient-messages.inbox') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-paper-plane mr-2"></i>Send Message</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
