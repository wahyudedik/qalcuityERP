@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Catatan Kesehatan & Pengobatan</h1>
            <p class="mt-2 text-gray-600">Pantau catatan kesehatan dan pengobatan ternak</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">Total Catatan</div>
                <div class="mt-2 text-3xl font-bold text-gray-900">
                    {{ number_format($stats['total_treatments']) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">Sedang Ditangani</div>
                <div class="mt-2 text-3xl font-bold text-yellow-600">
                    {{ number_format($stats['active_treatments']) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">Selesai</div>
                <div class="mt-2 text-3xl font-bold text-green-600">
                    {{ number_format($stats['completed_treatments']) }}</div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Catatan Kesehatan</h3>
                <button type="button" onclick="document.getElementById('addTreatmentModal').classList.remove('hidden')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Catatan
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Kelompok Ternak</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Tipe</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Kondisi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Tingkat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($treatments as $treatment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $treatment->date?->format('d M Y') ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $treatment->herd?->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $treatment->typeLabel() }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ Str::limit($treatment->condition, 50) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($treatment->severity)
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if ($treatment->severity === 'low') bg-gray-100 text-gray-800
                                            @elseif($treatment->severity === 'medium') bg-amber-100 text-amber-800
                                            @elseif($treatment->severity === 'high') bg-orange-100 text-orange-800
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ ucfirst($treatment->severity) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if ($treatment->status === 'active') bg-yellow-100 text-yellow-800
                                        @elseif($treatment->status === 'resolved') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $treatment->status === 'active' ? 'Aktif' : ($treatment->status === 'resolved' ? 'Selesai' : ucfirst($treatment->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    Belum ada catatan kesehatan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $treatments->links() }}
            </div>
        </div>
    </div>

    <!-- Add Treatment Modal -->
    <div id="addTreatmentModal"
        class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div
            class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Tambah Catatan Kesehatan</h3>
                <button type="button" onclick="document.getElementById('addTreatmentModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <form action="{{ route('livestock-enhancement.health.treatments.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kelompok
                            Ternak</label>
                        <select name="livestock_herd_id" required
                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih kelompok ternak</option>
                            @foreach ($herds as $herd)
                                <option value="{{ $herd->id }}">{{ $herd->code }} - {{ $herd->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                        <select name="type" required
                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                            <option value="illness">🤒 Penyakit</option>
                            <option value="treatment">💊 Pengobatan</option>
                            <option value="observation">👁️ Observasi</option>
                            <option value="quarantine">🔒 Karantina</option>
                            <option value="recovery">✅ Sembuh</option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1">Kondisi/Diagnosis</label>
                        <input type="text" name="condition" required
                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Deskripsi kondisi atau diagnosis">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah
                                Terdampak</label>
                            <input type="number" name="affected_count" min="0"
                                class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tingkat
                                Keparahan</label>
                            <select name="severity"
                                class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Pilih tingkat</option>
                                <option value="low">Rendah</option>
                                <option value="medium">Sedang</option>
                                <option value="high">Tinggi</option>
                                <option value="critical">Kritis</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1">Obat/Pengobatan</label>
                        <input type="text" name="medication"
                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nama obat atau tindakan pengobatan">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <textarea name="notes" rows="2"
                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Catatan tambahan"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('addTreatmentModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
