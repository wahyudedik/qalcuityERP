@extends('layouts.app')
@section('title', 'Kalkulator Biaya Resep')
@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Kalkulator Biaya Resep</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Kalkulasi biaya resep dan analisis keuntungan secara real-time</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('fnb.recipes.low-margin') }}"
                    class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors min-h-[44px]">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Resep Margin Rendah
                </a>
                <form action="{{ route('fnb.recipes.bulk-update-costs') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors min-h-[44px]"
                        onclick="return confirm('Update semua biaya bahan dari harga inventori terkini?')">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Update Biaya
                    </button>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 rounded text-green-700 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        @if ($lowMarginRecipes->isNotEmpty())
            <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 mb-6 rounded">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="text-red-700 dark:text-red-300 font-medium">
                        {{ $lowMarginRecipes->count() }} resep memiliki margin keuntungan rendah (&lt;30%).
                        <a href="{{ route('fnb.recipes.low-margin') }}" class="underline hover:no-underline">Lihat detail →</a>
                    </span>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($recipes as $recipe)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100">{{ $recipe->name }}</h3>
                        <span
                            class="px-2 py-1 text-xs rounded-full {{ $recipe->is_active ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300' }}">
                            {{ $recipe->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                        {{ $recipe->menuItem?->name ?? 'Tidak ada menu item' }}
                    </div>

                    <div class="space-y-1 text-sm mb-3">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Hasil:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $recipe->yield_quantity }} {{ $recipe->yield_unit }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Waktu Persiapan:</span>
                            <span class="text-gray-700 dark:text-gray-300">{{ $recipe->preparation_time_minutes ?? '-' }} menit</span>
                        </div>
                        @if ($recipe->cooking_time_minutes)
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Waktu Masak:</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ $recipe->cooking_time_minutes }} menit</span>
                            </div>
                        @endif
                    </div>

                    <a href="{{ route('fnb.recipes.calculate', $recipe) }}"
                        class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-medium transition-colors min-h-[44px] flex items-center justify-center">
                        Hitung Biaya
                    </a>
                </div>
            @empty
                <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p>Belum ada resep. Buat resep pertama Anda untuk mulai menghitung biaya.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
