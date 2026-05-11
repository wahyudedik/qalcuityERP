<x-app-layout>
    <x-slot name="header">Lembur Saya</x-slot>

    @if(!$employee)
    <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <p class="text-gray-500 text-sm">Akun Anda belum terhubung ke data karyawan.</p>
        <p class="text-gray-400 text-xs mt-1">Hubungi admin untuk menghubungkan akun ke profil karyawan.</p>
    </div>
    @else

    {{-- Statistik --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500 mb-1">Menunggu Persetujuan</p>
            <p class="text-3xl font-black text-amber-500">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500 mb-1">Disetujui Tahun Ini</p>
            <p class="text-3xl font-black text-green-500">{{ $stats['approved'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500 mb-1">Total Jam Lembur (Tahun Ini)</p>
            <p class="text-3xl font-black text-blue-500">{{ number_format($stats['total_hours'], 1) }}<span class="text-base font-normal text-gray-400 ml-1">jam</span></p>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-4">
        {{ $errors->first() }}
    </div>
    @endif
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-4">
        {{ session('success') }}
    </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-6">

        {{-- Form Pengajuan Lembur --}}
        <div class="lg:w-80 shrink-0">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Ajukan Lembur</h3>
                <form method="POST" action="{{ route('self-service.overtime.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal Lembur</label>
                        <input type="date" name="date" value="{{ old('date') }}" required
                            class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 text-sm px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Mulai</label>
                            <input type="time" name="start_time" value="{{ old('start_time') }}" required
                                class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 text-sm px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Selesai</label>
                            <input type="time" name="end_time" value="{{ old('end_time') }}" required
                                class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 text-sm px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                    @error('end_time')<p class="text-red-500 text-xs -mt-2">{{ $message }}</p>@enderror
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Alasan Lembur</label>
                        <textarea name="reason" rows="3" required placeholder="Jelaskan alasan lembur..."
                            class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 text-sm px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none">{{ old('reason') }}</textarea>
                        @error('reason')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit"
                        class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-colors">
                        Kirim Pengajuan
                    </button>
                </form>
            </div>
        </div>

        {{-- Riwayat Lembur --}}
        <div class="flex-1 min-w-0">
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Riwayat Pengajuan Lembur</h3>
                </div>
                @if($overtimes->isEmpty())
                <div class="p-12 text-center">
                    <p class="text-gray-400 text-sm">Belum ada pengajuan lembur.</p>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Waktu</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Durasi</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Alasan</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Upah Est.</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($overtimes ?? [] as $ot)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-gray-900 font-medium">
                                    {{ $ot->date->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ substr($ot->start_time, 0, 5) }} – {{ substr($ot->end_time, 0, 5) }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $ot->durationLabel() }}
                                </td>
                                <td class="px-4 py-3 text-gray-600 max-w-xs truncate">
                                    {{ $ot->reason }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-900">
                                    Rp {{ number_format($ot->overtime_pay, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $badge = match($ot->status) {
                                            'approved' => 'bg-green-100 text-green-700',
                                            'rejected' => 'bg-red-100 text-red-700',
                                            default    => 'bg-amber-100 text-amber-700',
                                        };
                                        $label = match($ot->status) {
                                            'approved' => 'Disetujui',
                                            'rejected' => 'Ditolak',
                                            default    => 'Menunggu',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $badge }}">{{ $label }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($ot->status === 'pending')
                                    <form method="POST" action="{{ route('self-service.overtime.cancel', $ot) }}"
                                        data-confirm="Batalkan pengajuan lembur ini?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 transition-colors">
                                            Batalkan
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-3 border-t border-gray-100">
                    {{ $overtimes->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</x-app-layout>
