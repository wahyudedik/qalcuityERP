<x-app-layout>
    <x-slot name="header">{{ __('Production Gantt Chart') }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <button onclick="optimizeSchedule()"
            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
            <i class="fas fa-magic mr-2"></i>Optimize
        </button>
        <button onclick="rescheduleOverdue()"
            class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition">
            <i class="fas fa-clock mr-2"></i>Fix Overdue
        </button>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Analytics Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Total Scheduled</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $analytics['total_scheduled'] }}</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">On-Time Rate</p>
                            <p class="text-2xl font-bold text-green-600">{{ $analytics['on_time_delivery_rate'] }}%</p>
                        </div>
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Overdue</p>
                            <p class="text-2xl font-bold text-red-600">{{ $analytics['overdue'] }}</p>
                        </div>
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Avg Variance</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $analytics['avg_schedule_variance_days'] }}d</p>
                        </div>
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-purple-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Date Filter --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                <form method="GET" class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Start
                            Date</label>
                        <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>

            {{-- Gantt Chart Container --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Production Schedule</h2>
                </div>

                <div class="overflow-x-auto">
                    <div class="p-6" id="gantt-chart" style="min-width: 1000px;">
                        @forelse($schedule['work_orders'] as $wo)
                            <div class="mb-4">
                                {{-- Work Order Info --}}
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="text-sm font-medium text-gray-900 w-32">{{ $wo['number'] }}</span>
                                    <span class="text-xs text-gray-600 flex-1">{{ $wo['product_name'] }}</span>

                                    {{-- Priority Badge --}}
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded
                            {{ $wo['priority'] == 1 ? 'bg-red-100 text-red-700' : '' }}
                            {{ $wo['priority'] == 2 ? 'bg-orange-100 text-orange-700' : '' }}
                            {{ $wo['priority'] == 3 ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $wo['priority'] == 4 ? 'bg-gray-100 text-gray-700' : '' }}">
                                        {{ $wo['priority_label'] }}
                                    </span>

                                    {{-- Progress --}}
                                    <span class="text-xs text-gray-600 w-16 text-right">{{ $wo['progress'] }}%</span>
                                </div>

                                {{-- Gantt Bar --}}
                                <div class="relative h-8 bg-gray-100 rounded-lg overflow-hidden ml-32">
                                    {{-- Planned Bar --}}
                                    <div class="absolute h-full bg-blue-500 rounded-lg opacity-30"
                                        style="left: 0%; width: 100%;"></div>

                                    {{-- Progress Bar --}}
                                    <div class="absolute h-full rounded-lg transition-all
                                    {{ $wo['is_overdue'] ? 'bg-red-500' : 'bg-green-500' }}"
                                        style="left: 0%; width: {{ $wo['progress'] }}%;"></div>

                                    {{-- Label --}}
                                    <div class="absolute inset-0 flex items-center px-3">
                                        <span class="text-xs font-medium text-white drop-shadow">{{ $wo['progress'] }}%
                                            Complete</span>
                                    </div>
                                </div>

                                {{-- Dates --}}
                                <div class="flex justify-between text-xs text-gray-500 ml-32 mt-1">
                                    <span>Start: {{ $wo['start'] }}</span>
                                    <span>End: {{ $wo['end'] }}</span>
                                    @if ($wo['is_overdue'])
                                        <span class="text-red-600 font-semibold"><i
                                                class="fas fa-exclamation-triangle mr-1"></i>Overdue</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500">No work orders scheduled for this period
                                </p>
                                <a href="{{ route('production.index') }}"
                                    class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    Create Work Order
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Priority Distribution --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Priority Distribution</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600"><i
                                    class="fas fa-circle text-red-600 mr-2"></i>Urgent</span>
                            <span
                                class="font-semibold text-gray-900">{{ $analytics['priority_distribution']['urgent'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600"><i
                                    class="fas fa-circle text-orange-600 mr-2"></i>High</span>
                            <span
                                class="font-semibold text-gray-900">{{ $analytics['priority_distribution']['high'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600"><i
                                    class="fas fa-circle text-blue-600 mr-2"></i>Normal</span>
                            <span
                                class="font-semibold text-gray-900">{{ $analytics['priority_distribution']['normal'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600"><i
                                    class="fas fa-circle text-gray-600 mr-2"></i>Low</span>
                            <span
                                class="font-semibold text-gray-900">{{ $analytics['priority_distribution']['low'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Schedule Summary</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Completed</span>
                            <span class="font-semibold text-green-600">{{ $analytics['completed'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">In Progress</span>
                            <span class="font-semibold text-blue-600">{{ $analytics['in_progress'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Pending</span>
                            <span class="font-semibold text-orange-600">{{ $analytics['pending'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">On-Time Rate</span>
                            <span
                                class="font-semibold text-purple-600">{{ $analytics['on_time_delivery_rate'] }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                async function optimizeSchedule() {
                    const confirmed = await Dialog.confirm(
                        'Optimize production schedule? This will analyze all pending work orders.');
                    if (!confirmed) return;

                    fetch('{{ route('production.gantt.optimize') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            Dialog.success(`Found ${data.total_optimizations} optimization opportunities`);
                            location.reload();
                        })
                        .catch(err => {
                            Dialog.warning('Error optimizing schedule');
                            console.error(err);
                        });
                }

                async function rescheduleOverdue() {
                    const confirmed = await Dialog.confirm('Reschedule all overdue work orders?');
                    if (!confirmed) return;

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('production.gantt.reschedule-overdue') }}';

                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);

                    document.body.appendChild(form);
                    form.submit();
                }
            </script>
        @endpush
</x-app-layout>
