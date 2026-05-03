@extends('layouts.app')

@section('title', 'Expiry Management Dashboard')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Expiry Management</h1>
                    <p class="mt-1 text-sm text-gray-500">Monitor expiry dates, alerts & batch recalls</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('cosmetic.expiry.recalls') }}"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition">
                        Batch Recalls
                    </a>
                    <a href="{{ route('cosmetic.expiry.reports') }}"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        Expiry Reports
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">Total Alerts</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_alerts'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">Unread</p>
                <p class="text-2xl font-bold text-orange-600">{{ $stats['unread_alerts'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">Expired</p>
                <p class="text-2xl font-bold text-red-600">{{ $stats['expired_batches'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">Critical</p>
                <p class="text-2xl font-bold text-red-800">{{ $stats['critical_alerts'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">Active Recalls</p>
                <p class="text-2xl font-bold text-purple-600">{{ $stats['active_recalls'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">Total Recalls</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_recalls'] }}</p>
            </div>
        </div>

        <!-- Alerts by Severity -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Alerts by Severity</h2>
            <div class="grid grid-cols-4 gap-4">
                <div class="p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-600">Info</p>
                    <p class="text-3xl font-bold text-blue-900">{{ $alertsBySeverity['info'] ?? 0 }}</p>
                </div>
                <div class="p-4 bg-yellow-50 rounded-lg">
                    <p class="text-sm text-yellow-600">Warning</p>
                    <p class="text-3xl font-bold text-yellow-900">{{ $alertsBySeverity['warning'] ?? 0 }}</p>
                </div>
                <div class="p-4 bg-orange-50 rounded-lg">
                    <p class="text-sm text-orange-600">Critical</p>
                    <p class="text-3xl font-bold text-orange-900">{{ $alertsBySeverity['critical'] ?? 0 }}</p>
                </div>
                <div class="p-4 bg-red-50 rounded-lg">
                    <p class="text-sm text-red-600">Expired</p>
                    <p class="text-3xl font-bold text-red-900">{{ $alertsBySeverity['expired'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Alerts -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">🔔 Recent Alerts</h2>
                </div>
                <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                    @forelse($alerts as $alert)
                        <div class="p-4 {{ !$alert->is_read ? 'bg-yellow-50' : '' }}">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="px-2 py-0.5 text-xs font-medium rounded 
                                        @if ($alert->severity == 'expired') bg-red-100 text-red-800
                                        @elseif($alert->severity == 'critical') bg-orange-100 text-orange-800
                                        @elseif($alert->severity == 'warning') bg-yellow-100 text-yellow-800
                                        @else bg-blue-100 text-blue-800 @endif">
                                            {{ $alert->severity_label }}
                                        </span>
                                        <span class="text-sm font-medium text-gray-900">{{ $alert->type_label }}</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ $alert->batch->batch_number ?? 'N/A' }} -
                                        {{ $alert->days_until_expiry < 0 ? 'Expired ' . abs($alert->days_until_expiry) . ' days ago' : $alert->days_until_expiry . ' days remaining' }}
                                    </p>
                                </div>
                                @if (!$alert->is_read)
                                    <form method="POST" action="{{ route('cosmetic.expiry.alerts.read', $alert->id) }}"
                                        class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs text-blue-600 hover:text-blue-900">Mark
                                            Read</button>
                                    </form>
                                @endif
                            </div>
                            @if (!$alert->is_actioned)
                                <div class="mt-2 flex gap-2">
                                    <form method="POST" action="{{ route('cosmetic.expiry.alerts.action', $alert->id) }}"
                                        class="inline">
                                        @csrf
                                        <input type="hidden" name="action" value="discounted">
                                        <button type="submit"
                                            class="px-2 py-1 bg-orange-100 hover:bg-orange-200 text-orange-800 text-xs rounded">Discount</button>
                                    </form>
                                    <form method="POST" action="{{ route('cosmetic.expiry.alerts.action', $alert->id) }}"
                                        class="inline">
                                        @csrf
                                        <input type="hidden" name="action" value="disposed">
                                        <button type="submit"
                                            class="px-2 py-1 bg-red-100 hover:bg-red-200 text-red-800 text-xs rounded">Dispose</button>
                                    </form>
                                </div>
                            @else
                                <p class="text-xs text-gray-500 mt-2">Actioned: {{ ucfirst($alert->action_taken) }}</p>
                            @endif
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-500">No alerts</div>
                    @endforelse
                </div>
                @if ($alerts->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">{{ $alerts->links() }}</div>
                @endif
            </div>

            <!-- Batches Expiring Soon -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">⏰ Expiring Soon (90 days)</h2>
                </div>
                <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                    @forelse($expiringSoon as $batch)
                        <div class="p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $batch->batch_number }}</p>
                                    <p class="text-xs text-gray-600">{{ $batch->formula->formula_name ?? 'N/A' }}</p>
                                </div>
                                <div class="text-right">
                                    <p
                                        class="text-sm font-semibold {{ $batch->expiry_date->diffInDays(now()) <= 30 ? 'text-red-600' : 'text-orange-600' }}">
                                        {{ $batch->expiry_date->format('d M Y') }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $batch->expiry_date->diffInDays(now()) }} days left
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-500">No batches expiring soon</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

@endsection
