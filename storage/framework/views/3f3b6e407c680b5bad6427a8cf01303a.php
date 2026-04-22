<!-- Payment Selection Modal -->
<div x-data="paymentSelection()" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <!-- Backdrop -->
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModal">
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8482;</span>

        <!-- Modal Panel -->
        <div
            class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">

            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg leading-6 font-semibold text-white" id="modal-title">
                        💳 Select Payment Method
                    </h3>
                    <button @click="closeModal" class="text-white hover:text-gray-200 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-600">Order #<?php echo e($order->order_number ?? 'ORD-001'); ?></p>
                        <p class="text-xs text-gray-500">
                            <?php echo e($order->created_at->format('d M Y, H:i') ?? now()->format('d M Y, H:i')); ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-gray-900">Rp
                            <?php echo e(number_format($order->grand_total ?? 0, 0, ',', '.')); ?></p>
                        <p class="text-xs text-gray-500"><?php echo e($order->items_count ?? 0); ?> items</p>
                    </div>
                </div>
            </div>

            <!-- Payment Methods Grid -->
            <div class="px-6 py-6">
                <div class="grid grid-cols-2 gap-4">

                    <!-- Cash Payment -->
                    <button @click="selectMethod('cash')"
                        :class="selectedMethod === 'cash' ? 'ring-2 ring-blue-500 bg-blue-50' : 'hover:bg-gray-50'"
                        class="relative p-6 border-2 border-gray-200 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-16 h-16 mb-3 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-900 mb-1">Cash</h4>
                            <p class="text-xs text-gray-500">Pay with cash</p>
                        </div>
                        <div x-show="selectedMethod === 'cash'" class="absolute top-2 right-2">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </button>

                    <!-- QRIS Payment -->
                    <button @click="selectMethod('qris')"
                        :class="selectedMethod === 'qris' ? 'ring-2 ring-blue-500 bg-blue-50' : 'hover:bg-gray-50'"
                        class="relative p-6 border-2 border-gray-200 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :disabled="!qrisAvailable">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-16 h-16 mb-3 bg-purple-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-900 mb-1">QRIS</h4>
                            <p class="text-xs text-gray-500">Scan & pay</p>
                            <p x-show="!qrisAvailable" class="text-xs text-red-500 mt-1">Not configured</p>
                        </div>
                        <div x-show="selectedMethod === 'qris'" class="absolute top-2 right-2">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </button>

                    <!-- Credit/Debit Card -->
                    <button @click="selectMethod('card')"
                        :class="selectedMethod === 'card' ? 'ring-2 ring-blue-500 bg-blue-50' : 'hover:bg-gray-50'"
                        class="relative p-6 border-2 border-gray-200 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-16 h-16 mb-3 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-900 mb-1">Card</h4>
                            <p class="text-xs text-gray-500">Credit/Debit card</p>
                        </div>
                        <div x-show="selectedMethod === 'card'" class="absolute top-2 right-2">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </button>

                    <!-- Bank Transfer -->
                    <button @click="selectMethod('bank_transfer')"
                        :class="selectedMethod === 'bank_transfer' ? 'ring-2 ring-blue-500 bg-blue-50' : 'hover:bg-gray-50'"
                        class="relative p-6 border-2 border-gray-200 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-16 h-16 mb-3 bg-orange-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-900 mb-1">Bank Transfer</h4>
                            <p class="text-xs text-gray-500">Virtual account</p>
                        </div>
                        <div x-show="selectedMethod === 'bank_transfer'" class="absolute top-2 right-2">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </button>

                </div>
            </div>

            <!-- Payment Details Section -->
            <div class="px-6 py-4 border-t border-gray-200">

                <!-- Cash Payment Form -->
                <div x-show="selectedMethod === 'cash'" x-transition class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount Received</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                            <input type="number" x-model="cashReceived" @input="calculateChange"
                                class="pl-12 pr-4 py-3 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg font-semibold"
                                placeholder="0">
                        </div>
                    </div>

                    <!-- Quick Amount Buttons -->
                    <div class="grid grid-cols-4 gap-2">
                        <template x-for="amount in quickCashAmounts" :key="amount">
                            <button @click="cashReceived = amount; calculateChange()"
                                class="px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                                x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(amount)"></button>
                        </template>
                    </div>

                    <!-- Change Display -->
                    <div x-show="change >= 0" class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-green-700 font-medium">Change:</span>
                            <span class="text-2xl font-bold text-green-700"
                                x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(change)"></span>
                        </div>
                    </div>

                    <div x-show="change < 0 && cashReceived > 0"
                        class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <p class="text-sm text-red-700">Insufficient amount</p>
                    </div>
                </div>

                <!-- QRIS Payment Info -->
                <div x-show="selectedMethod === 'qris'" x-transition class="space-y-4">
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <div class="flex items-start space-x-3">
                            <svg class="w-6 h-6 text-purple-600 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm text-purple-900 font-medium mb-1">QRIS Payment</p>
                                <p class="text-xs text-purple-700">Customer will scan QR code with their e-wallet app
                                    (GoPay, OVO, Dana, LinkAja, etc.)</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Payment expires in 15 minutes</span>
                    </div>
                </div>

                <!-- Card Payment Info -->
                <div x-show="selectedMethod === 'card'" x-transition class="space-y-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-blue-900">Card payment processing will be available soon.</p>
                    </div>
                </div>

                <!-- Bank Transfer Info -->
                <div x-show="selectedMethod === 'bank_transfer'" x-transition class="space-y-4">
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                        <p class="text-sm text-orange-900">Bank transfer payment processing will be available soon.</p>
                    </div>
                </div>

            </div>

            <!-- Footer Actions -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <div class="flex space-x-3">
                    <button @click="closeModal"
                        class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors font-medium">
                        Cancel
                    </button>
                    <button @click="processPayment" :disabled="!canProceed"
                        :class="canProceed ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed'"
                        class="flex-1 px-4 py-3 text-white rounded-lg transition-colors font-medium flex items-center justify-center space-x-2">
                        <span x-text="proceedButtonText"></span>
                        <svg x-show="processing" class="animate-spin h-5 w-5 text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function paymentSelection() {
        return {
            selectedMethod: null,
            cashReceived: '',
            change: 0,
            processing: false,
            qrisAvailable: true, // Check from API if gateway configured

            quickCashAmounts: [50000, 100000, 150000, 200000],

            get canProceed() {
                if (!this.selectedMethod) return false;
                if (this.processing) return false;

                if (this.selectedMethod === 'cash') {
                    return this.cashReceived >= <?php echo e($order->grand_total ?? 0); ?>;
                }

                return true;
            },

            get proceedButtonText() {
                if (this.processing) return 'Processing...';

                const texts = {
                    'cash': 'Complete Payment',
                    'qris': 'Generate QR Code',
                    'card': 'Process Card',
                    'bank_transfer': 'Generate VA Number'
                };

                return texts[this.selectedMethod] || 'Continue';
            },

            selectMethod(method) {
                this.selectedMethod = method;
                this.cashReceived = '';
                this.change = 0;
            },

            calculateChange() {
                const total = <?php echo e($order->grand_total ?? 0); ?>;
                const received = parseFloat(this.cashReceived) || 0;
                this.change = received - total;
            },

            async processPayment() {
                this.processing = true;

                try {
                    if (this.selectedMethod === 'cash') {
                        await this.processCashPayment();
                    } else if (this.selectedMethod === 'qris') {
                        await this.processQrisPayment();
                    }
                } catch (error) {
                    console.error('Payment processing error:', error);
                    alert('Payment failed: ' + error.message);
                } finally {
                    this.processing = false;
                }
            },

            async processCashPayment() {
                // API call to complete cash payment
                const response = await fetch(`/api/orders/<?php echo e($order->id); ?>/complete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                    },
                    body: JSON.stringify({
                        payment_method: 'cash',
                        amount_paid: parseFloat(this.cashReceived),
                        change: this.change
                    })
                });

                const result = await response.json();

                if (result.success) {
                    window.location.href = '/pos/receipt/' + result.order_id;
                } else {
                    throw new Error(result.error || 'Payment failed');
                }
            },

            async processQrisPayment() {
                // Generate QRIS payment
                const response = await fetch(`/api/payment/qris/<?php echo e($order->id); ?>`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                    }
                });

                const result = await response.json();

                if (result.success) {
                    // Redirect to QR code display page
                    window.location.href = '/pos/payment/qris/' + result.transaction_number;
                } else {
                    throw new Error(result.error || 'Failed to generate QR code');
                }
            },

            closeModal() {
                // Dispatch event to parent component
                this.$dispatch('payment-modal-closed');
            }
        }
    }
</script>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\payment-selection-modal.blade.php ENDPATH**/ ?>