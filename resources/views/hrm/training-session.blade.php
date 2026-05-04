<x-app-layout>
    <x-slot name="header">Peserta Pelatihan — {{ $session->program?->name }}</x-slot>

    <div class="flex flex-col lg:flex-row gap-5">

        {{-- Sidebar: info sesi + tambah peserta --}}
        <div class="lg:w-72 shrink-0 space-y-4">
            {{-- Info sesi --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 p-5 space-y-2 text-sm">
                <p class="font-semibold text-gray-900">{{ $session->program?->name }}</p>
                @if ($session->program?->category)
                    <span
                        class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">{{ $session->program?->category }}</span>
                @endif
                <div class="space-y-1 text-xs text-gray-500 pt-1">
                    <p>📅 {{ $session->start_date->format('d M Y') }} – {{ $session->end_date->format('d M Y') }}</p>
                    @if ($session->location)
                        <p>📍 {{ $session->location }}</p>
                    @endif
                    @if ($session->trainer)
                        <p>👤 {{ $session->trainer }}</p>
                    @endif
                    <p>👥
                        {{ $session->participants->count() }}{{ $session->max_participants > 0 ? '/' . $session->max_participants : '' }}
                        peserta</p>
                </div>
                {{-- Update status --}}
                <form method="POST" action="{{ route('hrm.training.sessions.status', $session) }}" class="pt-2">
                    @csrf @method('PATCH')
                    <div class="flex gap-2">
                        <select name="status"
                            class="flex-1 px-2 py-1.5 text-xs rounded-lg border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach (['scheduled' => 'Terjadwal', 'ongoing' => 'Berlangsung', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'] as $v => $l)
                                <option value="{{ $v }}" @selected($session->status === $v)>{{ $l }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit"
                            class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            </div>

            {{-- Tambah peserta --}}
            @if (!$session->isFull())
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <h3 class="font-semibold text-gray-900 mb-3 text-sm">Tambah Peserta</h3>
                    <form method="POST" action="{{ route('hrm.training.sessions.participants.add', $session) }}"
                        class="space-y-2">
                        @csrf
                        <select name="employee_id" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih karyawan...</option>
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit"
                            class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Daftarkan</button>
                    </form>
                </div>
            @else
                <div class="bg-amber-500/10 border border-amber-500/20 rounded-2xl p-4 text-xs text-amber-300">Sesi
                    sudah penuh.</div>
            @endif

            <a href="{{ route('hrm.training.index', ['tab' => 'sessions']) }}"
                class="block text-center px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                ← Kembali
            </a>
        </div>

        {{-- Daftar peserta --}}
        <div class="flex-1 min-w-0">
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Daftar Peserta</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Karyawan</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Nilai</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($session->participants as $p)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900">
                                            {{ $p->employee?->name ?? '-' }}</p>
                                        <p class="text-xs text-gray-400">
                                            {{ $p->employee?->department ?? ($p->employee?->position ?? '') }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <form method="POST"
                                            action="{{ route('hrm.training.participants.update', $p) }}"
                                            class="inline-flex items-center gap-1">
                                            @csrf @method('PATCH')
                                            <select name="status" onchange="this.form.submit()"
                                                class="px-2 py-1 text-xs rounded-lg border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none">
                                                @foreach (['registered' => 'Terdaftar', 'attended' => 'Hadir', 'passed' => 'Lulus', 'failed' => 'Tidak Lulus', 'absent' => 'Absen'] as $v => $l)
                                                    <option value="{{ $v }}" @selected($p->status === $v)>
                                                        {{ $l }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <form method="POST"
                                            action="{{ route('hrm.training.participants.update', $p) }}"
                                            class="inline-flex items-center gap-1">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $p->status }}">
                                            <input type="number" name="score" value="{{ $p->score }}"
                                                min="0" max="100" placeholder="—"
                                                class="w-16 px-2 py-1 text-xs rounded-lg border border-gray-200 bg-gray-50 text-gray-900 text-center focus:outline-none focus:ring-1 focus:ring-blue-500">
                                            <button type="submit"
                                                class="text-xs text-blue-400 hover:text-blue-300">Confirm</button>
                                        </form>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <form method="POST"
                                            action="{{ route('hrm.training.participants.remove', $p) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" onclick="return confirm('Hapus peserta ini?')"
                                                class="text-xs text-red-400 hover:text-red-300">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center text-gray-400">
                                        Belum ada peserta terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
