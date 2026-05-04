<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-video text-blue-600"></i> Book Teleconsultation
            </h1>
            <p class="text-gray-500">Schedule online consultation with doctor</p>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full md:w-2/3">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <form action="{{ route('healthcare.telemedicine.book.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label font-bold">Select Doctor</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                @forelse($availableDoctors ?? [] as $doctor)
                                    <div class="w-full md:w-1/2">
                                        <div
                                            class="bg-white rounded-2xl border border-gray-200 border-2 {{ old('doctor_id') == $doctor->id ? 'border-blue-500' : '' }}">
                                            <div class="p-5">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="doctor_id"
                                                        id="doctor{{ $doctor->id }}" value="{{ $doctor->id }}"
                                                        {{ old('doctor_id') == $doctor->id ? 'checked' : '' }} required>
                                                    <label class="form-check-label w-full" for="doctor{{ $doctor->id }}">
                                                        <div class="flex items-center">
                                                            <div class="me-3">
                                                                <div class="rounded-full bg-primary text-white flex items-center justify-center"
                                                                    style="width: 50px; height: 50px;">
                                                                    <i class="fas fa-user-md fa-lg"></i>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <strong>{{ $doctor->name }}</strong>
                                                                <br><small
                                                                    class="text-gray-500">{{ $doctor->specialty ?? 'General Practitioner' }}</small>
                                                                <br><small class="text-emerald-600">
                                                                    <i class="fas fa-check-circle"></i> Available
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="w-full">
                                        <p class="text-gray-500 text-center">No doctors available for teleconsultation</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Consultation Date</label>
                                <input type="date" name="consultation_date" class="form-control"
                                    value="{{ old('consultation_date', today()->format('Y-m-d')) }}" required
                                    min="{{ today()->format('Y-m-d') }}">
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Preferred Time</label>
                                <select name="preferred_time" class="form-select" required>
                                    <option value="">Select time slot</option>
                                    <option value="09:00">09:00 - 09:30</option>
                                    <option value="09:30">09:30 - 10:00</option>
                                    <option value="10:00">10:00 - 10:30</option>
                                    <option value="10:30">10:30 - 11:00</option>
                                    <option value="11:00">11:00 - 11:30</option>
                                    <option value="13:00">13:00 - 13:30</option>
                                    <option value="13:30">13:30 - 14:00</option>
                                    <option value="14:00">14:00 - 14:30</option>
                                    <option value="14:30">14:30 - 15:00</option>
                                    <option value="15:00">15:00 - 15:30</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Consultation Type</label>
                            <select name="consultation_type" class="form-select" required>
                                <option value="video">Video Call</option>
                                <option value="audio">Audio Call</option>
                                <option value="chat">Chat Consultation</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Chief Complaint</label>
                            <textarea name="chief_complaint" class="form-control" rows="3"
                                placeholder="Describe your symptoms or reason for consultation..." required>{{ old('chief_complaint') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Additional Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Any additional information...">{{ old('notes') }}</textarea>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Consultation Fee:</strong> Rp
                            {{ number_format($consultationFee ?? 150000, 0, ',', '.') }}
                            <br><small>Payment will be collected before the consultation begins.</small>
                        </div>

                        <button type="submit" class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-base font-medium transition">
                            <i class="fas fa-calendar-check"></i> Book Consultation
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="w-full md:w-1/3">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> How It Works
                    </h5>
                </div>
                <div class="p-5">
                    <div class="mb-3">
                        <div class="flex">
                            <div class="me-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 rounded-full"
                                    style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">1</span>
                            </div>
                            <div>
                                <strong>Book Appointment</strong>
                                <br><small class="text-gray-500">Choose doctor and time slot</small>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="flex">
                            <div class="me-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 rounded-full"
                                    style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">2</span>
                            </div>
                            <div>
                                <strong>Make Payment</strong>
                                <br><small class="text-gray-500">Pay consultation fee</small>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="flex">
                            <div class="me-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 rounded-full"
                                    style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">3</span>
                            </div>
                            <div>
                                <strong>Join Consultation</strong>
                                <br><small class="text-gray-500">Click link at scheduled time</small>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="flex">
                            <div class="me-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 rounded-full"
                                    style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">4</span>
                            </div>
                            <div>
                                <strong>Get Prescription</strong>
                                <br><small class="text-gray-500">Receive e-prescription</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 mt-3">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">
                        <i class="fas fa-headset"></i> Need Help?
                    </h5>
                </div>
                <div class="p-5">
                    <p class="mb-2"><i class="fas fa-phone mr-2"></i> <strong>Phone:</strong> (021) 1234-5678</p>
                    <p class="mb-2"><i class="fas fa-envelope mr-2"></i> <strong>Email:</strong>
                        telemedicine@hospital.com</p>
                    <p class="mb-0"><i class="fas fa-clock mr-2"></i> <strong>Hours:</strong> Mon-Sat, 08:00-17:00</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
