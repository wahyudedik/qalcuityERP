<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('tour-travel.packages.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-base font-semibold text-gray-900">{{ $package->name }}</h1>
                @php
                    $color = $package->status_color;
                @endphp
                <span class="px-2 py-1 text-xs rounded-full bg-{{ $color }}-100 text-{{ $color }}-700">
                    {{ ucfirst($package->status) }}
                </span>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('tour-travel.packages.edit', $package) }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                    Edit Package
                </a>
                <form action="{{ route('tour-travel.packages.toggle-status', $package) }}" method="POST"
                    class="inline">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-medium">
                        {{ $package->status === 'active' ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Package Overview Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <p class="text-xs text-gray-500">Package Code</p>
                    <p class="text-lg font-bold text-gray-900 mt-1">{{ $package->package_code }}</p>
                </div>
                <div class="bg-white rounded-xl border border-blue-200 p-4">
                    <p class="text-xs text-gray-500">Price / Person</p>
                    <p class="text-lg font-bold text-blue-600 mt-1">Rp
                        {{ number_format($package->price_per_person, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-xl border border-green-200 p-4">
                    <p class="text-xs text-gray-500">Profit Margin</p>
                    <p class="text-lg font-bold text-green-600 mt-1">{{ $package->profit_margin }}%</p>
                </div>
                <div class="bg-white rounded-xl border border-purple-200 p-4">
                    <p class="text-xs text-gray-500">Duration</p>
                    <p class="text-lg font-bold text-purple-600 mt-1">{{ $package->duration_days }}D /
                        {{ $package->duration_nights ?? $package->duration_days - 1 }}N</p>
                </div>
            </div>

            {{-- Package Details --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                {{-- Main Info --}}
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-base font-semibold text-gray-900">📋 Package Details</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500">Destination</p>
                                <p class="text-sm font-medium text-gray-900 mt-1">{{ $package->destination }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Category</p>
                                <p class="text-sm font-medium text-gray-900 mt-1">
                                    <span
                                        class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">{{ $package->category_label }}</span>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Min Pax</p>
                                <p class="text-sm font-medium text-gray-900 mt-1">{{ $package->min_pax ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Max Pax</p>
                                <p class="text-sm font-medium text-gray-900 mt-1">{{ $package->max_pax ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Valid From</p>
                                <p class="text-sm font-medium text-gray-900 mt-1">
                                    {{ $package->valid_from?->format('d M Y') ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Valid Until</p>
                                <p class="text-sm font-medium text-gray-900 mt-1">
                                    {{ $package->valid_until?->format('d M Y') ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Cost / Person</p>
                                <p class="text-sm font-medium text-gray-900 mt-1">Rp
                                    {{ number_format($package->cost_per_person, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Created By</p>
                                <p class="text-sm font-medium text-gray-900 mt-1">
                                    {{ $package->createdBy?->name ?? '-' }}</p>
                            </div>
                        </div>

                        @if ($package->description)
                            <div class="pt-4 border-t border-gray-100">
                                <p class="text-xs text-gray-500 mb-1">Description</p>
                                <p class="text-sm text-gray-700">{{ $package->description }}</p>
                            </div>
                        @endif

                        @if ($package->inclusions && count($package->inclusions) > 0)
                            <div class="pt-4 border-t border-gray-100">
                                <p class="text-xs text-gray-500 mb-2">Inclusions</p>
                                <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                                    @foreach ($package->inclusions as $inclusion)
                                        <li>{{ $inclusion }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($package->exclusions && count($package->exclusions) > 0)
                            <div class="pt-4 border-t border-gray-100">
                                <p class="text-xs text-gray-500 mb-2">Exclusions</p>
                                <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                                    @foreach ($package->exclusions as $exclusion)
                                        <li>{{ $exclusion }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Supplier Allocations --}}
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-base font-semibold text-gray-900">🤝 Supplier Allocations</h3>
                    </div>
                    <div class="p-6">
                        @if ($package->supplierAllocations->isEmpty())
                            <p class="text-sm text-gray-500 text-center py-4">No suppliers assigned yet.</p>
                        @else
                            <div class="space-y-3">
                                @foreach ($package->supplierAllocations as $allocation)
                                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                                        <div class="flex items-center justify-between mb-1">
                                            <span
                                                class="text-sm font-medium text-gray-900">{{ $allocation->supplier?->name ?? 'Unknown' }}</span>
                                            <span
                                                class="px-2 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-700">{{ $allocation->service_type_label }}</span>
                                        </div>
                                        @if ($allocation->service_description)
                                            <p class="text-xs text-gray-600 mb-1">
                                                {{ $allocation->service_description }}</p>
                                        @endif
                                        <div class="flex items-center justify-between text-xs text-gray-500">
                                            <span>{{ $allocation->day_number ? 'Day ' . $allocation->day_number : 'All days' }}</span>
                                            <span class="font-medium text-gray-900">Rp
                                                {{ number_format($allocation->cost_per_unit, 0, ',', '.') }} /
                                                {{ str_replace('_', ' ', $allocation->unit_type) }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Itinerary --}}
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900">🗓️ Itinerary</h3>
                </div>
                <div class="p-6">
                    @if ($package->itineraryDays->isEmpty())
                        <p class="text-sm text-gray-500 text-center py-4">No itinerary days added yet.</p>
                    @else
                        <div class="space-y-4">
                            @foreach ($package->itineraryDays as $day)
                                <div class="flex gap-4">
                                    <div
                                        class="flex-shrink-0 w-16 h-16 bg-indigo-50 rounded-xl flex flex-col items-center justify-center border border-indigo-100">
                                        <span class="text-xs text-indigo-500">Day</span>
                                        <span class="text-lg font-bold text-indigo-700">{{ $day->day_number }}</span>
                                    </div>
                                    <div class="flex-1 pb-4 border-b border-gray-100 last:border-0">
                                        <h4 class="text-sm font-semibold text-gray-900">{{ $day->title }}</h4>
                                        @if ($day->description)
                                            <p class="text-sm text-gray-600 mt-1">{{ $day->description }}</p>
                                        @endif
                                        <div class="flex flex-wrap gap-3 mt-2">
                                            @if ($day->accommodation)
                                                <span class="inline-flex items-center gap-1 text-xs text-gray-500">
                                                    🏨 {{ $day->accommodation }}
                                                </span>
                                            @endif
                                            @if ($day->transport_mode)
                                                <span class="inline-flex items-center gap-1 text-xs text-gray-500">
                                                    🚗 {{ ucfirst($day->transport_mode) }}
                                                </span>
                                            @endif
                                            @if ($day->meals && is_array($day->meals))
                                                <span class="inline-flex items-center gap-1 text-xs text-gray-500">
                                                    🍽️ {{ implode(', ', $day->meals) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Recent Bookings --}}
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900">📝 Recent Bookings</h3>
                </div>
                @if ($package->bookings->isEmpty())
                    <div class="p-6">
                        <p class="text-sm text-gray-500 text-center py-4">No bookings yet for this package.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Booking
                                        Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pax
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($package->bookings as $booking)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 font-medium text-indigo-600">
                                            {{ $booking->booking_code ?? '-' }}</td>
                                        <td class="px-6 py-4 text-gray-700">{{ $booking->customer_name ?? '-' }}</td>
                                        <td class="px-6 py-4 text-gray-700">{{ $booking->number_of_pax ?? '-' }}</td>
                                        <td class="px-6 py-4 text-gray-900 font-medium">Rp
                                            {{ number_format($booking->total_amount ?? 0, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4">
                                            @php
                                                $bColor = match ($booking->status) {
                                                    'confirmed', 'paid' => 'green',
                                                    'pending' => 'yellow',
                                                    'cancelled' => 'red',
                                                    default => 'gray',
                                                };
                                            @endphp
                                            <span
                                                class="px-2 py-1 text-xs rounded-full bg-{{ $bColor }}-100 text-{{ $bColor }}-700">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 text-xs">
                                            {{ $booking->created_at->format('d M Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
