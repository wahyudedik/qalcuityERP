<x-app-layout>
    <x-slot name="header">
        {{ __('Customer Subscriptions') }}
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Customer Subscriptions') }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Manage all customer internet subscriptions') }}</p>
                    </div>
                    <a href="{{ route('telecom.subscriptions.create') }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-700 dark:hover:bg-indigo-600">
                        <i class="fas fa-plus mr-2"></i>
                        {{ __('New Subscription') }}
                    </a>
                </div>
            </div>

            <!-- Filters & Stats -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 mb-6">
                <form method="GET" action="{{ route('telecom.subscriptions.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div>
                            <label for="search" class="sr-only">Cari</label>
                            <input type="text" name="search" id="search"
                                placeholder="Cari pelanggan..." value="{{ request('search') }}"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <select name="status" id="status" onchange="this.form.submit()"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">{{ __('All Status') }}</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                                    {{ __('Active') }}</option>
                                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>
                                    {{ __('Suspended') }}</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>
                                    {{ __('Cancelled') }}</option>
                            </select>
                        </div>

                        <!-- Package Filter -->
                        <div>
                            <select name="package_id" id="package_id" onchange="this.form.submit()"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">{{ __('All Packages') }}</option>
                                @foreach ($packages as $pkg)
                                    <option value="{{ $pkg->id }}"
                                        {{ request('package_id') == $pkg->id ? 'selected' : '' }}>
                                        {{ $pkg->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Device Filter -->
                        <div>
                            <select name="device_id" id="device_id" onchange="this.form.submit()"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">{{ __('All Devices') }}</option>
                                @foreach ($devices as $dev)
                                    <option value="{{ $dev->id }}"
                                        {{ request('device_id') == $dev->id ? 'selected' : '' }}>
                                        {{ $dev->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-green-100 dark:bg-green-900/30 rounded-md p-3">
                                    <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        {{ __('Active') }}</dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-white">
                                        {{ $stats['active'] ?? 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-yellow-100 dark:bg-yellow-900/30 rounded-md p-3">
                                    <i class="fas fa-pause-circle text-yellow-600 dark:text-yellow-400 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        {{ __('Suspended') }}</dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-white">
                                        {{ $stats['suspended'] ?? 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-red-100 dark:bg-red-900/30 rounded-md p-3">
                                    <i class="fas fa-times-circle text-red-600 dark:text-red-400 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        {{ __('Cancelled') }}</dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-white">
                                        {{ $stats['cancelled'] ?? 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-indigo-100 dark:bg-indigo-900/30 rounded-md p-3">
                                    <i class="fas fa-dollar-sign text-indigo-600 dark:text-indigo-400 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        {{ __('Monthly Revenue') }}</dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-white">Rp
                                        {{ number_format($stats['monthly_revenue'] ?? 0, 0, ',', '.') }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscriptions Table -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                @if ($subscriptions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {{ __('Customer') }}
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {{ __('Package') }}
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {{ __('Device') }}
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {{ __('Status') }}
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {{ __('Usage') }}
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {{ __('Next Billing') }}
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {{ __('Actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($subscriptions as $sub)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <!-- Customer -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div
                                                        class="h-10 w-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                                        <span
                                                            class="text-indigo-600 dark:text-indigo-400 font-semibold">
                                                            {{ substr($sub->customer?->name ?? '?', 0, 2) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ $sub->customer?->name ?? '-' }}
                                                    </div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $sub->customer?->email ?? '' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Package -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white">
                                                {{ $sub->package?->name ?? '-' }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $sub->package?->download_speed_mbps ?? 0 }}/{{ $sub->package?->upload_speed_mbps ?? 0 }}
                                                Mbps
                                            </div>
                                        </td>

                                        <!-- Device -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white">
                                                {{ $sub->device->name ?? 'N/A' }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $sub->device->ip_address ?? '-' }}</div>
                                        </td>

                                        <!-- Status -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($sub->status === 'active')
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                                    {{ __('Active') }}
                                                </span>
                                            @elseif($sub->status === 'suspended')
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400">
                                                    {{ __('Suspended') }}
                                                </span>
                                            @else
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400">
                                                    {{ __('Cancelled') }}
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Usage -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $quotaBytes = $sub->package->quota_bytes;
                                                $usedBytes = $sub->current_usage_bytes ?? 0;
                                                $percentage =
                                                    $quotaBytes > 0 ? round(($usedBytes / $quotaBytes) * 100, 1) : 0;
                                            @endphp
                                            <div class="text-sm text-gray-900 dark:text-white">
                                                {{ $percentage }}%
                                            </div>
                                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-1">
                                                <div class="bg-indigo-600 dark:bg-indigo-500 h-2 rounded-full"
                                                    style="width: {{ min($percentage, 100) }}%"></div>
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                {{ round($usedBytes / 1073741824, 2) }} GB /
                                                {{ $quotaBytes > 0 ? round($quotaBytes / 1073741824, 2) . ' GB' : __('Unlimited') }}
                                            </div>
                                        </td>

                                        <!-- Next Billing -->
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $sub->next_billing_date ? $sub->next_billing_date->format('d M Y') : '-' }}
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <a href="{{ route('telecom.subscriptions.show', $sub->id) }}"
                                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300"
                                                    title="{{ __('View Details') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                @if ($sub->status === 'active')
                                                    <form
                                                        action="{{ route('telecom.subscriptions.suspend', $sub->id) }}"
                                                        method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit"
                                                            class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-900 dark:hover:text-yellow-300"
                                                            title="{{ __('Suspend') }}"
                                                            onclick="return confirm('{{ __('Suspend this subscription?') }}')">
                                                            <i class="fas fa-pause-circle"></i>
                                                        </button>
                                                    </form>
                                                @elseif($sub->status === 'suspended')
                                                    <form
                                                        action="{{ route('telecom.subscriptions.reactivate', $sub->id) }}"
                                                        method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit"
                                                            class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300"
                                                            title="{{ __('Reactivate') }}"
                                                            onclick="return confirm('{{ __('Reactivate this subscription?') }}')">
                                                            <i class="fas fa-play-circle"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                <form action="{{ route('telecom.subscriptions.destroy', $sub->id) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                                        title="{{ __('Cancel Subscription') }}"
                                                        onclick="return confirm('{{ __('Are you sure? This will cancel the subscription permanently.') }}')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                        {{ $subscriptions->links() }}
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="text-center py-12">
                        <i class="fas fa-file-invoice text-gray-400 dark:text-gray-500 text-5xl mb-3"></i>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                            {{ __('No subscriptions found') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Get started by creating a new subscription.') }}</p>
                        <div class="mt-6">
                            <a href="{{ route('telecom.subscriptions.create') }}"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-700 dark:hover:bg-indigo-600">
                                <i class="fas fa-plus mr-2"></i>
                                {{ __('New Subscription') }}
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
