<x-app-layout>
    <x-slot name="header">Detail Work Order — {{ $workOrder->number }}</x-slot>

    <div class="space-y-6">
        {{-- Info WO --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $workOrder->number }}</h2>
                    <p class="text-sm text-gray-500">{{ $workOrder->product?->name }}</p>
                </div>
                <div class="flex items-center gap-2">
                    @php
                        $colors = ['pending'=>'amber','in_progress'=>'blue','completed'=>'green','cancelled'=>'red'];
                        $labels = ['pending'=>'Pending','in_progress'=>'Sedang Dikerjakan','completed'=>'Selesai','cancelled'=>'Dibatalkan'];
                        $c = $colors[$workOrder->status] ?? 'gray';
                    @endphp
                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-{{ $c  }}-100 text-{{ $c }}-700 $c }}-500/20 $c }}-400">
                        {{ $labels[$workOrder->status] ?? $workOrder->status }}
                    </span>
                    @if($workOrder->status === 'pending')
                    <form method="POST" action="{{ route('production.status', $workOrder) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="in_progress">
                        <button type="submit" class="px-3 py-1 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Mulai Produksi</button>
                    </form>
                    @elseif($workOrder->status === 'in_progress')
                    <form method="POST" action="{{ route('production.status', $workOrder) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="px-3 py-1 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Selesaikan</button>
                    </form>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-500">Target</p>
                    <p class="font-semibold text-gray-900">{{ number_format($workOrder->target_quantity, 0, ',', '.') }} {{ $workOrder->unit }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Output Bagus</p>
                    <p class="font-semibold text-green-500">{{ $workOrder->totalGoodQty() }} {{ $workOrder->unit }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Reject</p>
                    <p class="font-semibold text-red-500">{{ $workOrder->totalRejectQty() }} {{ $workOrder->unit }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Yield Rate</p>
                    <p class="font-semibold text-gray-900">{{ $workOrder->yieldRate() ?? '-' }}%</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Biaya Material</p>
                    <p class="font-semibold text-gray-900">Rp {{ number_format($workOrder->material_cost, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Biaya Tenaga Kerja</p>
                    <p class="font-semibold text-gray-900">Rp {{ number_format($workOrder->labor_cost, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Overhead</p>
                    <p class="font-semibold text-gray-900">Rp {{ number_format($workOrder->overhead_cost, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Total Biaya</p>
                    <p class="font-semibold text-gray-900">Rp {{ number_format($workOrder->total_cost, 0, ',', '.') }}</p>
                </div>
            </div>

            @if($workOrder->recipe)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs font-medium text-gray-500 mb-2">Resep: {{ $workOrder->recipe?->name }} (batch {{ $workOrder->recipe?->batch_size }} {{ $workOrder->recipe?->batch_unit }})</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($workOrder->recipe?->ingredients as $ing)
                    <span class="px-2 py-1 text-xs rounded-lg bg-gray-100 text-gray-700">
                        {{ $ing->product?->name }}: {{ $ing->quantity_per_batch }} {{ $ing->unit }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- BOM Info --}}
            @if($workOrder->bom)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-medium text-gray-500">
                        BOM: {{ $workOrder->bom?->name }} (batch {{ $workOrder->bom?->batch_size }} {{ $workOrder->bom?->batch_unit }})
                    </p>
                    <div class="flex items-center gap-2">
                        @if($workOrder->materials_consumed)
                            <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Material Dikonsumsi</span>
                        @elseif($workOrder->status === 'in_progress')
                            <form method="POST" action="{{ url('manufacturing') }}/{{ $workOrder->id }}/consume" onsubmit="return confirm('Konsumsi material dari stok sesuai BOM?')">
                                @csrf
                                <button type="submit" class="px-3 py-1 text-xs bg-amber-600 text-white rounded-xl hover:bg-amber-700">Konsumsi Material</button>
                            </form>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">Belum Dikonsumsi</span>
                        @endif
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($workOrder->bom?->lines as $line)
                    <span class="px-2 py-1 text-xs rounded-lg bg-gray-100 text-gray-700">
                        {{ $line->product?->name }}: {{ $line->quantity_per_batch }} {{ $line->unit }}
                        @if($line->childBom) <span class="text-purple-500">(sub-BOM)</span> @endif
                    </span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Journal Entry --}}
            @if($workOrder->journalEntry)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-500">
                    Jurnal Material: <a href="{{ url('accounting/journals') }}/{{ $workOrder->journalEntry?->id }}" class="text-blue-500 hover:underline">{{ $workOrder->journalEntry?->number }}</a>
                    <span class="ml-2 px-1.5 py-0.5 rounded text-xs {{ $workOrder->journalEntry?->status === 'posted' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $workOrder->journalEntry?->status }}</span>
                </p>
            </div>
            @endif
        </div>

        {{-- Catat Output (jika in_progress) --}}
        @if($workOrder->status === 'in_progress')
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Catat Output Produksi</h3>
            <form method="POST" action="{{ route('production.output', $workOrder) }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Qty Bagus *</label>
                        <input type="number" name="good_qty" required min="0" step="0.001"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Qty Reject</label>
                        <input type="number" name="reject_qty" min="0" step="0.001" value="0"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Alasan Reject</label>
                        <input type="text" name="reject_reason" placeholder="Opsional"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="auto_complete" value="1" class="rounded">
                        <span class="text-sm text-gray-700">Selesaikan WO & tambah stok otomatis</span>
                    </label>
                    <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Simpan Output</button>
                </div>
            </form>
        </div>
        @endif

        {{-- Routing Operations --}}
        @if($workOrder->operations->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Routing / Operasi</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-center">Seq</th>
                            <th class="px-4 py-3 text-left">Operasi</th>
                            <th class="px-4 py-3 text-left">Work Center</th>
                            <th class="px-4 py-3 text-right">Est. Jam</th>
                            <th class="px-4 py-3 text-right">Aktual Jam</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($workOrder->operations as $op)
                        @php
                            $opColors = ['pending'=>'amber','in_progress'=>'blue','completed'=>'green','skipped'=>'gray'];
                            $opLabels = ['pending'=>'Pending','in_progress'=>'Dikerjakan','completed'=>'Selesai','skipped'=>'Dilewati'];
                            $oc = $opColors[$op->status] ?? 'gray';
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-center font-mono text-xs text-gray-900">{{ $op->sequence }}</td>
                            <td class="px-4 py-3 text-gray-900">{{ $op->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $op->workCenter?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right text-gray-900">{{ $op->estimated_hours }}</td>
                            <td class="px-4 py-3 text-right text-gray-900">{{ $op->actual_hours ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $oc  }}-100 text-{{ $oc }}-700 $oc }}-500/20 $oc }}-400">
                                    {{ $opLabels[$op->status] ?? $op->status }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Riwayat Output --}}
        @if($workOrder->outputs->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Riwayat Output</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-right">Bagus</th>
                            <th class="px-4 py-3 text-right">Reject</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Alasan Reject</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($workOrder->outputs as $out)
                        <tr>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $out->created_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 text-right text-green-500 font-medium">{{ $out->good_qty + 0 }}</td>
                            <td class="px-4 py-3 text-right text-red-400">{{ $out->reject_qty + 0 }}</td>
                            <td class="px-4 py-3 text-right text-gray-900">{{ $out->output_qty + 0 }}</td>
                            <td class="px-4 py-3 hidden sm:table-cell text-xs text-gray-500">{{ $out->reject_reason ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
