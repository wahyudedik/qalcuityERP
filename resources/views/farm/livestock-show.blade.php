<x-app-layout>
    <x-slot name="header">{{ $livestockHerd->code }} — {{ $livestockHerd->name }}</x-slot>

    @if (session('success'))
        <div
            class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
            {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div
            class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
            {{ session('error') }}</div>
    @endif

    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('farm.livestock') }}" class="text-sm text-blue-500 hover:text-blue-600">← Daftar Ternak</a>
        @if ($livestockHerd->status === 'active')
            <button onclick="document.getElementById('movementModal').classList.remove('hidden')"
                class="px-3 py-2 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">Catat
                Perubahan</button>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- KPI --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <div class="flex items-center gap-3 mb-4">
                    <span
                        class="text-3xl">{{ explode(' ', \App\Models\LivestockHerd::ANIMAL_TYPES[$livestockHerd->animal_type] ?? '🐾')[0] }}</span>
                    <div>
                        <p class="font-bold text-gray-900 text-lg">{{ $livestockHerd->name }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $livestockHerd->breed ?? $livestockHerd->animal_type }} ·
                            {{ $livestockHerd->plot?->code ?? 'Tanpa kandang' }}</p>
                    </div>
                    <span
                        class="ml-auto text-4xl font-black text-emerald-600">{{ number_format($livestockHerd->current_count) }}</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Awal</p>
                        <p class="text-lg font-bold text-gray-900">
                            {{ number_format($livestockHerd->initial_count) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Umur</p>
                        <p class="text-lg font-bold text-gray-900">
                            {{ $livestockHerd->ageDays() ?? '-' }} hari</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Mortalitas</p>
                        <p class="text-lg font-bold text-red-500">{{ abs($livestockHerd->mortalityCount()) }}
                            ({{ $livestockHerd->mortalityRate() }}%)</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Terjual</p>
                        <p class="text-lg font-bold text-blue-600">
                            {{ $livestockHerd->soldCount() + $livestockHerd->harvestedCount() }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Revenue</p>
                        <p class="text-lg font-bold text-emerald-600">Rp
                            {{ number_format($livestockHerd->totalRevenue(), 0, ',', '.') }}</p>
                    </div>
                </div>
                @if ($livestockHerd->isHarvestOverdue())
                    <div
                        class="mt-3 px-3 py-2 rounded-lg bg-red-50 border border-red-200 text-xs text-red-600">
                        ⚠️ Target panen sudah lewat ({{ $livestockHerd->target_harvest_date->format('d M Y') }})
                    </div>
                @elseif($livestockHerd->daysUntilHarvest())
                    <p class="mt-3 text-xs text-gray-400">🎯 Target panen:
                        {{ $livestockHerd->target_harvest_date->format('d M Y') }}
                        ({{ $livestockHerd->daysUntilHarvest() }} hari lagi)</p>
                @endif
            </div>

            {{-- FCR & Feed Metrics --}}
            @php
                $fcr = $livestockHerd->fcr();
                $totalFeed = $livestockHerd->totalFeedKg();
                $totalFeedCost = $livestockHerd->totalFeedCost();
                $latestWeight = $livestockHerd->latestBodyWeight();
                $weightGain = $livestockHerd->weightGain();
                $avgDailyFeed = $livestockHerd->avgDailyFeed();
                $feedCostPerKg = $livestockHerd->feedCostPerKgGain();
            @endphp
            @if ($totalFeed > 0)
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <h3 class="font-semibold text-gray-900 mb-3">📊 Feed Conversion Ratio (FCR)</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div
                            class="p-3 rounded-xl {{ $fcr && $fcr <= 1.8 ? 'bg-green-50' : ($fcr && $fcr <= 2.2 ? 'bg-amber-50' : 'bg-gray-50') }}">
                            <p class="text-xs text-gray-500">FCR</p>
                            <p
                                class="text-2xl font-black {{ $fcr && $fcr <= 1.8 ? 'text-green-600' : ($fcr && $fcr <= 2.2 ? 'text-amber-600' : 'text-gray-900') }}">
                                {{ $fcr ?? '-' }}</p>
                            <p class="text-[10px] text-gray-400">
                                {{ $fcr ? ($fcr <= 1.6 ? 'Sangat baik' : ($fcr <= 1.8 ? 'Baik' : ($fcr <= 2.2 ? 'Cukup' : 'Perlu perbaikan'))) : 'Belum cukup data' }}
                            </p>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50">
                            <p class="text-xs text-gray-500">Total Pakan</p>
                            <p class="text-lg font-bold text-gray-900">
                                {{ number_format($totalFeed, 0) }} kg</p>
                            <p class="text-[10px] text-gray-400">Biaya: Rp
                                {{ number_format($totalFeedCost, 0, ',', '.') }}</p>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50">
                            <p class="text-xs text-gray-500">Berat Rata-rata</p>
                            <p class="text-lg font-bold text-gray-900">
                                {{ $latestWeight ? number_format($latestWeight, 2) . ' kg' : '-' }}</p>
                            @if ($weightGain)
                                <p class="text-[10px] text-emerald-600">+{{ number_format($weightGain, 3) }} kg gain
                                </p>
                            @endif
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50">
                            <p class="text-xs text-gray-500">Biaya Pakan/kg Gain</p>
                            <p class="text-lg font-bold text-gray-900">
                                {{ $feedCostPerKg ? 'Rp ' . number_format($feedCostPerKg, 0, ',', '.') : '-' }}</p>
                            @if ($avgDailyFeed)
                                <p class="text-[10px] text-gray-400">{{ $avgDailyFeed }} kg/hari</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Feed Log --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">🌾 Catatan Pakan</h3>
                    @if ($livestockHerd->status === 'active')
                        <button onclick="document.getElementById('feedModal').classList.remove('hidden')"
                            class="text-xs px-3 py-1.5 bg-amber-600 text-white rounded-lg hover:bg-amber-700">+ Catat
                            Pakan</button>
                    @endif
                </div>
                @if ($livestockHerd->feedLogs->isEmpty())
                    <div class="p-6 text-center text-sm text-gray-400">Belum ada catatan pakan. Catat pemberian pakan
                        harian untuk menghitung FCR.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                                <tr>
                                    <th class="px-4 py-2 text-left">Tanggal</th>
                                    <th class="px-4 py-2 text-left">Jenis</th>
                                    <th class="px-4 py-2 text-right">Jumlah</th>
                                    <th class="px-4 py-2 text-right">g/ekor</th>
                                    <th class="px-4 py-2 text-right">Berat</th>
                                    <th class="px-4 py-2 text-right">Biaya</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($livestockHerd->feedLogs->take(14) as $fl)
                                    <tr>
                                        <td class="px-4 py-2 text-xs text-gray-500">{{ $fl->date->format('d M') }}</td>
                                        <td class="px-4 py-2 text-gray-700">{{ $fl->feed_type }}
                                        </td>
                                        <td class="px-4 py-2 text-right font-mono">
                                            {{ number_format($fl->quantity_kg, 1) }} kg</td>
                                        <td class="px-4 py-2 text-right font-mono text-xs text-gray-400">
                                            {{ $fl->feedPerHead() }}g</td>
                                        <td
                                            class="px-4 py-2 text-right font-mono text-xs {{ $fl->avg_body_weight_kg > 0 ? 'text-emerald-600' : 'text-gray-300' }}">
                                            {{ $fl->avg_body_weight_kg > 0 ? number_format($fl->avg_body_weight_kg, 2) . ' kg' : '-' }}
                                        </td>
                                        <td class="px-4 py-2 text-right text-xs text-gray-500">
                                            {{ $fl->cost > 0 ? 'Rp ' . number_format($fl->cost, 0, ',', '.') : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Movement History --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Riwayat Populasi
                        ({{ $livestockHerd->movements->count() }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-2 text-left">Tanggal</th>
                                <th class="px-4 py-2 text-left">Jenis</th>
                                <th class="px-4 py-2 text-right">Jumlah</th>
                                <th class="px-4 py-2 text-right">Populasi</th>
                                <th class="px-4 py-2 text-left">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($livestockHerd->movements as $mv)
                                <tr>
                                    <td class="px-4 py-2 text-xs text-gray-500">{{ $mv->date->format('d M Y') }}</td>
                                    <td class="px-4 py-2 text-xs">{{ $mv->typeLabel() }}</td>
                                    <td
                                        class="px-4 py-2 text-right font-mono font-medium {{ $mv->quantity > 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                        {{ $mv->quantity > 0 ? '+' : '' }}{{ $mv->quantity }}</td>
                                    <td class="px-4 py-2 text-right font-mono text-gray-700">
                                        {{ number_format($mv->count_after) }}</td>
                                    <td class="px-4 py-2 text-xs text-gray-400">
                                        {{ $mv->reason ?? ($mv->destination ?? '') }}
                                        @if ($mv->price_total > 0)
                                            · Rp {{ number_format($mv->price_total, 0, ',', '.') }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Vaccination Schedule --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">💉 Jadwal Vaksinasi</h3>
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('farm.livestock.vaccinations.generate', $livestockHerd) }}">
                        @csrf
                        <button type="submit"
                            class="text-xs px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Auto-Generate</button>
                    </form>
                </div>
            </div>
            @if ($livestockHerd->vaccinations->isEmpty())
                <div class="p-6 text-center text-sm text-gray-400">Belum ada jadwal vaksinasi. Klik "Auto-Generate"
                    untuk jadwal otomatis.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-2 text-left">Vaksin</th>
                                <th class="px-4 py-2 text-center">Hari ke-</th>
                                <th class="px-4 py-2 text-center">Jadwal</th>
                                <th class="px-4 py-2 text-center">Status</th>
                                <th class="px-4 py-2 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($livestockHerd->vaccinations as $vax)
                                @php $overdue = $vax->isOverdue(); @endphp
                                <tr class="{{ $overdue ? 'bg-red-50/50' : '' }}">
                                    <td class="px-4 py-2 text-gray-900 font-medium">
                                        {{ $vax->vaccine_name }}</td>
                                    <td class="px-4 py-2 text-center text-xs text-gray-500">{{ $vax->dose_age_days }}
                                    </td>
                                    <td
                                        class="px-4 py-2 text-center text-xs {{ $overdue ? 'text-red-500 font-medium' : 'text-gray-500' }}">
                                        {{ $vax->scheduled_date->format('d M Y') }}{{ $overdue ? ' ⚠️' : '' }}</td>
                                    <td class="px-4 py-2 text-center">
                                        @if ($vax->status === 'completed')
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700">✅
                                                Selesai</span>
                                        @elseif($overdue)
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700">Terlambat</span>
                                        @else
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Dijadwalkan</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        @if ($vax->status === 'scheduled')
                                            <form method="POST"
                                                action="{{ route('farm.livestock.vaccinations.record', $vax) }}"
                                                class="inline">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="administered_date"
                                                    value="{{ date('Y-m-d') }}">
                                                <input type="hidden" name="vaccinated_count"
                                                    value="{{ $livestockHerd->current_count }}">
                                                <button type="submit"
                                                    class="text-xs text-blue-500 hover:text-blue-600">Catat
                                                    Selesai</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Health Records --}}
        <div
            class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">🏥 Catatan Kesehatan</h3>
                @if ($livestockHerd->status === 'active')
                    <button onclick="document.getElementById('healthModal').classList.remove('hidden')"
                        class="text-xs px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700">+ Catat</button>
                @endif
            </div>
            @if ($livestockHerd->healthRecords->isEmpty())
                <div class="p-6 text-center text-sm text-gray-400">Belum ada catatan kesehatan.</div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach ($livestockHerd->healthRecords->take(20) as $hr)
                        @php $sc = \App\Models\LivestockHealthRecord::SEVERITY_COLORS[$hr->severity] ?? 'gray'; @endphp
                        <div class="px-5 py-3">
                            <div class="flex items-center gap-2 mb-1">
                                <span
                                    class="text-xs px-2 py-0.5 rounded-full bg-{{ $sc  }}-100 text-{{ $sc }}-700 $sc }}-500/20 $sc }}-400">{{ ucfirst($hr->severity) }}</span>
                                <span
                                    class="text-sm font-medium text-gray-900">{{ $hr->condition }}</span>
                                <span class="text-xs text-gray-400 ml-auto">{{ $hr->date->format('d M Y') }}</span>
                            </div>
                            <div class="flex flex-wrap gap-x-3 text-xs text-gray-500">
                                <span>{{ $hr->typeLabel() }}</span>
                                @if ($hr->affected_count > 0)
                                    <span>Terdampak: {{ $hr->affected_count }}</span>
                                @endif
                                @if ($hr->death_count > 0)
                                    <span class="text-red-500">Mati: {{ $hr->death_count }}</span>
                                @endif
                                @if ($hr->medication)
                                    <span>Obat: {{ $hr->medication }}</span>
                                @endif
                                @if ($hr->medication_cost > 0)
                                    <span>Biaya: Rp {{ number_format($hr->medication_cost, 0, ',', '.') }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Right: Info --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5 h-fit">
        <h3 class="font-semibold text-gray-900 mb-3">Info Ternak</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">Kode</span><span
                    class="font-mono">{{ $livestockHerd->code }}</span></div>
            <div class="flex justify-between"><span
                    class="text-gray-500">Jenis</span><span>{{ $livestockHerd->animalLabel() }}</span></div>
            @if ($livestockHerd->breed)
                <div class="flex justify-between"><span
                        class="text-gray-500">Ras</span><span>{{ $livestockHerd->breed }}</span></div>
            @endif
            <div class="flex justify-between"><span
                    class="text-gray-500">Masuk</span><span>{{ $livestockHerd->entry_date?->format('d M Y') }}</span>
            </div>
            @if ($livestockHerd->entry_weight_kg > 0)
                <div class="flex justify-between"><span class="text-gray-500">Berat
                        Masuk</span><span>{{ $livestockHerd->entry_weight_kg }} kg/ekor</span></div>
            @endif
            @if ($livestockHerd->purchase_price > 0)
                <div class="flex justify-between"><span class="text-gray-500">Harga Beli</span><span>Rp
                        {{ number_format($livestockHerd->purchase_price, 0, ',', '.') }}</span></div>
            @endif
            @if ($livestockHerd->plot)
                <div class="flex justify-between"><span
                        class="text-gray-500">Kandang</span><span>{{ $livestockHerd->plot?->code }}</span></div>
            @endif
            <div class="flex justify-between"><span class="text-gray-500">Status</span><span
                    class="font-medium">{{ ucfirst($livestockHerd->status) }}</span></div>
        </div>
    </div>
    </div>

    {{-- Feed Log Modal --}}
    <div id="feedModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white rounded-2xl border border-gray-200 w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900">🌾 Catat Pemberian Pakan</h3>
                <button onclick="document.getElementById('feedModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('farm.livestock.feed.store', $livestockHerd) }}"
                class="space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900'; @endphp
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jenis Pakan
                            *</label>
                        <input type="text" name="feed_type" required placeholder="Starter, Grower, Finisher"
                            class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal
                            *</label>
                        <input type="date" name="date" required value="{{ date('Y-m-d') }}"
                            class="{{ $cls }}">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah (kg)
                            *</label>
                        <input type="number" name="quantity_kg" required step="0.001" min="0.001"
                            class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Biaya
                            (Rp)</label>
                        <input type="number" name="cost" step="1" min="0"
                            class="{{ $cls }}">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Berat Rata-rata
                        Saat Ini (kg/ekor) — untuk hitung FCR</label>
                    <input type="number" name="avg_body_weight_kg" step="0.001"
                        placeholder="Timbang sampling, misal 1.250" class="{{ $cls }}">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('feedModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 text-gray-700">Batal</button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 rounded-lg text-sm bg-amber-600 hover:bg-amber-700 text-white font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Health Record Modal --}}
    <div id="healthModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white rounded-2xl border border-gray-200 w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900">🏥 Catat Kesehatan</h3>
                <button onclick="document.getElementById('healthModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('farm.livestock.health.store', $livestockHerd) }}"
                class="space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900'; @endphp
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jenis *</label>
                        <select name="type" required class="{{ $cls }}">
                            @foreach (\App\Models\LivestockHealthRecord::TYPE_LABELS as $v => $l)
                                <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-xs font-medium text-gray-600 mb-1">Severity</label>
                        <select name="severity" class="{{ $cls }}">
                            <option value="low">Rendah</option>
                            <option value="medium" selected>Sedang</option>
                            <option value="high">Tinggi</option>
                            <option value="critical">Kritis</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kondisi / Penyakit
                        *</label>
                    <input type="text" name="condition" required placeholder="CRD, Snot, Diare, dll"
                        class="{{ $cls }}">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal *</label>
                    <input type="date" name="date" required value="{{ date('Y-m-d') }}"
                        class="{{ $cls }}">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Terdampak (ekor)</label>
                        <input type="number" name="affected_count" min="0" class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Kematian (ekor)</label>
                        <input type="number" name="death_count" min="0" value="0"
                            class="{{ $cls }}">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Gejala</label>
                    <input type="text" name="symptoms" placeholder="Ngorok, lesu, nafsu makan turun"
                        class="{{ $cls }}">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Obat / Treatment</label>
                        <input type="text" name="medication" placeholder="Antibiotik, vitamin"
                            class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Biaya Obat (Rp)</label>
                        <input type="number" name="medication_cost" step="1" min="0"
                            class="{{ $cls }}">
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('healthModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 text-gray-700">Batal</button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 rounded-lg text-sm bg-red-600 hover:bg-red-700 text-white font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Movement Modal --}}
    <div id="movementModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white rounded-2xl border border-gray-200 w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900">📝 Catat Perubahan Populasi</h3>
                <button onclick="document.getElementById('movementModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <p class="text-xs text-gray-400 mb-4">Populasi saat ini: <span
                    class="font-bold text-gray-900">{{ $livestockHerd->current_count }} ekor</span>
            </p>
            <form method="POST" action="{{ route('farm.livestock.movement', $livestockHerd) }}" class="space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900'; @endphp
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jenis *</label>
                        <select name="type" required class="{{ $cls }}">
                            @foreach (\App\Models\LivestockMovement::TYPE_LABELS as $v => $l)
                                @if ($v !== 'purchase')
                                    <option value="{{ $v }}">{{ $l }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah
                            *</label>
                        <input type="number" name="quantity" required min="1" class="{{ $cls }}">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal *</label>
                    <input type="date" name="date" required value="{{ date('Y-m-d') }}"
                        class="{{ $cls }}">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Berat Total (kg)</label>
                        <input type="number" name="weight_kg" step="0.001" class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Nilai (Rp)</label>
                        <input type="number" name="price_total" step="1" class="{{ $cls }}">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Alasan / Tujuan</label>
                    <input type="text" name="reason" placeholder="Penyakit, pembeli, kandang tujuan..."
                        class="{{ $cls }}">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('movementModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 text-gray-700">Batal</button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 rounded-lg text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
