@extends('layouts.app')

@section('title', 'Custom Report Builder')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Custom Report Builder</h1>
                    <p class="mt-2 text-sm text-gray-600">Build and export custom analytics reports</p>
                </div>
                <a href="{{ route('analytics.advanced') }}"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>

        <form action="{{ route('analytics.generate-report') }}" method="POST" id="reportForm">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Panel: Metrics & Filters -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Metrics Selection -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-chart-bar mr-2 text-indigo-600"></i>Select Metrics
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <label class="cursor-pointer">
                                <input type="checkbox" name="metrics[]" value="revenue" class="hidden peer" checked>
                                <div
                                    class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-indigo-600 peer-checked:bg-indigo-50 hover:border-indigo-300 transition">
                                    <i class="fas fa-dollar-sign text-2xl text-indigo-600 mb-2"></i>
                                    <div class="font-medium text-gray-900">Revenue</div>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="checkbox" name="metrics[]" value="orders" class="hidden peer" checked>
                                <div
                                    class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-indigo-600 peer-checked:bg-indigo-50 hover:border-indigo-300 transition">
                                    <i class="fas fa-shopping-cart text-2xl text-blue-600 mb-2"></i>
                                    <div class="font-medium text-gray-900">Orders</div>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="checkbox" name="metrics[]" value="customers" class="hidden peer" checked>
                                <div
                                    class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-indigo-600 peer-checked:bg-indigo-50 hover:border-indigo-300 transition">
                                    <i class="fas fa-users text-2xl text-green-600 mb-2"></i>
                                    <div class="font-medium text-gray-900">Customers</div>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="checkbox" name="metrics[]" value="inventory" class="hidden peer">
                                <div
                                    class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-indigo-600 peer-checked:bg-indigo-50 hover:border-indigo-300 transition">
                                    <i class="fas fa-boxes text-2xl text-purple-600 mb-2"></i>
                                    <div class="font-medium text-gray-900">Inventory</div>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="checkbox" name="metrics[]" value="products" class="hidden peer">
                                <div
                                    class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-indigo-600 peer-checked:bg-indigo-50 hover:border-indigo-300 transition">
                                    <i class="fas fa-box text-2xl text-orange-600 mb-2"></i>
                                    <div class="font-medium text-gray-900">Products</div>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="checkbox" name="metrics[]" value="profit" class="hidden peer">
                                <div
                                    class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-indigo-600 peer-checked:bg-indigo-50 hover:border-indigo-300 transition">
                                    <i class="fas fa-coins text-2xl text-yellow-600 mb-2"></i>
                                    <div class="font-medium text-gray-900">Profit</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-calendar mr-2 text-indigo-600"></i>Date Range
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                <input type="date" name="start_date" value="{{ now()->subDays(30)->format('Y-m-d') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                <input type="date" name="end_date" value="{{ now()->format('Y-m-d') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quick Select</label>
                                <select id="quickDate"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Custom</option>
                                    <option value="7">Last 7 Days</option>
                                    <option value="30" selected>Last 30 Days</option>
                                    <option value="90">Last 90 Days</option>
                                    <option value="365">Last Year</option>
                                    <option value="mtd">Month to Date</option>
                                    <option value="ytd">Year to Date</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-filter mr-2 text-indigo-600"></i>Filters (Optional)
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Module</label>
                                <select name="filters[module]"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    <option value="">All Modules</option>
                                    <option value="sales">Sales</option>
                                    <option value="inventory">Inventory</option>
                                    <option value="finance">Finance</option>
                                    <option value="crm">CRM</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                <input type="text" name="filters[category]" placeholder="e.g. Electronics"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select name="filters[status]"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    <option value="">All</option>
                                    <option value="completed">Completed</option>
                                    <option value="pending">Pending</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Panel: Export Options -->
                <div class="space-y-6">
                    <!-- Export Format -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-file-export mr-2 text-green-600"></i>Export Format
                        </h3>
                        <div class="space-y-3">
                            <label
                                class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-indigo-300 transition">
                                <input type="radio" name="format" value="pdf" class="text-indigo-600" checked>
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900">PDF Document</div>
                                    <div class="text-sm text-gray-500">Formatted report with charts</div>
                                </div>
                                <i class="fas fa-file-pdf ml-auto text-2xl text-red-500"></i>
                            </label>

                            <label
                                class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-indigo-300 transition">
                                <input type="radio" name="format" value="excel" class="text-indigo-600">
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900">Excel Spreadsheet</div>
                                    <div class="text-sm text-gray-500">Editable with formulas</div>
                                </div>
                                <i class="fas fa-file-excel ml-auto text-2xl text-green-500"></i>
                            </label>

                            <label
                                class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-indigo-300 transition">
                                <input type="radio" name="format" value="csv" class="text-indigo-600">
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900">CSV File</div>
                                    <div class="text-sm text-gray-500">Raw data for import</div>
                                </div>
                                <i class="fas fa-file-csv ml-auto text-2xl text-blue-500"></i>
                            </label>
                        </div>
                    </div>

                    <!-- Report Summary -->
                    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Report Summary</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Metrics:</span>
                                <span class="font-semibold" id="metricCount">3 selected</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Date Range:</span>
                                <span class="font-semibold" id="dateRange">30 days</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Format:</span>
                                <span class="font-semibold" id="formatDisplay">PDF</span>
                            </div>
                        </div>
                    </div>

                    <!-- Generate Button -->
                    <button type="submit"
                        class="w-full px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition font-semibold text-lg shadow-lg">
                        <i class="fas fa-download mr-2"></i>Generate & Download Report
                    </button>

                    <!-- Tips -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-lightbulb text-yellow-600 mt-1"></i>
                            <div class="text-sm text-yellow-800">
                                <strong>Tip:</strong> For best results, select specific date ranges and filters to get
                                focused insights.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Quick date selection
            const quickDateSelect = document.getElementById('quickDate');
            const startDateInput = document.querySelector('input[name="start_date"]');
            const endDateInput = document.querySelector('input[name="end_date"]');

            quickDateSelect.addEventListener('change', function() {
                const value = this.value;
                const endDate = new Date();
                let startDate = new Date();

                if (value === '7') {
                    startDate.setDate(endDate.getDate() - 7);
                } else if (value === '30') {
                    startDate.setDate(endDate.getDate() - 30);
                } else if (value === '90') {
                    startDate.setDate(endDate.getDate() - 90);
                } else if (value === '365') {
                    startDate.setDate(endDate.getDate() - 365);
                } else if (value === 'mtd') {
                    startDate = new Date(endDate.getFullYear(), endDate.getMonth(), 1);
                } else if (value === 'ytd') {
                    startDate = new Date(endDate.getFullYear(), 0, 1);
                }

                if (value) {
                    startDateInput.value = startDate.toISOString().split('T')[0];
                    endDateInput.value = endDate.toISOString().split('T')[0];
                    updateSummary();
                }
            });

            // Update summary on metric change
            document.querySelectorAll('input[name="metrics[]"]').forEach(checkbox => {
                checkbox.addEventListener('change', updateSummary);
            });

            // Update summary on format change
            document.querySelectorAll('input[name="format"]').forEach(radio => {
                radio.addEventListener('change', updateSummary);
            });

            // Update date range display
            startDateInput.addEventListener('change', updateSummary);
            endDateInput.addEventListener('change', updateSummary);

            function updateSummary() {
                // Update metric count
                const checkedMetrics = document.querySelectorAll('input[name="metrics[]"]:checked');
                document.getElementById('metricCount').textContent = checkedMetrics.length + ' selected';

                // Update date range
                const start = new Date(startDateInput.value);
                const end = new Date(endDateInput.value);
                const days = Math.round((end - start) / (1000 * 60 * 60 * 24));
                document.getElementById('dateRange').textContent = days + ' days';

                // Update format
                const format = document.querySelector('input[name="format"]:checked').value;
                document.getElementById('formatDisplay').textContent = format.toUpperCase();
            }

            // Form validation
            document.getElementById('reportForm').addEventListener('submit', function(e) {
                const checkedMetrics = document.querySelectorAll('input[name="metrics[]"]:checked');
                if (checkedMetrics.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one metric');
                }
            });

            // Initialize summary
            updateSummary();
        });
    </script>
@endpush
