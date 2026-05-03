<x-app-layout>
    <x-slot name="header">Survei Kepuasan Pasien</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Kepuasan Pasien', 'url' => route('healthcare.patient-satisfaction.index')],
        ['label' => 'Buat Survei'],
    ]" />

    <div class="py-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('healthcare.patient-satisfaction.store') }}" method="POST"
                class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
                @csrf
                <div>
                    <label for="patient_visit_id"
                        class="block text-sm font-medium text-gray-700 mb-1">Kunjungan Pasien
                        *</label>
                    <select name="patient_visit_id" id="patient_visit_id" required
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('patient_visit_id') border-red-500 @enderror">
                        <option value="">Pilih kunjungan pasien</option>
                        @foreach ($visits as $visit)
                            <option value="{{ $visit->id }}">{{ $visit->patient->name ?? 'Unknown' }} -
                                {{ $visit->visit_date }}</option>
                        @endforeach
                    </select>
                    @error('patient_visit_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="overall_rating"
                        class="block text-sm font-medium text-gray-700 mb-1">Penilaian Keseluruhan
                        *</label>
                    <select name="overall_rating" id="overall_rating" required
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('overall_rating') border-red-500 @enderror">
                        <option value="">Pilih penilaian</option>
                        <option value="5">5 - Sangat Baik</option>
                        <option value="4">4 - Baik</option>
                        <option value="3">3 - Cukup</option>
                        <option value="2">2 - Buruk</option>
                        <option value="1">1 - Sangat Buruk</option>
                    </select>
                    @error('overall_rating')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="doctor_rating"
                            class="block text-sm font-medium text-gray-700 mb-1">Penilaian
                            Dokter</label>
                        <select name="doctor_rating" id="doctor_rating"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih penilaian</option>
                            <option value="5">5 - Sangat Baik</option>
                            <option value="4">4 - Baik</option>
                            <option value="3">3 - Cukup</option>
                            <option value="2">2 - Buruk</option>
                            <option value="1">1 - Sangat Buruk</option>
                        </select>
                    </div>
                    <div>
                        <label for="nurse_rating"
                            class="block text-sm font-medium text-gray-700 mb-1">Penilaian
                            Perawat</label>
                        <select name="nurse_rating" id="nurse_rating"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih penilaian</option>
                            <option value="5">5 - Sangat Baik</option>
                            <option value="4">4 - Baik</option>
                            <option value="3">3 - Cukup</option>
                            <option value="2">2 - Buruk</option>
                            <option value="1">1 - Sangat Buruk</option>
                        </select>
                    </div>
                    <div>
                        <label for="facility_rating"
                            class="block text-sm font-medium text-gray-700 mb-1">Penilaian
                            Fasilitas</label>
                        <select name="facility_rating" id="facility_rating"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih penilaian</option>
                            <option value="5">5 - Sangat Baik</option>
                            <option value="4">4 - Baik</option>
                            <option value="3">3 - Cukup</option>
                            <option value="2">2 - Buruk</option>
                            <option value="1">1 - Sangat Buruk</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label for="cleanliness_rating"
                        class="block text-sm font-medium text-gray-700 mb-1">Penilaian
                        Kebersihan</label>
                    <select name="cleanliness_rating" id="cleanliness_rating"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih penilaian</option>
                        <option value="5">5 - Sangat Baik</option>
                        <option value="4">4 - Baik</option>
                        <option value="3">3 - Cukup</option>
                        <option value="2">2 - Buruk</option>
                        <option value="1">1 - Sangat Buruk</option>
                    </select>
                </div>
                <div>
                    <label for="comments"
                        class="block text-sm font-medium text-gray-700 mb-1">Komentar</label>
                    <textarea name="comments" id="comments" rows="4"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="would_recommend" id="would_recommend" value="1"
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="would_recommend" class="ml-2 block text-sm text-gray-700">Akan
                        merekomendasikan fasilitas ini</label>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('healthcare.patient-satisfaction.index') }}"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Simpan Survei
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
