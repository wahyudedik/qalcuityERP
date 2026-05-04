<x-app-layout>
    <x-slot name="header">Absensi Saya</x-slot>

    @if(!$employee)
    <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <p class="text-gray-500 text-sm">Akun Anda belum terhubung ke data karyawan.</p>
        <p class="text-gray-400 text-xs mt-1">Hubungi admin untuk menghubungkan akun ke profil karyawan.</p>
    </div>
    @else

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

    {{-- Clock In/Out Card --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- Clock widget --}}
        <div class="lg:col-span-1 bg-white rounded-2xl border border-gray-200 p-6 flex flex-col items-center text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">{{ now()->translatedFormat('l, d F Y') }}</p>

            {{-- Live clock --}}
            <p id="live-clock" class="text-4xl font-black text-gray-900 tabular-nums mb-4">{{ now()->format('H:i:s') }}</p>

            @if($today)
                <div class="w-full space-y-2 mb-5">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Clock In</span>
                        <span class="font-semibold text-green-600">{{ $today->check_in ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Clock Out</span>
                        <span class="font-semibold {{ $today->check_out ? 'text-blue-600' : 'text-gray-400' }}">
                            {{ $today->check_out ?? '—' }}
                        </span>
                    </div>
                    @if($today->check_in && $today->check_out)
                    @php
                        $duration = \Carbon\Carbon::parse($today->check_in)->diffInMinutes(\Carbon\Carbon::parse($today->check_out));
                        $h = intdiv($duration, 60); $m = $duration % 60;
                    @endphp
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Durasi</span>
                        <span class="font-semibold text-gray-900">{{ $h }}j {{ $m }}m</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Status</span>
                        @php
                            $sc = match($today->status) {
                                'present' => 'text-green-600',
                                'late'    => 'text-amber-600',
                                'absent'  => 'text-red-600',
                                default   => 'text-gray-500',
                            };
                            $sl = match($today->status) {
                                'present' => 'Hadir', 'late' => 'Terlambat', 'absent' => 'Absen',
                                'leave' => 'Cuti', 'sick' => 'Sakit', default => ucfirst($today->status),
                            };
                        @endphp
                        <span class="font-semibold {{ $sc }}">{{ $sl }}</span>
                    </div>
                </div>
            @endif

            {{-- Action buttons --}}
            @if(!$today || !$today->check_in)
            <form method="POST" action="{{ route('self-service.attendance.clock-in') }}" class="w-full">
                @csrf
                <button type="submit"
                    class="w-full py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl flex items-center justify-center gap-2 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    Clock In
                </button>
            </form>
            @elseif($today->check_in && !$today->check_out)
            <form method="POST" action="{{ route('self-service.attendance.clock-out') }}" class="w-full">
                @csrf
                <button type="submit"
                    class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl flex items-center justify-center gap-2 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Clock Out
                </button>
            </form>
            @else
            <div class="w-full py-3 bg-gray-100 text-gray-500 font-medium rounded-xl text-center text-sm">
                ✓ Selesai hari ini
            </div>
            @endif
        </div>

        {{-- Stats bulan ini --}}
        <div class="lg:col-span-2 grid grid-cols-2 sm:grid-cols-4 gap-4 content-start">
            @php
                $statItems = [
                    ['label' => 'Hadir', 'key' => 'present', 'color' => 'text-green-600'],
                    ['label' => 'Terlambat', 'key' => 'late', 'color' => 'text-amber-600'],
                    ['label' => 'Absen', 'key' => 'absent', 'color' => 'text-red-600'],
                    ['label' => 'Cuti/Sakit', 'key' => 'leave', 'color' => 'text-blue-600'],
                ];
            @endphp
            @foreach($statItems ?? [] as $s)
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">{{ $s['label'] }}</p>
                <p class="text-2xl font-bold {{ $s['color'] }} mt-1">
                    {{ ($monthStats[$s['key']] ?? 0) + ($s['key'] === 'leave' ? ($monthStats['sick'] ?? 0) : 0) }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">hari bulan ini</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Riwayat 30 hari --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <p class="font-semibold text-gray-900">Riwayat Absensi (30 Hari Terakhir)</p>
        </div>
        @if($history->isEmpty())
        <div class="px-5 py-10 text-center text-sm text-gray-400">Belum ada data absensi.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-center">Clock In</th>
                        <th class="px-4 py-3 text-center">Clock Out</th>
                        <th class="px-4 py-3 text-center">Durasi</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($history ?? [] as $att)
                    @php
                        $dur = '';
                        if ($att->check_in && $att->check_out) {
                            $mins = \Carbon\Carbon::parse($att->check_in)->diffInMinutes(\Carbon\Carbon::parse($att->check_out));
                            $dur = intdiv($mins, 60) . 'j ' . ($mins % 60) . 'm';
                        }
                        $sc = match($att->status) {
                            'present' => 'bg-green-100 text-green-700',
                            'late'    => 'bg-amber-100 text-amber-700',
                            'absent'  => 'bg-red-100 text-red-700',
                            'leave'   => 'bg-blue-100 text-blue-700',
                            'sick'    => 'bg-purple-100 text-purple-700',
                            default   => 'bg-gray-100 text-gray-500',
                        };
                        $sl = match($att->status) {
                            'present' => 'Hadir', 'late' => 'Terlambat', 'absent' => 'Absen',
                            'leave' => 'Cuti', 'sick' => 'Sakit', default => ucfirst($att->status),
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-900 font-medium">
                            {{ $att->date->translatedFormat('D, d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-700">{{ $att->check_in ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-gray-700">{{ $att->check_out ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $dur ?: '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $sc }}">{{ $sl }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif

    @push('scripts')
    <script>
    // Live clock
    function updateClock() {
        const el = document.getElementById('live-clock');
        if (!el) return;
        const now = new Date();
        el.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
    }
    setInterval(updateClock, 1000);
    </script>
    @endpush
</x-app-layout>
