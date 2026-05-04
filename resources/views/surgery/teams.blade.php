<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-users text-blue-600"></i> Surgery Teams
            </h1>
            <p class="text-gray-500">Manage surgical team assignments</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition" data-bs-toggle="modal" data-bs-target="#addTeamModal">
                <i class="fas fa-plus"></i> Create Team
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($teams as $team)
            <div class="w-full md:w-1/2">
                <div class="bg-white rounded-2xl border border-gray-200">
                    <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                        <strong>{{ $team->team_name ?? 'Surgery Team' }}</strong>
                        <span class="badge bg-{{ $team->is_active ? 'emerald-500' : 'secondary'  }}">
                            {{ $team->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="p-5">
                        <h6 class="text-blue-600">Team Members</h6>
                        <div class="mb-3">
                            @forelse($team->members ?? [] as $member)
                                <div class="flex justify-between mb-2 p-2 bg-gray-50 rounded">
                                    <div>
                                        <strong>{{ $member['role'] ?? '-' }}</strong>
                                        <br><small>{{ $member['name'] ?? '-' }}</small>
                                    </div>
                                    <span class="badge bg-{{ $member['available'] ? 'emerald-500' : 'red-500'  }}">
                                        {{ $member['available'] ? 'Available' : 'Busy' }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-gray-500 text-center">No members assigned</p>
                            @endforelse
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                            <div class="col-4">
                                <h5 class="text-emerald-600">{{ $team->surgeries_completed ?? 0 }}</h5>
                                <small class="text-gray-500">Completed</small>
                            </div>
                            <div class="col-4">
                                <h5 class="text-amber-600">{{ $team->surgeries_scheduled ?? 0 }}</h5>
                                <small class="text-gray-500">Scheduled</small>
                            </div>
                            <div class="col-4">
                                <h5 class="text-sky-600">{{ $team->success_rate ?? 0 }}%</h5>
                                <small class="text-gray-500">Success Rate</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="w-full">
                <div class="bg-white rounded-2xl border border-gray-200">
                    <div class="p-5 text-center py-10">
                        <i class="fas fa-users fa-3x text-gray-500 mb-3"></i>
                        <p class="text-gray-500">No surgery teams created yet</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</x-app-layout>
