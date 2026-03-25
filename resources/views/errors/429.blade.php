@extends('errors.layout')

@section('title', 'Terlalu Banyak Permintaan')
@section('code', '429')
@section('icon', '🚦')
@section('icon-bg', 'bg-orange-500/10')
@section('heading', 'Terlalu Banyak Permintaan')
@section('message', 'Anda mengirim terlalu banyak permintaan dalam waktu singkat. Tunggu beberapa saat lalu coba lagi.')

@section('extra')
<div class="mt-4 bg-orange-50 dark:bg-orange-500/10 border border-orange-200 dark:border-orange-500/20 rounded-xl p-4 text-sm text-orange-700 dark:text-orange-400">
    Jika ini terkait kuota AI, Anda bisa upgrade paket di menu <a href="{{ url('/subscription') }}" class="underline font-medium">Langganan</a>.
</div>
@endsection
