<x-app-layout>
    <x-slot name="header">Persetujuan</x-slot>

    <div class="space-y-6">

        {{-- Header actions --}}
        <div class="flex items-center justify-between">
            <div></div>
            <a href="{{ route('approvals.workflows') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Kelola Workflow
            </a>
        </div>

        {{-- Pending --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Menunggu Persetujuan</h2>
                <span class="text-xs bg-amber-500/20 text-amber-300 px-2 py-1 rounded-full font-medium">{{ $pending->count() }} pending</span>
            </div>

            @if($pending->isEmpty())
                <div class="px-6 py-10 text-center text-gray-400 text-sm">Tidak ada permintaan yang menunggu persetujuan.</div>
            @else
            <div class="divide-y divide-gray-100">
                @foreach($pending as $req)
                <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 text-sm">{{ $req->workflow?->name ?? 'Permintaan Persetujuan' }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Diminta oleh <span class="font-medium text-gray-700">{{ $req->requester?->name }}</span>
                            · {{ $req->created_at->diffForHumans() }}
                        </p>
                        @if($req->amount)
                        <p class="text-xs text-blue-400 font-medium mt-1">Rp {{ number_format($req->amount, 0, ',', '.') }}</p>
                        @endif
                        @if($req->notes)
                        <p class="text-xs text-gray-400 mt-1 italic">{{ $req->notes }}</p>
                        @endif
                        {{-- Chain visualization --}}
                        @if($req->workflow?->approver_roles)
                        <div class="flex items-center gap-1 mt-2 flex-wrap">
                            @foreach($req->workflow->approver_roles as $i => $role)
                            @if($i > 0)
                            <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            @endif
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                {{ in_array($role, ['admin','manager']) ? 'bg-blue-500/20 text-blue-400' : 'bg-gray-500/20 text-gray-400' }}">
                                {{ ucfirst($role) }}
                            </span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    <div class="flex gap-2 sm:shrink-0">
                        <form method="POST" action="{{ route('approvals.approve', $req) }}">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 bg-green-600 hover:bg-green-500 text-gray-900 text-xs font-medium rounded-lg transition">
                                Setujui
                            </button>
                        </form>
                        <button onclick="showRejectModal({{ $req->id }})"
                            class="px-3 py-1.5 bg-red-500/20 hover:bg-red-500/30 text-red-400 text-xs font-medium rounded-lg transition">
                            Tolak
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- History --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">Riwayat Persetujuan</h2>
            </div>
            @if($history->isEmpty())
                <div class="px-6 py-8 text-center text-gray-400 text-sm">Belum ada riwayat.</div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Permintaan</th>
                            <th class="px-6 py-3 text-left hidden sm:table-cell">Pemohon</th>
                            <th class="px-6 py-3 text-left hidden md:table-cell">Diproses oleh</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left hidden sm:table-cell">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($history as $req)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $req->workflow?->name ?? '-' }}</td>
                            <td class="px-6 py-3 text-gray-500 hidden sm:table-cell">{{ $req->requester?->name }}</td>
                            <td class="px-6 py-3 text-gray-500 hidden md:table-cell">{{ $req->approver?->name ?? '-' }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $req->status === 'approved' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                                    {{ $req->status === 'approved' ? 'Disetujui' : 'Ditolak' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-gray-400 hidden sm:table-cell">{{ $req->responded_at?->format('d M Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- Reject Modal --}}
    <div id="reject-modal" class="fixed inset-0 bg-black/70 z-50 hidden items-center justify-center">
        <div class="bg-white border border-gray-200 rounded-2xl w-full max-w-sm mx-4 p-6 shadow-2xl">
            <h3 class="font-semibold text-gray-900 mb-4">Alasan Penolakan</h3>
            <form id="reject-form" method="POST">
                @csrf
                <textarea name="reason" rows="3" required placeholder="Masukkan alasan penolakan..."
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 placeholder-slate-600 focus:outline-none focus:border-red-500 resize-none"></textarea>
                <div class="flex gap-2 mt-4">
                    <button type="button" onclick="closeRejectModal()"
                        class="flex-1 py-2 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100 transition">Batal</button>
                    <button type="submit"
                        class="flex-1 py-2 bg-red-600 text-gray-900 rounded-xl text-sm font-medium hover:bg-red-500 transition">Tolak</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function showRejectModal(id) {
        document.getElementById('reject-form').action = '{{ url("approvals") }}/' + id + '/reject';
        document.getElementById('reject-modal').classList.remove('hidden');
        document.getElementById('reject-modal').classList.add('flex');
    }
    function closeRejectModal() {
        document.getElementById('reject-modal').classList.add('hidden');
        document.getElementById('reject-modal').classList.remove('flex');
    }
    </script>
    @endpush
</x-app-layout>
