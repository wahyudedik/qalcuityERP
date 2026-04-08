<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Strategic Sourcing Analytics</h1>
            <button onclick="document.getElementById('createOpportunityModal').classList.remove('hidden')"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium whitespace-nowrap">
                + New Opportunity
            </button>
        </div>
    </x-slot>

    {{-- Pipeline Metrics --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Active Opportunities</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $dashboard['active_opportunities'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">RFQs This Month</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $dashboard['rfqs_this_month'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Potential Savings</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">Rp
                {{ number_format($dashboard['potential_savings'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Avg Response Time</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $dashboard['avg_response_time'] }}h</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Completion Rate</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $dashboard['completion_rate'] }}%</p>
        </div>
    </div>

    {{-- Identified Opportunities --}}
    <div
        class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Identified Opportunities</h2>
            <a href="#" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">View All →</a>
        </div>

        @if (count($opportunities) === 0)
            <div class="p-12 text-center">
                <p class="text-4xl mb-3">🎯</p>
                <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada opportunity yang teridentifikasi.</p>
                <button onclick="document.getElementById('createOpportunityModal').classList.remove('hidden')"
                    class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm">
                    Buat Opportunity Baru
                </button>
            </div>
        @else
            <div class="divide-y divide-gray-200 dark:divide-white/5">
                @foreach ($opportunities as $opportunity)
                    <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-[#0f172a] transition">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="font-medium text-gray-900 dark:text-white">{{ $opportunity->title }}</h4>
                                    @php
                                        $priorityColors = [
                                            'low' => 'gray',
                                            'medium' => 'blue',
                                            'high' => 'orange',
                                            'critical' => 'red',
                                        ];
                                        $color = $priorityColors[$opportunity->priority] ?? 'gray';
                                    @endphp
                                    <span
                                        class="px-2 py-0.5 text-xs rounded-full bg-{{ $color }}-100 text-{{ $color }}-700 dark:bg-{{ $color }}-500/20 dark:text-{{ $color }}-400">
                                        {{ ucfirst($opportunity->priority) }}
                                    </span>
                                </div>

                                @if ($opportunity->category)
                                    <p class="text-xs text-gray-500 dark:text-slate-400 mb-2">Category:
                                        {{ $opportunity->category }}</p>
                                @endif

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                                    <div>
                                        <span class="text-gray-500 dark:text-slate-400">Annual Spend:</span>
                                        <span class="font-medium text-gray-900 dark:text-white ml-1">Rp
                                            {{ number_format($opportunity->estimated_annual_spend, 0, ',', '.') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-slate-400">Potential Savings:</span>
                                        <span class="font-medium text-green-600 dark:text-green-400 ml-1">Rp
                                            {{ number_format($opportunity->potential_savings, 0, ',', '.') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-slate-400">Status:</span>
                                        <span
                                            class="font-medium text-gray-900 dark:text-white ml-1">{{ ucfirst(str_replace('_', ' ', $opportunity->status)) }}</span>
                                    </div>
                                    @if ($opportunity->target_completion_date)
                                        <div>
                                            <span class="text-gray-500 dark:text-slate-400">Target:</span>
                                            <span
                                                class="font-medium text-gray-900 dark:text-white ml-1">{{ $opportunity->target_completion_date->format('d M Y') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="ml-4">
                                <select onchange="updateOpportunityStatus({{ $opportunity->id }}, this.value)"
                                    class="text-xs border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                    <option value="identified"
                                        {{ $opportunity->status === 'identified' ? 'selected' : '' }}>Identified
                                    </option>
                                    <option value="analyzing"
                                        {{ $opportunity->status === 'analyzing' ? 'selected' : '' }}>Analyzing</option>
                                    <option value="rfq_sent"
                                        {{ $opportunity->status === 'rfq_sent' ? 'selected' : '' }}>RFQ Sent</option>
                                    <option value="negotiated"
                                        {{ $opportunity->status === 'negotiated' ? 'selected' : '' }}>Negotiated
                                    </option>
                                    <option value="implemented"
                                        {{ $opportunity->status === 'implemented' ? 'selected' : '' }}>Implemented
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10">
                {{ $opportunities->links() }}
            </div>
        @endif
    </div>

    {{-- Supplier Consolidation Recommendations --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Supplier Consolidation Recommendations
        </h2>

        <div class="space-y-3">
            <div
                class="p-4 bg-yellow-50 dark:bg-yellow-500/10 border border-yellow-200 dark:border-yellow-500/30 rounded-lg">
                <div class="flex items-start gap-3">
                    <span class="text-2xl">⚠️</span>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900 dark:text-white">High Fragmentation Detected</h4>
                        <p class="text-sm text-gray-600 dark:text-slate-400 mt-1">
                            Anda memiliki banyak supplier dengan kategori yang sama. Konsolidasi dapat menghemat hingga
                            10-15% biaya procurement.
                        </p>
                        <div class="mt-2 flex gap-2">
                            <button
                                class="px-3 py-1 text-xs bg-yellow-600 text-white rounded hover:bg-yellow-700 transition">Analyze
                                Categories</button>
                            <button
                                class="px-3 py-1 text-xs border border-yellow-600 text-yellow-600 rounded hover:bg-yellow-50 transition">View
                                Details</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 rounded-lg">
                <div class="flex items-start gap-3">
                    <span class="text-2xl">💡</span>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900 dark:text-white">Volume Discount Opportunity</h4>
                        <p class="text-sm text-gray-600 dark:text-slate-400 mt-1">
                            Konsolidasi volume pembelian ke fewer suppliers dapat memberikan leverage untuk negosiasi
                            harga yang lebih baik.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Opportunity Modal --}}
    <div id="createOpportunityModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <form action="{{ route('suppliers.opportunities.create') }}" method="POST">
                @csrf

                <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Create New Opportunity</h3>
                    <button type="button"
                        onclick="document.getElementById('createOpportunityModal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">✕</button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Title *</label>
                        <input type="text" name="title" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                            placeholder="e.g., Consolidate IT Equipment Suppliers">
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Description</label>
                        <textarea name="description" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                            placeholder="Describe the opportunity..."></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Category</label>
                            <input type="text" name="category"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                                placeholder="e.g., Electronics">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Priority
                                *</label>
                            <select name="priority" required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Estimated
                            Annual Spend (Rp) *</label>
                        <input type="number" name="estimated_annual_spend" required min="0" step="1000000"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                            placeholder="100000000">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Target
                            Completion Date</label>
                        <input type="date" name="target_completion_date"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10 flex justify-end gap-3">
                    <button type="button"
                        onclick="document.getElementById('createOpportunityModal').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-slate-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        Create Opportunity
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateOpportunityStatus(opportunityId, status) {
            fetch(`/supplier-scorecards/opportunities/${opportunityId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        status: status
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to update status');
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
</x-app-layout>
