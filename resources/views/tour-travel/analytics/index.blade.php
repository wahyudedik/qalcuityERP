<x-app-layout>
    <x-slot name="header">
        <h1 class="text-base font-semibold text-gray-900">Tour & Travel Analytics</h1>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="mb-6">
                <p class="text-sm text-gray-600">Analisis komprehensif performa booking tour Anda</p>
            </div>

            {{-- Key Metrics --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="ml-4 flex-1 min-w-0">
                            <p class="text-xs text-gray-500 truncate">Total Bookings</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ number_format($bookingStats['total_bookings']) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-green-200 p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4 flex-1 min-w-0">
                            <p class="text-xs text-gray-500 truncate">Completed</p>
                            <p class="text-2xl font-bold text-green-600">
                                {{ number_format($bookingStats['completed_bookings']) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-purple-200 p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4 flex-1 min-w-0">
                            <p class="text-xs text-gray-500 truncate">Total Revenue</p>
                            <p class="text-2xl font-bold text-purple-600">
                                Rp {{ number_format($bookingStats['total_revenue'], 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-yellow-200 p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4 flex-1 min-w-0">
                            <p class="text-xs text-gray-500 truncate">Pending Revenue</p>
                            <p class="text-2xl font-bold text-yellow-600">
                                Rp {{ number_format($bookingStats['pending_revenue'], 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Charts Row --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {{-- Monthly Bookings Trend --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">📊 Tren Booking Bulanan</h3>
                    <div class="h-64">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>

                {{-- Booking Status Distribution --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">📈 Distribusi Status Booking
                    </h3>
                    <div class="h-64">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Top Packages Table --}}
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900">🏆 Paket Tour Terpopuler</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Nama Paket</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Bookings</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Revenue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($topPackages as $package)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <span class="font-medium text-gray-900">{{ $package->name }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">
                                        {{ $package->bookings_count }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">
                                        Rp {{ number_format($package->bookings_sum_total_amount ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $statusColor = $package->is_active ? 'green' : 'gray';
                                        @endphp
                                        <span
                                            class="px-2 py-1 text-xs rounded-full bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700 $statusColor }}-500/20 $statusColor }}-400">
                                            {{ $package->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        Belum ada data paket tour
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Popular Destinations --}}
            @if ($popularDestinations->count() > 0)
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-base font-semibold text-gray-900">🌍 Destinasi Populer</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Destinasi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Bookings</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($popularDestinations as $destination)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4">
                                            <span
                                                class="font-medium text-gray-900">{{ $destination->destination ?? 'N/A' }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $destination->bookings }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            Rp {{ number_format($destination->revenue ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const isDark = document.documentElement.classList.contains('dark');
                const textColor = isDark ? '#94a3b8' : '#6b7280';
                const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';

                // Monthly Bookings Chart (Chart.js)
                const monthlyData = @json($monthlyBookings);
                const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                const monthlyLabels = monthlyData.map(item => monthNames[item.month - 1] + ' ' + item.year);
                const monthlyTotals = monthlyData.map(item => item.total);
                const monthlyRevenue = monthlyData.map(item => parseFloat(item.revenue));

                const monthlyCtx = document.getElementById('monthlyChart');
                if (monthlyCtx) {
                    new Chart(monthlyCtx, {
                        type: 'bar',
                        data: {
                            labels: monthlyLabels,
                            datasets: [{
                                    label: 'Bookings',
                                    data: monthlyTotals,
                                    backgroundColor: isDark ? 'rgba(99, 102, 241, 0.7)' :
                                        'rgba(99, 102, 241, 0.8)',
                                    borderRadius: 4,
                                    yAxisID: 'y',
                                },
                                {
                                    label: 'Revenue (Rp)',
                                    data: monthlyRevenue,
                                    backgroundColor: isDark ? 'rgba(34, 197, 94, 0.7)' :
                                        'rgba(34, 197, 94, 0.8)',
                                    borderRadius: 4,
                                    yAxisID: 'y1',
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    labels: {
                                        color: textColor
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    ticks: {
                                        color: textColor
                                    },
                                    grid: {
                                        color: gridColor
                                    }
                                },
                                y: {
                                    type: 'linear',
                                    position: 'left',
                                    ticks: {
                                        color: textColor
                                    },
                                    grid: {
                                        color: gridColor
                                    }
                                },
                                y1: {
                                    type: 'linear',
                                    position: 'right',
                                    ticks: {
                                        color: textColor,
                                        callback: function(value) {
                                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                        }
                                    },
                                    grid: {
                                        drawOnChartArea: false
                                    }
                                }
                            }
                        }
                    });
                }

                // Status Distribution Chart (Chart.js Doughnut)
                const statusData = @json($statusDistribution);
                const statusLabels = statusData.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1));
                const statusCounts = statusData.map(item => item.count);
                const statusColors = statusData.map(item => {
                    const colorMap = {
                        'pending': '#eab308',
                        'confirmed': '#3b82f6',
                        'paid': '#22c55e',
                        'completed': '#6b7280',
                        'cancelled': '#ef4444',
                        'refunded': '#f97316',
                    };
                    return colorMap[item.status] || '#9ca3af';
                });

                const statusCtx = document.getElementById('statusChart');
                if (statusCtx) {
                    new Chart(statusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: statusLabels,
                            datasets: [{
                                data: statusCounts,
                                backgroundColor: statusColors,
                                borderWidth: isDark ? 0 : 2,
                                borderColor: isDark ? 'transparent' : '#ffffff',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: textColor,
                                        padding: 12
                                    }
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
