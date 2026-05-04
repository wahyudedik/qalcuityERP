@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8 space-y-8">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Profil Perusahaan</h1>
        <p class="text-sm text-gray-500 mt-1">Identitas perusahaan yang tampil di semua dokumen cetak (invoice, laporan, surat).</p>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">
        {{ session('success') }}
    </div>
    @endif

    {{-- Company Profile Form --}}
    <form method="POST" action="{{ route('company-profile.update') }}" enctype="multipart/form-data"
          class="bg-white rounded-2xl border border-gray-200 p-6 space-y-6">
        @csrf @method('PUT')

        {{-- Identitas Utama --}}
        <div>
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Identitas Utama</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Perusahaan <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required
                           class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tagline / Slogan</label>
                    <input type="text" name="tagline" value="{{ old('tagline', $tenant->tagline) }}" placeholder="Solusi terbaik untuk bisnis Anda"
                           class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $tenant->email) }}"
                           class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}"
                           class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NPWP</label>
                    <input type="text" name="npwp" value="{{ old('npwp', $tenant->npwp) }}" placeholder="00.000.000.0-000.000"
                           class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                    <input type="url" name="website" value="{{ old('website', $tenant->website) }}" placeholder="https://perusahaan.com"
                           class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
        </div>

        {{-- Alamat --}}
        <div>
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Alamat</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                    <textarea name="address" rows="2"
                              class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('address', $tenant->address) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kota</label>
                    <input type="text" name="city" value="{{ old('city', $tenant->city) }}"
                           class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Provinsi</label>
                    <input type="text" name="province" value="{{ old('province', $tenant->province) }}"
                           class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Pos</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code', $tenant->postal_code) }}"
                           class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
        </div>

        {{-- Rekening Bank --}}
        <div>
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Rekening Bank</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Bank</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $tenant->bank_name) }}" placeholder="BCA, Mandiri, BNI..."
                           class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. Rekening</label>
                    <input type="text" name="bank_account" value="{{ old('bank_account', $tenant->bank_account) }}"
                           class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Atas Nama</label>
                    <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $tenant->bank_account_name) }}"
                           class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
        </div>

        {{-- Pengaturan Dokumen --}}
        <div>
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Pengaturan Dokumen</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Footer Invoice</label>
                    <textarea name="invoice_footer_notes" rows="2" placeholder="Terima kasih atas kepercayaan Anda..."
                              class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('invoice_footer_notes', $tenant->invoice_footer_notes) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Syarat Pembayaran Default</label>
                    <input type="text" name="invoice_payment_terms" value="{{ old('invoice_payment_terms', $tenant->invoice_payment_terms) }}"
                           placeholder="Pembayaran dalam 14 hari setelah invoice diterima"
                           class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prefix Nomor Dokumen</label>
                        <input type="text" name="doc_number_prefix" value="{{ old('doc_number_prefix', $tenant->doc_number_prefix) }}"
                               placeholder="INV, PO, QT..."
                               class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Warna Kop Surat</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="letter_head_color" value="{{ old('letter_head_color', $tenant->letter_head_color ?? '#1d4ed8') }}"
                               class="h-10 w-16 rounded-lg border border-gray-300 cursor-pointer">
                        <span class="text-sm text-gray-500">Warna aksen pada kop surat PDF</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Upload Gambar --}}
        <div>
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Logo, Stempel & Tanda Tangan</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                @foreach([
                    ['field' => 'logo', 'label' => 'Logo Perusahaan', 'desc' => 'Tampil di kop surat semua dokumen'],
                    ['field' => 'stamp_image', 'label' => 'Stempel Perusahaan', 'desc' => 'Tampil di footer invoice & surat'],
                    ['field' => 'director_signature', 'label' => 'TTD Direktur', 'desc' => 'Tanda tangan default untuk surat'],
                ] as $img)
                <div class="border border-gray-200 rounded-xl p-4">
                    <p class="text-sm font-medium text-gray-700 mb-1">{{ $img['label'] }}</p>
                    <p class="text-xs text-gray-400 mb-3">{{ $img['desc'] }}</p>

                    @if($tenant->{$img['field']})
                    <div class="mb-3 relative">
                        <img src="{{ Storage::url($tenant->{$img['field']}) }}" alt="{{ $img['label'] }}"
                             class="max-h-20 max-w-full object-contain rounded-lg border border-gray-200">
                        <form method="POST" action="{{ route('company-profile.remove-image', $img['field']) }}" class="mt-2">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus gambar</button>
                        </form>
                    </div>
                    @endif

                    <input type="file" name="{{ $img['field'] }}" accept="image/*"
                           class="block w-full text-sm text-gray-500
                                  file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                                  file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700
                                  hover:file:bg-blue-100 cursor-pointer">
                    <p class="text-xs text-gray-400 mt-1">PNG/JPG, maks 2MB</p>
                </div>
                @endforeach

            </div>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-colors">
                Simpan Profil Perusahaan
            </button>
        </div>
    </form>

    {{-- Document Templates --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Template Dokumen</h2>
                <p class="text-sm text-gray-500 mt-0.5">Kustomisasi template HTML untuk setiap jenis dokumen.</p>
            </div>
            <button onclick="document.getElementById('modal-add-template').classList.remove('hidden')"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-colors">
                + Tambah Template
            </button>
        </div>

        @if($templates->isEmpty())
        <div class="text-center py-10 text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-sm">Belum ada template. Tambahkan template kustom untuk dokumen Anda.</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($templates ?? [] as $tpl)
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-900">{{ $tpl->name }}</span>
                        @if($tpl->is_default)
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Default</span>
                        @endif
                    </div>
                    <span class="text-xs text-gray-500">{{ \App\Models\DocumentTemplate::docTypeLabel($tpl->doc_type) }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="editTemplate({{ $tpl->id }}, '{{ addslashes($tpl->name) }}', `{{ addslashes($tpl->html_content) }}`, {{ $tpl->is_default ? 'true' : 'false' }})"
                            class="text-xs text-blue-600 hover:underline">Edit</button>
                    <form method="POST" action="{{ route('company-profile.templates.destroy', $tpl) }}" onsubmit="return confirm('Hapus template ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:underline">Hapus</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- Modal Tambah Template --}}
<div id="modal-add-template" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Tambah Template Dokumen</h3>
        <form method="POST" action="{{ route('company-profile.templates.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Template</label>
                    <input type="text" name="name" required placeholder="Template Invoice Standar"
                           class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Dokumen</label>
                    <select name="doc_type" required class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm">
                        <option value="invoice">Invoice</option>
                        <option value="po">Purchase Order</option>
                        <option value="quotation">Penawaran (Quotation)</option>
                        <option value="letter">Surat Umum</option>
                        <option value="memo">Memo Internal</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Konten HTML</label>
                <p class="text-xs text-gray-400 mb-2">Gunakan placeholder: <code class="bg-gray-100 px-1 rounded">@{{ company_name }}</code>, <code class="bg-gray-100 px-1 rounded">@{{ date }}</code>, <code class="bg-gray-100 px-1 rounded">@{{ recipient }}</code>, <code class="bg-gray-100 px-1 rounded">@{{ npwp }}</code></p>
                <textarea name="html_content" rows="10" required
                          class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm font-mono text-xs"
                          placeholder="<div>@{{ company_name }}</div>..."></textarea>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_default" value="1" id="is_default_new" class="rounded">
                <label for="is_default_new" class="text-sm text-gray-700">Jadikan template default untuk jenis ini</label>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-add-template').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Batal</button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Template --}}
<div id="modal-edit-template" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Template</h3>
        <form id="form-edit-template" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Template</label>
                <input type="text" name="name" id="edit_name" required
                       class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Konten HTML</label>
                <textarea name="html_content" id="edit_html" rows="10" required
                          class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm font-mono text-xs"></textarea>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_default" value="1" id="edit_is_default" class="rounded">
                <label for="edit_is_default" class="text-sm text-gray-700">Jadikan template default</label>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-edit-template').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Batal</button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl">Perbarui</button>
            </div>
        </form>
    </div>
</div>

<script>
function editTemplate(id, name, html, isDefault) {
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_html').value = html;
    document.getElementById('edit_is_default').checked = isDefault;
    document.getElementById('form-edit-template').action = '{{ url("settings/company-profile/templates") }}/' + id;
    document.getElementById('modal-edit-template').classList.remove('hidden');
}
</script>
@endsection
