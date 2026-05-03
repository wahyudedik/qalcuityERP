<x-app-layout>
    <x-slot name="header">Tiket — {{ $helpdeskTicket->ticket_number }}</x-slot>

    @php $t = $helpdeskTicket; @endphp
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Ticket info --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ $t->subject }}</h2>
                        <p class="text-xs text-gray-500 mt-1">{{ $t->ticket_number }} · {{ $t->created_at->format('d/m/Y H:i') }} · oleh {{ $t->creator->name ?? '-' }}</p>
                    </div>
                    @php
                        $pc = ['low'=>'gray','medium'=>'blue','high'=>'amber','urgent'=>'red'][$t->priority] ?? 'gray';
                        $sc = ['open'=>'blue','in_progress'=>'amber','waiting'=>'purple','resolved'=>'green','closed'=>'gray'][$t->status] ?? 'gray';
                    @endphp
                    <div class="flex gap-2">
                        <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $pc }}-100 text-{{ $pc }}-700 $pc }}-500/20 $pc }}-400">{{ ucfirst($t->priority) }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $sc }}-100 text-{{ $sc }}-700 $sc }}-500/20 $sc }}-400">{{ ucfirst(str_replace('_', ' ', $t->status)) }}</span>
                    </div>
                </div>
                <div class="prose prose-sm max-w-none text-gray-700">
                    {!! nl2br(e($t->description)) !!}
                </div>
            </div>

            {{-- Replies --}}
            <div class="space-y-3">
                @foreach($t->replies->sortBy('created_at') as $reply)
                <div class="bg-white rounded-2xl border {{ $reply->is_internal ? 'border-amber-200' : 'border-gray-200' }} p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-900">{{ $reply->user->name ?? 'System' }}</span>
                        <div class="flex items-center gap-2">
                            @if($reply->is_internal)<span class="text-xs text-amber-500">🔒 Internal</span>@endif
                            <span class="text-xs text-gray-400">{{ $reply->created_at->format('d/m H:i') }}</span>
                        </div>
                    </div>
                    <div class="text-sm text-gray-700">{!! nl2br(e($reply->body)) !!}</div>
                </div>
                @endforeach
            </div>

            {{-- Reply form --}}
            @if(!in_array($t->status, ['closed']))
            @canmodule('helpdesk', 'create')
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-3">Balas</h3>
                <form method="POST" action="{{ route('helpdesk.reply', $t) }}" class="space-y-3">
                    @csrf
                    <textarea name="body" required rows="3" placeholder="Tulis balasan..."
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900"></textarea>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_internal" value="1" class="rounded">
                            <span class="text-xs text-gray-500">Internal note (tidak terlihat customer)</span>
                        </label>
                        <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Kirim</button>
                    </div>
                </form>
            </div>
            @endcanmodule
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Status & Assignment --}}
            @canmodule('helpdesk', 'edit')
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-3">Kelola Tiket</h4>
                <form method="POST" action="{{ route('helpdesk.status', $t) }}" class="space-y-3">
                    @csrf @method('PATCH')
                    <div><label class="block text-xs text-gray-500 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                            @foreach(['open'=>'Open','in_progress'=>'In Progress','waiting'=>'Waiting','resolved'=>'Resolved','closed'=>'Closed'] as $v=>$l)
                            <option value="{{ $v }}" @selected($t->status===$v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div><label class="block text-xs text-gray-500 mb-1">Assign ke</label>
                        <select name="assigned_to" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                            <option value="">-- Unassigned --</option>
                            @foreach($agents as $a)<option value="{{ $a->id }}" @selected($t->assigned_to==$a->id)>{{ $a->name }}</option>@endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full px-3 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Update</button>
                </form>
            </div>
            @endcanmodule

            {{-- Details --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-4 space-y-2 text-sm">
                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">Detail</h4>
                <div><span class="text-gray-500">Customer:</span> <span class="text-gray-900">{{ $t->customer->name ?? $t->contact_name ?? '-' }}</span></div>
                <div><span class="text-gray-500">Email:</span> <span class="text-gray-900">{{ $t->contact_email ?? $t->customer->email ?? '-' }}</span></div>
                <div><span class="text-gray-500">Kategori:</span> <span class="text-gray-900">{{ ucfirst($t->category) }}</span></div>
                @if($t->contract)<div><span class="text-gray-500">Kontrak:</span> <a href="{{ route('contracts.show', $t->contract) }}" class="text-blue-500 hover:underline">{{ $t->contract->contract_number }}</a></div>@endif
            </div>

            {{-- SLA --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-4 space-y-2 text-sm">
                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">SLA</h4>
                <div class="flex justify-between"><span class="text-gray-500">Response Due:</span>
                    <span class="{{ $t->sla_response_due && $t->sla_response_due->isPast() && !$t->first_responded_at ? 'text-red-500' : 'text-gray-900' }}">{{ $t->sla_response_due?->format('d/m H:i') ?? '-' }}</span>
                </div>
                <div class="flex justify-between"><span class="text-gray-500">First Response:</span>
                    <span class="text-gray-900">{{ $t->first_responded_at?->format('d/m H:i') ?? '-' }}
                        @if($t->sla_response_met === true) ✅ @elseif($t->sla_response_met === false) ❌ @endif
                    </span>
                </div>
                <div class="flex justify-between"><span class="text-gray-500">Resolve Due:</span>
                    <span class="{{ $t->isOverdue() ? 'text-red-500 font-semibold' : 'text-gray-900' }}">{{ $t->sla_resolve_due?->format('d/m H:i') ?? '-' }}</span>
                </div>
                <div class="flex justify-between"><span class="text-gray-500">Resolved:</span>
                    <span class="text-gray-900">{{ $t->resolved_at?->format('d/m H:i') ?? '-' }}
                        @if($t->sla_resolve_met === true) ✅ @elseif($t->sla_resolve_met === false) ❌ @endif
                    </span>
                </div>
            </div>

            {{-- Suggested KB Articles --}}
            @if($kbArticles->isNotEmpty())
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">Artikel Terkait</h4>
                <div class="space-y-1">
                    @foreach($kbArticles as $kb)
                    <a href="#" class="block text-sm text-blue-500 hover:underline">📄 {{ $kb->title }}</a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Rating --}}
            @if($t->status === 'resolved' || $t->status === 'closed')
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">Kepuasan</h4>
                @if($t->satisfaction_rating)
                <p class="text-2xl">{{ str_repeat('⭐', (int) $t->satisfaction_rating) }}</p>
                @else
                <form method="POST" action="{{ route('helpdesk.rate', $t) }}" class="flex items-center gap-2">
                    @csrf @method('PATCH')
                    <select name="satisfaction_rating" class="px-2 py-1 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900">
                        @for($i=1;$i<=5;$i++)<option value="{{ $i }}">{{ $i }} ⭐</option>@endfor
                    </select>
                    <button type="submit" class="px-3 py-1 text-xs bg-blue-600 text-white rounded-lg">Rate</button>
                </form>
                @endif
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
