

<?php $__env->startSection('title', 'Payment History'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 py-8" x-data="paymentHistory()">

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Payment History</h1>
                    <p class="text-gray-600">View and manage all payment transactions</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="refreshData()"
                        class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 py-2 px-4 rounded-lg transition-colors flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Refresh</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-xs text-gray-500">Success Rate</span>
                </div>
                <p class="text-2xl font-bold text-gray-900" x-text="stats.success_rate + '%'"></p>
                <p class="text-sm text-gray-500 mt-1">Last 30 days</p>
            </div>

            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-xs text-gray-500">Total Revenue</span>
                </div>
                <p class="text-2xl font-bold text-gray-900"
                    x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(stats.total_revenue)"></p>
                <p class="text-sm text-gray-500 mt-1">This month</p>
            </div>

            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <span class="text-xs text-gray-500">Transactions</span>
                </div>
                <p class="text-2xl font-bold text-gray-900" x-text="stats.total_transactions"></p>
                <p class="text-sm text-gray-500 mt-1">All time</p>
            </div>

            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-xs text-gray-500">Pending</span>
                </div>
                <p class="text-2xl font-bold text-gray-900" x-text="stats.pending_count"></p>
                <p class="text-sm text-gray-500 mt-1">Awaiting payment</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow p-4 mb-6">
            <div class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select x-model="filters.status" @change="loadTransactions()"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Status</option>
                        <option value="success">Success</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>

                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                    <select x-model="filters.method" @change="loadTransactions()"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Methods</option>
                        <option value="qris">QRIS</option>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>

                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                    <input type="date" x-model="filters.date_from" @change="loadTransactions()"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Transaction</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="transaction in transactions" :key="transaction.id">
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"
                                        x-text="transaction.transaction_number"></div>
                                    <div class="text-xs text-gray-500" x-text="transaction.gateway_provider"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"
                                        x-text="transaction.sales_order?.order_number || 'N/A'"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <span x-show="transaction.payment_method === 'qris'"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            QRIS
                                        </span>
                                        <span x-show="transaction.payment_method === 'cash'"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Cash
                                        </span>
                                        <span x-show="transaction.payment_method === 'card'"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Card
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900"
                                        x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(transaction.amount)"></div>
                                    <div x-show="transaction.fee > 0" class="text-xs text-gray-500"
                                        x-text="'Fee: Rp ' + new Intl.NumberFormat('id-ID').format(transaction.fee)"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="{
                                            'bg-green-100 text-green-800': transaction.status === 'success',
                                            'bg-yellow-100 text-yellow-800': ['pending', 'waiting_payment'].includes(
                                                transaction.status),
                                            'bg-red-100 text-red-800': ['failed', 'expired', 'cancelled'].includes(
                                                transaction.status),
                                            'bg-blue-100 text-blue-800': transaction.status === 'processing'
                                        }"
                                        x-text="transaction.status"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    x-text="new Date(transaction.created_at).toLocaleDateString('id-ID')"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button @click="viewDetails(transaction)"
                                        class="text-blue-600 hover:text-blue-900 mr-3">
                                        View
                                    </button>
                                    <button x-show="transaction.status === 'pending'"
                                        @click="checkTransactionStatus(transaction.transaction_number)"
                                        class="text-green-600 hover:text-green-900">
                                        Check
                                    </button>
                                </td>
                            </tr>
                        </template>

                        <!-- Empty State -->
                        <tr x-show="transactions.length === 0 && !loading">
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-gray-500">No transactions found</p>
                            </td>
                        </tr>

                        <!-- Loading State -->
                        <tr x-show="loading">
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <p class="text-gray-500 mt-2">Loading transactions...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div x-show="pagination.last_page > 1" class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing <span class="font-medium" x-text="pagination.from"></span> to <span class="font-medium"
                            x-text="pagination.to"></span> of <span class="font-medium" x-text="pagination.total"></span>
                        results
                    </div>
                    <div class="flex space-x-2">
                        <button @click="pagination.current_page > 1 ? goToPage(pagination.current_page - 1) : null"
                            :disabled="pagination.current_page <= 1"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100">
                            Previous
                        </button>
                        <button
                            @click="pagination.current_page < pagination.last_page ? goToPage(pagination.current_page + 1) : null"
                            :disabled="pagination.current_page >= pagination.last_page"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Details Modal -->
        <div x-show="showDetailsModal" x-transition
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6" x-show="selectedTransaction">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Transaction Details</h3>
                        <button @click="showDetailsModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs text-gray-500">Transaction Number</label>
                                <p class="font-mono text-sm font-medium" x-text="selectedTransaction?.transaction_number">
                                </p>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Status</label>
                                <p class="text-sm font-medium capitalize" x-text="selectedTransaction?.status"></p>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Amount</label>
                                <p class="text-sm font-medium"
                                    x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(selectedTransaction?.amount)">
                                </p>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Fee</label>
                                <p class="text-sm font-medium"
                                    x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(selectedTransaction?.fee)"></p>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Payment Method</label>
                                <p class="text-sm font-medium capitalize" x-text="selectedTransaction?.payment_method">
                                </p>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Gateway</label>
                                <p class="text-sm font-medium capitalize" x-text="selectedTransaction?.gateway_provider">
                                </p>
                            </div>
                        </div>

                        <div x-show="selectedTransaction?.qr_string" class="border-t pt-4">
                            <label class="text-xs text-gray-500 block mb-2">QR Code</label>
                            <img :src="selectedTransaction?.qr_image_url" alt="QR Code" class="w-48 h-48">
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function paymentHistory() {
            return {
                transactions: [],
                loading: false,
                showDetailsModal: false,
                selectedTransaction: null,
                filters: {
                    status: '',
                    method: '',
                    date_from: '',
                    date_to: ''
                },
                pagination: {
                    current_page: 1,
                    last_page: 1,
                    from: 0,
                    to: 0,
                    total: 0
                },
                stats: {
                    success_rate: 0,
                    total_revenue: 0,
                    total_transactions: 0,
                    pending_count: 0
                },

                init() {
                    this.loadTransactions();
                    this.loadStats();
                },

                async loadTransactions(page = 1) {
                    this.loading = true;

                    try {
                        let url = `/api/payment/history?page=${page}&limit=50`;

                        if (this.filters.status) {
                            url += `&status=${this.filters.status}`;
                        }

                        const response = await fetch(url, {
                            headers: {
                                'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                            },
                        });

                        const result = await response.json();

                        if (result.success) {
                            this.transactions = result.data.data;
                            this.pagination = {
                                current_page: result.data.current_page,
                                last_page: result.data.last_page,
                                from: result.data.from,
                                to: result.data.to,
                                total: result.data.total
                            };
                        }

                    } catch (error) {
                        console.error('Load error:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async loadStats() {
                    // Load statistics (you can add a dedicated endpoint for this)
                    this.stats = {
                        success_rate: 95,
                        total_revenue: 15000000,
                        total_transactions: 150,
                        pending_count: 5
                    };
                },

                goToPage(page) {
                    this.loadTransactions(page);
                },

                refreshData() {
                    this.loadTransactions(this.pagination.current_page);
                    this.loadStats();
                },

                viewDetails(transaction) {
                    this.selectedTransaction = transaction;
                    this.showDetailsModal = true;
                },

                async checkTransactionStatus(transactionNumber) {
                    try {
                        const response = await fetch(`/api/payment/status?transaction_number=${transactionNumber}`, {
                            headers: {
                                'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                            },
                        });

                        const result = await response.json();

                        if (result.success) {
                            alert(`Status: ${result.status}\nPaid at: ${result.paid_at || 'N/A'}`);
                            this.refreshData();
                        } else {
                            alert('Failed to check status');
                        }

                    } catch (error) {
                        alert('Error checking status');
                    }
                }
            }
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\pos\payment-history.blade.php ENDPATH**/ ?>