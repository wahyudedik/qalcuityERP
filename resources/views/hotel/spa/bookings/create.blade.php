<x-app-layout title="Booking Baru">
    <x-slot name="header">Booking Baru</x-slot>

    <x-slot name="pageTitle">Buat Booking Spa</x-slot>

    <div class="max-w-2xl">
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <form method="POST" action="{{ route('hotel.spa.bookings.store') }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Tamu</label>
                        <input type="text" name="guest_name" value="{{ old('guest_name') }}" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">
                        @error('guest_name')
                            <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">No. Kamar (opsional)</label>
                        <input type="text" name="room_number" value="{{ old('room_number') }}"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal</label>
                        <input type="date" name="booking_date"
                            value="{{ old('booking_date', today()->format('Y-m-d')) }}" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">
                        @error('booking_date')
                            <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Waktu Mulai</label>
                        <input type="time" name="start_time" value="{{ old('start_time') }}" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">
                        @error('start_time')
                            <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Treatment</label>
                    <select name="treatment_id"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Treatment --</option>
                        @foreach ($treatments as $treatment)
                            <option value="{{ $treatment->id }}"
                                {{ old('treatment_id') == $treatment->id ? 'selected' : '' }}>
                                {{ $treatment->name }} ({{ $treatment->duration }} menit - Rp
                                {{ number_format($treatment->price, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                    @error('treatment_id')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Atau Paket</label>
                    <select name="package_id"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Paket --</option>
                        @foreach ($packages as $package)
                            <option value="{{ $package->id }}"
                                {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                {{ $package->name }} (Rp {{ number_format($package->price ?? 0, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                    @error('package_id')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Terapis</label>
                    <select name="therapist_id"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Terapis --</option>
                        @foreach ($therapists as $therapist)
                            <option value="{{ $therapist->id }}"
                                {{ old('therapist_id') == $therapist->id ? 'selected' : '' }}>
                                {{ $therapist->name }}
                                {{ $therapist->specialization ? '(' . $therapist->specialization . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('therapist_id')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan (opsional)</label>
                    <textarea name="notes" rows="3"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-3">
                    <button type="submit"
                        class="px-5 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition">
                        Simpan Booking
                    </button>
                    <a href="{{ route('hotel.spa.bookings.index') }}"
                        class="px-5 py-2 text-sm font-medium border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
