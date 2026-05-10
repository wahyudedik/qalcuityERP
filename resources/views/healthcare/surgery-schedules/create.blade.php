<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-calendar-plus mr-2"></i>{{ __('Jadwalkan Operasi Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('healthcare.surgery-schedules.store') }}">
                    @csrf
                    <div class="space-y-6">
                        {{-- Patient --}}
                        <div>
                            <label for="patient_id" class="block text-sm font-medium text-gray-700">Pasien *</label>
                            <select name="patient_id" id="patient_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih Pasien</option>
                                @foreach ($patients as $patient)
                                    <option value="{{ $patient->id }}"
                                        {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->name }} - {{ $patient->medical_record_number }}
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Doctor/Surgeon --}}
                        <div>
                            <label for="surgeon_id" class="block text-sm font-medium text-gray-700">Dokter Bedah
                                *</label>
                            <select name="surgeon_id" id="surgeon_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih Dokter Bedah</option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->id }}"
                                        {{ old('surgeon_id') == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->name }} - {{ $doctor->specialization }}
                                    </option>
                                @endforeach
                            </select>
                            @error('surgeon_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Operating Room --}}
                        <div>
                            <label for="operating_room_id" class="block text-sm font-medium text-gray-700">Ruang Operasi
                                *</label>
                            <select name="operating_room_id" id="operating_room_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih Ruang Operasi</option>
                                @foreach ($operatingRooms as $room)
                                    <option value="{{ $room->id }}"
                                        {{ old('operating_room_id') == $room->id ? 'selected' : '' }}>
                                        {{ $room->name }} - {{ $room->room_number }}
                                    </option>
                                @endforeach
                            </select>
                            @error('operating_room_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Surgery Type --}}
                        <div>
                            <label for="surgery_type" class="block text-sm font-medium text-gray-700">Jenis Operasi
                                *</label>
                            <input type="text" name="surgery_type" id="surgery_type" required
                                value="{{ old('surgery_type') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Contoh: Appendektomi">
                            @error('surgery_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Scheduled Date --}}
                        <div>
                            <label for="scheduled_date" class="block text-sm font-medium text-gray-700">Tanggal Operasi
                                *</label>
                            <input type="date" name="scheduled_date" id="scheduled_date" required
                                value="{{ old('scheduled_date') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('scheduled_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Start Time --}}
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700">Waktu Mulai
                                *</label>
                            <input type="time" name="start_time" id="start_time" required
                                value="{{ old('start_time') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('start_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Estimated Duration --}}
                        <div>
                            <label for="estimated_duration" class="block text-sm font-medium text-gray-700">Estimasi
                                Durasi (menit) *</label>
                            <input type="number" name="estimated_duration" id="estimated_duration" required
                                value="{{ old('estimated_duration') }}" min="15"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Contoh: 120">
                            @error('estimated_duration')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Diagnosis --}}
                        <div>
                            <label for="diagnosis" class="block text-sm font-medium text-gray-700">Diagnosis</label>
                            <textarea name="diagnosis" id="diagnosis" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Diagnosis pra-operasi...">{{ old('diagnosis') }}</textarea>
                            @error('diagnosis')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Procedure Notes --}}
                        <div>
                            <label for="procedure_notes" class="block text-sm font-medium text-gray-700">Catatan
                                Prosedur</label>
                            <textarea name="procedure_notes" id="procedure_notes" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Catatan khusus prosedur...">{{ old('procedure_notes') }}</textarea>
                            @error('procedure_notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('healthcare.surgery-schedules.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            <i class="fas fa-times mr-1"></i>Batal
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            <i class="fas fa-save mr-2"></i>Simpan Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
