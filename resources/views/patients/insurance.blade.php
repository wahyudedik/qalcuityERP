<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('healthcare.patients.index') }}">Patients</a></li>
                    <li class="breadcrumb-item"><a
                            href="{{ route('healthcare.patients.show', $patient) }}">{{ $patient->name }}</a></li>
                    <li class="breadcrumb-item active">Insurance</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-shield-alt text-blue-600"></i> Insurance Coverage
            </h1>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition" data-bs-toggle="modal" data-bs-target="#addInsuranceModal">
                <i class="fas fa-plus"></i> Add Insurance
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($insurances as $insurance)
            <div class="w-full md:w-1/2">
                <div class="bg-white rounded-2xl border border-gray-200">
                    <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="badge bg-{{ $insurance->is_active ? 'emerald-500' : 'red-500'  }} mr-2">
                                {{ $insurance->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <strong>{{ $insurance->insurance_provider ?? 'N/A' }}</strong>
                        </div>
                        <div class="flex gap-1">
                            <button class="px-3 py-1.5 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-xs transition">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                            <div class="w-1/2"><small class="text-gray-500">Policy Number</small></div>
                            <div class="w-1/2"><strong>{{ $insurance->policy_number ?? 'N/A' }}</strong></div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                            <div class="w-1/2"><small class="text-gray-500">Group Number</small></div>
                            <div class="w-1/2"><strong>{{ $insurance->group_number ?? 'N/A' }}</strong></div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                            <div class="w-1/2"><small class="text-gray-500">Plan Type</small></div>
                            <div class="w-1/2"><strong>{{ $insurance->plan_type ?? 'N/A' }}</strong></div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                            <div class="w-1/2"><small class="text-gray-500">Valid Period</small></div>
                            <div class="w-1/2">
                                <strong>
                                    @if ($insurance->valid_from && $insurance->valid_until)
                                        {{ $insurance->valid_from->format('d/m/Y') }} -
                                        {{ $insurance->valid_until->format('d/m/Y') }}
                                    @else
                                        N/A
                                    @endif
                                </strong>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                            <div class="w-1/2"><small class="text-gray-500">Coverage</small></div>
                            <div class="w-1/2"><strong>Rp
                                    {{ number_format($insurance->coverage_limit ?? 0, 0, ',', '.') }}</strong></div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-1/2"><small class="text-gray-500">Copay</small></div>
                            <div class="w-1/2"><strong>{{ $insurance->copay_percentage ?? 0 }}%</strong></div>
                        </div>
                    </div>
                    <div class="px-5 py-4 border-t border-gray-200 bg-gray-50">
                        <div class="w-full bg-gray-200 rounded-full overflow-hidden" style="height: 8px;">
                            <div class="h-full rounded-full bg-{{ $insurance->usage_percentage > 80 ? 'red-500' : ($insurance->usage_percentage > 50 ? 'amber-500' : 'emerald-500')   }}"
                                style="width: {{ $insurance->usage_percentage ?? 0 }}%"></div>
                        </div>
                        <small class="text-gray-500">Used: {{ $insurance->usage_percentage ?? 0 }}%</small>
                    </div>
                </div>
            </div>
        @empty
            <div class="w-full">
                <div class="bg-white rounded-2xl border border-gray-200">
                    <div class="p-5 text-center py-10">
                        <i class="fas fa-shield-alt fa-3x text-gray-500 mb-3"></i>
                        <p class="text-gray-500">No insurance policies added yet</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Add Insurance Modal -->
    <div class="modal fade" id="addInsuranceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('healthcare.patients.insurance.store', $patient) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Insurance Policy</h5>
                        <button type="button" class="text-gray-400 hover:text-gray-600" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Insurance Provider</label>
                            <input type="text" name="insurance_provider" class="form-control" required>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Policy Number</label>
                                <input type="text" name="policy_number" class="form-control" required>
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Group Number</label>
                                <input type="text" name="group_number" class="form-control">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Valid From</label>
                                <input type="date" name="valid_from" class="form-control" required>
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Valid Until</label>
                                <input type="date" name="valid_until" class="form-control" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Coverage Limit</label>
                                <input type="number" name="coverage_limit" class="form-control" step="0.01">
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Copay %</label>
                                <input type="number" name="copay_percentage" class="form-control" min="0"
                                    max="100">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">Add Insurance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
