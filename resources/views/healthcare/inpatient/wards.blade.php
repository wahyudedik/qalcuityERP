<x-app-layout>
    <x-slot name="header">Manajemen Ruang Rawat</x-slot>

    @php $tid = auth()->user()->tenant_id; @endphp

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @php
            $totalWards = \App\Models\Ward::where('tenant_id', $tid)->count();
            $totalBeds = \App\Models\Bed::where('tenant_id', $tid)->count();
            $occupiedBeds = \App\Models\Bed::where('tenant_id', $tid)->where('status', 'occupied')->count();
            $availableBeds = \App\Models\Bed::where('tenant_id', $tid)->where('status', 'available')->count();
            $occupancyRate = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 1) : 0;
        @endphp
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Ruang</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalWards }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Tempat Tidur</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalBeds }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Tersedia</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $availableBeds }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Okupansi</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $occupancyRate }}%</p>
        </div>
    </div>

    {{-- Legend --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap items-center gap-4 text-sm">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-green-500"></div>
                <span class="text-gray-700">Tersedia</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-red-500"></div>
                <span class="text-gray-700">Terisi</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-amber-500"></div>
                <span class="text-gray-700">Maintenance</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-gray-400"></div>
                <span class="text-gray-700">Nonaktif</span>
            </div>
        </div>
    </div>

    {{-- Wards Grid --}}
    <div class="space-y-6">
        @forelse($wards ?? [] as $ward)
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $ward->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $ward->ward_type ?? '-' }} |
                            {{ $ward->floor ?? '-' }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        @php
                            $wardBeds = $ward->beds ?? collect();
                            $wardOccupied = $wardBeds->where('status', 'occupied')->count();
                            $wardTotal = $wardBeds->count();
                            $wardOccupancy = $wardTotal > 0 ? round(($wardOccupied / $wardTotal) * 100, 1) : 0;
                        @endphp
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Okupansi</p>
                            <p class="text-lg font-bold text-blue-600">
                                {{ $wardOccupied }}/{{ $wardTotal }} ({{ $wardOccupancy }}%)</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-3">
                        @forelse($wardBeds as $bed)
                            <button onclick="showBedDetails({{ $bed->id }})"
                                class="bed-item relative aspect-square rounded-xl border-2 transition-all hover:scale-105 cursor-pointer
                                    @if ($bed->status === 'available') bg-green-100 border-green-300 hover:bg-green-200
                                    @elseif($bed->status === 'occupied')
                                        bg-red-100 border-red-300 hover:bg-red-200
                                    @elseif($bed->status === 'maintenance')
                                        bg-amber-100 border-amber-300 hover:bg-amber-200
                                    @else
                                        bg-gray-100 border-gray-300 hover:bg-gray-200 @endif">
                                <div class="flex flex-col items-center justify-center h-full">
                                    <svg class="w-5 h-5 mb-1
                                        @if ($bed->status === 'available') text-green-600
                                        @elseif($bed->status === 'occupied') text-red-600
                                        @elseif($bed->status === 'maintenance') text-amber-600
                                        @else text-gray-500 @endif"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                        </path>
                                    </svg>
                                    <span
                                        class="text-xs font-semibold
                                        @if ($bed->status === 'available') text-green-700
                                        @elseif($bed->status === 'occupied') text-red-700
                                        @elseif($bed->status === 'maintenance') text-amber-700
                                        @else text-gray-600 @endif">
                                        {{ $bed->bed_number }}
                                    </span>
                                </div>
                                @if ($bed->status === 'occupied' && $bed->admission)
                                    <div
                                        class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-white">
                                    </div>
                                @endif
                            </button>
                        @empty
                            <div class="col-span-full text-center py-8 text-gray-500">
                                Belum ada tempat tidur di ruang ini
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                    </path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum Ada Ruang Rawat</h3>
                <p class="text-sm text-gray-500 mb-4">Silakan tambahkan ruang rawat terlebih dahulu
                </p>
                <button onclick="document.getElementById('modal-add-ward').classList.remove('hidden')"
                    class="px-6 py-2.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium">
                    + Tambah Ruang
                </button>
            </div>
        @endforelse
    </div>

    {{-- Add Ward Modal --}}
    <div id="modal-add-ward"
        class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-lg">
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Tambah Ruang Rawat Baru</h3>
                <button onclick="document.getElementById('modal-add-ward').classList.add('hidden')"
                    class="p-2 hover:bg-gray-100 rounded-xl">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <form action="{{ route('healthcare.inpatient.wards.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Ruang
                            *</label>
                        <input type="text" name="name" required placeholder="Contoh: Ruang Melati"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Ruang
                            *</label>
                        <select name="ward_type" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Tipe</option>
                            <option value="VIP">VIP</option>
                            <option value="Kelas 1">Kelas 1</option>
                            <option value="Kelas 2">Kelas 2</option>
                            <option value="Kelas 3">Kelas 3</option>
                            <option value="ICU">ICU</option>
                            <option value="NICU">NICU</option>
                            <option value="HCU">HCU</option>
                            <option value="Isolasi">Isolasi</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lantai</label>
                        <input type="text" name="floor" placeholder="Contoh: Lantai 2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kapasitas
                            Tempat Tidur</label>
                        <input type="number" name="capacity" min="1" placeholder="Jumlah tempat tidur"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea name="description" rows="2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('modal-add-ward').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function showBedDetails(bedId) {
                // Implement bed details modal or navigation
                Dialog.alert('Detail tempat tidur ID: ' + bedId);
            }
        </script>
    @endpush
</x-app-layout>
