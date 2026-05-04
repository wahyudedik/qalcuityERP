<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-x-ray text-blue-600"></i> Radiology Orders
            </h1>
            <p class="text-gray-500">Manage radiology imaging orders</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition" data-bs-toggle="modal" data-bs-target="#addOrderModal">
                <i class="fas fa-plus"></i> New Radiology Order
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Exam Type</th>
                                    <th>Body Part</th>
                                    <th>Priority</th>
                                    <th>Ordered By</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr>
                                        <td><code>{{ $order->order_number }}</code></td>
                                        <td>{{ $order->order_date?->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td>{{ $order->patient?->name ?? '-' }}</td>
                                        <td>
                                            @php
                                                $icons = [
                                                    'X-Ray' => 'fa-x-ray',
                                                    'MRI' => 'fa-magnet',
                                                    'CT Scan' => 'fa-circle-notch',
                                                    'Ultrasound' => 'fa-wave-square',
                                                ];
                                            @endphp
                                            <i class="fas {{ $icons[$order->exam_type] ?? 'fa-x-ray' }} mr-1"></i>
                                            {{ $order->exam_type ?? '-' }}
                                        </td>
                                        <td>{{ $order->body_part ?? '-' }}</td>
                                        <td>
                                            @if ($order->priority == 'stat')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">STAT</span>
                                            @elseif($order->priority == 'urgent')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Urgent</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">Routine</span>
                                            @endif
                                        </td>
                                        <td>{{ $order->ordered_by?->name ?? '-' }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'info',
                                                    'in_progress' => 'warning',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary'  }}">
                                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex gap-1">
                                                <a href="{{ route('healthcare.emr.radiology-orders.show', $order) }}"
                                                    class="px-3 py-1.5 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-xs transition">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-6 text-gray-400">No radiology orders found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- New Radiology Order Modal -->
    <div class="modal fade" id="addOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('healthcare.emr.radiology-orders.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">New Radiology Order</h5>
                        <button type="button" class="text-gray-400 hover:text-gray-600" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Patient</label>
                                <select name="patient_id" class="form-select" required>
                                    <option value="">Select patient</option>
                                    @foreach ($patients ?? [] as $patient)
                                        <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Exam Type</label>
                                <select name="exam_type" class="form-select" required>
                                    <option value="">Select exam type</option>
                                    <option value="X-Ray">X-Ray</option>
                                    <option value="MRI">MRI</option>
                                    <option value="CT Scan">CT Scan</option>
                                    <option value="Ultrasound">Ultrasound</option>
                                    <option value="Mammography">Mammography</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Body Part</label>
                                <input type="text" name="body_part" class="form-control"
                                    placeholder="e.g., Chest, Left Knee" required>
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select" required>
                                    <option value="routine">Routine</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="stat">STAT</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Clinical Indication</label>
                            <textarea name="clinical_indication" class="form-control" rows="3" placeholder="Reason for exam..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">Create Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
