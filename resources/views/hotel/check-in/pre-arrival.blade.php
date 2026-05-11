<x-app-layout>
    <x-slot name="header">Pre-Arrival Form — Reservation #{{ $reservation->reservation_number }}</x-slot>

    @php
        $guest = $reservation->guest;
        $roomType = $reservation->roomType;
    @endphp

    <div class="max-w-5xl mx-auto space-y-6">
        {{-- Info Banner --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-blue-900">Pre-Arrival Registration</h3>
                    <p class="text-sm text-blue-700 mt-1">
                        Please complete this form before your arrival to speed up the check-in process.
                    </p>
                </div>
            </div>
        </div>

        {{-- Guest & Reservation Info --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">Guest
                    Information</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500">Name</p>
                        <p class="font-medium text-gray-900">{{ $guest?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Email</p>
                        <p class="font-medium text-gray-900">{{ $guest?->email ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Phone</p>
                        <p class="font-medium text-gray-900">{{ $guest?->phone ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">
                    Reservation Details</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500">Room Type</p>
                        <p class="font-medium text-gray-900">{{ $roomType?->name ?? '-' }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500">Check-in</p>
                            <p class="font-medium text-gray-900">
                                {{ $reservation->check_in_date->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Check-out</p>
                            <p class="font-medium text-gray-900">
                                {{ $reservation->check_out_date->format('d M Y') }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Nights</p>
                        <p class="font-medium text-gray-900">{{ $reservation->nights }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pre-Arrival Form --}}
        <form method="POST" action="{{ route('hotel.checkin.pre-arrival.submit', $reservation) }}"
            class="bg-white rounded-xl border border-gray-200 p-6">
            @csrf

            {{-- Section 1: ID Information --}}
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                    Identification Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Type</label>
                        <select name="id_type" value="{{ old('id_type', $form->id_type ?? '') }}"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">
                            <option value="">Select ID Type</option>
                            <option value="passport">Passport</option>
                            <option value="ktp">KTP (National ID)</option>
                            <option value="sim">Driver's License</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Number</label>
                        <input type="text" name="id_number" value="{{ old('id_number', $form->id_number ?? '') }}"
                            placeholder="Enter your ID number"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Expiry
                            Date</label>
                        <input type="date" name="id_expiry" value="{{ old('id_expiry', $form->id_expiry ?? '') }}"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nationality</label>
                        <input type="text" name="nationality"
                            value="{{ old('nationality', $form->nationality ?? 'Indonesian') }}"
                            placeholder="e.g., Indonesian"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date of
                            Birth</label>
                        <input type="date" name="date_of_birth"
                            value="{{ old('date_of_birth', $form->date_of_birth ?? '') }}"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                        <select name="gender"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Section 2: Emergency Contact --}}
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    Emergency Contact
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Contact
                            Name</label>
                        <input type="text" name="emergency_contact_name"
                            value="{{ old('emergency_contact_name', $form->emergency_contact_name ?? '') }}"
                            placeholder="Full name"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Contact
                            Phone</label>
                        <input type="text" name="emergency_contact_phone"
                            value="{{ old('emergency_contact_phone', $form->emergency_contact_phone ?? '') }}"
                            placeholder="+62 xxx xxxx xxxx"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Relationship</label>
                        <input type="text" name="emergency_contact_relationship"
                            value="{{ old('emergency_contact_relationship', $form->emergency_contact_relationship ?? '') }}"
                            placeholder="e.g., Spouse, Parent"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            {{-- Section 3: Room Preferences --}}
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    Room & Bed Preferences
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Room
                            Preference</label>
                        <select name="room_preference"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">
                            <option value="">No Preference</option>
                            <option value="high_floor">High Floor</option>
                            <option value="low_floor">Low Floor</option>
                            <option value="near_elevator">Near Elevator</option>
                            <option value="away_from_elevator">Away from Elevator</option>
                            <option value="ocean_view">Ocean View</option>
                            <option value="city_view">City View</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bed
                            Preference</label>
                        <select name="bed_preference"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">
                            <option value="">No Preference</option>
                            <option value="twin">Twin Beds</option>
                            <option value="king">King Bed</option>
                            <option value="queen">Queen Bed</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Section 4: Arrival Details --}}
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Arrival Details
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estimated
                            Arrival Time</label>
                        <input type="time" name="estimated_arrival_time"
                            value="{{ old('estimated_arrival_time', $form->estimated_arrival_time ?? '') }}"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Transportation
                            Method</label>
                        <select name="transportation_method"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Method</option>
                            <option value="taxi">Taxi</option>
                            <option value="airport_shuttle">Airport Shuttle</option>
                            <option value="private_car">Private Car</option>
                            <option value="public_transport">Public Transport</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Flight Number
                            (if applicable)</label>
                        <input type="text" name="flight_number"
                            value="{{ old('flight_number', $form->flight_number ?? '') }}" placeholder="e.g., GA123"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="airport_pickup_required" value="1"
                            {{ old('airport_pickup_required', $form->airport_pickup_required ?? false) ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label class="ml-2 text-sm text-gray-700">
                            Require Airport Pickup
                        </label>
                    </div>
                </div>
            </div>

            {{-- Section 5: Special Requests --}}
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Special Requests & Dietary Requirements
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Dietary
                            Requirements</label>
                        <textarea name="dietary_requirements" rows="3"
                            placeholder="Any allergies, dietary restrictions, or special meal requests..."
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">{{ old('dietary_requirements', $form->dietary_requirements ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Special
                            Requests</label>
                        <textarea name="special_requests" rows="3" placeholder="Any special requests or notes for your stay..."
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">{{ old('special_requests', is_array($form->special_requests ?? null) ? implode("\n", $form->special_requests) : $form->special_requests ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Section 6: Terms & Consent --}}
            <div x-data="{
                showPolicy: false,
                policyTitle: '',
                policyContent: '',
                openPolicy(title, content) {
                    this.policyTitle = title;
                    this.policyContent = content;
                    this.showPolicy = true;
                }
            }" class="mb-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Terms & Consent</h3>
                <div class="space-y-3">
                    <label class="flex items-start">
                        <input type="checkbox" name="terms_accepted" required
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-1">
                        <span class="ml-2 text-sm text-gray-700">
                            I agree to the hotel's <a
                                @click.prevent="openPolicy('Terms & Conditions', 'Dengan melakukan reservasi dan check-in di hotel kami, Anda menyetujui syarat dan ketentuan berikut:\n\n1. Check-in dilakukan mulai pukul 14:00 dan check-out paling lambat pukul 12:00 waktu setempat.\n2. Tamu wajib menunjukkan identitas resmi (KTP/Passport) saat check-in.\n3. Hotel berhak menolak tamu yang melanggar peraturan hotel.\n4. Kerusakan pada fasilitas kamar menjadi tanggung jawab tamu.\n5. Barang berharga harap disimpan di safety deposit box yang tersedia.\n6. Hotel tidak bertanggung jawab atas kehilangan barang pribadi tamu.\n7. Dilarang merokok di area non-smoking. Pelanggaran dikenakan denda.\n8. Tamu tambahan yang tidak terdaftar akan dikenakan biaya tambahan.\n9. Hewan peliharaan tidak diperkenankan kecuali di kamar pet-friendly.\n10. Manajemen berhak mengubah syarat dan ketentuan ini sewaktu-waktu.')"
                                class="text-blue-600 hover:underline cursor-pointer">Terms &
                                Conditions</a> and <a
                                @click.prevent="openPolicy('Cancellation Policy', 'Kebijakan pembatalan reservasi hotel kami adalah sebagai berikut:\n\n1. Pembatalan gratis jika dilakukan minimal 48 jam sebelum tanggal check-in.\n2. Pembatalan dalam 24-48 jam sebelum check-in dikenakan biaya 50% dari tarif malam pertama.\n3. Pembatalan kurang dari 24 jam atau no-show dikenakan biaya penuh malam pertama.\n4. Untuk reservasi non-refundable, tidak ada pengembalian dana untuk pembatalan kapan pun.\n5. Perubahan tanggal dapat dilakukan tanpa biaya tambahan (tergantung ketersediaan) jika diajukan minimal 48 jam sebelumnya.\n6. Pengembalian dana akan diproses dalam 7-14 hari kerja ke metode pembayaran asal.\n7. Untuk reservasi grup (5 kamar atau lebih), kebijakan pembatalan khusus berlaku — silakan hubungi reservasi.\n8. Force majeure (bencana alam, pandemi) akan ditangani secara case-by-case.')"
                                class="text-blue-600 hover:underline cursor-pointer">Cancellation Policy</a> *
                        </span>
                    </label>
                    <label class="flex items-start">
                        <input type="checkbox" name="data_processing_consent" required
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-1">
                        <span class="ml-2 text-sm text-gray-700">
                            I consent to the processing of my personal data in accordance with the <a
                                @click.prevent="openPolicy('Privacy Policy', 'Kebijakan privasi kami menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi data pribadi Anda:\n\n1. Data yang Dikumpulkan: Nama, email, nomor telepon, identitas (KTP/Passport), informasi pembayaran, dan preferensi menginap.\n2. Tujuan Penggunaan: Memproses reservasi, meningkatkan layanan, komunikasi terkait pemesanan, dan (dengan persetujuan) penawaran promosi.\n3. Penyimpanan Data: Data disimpan dengan enkripsi dan akses terbatas selama diperlukan untuk tujuan bisnis yang sah.\n4. Hak Anda: Anda berhak mengakses, memperbarui, atau meminta penghapusan data pribadi Anda kapan saja.\n5. Berbagi Data: Kami tidak menjual data Anda. Data hanya dibagikan kepada pihak ketiga yang diperlukan untuk layanan (payment gateway, sistem reservasi).\n6. Cookies: Website kami menggunakan cookies untuk meningkatkan pengalaman pengguna.\n7. Keamanan: Kami menerapkan standar keamanan industri untuk melindungi data Anda.\n8. Kontak: Untuk pertanyaan terkait privasi, hubungi privacy@hotel.com.\n\nKebijakan ini sesuai dengan UU Perlindungan Data Pribadi (UU PDP) Republik Indonesia.')"
                                class="text-blue-600 hover:underline cursor-pointer">Privacy Policy</a> *
                        </span>
                    </label>
                    <label class="flex items-start">
                        <input type="checkbox" name="marketing_consent" value="1"
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-1">
                        <span class="ml-2 text-sm text-gray-700">
                            I would like to receive promotional offers and updates (optional)
                        </span>
                    </label>
                </div>

                {{-- Policy Modal --}}
                <div x-show="showPolicy" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    x-cloak>
                    {{-- Backdrop --}}
                    <div @click="showPolicy = false" class="absolute inset-0 bg-black/50"></div>
                    {{-- Modal Content --}}
                    <div x-show="showPolicy" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="relative bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[80vh] flex flex-col">
                        {{-- Header --}}
                        <div class="flex items-center justify-between p-5 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900" x-text="policyTitle"></h3>
                            <button @click="showPolicy = false" type="button"
                                class="text-gray-400 hover:text-gray-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        {{-- Body --}}
                        <div class="p-5 overflow-y-auto">
                            <p class="text-sm text-gray-700 whitespace-pre-line" x-text="policyContent"></p>
                        </div>
                        {{-- Footer --}}
                        <div class="p-4 border-t border-gray-200 flex justify-end">
                            <button @click="showPolicy = false" type="button"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row gap-3 justify-between items-center pt-6 border-t border-gray-200">
                <a href="{{ route('hotel.checkin-out.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                    ← Back to Dashboard
                </a>
                <button type="submit"
                    class="w-full sm:w-auto px-8 py-3 text-base font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Submit Pre-Arrival Form
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
