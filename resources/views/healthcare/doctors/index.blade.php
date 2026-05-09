<x-app-layout>
    <x-slot name="header">Data Dokter</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Data Dokter'],
    ]" />

    {{-- Stats - Data from Controller (no more queries in Blade) --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Dokter</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $statistics['total_doctors'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Dokter Aktif</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $statistics['active_doctors'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Tersedia Hari Ini</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $statistics['available_today'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Cuti</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $statistics['on_leave'] }}</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="bg-white rounded-2xl border border-gray-200 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama / spesialis..."
                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="specialization"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Spesialisasi</option>
                    @php
                        $specializations = \App\Models\Doctor::where('tenant_id', auth()->user()->tenant_id)
                            ->whereNotNull('specialization')
                            ->distinct()
                            ->pluck('specialization');
                    @endphp
                    @foreach ($specializations as $spec)
                        <option value="{{ $spec }}" @selected(request('specialization') === $spec)>{{ $spec }}</option>
                    @endforeach
                </select>
                <select name="status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Status</option>
                    <option value="active" @selected(request('status') === 'active')>Aktif</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
                    <option value="on_leave" @selected(request('status') === 'on_leave')>Cuti</option>
                </select>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
            </form>
            <div class="flex gap-2">
                <button onclick="document.getElementById('modal-add-doctor').classList.remove('hidden')"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Dokter</button>
            </div>
        </div>
    </div>

    {{-- Table / Card View - Responsive --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        {{-- Desktop Table View (hidden on mobile <768px) --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Dokter</th>
                        <th class="px-4 py-3 text-left">Spesialisasi</th>
                        <th class="px-4 py-3 text-left">No. STR</th>
                        <th class="px-4 py-3 text-center">Jadwal Hari Ini</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($doctors as $doctor)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($doctor->photo)
                                        <img src="{{ $doctor->photo }}" alt="{{ $doctor->name }}" loading="lazy"
                                            class="w-9 h-9 rounded-xl object-cover shrink-0 border border-gray-200">
                                    @else
                                        <div
                                            class="w-9 h-9 rounded-xl bg-purple-100 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                </path>
                                            </svg>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $doctor->name }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $doctor->email ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-purple-100 text-purple-700">
                                    {{ $doctor->specialization ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-600">
                                {{ $doctor->str_number ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $todaySchedule = $doctor->schedules
                                        ->where('day_of_week', now()->dayOfWeek)
                                        ->where('is_active', true)
                                        ->first();
                                @endphp
                                @if ($todaySchedule)
                                    <span class="text-xs text-green-600">
                                        {{ $todaySchedule->start_time }} - {{ $todaySchedule->end_time }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">Tidak ada jadwal</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($doctor->status === 'active')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Aktif</span>
                                @elseif($doctor->status === 'inactive')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700">Nonaktif</span>
                                @elseif($doctor->status === 'on_leave')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700">Cuti</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('healthcare.doctors.show', $doctor) }}"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </a>
                                    <button onclick="editDoctor({{ $doctor->id }})"
                                        class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                                <p>Belum ada data dokter</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card View (visible only on mobile <768px) --}}
        <div class="md:hidden divide-y divide-gray-100">
            @forelse($doctors as $doctor)
                <div class="p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            @if ($doctor->photo)
                                <img src="{{ $doctor->photo }}" alt="{{ $doctor->name }}" loading="lazy"
                                    class="w-12 h-12 rounded-xl object-cover shrink-0 border border-gray-200">
                            @else
                                <div
                                    class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                        </path>
                                    </svg>
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-gray-900 truncate">{{ $doctor->name }}
                                </p>
                                <p class="text-xs text-gray-500">{{ $doctor->email ?? '-' }}</p>
                            </div>
                        </div>
                        @if ($doctor->status === 'active')
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 shrink-0">Aktif</span>
                        @elseif($doctor->status === 'inactive')
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 shrink-0">Nonaktif</span>
                        @elseif($doctor->status === 'on_leave')
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700 shrink-0">Cuti</span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                        <div class="col-span-2">
                            <p class="text-gray-400">Spesialisasi</p>
                            <p class="text-purple-600 font-medium">
                                {{ $doctor->specialization ?? '-' }}</p>
                        </div>
                        @if ($doctor->str_number)
                            <div class="col-span-2">
                                <p class="text-gray-400">No. STR</p>
                                <p class="font-mono text-gray-700">{{ $doctor->str_number }}</p>
                            </div>
                        @endif
                        <div class="col-span-2">
                            <p class="text-gray-400">Jadwal Hari Ini</p>
                            @php
                                $todaySchedule = $doctor->schedules
                                    ->where('day_of_week', now()->dayOfWeek)
                                    ->where('is_active', true)
                                    ->first();
                            @endphp
                            @if ($todaySchedule)
                                <p class="text-green-600 font-medium">
                                    {{ $todaySchedule->start_time }} - {{ $todaySchedule->end_time }}
                                </p>
                            @else
                                <p class="text-gray-400">Tidak ada jadwal</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                        <a href="{{ route('healthcare.doctors.show', $doctor) }}"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                </path>
                            </svg>
                            Detail
                        </a>
                        <button onclick="editDoctor({{ $doctor->id }})"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                            Edit
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                    <p class="text-gray-500">Belum ada data dokter</p>
                    <p class="text-xs text-gray-400 mt-1">Klik tombol "+ Dokter" untuk menambah
                        data</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($doctors->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $doctors->links() }}
            </div>
        @endif
    </div>

    {{-- Add Doctor Modal --}}
    <div id="modal-add-doctor"
        class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Tambah Dokter Baru</h3>
                <button onclick="document.getElementById('modal-add-doctor').classList.add('hidden')"
                    class="p-2 hover:bg-gray-100 rounded-xl">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form action="{{ route('healthcare.doctors.index') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap
                            *</label>
                        <input type="text" name="name" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
                        <input type="tel" name="phone"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Spesialisasi
                            *</label>
                        <select name="specialization" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Spesialisasi</option>
                            <option value="Umum">Dokter Umum</option>
                            <option value="Spesialis Anak">Spesialis Anak</option>
                            <option value="Spesialis Penyakit Dalam">Spesialis Penyakit Dalam</option>
                            <option value="Spesialis Bedah">Spesialis Bedah</option>
                            <option value="Spesialis Obgyn">Spesialis Obgyn</option>
                            <option value="Spesialis Jantung">Spesialis Jantung</option>
                            <option value="Spesialis Saraf">Spesialis Saraf</option>
                            <option value="Spesialis Mata">Spesialis Mata</option>
                            <option value="Spesialis THT">Spesialis THT</option>
                            <option value="Spesialis Kulit">Spesialis Kulit</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. STR</label>
                        <input type="text" name="str_number"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                        <textarea name="address" rows="2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button"
                        onclick="document.getElementById('modal-add-doctor').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
