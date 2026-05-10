<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-edit mr-2"></i>{{ __('Edit Jadwal Operasi') }} - {{ $model->surgery_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('healthcare.surgery-schedules.update', $model) }}">
                    @csrf
                    @method('PUT')
                    <div class="space-y-6">
                        {{-- Patient --}}
                        <div>
                            <label for="patient_id" class="block text-sm font-medium text-gray-700">Pasien *</label>
                            <input type="text" id="patient_id_display" value="{{ $model->patient?->name }}" readonly
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 focus:border-blue-500 focus:ring-blue-500">
                            <input type="hidden" name="patient_id" value="{{ old('patient_id', $model->patient_id) }}">
                            @error('patient_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Surgeon --}}
                        <div>
                            <label for="surgeon_id" class="block text-sm font-medium text-gray-700">Dokter Bedah
                                *</label>
                            <input type="text" id="surgeon_id_display" value="{{ $model->surgeon?->name }}" readonly
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 focus:border-blue-500 focus:ring-blue-500">
                            <input type="hidden" name="surgeon_id" value="{{ old('surgeon_id', $model->surgeon_id) }}">
                            @error('surgeon_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Operating Room --}}
                        <div>
                            <label for="operating_room_id" class="block text-sm font-medium text-gray-700">Ruang Operasi
                                *</label>
                            <input type="text" id="operating_room_id_display"
                                value="{{ $model->operatingRoom?->name }}" readonly
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 focus:border-blue-500 focus:ring-blue-500">
                            <input type="hidden" name="operating_room_id"
                                value="{{ old('operating_room_id', $model->operating_room_id) }}">
                            @error('operating_room_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Surgery Type --}}
                        <div>
                            <label for="surgery_type" class="block text-sm font-medium text-gray-700">Jenis Operasi
                                *</label>
                            <input type="text" name="surgery_type" id="surgery_type" required
                                value="{{ old('surgery_type', $model->surgery_type) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('surgery_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Scheduled Date --}}
                        <div>
                            <label for="scheduled_date" class="block text-sm font-medium text-gray-700">Tanggal Operasi
                                *</label>
                            <input type="date" name="scheduled_date" id="scheduled_date" required
                                value="{{ old('scheduled_date', $model->scheduled_date?->format('Y-m-d')) }}"
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
                                value="{{ old('start_time', $model->scheduled_start_time) }}"
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
                                value="{{ old('estimated_duration', $model->estimated_duration) }}" min="15"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('estimated_duration')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Priority --}}
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700">Prioritas</label>
                            <select name="priority" id="priority"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="low"
                                    {{ old('priority', $model->priority) === 'low' ? 'selected' : '' }}>Rendah</option>
                                <option value="normal"
                                    {{ old('priority', $model->priority) === 'normal' ? 'selected' : '' }}>Normal
                                </option>
                                <option value="high"
                                    {{ old('priority', $model->priority) === 'high' ? 'selected' : '' }}>Tinggi
                                </option>
                                <option value="emergency"
                                    {{ old('priority', $model->priority) === 'emergency' ? 'selected' : '' }}>Darurat
                                </option>
                            </select>
                            @error('priority')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="scheduled"
                                    {{ old('status', $model->status) === 'scheduled' ? 'selected' : '' }}>Terjadwal
                                </option>
                                <option value="in_progress"
                                    {{ old('status', $model->status) === 'in_progress' ? 'selected' : '' }}>Sedang
                                    Berlangsung</option>
                                <option value="completed"
                                    {{ old('status', $model->status) === 'completed' ? 'selected' : '' }}>Selesai
                                </option>
                                <option value="cancelled"
                                    {{ old('status', $model->status) === 'cancelled' ? 'selected' : '' }}>Dibatalkan
                                </option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Diagnosis --}}
                        <div>
                            <label for="diagnosis" class="block text-sm font-medium text-gray-700">Diagnosis</label>
                            <textarea name="diagnosis" id="diagnosis" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Diagnosis pra-operasi...">{{ old('diagnosis', $model->pre_operative_diagnosis) }}</textarea>
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
                                placeholder="Catatan khusus prosedur...">{{ old('procedure_notes', $model->preoperative_notes) }}</textarea>
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
                            <i class="fas fa-save mr-2"></i>Perbarui Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
