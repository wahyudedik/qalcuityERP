<x-app-layout>
    <x-slot name="header">📦 Export Documentation</x-slot>

    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
            {{ session('success') }}</div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Izin Ekspor Aktif</p>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['active_permits'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Sertifikat Kesehatan</p>
            <p class="text-2xl font-bold text-emerald-600">{{ $stats['health_certificates'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Deklarasi Bea Cukai</p>
            <p class="text-2xl font-bold text-purple-600">{{ $stats['customs_declarations'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Pengiriman Bulan Ini</p>
            <p class="text-2xl font-bold text-orange-600">{{ $stats['shipments_this_month'] ?? 0 }}</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-white rounded-xl border border-gray-200" x-data="{ tab: @js(request('tab', 'permits')) }">
        <div class="flex border-b border-gray-200 overflow-x-auto">
            <button @click="tab = 'permits'; window.location.href = '?tab=permits'"
                :class="tab === 'permits' ? 'border-b-2 border-orange-600 text-orange-600' :
                    'text-gray-500'"
                class="px-4 py-3 text-sm font-medium whitespace-nowrap transition">
                📄 Izin Ekspor
            </button>
            <button @click="tab = 'certificates'; window.location.href = '?tab=certificates'"
                :class="tab === 'certificates' ? 'border-b-2 border-orange-600 text-orange-600' :
                    'text-gray-500'"
                class="px-4 py-3 text-sm font-medium whitespace-nowrap transition">
                🏥 Sertifikat Kesehatan
            </button>
            <button @click="tab = 'customs'; window.location.href = '?tab=customs'"
                :class="tab === 'customs' ? 'border-b-2 border-orange-600 text-orange-600' :
                    'text-gray-500'"
                class="px-4 py-3 text-sm font-medium whitespace-nowrap transition">
                🛃 Bea Cukai
            </button>
            <button @click="tab = 'shipments'; window.location.href = '?tab=shipments'"
                :class="tab === 'shipments' ? 'border-b-2 border-orange-600 text-orange-600' :
                    'text-gray-500'"
                class="px-4 py-3 text-sm font-medium whitespace-nowrap transition">
                🚢 Pengiriman
            </button>
        </div>

        {{-- Permits Tab --}}
        <div x-show="tab === 'permits'" class="p-4">
            <div class="flex items-center justify-between mb-4">
                <form class="flex items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari izin..."
                        class="px-3 py-2 text-sm rounded-lg border border-gray-200 bg-white text-gray-900 w-48">
                    <select name="status" onchange="this.form.submit()"
                        class="px-3 py-2 text-sm rounded-lg border border-gray-200 bg-white text-gray-900">
                        <option value="">Semua Status</option>
                        <option value="pending" @selected(request('status') === 'pending')">Pending</option>
                        <option value="approved" @selected(request('status') === 'approved')">Disetujui</option>
                        <option value="rejected" @selected(request('status') === 'rejected')">Ditolak</option>
                        <option value="expired" @selected(request('status') === 'expired')">Kadaluarsa</option>
                    </select>
                </form>
                <button onclick="document.getElementById('addPermitModal').classList.remove('hidden')"
                    class="px-4 py-2 text-sm bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition flex items-center gap-2">
                    <span>📝</span> Ajukan Izin Baru
                </button>
            </div>

            @if (empty($permits) || count($permits) === 0)
                <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
                    <p class="text-4xl mb-3">📄</p>
                    <p class="text-sm text-gray-500">Belum ada izin ekspor. Ajukan izin pertama
                        Anda.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($permits as $permit)
                        @php
                            $statusColors = [
                                'pending' => 'yellow',
                                'approved' => 'green',
                                'rejected' => 'red',
                                'expired' => 'gray',
                            ];
                            $color = $statusColors[$permit->status] ?? 'gray';
                        @endphp
                        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg transition">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-base font-bold text-gray-900">
                                            {{ $permit->permit_number }}</h4>
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full bg-{{ $color }}-100 text-{{ $color }}-700 $color }}-500/20 $color }}-400">
                                            {{ ucfirst($permit->status) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ $permit->permit_type }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">Berlaku Sampai</p>
                                    <p
                                        class="text-sm font-medium {{ $permit->isExpired() ? 'text-red-600' : 'text-gray-700' }}">
                                        {{ $permit->valid_until->format('d M Y') }}
                                        @if ($permit->isExpired())
                                            <span class="text-xs">(Kadaluarsa)</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                @if ($permit->destination_country)
                                    <div>
                                        <span class="text-gray-400 text-xs block">Negara Tujuan</span>
                                        <span
                                            class="text-gray-700 font-medium">{{ $permit->destination_country }}</span>
                                    </div>
                                @endif
                                @if ($permit->commodity)
                                    <div>
                                        <span class="text-gray-400 text-xs block">Komoditas</span>
                                        <span class="text-gray-700 font-medium">{{ $permit->commodity }}</span>
                                    </div>
                                @endif
                                @if ($permit->quantity_kg)
                                    <div>
                                        <span class="text-gray-400 text-xs block">Kuantitas</span>
                                        <span
                                            class="text-gray-700 font-medium">{{ number_format($permit->quantity_kg, 1) }}
                                            kg</span>
                                    </div>
                                @endif
                                @if ($permit->issuing_authority)
                                    <div>
                                        <span class="text-gray-400 text-xs block">Penerbit</span>
                                        <span class="text-gray-700 font-medium">{{ $permit->issuing_authority }}</span>
                                    </div>
                                @endif
                            </div>

                            @if ($permit->notes)
                                <p class="text-xs text-gray-500 mt-3 pt-3 border-t border-gray-100">
                                    {{ Str::limit($permit->notes, 150) }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">{{ $permits->links() }}</div>
            @endif
        </div>

        {{-- Certificates Tab --}}
        <div x-show="tab === 'certificates'" class="p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-700">Sertifikat Kesehatan Ikan</h3>
                <button onclick="document.getElementById('addCertificateModal').classList.remove('hidden')"
                    class="px-4 py-2 text-sm bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition flex items-center gap-2">
                    <span>🏥</span> Buat Sertifikat Baru
                </button>
            </div>

            @if (empty($certificates) || count($certificates) === 0)
                <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
                    <p class="text-4xl mb-3">🏥</p>
                    <p class="text-sm text-gray-500">Belum ada sertifikat kesehatan. Buat sertifikat
                        pertama Anda.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($certificates as $cert)
                        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg transition">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h4 class="text-base font-bold text-gray-900">
                                        {{ $cert->certificate_number }}</h4>
                                    <p class="text-sm text-gray-500 mt-1">Issued:
                                        {{ $cert->issued_date->format('d M Y') }}</p>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">
                                    Valid
                                </span>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
                                @if ($cert->veterinarian_name)
                                    <div>
                                        <span class="text-gray-400 text-xs block">Dokter Hewan</span>
                                        <span class="text-gray-700 font-medium">{{ $cert->veterinarian_name }}</span>
                                    </div>
                                @endif
                                @if ($cert->species_tested)
                                    <div>
                                        <span class="text-gray-400 text-xs block">Spesies Diuji</span>
                                        <span class="text-gray-700 font-medium">{{ $cert->species_tested }}</span>
                                    </div>
                                @endif
                                @if ($cert->test_results)
                                    <div class="col-span-2 md:col-span-3">
                                        <span class="text-gray-400 text-xs block">Hasil Tes</span>
                                        <span class="text-gray-700">{{ Str::limit($cert->test_results, 200) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Customs Declarations Tab --}}
        <div x-show="tab === 'customs'" class="p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-700">Deklarasi Bea Cukai</h3>
                <button onclick="document.getElementById('addCustomsModal').classList.remove('hidden')"
                    class="px-4 py-2 text-sm bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition flex items-center gap-2">
                    <span>🛃</span> Buat Deklarasi Baru
                </button>
            </div>

            @if (empty($customsDeclarations) || count($customsDeclarations) === 0)
                <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
                    <p class="text-4xl mb-3">🛃</p>
                    <p class="text-sm text-gray-500">Belum ada deklarasi bea cukai. Buat deklarasi
                        pertama Anda.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($customsDeclarations as $declaration)
                        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg transition">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h4 class="text-base font-bold text-gray-900">
                                        {{ $declaration->declaration_number }}</h4>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ $declaration->declaration_type }}</p>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full bg-purple-100 text-purple-700">
                                    {{ ucfirst($declaration->status) }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                @if ($declaration->hs_code)
                                    <div>
                                        <span class="text-gray-400 text-xs block">HS Code</span>
                                        <span class="text-gray-700 font-medium">{{ $declaration->hs_code }}</span>
                                    </div>
                                @endif
                                @if ($declaration->declared_value_usd)
                                    <div>
                                        <span class="text-gray-400 text-xs block">Nilai Deklarasi</span>
                                        <span
                                            class="text-gray-700 font-medium">${{ number_format($declaration->declared_value_usd, 2) }}</span>
                                    </div>
                                @endif
                                @if ($declaration->weight_kg)
                                    <div>
                                        <span class="text-gray-400 text-xs block">Berat</span>
                                        <span
                                            class="text-gray-700 font-medium">{{ number_format($declaration->weight_kg, 1) }}
                                            kg</span>
                                    </div>
                                @endif
                                @if ($declaration->country_of_origin)
                                    <div>
                                        <span class="text-gray-400 text-xs block">Negara Asal</span>
                                        <span
                                            class="text-gray-700 font-medium">{{ $declaration->country_of_origin }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Shipments Tab --}}
        <div x-show="tab === 'shipments'" class="p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-700">Pengiriman Ekspor</h3>
                <button onclick="document.getElementById('addShipmentModal').classList.remove('hidden')"
                    class="px-4 py-2 text-sm bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition flex items-center gap-2">
                    <span>🚢</span> Buat Pengiriman Baru
                </button>
            </div>

            @if (empty($shipments) || count($shipments) === 0)
                <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
                    <p class="text-4xl mb-3">🚢</p>
                    <p class="text-sm text-gray-500">Belum ada pengiriman ekspor. Buat pengiriman
                        pertama Anda.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($shipments as $shipment)
                        @php
                            $statusColors = [
                                'preparing' => 'gray',
                                'ready_to_ship' => 'blue',
                                'in_transit' => 'yellow',
                                'customs_clearance' => 'purple',
                                'delivered' => 'green',
                                'cancelled' => 'red',
                            ];
                            $color = $statusColors[$shipment->status] ?? 'gray';
                        @endphp
                        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg transition">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-base font-bold text-gray-900">
                                            {{ $shipment->shipment_number }}</h4>
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full bg-{{ $color }}-100 text-{{ $color }}-700 $color }}-500/20 $color }}-400">
                                            {{ str_replace('_', ' ', ucfirst($shipment->status)) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ $shipment->origin }} → {{ $shipment->destination }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">Estimasi Tiba</p>
                                    <p class="text-sm font-medium text-gray-700">
                                        {{ $shipment->estimated_arrival ? $shipment->estimated_arrival->format('d M Y') : '-' }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                @if ($shipment->carrier)
                                    <div>
                                        <span class="text-gray-400 text-xs block">Ekspedisi</span>
                                        <span class="text-gray-700 font-medium">{{ $shipment->carrier }}</span>
                                    </div>
                                @endif
                                @if ($shipment->tracking_number)
                                    <div>
                                        <span class="text-gray-400 text-xs block">No. Tracking</span>
                                        <span
                                            class="text-gray-700 font-medium">{{ $shipment->tracking_number }}</span>
                                    </div>
                                @endif
                                @if ($shipment->total_weight_kg)
                                    <div>
                                        <span class="text-gray-400 text-xs block">Berat Total</span>
                                        <span
                                            class="text-gray-700 font-medium">{{ number_format($shipment->total_weight_kg, 1) }}
                                            kg</span>
                                    </div>
                                @endif
                                @if ($shipment->declared_value_usd)
                                    <div>
                                        <span class="text-gray-400 text-xs block">Nilai</span>
                                        <span
                                            class="text-gray-700 font-medium">${{ number_format($shipment->declared_value_usd, 2) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">{{ $shipments->links() }}</div>
            @endif
        </div>
    </div>

    {{-- Add Permit Modal --}}
    <div id="addPermitModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl border border-gray-200 w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-semibold text-gray-900">Ajukan Izin Ekspor Baru</h2>
                <button onclick="document.getElementById('addPermitModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('fisheries.api.export.permits.apply') }}" class="space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900'; @endphp

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipe Izin
                            *</label>
                        <select name="permit_type" required class="{{ $cls }}">
                            <option value="export_license">Export License</option>
                            <option value="catch_certificate">Catch Certificate</option>
                            <option value="processing_statement">Processing Statement</option>
                            <option value="re_export_certificate">Re-export Certificate</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Negara Tujuan
                            *</label>
                        <input type="text" name="destination_country" required placeholder="United States"
                            class="{{ $cls }}">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Komoditas
                            *</label>
                        <input type="text" name="commodity" required placeholder="Frozen Shrimp"
                            class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kuantitas (kg)
                            *</label>
                        <input type="number" name="quantity_kg" required step="0.01" min="0"
                            placeholder="5000" class="{{ $cls }}">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Berlaku
                            *</label>
                        <input type="date" name="valid_from" required class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kadaluarsa
                            *</label>
                        <input type="date" name="valid_until" required class="{{ $cls }}">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Otoritas
                        Penerbit</label>
                    <input type="text" name="issuing_authority"
                        placeholder="Ministry of Marine Affairs and Fisheries" class="{{ $cls }}">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                    <textarea name="notes" rows="3" placeholder="Persyaratan khusus, regulasi, dll."
                        class="{{ $cls }}"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 text-sm bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition">
                        📤 Ajukan Izin
                    </button>
                    <button type="button" onclick="document.getElementById('addPermitModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-lg transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Other modals can be added similarly for certificates, customs, shipments --}}
</x-app-layout>
