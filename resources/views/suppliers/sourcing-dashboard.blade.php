<x-app-layout>
    <x-slot name="header">Strategic Sourcing Analytics</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <button onclick="document.getElementById('createOpportunityModal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Opportunity
            </button>
    </div>

    {{-- Pipeline Metrics --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Active Opportunities</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $dashboard['active_opportunities'] }}
            </p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">RFQs This Month</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $dashboard['rfqs_this_month'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Potential Savings</p>
            <p class="text-2xl font-bold text-green-600 mt-1">Rp
                {{ number_format($dashboard['potential_savings'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Avg Response Time</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $dashboard['avg_response_time'] }}h</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Completion Rate</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $dashboard['completion_rate'] }}%</p>
        </div>
    </div>

    {{-- Identified Opportunities --}}
    <div
        class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-900">Identified Opportunities</h2>
            <a href="#" class="text-sm text-indigo-600 hover:underline">View All →</a>
        </div>

        @if (count($opportunities) === 0)
            <div class="p-12 text-center">
                <svg class="mx-auto w-16 h-16 text-gray-300 mb-3" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <p class="text-sm text-gray-500">Belum ada opportunity yang teridentifikasi.</p>
                <button onclick="document.getElementById('createOpportunityModal').classList.remove('hidden')"
                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Opportunity Baru
                </button>
            </div>
        @else
            <div class="divide-y divide-gray-200">
                @foreach ($opportunities as $opportunity)
                    <div class="px-6 py-4 hover:bg-gray-50 transition">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="font-medium text-gray-900">{{ $opportunity->title }}</h4>
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
                                        class="px-2 py-0.5 text-xs rounded-full bg-{{ $color  }}-100 text-{{ $color }}-700 $color }}-500/20 $color }}-400">
                                        {{ ucfirst($opportunity->priority) }}
                                    </span>
                                </div>

                                @if ($opportunity->category)
                                    <p class="text-xs text-gray-500 mb-2">Category:
                                        {{ $opportunity->category }}</p>
                                @endif

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                                    <div>
                                        <span class="text-gray-500">Annual Spend:</span>
                                        <span class="font-medium text-gray-900 ml-1">Rp
                                            {{ number_format($opportunity->estimated_annual_spend, 0, ',', '.') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Potential Savings:</span>
                                        <span class="font-medium text-green-600 ml-1">Rp
                                            {{ number_format($opportunity->potential_savings, 0, ',', '.') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Status:</span>
                                        <span
                                            class="font-medium text-gray-900 ml-1">{{ ucfirst(str_replace('_', ' ', $opportunity->status)) }}</span>
                                    </div>
                                    @if ($opportunity->target_completion_date)
                                        <div>
                                            <span class="text-gray-500">Target:</span>
                                            <span
                                                class="font-medium text-gray-900 ml-1">{{ $opportunity->target_completion_date->format('d M Y') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="ml-4">
                                <select onchange="updateOpportunityStatus({{ $opportunity->id }}, this.value)"
                                    class="text-xs border border-gray-200 rounded-xl px-2 py-1.5 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
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

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $opportunities->links() }}
            </div>
        @endif
    </div>

    {{-- Supplier Consolidation Recommendations --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Supplier Consolidation Recommendations
        </h2>

        <div class="space-y-3">
            <div
                class="p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-yellow-600 flex-shrink-0 mt-0.5" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900">High Fragmentation Detected</h4>
                        <p class="text-sm text-gray-600 mt-1">
                            Anda memiliki banyak supplier dengan kategori yang sama. Konsolidasi dapat menghemat hingga
                            10-15% biaya procurement.
                        </p>
                        <div class="mt-2 flex gap-2">
                            <button
                                class="px-3 py-1.5 text-xs bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 transition">Analyze
                                Categories</button>
                            <button
                                class="px-3 py-1.5 text-xs border border-yellow-600 text-yellow-600 rounded-xl hover:bg-yellow-50 transition">View
                                Details</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900">Volume Discount Opportunity</h4>
                        <p class="text-sm text-gray-600 mt-1">
                            Konsolidasi volume pembelian ke fewer suppliers dapat memberikan leverage untuk negosiasi
                            harga yang lebih baik.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Opportunity Modal --}}
    <div id="createOpportunityModal"
        class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <form action="{{ route('suppliers.opportunities.create') }}" method="POST">
                @csrf

                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Create New Opportunity</h3>
                    <button type="button"
                        onclick="document.getElementById('createOpportunityModal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                        <input type="text" name="title" required
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., Consolidate IT Equipment Suppliers">
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Describe the opportunity..."></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <input type="text" name="category"
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g., Electronics">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Priority
                                *</label>
                            <select name="priority" required
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estimated
                            Annual Spend (Rp) *</label>
                        <input type="number" name="estimated_annual_spend" required min="0" step="1000000"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="100000000">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Target
                            Completion Date</label>
                        <input type="date" name="target_completion_date"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                    <button type="button"
                        onclick="document.getElementById('createOpportunityModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">
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
