<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Karir') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-3xl font-bold mb-6">Karir di Qalcuity</h1>
                    <p class="text-lg mb-6">Bergabunglah dengan tim kami untuk membangun masa depan ERP Indonesia.</p>

                    <div class="space-y-4">
                        <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <h3 class="text-xl font-semibold mb-2">🚀 Senior Laravel Developer</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-3">Full-time • Remote OK</p>
                            <a href="#" class="text-blue-600 hover:text-blue-700">Apply Now →</a>
                        </div>

                        <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <h3 class="text-xl font-semibold mb-2">🎨 UI/UX Designer</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-3">Full-time • Jakarta</p>
                            <a href="#" class="text-blue-600 hover:text-blue-700">Apply Now →</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
