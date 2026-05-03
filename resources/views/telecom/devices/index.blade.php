<x-app-layout>
    <x-slot name="header">
        {{ __('Network Devices') }}
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('Network Devices') }}</h1>
                    <p class="text-gray-600 mt-1">
                        {{ __('Kelola router, access point, dan network devices') }}</p>
                </div>
                <a href="{{ route('telecom.devices.create') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    {{ __('Tambah Device') }}
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Total Devices') }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-server text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Online') }}</p>
                            <p class="text-2xl font-bold text-green-600">{{ $stats['online'] }}</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Offline') }}</p>
                            <p class="text-2xl font-bold text-red-600">{{ $stats['offline'] }}</p>
                        </div>
                        <div class="bg-red-100 p-3 rounded-full">
                            <i class="fas fa-times-circle text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Maintenance') }}</p>
                            <p class="text-2xl font-bold text-yellow-600">
                                {{ $stats['maintenance'] }}</p>
                        </div>
                        <div class="bg-yellow-100 p-3 rounded-full">
                            <i class="fas fa-wrench text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters & Search -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 mb-6">
                <form method="GET" action="{{ route('telecom.devices.index') }}"
                    class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="md:col-span-2">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="{{ __('Cari device...') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <select name="status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">{{ __('Semua Status') }}</option>
                            <option value="online" {{ request('status') == 'online' ? 'selected' : '' }}>
                                {{ __('Online') }}</option>
                            <option value="offline" {{ request('status') == 'offline' ? 'selected' : '' }}>
                                {{ __('Offline') }}</option>
                            <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>
                                {{ __('Maintenance') }}</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                {{ __('Pending') }}</option>
                        </select>
                    </div>

                    <div>
                        <select name="brand"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">{{ __('Semua Brand') }}</option>
                            <option value="mikrotik" {{ request('brand') == 'mikrotik' ? 'selected' : '' }}>MikroTik
                            </option>
                            <option value="ubiquiti" {{ request('brand') == 'ubiquiti' ? 'selected' : '' }}>Ubiquiti
                            </option>
                            <option value="cisco" {{ request('brand') == 'cisco' ? 'selected' : '' }}>Cisco</option>
                            <option value="openwrt" {{ request('brand') == 'openwrt' ? 'selected' : '' }}>OpenWRT
                            </option>
                            <option value="other" {{ request('brand') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div>
                        <select name="type"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">{{ __('Semua Type') }}</option>
                            <option value="router" {{ request('type') == 'router' ? 'selected' : '' }}>
                                {{ __('Router') }}</option>
                            <option value="access_point" {{ request('type') == 'access_point' ? 'selected' : '' }}>
                                {{ __('Access Point') }}</option>
                            <option value="switch" {{ request('type') == 'switch' ? 'selected' : '' }}>
                                {{ __('Switch') }}</option>
                            <option value="firewall" {{ request('type') == 'firewall' ? 'selected' : '' }}>
                                {{ __('Firewall') }}</option>
                        </select>
                    </div>

                    <div class="md:col-span-5 flex gap-2">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                            <i class="fas fa-filter mr-2"></i>{{ __('Filter') }}
                        </button>
                        <a href="{{ route('telecom.devices.index') }}"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg">
                            <i class="fas fa-redo mr-2"></i>{{ __('Reset') }}
                        </a>
                    </div>
                </form>
            </div>

            <!-- Success/Error Messages -->
            @if (session('success'))
                <div
                    class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div
                    class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Devices Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Device') }}</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Brand/Model') }}</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('IP Address') }}</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Status') }}</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Subscriptions') }}</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Users') }}</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Last Seen') }}</th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($devices as $device)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if ($device->status === 'online')
                                                <div
                                                    class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                                    <i class="fas fa-server text-green-600"></i>
                                                </div>
                                            @elseif($device->status === 'offline')
                                                <div
                                                    class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center">
                                                    <i class="fas fa-times-circle text-red-600"></i>
                                                </div>
                                            @else
                                                <div
                                                    class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center">
                                                    <i class="fas fa-wrench text-yellow-600"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $device->name }}</div>
                                            <div class="text-sm text-gray-500">
                                                {{ ucfirst($device->device_type) }}</div>
                                            @if ($device->location)
                                                <div class="text-xs text-gray-400">
                                                    {{ $device->location }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ ucfirst($device->brand) }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $device->model ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 font-mono">
                                        {{ $device->ip_address }}</div>
                                    <div class="text-xs text-gray-500">Port: {{ $device->port }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($device->status === 'online')
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ __('Online') }}
                                        </span>
                                    @elseif($device->status === 'offline')
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ __('Offline') }}
                                        </span>
                                    @elseif($device->status === 'maintenance')
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            {{ __('Maintenance') }}
                                        </span>
                                    @else
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ __('Pending') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $device->subscriptions_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $device->hotspot_users_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : __('Never') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('telecom.devices.show', $device) }}"
                                            class="text-blue-600 hover:text-blue-900"
                                            title="{{ __('View Details') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('telecom.devices.edit', $device) }}"
                                            class="text-yellow-600 hover:text-yellow-900"
                                            title="{{ __('Edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('telecom.devices.destroy', $device) }}" method="POST"
                                            onsubmit="return confirm('{{ __('Yakin ingin menghapus device ini?') }}')"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-900"
                                                title="{{ __('Delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="text-gray-400">
                                        <i class="fas fa-server text-6xl"></i>
                                        <p class="mt-2 text-sm">{{ __('Belum ada device yang terdaftar') }}</p>
                                        <a href="{{ route('telecom.devices.create') }}"
                                            class="mt-2 inline-block text-blue-600 hover:text-blue-800">
                                            {{ __('Tambah device pertama') }} →
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                @if ($devices->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $devices->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
