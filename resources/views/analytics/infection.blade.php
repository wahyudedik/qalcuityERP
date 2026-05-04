<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-virus text-blue-600"></i> Healthcare-Associated Infection Rate
            </h1>
            <p class="text-gray-500">Hospital-acquired infection tracking</p>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div class="w-full md:w-1/4">
                            <h2 class="text-red-600">{{ $stats['infection_rate'] ?? 0 }}%</h2>
                            <small class="text-gray-500">Overall HAI Rate</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h2 class="text-amber-600">{{ $stats['total_infections'] ?? 0 }}</h2>
                            <small class="text-gray-500">Total Infections</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h2 class="text-emerald-600">{{ $stats['total_admissions'] ?? 0 }}</h2>
                            <small class="text-gray-500">Total Admissions</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h2 class="text-sky-600">{{ $stats['target_rate'] ?? 0 }}%</h2>
                            <small class="text-gray-500">Target Rate</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full md:w-1/2">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">Infections by Type</h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Infection Type</th>
                                    <th>Cases</th>
                                    <th>Rate</th>
                                    <th>% of Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($infectionTypes ?? [] as $type)
                                    <tr>
                                        <td><strong>{{ $type['name'] }}</strong></td>
                                        <td>{{ $type['cases'] }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $type['rate'] > ($stats['target_rate'] ?? 2) ? 'red-500' : 'emerald-500'  }}">
                                                {{ $type['rate'] }}%
                                            </span>
                                        </td>
                                        <td>
                                            <div class="w-full bg-gray-200 rounded-full overflow-hidden" style="height: 20px;">
                                                <div class="bg-red-500 h-full rounded-full"
                                                    style="width: {{ $type['percentage'] }}%">
                                                    {{ $type['percentage'] }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-gray-400">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full md:w-1/2">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">Infections by Ward</h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Ward</th>
                                    <th>Infections</th>
                                    <th>Admissions</th>
                                    <th>Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($wardInfections ?? [] as $ward)
                                    <tr>
                                        <td>{{ $ward['name'] }}</td>
                                        <td>{{ $ward['infections'] }}</td>
                                        <td>{{ $ward['admissions'] }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $ward['rate'] > ($stats['target_rate'] ?? 2) ? 'red-500' : 'emerald-500'  }}">
                                                {{ $ward['rate'] }}%
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-gray-400">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
