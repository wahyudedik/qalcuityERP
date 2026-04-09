<x-app-layout>
    <x-slot name="header">Access Restricted</x-slot>

    <div class="min-h-screen flex items-center justify-center">
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-8 max-w-2xl mx-auto text-center">
            {{-- Warning Icon --}}
            <div
                class="w-20 h-20 mx-auto bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center mb-6">
                <svg class="w-10 h-10 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            {{-- Title --}}
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                Access Outside Business Hours
            </h1>

            {{-- Message --}}
            <p class="text-gray-600 dark:text-slate-300 mb-6">
                {{ $message ?? 'Access to this resource is restricted to business hours only.' }}
            </p>

            {{-- Business Hours Info --}}
            <div
                class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-900/30 rounded-xl p-4 mb-6">
                <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-200 mb-2">Business Hours</h3>
                <p class="text-sm text-blue-700 dark:text-blue-300">
                    {{ $business_hours['display'] ?? 'Monday - Friday, 08:00 - 18:00' }}
                </p>
                @if (isset($current_time))
                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-2">
                        Current time: {{ $current_time->format('l, H:i') }}
                    </p>
                @endif
            </div>

            {{-- Emergency Access --}}
            @if (auth()->user() && (auth()->user()->hasRole('doctor') || auth()->user()->hasRole('emergency_staff')))
                <div
                    class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/30 rounded-xl p-4 mb-6">
                    <h3 class="text-sm font-semibold text-red-900 dark:text-red-200 mb-2">Emergency Access</h3>
                    <p class="text-sm text-red-700 dark:text-red-300 mb-3">
                        If this is an emergency situation, you can request emergency access.
                    </p>
                    <form action="{{ url()->current() }}" method="POST">
                        @csrf
                        <input type="hidden" name="emergency_access" value="true">
                        <div class="mb-3">
                            <textarea name="override_reason" placeholder="Please provide reason for emergency access..." required
                                class="w-full px-3 py-2 text-sm rounded-lg border border-red-200 dark:border-red-900/30 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500"
                                rows="3"></textarea>
                        </div>
                        <button type="submit"
                            class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            Request Emergency Access
                        </button>
                    </form>
                </div>
            @endif

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ url('/healthcare') }}"
                    class="px-6 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Back to Healthcare Dashboard
                </a>
                <button onclick="history.back()"
                    class="px-6 py-2 text-sm bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Go Back
                </button>
            </div>

            {{-- Help Text --}}
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-6">
                If you believe this is an error, please contact your system administrator.
            </p>
        </div>
    </div>
</x-app-layout>
