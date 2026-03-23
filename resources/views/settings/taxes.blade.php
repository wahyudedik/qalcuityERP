<x-app-layout>
    <x-slot name="header">Manajemen Tarif Pajak</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 text-sm">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif

        {{-- e-Faktur Export --}}
        <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-xl p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-white text-sm font-medium">Export e-Faktur DJP</p>
                    <p class="text-gray-400 text-xs mt-0.5">Export data PPN ke format CSV siap import ke aplikasi e-Faktur DJP</p>
                </div>
                <form method="GET" action="{{ route('taxes.efaktur') }}" class="flex gap-2 items-center">
                    <input type="date" name="from" value="{{ now()->startOfMonth()->toDateString() }}"
                        class="bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <input type="date" name="to" value="{{ now()->toDateString() }}"
                        class="bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-1.5 rounded-lg">
                        ⬇ Export CSV
                    </button>
                </form>
            </div>
        </div>

        {{-- Tambah Tarif Pajak --}}
        <div class="bg-white/5 border border-white/10 rounded-xl p-6">
            <h2 class="font-semibold text-white mb-4">Tambah Tarif Pajak</h2>

            <form method="POST" action="{{ route('taxes.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Nama Pajak *</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="cth: PPN 11%"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">Kode *</label>
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="cth: PPN11"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">Jenis Pajak *</label>
                    <select name="tax_type" required
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="ppn" @selected(old('tax_type')=='ppn')>PPN (Pajak Pertambahan Nilai)</option>
                        <option value="pph21" @selected(old('tax_type')=='pph21')>PPh 21 (Penghasilan Karyawan)</option>
                        <option value="pph23" @selected(old('tax_type')=='pph23')>PPh 23 (Jasa/Royalti)</option>
                        <option value="pph4ayat2" @selected(old('tax_type')=='pph4ayat2')>PPh 4 Ayat 2 (Final)</option>
                        <option value="custom" @selected(old('tax_type')=='custom')>Custom</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">Tipe Perhitungan *</label>
                    <select name="type"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="percentage" @selected(old('type')=='percentage')>Persentase (%)</option>
                        <option value="fixed" @selected(old('type')=='fixed')>Nominal Tetap (Rp)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">Tarif *</label>
                    <input type="number" name="rate" value="{{ old('rate') }}" placeholder="cth: 11" step="0.01" min="0" max="100"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">Kode Akun GL (opsional)</label>
                    <input type="text" name="account_code" value="{{ old('account_code') }}" placeholder="cth: 2103"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="sm:col-span-2 flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_withholding" value="1" @checked(old('is_withholding'))
                            class="rounded border-white/20 bg-white/5 text-indigo-500">
                        <span class="text-sm text-gray-300">Pajak Pemotongan (Withholding Tax) — PPh 21/23/4(2)</span>
                    </label>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl">
                        Tambah Tarif
                    </button>
                </div>
            </form>
        </div>

        {{-- Daftar Tarif Pajak --}}
        <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10">
                <h2 class="font-semibold text-white">Daftar Tarif Pajak</h2>
            </div>

            @if($taxes->isEmpty())
                <div class="px-6 py-10 text-center text-gray-500 text-sm">Belum ada tarif pajak.</div>
            @else
                <div class="divide-y divide-white/5">
                    @foreach($taxes as $tax)
                    <div class="px-6 py-4 flex items-start gap-4">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-medium text-white text-sm">{{ $tax->name }}</span>
                                <span class="px-2 py-0.5 bg-white/10 text-gray-400 text-xs rounded-full font-mono">{{ $tax->code }}</span>
                                <span class="px-2 py-0.5 text-xs rounded-full
                                    {{ $tax->tax_type === 'ppn' ? 'bg-blue-500/20 text-blue-400' : '' }}
                                    {{ in_array($tax->tax_type, ['pph21','pph23','pph4ayat2']) ? 'bg-orange-500/20 text-orange-400' : '' }}
                                    {{ $tax->tax_type === 'custom' ? 'bg-gray-500/20 text-gray-400' : '' }}">
                                    {{ $tax->getTypeLabel() }}
                                </span>
                                @if($tax->is_withholding)
                                <span class="px-2 py-0.5 bg-yellow-500/20 text-yellow-400 text-xs rounded-full">Withholding</span>
                                @endif
                                @if($tax->is_active)
                                <span class="px-2 py-0.5 bg-green-500/20 text-green-400 text-xs rounded-full">Aktif</span>
                                @else
                                <span class="px-2 py-0.5 bg-gray-500/20 text-gray-400 text-xs rounded-full">Nonaktif</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $tax->type === 'percentage' ? $tax->rate . '%' : 'Rp ' . number_format($tax->rate, 0, ',', '.') }}
                                @if($tax->account_code) &bull; Akun GL: <span class="font-mono">{{ $tax->account_code }}</span> @endif
                            </p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            {{-- Toggle Active --}}
                            <form method="POST" action="{{ route('taxes.update', $tax) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="name" value="{{ $tax->name }}">
                                <input type="hidden" name="code" value="{{ $tax->code }}">
                                <input type="hidden" name="type" value="{{ $tax->type }}">
                                <input type="hidden" name="tax_type" value="{{ $tax->tax_type ?? 'ppn' }}">
                                <input type="hidden" name="rate" value="{{ $tax->rate }}">
                                <input type="hidden" name="is_withholding" value="{{ $tax->is_withholding ? '1' : '0' }}">
                                <input type="hidden" name="account_code" value="{{ $tax->account_code }}">
                                <input type="hidden" name="is_active" value="{{ $tax->is_active ? '0' : '1' }}">
                                <button type="submit"
                                    class="text-xs px-3 py-1.5 rounded-lg border {{ $tax->is_active ? 'border-yellow-500/30 text-yellow-400 hover:bg-yellow-500/10' : 'border-green-500/30 text-green-400 hover:bg-green-500/10' }}">
                                    {{ $tax->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('taxes.destroy', $tax) }}" onsubmit="return confirm('Hapus tarif pajak {{ $tax->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs px-3 py-1.5 rounded-lg border border-red-500/30 text-red-400 hover:bg-red-500/10">Hapus</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Info --}}
        <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl px-4 py-3 text-sm text-blue-400">
            <strong>Jenis Pajak:</strong> PPN = Pajak Pertambahan Nilai (dikenakan ke pelanggan). PPh = Pajak Penghasilan (dipotong dari pembayaran). Withholding Tax = pajak yang dipotong oleh pembeli saat membayar.
        </div>
    </div>
</x-app-layout>
