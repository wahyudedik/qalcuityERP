<x-app-layout>
    <x-slot name="header">Project Billing — {{ $project->name }}</x-slot>

    @php $config = $project->billingConfig; @endphp
    <div class="space-y-6">
        {{-- Summary --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Tipe Billing</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ ['time_material'=>'Time & Material','milestone'=>'Milestone','retainer'=>'Retainer','fixed_price'=>'Fixed Price'][$config->billing_type ?? ''] ?? 'Belum diset' }}</p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Unbilled Hours</p>
                <p class="text-xl font-bold text-amber-500">{{ number_format($unbilledHours, 1) }}h</p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Unbilled Amount</p>
                <p class="text-lg font-bold text-amber-500">Rp {{ number_format($unbilledAmount, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Billed</p>
                <p class="text-lg font-bold text-blue-500">Rp {{ number_format($totalBilled, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Paid</p>
                <p class="text-lg font-bold text-green-500">Rp {{ number_format($totalPaid, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Billing Config --}}
            @canmodule('project_billing', 'edit')
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Konfigurasi Billing</h3>
                <form method="POST" action="{{ route('project-billing.config', $project) }}" class="space-y-3">
                    @csrf
                    @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe *</label>
                            <select name="billing_type" required class="{{ $cls }}">
                                @foreach(['time_material'=>'Time & Material','milestone'=>'Milestone','retainer'=>'Retainer','fixed_price'=>'Fixed Price'] as $v=>$l)
                                <option value="{{ $v }}" @selected(($config->billing_type ?? '')===$v)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Hourly Rate</label>
                            <input type="number" name="hourly_rate" min="0" step="1000" value="{{ $config->hourly_rate ?? 0 }}" class="{{ $cls }}">
                        </div>
                        <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Retainer/Bulan</label>
                            <input type="number" name="retainer_amount" min="0" step="100000" value="{{ $config->retainer_amount ?? 0 }}" class="{{ $cls }}">
                        </div>
                        <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Next Billing</label>
                            <input type="date" name="next_billing_date" value="{{ $config->next_billing_date?->format('Y-m-d') ?? '' }}" class="{{ $cls }}">
                        </div>
                    </div>
                    <button type="submit" class="w-full px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan Config</button>
                </form>
            </div>
            @endcanmodule

            {{-- Generate Invoice --}}
            @canmodule('project_billing', 'create')
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Generate Invoice</h3>
                @if(($config->billing_type ?? '') === 'time_material' || !$config)
                <form method="POST" action="{{ route('project-billing.time-material', $project) }}" class="space-y-3 mb-4">
                    @csrf
                    @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp
                    <p class="text-sm text-gray-500 dark:text-slate-400">Time & Material — {{ number_format($unbilledHours, 1) }}h unbilled</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Dari</label><input type="date" name="period_start" required class="{{ $cls }}"></div>
                        <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Sampai</label><input type="date" name="period_end" required value="{{ date('Y-m-d') }}" class="{{ $cls }}"></div>
                    </div>
                    <button type="submit" class="w-full px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Generate T&M Invoice</button>
                </form>
                @endif
                @if(($config->billing_type ?? '') === 'retainer')
                <form method="POST" action="{{ route('project-billing.retainer', $project) }}">
                    @csrf
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-2">Retainer: Rp {{ number_format($config->retainer_amount ?? 0, 0, ',', '.') }} / {{ $config->retainer_cycle ?? 'monthly' }}</p>
                    <button type="submit" class="w-full px-4 py-2 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700">Generate Retainer Invoice</button>
                </form>
                @endif
            </div>
            @endcanmodule
        </div>

        {{-- Milestones --}}
        @if(($config->billing_type ?? '') === 'milestone')
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-white">Milestones</h3>
                @canmodule('project_billing', 'create')
                <button onclick="document.getElementById('modal-ms').classList.remove('hidden')" class="text-xs px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700">+ Milestone</button>
                @endcanmodule
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr><th class="px-4 py-3 text-left">Milestone</th><th class="px-4 py-3 text-right">Nilai</th><th class="px-4 py-3 text-center">Due</th><th class="px-4 py-3 text-center">Status</th><th class="px-4 py-3 text-center">Aksi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach($project->milestones as $ms)
                        @php $mc = ['pending'=>'gray','completed'=>'amber','invoiced'=>'green'][$ms->status] ?? 'gray'; @endphp
                        <tr>
                            <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $ms->name }}</td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">Rp {{ number_format($ms->amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $ms->due_date?->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs bg-{{ $mc }}-100 text-{{ $mc }}-700 dark:bg-{{ $mc }}-500/20 dark:text-{{ $mc }}-400">{{ ucfirst($ms->status) }}</span></td>
                            <td class="px-4 py-3 text-center">
                                @canmodule('project_billing', 'edit')
                                @if($ms->status === 'pending')
                                <form method="POST" action="{{ route('project-billing.milestones.complete', $ms) }}" class="inline">@csrf @method('PATCH')
                                    <button type="submit" class="text-xs px-2 py-1 bg-amber-600 text-white rounded-lg">Complete</button>
                                </form>
                                @elseif($ms->status === 'completed')
                                <form method="POST" action="{{ route('project-billing.milestones.invoice', $ms) }}" class="inline">@csrf
                                    <button type="submit" class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg">Invoice</button>
                                </form>
                                @endif
                                @endcanmodule
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Unbilled Timesheets --}}
        @if($unbilledTimesheets->isNotEmpty())
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Timesheet Unbilled</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr><th class="px-4 py-3 text-left">Tanggal</th><th class="px-4 py-3 text-left">Staff</th><th class="px-4 py-3 text-left">Deskripsi</th><th class="px-4 py-3 text-right">Jam</th><th class="px-4 py-3 text-right">Rate</th><th class="px-4 py-3 text-right">Total</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach($unbilledTimesheets as $ts)
                        <tr>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $ts->date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $ts->user->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-500 dark:text-slate-400 text-xs">{{ Str::limit($ts->description, 40) }}</td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ $ts->hours }}h</td>
                            <td class="px-4 py-3 text-right text-gray-500">Rp {{ number_format($ts->hourly_rate ?: ($config->hourly_rate ?? 0), 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp {{ number_format($ts->laborCost(), 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Invoice History --}}
        @if($project->projectInvoices->isNotEmpty())
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Riwayat Invoice</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr><th class="px-4 py-3 text-left">Invoice</th><th class="px-4 py-3 text-center">Tipe</th><th class="px-4 py-3 text-center">Periode</th><th class="px-4 py-3 text-right">Jumlah</th><th class="px-4 py-3 text-center">Status</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach($project->projectInvoices->sortByDesc('created_at') as $pi)
                        @php $ic = ['draft'=>'gray','invoiced'=>'blue','paid'=>'green'][$pi->status] ?? 'gray'; @endphp
                        <tr>
                            <td class="px-4 py-3 text-gray-900 dark:text-white font-mono text-xs">{{ $pi->invoice->number ?? '-' }}</td>
                            <td class="px-4 py-3 text-center text-xs text-gray-500">{{ ['time_material'=>'T&M','milestone'=>'Milestone','retainer'=>'Retainer','fixed_price'=>'Fixed'][$pi->billing_type] ?? $pi->billing_type }}</td>
                            <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $pi->period_start?->format('d/m') ?? '' }}{{ $pi->period_end ? ' — ' . $pi->period_end->format('d/m/Y') : '' }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp {{ number_format($pi->total_amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs bg-{{ $ic }}-100 text-{{ $ic }}-700 dark:bg-{{ $ic }}-500/20 dark:text-{{ $ic }}-400">{{ ucfirst($pi->status) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- Modal Add Milestone --}}
    <div id="modal-ms" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Tambah Milestone</h3>
                <button onclick="document.getElementById('modal-ms').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('project-billing.milestones.store', $project) }}" class="p-6 space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama *</label><input type="text" name="name" required class="{{ $cls }}"></div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nilai (Rp) *</label><input type="number" name="amount" required min="0" step="1000" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Due Date</label><input type="date" name="due_date" class="{{ $cls }}"></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-ms').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
