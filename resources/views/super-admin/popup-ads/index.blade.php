<x-app-layout>
    <x-slot name="title">Popup Iklan — Qalcuity ERP</x-slot>
    <x-slot name="header">Popup Iklan</x-slot>
    <x-slot name="topbarActions">
        <a href="{{ route('super-admin.popup-ads.create') }}"
            class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Buat Iklan
        </a>
    </x-slot>

    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-500/20 border border-green-500/30 text-green-400 text-sm rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-[#1e293b] border border-white/10 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-white/10 bg-white/5">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Judul</th>
                        <th
                            class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider hidden md:table-cell">
                            Target</th>
                        <th
                            class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider hidden md:table-cell">
                            Frekuensi</th>
                        <th
                            class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider hidden lg:table-cell">
                            Periode</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Status</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($ads as $ad)
                        @php
                            $statusLabel = $ad->status_label;
                            $statusClass = match ($statusLabel) {
                                'Aktif' => 'text-green-400 bg-green-500/15 border-green-500/30',
                                'Nonaktif' => 'text-slate-400 bg-white/10 border-white/10',
                                'Terjadwal' => 'text-blue-400 bg-blue-500/15 border-blue-500/30',
                                'Kedaluwarsa' => 'text-red-400 bg-red-500/15 border-red-500/30',
                                default => 'text-slate-400 bg-white/10 border-white/10',
                            };
                        @endphp
                        <tr class="hover:bg-white/5 transition">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if ($ad->image_path)
                                        <img src="{{ Storage::url($ad->image_path) }}"
                                            class="w-10 h-10 rounded-lg object-cover shrink-0 border border-white/10">
                                    @else
                                        <div
                                            class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-slate-100 truncate">{{ $ad->title }}</p>
                                        @if ($ad->button_label)
                                            <p class="text-xs text-slate-500 truncate">CTA: {{ $ad->button_label }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 hidden md:table-cell">
                                @if ($ad->target === 'all')
                                    <span class="text-xs text-slate-300">Semua Tenant</span>
                                @else
                                    <span class="text-xs text-blue-400">{{ count($ad->tenant_ids ?? []) }} tenant</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 hidden md:table-cell">
                                <span class="text-xs text-slate-300">
                                    {{ match ($ad->frequency) {
                                        'once' => 'Sekali saja',
                                        'daily' => 'Setiap hari',
                                        'always' => 'Selalu',
                                        default => $ad->frequency,
                                    } }}
                                </span>
                            </td>
                            <td class="px-5 py-4 hidden lg:table-cell">
                                <p class="text-xs text-slate-400">
                                    {{ $ad->starts_at ? $ad->starts_at->format('d M Y') : '—' }}
                                    @if ($ad->ends_at)
                                        <span class="text-slate-600 mx-1">→</span>
                                        {{ $ad->ends_at->format('d M Y') }}
                                    @endif
                                </p>
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('super-admin.popup-ads.edit', $ad) }}"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-blue-400 hover:bg-blue-500/10 transition"
                                        title="Edit">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('super-admin.popup-ads.toggle', $ad) }}"
                                        class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                            class="p-1.5 rounded-lg transition {{ $ad->is_active ? 'text-green-400 hover:text-slate-400 hover:bg-white/10' : 'text-slate-400 hover:text-green-400 hover:bg-green-500/10' }}"
                                            title="{{ $ad->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="{{ $ad->is_active ? 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' }}" />
                                            </svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('super-admin.popup-ads.destroy', $ad) }}"
                                        onsubmit="return confirm('Hapus popup iklan ini?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition"
                                            title="Hapus">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-slate-500">
                                    <svg class="w-10 h-10 opacity-30" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                    </svg>
                                    <p class="text-sm">Belum ada popup iklan.</p>
                                    <a href="{{ route('super-admin.popup-ads.create') }}"
                                        class="text-sm text-blue-400 hover:text-blue-300 font-medium">+ Buat iklan
                                        pertama</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($ads->hasPages())
            <div class="px-6 py-4 border-t border-white/10">{{ $ads->links() }}</div>
        @endif
    </div>
</x-app-layout>
