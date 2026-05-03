<x-app-layout>
    <x-slot name="title">Manajemen Cuti — Qalcuity ERP</x-slot>
    <x-slot name="header">Manajemen Cuti</x-slot>
    <x-slot name="pageHeader">
        <button onclick="document.getElementById('modal-add-leave').classList.remove('hidden')"
            class="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Ajukan Cuti
        </button>
    </x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Menunggu Persetujuan</p>
            <p class="text-2xl font-bold text-amber-500 mt-1">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Disetujui ({{ now()->year }})</p>
            <p class="text-2xl font-bold text-green-500 mt-1">{{ $stats['approved'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Ditolak ({{ now()->year }})</p>
            <p class="text-2xl font-bold text-red-500 mt-1">{{ $stats['rejected'] }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="employee_id" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                <option value="">Semua Karyawan</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" @selected(request('employee_id') == $emp->id)>{{ $emp->name }}</option>
                @endforeach
            </select>
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                <option value="">Semua Status</option>
                <option value="pending" @selected(request('status')==='pending')>Pending</option>
                <option value="approved" @selected(request('status')==='approved')>Disetujui</option>
                <option value="rejected" @selected(request('status')==='rejected')>Ditolak</option>
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
            <a href="{{ route('hrm.leave') }}" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Reset</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Karyawan</th>
                        <th class="px-4 py-3 text-left">Jenis</th>
                        <th class="px-4 py-3 text-left">Periode</th>
                        <th class="px-4 py-3 text-center">Hari</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($leaves as $leave)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $leave->employee->name }}</p>
                            <p class="text-xs text-gray-500">{{ $leave->employee->position ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $leave->typeLabel() }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            {{ $leave->start_date->format('d M Y') }} — {{ $leave->end_date->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-gray-900">{{ $leave->days }}</td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $badge = match($leave->status) {
                                    'approved' => 'bg-green-100 text-green-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    default    => 'bg-amber-100 text-amber-700',
                                };
                                $label = match($leave->status) {
                                    'approved' => 'Disetujui', 'rejected' => 'Ditolak', default => 'Pending',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $badge }}">{{ $label }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                @if($leave->status === 'pending')
                                <button onclick="openApprove({{ $leave->id }}, '{{ addslashes($leave->employee->name) }}')"
                                    class="px-2 py-1 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700">Proses</button>
                                <form method="POST" action="{{ route('hrm.leave.destroy', $leave) }}"
                                      onsubmit="return confirm('Hapus pengajuan ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @else
                                <span class="text-xs text-gray-400">{{ $leave->approved_at?->format('d M Y') ?? '-' }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">Belum ada pengajuan cuti.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($leaves->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $leaves->links() }}</div>
        @endif
    </div>

    {{-- Modal Ajukan Cuti --}}
    <div id="modal-add-leave" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Ajukan Cuti</h3>
                <button onclick="document.getElementById('modal-add-leave').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('hrm.leave.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Karyawan *</label>
                    <select name="employee_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        <option value="">Pilih karyawan...</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->position ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jenis Cuti *</label>
                    <select name="type" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        <option value="annual">Cuti Tahunan</option>
                        <option value="sick">Sakit</option>
                        <option value="maternity">Cuti Melahirkan</option>
                        <option value="paternity">Cuti Ayah</option>
                        <option value="unpaid">Cuti Tanpa Gaji</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Mulai *</label>
                        <input type="date" name="start_date" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Selesai *</label>
                        <input type="date" name="end_date" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Alasan</label>
                    <textarea name="reason" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-leave').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Ajukan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Proses Cuti --}}
    <div id="modal-approve-leave" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Proses Pengajuan Cuti</h3>
                <button onclick="document.getElementById('modal-approve-leave').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-approve-leave" method="POST" class="p-6 space-y-4">
                @csrf @method('PATCH')
                <p id="approve-emp-name" class="text-sm text-gray-700"></p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Keputusan *</label>
                    <select name="action" id="approve-action" required onchange="toggleRejectionReason()"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        <option value="approved">Setujui</option>
                        <option value="rejected">Tolak</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Disetujui Oleh</label>
                    <select name="approved_by" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        <option value="">Pilih atasan...</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="rejection-reason-wrap" class="hidden">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Alasan Penolakan</label>
                    <textarea name="rejection_reason" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-approve-leave').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openApprove(id, name) {
        document.getElementById('form-approve-leave').action = '{{ url("hrm/leave") }}/' + id + '/approve';
        document.getElementById('approve-emp-name').textContent = 'Karyawan: ' + name;
        document.getElementById('modal-approve-leave').classList.remove('hidden');
    }
    function toggleRejectionReason() {
        const action = document.getElementById('approve-action').value;
        document.getElementById('rejection-reason-wrap').classList.toggle('hidden', action !== 'rejected');
    }
    </script>
    @endpush
</x-app-layout>
