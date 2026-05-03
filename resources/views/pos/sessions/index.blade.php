@extends('layouts.app')

@section('title', 'Riwayat Sesi Kasir')

@section('content')
<div class="p-4 sm:p-6 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Sesi Kasir</h1>
            <p class="text-sm text-gray-500 mt-0.5">Riwayat buka dan tutup sesi kasir</p>
        </div>
        <a href="{{ route('pos.sessions.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buka Sesi Baru
        </a>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('info'))
        <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-xl text-sm">
            {{ session('info') }}
        </div>
    @endif

    {{-- Tabel sesi --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Kasir</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Register</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Dibuka</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Ditutup</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Total Penjualan</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sessions as $session)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-900 font-medium">
                            {{ $session->cashier?->name ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $session->register_name ?? 'Kasir Utama' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $session->opened_at?->format('d/m/Y H:i') ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $session->closed_at?->format('d/m/Y H:i') ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-900 font-medium">
                            Rp {{ number_format($session->total_sales, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($session->isOpen())
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                                    Terbuka
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    Ditutup
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('pos.sessions.show', $session) }}"
                                    class="text-blue-600 hover:underline text-xs font-medium">
                                    Lihat
                                </a>
                                @if($session->isOpen())
                                    <a href="{{ route('pos.sessions.close-form', $session) }}"
                                        class="text-red-600 hover:underline text-xs font-medium">
                                        Tutup
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                            Belum ada sesi kasir. Klik "Buka Sesi Baru" untuk memulai.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sessions->hasPages())
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $sessions->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
