<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Konsultasi Telemedicine Baru') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                <form method="POST" action="{{ route('healthcare.telemedicine.book') }}">
                    @csrf
                    <div class="space-y-6">
                        <div>
                            <label for="patient_id"
                                class="block text-sm font-medium text-gray-700">Pasien *</label>
                            <select name="patient_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih Pasien</option>
                                @foreach ($patients ?? [] as $patient)
                                    <option value="{{ $patient->id }}"
                                        {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->full_name ?? $patient->name }} -
                                        {{ $patient->medical_record_number ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="doctor_id"
                                class="block text-sm font-medium text-gray-700">Dokter *</label>
                            <select name="doctor_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih Dokter</option>
                                @foreach ($doctors ?? [] as $doctor)
                                    <option value="{{ $doctor->id }}"
                                        {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->name }} - {{ $doctor->specialization ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="consultation_type"
                                class="block text-sm font-medium text-gray-700">Tipe Konsultasi
                                *</label>
                            <select name="consultation_type" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="video" {{ old('consultation_type') === 'video' ? 'selected' : '' }}>
                                    Video Call</option>
                                <option value="voice" {{ old('consultation_type') === 'voice' ? 'selected' : '' }}>
                                    Voice Call</option>
                                <option value="chat" {{ old('consultation_type') === 'chat' ? 'selected' : '' }}>Chat
                                </option>
                            </select>
                            @error('consultation_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="reason"
                                class="block text-sm font-medium text-gray-700">Keluhan Utama
                                *</label>
                            <textarea name="reason" required rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Jelaskan keluhan utama...">{{ old('reason') }}</textarea>
                            @error('reason')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="consultation_date"
                                    class="block text-sm font-medium text-gray-700">Tanggal
                                    Konsultasi *</label>
                                <input type="date" name="consultation_date" required
                                    value="{{ old('consultation_date', now()->addDay()->format('Y-m-d')) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('consultation_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="consultation_time"
                                    class="block text-sm font-medium text-gray-700">Waktu Konsultasi
                                    *</label>
                                <input type="time" name="consultation_time" required
                                    value="{{ old('consultation_time', '09:00') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('consultation_time')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="notes"
                                class="block text-sm font-medium text-gray-700">Catatan
                                Tambahan</label>
                            <textarea name="notes" rows="2"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Informasi tambahan...">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row justify-end gap-3">
                        <a href="{{ route('healthcare.telemedicine.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-center">Batal</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Jadwalkan Konsultasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
