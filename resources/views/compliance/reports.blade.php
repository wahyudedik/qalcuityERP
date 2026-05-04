<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-file-contract text-blue-600"></i> Compliance Reports
            </h1>
            <p class="text-gray-500">Regulatory compliance and audit reports</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <form method="GET" action="{{ route('compliance.reports.index') }}" class="grid grid-cols-1 md:grid-cols-2 gap-6 g-3">
                        <div class="w-full md:w-1/4">
                            <label class="form-label">Report Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Reports</option>
                                <option value="hipaa" {{ request('type') == 'hipaa' ? 'selected' : '' }}>HIPAA Compliance
                                </option>
                                <option value="data-protection"
                                    {{ request('type') == 'data-protection' ? 'selected' : '' }}>Data Protection</option>
                                <option value="access-control" {{ request('type') == 'access-control' ? 'selected' : '' }}>
                                    Access Control</option>
                                <option value="security" {{ request('type') == 'security' ? 'selected' : '' }}>Security
                                    Audit</option>
                            </select>
                        </div>
                        <div class="w-full md:w-1/4">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="w-full md:w-1/4">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="w-full md:w-1/4">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">
                                <i class="fas fa-filter"></i> Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-emerald-300">
                <div class="p-5 text-center">
                    <h3 class="text-emerald-600">{{ $stats['compliance_score'] ?? 0 }}%</h3>
                    <small class="text-gray-500">Overall Compliance Score</small>
                    <div class="w-full bg-gray-200 rounded-full overflow-hidden mt-2" style="height: 8px;">
                        <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $stats['compliance_score'] ?? 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-blue-300">
                <div class="p-5 text-center">
                    <h3 class="text-sky-600">{{ $stats['audits_passed'] ?? 0 }}</h3>
                    <small class="text-gray-500">Audits Passed</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-amber-300">
                <div class="p-5 text-center">
                    <h3 class="text-amber-600">{{ $stats['pending_reviews'] ?? 0 }}</h3>
                    <small class="text-gray-500">Pending Reviews</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-red-300">
                <div class="p-5 text-center">
                    <h3 class="text-red-600">{{ $stats['violations'] ?? 0 }}</h3>
                    <small class="text-gray-500">Violations</small>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full md:w-1/2">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">Compliance by Category</h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Score</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories ?? [] as $category)
                                    <tr>
                                        <td>{{ $category['name'] ?? '-' }}</td>
                                        <td>
                                            <div class="flex items-center">
                                                <div class="flex-1 bg-gray-200 rounded-full overflow-hidden mr-2" style="height: 8px;">
                                                    <div class="h-full rounded-full bg-{{ $category['score'] >= 90 ? 'emerald-500' : ($category['score'] >= 70 ? 'amber-500' : 'red-500')   }}"
                                                        style="width: {{ $category['score'] ?? 0 }}%"></div>
                                                </div>
                                                <strong>{{ $category['score'] ?? 0 }}%</strong>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($category['score'] >= 90)
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Compliant</span>
                                            @elseif($category['score'] >= 70)
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Partial</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Non-Compliant</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-gray-400">No data available</td>
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
                    <h5 class="mb-0">Recent Violations</h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Severity</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($violations ?? [] as $violation)
                                    <tr>
                                        <td>
                                            <small>{{ $violation['date'] ?? '-' }}</small>
                                        </td>
                                        <td>{{ $violation['type'] ?? '-' }}</td>
                                        <td>
                                            @if ($violation['severity'] == 'critical')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Critical</span>
                                            @elseif($violation['severity'] == 'high')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">High</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-700">Medium</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($violation['status'] == 'resolved')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Resolved</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Open</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-gray-400">No violations found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">Compliance Timeline</h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Report Date</th>
                                    <th>Report Type</th>
                                    <th>Compliance Score</th>
                                    <th>Auditor</th>
                                    <th>Findings</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports ?? [] as $report)
                                    <tr>
                                        <td>
                                            <strong>{{ $report['date'] ?? '-' }}</strong>
                                        </td>
                                        <td>
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">{{ $report['type'] ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <div class="flex items-center">
                                                <div class="flex-1 bg-gray-200 rounded-full overflow-hidden mr-2" style="height: 8px;">
                                                    <div class="h-full rounded-full bg-{{ $report['score'] >= 90 ? 'emerald-500' : ($report['score'] >= 70 ? 'amber-500' : 'red-500')   }}"
                                                        style="width: {{ $report['score'] ?? 0 }}%"></div>
                                                </div>
                                                <strong>{{ $report['score'] ?? 0 }}%</strong>
                                            </div>
                                        </td>
                                        <td>{{ $report['auditor'] ?? '-' }}</td>
                                        <td>{{ $report['findings'] ?? 0 }}</td>
                                        <td>
                                            <button class="px-3 py-1.5 bg-sky-500 hover:bg-sky-600 text-white rounded-lg text-xs transition" title="View Report">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs transition" title="Download"
                                                onclick="window.print()">
                                                <i class="fas fa-download"></i> Download
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-file-contract fa-2x text-gray-500 mb-2"></i>
                                            <p class="text-gray-500">No compliance reports generated</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-3">
        <div class="w-full">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Regulatory Requirements:</strong> Compliance reports must be reviewed quarterly and retained for a
                minimum of 7 years.
                All violations must be addressed within 30 days of discovery.
            </div>
        </div>
    </div>
</x-app-layout>
