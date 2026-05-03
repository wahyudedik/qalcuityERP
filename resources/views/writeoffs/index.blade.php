<x-app-layout>
    <x-slot name="header">Write-off Hutang / Piutang</x-slot>

    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-6">
        <form method="GET" class="flex gap-2 flex-wrap">
            <select name="type" onchange="this.form.submit()" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Tipe</option>
                <option value="receivable" {{ request('type') === 'receivable' ? 'selected' : '' }}>Piutang</option>
                <option value="payable" {{ request('type') === 'payable' ? 'selected' : '' }}>Hutang</option>
            </select>
            <select name="status" onchange="this.form.submit()" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                <option value="posted" {{ request('status') === 'posted' ? 'selected' : '' }}>Diposting</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
            </select>
        </form>
        <div class="ml-auto flex gap-2">
            <a href="{{ route('writeoffs.create', ['type' => 'receivable']) }}" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Write-off Piutang</a>
            <a href="{{ route('writeoffs.create', ['type' => 'payable']) }}" class="px-4 py-2 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700">+ Write-off Hutang</a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nomor</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Tipe</th>
                        <th class="px-4 py-3 text-left">Referensi</th>
                        <th class="px-4 py-3 text-right">Jumlah Write-off</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Alasan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($writeoffs as $wo)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $wo->number }}</p>
                            <p class="text-xs text-gray-400">{{ $wo->created_at->format('d M Y') }}</p>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $wo->type === 'receivable' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                {{ $wo->typeLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $wo->reference_number }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-red-600">Rp {{ number_format($wo->writeoff_amount,0,',','.') }}</td>
                        <td class="px-4 py-3 hidden md:table-cell text-gray-500 max-w-xs truncate">{{ $wo->reason }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $wo->statusColor() }}">{{ ucfirst($wo->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                @if($wo->isPending() && in_array(auth()->user()->role, ['admin', 'manager']))
                                <form method="POST" action="{{ route('writeoffs.approve', $wo) }}">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700">Setujui</button>
                                </form>
                                <button onclick="openReject({{ $wo->id }})" class="px-2 py-1 text-xs bg-red-600 text-white rounded-lg hover:bg-red-700">Tolak</button>
                                @endif
                                @if($wo->isApproved() && in_array(auth()->user()->role, ['admin', 'manager']))
                                <form method="POST" action="{{ route('writeoffs.post', $wo) }}" onsubmit="return confirm('Post jurnal write-off ini?')">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700">Post Jurnal</button>
                                </form>
                                @endif
                                @if($wo->journalEntry)
                                <a href="{{ route('journals.show', $wo->journalEntry) }}" class="px-2 py-1 text-xs border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50">Jurnal</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">Belum ada data write-off.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($writeoffs->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $writeoffs->links() }}</div>
        @endif
    </div>

    {{-- Modal Reject --}}
    <div id="modal-reject" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Tolak Write-off</h3>
                <button onclick="document.getElementById('modal-reject').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-reject" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Alasan Penolakan *</label>
                    <textarea name="reason" required rows="3" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-reject').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700">Tolak</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openReject(id) {
        document.getElementById('form-reject').action = '{{ url("writeoffs") }}/' + id + '/reject';
        document.getElementById('modal-reject').classList.remove('hidden');
    }
    </script>
    @endpush
</x-app-layout>
