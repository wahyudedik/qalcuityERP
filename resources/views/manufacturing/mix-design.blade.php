<x-app-layout>
    <x-slot name="header">🧮 Mix Design Beton - Kalkulator Mutu Beton SNI</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        @if ($selectedMix && $calculation)
            <form method="POST" action="{{ route('manufacturing.mix-design.export-pdf') }}" target="_blank" class="inline">
                @csrf
                <input type="hidden" name="mix_design_id" value="{{ $selectedMix->id }}">
                <input type="hidden" name="volume" value="{{ $calculation['adjusted']['volume_m3'] }}">
                <input type="hidden" name="waste_percent" value="{{ $calculation['adjusted']['waste_percent'] }}">
                <button type="submit"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    📄 Export PDF
                </button>
            </form>
        @endif
        <button onclick="document.getElementById('addMixModal').showModal()"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            + Tambah Custom Mix
        </button>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Success/Error Messages --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Calculator Form --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">📊 Kalkulator Kebutuhan Material</h3>

                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Mutu Beton</label>
                            <select name="mix_design_id" class="w-full border rounded px-3 py-2" required>
                                <option value="">-- Pilih Mutu --</option>
                                @foreach ($mixDesigns as $mix)
                                    <option value="{{ $mix->id }}"
                                        {{ request('mix_design_id') == $mix->id ? 'selected' : '' }}>
                                        {{ $mix->grade }} - {{ $mix->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Volume (m³)</label>
                            <input type="number" name="volume" step="0.1" min="0.1"
                                value="{{ request('volume', 1) }}" class="w-full border rounded px-3 py-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Waste Factor (%)</label>
                            <input type="number" name="waste_percent" step="0.5" min="0" max="50"
                                value="{{ request('waste_percent', 5) }}" class="w-full border rounded px-3 py-2">
                        </div>
                        <div class="flex items-end">
                            <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                                🔍 Hitung Kebutuhan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Calculation Results --}}
            @if ($calculation)
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Material Needs --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">📦 Kebutuhan Material</h3>

                            <div class="space-y-3">
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                    <span class="font-medium">🏭 Semen</span>
                                    <div class="text-right">
                                        <div class="font-bold">
                                            {{ number_format($calculation['adjusted']['cement_kg'], 1) }} kg</div>
                                        <div class="text-sm text-gray-500">{{ $calculation['adjusted']['cement_sak'] }}
                                            sak (@50kg)</div>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                    <span class="font-medium">💧 Air</span>
                                    <div class="text-right">
                                        <div class="font-bold">
                                            {{ number_format($calculation['adjusted']['water_liter'], 1) }} liter</div>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                    <span class="font-medium">🪨 Pasir (Agregat Halus)</span>
                                    <div class="text-right">
                                        <div class="font-bold">
                                            {{ number_format($calculation['adjusted']['fine_agg_kg'], 1) }} kg</div>
                                        <div class="text-sm text-gray-500">
                                            {{ $calculation['adjusted']['fine_agg_m3'] }} m³</div>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                    <span class="font-medium">🪨 Split (Agregat Kasar)</span>
                                    <div class="text-right">
                                        <div class="font-bold">
                                            {{ number_format($calculation['adjusted']['coarse_agg_kg'], 1) }} kg</div>
                                        <div class="text-sm text-gray-500">
                                            {{ $calculation['adjusted']['coarse_agg_m3'] }} m³</div>
                                    </div>
                                </div>
                                @if ($calculation['adjusted']['admixture_liter'] > 0)
                                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                        <span class="font-medium">⚗️ Admixture</span>
                                        <div class="text-right">
                                            <div class="font-bold">
                                                {{ number_format($calculation['adjusted']['admixture_liter'], 2) }}
                                                liter</div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-4 p-3 bg-blue-50 rounded text-sm">
                                <strong>Volume:</strong> {{ $calculation['adjusted']['volume_m3'] }} m³ |
                                <strong>Waste:</strong> {{ $calculation['adjusted']['waste_percent'] }}% |
                                <strong>Grade:</strong> {{ $calculation['adjusted']['grade'] }}
                            </div>
                        </div>
                    </div>

                    {{-- Cost Analysis --}}
                    @if ($costAnalysis)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4">💰 Analisis Biaya</h3>

                                <div class="text-center mb-4">
                                    <div class="text-3xl font-bold text-green-600">
                                        Rp {{ number_format($costAnalysis['total_cost'], 0, ',', '.') }}
                                    </div>
                                    <div class="text-sm text-gray-500">Total Biaya</div>
                                </div>

                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span>Biaya per m³</span>
                                        <span class="font-semibold">Rp
                                            {{ number_format($costAnalysis['cost_per_m3']['total'], 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Biaya per sak semen</span>
                                        <span class="font-semibold">Rp
                                            {{ number_format($costAnalysis['cost_per_sack_cement'], 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <h4 class="font-medium mb-2">Breakdown Biaya:</h4>
                                    <div class="space-y-2">
                                        @foreach ($costAnalysis['cost_per_m3'] as $item => $cost)
                                            @if ($item !== 'total' && $cost > 0)
                                                <div>
                                                    <div class="flex justify-between text-sm mb-1">
                                                        <span>{{ ucfirst(str_replace('_', ' ', $item)) }}</span>
                                                        <span>Rp {{ number_format($cost, 0, ',', '.') }}</span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                                        <div class="bg-blue-600 h-2 rounded-full"
                                                            style="width: {{ $costAnalysis['breakdown_percent'][$item] }}%">
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Material Availability --}}
                @if ($availability)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">
                                📋 Ketersediaan Material
                                @if ($availability['all_available'])
                                    <span class="ml-2 px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">✓
                                        Semua Tersedia</span>
                                @else
                                    <span class="ml-2 px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm">✗ Ada
                                        Kekurangan</span>
                                @endif
                            </h3>

                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left">Material</th>
                                            <th class="px-4 py-2 text-right">Dibutuhkan</th>
                                            <th class="px-4 py-2 text-right">Tersedia</th>
                                            <th class="px-4 py-2 text-right">Kekurangan</th>
                                            <th class="px-4 py-2 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($availability['availability'] as $material => $data)
                                            <tr class="border-t">
                                                <td class="px-4 py-3 font-medium">
                                                    {{ ucfirst(str_replace('_', ' ', $material)) }}</td>
                                                <td class="px-4 py-3 text-right">
                                                    {{ number_format($data['required'], 1) }} {{ $data['unit'] }}</td>
                                                <td class="px-4 py-3 text-right">
                                                    {{ number_format($data['available'], 1) }} {{ $data['unit'] }}
                                                </td>
                                                <td
                                                    class="px-4 py-3 text-right {{ $data['shortage'] > 0 ? 'text-red-600 font-bold' : '' }}">
                                                    {{ $data['shortage'] > 0 ? number_format($data['shortage'], 1) : '-' }}
                                                    {{ $data['unit'] }}
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    @if ($data['sufficient'])
                                                        <span
                                                            class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">✓
                                                            Cukup</span>
                                                    @else
                                                        <span
                                                            class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">✗
                                                            Kurang</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            {{-- Recommendation Tool --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">🎯 Rekomendasi Mix Design</h3>

                    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Mutu Required (K)</label>
                            <input type="number" name="required_strength" step="1" min="100"
                                placeholder="e.g. 300" class="w-full border rounded px-3 py-2"
                                value="{{ request('required_strength') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Volume (m³)</label>
                            <input type="number" name="rec_volume" step="0.1" min="0.1"
                                placeholder="e.g. 10" class="w-full border rounded px-3 py-2"
                                value="{{ request('rec_volume') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Max Budget (Rp/m³)</label>
                            <input type="number" name="max_budget" step="1000" min="0"
                                placeholder="Optional" class="w-full border rounded px-3 py-2"
                                value="{{ request('max_budget') }}">
                        </div>
                        <div class="flex items-end">
                            <button type="submit"
                                class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                                🎯 Cari Rekomendasi
                            </button>
                        </div>
                        <div class="flex items-end">
                            <button type="button" onclick="document.getElementById('compareModal').showModal()"
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">
                                ⚖️ Bandingkan
                            </button>
                        </div>
                    </form>

                    @if ($recommendation && $recommendation['status'] === 'success')
                        <div class="mt-4 p-4 bg-green-50 rounded border border-green-300">
                            <h4 class="font-bold text-green-800 mb-2">✓ Rekomendasi Terbaik</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <div class="text-gray-600">Grade</div>
                                    <div class="font-bold">{{ $recommendation['recommended_mix']->grade }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-600">Kuat Tekan</div>
                                    <div class="font-bold">{{ $recommendation['recommended_mix']->target_strength }} K
                                    </div>
                                </div>
                                <div>
                                    <div class="text-gray-600">Biaya/m³</div>
                                    <div class="font-bold">Rp
                                        {{ number_format($recommendation['cost_analysis']['cost_per_m3']['total'], 0, ',', '.') }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-gray-600">Total Biaya</div>
                                    <div class="font-bold">Rp
                                        {{ number_format($recommendation['cost_analysis']['total_cost'], 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Mix Design List --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">📚 Daftar Mix Design</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($mixDesigns as $mix)
                            <div
                                class="border rounded-lg p-4 hover:shadow-md transition {{ $mix->is_standard ? 'bg-blue-50' : 'bg-white' }}">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-lg">{{ $mix->grade }}</h4>
                                    @if ($mix->is_standard)
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Standar
                                            SNI</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600 mb-3">{{ $mix->name }}</p>

                                <div class="space-y-1 text-sm">
                                    <div class="flex justify-between">
                                        <span>Kuat Tekan:</span>
                                        <span class="font-semibold">{{ $mix->target_strength }}
                                            {{ $mix->strength_unit }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>W/C Ratio:</span>
                                        <span class="font-semibold">{{ $mix->water_cement_ratio }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Semen:</span>
                                        <span class="font-semibold">{{ $mix->cement_kg }} kg/m³</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Slump:</span>
                                        <span class="font-semibold">{{ $mix->slump_min }} - {{ $mix->slump_max }}
                                            cm</span>
                                    </div>
                                </div>

                                @if (!$mix->is_standard)
                                    <div class="mt-3 flex gap-2">
                                        <button onclick="editMixDesign({{ $mix->id }})"
                                            class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm">
                                            Edit
                                        </button>
                                        <button onclick="deleteMixDesign({{ $mix->id }})"
                                            class="flex-1 bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                                            Hapus
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Mix Design Modal --}}
    <dialog id="addMixModal" class="modal">
        <div class="modal-box max-w-2xl">
            <h3 class="font-bold text-lg mb-4">Tambah Custom Mix Design</h3>
            <form method="POST" action="{{ route('manufacturing.mix-design.store') }}">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Grade *</label>
                        <input type="text" name="grade" required class="w-full border rounded px-3 py-2"
                            placeholder="e.g. K-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama *</label>
                        <input type="text" name="name" required class="w-full border rounded px-3 py-2"
                            placeholder="e.g. Beton K-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Kuat Tekan (K) *</label>
                        <input type="number" name="target_strength" step="1" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Satuan Kuat *</label>
                        <select name="strength_unit" required class="w-full border rounded px-3 py-2">
                            <option value="K">K</option>
                            <option value="MPa">MPa</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Semen (kg/m³) *</label>
                        <input type="number" name="cement_kg" step="0.1" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Air (liter/m³) *</label>
                        <input type="number" name="water_liter" step="0.1" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">W/C Ratio *</label>
                        <input type="number" name="water_cement_ratio" step="0.01" min="0"
                            max="1" required class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Tipe Semen *</label>
                        <select name="cement_type" required class="w-full border rounded px-3 py-2">
                            <option value="PCC">PCC</option>
                            <option value="OPC">OPC</option>
                            <option value="Type I">Type I</option>
                            <option value="Type II">Type II</option>
                            <option value="Type III">Type III</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Pasir (kg/m³) *</label>
                        <input type="number" name="fine_agg_kg" step="0.1" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Split (kg/m³) *</label>
                        <input type="number" name="coarse_agg_kg" step="0.1" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Admixture (liter/m³)</label>
                        <input type="number" name="admixture_liter" step="0.001" value="0"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Ukuran Agregat *</label>
                        <select name="agg_max_size" required class="w-full border rounded px-3 py-2">
                            <option value="10mm">10mm</option>
                            <option value="20mm">20mm</option>
                            <option value="40mm">40mm</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Slump Min (cm)</label>
                        <input type="number" name="slump_min" step="0.1" value="8"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Slump Max (cm)</label>
                        <input type="number" name="slump_max" step="0.1" value="12"
                            class="w-full border rounded px-3 py-2">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium mb-1">Catatan</label>
                    <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2"></textarea>
                </div>
                <div class="modal-action">
                    <button type="button" onclick="document.getElementById('addMixModal').close()"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm transition">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">Simpan</button>
                </div>
            </form>
        </div>
    </dialog>

    <script>
        function editMixDesign(id) {
            // Fetch mix design data
            fetch(`/api/mix-design/${id}`)
                .then(response => response.json())
                .then(data => {
                    // Populate edit form
                    document.getElementById('edit_mix_id').value = data.id;
                    document.getElementById('editMixForm').action = `/manufacturing/mix-design/${id}`;
                    document.getElementById('edit_grade').value = data.grade;
                    document.getElementById('edit_name').value = data.name;
                    document.getElementById('edit_target_strength').value = data.target_strength;
                    document.getElementById('edit_strength_unit').value = data.strength_unit;
                    document.getElementById('edit_cement_kg').value = data.cement_kg;
                    document.getElementById('edit_water_liter').value = data.water_liter;
                    document.getElementById('edit_water_cement_ratio').value = data.water_cement_ratio;
                    document.getElementById('edit_cement_type').value = data.cement_type;
                    document.getElementById('edit_fine_agg_kg').value = data.fine_agg_kg;
                    document.getElementById('edit_coarse_agg_kg').value = data.coarse_agg_kg;
                    document.getElementById('edit_admixture_liter').value = data.admixture_liter || 0;
                    document.getElementById('edit_agg_max_size').value = data.agg_max_size;
                    document.getElementById('edit_slump_min').value = data.slump_min || 8;
                    document.getElementById('edit_slump_max').value = data.slump_max || 12;
                    document.getElementById('edit_notes').value = data.notes || '';

                    // Set version history link
                    document.getElementById('viewVersionsLink').href = `/manufacturing/mix-design/${data.id}/versions`;

                    // Show modal
                    document.getElementById('editMixModal').showModal();
                })
                .catch(error => {
                    alert('Error loading mix design data');
                    console.error(error);
                });
        }

        function deleteMixDesign(id) {
            if (confirm('Yakin ingin menghapus mix design ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/manufacturing/mix-design/${id}`;
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>

    {{-- Edit Mix Design Modal --}}
    <dialog id="editMixModal" class="modal">
        <div class="modal-box max-w-2xl">
            <h3 class="font-bold text-lg mb-4">Edit Custom Mix Design</h3>
            <form method="POST" id="editMixForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_mix_id" name="mix_design_id">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Grade *</label>
                        <input type="text" id="edit_grade" name="grade" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama *</label>
                        <input type="text" id="edit_name" name="name" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Kuat Tekan (K) *</label>
                        <input type="number" id="edit_target_strength" name="target_strength" step="1"
                            required class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Satuan Kuat *</label>
                        <select id="edit_strength_unit" name="strength_unit" required
                            class="w-full border rounded px-3 py-2">
                            <option value="K">K</option>
                            <option value="MPa">MPa</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Semen (kg/m³) *</label>
                        <input type="number" id="edit_cement_kg" name="cement_kg" step="0.1" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Air (liter/m³) *</label>
                        <input type="number" id="edit_water_liter" name="water_liter" step="0.1" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">W/C Ratio *</label>
                        <input type="number" id="edit_water_cement_ratio" name="water_cement_ratio" step="0.01"
                            min="0" max="1" required class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Tipe Semen *</label>
                        <select id="edit_cement_type" name="cement_type" required
                            class="w-full border rounded px-3 py-2">
                            <option value="PCC">PCC</option>
                            <option value="OPC">OPC</option>
                            <option value="Type I">Type I</option>
                            <option value="Type II">Type II</option>
                            <option value="Type III">Type III</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Pasir (kg/m³) *</label>
                        <input type="number" id="edit_fine_agg_kg" name="fine_agg_kg" step="0.1" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Split (kg/m³) *</label>
                        <input type="number" id="edit_coarse_agg_kg" name="coarse_agg_kg" step="0.1" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Admixture (liter/m³)</label>
                        <input type="number" id="edit_admixture_liter" name="admixture_liter" step="0.001"
                            value="0" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Ukuran Agregat *</label>
                        <select id="edit_agg_max_size" name="agg_max_size" required
                            class="w-full border rounded px-3 py-2">
                            <option value="10mm">10mm</option>
                            <option value="20mm">20mm</option>
                            <option value="40mm">40mm</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Slump Min (cm)</label>
                        <input type="number" id="edit_slump_min" name="slump_min" step="0.1" value="8"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Slump Max (cm)</label>
                        <input type="number" id="edit_slump_max" name="slump_max" step="0.1" value="12"
                            class="w-full border rounded px-3 py-2">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium mb-1">Catatan</label>
                    <textarea id="edit_notes" name="notes" rows="2" class="w-full border rounded px-3 py-2"></textarea>
                </div>
                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <label class="block text-sm font-medium mb-1 text-yellow-800">
                        ⚠️ Change Reason (Required for Version Tracking)
                    </label>
                    <input type="text" id="edit_change_reason" name="change_reason" required
                        placeholder="e.g., Adjusted cement ratio for better strength"
                        class="w-full border border-yellow-300 rounded px-3 py-2">
                    <p class="text-xs text-yellow-600 mt-1">
                        A new version will be created to track this change
                    </p>
                </div>
                <div class="modal-action">
                    <button type="button" onclick="document.getElementById('editMixModal').close()"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm transition">Batal</button>
                    <a href="#" id="viewVersionsLink" target="_blank"
                        class="px-4 py-2 border border-sky-500 text-sky-600 hover:bg-sky-50 rounded-xl text-sm transition">📋
                        View
                        Versions</a>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">Update
                        & Create Version</button>
                </div>
            </form>
        </div>
    </dialog>
</x-app-layout>
