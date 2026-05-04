<x-app-layout>
    <x-slot name="header">Program Loyalitas</x-slot>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">{{ $errors->first() }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Left: Members ──────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Stats --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach([
                    ['label'=>'Total Member','value'=>number_format($stats['total_members']),'color'=>'text-gray-900'],
                    ['label'=>'Total Poin Aktif','value'=>number_format($stats['total_points']),'color'=>'text-blue-600'],
                    ['label'=>'Poin Diperoleh (Bln)','value'=>number_format($stats['earned_month']),'color'=>'text-green-600'],
                    ['label'=>'Poin Ditukar (Bln)','value'=>number_format($stats['redeemed_month']),'color'=>'text-amber-600'],
                ] as $s)
                <div class="bg-white rounded-2xl p-4 border border-gray-200">
                    <p class="text-xs text-gray-500">{{ $s['label'] }}</p>
                    <p class="text-xl font-bold {{ $s['color'] }} mt-1">{{ $s['value'] }}</p>
                </div>
                @endforeach
            </div>

            {{-- Toolbar --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                <form method="GET" class="flex gap-2 flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama pelanggan..."
                        class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <select name="tier" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
                        <option value="">Semua Tier</option>
                        @foreach($tiers ?? [] as $tier)
                        <option value="{{ $tier->name }}" @selected(request('tier')===$tier->name)>{{ $tier->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
                </form>
                <button onclick="document.getElementById('modal-add-points').classList.remove('hidden')"
                    class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700 shrink-0">+ Tambah Poin</button>
                <button onclick="document.getElementById('modal-redeem').classList.remove('hidden')"
                    class="px-4 py-2 text-sm bg-amber-600 text-white rounded-xl hover:bg-amber-700 shrink-0">Tukar Poin</button>
            </div>

            {{-- Members Table --}}
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Pelanggan</th>
                                <th class="px-4 py-3 text-center">Tier</th>
                                <th class="px-4 py-3 text-right">Poin Aktif</th>
                                <th class="px-4 py-3 text-right hidden md:table-cell">Lifetime</th>
                                @if($program)
                                <th class="px-4 py-3 text-right hidden sm:table-cell">Nilai Poin</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($points as $lp)
                            @php
                                $tierColors = ['Bronze'=>'amber','Silver'=>'gray','Gold'=>'yellow','Platinum'=>'blue'];
                                $tc = $tierColors[$lp->tier] ?? 'gray';
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900">{{ $lp->customer?->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $lp->customer?->phone ?? $lp->customer?->email ?? '' }}</p>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $tc  }}-100 text-{{ $tc }}-700 $tc }}-500/20 $tc }}-400">
                                        {{ $lp->tier }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ number_format($lp->total_points) }}</td>
                                <td class="px-4 py-3 text-right hidden md:table-cell text-gray-500">{{ number_format($lp->lifetime_points) }}</td>
                                @if($program)
                                <td class="px-4 py-3 text-right hidden sm:table-cell text-blue-600 font-medium">
                                    Rp {{ number_format($lp->total_points * $program->idr_per_point, 0, ',', '.') }}
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr><td colspan="5" class="px-4 py-12 text-center text-gray-400">Belum ada member. Tambahkan poin ke pelanggan untuk memulai.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($points->hasPages())
                <div class="px-4 py-3 border-t border-gray-100">{{ $points->links() }}</div>
                @endif
            </div>
        </div>

        {{-- ── Right: Program Settings ─────────────────────────── --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-900 mb-4">Pengaturan Program</h3>
                <form method="POST" action="{{ route('loyalty.program.save') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Program</label>
                        <input type="text" name="name" value="{{ $program?->name ?? 'Program Poin Setia' }}" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Poin per Rp 1</label>
                        <input type="number" name="points_per_idr" value="{{ $program?->points_per_idr ?? 0.01 }}" step="0.001" min="0" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-400 mt-1">0.01 = 1 poin per Rp 100</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nilai 1 Poin (Rp)</label>
                        <input type="number" name="idr_per_point" value="{{ $program?->idr_per_point ?? 100 }}" min="0" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Minimum Redeem (poin)</label>
                        <input type="number" name="min_redeem_points" value="{{ $program?->min_redeem_points ?? 100 }}" min="1" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Masa Berlaku Poin (hari, 0=∞)</label>
                        <input type="number" name="expiry_days" value="{{ $program?->expiry_days ?? 365 }}" min="0" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan Program</button>
                </form>
            </div>

            {{-- Tier Info --}}
            @if($tiers->count() > 0)
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-900 mb-3">Tier Member</h3>
                <div class="space-y-2">
                    @foreach($tiers ?? [] as $tier)
                    <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full" style="background:{{ $tier->color }}"></div>
                            <span class="text-sm font-medium text-gray-900">{{ $tier->name }}</span>
                        </div>
                        <div class="text-right text-xs text-gray-500">
                            <p>≥ {{ number_format($tier->min_points) }} poin</p>
                            <p>{{ $tier->multiplier }}x multiplier</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Modal Tambah Poin --}}
    <div id="modal-add-points" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Tambah Poin</h3>
                <button onclick="document.getElementById('modal-add-points').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('loyalty.add-points') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Pelanggan *</label>
                    <select name="customer_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach(\App\Models\Customer::where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get() as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah Transaksi (Rp) *</label>
                    <input type="number" name="transaction_amount" min="0" step="1000" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @if($program)<p class="text-xs text-gray-400 mt-1">Poin otomatis: Rp 100 = 1 poin</p>@endif
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Override Poin (opsional)</label>
                    <input type="number" name="points_override" min="1" placeholder="Kosongkan untuk kalkulasi otomatis" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Referensi</label>
                    <input type="text" name="reference" placeholder="No. invoice / transaksi" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-points').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Tambah Poin</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Tukar Poin --}}
    <div id="modal-redeem" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Tukar Poin</h3>
                <button onclick="document.getElementById('modal-redeem').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('loyalty.redeem') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Pelanggan *</label>
                    <select name="customer_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach(\App\Models\Customer::where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get() as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah Poin *</label>
                    <input type="number" name="points" min="{{ $program?->min_redeem_points ?? 100 }}" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @if($program)<p class="text-xs text-gray-400 mt-1">Min. {{ $program->min_redeem_points }} poin · 1 poin = Rp {{ number_format($program->idr_per_point,0,',','.') }}</p>@endif
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Referensi</label>
                    <input type="text" name="reference" placeholder="No. transaksi" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-redeem').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-amber-600 text-white rounded-xl hover:bg-amber-700">Tukar Poin</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
