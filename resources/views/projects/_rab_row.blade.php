@php
    $isGroup = $item->type === 'group';
    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $depth);
    $realPct = $item->realizationPercent();
    $overBudget = $item->actual_cost > $item->subtotal && $item->subtotal > 0;
@endphp
<tr class="{{ $isGroup ? 'bg-gray-50/50 dark:bg-white/[0.02]' : '' }} hover:bg-gray-50 dark:hover:bg-white/5 transition">
    {{-- Kode --}}
    <td class="px-4 py-2.5 text-xs text-gray-500 dark:text-slate-400 font-mono">{{ $item->code }}</td>

    {{-- Uraian --}}
    <td class="px-4 py-2.5">
        <span
            class="{{ $isGroup ? 'font-semibold text-gray-900 dark:text-white' : 'text-gray-700 dark:text-slate-300' }} text-sm">
            {!! $indent !!}{{ $item->name }}
        </span>
        @if ($item->category)
            <span
                class="ml-1.5 text-[10px] px-1.5 py-0.5 rounded bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-slate-400 capitalize">{{ $item->category }}</span>
        @endif
    </td>

    {{-- Volume --}}
    <td class="px-4 py-2.5 text-right text-sm text-gray-600 dark:text-slate-300 font-mono">
        @if (!$isGroup && $item->volume > 0)
            {{ number_format($item->volume, $item->volume == (int) $item->volume ? 0 : 2) }}
            @if ($item->actual_volume > 0)
                <div class="text-[10px] text-blue-500">
                    {{ number_format($item->actual_volume, $item->actual_volume == (int) $item->actual_volume ? 0 : 2) }}
                    real</div>
            @endif
        @endif
    </td>

    {{-- Satuan --}}
    <td class="px-4 py-2.5 text-xs text-gray-500 dark:text-slate-400">{{ $item->unit }}</td>

    {{-- Harga Satuan --}}
    <td class="px-4 py-2.5 text-right text-sm text-gray-600 dark:text-slate-300 font-mono">
        @if (!$isGroup && $item->unit_price > 0)
            {{ number_format($item->unit_price, 0, ',', '.') }}
        @endif
    </td>

    {{-- Koefisien --}}
    <td class="px-4 py-2.5 text-right text-xs text-gray-500 dark:text-slate-400 font-mono">
        @if (!$isGroup && $item->coefficient != 1)
            {{ $item->coefficient }}
        @endif
    </td>

    {{-- Jumlah RAB --}}
    <td
        class="px-4 py-2.5 text-right text-sm font-mono {{ $isGroup ? 'font-semibold text-gray-900 dark:text-white' : 'text-gray-700 dark:text-slate-300' }}">
        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
    </td>

    {{-- Realisasi --}}
    <td
        class="px-4 py-2.5 text-right text-sm font-mono {{ $overBudget ? 'text-red-500 font-semibold' : 'text-gray-600 dark:text-slate-400' }}">
        @if ($item->actual_cost > 0)
            Rp {{ number_format($item->actual_cost, 0, ',', '.') }}
            <div class="text-[10px] {{ $overBudget ? 'text-red-400' : 'text-gray-400' }}">{{ $realPct }}%</div>
        @else
            <span class="text-gray-300 dark:text-slate-600">—</span>
        @endif
    </td>

    {{-- Aksi --}}
    <td class="px-4 py-2.5">
        <div class="flex items-center gap-1">
            @if (!$isGroup)
                <button
                    onclick="openActualModal({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->actual_cost }}, {{ $item->actual_volume }})"
                    class="text-[10px] text-blue-500 hover:text-blue-600 px-1.5 py-0.5 rounded hover:bg-blue-50 dark:hover:bg-blue-500/10"
                    title="Catat Realisasi">📝</button>
            @endif
            <form method="POST" action="{{ route('projects.rab.destroy', $item) }}"
                onsubmit="return confirm('Hapus item ini?')" class="inline">
                @csrf @method('DELETE')
                <button type="submit"
                    class="text-[10px] text-red-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 dark:hover:bg-red-500/10"
                    title="Hapus">Hapus</button>
            </form>
        </div>
    </td>
</tr>
