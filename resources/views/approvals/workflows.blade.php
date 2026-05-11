<x-app-layout>
    <x-slot name="header">Workflow Persetujuan</x-slot>

    <div class="space-y-6">

        {{-- Back link --}}
        <a href="{{ route('approvals.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Persetujuan
        </a>

        {{-- Create Workflow --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h2 class="font-semibold text-gray-900 mb-5">Buat Workflow Baru</h2>
            <form method="POST" action="{{ route('approvals.workflows.store') }}"
                  class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nama Workflow *</label>
                    <input type="text" name="name" required placeholder="cth: Persetujuan Pembelian > 5 Juta"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tipe Dokumen</label>
                    <select name="model_type"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:border-blue-500">
                        <option value="">Umum (semua)</option>
                        <option value="App\Models\Invoice">Invoice</option>
                        <option value="App\Models\PurchaseOrder">Purchase Order</option>
                        <option value="App\Models\Quotation">Penawaran</option>
                        <option value="App\Models\Payroll">Penggajian</option>
                        <option value="App\Models\Budget">Anggaran</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Jumlah Minimum (Rp)</label>
                    <input type="number" name="min_amount" min="0" step="1000" placeholder="0"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Jumlah Maksimum (Rp, kosongkan = tidak terbatas)</label>
                    <input type="number" name="max_amount" min="0" step="1000" placeholder="Tidak terbatas"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:border-blue-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-2">Role yang Bisa Menyetujui *</label>
                    <div class="flex flex-wrap gap-3">
                        @foreach(['admin' => 'Admin', 'manager' => 'Manajer', 'staff' => 'Staff', 'kasir' => 'Kasir', 'gudang' => 'Gudang'] as $role => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="approver_roles[]" value="{{ $role }}"
                                {{ in_array($role, ['admin','manager']) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="sm:col-span-2 flex justify-end">
                    <button type="submit"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl transition">
                        Buat Workflow
                    </button>
                </div>
            </form>
        </div>

        {{-- Existing Workflows --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">Daftar Workflow</h2>
            </div>
            @if($workflows->isEmpty())
                <div class="px-6 py-12 text-center text-gray-400 text-sm">Belum ada workflow. Buat workflow pertama di atas.</div>
            @else
            <div class="divide-y divide-gray-100">
                @foreach($workflows ?? [] as $wf)
                <div class="px-6 py-4" x-data="{ editing: false }">
                    {{-- View mode --}}
                    <div x-show="!editing" class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-medium text-gray-900 text-sm">{{ $wf->name }}</p>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $wf->is_active ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400' }}">
                                    {{ $wf->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-3 mt-1.5 text-xs text-gray-400">
                                @if($wf->model_type)
                                <span>?? {{ class_basename($wf->model_type) }}</span>
                                @endif
                                <span>?? Rp {{ number_format($wf->min_amount, 0, ',', '.') }}
                                    @if($wf->max_amount) – Rp {{ number_format($wf->max_amount, 0, ',', '.') }} @else + @endif
                                </span>
                                <span>?? {{ implode(', ', $wf->approver_roles ?? []) }}</span>
                            </div>
                            {{-- Chain visualization --}}
                            <div class="flex items-center gap-1 mt-2 flex-wrap">
                                @foreach($wf->approver_roles ?? [] as $i => $role)
                                @if($i > 0)
                                <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                @endif
                                <span class="px-2 py-0.5 bg-blue-500/10 text-blue-600 rounded-full text-xs font-medium">
                                    {{ ucfirst($role) }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex gap-2 sm:shrink-0">
                            <button @click="editing = true"
                                class="px-3 py-1.5 border border-gray-200 text-gray-600 text-xs font-medium rounded-lg hover:bg-gray-100 transition">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('approvals.workflows.destroy', $wf) }}" data-confirm="Hapus workflow ini?" data-confirm-type="danger">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="px-3 py-1.5 bg-red-500/20 hover:bg-red-500/30 text-red-400 text-xs font-medium rounded-lg transition">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Edit mode --}}
                    <div x-show="editing" x-cloak>
                        <form method="POST" action="{{ route('approvals.workflows.update', $wf) }}"
                              class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @csrf @method('PUT')
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Nama *</label>
                                <input type="text" name="name" required value="{{ $wf->name }}"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Tipe Dokumen</label>
                                <select name="model_type"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:border-blue-500">
                                    <option value="" {{ !$wf->model_type ? 'selected' : '' }}>Umum</option>
                                    <option value="App\Models\Invoice" {{ $wf->model_type === 'App\Models\Invoice' ? 'selected' : '' }}>Invoice</option>
                                    <option value="App\Models\PurchaseOrder" {{ $wf->model_type === 'App\Models\PurchaseOrder' ? 'selected' : '' }}>Purchase Order</option>
                                    <option value="App\Models\Quotation" {{ $wf->model_type === 'App\Models\Quotation' ? 'selected' : '' }}>Penawaran</option>
                                    <option value="App\Models\Payroll" {{ $wf->model_type === 'App\Models\Payroll' ? 'selected' : '' }}>Penggajian</option>
                                    <option value="App\Models\Budget" {{ $wf->model_type === 'App\Models\Budget' ? 'selected' : '' }}>Anggaran</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Min Amount</label>
                                <input type="number" name="min_amount" min="0" step="1000" value="{{ $wf->min_amount }}"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Max Amount</label>
                                <input type="number" name="max_amount" min="0" step="1000" value="{{ $wf->max_amount }}"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:border-blue-500">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 mb-2">Role Approver *</label>
                                <div class="flex flex-wrap gap-3">
                                    @foreach(['admin' => 'Admin', 'manager' => 'Manajer', 'staff' => 'Staff', 'kasir' => 'Kasir', 'gudang' => 'Gudang'] as $role => $label)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="approver_roles[]" value="{{ $role }}"
                                            {{ in_array($role, $wf->approver_roles ?? []) ? 'checked' : '' }}
                                            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">{{ $label }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="sm:col-span-2 flex items-center justify-between">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1"
                                        {{ $wf->is_active ? 'checked' : '' }}
                                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Aktif</span>
                                </label>
                                <div class="flex gap-2">
                                    <button type="button" @click="editing = false"
                                        class="px-4 py-2 border border-gray-200 text-gray-600 text-sm font-medium rounded-xl hover:bg-gray-100 transition">
                                        Batal
                                    </button>
                                    <button type="submit"
                                        class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl transition">
                                        Simpan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
