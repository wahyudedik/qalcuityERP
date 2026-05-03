<x-app-layout>
    <x-slot name="header">Custom Fields</x-slot>

    <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Tambah Field -->
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-900 mb-4">Tambah Field Baru</h3>
                <form method="POST" action="{{ route('custom-fields.store') }}" x-data="{ type: 'text' }" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Modul</label>
                        <select name="module" class="w-full rounded-lg border-gray-300 text-sm">
                            @foreach($modules as $key => $label)
                                <option value="{{ $key }}" {{ $module === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Label Field</label>
                        <input type="text" name="label" required placeholder="Contoh: Nomor Kontrak"
                               class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipe</label>
                        <select name="type" x-model="type" class="w-full rounded-lg border-gray-300 text-sm">
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="type === 'select'">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Pilihan (satu per baris)</label>
                        <textarea name="options" rows="4" placeholder="Opsi 1&#10;Opsi 2&#10;Opsi 3"
                                  class="w-full rounded-lg border-gray-300 text-sm"></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="required" id="required" value="1"
                               class="rounded border-gray-300 text-blue-600">
                        <label for="required" class="text-sm text-gray-600">Wajib diisi</label>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Urutan</label>
                        <input type="number" name="sort_order" value="0" min="0"
                               class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <button type="submit"
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                        Tambah Field
                    </button>
                </form>
            </div>

            <!-- Daftar Field -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Module Tabs -->
                <div class="flex flex-wrap gap-2">
                    @foreach($modules as $key => $label)
                        <a href="{{ route('custom-fields.index', ['module' => $key]) }}"
                           class="px-3 py-1.5 rounded-full text-xs font-medium transition
                               {{ $module === $key
                                   ? 'bg-blue-600 text-white'
                                   : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                @if($fields->isEmpty())
                    <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center text-gray-500">
                        <p class="text-sm">Belum ada custom field untuk modul ini.</p>
                    </div>
                @else
                    <div class="bg-white rounded-2xl border border-gray-200 divide-y divide-gray-100">
                        @foreach($fields as $field)
                            <div class="p-4 flex items-center justify-between gap-4" x-data="{ editing: false }">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-gray-900 text-sm">{{ $field->label }}</span>
                                        @if($field->required)
                                            <span class="text-xs text-red-500">*wajib</span>
                                        @endif
                                        @if(!$field->is_active)
                                            <span class="text-xs px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded">nonaktif</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        Key: <code class="font-mono">{{ $field->key }}</code> ·
                                        Tipe: {{ $types[$field->type] ?? $field->type }}
                                        @if($field->type === 'select' && $field->options)
                                            · {{ count($field->options) }} pilihan
                                        @endif
                                    </p>
                                </div>
                                <div class="flex gap-2 shrink-0">
                                    <button @click="editing = !editing"
                                            class="text-xs text-blue-500 hover:text-blue-700">Edit</button>
                                    <form method="POST" action="{{ route('custom-fields.destroy', $field) }}"
                                          onsubmit="return confirm('Hapus field ini? Semua nilai yang tersimpan akan ikut terhapus.')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                                    </form>
                                </div>
                            </div>
                            <!-- Edit Form -->
                            <div x-show="editing" class="px-4 pb-4 bg-gray-50">
                                <form method="POST" action="{{ route('custom-fields.update', $field) }}" class="grid grid-cols-2 gap-3">
                                    @csrf @method('PUT')
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Label</label>
                                        <input type="text" name="label" value="{{ $field->label }}" required
                                               class="w-full rounded-lg border-gray-300 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Urutan</label>
                                        <input type="number" name="sort_order" value="{{ $field->sort_order }}" min="0"
                                               class="w-full rounded-lg border-gray-300 text-sm">
                                    </div>
                                    @if($field->type === 'select')
                                        <div class="col-span-2">
                                            <label class="block text-xs text-gray-500 mb-1">Pilihan (satu per baris)</label>
                                            <textarea name="options" rows="3"
                                                      class="w-full rounded-lg border-gray-300 text-sm">{{ $field->options ? implode("\n", $field->options) : '' }}</textarea>
                                        </div>
                                    @endif
                                    <div class="flex items-center gap-4 col-span-2">
                                        <label class="flex items-center gap-2 text-sm text-gray-600">
                                            <input type="checkbox" name="required" value="1" {{ $field->required ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-blue-600">
                                            Wajib
                                        </label>
                                        <label class="flex items-center gap-2 text-sm text-gray-600">
                                            <input type="checkbox" name="is_active" value="1" {{ $field->is_active ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-blue-600">
                                            Aktif
                                        </label>
                                        <button type="submit"
                                                class="ml-auto px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700">
                                            Simpan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
