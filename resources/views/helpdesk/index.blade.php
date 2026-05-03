<x-app-layout>
    <x-slot name="header">Helpdesk</x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Open</p>
            <p class="text-2xl font-bold text-blue-500">{{ $stats['open'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">In Progress</p>
            <p class="text-2xl font-bold text-amber-500">{{ $stats['in_progress'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Overdue SLA</p>
            <p class="text-2xl font-bold text-red-500">{{ $stats['overdue'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Resolved Bulan Ini</p>
            <p class="text-2xl font-bold text-green-500">{{ $stats['resolved_month'] }}</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari tiket..."
                class="flex-1 min-w-[120px] px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
                <option value="">Semua Status</option>
                @foreach(['open'=>'Open','in_progress'=>'In Progress','waiting'=>'Waiting','resolved'=>'Resolved','closed'=>'Closed'] as $v=>$l)
                <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <select name="priority" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
                <option value="">Semua Prioritas</option>
                @foreach(['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent'] as $v=>$l)
                <option value="{{ $v }}" @selected(request('priority')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
        </form>
        <div class="flex gap-2">
            <a href="{{ route('helpdesk.kb') }}" class="px-3 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Knowledge Base</a>
            @canmodule('helpdesk', 'create')
            <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Tiket</button>
            @endcanmodule
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Tiket</th>
                        <th class="px-4 py-3 text-left">Subjek</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Customer</th>
                        <th class="px-4 py-3 text-center">Prioritas</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Assigned</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center hidden md:table-cell">SLA</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tickets as $t)
                    @php
                        $pc = ['low'=>'gray','medium'=>'blue','high'=>'amber','urgent'=>'red'][$t->priority] ?? 'gray';
                        $sc = ['open'=>'blue','in_progress'=>'amber','waiting'=>'purple','resolved'=>'green','closed'=>'gray'][$t->status] ?? 'gray';
                        $sl = ['open'=>'Open','in_progress'=>'Progress','waiting'=>'Waiting','resolved'=>'Resolved','closed'=>'Closed'][$t->status] ?? $t->status;
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $t->isOverdue() ? 'bg-red-50/50' : '' }}">
                        <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900">
                            <a href="{{ route('helpdesk.show', $t) }}" class="hover:text-blue-500">{{ $t->ticket_number }}</a>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ Str::limit($t->subject, 35) }}</td>
                        <td class="px-4 py-3 hidden sm:table-cell text-xs text-gray-500">{{ $t->customer->name ?? $t->contact_name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs bg-{{ $pc }}-100 text-{{ $pc }}-700 $pc }}-500/20 $pc }}-400">{{ ucfirst($t->priority) }}</span></td>
                        <td class="px-4 py-3 text-center hidden sm:table-cell text-xs text-gray-500">{{ $t->assignee->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs bg-{{ $sc }}-100 text-{{ $sc }}-700 $sc }}-500/20 $sc }}-400">{{ $sl }}</span></td>
                        <td class="px-4 py-3 text-center hidden md:table-cell">
                            @if($t->isOverdue())<span class="text-red-500 text-xs">⏰ Overdue</span>
                            @elseif($t->sla_resolve_met === true)<span class="text-green-500 text-xs">✅</span>
                            @elseif($t->sla_resolve_met === false)<span class="text-red-500 text-xs">❌</span>
                            @else<span class="text-gray-400 text-xs">—</span>@endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('helpdesk.show', $t) }}" class="text-xs px-2 py-1 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">Belum ada tiket.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())<div class="px-4 py-3 border-t border-gray-100">{{ $tickets->links() }}</div>@endif
    </div>

    {{-- Modal Create Ticket --}}
    <div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Buat Tiket</h3>
                <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('helpdesk.store') }}" class="p-6 space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900'; @endphp
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Subjek *</label><input type="text" name="subject" required class="{{ $cls }}"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi *</label><textarea name="description" required rows="3" class="{{ $cls }}"></textarea></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Customer</label>
                        <select name="customer_id" class="{{ $cls }}"><option value="">-- Pilih --</option>
                            @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Prioritas *</label>
                        <select name="priority" required class="{{ $cls }}">
                            <option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Kategori *</label>
                        <select name="category" required class="{{ $cls }}">
                            <option value="general">Umum</option><option value="billing">Billing</option><option value="technical">Teknis</option><option value="delivery">Pengiriman</option><option value="product">Produk</option><option value="complaint">Komplain</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Assign ke</label>
                        <select name="assigned_to" class="{{ $cls }}"><option value="">-- Auto --</option>
                            @foreach($agents as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Nama Kontak</label><input type="text" name="contact_name" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Email Kontak</label><input type="email" name="contact_email" class="{{ $cls }}"></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat Tiket</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
