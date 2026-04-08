<x-app-layout>
    <x-slot name="header">
        <h1 class="text-base font-semibold text-gray-900 dark:text-white">Tour Packages</h1>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Action Button --}}
            <div class="mb-4 flex justify-end">
                <a href="{{ route('tour-travel.packages.create') }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                    + New Package
                </a>
            </div>

            {{-- Statistics Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Total Packages</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total_packages'] }}</p>
                </div>
                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-green-200 dark:border-green-500/30 p-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Active Packages</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">
                        {{ $stats['active_packages'] }}</p>
                </div>
                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-blue-200 dark:border-blue-500/30 p-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Total Bookings</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $stats['total_bookings'] }}
                    </p>
                </div>
                <div
                    class="bg-white dark:bg-[#1e293b] rounded-xl border border-purple-200 dark:border-purple-500/30 p-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Upcoming Departures</p>
                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">
                        {{ $stats['upcoming_departures'] }}
                    </p>
                </div>
                <div
                    class="bg-white dark:bg-[#1e293b] rounded-xl border border-orange-200 dark:border-orange-500/30 p-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Pending Visas</p>
                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400 mt-1">
                        {{ $stats['pending_visas'] }}</p>
                </div>
            </div>

            {{-- Packages Table --}}
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">📦 Tour Packages</h3>
                    <div class="flex gap-2">
                        <select
                            class="text-sm border border-gray-300 dark:border-gray-600 rounded px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                            <option value="">All Categories</option>
                            <option value="domestic">Domestic</option>
                            <option value="international">International</option>
                            <option value="adventure">Adventure</option>
                            <option value="luxury">Luxury</option>
                            <option value="cultural">Cultural</option>
                        </select>
                        <select
                            class="text-sm border border-gray-300 dark:border-gray-600 rounded px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                @if ($packages->count() === 0)
                    <div class="p-12 text-center">
                        <p class="text-4xl mb-3">✈️</p>
                        <p class="text-sm text-gray-500 dark:text-slate-400">No tour packages yet.</p>
                        <a href="{{ route('tour-travel.packages.create') }}"
                            class="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm">
                            Create First Package
                        </a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-[#0f172a]">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                        Package Code</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                        Name</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                        Destination</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                        Category</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                        Duration</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                        Price</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                        Bookings</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                                @foreach ($packages as $package)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-[#0f172a] transition">
                                        <td class="px-6 py-4">
                                            <a href="{{ route('tour-travel.packages.show', $package) }}"
                                                class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                                {{ $package->package_code }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700 dark:text-slate-300 font-medium">
                                            {{ $package->name }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                            {{ $package->destination }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400">
                                                {{ $package->category_label }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                            {{ $package->duration_days }}D/{{ $package->duration_nights }}N
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm">
                                                <p class="font-medium text-gray-900 dark:text-white">Rp
                                                    {{ number_format($package->price_per_person, 0, ',', '.') }}</p>
                                                @if ($package->profit_margin > 0)
                                                    <p class="text-xs text-green-600 dark:text-green-400">
                                                        {{ $package->profit_margin }}% margin</p>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                            {{ $package->bookings_count }} bookings
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $color = match ($package->status) {
                                                    'draft' => 'gray',
                                                    'active' => 'green',
                                                    'inactive' => 'yellow',
                                                    'archived' => 'red',
                                                    default => 'gray',
                                                };
                                            @endphp
                                            <span
                                                class="px-2 py-1 text-xs rounded-full bg-{{ $color }}-100 text-{{ $color }}-700 dark:bg-{{ $color }}-500/20 dark:text-{{ $color }}-400">
                                                {{ ucfirst($package->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex gap-2">
                                                <a href="{{ route('tour-travel.packages.show', $package) }}"
                                                    class="text-indigo-600 dark:text-indigo-400 hover:underline text-xs">View</a>
                                                <a href="{{ route('tour-travel.packages.edit', $package) }}"
                                                    class="text-blue-600 dark:text-blue-400 hover:underline text-xs">Edit</a>
                                                <form
                                                    action="{{ route('tour-travel.packages.toggle-status', $package) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="text-orange-600 dark:text-orange-400 hover:underline text-xs">
                                                        {{ $package->status === 'active' ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10">
                        {{ $packages->links() }}
                    </div>
                @endif
            </div>
</x-app-layout>
