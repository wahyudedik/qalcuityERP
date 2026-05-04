<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-clipboard-list text-blue-600"></i> Inventory Requests
            </h1>
            <p class="text-gray-500">Medical supply requisition and approval</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition" data-bs-toggle="modal" data-bs-target="#addRequestModal">
                <i class="fas fa-plus"></i> New Request
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-amber-300">
                <div class="p-5 text-center">
                    <h3 class="text-amber-600">{{ $requests->where('status', 'pending')->count() }}</h3>
                    <small class="text-gray-500">Pending</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-blue-300">
                <div class="p-5 text-center">
                    <h3 class="text-sky-600">{{ $requests->where('status', 'approved')->count() }}</h3>
                    <small class="text-gray-500">Approved</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-emerald-300">
                <div class="p-5 text-center">
                    <h3 class="text-emerald-600">{{ $requests->where('status', 'fulfilled')->count() }}</h3>
                    <small class="text-gray-500">Fulfilled</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-red-300">
                <div class="p-5 text-center">
                    <h3 class="text-red-600">{{ $requests->where('status', 'rejected')->count() }}</h3>
                    <small class="text-gray-500">Rejected</small>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Request #</th>
                                    <th>Date</th>
                                    <th>Requested By</th>
                                    <th>Department</th>
                                    <th>Items</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $request)
                                    <tr>
                                        <td><code>{{ $request->request_number }}</code></td>
                                        <td>{{ $request->created_at->format('d/m/Y') }}</td>
                                        <td>{{ $request->requested_by?->name ?? '-' }}</td>
                                        <td>{{ $request->department ?? '-' }}</td>
                                        <td><strong>{{ count($request->items ?? []) }} items</strong></td>
                                        <td>
                                            @if ($request->priority == 'urgent')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Urgent</span>
                                            @elseif($request->priority == 'high')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">High</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">Normal</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'approved' => 'info',
                                                    'fulfilled' => 'success',
                                                    'rejected' => 'danger',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$request->status] ?? 'secondary'  }}">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex gap-1">
                                                <button class="px-3 py-1.5 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-xs transition">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if ($request->status == 'pending')
                                                    <button class="px-3 py-1.5 border border-emerald-500 text-emerald-600 hover:bg-emerald-50 rounded-lg text-xs transition">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="px-3 py-1.5 border border-red-500 text-red-600 hover:bg-red-50 rounded-lg text-xs transition">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-6 text-gray-400">No requests found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $requests->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
