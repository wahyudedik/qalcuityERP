<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900 dark:text-white">IoT Device Management</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">ESP32 · Arduino · Raspberry Pi</p>
            </div>
            <a href="{{ route('iot.devices.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white rounded-lg transition">
                <i class="fas fa-plus"></i> Tambah Device
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg flex items-start gap-3">
                    <i class="fas fa-check-circle text-green-600 dark:text-green-400 mt-0.5"></i>
                    <div class="flex-1">
                        <p class="text-sm text-green-800 dark:text-green-300">{{ session('success') }}</p>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Device</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $devices->total() }}</p>
                        </div>
                        <div class="text-4xl text-blue-100 dark:text-blue-900">
                            <i class="fas fa-microchip"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Online</p>
                            <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-2">{{ $devices->where('is_connected', true)->count() }}</p>
                        </div>
                        <div class="text-4xl text-green-100 dark:text-green-900">
                            <i class="fas fa-wifi"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Aktif / Offline</p>
                            <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mt-2">{{ $devices->where('is_active', true)->where('is_connected', false)->count() }}</p>
                        </div>
                        <div class="text-4xl text-yellow-100 dark:text-yellow-900">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Nonaktif</p>
                            <p class="text-3xl font-bold text-gray-600 dark:text-gray-400 mt-2">{{ $devices->where('is_active', false)->count() }}</p>
                        </div>
                        <div class="text-4xl text-gray-100 dark:text-gray-700">
                            <i class="fas fa-power-off"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Devices Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Device</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Tipe</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Lokasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Module</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Terakhir Online</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Log</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($devices as $device)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $device->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $device->device_id }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $icons = ['esp32'=>'🔌','arduino'=>'⚡','raspberry_pi'=>'🍓','generic'=>'📡'];
                                    @endphp
                                    <span class="text-lg">{{ $icons[$device->device_type] ?? '📡' }}</span>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ \App\Models\IotDevice::deviceTypes()[$device->device_type] ?? $device->device_type }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $device->location ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                        {{ \App\Models\IotDevice::targetModules()[$device->target_module] ?? $device->target_module }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(!$device->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                            <i class="fas fa-circle text-gray-400 me-1"></i> Nonaktif
                                        </span>
                                    @elseif($device->is_connected)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                            <i class="fas fa-circle text-green-500 me-1"></i> Online
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">
                                            <i class="fas fa-circle text-yellow-500 me-1"></i> Offline
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'Belum pernah' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    {{ number_format($device->telemetry_logs_count) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <a href="{{ route('iot.devices.show', $device) }}" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 border border-blue-200 dark:border-blue-800 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                                        <i class="fas fa-eye me-1"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-microchip text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                        <p class="text-gray-600 dark:text-gray-400 mb-2">Belum ada device IoT</p>
                                        <a href="{{ route('iot.devices.create') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">Tambah sekarang</a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $devices->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
