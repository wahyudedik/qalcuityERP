<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Multi Company</h2>
            <a href="{{ route('company-groups.create') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                + Buat Grup
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        @if($groups->isEmpty())
            <div class="text-center py-16 text-gray-500 dark:text-gray-400">
                <div class="text-5xl mb-4">🏢</div>
                <p class="text-lg font-medium">Belum ada grup perusahaan</p>
                <p class="text-sm mt-1">Buat grup untuk mengelola beberapa perusahaan dan laporan konsolidasi.</p>
                <a href="{{ route('company-groups.create') }}"
                   class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                    Buat Grup Pertama
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($groups as $group)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="font-semibold text-gray-800 dark:text-gray-100">{{ $group->name }}</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $group->members_count }} perusahaan · {{ $group->currency_code }}
                                </p>
                            </div>
                            <span class="text-2xl">🏢</span>
                        </div>
                        <a href="{{ route('company-groups.show', $group) }}"
                           class="block w-full text-center px-3 py-2 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg text-sm hover:bg-blue-100 dark:hover:bg-blue-900/50">
                            Lihat Konsolidasi →
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
