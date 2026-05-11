@extends('layouts.app')

@section('title', 'Reservasi Meja ' . $table->table_number)

@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <div class="mb-6">
            <a href="{{ route('fnb.tables.index') }}"
                class="text-blue-600 hover:text-blue-800 text-sm transition-colors">
                ? Kembali ke Manajemen Meja
            </a>
        </div>

        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    Reservasi Meja {{ $table->table_number }}
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Kapasitas: {{ $table->capacity }} orang &bull; Lokasi: {{ $table->location ?? 'Area Utama' }}
                </p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                {{ $table->status === 'available' ? 'bg-green-100 text-green-800' : ($table->status === 'occupied' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                @switch($table->status)
                    @case('available') Tersedia @break
                    @case('occupied') Terisi @break
                    @case('reserved') Dipesan @break
                    @default {{ ucfirst($table->status) }}
                @endswitch
            </span>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 rounded text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <!-- Reservations Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Reservasi Mendatang</h2>
            </div>

            @if ($reservations->isEmpty())
                <div class="text-center py-12 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p>Tidak ada reservasi mendatang untuk meja ini</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal & Jam</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tamu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($reservations as $reservation)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $reservation->customer_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $reservation->customer_phone }}</div>
                                        @if ($reservation->customer_email)
                                            <div class="text-xs text-gray-500">{{ $reservation->customer_email }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($reservation->reservation_date)->format('d M Y') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($reservation->reservation_time)->format('H:i') }}
                                            — {{ $reservation->getEndTime() }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $reservation->party_size }} orang
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $reservation->duration_minutes }} menit
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusClasses = [
                                                'confirmed' => 'bg-blue-100 text-blue-800',
                                                'seated' => 'bg-green-100 text-green-800',
                                                'completed' => 'bg-gray-100 text-gray-800',
                                                'cancelled' => 'bg-red-100 text-red-800',
                                                'no_show' => 'bg-yellow-100 text-yellow-800',
                                            ];
                                            $statusLabels = [
                                                'confirmed' => 'Dikonfirmasi',
                                                'seated' => 'Duduk',
                                                'completed' => 'Selesai',
                                                'cancelled' => 'Dibatalkan',
                                                'no_show' => 'Tidak Hadir',
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 text-xs rounded-full {{ $statusClasses[$reservation->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $statusLabels[$reservation->status] ?? ucfirst($reservation->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center space-x-2">
                                            @if (in_array($reservation->status, ['confirmed']))
                                                <form action="{{ route('fnb.tables.reservations.update-status', $reservation) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="seated">
                                                    <button type="submit"
                                                        class="text-xs bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded transition-colors min-h-[32px]">
                                                        Dudukkan
                                                    </button>
                                                </form>
                                            @endif
                                            @if (in_array($reservation->status, ['confirmed', 'seated']))
                                                <form action="{{ route('fnb.tables.reservations.cancel', $reservation) }}" method="POST" class="inline" data-confirm="Batalkan reservasi ini?" data-confirm-type="danger">
                                                    @csrf
                                                    <button type="submit"
                                                        class="text-xs bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded transition-colors min-h-[32px]">
                                                        Batalkan
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if ($reservations->isNotEmpty())
            <div class="mt-4 text-sm text-gray-500">
                Menampilkan {{ $reservations->count() }} reservasi mendatang
            </div>
        @endif
    </div>
@endsection
