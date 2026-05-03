<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Konsultasi Telemedicine') }} -
            {{ $telemedicine->consultation_number ?? '' }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                <form method="POST" action="{{ route('healthcare.teleconsultations.update', $telemedicine) }}">
                    @csrf
                    @method('PUT')
                    <div class="space-y-6">
                        <div>
                            <label for="consultation_type"
                                class="block text-sm font-medium text-gray-700">Tipe Konsultasi
                                *</label>
                            <select name="consultation_type" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="video"
                                    {{ old('consultation_type', $telemedicine->consultation_type ?? $telemedicine->platform) === 'video' ? 'selected' : '' }}>
                                    Video Call</option>
                                <option value="voice"
                                    {{ old('consultation_type', $telemedicine->consultation_type ?? $telemedicine->platform) === 'voice' ? 'selected' : '' }}>
                                    Voice Call</option>
                                <option value="chat"
                                    {{ old('consultation_type', $telemedicine->consultation_type ?? $telemedicine->platform) === 'chat' ? 'selected' : '' }}>
                                    Chat</option>
                            </select>
                            @error('consultation_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status"
                                class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="scheduled"
                                    {{ old('status', $telemedicine->status) === 'scheduled' ? 'selected' : '' }}>
                                    Terjadwal</option>
                                <option value="in_progress"
                                    {{ old('status', $telemedicine->status) === 'in_progress' ? 'selected' : '' }}>
                                    Berlangsung</option>
                                <option value="completed"
                                    {{ old('status', $telemedicine->status) === 'completed' ? 'selected' : '' }}>
                                    Selesai</option>
                                <option value="cancelled"
                                    {{ old('status', $telemedicine->status) === 'cancelled' ? 'selected' : '' }}>
                                    Dibatalkan</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="chief_complaint"
                                class="block text-sm font-medium text-gray-700">Keluhan Utama
                                *</label>
                            <textarea name="chief_complaint" required rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('chief_complaint', $telemedicine->chief_complaint) }}</textarea>
                            @error('chief_complaint')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="diagnosis"
                                class="block text-sm font-medium text-gray-700">Diagnosis</label>
                            <textarea name="diagnosis" rows="2"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('diagnosis', $telemedicine->diagnosis) }}</textarea>
                        </div>

                        <div>
                            <label for="treatment_plan"
                                class="block text-sm font-medium text-gray-700">Rencana
                                Perawatan</label>
                            <textarea name="treatment_plan" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Rencana perawatan...">{{ old('treatment_plan', $telemedicine->treatment_plan) }}</textarea>
                        </div>

                        <div>
                            <label for="notes"
                                class="block text-sm font-medium text-gray-700">Catatan
                                Tambahan</label>
                            <textarea name="notes" rows="2"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes', $telemedicine->notes) }}</textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row justify-end gap-3">
                        <a href="{{ route('healthcare.telemedicine.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-center">Batal</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Perbarui Konsultasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
