<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    <i class="fas fa-exclamation-triangle mr-2 text-red-600"></i>Recall Management
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Manage product recalls and batch expirations</p>
            </div>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('cosmetic.recall.auto-expire') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition"
                        onclick="return confirm('Auto-expire all expired batches?')">
                        <i class="fas fa-clock mr-2"></i>Auto-Expire Batches
                    </button>
                </form>
                <a href="{{ route('cosmetic.recall.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i>New Recall
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Recalls</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Recalls</div>
                    <div class="mt-2 text-3xl font-bold text-orange-600">{{ $stats['active'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Critical</div>
                    <div class="mt-2 text-3xl font-bold text-red-600">{{ $stats['critical'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Resolution Rate</div>
                    <div class="mt-2 text-3xl font-bold text-green-600">{{ $stats['resolution_rate'] }}%</div>
                </div>
            </div>

            <!-- Expiry Alerts -->
            @if ($expiryInfo['expiring_count'] > 0 || $expiryInfo['expired_count'] > 0)
                <div class="space-y-4">
                    @if ($expiryInfo['expiring_count'] > 0)
                        <div
                            class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                            <div class="flex items-start">
                                <i
                                    class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400 mt-1 mr-3"></i>
                                <div>
                                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-300">
                                        {{ $expiryInfo['expiring_count'] }} Batch(es) Expiring Within 90 Days
                                    </h3>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($expiryInfo['expired_count'] > 0)
                        <div
                            class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                            <div class="flex items-start">
                                <i class="fas fa-times-circle text-red-600 dark:text-red-400 mt-1 mr-3"></i>
                                <div>
                                    <h3 class="text-sm font-medium text-red-800 dark:text-red-300">
                                        {{ $expiryInfo['expired_count'] }} Batch(es) Expired
                                    </h3>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Active Recalls -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Active Recalls</h3>
                </div>

                <div class="overflow-x-auto">
                    @if ($activeRecalls->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        Recall #</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        Product</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        Severity</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        Type</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        Affected Units</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($activeRecalls as $recall)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-red-600 dark:text-red-400">
                                                {{ $recall->recall_number }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $recall->start_date->format('d M Y') }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $recall->product->formula_name ?? 'Unknown' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full
                                        @if ($recall->severity == 'critical') bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300
                                        @elseif($recall->severity == 'major') bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300
                                        @else bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 @endif">
                                                {{ ucfirst($recall->severity) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full
                                        @if ($recall->recall_type == 'mandatory') bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300
                                        @else bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 @endif">
                                                {{ ucfirst($recall->recall_type) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white">
                                                {{ number_format($recall->affected_units) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full
                                        @if ($recall->status == 'initiated') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300
                                        @else bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 @endif">
                                                {{ ucfirst(str_replace('_', ' ', $recall->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('cosmetic.recall.show', $recall) }}"
                                                class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-check-circle text-4xl mb-2 text-green-500"></i>
                            <p>No active recalls</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
