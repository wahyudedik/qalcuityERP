@extends('errors.layout')

@section('title', 'Sedang Maintenance')
@section('code', '503')
@section('icon', '🛠️')
@section('icon-bg', 'bg-indigo-500/10')
@section('heading', 'Sedang Maintenance')
@section('message', 'Qalcuity ERP sedang dalam pemeliharaan terjadwal. Kami akan kembali dalam beberapa menit.')

@section('extra')
<div class="mt-4 bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/20 rounded-xl p-4 text-sm text-indigo-700 dark:text-indigo-400">
    <p class="font-medium mb-1">Estimasi selesai:</p>
    <p>Biasanya kurang dari 15 menit. Halaman ini akan otomatis refresh.</p>
</div>
<script>setTimeout(() => location.reload(), 30000);</script>
@endsection
