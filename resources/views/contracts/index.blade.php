<x-app-layout>
    <x-slot name="header">Manajemen Kontrak</x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Kontrak Aktif</p>
            <p class="text-2xl font-bold text-green-500">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Segera Expired</p>
            <p class="text-2xl font-bold text-amber-500">{{ $stats['expiring'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Nilai Aktif</p>
            <p class="text-lg font-bold text-gray-900">Rp {{ number_format($stats['value'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Billing Pending</p>
            <p class="text-2xl font-bold text-blue-500">{{ $stats['pending_billing'] }}</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kontrak..."
                class="flex-1 min-w-[150px] px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
                <option value="">Semua Status</option>
                @foreach(['draft'=>'Draft','active'=>'Aktif','expired'=>'Expired','terminated'=>'Terminasi','renewed'=>'Renewed'] as $v=>$l)
                <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <div class="flex gap-2">
            <a href="{{ route('contracts.templates') }}" class="px-3 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Template</a>
            @canmodule('contracts', 'create')
            <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Kontrak</button>
            @endcanmodule
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No. Kontrak</th>
                        <th class="px-4 py-3 text-left">Judul</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Pihak</th>
                        <th class="px-4 py-3 text-center hidden md:table-cell">Periode</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Nilai</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($contracts as $c)
                    @php
                        $sc = ['draft'=>'gray','active'=>'green','expired'=>'red','terminated'=>'red','renewed'=>'purple'][$c->status] ?? 'gray';
                        $sl = ['draft'=>'Draft','active'=>'Aktif','expired'=>'Expired','terminated'=>'Terminasi','renewed'=>'Renewed'][$c->status] ?? $c->status;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900">
                            <a href="{{ route('contracts.show', $c) }}" class="hover:text-blue-500">{{ $c->contract_number }}</a>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ Str::limit($c->title, 40) }}</td>
                        <td class="px-4 py-3 hidden sm:table-cell text-gray-500 text-xs">
                            {{ $c->party_type === 'customer' ? '👤' : '🏢' }} {{ $c->partyName() }}
                        </td>
                        <td class="px-4 py-3 text-center hidden md:table-cell text-xs text-gray-500">
                            {{ $c->start_date->format('d/m/y') }} — {{ $c->end_date->format('d/m/y') }}
                            @if($c->isExpiringSoon()) <span class="text-amber-500 ml-1">⏰ {{ $c->daysRemaining() }}d</span> @endif
                        </td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-gray-900">Rp {{ number_format($c->value, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $sc  }}-100 text-{{ $sc }}-700 $sc }}-500/20 $sc }}-400">{{ $sl }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('contracts.show', $c) }}" class="text-xs px-2 py-1 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">Belum ada kontrak.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($contracts->hasPages())<div class="px-4 py-3 border-t border-gray-100">{{ $contracts->links() }}</div>@endif
    </div>

    {{-- Modal Add Contract --}}
    <div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Buat Kontrak Baru</h3>
                <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('contracts.store') }}" class="p-6 space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900'; @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2"><label class="block text-xs font-medium text-gray-600 mb-1">Judul Kontrak *</label><input type="text" name="title" required placeholder="Kontrak Sewa Gudang 2026" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Pihak *</label>
                        <select name="party_type" id="party-type" required onchange="toggleParty()" class="{{ $cls }}">
                            <option value="customer">Customer</option><option value="supplier">Supplier</option>
                        </select>
                    </div>
                    <div id="party-customer"><label class="block text-xs font-medium text-gray-600 mb-1">Customer</label>
                        <select name="customer_id" class="{{ $cls }}"><option value="">-- Pilih --</option>
                            @foreach($customers ?? [] as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div id="party-supplier" class="hidden"><label class="block text-xs font-medium text-gray-600 mb-1">Supplier</label>
                        <select name="supplier_id" class="{{ $cls }}"><option value="">-- Pilih --</option>
                            @foreach($suppliers ?? [] as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Kategori *</label>
                        <select name="category" required class="{{ $cls }}">
                            <option value="service">Jasa</option><option value="lease">Sewa</option><option value="supply">Supply</option><option value="maintenance">Maintenance</option><option value="subscription">Langganan</option>
                        </select>
                    </div>
                    @if($templates->isNotEmpty())
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Template</label>
                        <select name="template_id" class="{{ $cls }}"><option value="">-- Tanpa Template --</option>
                            @foreach($templates ?? [] as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                        </select>
                    </div>
                    @endif
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Mulai *</label><input type="date" name="start_date" required class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Berakhir *</label><input type="date" name="end_date" required class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Nilai Kontrak (Rp) *</label><input type="number" name="value" required min="0" step="1000" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Siklus Billing *</label>
                        <select name="billing_cycle" required class="{{ $cls }}">
                            <option value="monthly">Bulanan</option><option value="quarterly">Triwulan</option><option value="semi_annual">Semester</option><option value="annual">Tahunan</option><option value="one_time">Sekali</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Billing per Siklus (Rp)</label><input type="number" name="billing_amount" min="0" step="1000" class="{{ $cls }}"></div>
                    <div><label class="flex items-center gap-2 cursor-pointer mt-5"><input type="checkbox" name="auto_renew" value="1" class="rounded"><span class="text-sm text-gray-700">Auto Renew</span></label></div>
                </div>
                <details class="text-sm">
                    <summary class="cursor-pointer text-blue-500 hover:underline">SLA & Ketentuan (opsional)</summary>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-3">
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Response Time (jam)</label><input type="number" name="sla_response_hours" min="1" class="{{ $cls }}"></div>
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Resolution Time (jam)</label><input type="number" name="sla_resolution_hours" min="1" class="{{ $cls }}"></div>
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Uptime (%)</label><input type="number" name="sla_uptime_pct" min="0" max="100" step="0.01" placeholder="99.90" class="{{ $cls }}"></div>
                        <div class="sm:col-span-3"><label class="block text-xs font-medium text-gray-600 mb-1">Ketentuan SLA</label><textarea name="sla_terms" rows="2" class="{{ $cls }}"></textarea></div>
                        <div class="sm:col-span-3"><label class="block text-xs font-medium text-gray-600 mb-1">Terms & Conditions</label><textarea name="terms" rows="2" class="{{ $cls }}"></textarea></div>
                    </div>
                </details>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function toggleParty() {
        const t = document.getElementById('party-type').value;
        document.getElementById('party-customer').classList.toggle('hidden', t !== 'customer');
        document.getElementById('party-supplier').classList.toggle('hidden', t !== 'supplier');
    }
    </script>
    @endpush
</x-app-layout>
