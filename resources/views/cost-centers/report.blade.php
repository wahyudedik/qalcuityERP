@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Laporan Segment (P&L per Cost Center)</h2>
            <p class="text-sm text-slate-500 mt-0.5">Laba rugi per divisi / cabang / proyek</p>
        </div>
        <a href="{{ route('cost-centers.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/10 transition">
            ← Kembali
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" class="flex gap-3 flex-wrap items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Dari</label>
            <input type="date" name="from" value="{{ $from }}"
                class="px-3 py-2 rounded-xl text-sm border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Sampai</label>
            <input type="date" name="to" value="{{ $to }}"
                class="px-3 py-2 rounded-xl text-sm border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-blue-600 text-white hover:bg-blue-700 transi