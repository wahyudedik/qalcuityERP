@extends('errors.layout')

@section('title', 'Sesi Kedaluwarsa')
@section('code', '419')
@section('icon', '⏰')
@section('icon-bg', 'bg-amber-500/10')
@section('heading', 'Sesi Kedaluwarsa')
@section('message', 'Sesi Anda telah berakhir karena tidak aktif terlalu lama. Silakan muat ulang halaman dan coba lagi.')

@section('extra')
<div class="mt-4">
    <button onclick="location.reload()"
        class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-xl transition">
        Muat Ulang Halaman
    </button>
</div>
@endsection
