<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-clipboard-check text-blue-600"></i> Stock Opname
            </h1>
            <p class="text-gray-500">Physical inventory count and reconciliation</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition" data-bs-toggle="modal" data-bs-target="#newOpnameModal">
                <i class="fas fa-plus"></i> New Stock Opname
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
                                    <th>Opname #</th>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Items Counted</th>
                                    <th>Discrepancies</th>
                                    <th>Status</th>
                                    <th>Conducted By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($opnames as $opname)
                                    <tr>
                                        <td><code>{{ $opname->opname_number }}</code></td>
                                        <td>{{ $opname->opname_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td>{{ ucfirst($opname->category ?? 'All') }}</td>
                                        <td>{{ $opname->items_counted ?? 0 }}</td>
                                        <td>
                                            @if ($opname->discrepancies > 0)
                                                <span class="text-red-600 font-bold">{{ $opname->discrepancies }}</span>
                                            @else
                                                <span class="text-emerald-600">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'draft' => 'secondary',
                                                    'in_progress' => 'warning',
                                                    'completed' => 'success',
                                                    'reconciled' => 'info',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$opname->status] ?? 'secondary'  }}">
                                                {{ ucfirst(str_replace('_', ' ', $opname->status)) }}
                                            </span>
                                        </td>
                                        <td>{{ $opname->conducted_by?->name ?? '-' }}</td>
                                        <td>
                                            <div class="flex gap-1">
                                                <a href="{{ route('healthcare.pharmacy.stock-opname.show', $opname) }}"
                                                    class="px-3 py-1.5 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-xs transition">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if ($opname->status != 'reconciled')
                                                    <button class="px-3 py-1.5 border border-emerald-500 text-emerald-600 hover:bg-emerald-50 rounded-lg text-xs transition">
                                                        <i class="fas fa-check"></i> Reconcile
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-6 text-gray-400">No stock opname records found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $opnames->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- New Stock Opname Modal -->
    <div class="modal fade" id="newOpnameModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('healthcare.pharmacy.stock-opname.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">New Stock Opname</h5>
                        <button type="button" class="text-gray-400 hover:text-gray-600" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="all">All Categories</option>
                                <option value="medications">Medications Only</option>
                                <option value="supplies">Medical Supplies</option>
                                <option value="equipment">Equipment</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Opname Date</label>
                            <input type="date" name="opname_date" class="form-control"
                                value="{{ today()->format('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">Create Opname</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
