<x-app-layout>
    <x-slot name="header">{{ $group->name }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('consolidation.index') }}" class="text-gray-600 hover:text-gray-900">
                ← Kembali
            </a>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="text-sm text-gray-600">Member Companies</div>
                    <div class="text-2xl font-bold">{{ $group->members->count() }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="text-sm text-gray-600">Consolidation Reports</div>
                    <div class="text-2xl font-bold">{{ $reports->total() }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="text-sm text-gray-600">Currency</div>
                    <div class="text-2xl font-bold">{{ $group->currency_code }}</div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Quick Actions</h3>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <a href="{{ route('consolidation.report.generate', $group) }}" class="border-2 border-blue-600 text-blue-600 hover:bg-blue-50 px-4 py-3 rounded-lg text-center">
                            📊 Generate Report
                        </a>
                        <a href="{{ route('consolidation.master-accounts', $group) }}" class="border-2 border-gray-300 hover:bg-gray-50 px-4 py-3 rounded-lg text-center">
                            📋 Master COA
                        </a>
                        <a href="{{ route('consolidation.mappings', $group) }}" class="border-2 border-gray-300 hover:bg-gray-50 px-4 py-3 rounded-lg text-center">
                            🔗 Account Mapping
                        </a>
                        <a href="{{ route('consolidation.eliminations', $group) }}" class="border-2 border-gray-300 hover:bg-gray-50 px-4 py-3 rounded-lg text-center">
                            ✂️ Eliminations
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Member Companies</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Business Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($group->members as $member)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $member->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded {{ $member->pivot->role === 'owner' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ ucfirst($member->pivot->role) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $member->businessTypeLabel() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($member->pivot->role !== 'owner')
                                                <form action="{{ route('consolidation.members.remove', [$group, $member->id]) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Hapus member ini?')">
                                                        Remove
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">Belum ada member</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Recent Consolidation Reports</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Report Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Generated</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($reports as $report)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $report->getReportTypeLabel() }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ $report->period_start->format('d M Y') }} - {{ $report->period_end->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded {{ $report->status === 'finalized' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ ucfirst($report->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $report->created_at->format('d M Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <a href="{{ route('consolidation.report.show', [$group, $report]) }}" class="text-blue-600 hover:text-blue-900">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">Belum ada laporan konsolidasi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $reports->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
