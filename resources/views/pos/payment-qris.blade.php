@extends('layouts.app')

@section('title', 'QRIS Payment - ' . $transaction->transaction_number)

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-purple-50 via-blue-50 to-indigo-50 flex items-center justify-center p-4"
        x-data="qrisPayment()" x-init="init()">

        <div class="max-w-md w-full">

            <!-- Header Card -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-purple-600 to-blue-600 px-6 py-4">
                    <div class="flex items-center justify-between text-white">
                        <div>
                            <h1 class="text-xl font-bold">QRIS Payment</h1>
                            <p class="text-sm opacity-90">Scan & Pay</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs opacity-75">Transaction #</p>
                            <p class="font-mono font-semibold">{{ $transaction->transaction_number }}</p>
                        </div>
                    </div>
                </div>

                <!-- Amount Display -->
                <div class="px-6 py-8 text-center border-b border-gray-100">
                    <p class="text-sm text-gray-500 mb-2">Total Amount</p>
                    <p class="text-4xl font-bold text-gray-900">Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                    </p>

                    @if ($transaction->salesOrder)
                        <p class="text-xs text-gray-400 mt-2">Order #{{ $transaction->salesOrder?->order_number }}</p>
                    @endif
                </div>
            </div>

            <!-- QR Code Card -->
            <div class="bg-white rounded-2xl shadow-xl p-6 mb-6">

                <!-- QR Code Display -->
                <div class="flex flex-col items-center">
                    <div class="bg-white p-4 rounded-xl border-2 border-purple-100 shadow-inner mb-4">
                        @if ($transaction->qr_image_url)
                            <img src="{{ $transaction->qr_image_url }}" alt="QRIS Code" class="w-64 h-64 object-contain"
                                id="qr-code-image">
                        @else
                            <div class="w-64 h-64 flex items-center justify-center bg-gray-100 rounded-lg">
                                <div class="text-center">
                                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                    </svg>
                                    <p class="text-sm text-gray-500">Loading QR Code...</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <p class="text-sm text-gray-600 text-center mb-4">
                        Scan QR code dengan aplikasi e-wallet Anda
                    </p>

                    <!-- Supported E-Wallets -->
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mb-1">
                                <span class="text-xs font-bold text-blue-600">GP</span>
                            </div>
                            <span class="text-xs text-gray-500">GoPay</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mb-1">
                                <span class="text-xs font-bold text-purple-600">OVO</span>
                            </div>
                            <span class="text-xs text-gray-500">OVO</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mb-1">
                                <span class="text-xs font-bold text-blue-600">DANA</span>
                            </div>
                            <span class="text-xs text-gray-500">DANA</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mb-1">
                                <span class="text-xs font-bold text-red-600">LA</span>
                            </div>
                            <span class="text-xs text-gray-500">LinkAja</span>
                        </div>
                    </div>
                </div>

                <!-- Countdown Timer -->
                <div class="bg-gradient-to-r from-orange-50 to-red-50 border border-orange-200 rounded-xl p-4 mb-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm font-medium text-orange-900">Expires in:</span>
                        </div>
                        <div class="text-2xl font-bold font-mono"
                            :class="timeLeft < 60 ? 'text-red-600' : 'text-orange-600'" x-text="formatTime(timeLeft)">
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-3 bg-orange-200 rounded-full h-2 overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-orange-500 to-red-500 transition-all duration-1000"
                            :style="'width: ' + (timeLeft / 900 * 100) + '%'"></div>
                    </div>
                </div>

                <!-- Status Indicator -->
                <div class="flex items-center justify-center space-x-2 text-sm" :class="statusColor">
                    <div class="w-2 h-2 rounded-full" :class="statusDot"></div>
                    <span x-text="statusText"></span>
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">

                <!-- Check Status Button -->
                <button @click="checkStatus()" :disabled="checking || isCompleted"
                    class="w-full bg-white hover:bg-gray-50 text-gray-900 font-semibold py-4 px-6 rounded-xl shadow-lg border-2 border-gray-200 transition-all duration-200 flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg x-show="checking" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <svg x-show="!checking" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span x-text="isCompleted ? 'Payment Completed' : 'Check Payment Status'"></span>
                </button>

                <!-- Cancel Button -->
                <button @click="cancelPayment()" :disabled="isCompleted"
                    class="w-full bg-red-50 hover:bg-red-100 text-red-700 font-semibold py-3 px-6 rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Cancel Payment
                </button>

            </div>

            <!-- Help Text -->
            <div class="mt-6 text-center">
                <p class="text-xs text-gray-500">
                    After payment is complete, receipt will be printed automatically
                </p>
            </div>

        </div>

        <!-- Success Modal -->
        <div x-show="showSuccessModal" x-transition
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
            <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-8 text-center">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h3>
                <p class="text-gray-600 mb-6">Your payment has been processed successfully.</p>
                <button @click="printReceipt()"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
                    Print Receipt
                </button>
            </div>
        </div>

        <!-- Expired Modal -->
        <div x-show="showExpiredModal" x-transition
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
            <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-8 text-center">
                <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Payment Expired</h3>
                <p class="text-gray-600 mb-6">The QR code has expired. Please try again.</p>
                <button @click="window.location.reload()"
                    class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
                    Generate New QR Code
                </button>
            </div>
        </div>

    </div>

    <script>
        function qrisPayment() {
            return {
                timeLeft: {{ $transaction->expired_at->diffInSeconds(now()) ?? 900 }},
                checking: false,
                isCompleted: false,
                showSuccessModal: false,
                showExpiredModal: false,
                pollInterval: null,
                countdownInterval: null,

                get statusColor() {
                    if (this.isCompleted) return 'text-green-600';
                    if (this.timeLeft < 60) return 'text-red-600';
                    return 'text-blue-600';
                },

                get statusDot() {
                    if (this.isCompleted) return 'bg-green-600';
                    if (this.timeLeft < 60) return 'bg-red-600 animate-pulse';
                    return 'bg-blue-600 animate-pulse';
                },

                get statusText() {
                    if (this.isCompleted) return 'Payment completed';
                    if (this.timeLeft < 60) return 'Expiring soon...';
                    return 'Waiting for payment...';
                },

                init() {
                    this.startCountdown();
                    this.startPolling();
                },

                startCountdown() {
                    this.countdownInterval = setInterval(() => {
                        this.timeLeft--;

                        if (this.timeLeft <= 0) {
                            clearInterval(this.countdownInterval);
                            this.stopPolling();
                            this.showExpiredModal = true;
                        }
                    }, 1000);
                },

                startPolling() {
                    // Poll every 5 seconds
                    this.pollInterval = setInterval(() => {
                        this.checkStatus(true);
                    }, 5000);
                },

                stopPolling() {
                    if (this.pollInterval) {
                        clearInterval(this.pollInterval);
                        this.pollInterval = null;
                    }
                },

                async checkStatus(silent = false) {
                    if (this.checking || this.isCompleted) return;

                    this.checking = true;

                    try {
                        const response = await fetch(
                            `/api/payment/status?transaction_number={{ $transaction->transaction_number }}`, {
                                headers: {
                                    'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                                },
                            });

                        const data = await response.json();

                        if (data.success) {
                            if (data.status === 'success') {
                                this.handlePaymentSuccess();
                            } else if (['failed', 'expired', 'cancelled'].includes(data.status)) {
                                this.handlePaymentFailure(data.status);
                            } else if (!silent) {
                                // Show status toast
                                this.showToast('Payment status: ' + data.status);
                            }
                        } else {
                            if (!silent) {
                                this.showToast('Failed to check status', 'error');
                            }
                        }

                    } catch (error) {
                        console.error('Status check error:', error);
                        if (!silent) {
                            this.showToast('Connection error', 'error');
                        }
                    } finally {
                        this.checking = false;
                    }
                },

                handlePaymentSuccess() {
                    this.isCompleted = true;
                    this.stopPolling();
                    clearInterval(this.countdownInterval);
                    this.showSuccessModal = true;

                    // Auto-print after 2 seconds
                    setTimeout(() => {
                        this.printReceipt();
                    }, 2000);
                },

                handlePaymentFailure(status) {
                    this.stopPolling();
                    clearInterval(this.countdownInterval);

                    if (status === 'expired') {
                        this.showExpiredModal = true;
                    } else {
                        this.showToast('Payment ' + status, 'error');
                    }
                },

                async printReceipt() {
                    try {
                        const response = await fetch(`/api/pos/print/receipt/{{ $transaction->sales_order_id }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                            },
                        });

                        const result = await response.json();

                        if (result.success) {
                            window.location.href = '/pos/orders';
                        } else {
                            alert('Print failed: ' + result.error);
                        }

                    } catch (error) {
                        console.error('Print error:', error);
                        alert('Failed to print receipt');
                    }
                },

                cancelPayment() {
                    if (confirm('Are you sure you want to cancel this payment?')) {
                        this.stopPolling();
                        clearInterval(this.countdownInterval);
                        window.location.href = '/pos/orders';
                    }
                },

                formatTime(seconds) {
                    const mins = Math.floor(seconds / 60);
                    const secs = seconds % 60;
                    return `${mins}:${secs.toString().padStart(2, '0')}`;
                },

                showToast(message, type = 'info') {
                    // Simple toast notification
                    const toast = document.createElement('div');
                    toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white ${
                type === 'error' ? 'bg-red-600' : 'bg-blue-600'
            }`;
                    toast.textContent = message;
                    document.body.appendChild(toast);

                    setTimeout(() => toast.remove(), 3000);
                }
            }
        }
    </script>
@endsection
