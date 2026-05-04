<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-microscope text-blue-600"></i> Laboratory Equipment
            </h1>
            <p class="text-gray-500">Manage laboratory equipment and maintenance</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">
                <i class="fas fa-plus"></i> Add Equipment
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div class="w-full md:w-1/4">
                            <h3 class="text-emerald-600">{{ $equipment->where('status', 'active')->count() }}</h3>
                            <small class="text-gray-500">Active</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h3 class="text-amber-600">{{ $equipment->where('status', 'maintenance')->count() }}</h3>
                            <small class="text-gray-500">Maintenance</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h3 class="text-red-600">{{ $equipment->where('status', 'out_of_order')->count() }}</h3>
                            <small class="text-gray-500">Out of Order</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h3 class="text-sky-600">{{ $equipment->where('auto_polling', true)->count() }}</h3>
                            <small class="text-gray-500">Auto-Polling</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($equipment as $device)
            <div class="w-full md:w-1/2">
                <div class="bg-white rounded-2xl border border-gray-200">
                    <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <strong>{{ $device->name }}</strong>
                            <span
                                class="badge bg-{{ $device->status == 'active' ? 'emerald-500' : ($device->status == 'maintenance' ? 'amber-500' : 'red-500')  }} ml-2">
                                {{ ucfirst(str_replace('_', ' ', $device->status)) }}
                            </span>
                        </div>
                        <div class="flex gap-1">
                            <button class="px-3 py-1.5 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-xs transition">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                            <div class="w-1/2"><small class="text-gray-500">Manufacturer</small></div>
                            <div class="w-1/2"><strong>{{ $device->manufacturer ?? 'N/A' }}</strong></div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                            <div class="w-1/2"><small class="text-gray-500">Model</small></div>
                            <div class="w-1/2"><strong>{{ $device->model ?? 'N/A' }}</strong></div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                            <div class="w-1/2"><small class="text-gray-500">Serial Number</small></div>
                            <div class="w-1/2"><code>{{ $device->serial_number ?? 'N/A' }}</code></div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                            <div class="w-1/2"><small class="text-gray-500">Location</small></div>
                            <div class="w-1/2"><strong>{{ $device->location ?? 'N/A' }}</strong></div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                            <div class="w-1/2"><small class="text-gray-500">Last Calibration</small></div>
                            <div class="w-1/2">
                                <strong>
                                    @if ($device->last_calibration_date)
                                        {{ $device->last_calibration_date->format('d/m/Y') }}
                                    @else
                                        N/A
                                    @endif
                                </strong>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                            <div class="w-1/2"><small class="text-gray-500">Next Calibration</small></div>
                            <div class="w-1/2">
                                @if ($device->next_calibration_date)
                                    @if ($device->next_calibration_date->isPast())
                                        <strong
                                            class="text-red-600">{{ $device->next_calibration_date->format('d/m/Y') }}</strong>
                                    @else
                                        <strong>{{ $device->next_calibration_date->format('d/m/Y') }}</strong>
                                    @endif
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-1/2"><small class="text-gray-500">Auto Polling</small></div>
                            <div class="w-1/2">
                                @if ($device->auto_polling)
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Enabled</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">Disabled</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if ($device->auto_polling)
                        <div class="px-5 py-4 border-t border-gray-200 bg-gray-50">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                                <div class="w-1/2">
                                    <small class="text-gray-500">Last Polled</small>
                                    <br><strong>{{ $device->last_polled_at?->diffForHumans() ?? 'Never' }}</strong>
                                </div>
                                <div class="w-1/2">
                                    <small class="text-gray-500">Poll Interval</small>
                                    <br><strong>{{ $device->poll_interval_minutes ?? 30 }} min</strong>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="w-full">
                <div class="bg-white rounded-2xl border border-gray-200">
                    <div class="p-5 text-center py-10">
                        <i class="fas fa-microscope fa-3x text-gray-500 mb-3"></i>
                        <p class="text-gray-500">No laboratory equipment registered</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Add Equipment Modal -->
    <div class="modal fade" id="addEquipmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('healthcare.laboratory.equipment.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Laboratory Equipment</h5>
                        <button type="button" class="text-gray-400 hover:text-gray-600" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Equipment Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Manufacturer</label>
                                <input type="text" name="manufacturer" class="form-control">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Model</label>
                                <input type="text" name="model" class="form-control">
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Serial Number</label>
                                <input type="text" name="serial_number" class="form-control">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-control"
                                    placeholder="e.g., Lab Room 1">
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="out_of_order">Out of Order</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="auto_polling" id="autoPolling">
                            <label class="form-check-label" for="autoPolling">Enable Auto Polling</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">Add Equipment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
