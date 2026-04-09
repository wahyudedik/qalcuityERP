<x-app-layout>
    <x-slot name="header">Edit Pasien - {{ $patient->full_name }}</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Data Pasien', 'url' => route('healthcare.patients.index')],
        ['label' => 'Edit Pasien'],
    ]" />

    <div class="py-4">
        <form action="{{ route('healthcare.patients.update', $patient) }}" method="POST" x-data="{ loading: false }"
            @submit="loading = true" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Personal Information --}}
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Pribadi</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="full_name" value="{{ old('full_name', $patient->full_name) }}"
                                required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 @error('full_name') border-red-500 @enderror">
                            @error('full_name')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">NIK</label>
                            <input type="text" name="nik" value="{{ old('nik', $patient->nik) }}" maxlength="16"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nik') border-red-500 @enderror">
                            @error('nik')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                No. Kartu Keluarga
                            </label>
                            <input type="text" name="family_card_number"
                                value="{{ old('family_card_number', $patient->family_card_number) }}" maxlength="16"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                Tanggal Lahir <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="date_of_birth"
                                value="{{ old('date_of_birth', $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('Y-m-d') : '') }}"
                                required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 @error('date_of_birth') border-red-500 @enderror">
                            @error('date_of_birth')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                Tempat Lahir
                            </label>
                            <input type="text" name="place_of_birth"
                                value="{{ old('place_of_birth', $patient->place_of_birth) }}"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                Gender <span class="text-red-500">*</span>
                            </label>
                            <select name="gender" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 @error('gender') border-red-500 @enderror">
                                <option value="">Pilih Gender</option>
                                <option value="male" @selected(old('gender', $patient->gender) === 'male')>Laki-laki</option>
                                <option value="female" @selected(old('gender', $patient->gender) === 'female')>Perempuan</option>
                            </select>
                            @error('gender')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                Golongan Darah
                            </label>
                            <select name="blood_type"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih Golongan Darah</option>
                                <option value="A" @selected(old('blood_type', $patient->blood_type) === 'A')>A</option>
                                <option value="B" @selected(old('blood_type', $patient->blood_type) === 'B')>B</option>
                                <option value="AB" @selected(old('blood_type', $patient->blood_type) === 'AB')>AB</option>
                                <option value="O" @selected(old('blood_type', $patient->blood_type) === 'O')>O</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                Status Pernikahan
                            </label>
                            <select name="marital_status"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih Status</option>
                                <option value="single" @selected(old('marital_status', $patient->marital_status) === 'single')>Belum Menikah</option>
                                <option value="married" @selected(old('marital_status', $patient->marital_status) === 'married')>Menikah</option>
                                <option value="divorced" @selected(old('marital_status', $patient->marital_status) === 'divorced')>Cerai</option>
                                <option value="widowed" @selected(old('marital_status', $patient->marital_status) === 'widowed')>Janda/Duda</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                Agama
                            </label>
                            <select name="religion"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih Agama</option>
                                <option value="islam" @selected(old('religion', $patient->religion) === 'islam')>Islam</option>
                                <option value="christian" @selected(old('religion', $patient->religion) === 'christian')>Kristen</option>
                                <option value="catholic" @selected(old('religion', $patient->religion) === 'catholic')>Katolik</option>
                                <option value="hindu" @selected(old('religion', $patient->religion) === 'hindu')>Hindu</option>
                                <option value="buddhist" @selected(old('religion', $patient->religion) === 'buddhist')>Buddha</option>
                                <option value="confucian" @selected(old('religion', $patient->religion) === 'confucian')>Konghucu</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Status
                                Pasien</label>
                            <select name="status"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="active" @selected(old('status', $patient->status) === 'active')>Aktif</option>
                                <option value="inactive" @selected(old('status', $patient->status) === 'inactive')>Nonaktif</option>
                                <option value="deceased" @selected(old('status', $patient->status) === 'deceased')>Meninggal</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Alamat</label>
                            <textarea name="address" rows="3"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 @error('address') border-red-500 @enderror">{{ old('address', $patient->address) }}</textarea>
                            @error('address')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">RT/RW</label>
                            <input type="text" name="rt_rw" value="{{ old('rt_rw', $patient->rt_rw) }}"
                                placeholder="001/002"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Kelurahan/Desa</label>
                            <input type="text" name="village" value="{{ old('village', $patient->village) }}"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Kecamatan</label>
                            <input type="text" name="district" value="{{ old('district', $patient->district) }}"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Kota/Kabupaten</label>
                            <input type="text" name="city" value="{{ old('city', $patient->city) }}"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Provinsi</label>
                            <input type="text" name="province" value="{{ old('province', $patient->province) }}"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Kode
                                Pos</label>
                            <input type="text" name="postal_code"
                                value="{{ old('postal_code', $patient->postal_code) }}" maxlength="5"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact Information --}}
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Kontak</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                Telepon <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" name="phone" value="{{ old('phone', $patient->phone) }}"
                                required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 @error('phone') border-red-500 @enderror">
                            @error('phone')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Email</label>
                            <input type="email" name="email" value="{{ old('email', $patient->email) }}"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                            @error('email')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Kontak
                                Darurat</label>
                            <input type="tel" name="emergency_contact"
                                value="{{ old('emergency_contact', $patient->emergency_contact) }}"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Nama Kontak
                                Darurat</label>
                            <input type="text" name="emergency_contact_name"
                                value="{{ old('emergency_contact_name', $patient->emergency_contact_name) }}"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Medical Information --}}
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Medis</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Alergi</label>
                            <textarea name="allergies" rows="2" placeholder="Contoh: Penisilin, Kacang, dll"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('allergies', $patient->allergies) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500 dark:text-slate-400">Pisahkan dengan koma jika lebih
                                dari satu</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Riwayat
                                Penyakit</label>
                            <textarea name="medical_history" rows="3" placeholder="Contoh: Diabetes, Hipertensi, dll"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('medical_history', $patient->medical_history) }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Obat yang
                                Sedang Dikonsumsi</label>
                            <textarea name="current_medications" rows="2"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('current_medications', $patient->current_medications) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Insurance Information --}}
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Asuransi</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Penyedia
                                Asuransi</label>
                            <input type="text" name="insurance_provider"
                                value="{{ old('insurance_provider', $patient->insurance_provider) }}"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">No.
                                Polis</label>
                            <input type="text" name="insurance_policy"
                                value="{{ old('insurance_policy', $patient->insurance_policy) }}"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Tipe
                                Asuransi</label>
                            <select name="insurance_type"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih Tipe</option>
                                <option value="bpjs" @selected(old('insurance_type', $patient->insurance_type) === 'bpjs')>BPJS</option>
                                <option value="private" @selected(old('insurance_type', $patient->insurance_type) === 'private')>Swasta</option>
                                <option value="corporate" @selected(old('insurance_type', $patient->insurance_type) === 'corporate')>Korporat</option>
                                <option value="self_pay" @selected(old('insurance_type', $patient->insurance_type) === 'self_pay')>Bayar Sendiri</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Masa
                                Berlaku</label>
                            <input type="date" name="insurance_expiry"
                                value="{{ old('insurance_expiry', $patient->insurance_expiry ? \Carbon\Carbon::parse($patient->insurance_expiry)->format('Y-m-d') : '') }}"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex justify-end gap-3">
                <a href="{{ route('healthcare.patients.index') }}"
                    class="px-6 py-2.5 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Batal</a>
                <button type="submit" :disabled="loading"
                    class="px-6 py-2.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center">
                    <template x-if="loading">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </template>
                    <span x-text="loading ? 'Memproses...' : 'Update Pasien'"></span>
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
