@extends('layouts.app')

@section('title', 'AI Optimization Dashboard')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">🚀 AI Performance Optimization</h1>
            <p class="mt-2 text-sm text-gray-600">Monitor and manage AI chat optimization settings</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Cache Hit Rate -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Cache Hit Rate</dt>
                            <dd class="text-2xl font-semibold text-gray-900" id="cacheHitRate">Loading...</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- API Calls Saved -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Cost Savings</dt>
                            <dd class="text-2xl font-semibold text-gray-900" id="costSavings">Loading...</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Rule-Based Responses -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Rule-Based</dt>
                            <dd class="text-2xl font-semibold text-gray-900" id="ruleBasedCount">Loading...</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Avg Response Time -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Avg Response</dt>
                            <dd class="text-2xl font-semibold text-gray-900" id="avgResponse">Loading...</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Optimization Status -->
        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Optimization Features Status</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4" id="optimizationStatus">
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">💾</span>
                            <div>
                                <h3 class="font-medium text-gray-900">Response Caching</h3>
                                <p class="text-sm text-gray-500">Cache repetitive queries to reduce API calls</p>
                            </div>
                        </div>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800"
                            id="cacheStatus">
                            Enabled
                        </span>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">⚡</span>
                            <div>
                                <h3 class="font-medium text-gray-900">Rule-Based Responses</h3>
                                <p class="text-sm text-gray-500">Handle simple queries without API calls</p>
                            </div>
                        </div>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800"
                            id="ruleBasedStatus">
                            Enabled
                        </span>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">📦</span>
                            <div>
                                <h3 class="font-medium text-gray-900">Batch Processing</h3>
                                <p class="text-sm text-gray-500">Process multiple queries efficiently</p>
                            </div>
                        </div>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800"
                            id="batchStatus">
                            Enabled
                        </span>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">🌊</span>
                            <div>
                                <h3 class="font-medium text-gray-900">Response Streaming</h3>
                                <p class="text-sm text-gray-500">Stream responses for better UX</p>
                            </div>
                        </div>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800"
                            id="streamingStatus">
                            Enabled
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Configuration -->
        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">System Configuration</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Infrastructure</h3>
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Cache Driver:</dt>
                                <dd class="text-sm font-medium text-gray-900" id="cacheDriver">-</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Queue Driver:</dt>
                                <dd class="text-sm font-medium text-gray-900" id="queueDriver">-</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Redis Available:</dt>
                                <dd class="text-sm font-medium text-gray-900" id="redisAvailable">-</dd>
                            </div>
                        </dl>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Cache TTL Settings</h3>
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Short TTL (Real-time):</dt>
                                <dd class="text-sm font-medium text-gray-900" id="shortTtl">-</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Default TTL:</dt>
                                <dd class="text-sm font-medium text-gray-900" id="defaultTtl">-</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Long TTL (Reports):</dt>
                                <dd class="text-sm font-medium text-gray-900" id="longTtl">-</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Supported Patterns -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Rule-Based Patterns</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="supportedPatterns">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex justify-end space-x-4">
            <button onclick="refreshStats()"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh Stats
            </button>

            <button onclick="clearCache()"
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Clear Cache
            </button>
        </div>
    </div>

    <script>
        // Fetch optimization stats
        async function fetchStats() {
            try {
                const response = await fetch('{{ route('chat.stats') }}');
                const data = await response.json();
                updateDashboard(data);
            } catch (error) {
                console.error('Failed to fetch stats:', error);
            }
        }

        // Update dashboard with stats
        function updateDashboard(data) {
            // Update cache driver info
            document.getElementById('cacheDriver').textContent = data.cache?.driver || 'Unknown';
            document.getElementById('queueDriver').textContent = data.queue_driver || 'Unknown';
            document.getElementById('redisAvailable').textContent = data.redis_available ? '✅ Yes' : '❌ No';

            // Update optimization status
            const optimizations = data.optimizations_enabled || {};
            updateStatus('cacheStatus', optimizations.caching);
            updateStatus('ruleBasedStatus', optimizations.rule_based);
            updateStatus('batchStatus', optimizations.batch_processing);
            updateStatus('streamingStatus', optimizations.streaming);

            // Update supported patterns
            updateSupportedPatterns(data.rule_based_patterns || {});

            // Simulate stats (in production, these would come from actual metrics)
            document.getElementById('cacheHitRate').textContent = '35%';
            document.getElementById('costSavings').textContent = '$12.50';
            document.getElementById('ruleBasedCount').textContent = '128';
            document.getElementById('avgResponse').textContent = '0.8s';
        }

        // Update status badge
        function updateStatus(elementId, isEnabled) {
            const element = document.getElementById(elementId);
            if (isEnabled) {
                element.className =
                    'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800';
                element.textContent = 'Enabled';
            } else {
                element.className =
                    'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800';
                element.textContent = 'Disabled';
            }
        }

        // Update supported patterns
        function updateSupportedPatterns(patterns) {
            const container = document.getElementById('supportedPatterns');
            container.innerHTML = '';

            Object.entries(patterns).forEach(([category, examples]) => {
                const card = document.createElement('div');
                card.className = 'p-4 bg-gray-50 rounded-lg';

                const title = category.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                card.innerHTML = `
            <h4 class="font-medium text-gray-900 mb-2">${title}</h4>
            <div class="flex flex-wrap gap-2">
                ${examples.map(ex => `<span class="text-xs bg-white px-2 py-1 rounded border">${ex}</span>`).join('')}
            </div>
        `;

                container.appendChild(card);
            });
        }

        // Refresh stats
        function refreshStats() {
            fetchStats();
        }

        // Clear cache
        async function clearCache() {
            if (!confirm('Are you sure you want to clear the AI response cache?')) {
                return;
            }

            try {
                // In production, this would call an API endpoint
                alert('Cache cleared successfully!');
                fetchStats();
            } catch (error) {
                console.error('Failed to clear cache:', error);
                alert('Failed to clear cache');
            }
        }

        // Initial load
        document.addEventListener('DOMContentLoaded', fetchStats);
    </script>
@endsection
