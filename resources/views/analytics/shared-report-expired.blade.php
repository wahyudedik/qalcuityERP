<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Report Expired') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl p-8 shadow text-center">
                <div class="text-6xl mb-4">⏰</div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">
                    Report Has Expired
                </h3>
                <p class="text-gray-600 mb-6">
                    The report "<strong>{{ $sharedReport->name }}</strong>" is no longer available.
                </p>

                <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                    <p class="text-sm text-gray-600 mb-2">
                        <span class="font-semibold">Expired on:</span>
                        {{ $sharedReport->expires_at->format('d M Y H:i') }}
                    </p>
                    <p class="text-sm text-gray-600 mb-2">
                        <span class="font-semibold">Shared by:</span>
                        {{ $sharedReport->creator?->name ?? 'Unknown' }}
                    </p>
                    <p class="text-sm text-gray-600">
                        <span class="font-semibold">Total views:</span>
                        {{ $sharedReport->access_count }}
                    </p>
                </div>

                <p class="text-sm text-gray-500 mb-6">
                    Please contact the person who shared this report if you need access.
                </p>

                <a href="/"
                    class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    Go to Dashboard
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
