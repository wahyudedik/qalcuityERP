@extends('layouts.app')

@section('title', 'Scheduled Reports')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Scheduled Reports</h1>
                    <p class="mt-2 text-sm text-gray-600">Automate report generation and email delivery</p>
                </div>
                <div class="flex gap-3">
                    <button onclick="openCreateModal()"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-plus mr-2"></i>Create Schedule
                    </button>
                    <a href="{{ route('analytics.advanced') }}"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Scheduled Reports List -->
        @if (count($schedules) > 0)
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Report Name</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Frequency</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Metrics</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recipients</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Format</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Next Run</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Last Run</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($schedules as $schedule)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $schedule->name }}</div>
                                        @if ($schedule->description)
                                            <div class="text-xs text-gray-500">{{ $schedule->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($schedule->frequency == 'daily')
                                            <span
                                                class="px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">
                                                <i class="fas fa-calendar-day mr-1"></i>Daily
                                            </span>
                                        @elseif($schedule->frequency == 'weekly')
                                            <span
                                                class="px-2 py-1 text-xs font-semibold bg-purple-100 text-purple-800 rounded-full">
                                                <i class="fas fa-calendar-week mr-1"></i>Weekly
                                            </span>
                                        @else
                                            <span
                                                class="px-2 py-1 text-xs font-semibold bg-indigo-100 text-indigo-800 rounded-full">
                                                <i class="fas fa-calendar-alt mr-1"></i>Monthly
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($schedule->metrics as $metric)
                                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">
                                                    {{ ucfirst($metric) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            {{ count($schedule->recipients) }} recipient(s)
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ implode(', ', array_slice($schedule->recipients, 0, 2)) }}
                                            @if (count($schedule->recipients) > 2)
                                                +{{ count($schedule->recipients) - 2 }} more
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($schedule->format == 'pdf')
                                            <i class="fas fa-file-pdf text-red-500 text-xl"></i>
                                        @elseif($schedule->format == 'excel')
                                            <i class="fas fa-file-excel text-green-500 text-xl"></i>
                                        @else
                                            <i class="fas fa-file-csv text-blue-500 text-xl"></i>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($schedule->is_active)
                                            <span
                                                class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">
                                                Active
                                            </span>
                                        @else
                                            <span
                                                class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-800 rounded-full">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="text-sm text-gray-900">
                                            @if ($schedule->next_run)
                                                {{ $schedule->next_run->format('M d, Y') }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            @if ($schedule->next_run)
                                                {{ $schedule->next_run->format('H:i') }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="text-sm text-gray-900">
                                            @if ($schedule->last_run_at)
                                                {{ $schedule->last_run_at->diffForHumans() }}
                                            @else
                                                Never
                                            @endif
                                        </div>
                                        @if ($schedule->last_status)
                                            <div
                                                class="text-xs {{ $schedule->last_status == 'success' ? 'text-green-600' : 'text-red-600' }}">
                                                <i
                                                    class="fas fa-{{ $schedule->last_status == 'success' ? 'check-circle' : 'times-circle' }}"></i>
                                                {{ ucfirst($schedule->last_status) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button onclick="toggleSchedule({{ $schedule->id }})"
                                                class="px-3 py-1 text-xs {{ $schedule->is_active ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200' : 'bg-green-100 text-green-800 hover:bg-green-200' }} rounded transition">
                                                <i class="fas fa-{{ $schedule->is_active ? 'pause' : 'play' }}"></i>
                                                {{ $schedule->is_active ? 'Pause' : 'Resume' }}
                                            </button>
                                            <button onclick="deleteSchedule({{ $schedule->id }})"
                                                class="px-3 py-1 text-xs bg-red-100 text-red-800 hover:bg-red-200 rounded transition">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <i class="fas fa-clock text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Scheduled Reports</h3>
                <p class="text-gray-500 mb-6">Create your first scheduled report to automate report delivery</p>
                <button onclick="openCreateModal()"
                    class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-plus mr-2"></i>Create Your First Schedule
                </button>
            </div>
        @endif
    </div>

    <!-- Create Schedule Modal -->
    <div id="createModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-900">Create Scheduled Report</h3>
                    <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <form action="{{ route('analytics.create-scheduled-report') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <!-- Report Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Report Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Weekly Sales Summary"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                    <textarea name="description" rows="2" placeholder="Brief description of this report"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <!-- Metrics -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Metrics *</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label
                            class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-indigo-300">
                            <input type="checkbox" name="metrics[]" value="revenue" class="text-indigo-600" checked>
                            <span class="ml-2">Revenue</span>
                        </label>
                        <label
                            class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-indigo-300">
                            <input type="checkbox" name="metrics[]" value="orders" class="text-indigo-600" checked>
                            <span class="ml-2">Orders</span>
                        </label>
                        <label
                            class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-indigo-300">
                            <input type="checkbox" name="metrics[]" value="customers" class="text-indigo-600">
                            <span class="ml-2">Customers</span>
                        </label>
                        <label
                            class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-indigo-300">
                            <input type="checkbox" name="metrics[]" value="inventory" class="text-indigo-600">
                            <span class="ml-2">Inventory</span>
                        </label>
                    </div>
                </div>

                <!-- Frequency -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Frequency *</label>
                    <select name="frequency" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="daily">Daily</option>
                        <option value="weekly" selected>Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>

                <!-- Recipients -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Recipients *</label>
                    <input type="text" name="recipients_input" required
                        placeholder="email1@example.com, email2@example.com"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">Separate multiple emails with commas</p>
                </div>

                <!-- Format -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Export Format *</label>
                    <div class="grid grid-cols-3 gap-3">
                        <label
                            class="flex items-center p-3 border-2 border-indigo-600 bg-indigo-50 rounded-lg cursor-pointer">
                            <input type="radio" name="format" value="pdf" class="text-indigo-600" checked>
                            <div class="ml-2">
                                <i class="fas fa-file-pdf text-red-500 mr-1"></i>
                                <span>PDF</span>
                            </div>
                        </label>
                        <label
                            class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-indigo-300">
                            <input type="radio" name="format" value="excel" class="text-indigo-600">
                            <div class="ml-2">
                                <i class="fas fa-file-excel text-green-500 mr-1"></i>
                                <span>Excel</span>
                            </div>
                        </label>
                        <label
                            class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-indigo-300">
                            <input type="radio" name="format" value="csv" class="text-indigo-600">
                            <div class="ml-2">
                                <i class="fas fa-file-csv text-blue-500 mr-1"></i>
                                <span>CSV</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex gap-3 pt-4">
                    <button type="submit"
                        class="flex-1 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-semibold">
                        <i class="fas fa-save mr-2"></i>Create Schedule
                    </button>
                    <button type="button" onclick="closeCreateModal()"
                        class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
        }

        function toggleSchedule(id) {
            if (confirm('Are you sure you want to toggle this schedule?')) {
                // TODO: Implement toggle endpoint
                alert('Toggle functionality will be implemented');
            }
        }

        function deleteSchedule(id) {
            if (confirm('Are you sure you want to delete this schedule? This action cannot be undone.')) {
                // TODO: Implement delete endpoint
                alert('Delete functionality will be implemented');
            }
        }

        // Close modal on outside click
        document.getElementById('createModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCreateModal();
            }
        });
    </script>
@endpush
