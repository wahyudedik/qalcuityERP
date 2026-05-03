<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Banquet & Events') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            {{-- Statistics --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Total Events</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ $stats['total_events'] ?? 0 }}</p>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Upcoming Events</h3>
                    <p class="text-3xl font-bold text-orange-600">
                        {{ $stats['upcoming_events'] ?? 0 }}</p>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">This Month Revenue</h3>
                    <p class="text-3xl font-bold text-green-600">Rp
                        {{ number_format($stats['month_revenue'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Upcoming Events --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Upcoming Events</h3>
                    <a href="{{ route('hotel.fb.banquet.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                        New Event
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Event #</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Event Name</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Date</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Type</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Guests</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Total</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($upcomingEvents as $event)
                                <tr>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $event->event_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $event->event_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $event->event_date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                            {{ ucfirst(str_replace('_', ' ', $event->event_type)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $event->confirmed_guests }} / {{ $event->expected_guests }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        Rp {{ number_format($event->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $event->status === 'confirmed'
                                            ? 'bg-green-100 text-green-800'
                                            : ($event->status === 'in_progress'
                                                ? 'bg-blue-100 text-blue-800'
                                                : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ ucfirst(str_replace('_', ' ', $event->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <a href="{{ route('hotel.fb.banquet.show', $event->id) }}"
                                            class="text-indigo-600 hover:text-indigo-900">View</a>

                                        @if ($event->status === 'confirmed')
                                            <form action="{{ route('hotel.fb.banquet.complete', $event->id) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="text-green-600 hover:text-green-900">Complete</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                        No upcoming events
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
