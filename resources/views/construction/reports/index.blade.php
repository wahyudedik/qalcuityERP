@extends('layouts.app')

@section('title', 'Daily Site Reports')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Daily Site Reports</h1>
                <p class="text-sm text-gray-600 mt-1">Track daily construction progress and activities</p>
            </div>
            <a href="{{ route('construction.reports.create') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Report
            </a>
        </div>

        <!-- Project Filter -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" class="flex gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Project</label>
                    <select name="project_id" onchange="this.form.submit()"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Select Project --</option>
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}" {{ $selectedProject == $project->id ? 'selected' : '' }}>
                                {{ $project->name }} ({{ $project->number }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Period</label>
                    <select name="period" onchange="this.form.submit()"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="all" {{ $period === 'all' ? 'selected' : '' }}>All Time</option>
                    </select>
                </div>
            </form>
        </div>

        @if ($summary)
            <!-- Summary Stats -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                    <div class="text-sm text-gray-600">Total Reports</div>
                    <div class="text-2xl font-bold text-blue-700">{{ $summary['total_reports'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                    <div class="text-sm text-gray-600">Avg Progress</div>
                    <div class="text-2xl font-bold text-green-700">{{ number_format($summary['avg_progress'], 1) }}%</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
                    <div class="text-sm text-gray-600">Total Manpower</div>
                    <div class="text-2xl font-bold text-purple-700">{{ $summary['total_manpower'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
                    <div class="text-sm text-gray-600">Labor Cost</div>
                    <div class="text-2xl font-bold text-yellow-700">Rp
                        {{ number_format($summary['total_labor_cost'], 0, ',', '.') }}</div>
                </div>
                <div
                    class="bg-white rounded-lg shadow p-4 border-l-4 {{ $summary['safety_incidents'] > 0 ? 'border-red-500' : 'border-gray-500' }}">
                    <div class="text-sm text-gray-600">Safety Incidents</div>
                    <div
                        class="text-2xl font-bold {{ $summary['safety_incidents'] > 0 ? 'text-red-700' : 'text-gray-700' }}">
                        {{ $summary['safety_incidents'] }}</div>
                </div>
            </div>

            <!-- Weather Summary -->
            @if (!empty($summary['weather_summary']))
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Weather Conditions Summary</h3>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($summary['weather_summary'] as $weather => $count)
                            <div class="px-4 py-2 bg-gray-100 rounded-lg">
                                <span class="font-medium">{{ ucfirst($weather) }}</span>
                                <span class="text-gray-600 ml-2">{{ $count }} days</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Labor Analysis -->
            @if ($laborAnalysis)
                <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Labor Cost Analysis</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="text-center p-4 bg-blue-50 rounded-lg">
                                <div class="text-sm text-gray-600">Total Workers</div>
                                <div class="text-2xl font-bold text-blue-700">{{ $laborAnalysis['total_workers'] }}</div>
                            </div>
                            <div class="text-center p-4 bg-green-50 rounded-lg">
                                <div class="text-sm text-gray-600">Total Hours</div>
                                <div class="text-2xl font-bold text-green-700">
                                    {{ number_format($laborAnalysis['total_hours'], 1) }}</div>
                            </div>
                            <div class="text-center p-4 bg-purple-50 rounded-lg">
                                <div class="text-sm text-gray-600">Total Cost</div>
                                <div class="text-2xl font-bold text-purple-700">Rp
                                    {{ number_format($laborAnalysis['total_cost'], 0, ',', '.') }}</div>
                            </div>
                        </div>

                        @if (!empty($laborAnalysis['by_trade']))
                            <h4 class="font-medium text-gray-900 mb-3">By Trade</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Trade</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                                                Workers</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                                                Hours</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                                                Cost</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Avg
                                                Rate/Hr</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($laborAnalysis['by_trade'] as $trade => $data)
                                            <tr>
                                                <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                                    {{ ucfirst($trade ?? 'Unspecified') }}</td>
                                                <td class="px-4 py-2 text-sm text-right">{{ $data['count'] }}</td>
                                                <td class="px-4 py-2 text-sm text-right">
                                                    {{ number_format($data['total_hours'], 1) }}</td>
                                                <td class="px-4 py-2 text-sm text-right">Rp
                                                    {{ number_format($data['total_cost'], 0, ',', '.') }}</td>
                                                <td class="px-4 py-2 text-sm text-right">Rp
                                                    {{ number_format($data['avg_hourly_rate'], 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Recent Reports Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Reports</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reported By</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Progress</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Manpower</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Weather</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Photos</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentReports as $report)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        {{ $report->report_date->format('d M Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        {{ $report->reportedBy->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $report->progress_percentage }}%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        {{ $report->manpower_count }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm capitalize">
                                        {{ $report->weather_condition ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if (count($report->photos ?? []) > 0)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ count($report->photos) }} photos
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if ($report->status === 'approved')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Approved</span>
                                        @elseif($report->status === 'submitted')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending
                                                Approval</span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Draft</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <a href="{{ route('construction.reports.show', $report) }}"
                                            class="text-blue-600 hover:text-blue-900">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                        No reports found. Create your first daily site report!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($recentReports instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $recentReports->links() }}
                    </div>
                @endif
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No project selected</h3>
                <p class="mt-1 text-sm text-gray-500">Select a project to view daily site reports.</p>
            </div>
        @endif
    </div>
@endsection
