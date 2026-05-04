<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-heartbeat text-blue-600"></i> Vital Signs Monitoring
            </h1>
            <p class="text-gray-500">Track patient vital signs over time</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition" data-bs-toggle="modal" data-bs-target="#addVitalsModal">
                <i class="fas fa-plus"></i> Record Vitals
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-6 g-2">
                        <div class="w-full md:w-1/3">
                            <select name="patient_id" class="form-select">
                                <option value="">All Patients</option>
                                @foreach ($patients ?? [] as $patient)
                                    <option value="{{ $patient->id }}"
                                        {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-1/4">
                            <input type="date" name="date" class="form-control"
                                value="{{ request('date', today()->format('Y-m-d')) }}">
                        </div>
                        <div class="w-full md:w-1/6">
                            <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                    </form>
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
                                    <th>Time</th>
                                    <th>Patient</th>
                                    <th>Temperature (°C)</th>
                                    <th>Heart Rate (bpm)</th>
                                    <th>Blood Pressure</th>
                                    <th>Resp Rate</th>
                                    <th>SpO2 (%)</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vitals as $vital)
                                    <tr>
                                        <td>{{ $vital->recorded_at?->format('H:i') ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('healthcare.patients.show', $vital->patient) }}">
                                                {{ $vital->patient?->name ?? '-' }}
                                            </a>
                                        </td>
                                        <td>
                                            @if ($vital->temperature)
                                                <span class="{{ $vital->temperature > 37.5 ? 'text-red-600 font-bold' : '' }}">
                                                    {{ $vital->temperature }}°C
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($vital->heart_rate)
                                                <span
                                                    class="{{ $vital->heart_rate > 100 || $vital->heart_rate < 60 ? 'text-red-600 font-bold' : '' }}">
                                                    {{ $vital->heart_rate }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($vital->blood_pressure)
                                                <span
                                                    class="{{ explode('/', $vital->blood_pressure)[0] > 140 ? 'text-red-600 font-bold' : '' }}">
                                                    {{ $vital->blood_pressure }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $vital->respiratory_rate ?? '-' }}</td>
                                        <td>
                                            @if ($vital->spo2)
                                                <span
                                                    class="{{ $vital->spo2 < 95 ? 'text-red-600 font-bold' : 'text-emerald-600' }}">
                                                    {{ $vital->spo2 }}%
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td><small>{{ Str::limit($vital->notes, 30) }}</small></td>
                                        <td>
                                            <div class="flex gap-1">
                                                <button class="px-3 py-1.5 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-xs transition">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="px-3 py-1.5 border border-amber-500 text-amber-600 hover:bg-amber-50 rounded-lg text-xs transition">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-6 text-gray-400">No vital signs recorded</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $vitals->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Add Vitals Modal -->
    <div class="modal fade" id="addVitalsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('healthcare.inpatient.vitals.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Record Vital Signs</h5>
                        <button type="button" class="text-gray-400 hover:text-gray-600" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Patient</label>
                            <select name="patient_id" class="form-select" required>
                                <option value="">Select patient</option>
                                @foreach ($patients ?? [] as $patient)
                                    <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Temperature (°C)</label>
                                <input type="number" name="temperature" class="form-control" step="0.1" min="30"
                                    max="45" placeholder="36.5">
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Heart Rate (bpm)</label>
                                <input type="number" name="heart_rate" class="form-control" min="30" max="250"
                                    placeholder="72">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Blood Pressure</label>
                                <input type="text" name="blood_pressure" class="form-control" placeholder="120/80">
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Respiratory Rate</label>
                                <input type="number" name="respiratory_rate" class="form-control" min="5"
                                    max="60" placeholder="16">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/2">
                                <label class="form-label">SpO2 (%)</label>
                                <input type="number" name="spo2" class="form-control" min="50" max="100"
                                    placeholder="98">
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Recorded At</label>
                                <input type="time" name="recorded_at" class="form-control"
                                    value="{{ now()->format('H:i') }}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">Save Vitals</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
