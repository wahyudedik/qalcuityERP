<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add Lab Equipment') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('healthcare.lab-equipment.store') }}">
                    @csrf
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Equipment Name
                                    *</label>
                                <input type="text" name="name" required value="{{ old('name') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="e.g., Sysmex XN-1000">
                            </div>
                            <div>
                                <label for="device_id" class="block text-sm font-medium text-gray-700">Device ID
                                    *</label>
                                <input type="text" name="device_id" required value="{{ old('device_id') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="e.g., SYSMEX-001">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">Equipment Type
                                    *</label>
                                <select name="type" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Type</option>
                                    <option value="hematology" {{ old('type') === 'hematology' ? 'selected' : '' }}>
                                        Hematology Analyzer</option>
                                    <option value="chemistry" {{ old('type') === 'chemistry' ? 'selected' : '' }}>
                                        Chemistry Analyzer</option>
                                    <option value="immunoassay" {{ old('type') === 'immunoassay' ? 'selected' : '' }}>
                                        Immunoassay Analyzer</option>
                                    <option value="urinalysis" {{ old('type') === 'urinalysis' ? 'selected' : '' }}>
                                        Urinalysis Analyzer</option>
                                    <option value="coagulation" {{ old('type') === 'coagulation' ? 'selected' : '' }}>
                                        Coagulation Analyzer</option>
                                    <option value="microscope" {{ old('type') === 'microscope' ? 'selected' : '' }}>
                                        Digital Microscope</option>
                                </select>
                            </div>
                            <div>
                                <label for="connection_type" class="block text-sm font-medium text-gray-700">Connection
                                    Type *</label>
                                <select name="connection_type" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Connection</option>
                                    <option value="hl7" {{ old('connection_type') === 'hl7' ? 'selected' : '' }}>HL7
                                        Protocol</option>
                                    <option value="astm" {{ old('connection_type') === 'astm' ? 'selected' : '' }}>
                                        ASTM Protocol</option>
                                    <option value="serial" {{ old('connection_type') === 'serial' ? 'selected' : '' }}>
                                        Serial (RS-232)</option>
                                    <option value="tcp" {{ old('connection_type') === 'tcp' ? 'selected' : '' }}>
                                        TCP/IP</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="ip_address" class="block text-sm font-medium text-gray-700">IP
                                    Address</label>
                                <input type="text" name="ip_address" value="{{ old('ip_address') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="e.g., 192.168.1.100">
                            </div>
                            <div>
                                <label for="poll_interval" class="block text-sm font-medium text-gray-700">Poll Interval
                                    (seconds) *</label>
                                <input type="number" name="poll_interval" required
                                    value="{{ old('poll_interval', 30) }}" min="1" max="60"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="auto_poll_enabled" id="auto_poll_enabled" value="1"
                                {{ old('auto_poll_enabled') ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="auto_poll_enabled" class="ml-2 block text-sm text-gray-900">Enable
                                Auto-Poll</label>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('healthcare.lab-equipment.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Add Equipment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
