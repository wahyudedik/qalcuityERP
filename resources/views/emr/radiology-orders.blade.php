@extends('layouts.app')

@section('title', 'Radiology Orders')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-x-ray text-primary"></i> Radiology Orders
            </h1>
            <p class="text-muted mb-0">Manage radiology imaging orders</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOrderModal">
                <i class="fas fa-plus"></i> New Radiology Order
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
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
                                        <td>{{ $order->patient->name ?? '-' }}</td>
                                        <td>
                                            @php
                                                $icons = [
                                                    'X-Ray' => 'fa-x-ray',
                                                    'MRI' => 'fa-magnet',
                                                    'CT Scan' => 'fa-circle-notch',
                                                    'Ultrasound' => 'fa-wave-square',
                                                ];
                                            @endphp
                                            <i class="fas {{ $icons[$order->exam_type] ?? 'fa-x-ray' }} me-1"></i>
                                            {{ $order->exam_type ?? '-' }}
                                        </td>
                                        <td>{{ $order->body_part ?? '-' }}</td>
                                        <td>
                                            @if ($order->priority == 'stat')
                                                <span class="badge bg-danger">STAT</span>
                                            @elseif($order->priority == 'urgent')
                                                <span class="badge bg-warning">Urgent</span>
                                            @else
                                                <span class="badge bg-secondary">Routine</span>
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
                                            <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('healthcare.emr.radiology-orders.show', $order) }}"
                                                    class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No radiology orders found
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Patient</label>
                                <select name="patient_id" class="form-select" required>
                                    <option value="">Select patient</option>
                                    @foreach ($patients ?? [] as $patient)
                                        <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
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
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Body Part</label>
                                <input type="text" name="body_part" class="form-control"
                                    placeholder="e.g., Chest, Left Knee" required>
                            </div>
                            <div class="col-md-6 mb-3">
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
