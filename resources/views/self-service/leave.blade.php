<x-app-layout>
    <x-slot name="header">Cuti Saya</x-slot>

    @if (!$employee)
        <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <p class="text-gray-500 text-sm">Akun Anda belum terhubung ke data karyawan.</p>
            <p class="text-gray-400 text-xs mt-1">Hubungi admin untuk menghubungkan akun ke profil karyawan.</p>
        </div>
    @else
        {{-- Quota card --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Kuota Cuti Tahunan</p>
                <div class="flex items-end gap-2">
                    <p class="text-3xl font-black text-gray-900">{{ $quota - $usedDays }}</p>
                    <p class="text-sm text-gray-400 mb-1">/ {{ $quota }} hari tersisa</p>
                </div>
                <div class="mt-3 h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500 rounded-full transition-all"
                        style="width: {{ $quota > 0 ? min(100, ($usedDays / $quota) * 100) : 0 }}%"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $usedDays }} hari terpakai tahun {{ now()->year }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Menunggu Persetujuan</p>
                <p class="text-3xl font-black text-amber-500">{{ $leaves->where('status', 'pending')->count() }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Disetujui Tahun Ini</p>
                <p class="text-3xl font-black text-green-500">{{ $leaves->where('status', 'approved')->count() }}</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-4">
                {{ $errors->first() }}
            </div>
        @endif
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col lg:flex-row gap-6">

            {{-- Form Ajukan Cuti --}}
            <div class="w-full lg:w-72 shrink-0">
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <h3 class="font-semibold text-gray-900 mb-4">Ajukan Cuti</h3>
                    <form method="POST" action="{{ route('self-service.leave.store') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Jenis Cuti *</label>
                            <select name="type" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="annual">Cuti Tahunan</option>
                                <option value="sick">Sakit</option>
                                <option value="maternity">Cuti Melahirkan</option>
                                <option value="paternity">Cuti Ayah</option>
                                <option value="unpaid">Cuti Tanpa Gaji</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Mulai *</label>
                            <input type="date" name="start_date" required min="{{ today()->format('Y-m-d') }}"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Selesai *</label>
                            <input type="date" name="end_date" required min="{{ today()->format('Y-m-d') }}"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Alasan</label>
                            <textarea name="reason" rows="3" placeholder="Opsional..."
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                        </div>
                        <button type="submit"
                            class="w-full py-2.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium">
                            Kirim Pengajuan
                        </button>
                    </form>
                </div>
            </div>

            {{-- Riwayat Cuti --}}
            <div class="flex-1">
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100">
                        <p class="font-semibold text-gray-900">Riwayat Pengajuan</p>
                    </div>
                    @if ($leaves->isEmpty())
                        <div class="px-5 py-12 text-center text-sm text-gray-400">Belum ada pengajuan cuti.</div>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach ($leaves ?? [] as $leave)
                                @php
                                    $badge = match ($leave->status) {
                                        'approved' => 'bg-green-100 text-green-700',
                                        'rejected' => 'bg-red-100 text-red-700',
                                        default => 'bg-amber-100 text-amber-700',
                                    };
                                    $statusLabel = match ($leave->status) {
                                        'approved' => 'Disetujui',
                                        'rejected' => 'Ditolak',
                                        default => 'Menunggu',
                                    };
                                @endphp
                                <div class="px-5 py-4 flex items-start gap-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <p class="text-sm font-medium text-gray-900">{{ $leave->typeLabel() }}</p>
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-full {{ $badge }}">{{ $statusLabel }}</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $leave->start_date->format('d M Y') }} —
                                            {{ $leave->end_date->format('d M Y') }}
                                            · <span class="font-medium">{{ $leave->days }} hari</span>
                                        </p>
                                        @if ($leave->reason)
                                            <p class="text-xs text-gray-400 mt-0.5 italic">{{ $leave->reason }}</p>
                                        @endif
                                        @if ($leave->status === 'rejected' && $leave->rejection_reason)
                                            <p class="text-xs text-red-500 mt-1">Alasan penolakan:
                                                {{ $leave->rejection_reason }}</p>
                                        @endif
                                        @if ($leave->status === 'approved' && $leave->approver)
                                            <p class="text-xs text-gray-400 mt-0.5">Disetujui oleh:
                                                {{ $leave->approver?->name }}</p>
                                        @endif
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="text-xs text-gray-400">{{ $leave->created_at->format('d M Y') }}</p>
                                        @if ($leave->status === 'pending')
                                            <form method="POST"
                                                action="{{ route('self-service.leave.cancel', $leave) }}"
                                                data-confirm="Batalkan pengajuan ini?" data-confirm-type="danger"
                                                class="mt-1">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="text-xs text-red-500 hover:text-red-700">Batalkan</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if ($leaves->hasPages())
                            <div class="px-5 py-3 border-t border-gray-100">{{ $leaves->links() }}</div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
