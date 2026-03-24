<x-app-layout>
    <x-slot name="header">Cicilan Invoice {{ $invoice->number }}</x-slot>

    <div class="max-w-3xl mx-auto space-y-5">

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 text-sm">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif

        {{-- Invoice Info --}}
        <div class="bg-white/5 border border-white/10 rounded-xl p-5 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <div class="text-gray-400 text-xs mb-1">Invoice</div>
                <div class="text-white font-mono font-semibold">{{ $invoice->number }}</div>
            </div>
            <div>
                <div class="text-gray-400 text-xs mb-1">Customer</div>
                <div class="text-white">{{ $invoice->customer?->name }}</div>
            </div>
            <div>
                <div class="text-gray-400 text-xs mb-1">Total</div>
                <div class="text-white font-semibold">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</div>
            </div>
            <div>
                <div class="text-gray-400 text-xs mb-1">Sisa</div>
                <div class="text-red-400 font-semibold">Rp {{ number_format($invoice->remaining_amount, 0, ',', '.') }}</div>
            </div>
        </div>

        {{-- Existing Installments --}}
        @if($invoice->installments->count() > 0)
        <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-white font-semibold text-sm">Jadwal Cicilan</h3>
                <span class="text-xs text-gray-400">{{ $invoice->installments->count() }} cicilan</span>
            </div>
            <table class="w-full text-sm text-gray-300">
                <thead class="bg-white/5 text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-right">Sudah Bayar</th>
                        <th class="px-4 py-3 text-right">Sisa</th>
                        <th class="px-4 py-3 text-left">Jatuh Tempo</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($invoice->installments as $inst)
                    @php $overdue = $inst->status !== 'paid' && $inst->due_date < today(); @endphp
                    <tr class="hover:bg-white/5 {{ $overdue ? 'bg-red-900/10' : '' }}">
                        <td class="px-4 py-3 font-semibold text-white">{{ $inst->installment_number }}</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format($inst->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-green-400">Rp {{ number_format($inst->paid_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right {{ $inst->remaining() > 0 ? 'text-red-400' : 'text-gray-500' }}">
                            Rp {{ number_format($inst->remaining(), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 {{ $overdue ? 'text-red-400 font-semibold' : '' }}">
                            {{ $inst->due_date->format('d M Y') }}
                            @if($overdue) <span class="text-xs">(Terlambat)</span> @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded text-xs
                                {{ $inst->status === 'paid' ? 'bg-green-500/20 text-green-400' : '' }}
                                {{ $inst->status === 'partial' ? 'bg-yellow-500/20 text-yellow-400' : '' }}
                                {{ $inst->status === 'unpaid' ? 'bg-red-500/20 text-red-400' : '' }}">
                                {{ ['paid' => 'Lunas', 'partial' => 'Sebagian', 'unpaid' => 'Belum Bayar'][$inst->status] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($inst->status !== 'paid')
                            <button onclick="openPayModal('{{ $inst->id }}', {{ $inst->remaining() }})"
                                class="text-xs px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded-lg">Bayar</button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Setup Installments Form --}}
        <div class="bg-white/5 border border-white/10 rounded-xl p-5 space-y-4">
            <h3 class="text-white font-semibold text-sm">
                {{ $invoice->installments->count() > 0 ? 'Ubah Jadwal Cicilan' : 'Buat Jadwal Cicilan' }}
            </h3>

            <form method="POST" action="{{ route('receivables.installments.store', $invoice) }}" id="installment-form" class="space-y-4">
                @csrf

                <div id="installment-lines" class="space-y-2">
                    @if($invoice->installments->count() > 0)
                        @foreach($invoice->installments as $i => $inst)
                        <div class="installment-row grid grid-cols-12 gap-2 items-center">
                            <div class="col-span-1 text-center text-gray-400 text-sm font-semibold">{{ $i + 1 }}</div>
                            <div class="col-span-4">
                                <input type="number" name="installments[{{ $i }}][amount]" value="{{ $inst->amount }}"
                                    placeholder="Jumlah" min="1" step="1" required
                                    class="amount-input w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            </div>
                            <div class="col-span-4">
                                <input type="date" name="installments[{{ $i }}][due_date]" value="{{ $inst->due_date->format('Y-m-d') }}" required
                                    class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            </div>
                            <div class="col-span-2">
                                <input type="text" name="installments[{{ $i }}][notes]" value="{{ $inst->notes }}" placeholder="Ket."
                                    class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            </div>
                            <div class="col-span-1 text-center">
                                <button type="button" onclick="this.closest('.installment-row').remove(); updateTotal();"
                                    class="text-red-400 hover:text-red-300 text-lg">×</button>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="installment-row grid grid-cols-12 gap-2 items-center">
                            <div class="col-span-1 text-center text-gray-400 text-sm font-semibold">1</div>
                            <div class="col-span-4">
                                <input type="number" name="installments[0][amount]" placeholder="Jumlah" min="1" step="1" required
                                    class="amount-input w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            </div>
                            <div class="col-span-4">
                                <input type="date" name="installments[0][due_date]" required
                                    class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            </div>
                            <div class="col-span-2">
                                <input type="text" name="installments[0][notes]" placeholder="Ket."
                                    class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            </div>
                            <div class="col-span-1"></div>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-between">
                    <button type="button" id="add-installment" class="text-indigo-400 hover:text-indigo-300 text-sm">+ Tambah Cicilan</button>
                    <div class="text-sm text-gray-400">
                        Total cicilan: <span id="installment-total" class="text-white font-mono font-semibold">0</span>
                        / <span class="text-indigo-400">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm">Simpan Jadwal</button>
                    <a href="{{ route('receivables.index') }}" class="bg-white/10 hover:bg-white/20 text-white px-5 py-2 rounded-lg text-sm">Kembali</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Bayar Cicilan --}}
    <div id="modal-pay-inst" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-sm p-6">
            <h3 class="text-white font-semibold mb-4">Bayar Cicilan</h3>
            <form id="form-pay-inst" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs text-gray-400 mb-1 block">Jumlah Bayar *</label>
                    <input type="number" name="amount" id="inst-amount" required min="1" step="1"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="text-xs text-gray-400 mb-1 block">Metode</label>
                    <select name="method" class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                        <option value="transfer">Transfer Bank</option>
                        <option value="cash">Cash</option>
                        <option value="qris">QRIS</option>
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg text-sm">Bayar</button>
                    <button type="button" onclick="document.getElementById('modal-pay-inst').classList.add('hidden')"
                        class="flex-1 bg-white/10 hover:bg-white/20 text-white py-2 rounded-lg text-sm">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    const invoiceTotal = {{ $invoice->total_amount }};
    let lineCount = {{ max($invoice->installments->count(), 1) }};

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.amount-input').forEach(i => total += parseFloat(i.value || 0));
        document.getElementById('installment-total').textContent = 'Rp ' + total.toLocaleString('id-ID');
    }

    document.addEventListener('input', e => {
        if (e.target.classList.contains('amount-input')) updateTotal();
    });

    document.getElementById('add-installment').addEventListener('click', () => {
        const idx = lineCount++;
        const div = document.createElement('div');
        div.className = 'installment-row grid grid-cols-12 gap-2 items-center';
        div.innerHTML = `
            <div class="col-span-1 text-center text-gray-400 text-sm font-semibold">${idx + 1}</div>
            <div class="col-span-4">
                <input type="number" name="installments[${idx}][amount]" placeholder="Jumlah" min="1" step="1" required
                    class="amount-input w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>
            <div class="col-span-4">
                <input type="date" name="installments[${idx}][due_date]" required
                    class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>
            <div class="col-span-2">
                <input type="text" name="installments[${idx}][notes]" placeholder="Ket."
                    class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>
            <div class="col-span-1 text-center">
                <button type="button" onclick="this.closest('.installment-row').remove(); updateTotal();"
                    class="text-red-400 hover:text-red-300 text-lg">×</button>
            </div>`;
        document.getElementById('installment-lines').appendChild(div);
    });

    function openPayModal(id, remaining) {
        document.getElementById('inst-amount').value = remaining;
        document.getElementById('inst-amount').max = remaining;
        document.getElementById('form-pay-inst').action = '{{ url("receivables/installment") }}/' + id + '/pay';
        document.getElementById('modal-pay-inst').classList.remove('hidden');
    }

    updateTotal();
    </script>
</x-app-layout>
