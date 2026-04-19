<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Upgrade Diperlukan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Upgrade Required Card --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-8 sm:p-12 text-center">
                    {{-- Icon --}}
                    <div class="flex justify-center mb-6">
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    </div>

                    {{-- Title --}}
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                        Modul {{ $moduleName ?? 'Ini' }} Memerlukan Upgrade
                    </h1>

                    {{-- Description --}}
                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-8 max-w-2xl mx-auto">
                        Modul <strong class="text-gray-900 dark:text-white">{{ $moduleName ?? 'ini' }}</strong> tidak termasuk dalam paket langganan Anda saat ini.
                        Upgrade ke paket yang lebih tinggi untuk mengakses fitur ini dan meningkatkan produktivitas bisnis Anda.
                    </p>

                    {{-- Current Plan Info --}}
                    @if(auth()->user()->tenant)
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg mb-8">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Paket Saat Ini:</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white uppercase">
                                {{ auth()->user()->tenant->plan ?? 'Trial' }}
                            </span>
                        </div>
                    @endif

                    {{-- Module Description --}}
                    @if(isset($moduleDescription))
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6 mb-8 text-left">
                            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">
                                Tentang Modul {{ $moduleName }}
                            </h3>
                            <p class="text-blue-800 dark:text-blue-200">
                                {{ $moduleDescription }}
                            </p>
                        </div>
                    @endif

                    {{-- Action Buttons --}}
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                        <a href="{{ route('subscription.index') }}" 
                           class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                            Lihat Paket Langganan
                        </a>

                        <a href="{{ route('dashboard') }}" 
                           class="inline-flex items-center px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-semibold rounded-lg transition-all duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Kembali ke Dashboard
                        </a>
                    </div>

                    {{-- Contact Support --}}
                    <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Butuh bantuan memilih paket yang tepat?
                            <a href="mailto:support@qalcuity.com" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                Hubungi Tim Kami
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Available Plans Preview --}}
            @if(isset($availablePlans) && count($availablePlans) > 0)
                <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">
                        Paket yang Menyertakan Modul Ini
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($availablePlans as $plan)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:border-blue-500 dark:hover:border-blue-400 transition-colors">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 uppercase">
                                    {{ $plan['name'] }}
                                </h3>
                                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mb-4">
                                    {{ $plan['price'] }}
                                    <span class="text-sm text-gray-600 dark:text-gray-400 font-normal">/bulan</span>
                                </p>
                                <ul class="space-y-2 mb-6">
                                    @foreach($plan['features'] as $feature)
                                        <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                                            <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span>{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                <a href="{{ route('subscription.index') }}" 
                                   class="block w-full text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                                    Pilih Paket
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
