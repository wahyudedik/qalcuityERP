

<?php $__env->startSection('title', 'Payment Gateway Settings'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-6xl mx-auto px-4 py-8" x-data="gatewaySettings()">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Payment Gateway Settings</h1>
            <p class="text-gray-600">Configure your payment gateway providers to accept QRIS and other payment methods</p>
        </div>

        <!-- Active Gateways -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

            <!-- Midtrans Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border-2"
                :class="gateways.midtrans?.is_active ? 'border-green-500' : 'border-gray-200'">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                    <div class="flex items-center justify-between text-white">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center">
                                <span class="text-blue-600 font-bold text-lg">M</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg">Midtrans</h3>
                                <p class="text-sm opacity-90">Indonesia's #1 Payment Gateway</p>
                            </div>
                        </div>
                        <div x-show="gateways.midtrans?.is_active"
                            class="bg-green-500 text-white text-xs px-3 py-1 rounded-full">
                            Active
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <?php if(isset($gateways['midtrans'])): ?>
                        <div class="space-y-3 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Environment:</span>
                                <span class="font-medium"
                                    :class="gateways.midtrans.environment === 'production' ? 'text-green-600' :
                                        'text-orange-600'"
                                    x-text="gateways.midtrans.environment === 'production' ? 'Production' : 'Sandbox'"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Last Verified:</span>
                                <span class="font-medium"
                                    x-text="gateways.midtrans.last_verified_at || 'Not verified'"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Default:</span>
                                <span class="font-medium" x-text="gateways.midtrans.is_default ? 'Yes' : 'No'"></span>
                            </div>
                        </div>

                        <div class="flex space-x-2">
                            <button @click="editGateway('midtrans')"
                                class="flex-1 bg-blue-50 hover:bg-blue-100 text-blue-700 py-2 px-4 rounded-lg transition-colors text-sm font-medium">
                                Configure
                            </button>
                            <button @click="testGateway('midtrans')"
                                class="flex-1 bg-gray-50 hover:bg-gray-100 text-gray-700 py-2 px-4 rounded-lg transition-colors text-sm font-medium">
                                Test
                            </button>
                            <button @click="toggleGateway('midtrans')"
                                :class="gateways.midtrans.is_active ? 'bg-red-50 hover:bg-red-100 text-red-700' :
                                    'bg-green-50 hover:bg-green-100 text-green-700'"
                                class="py-2 px-4 rounded-lg transition-colors text-sm font-medium">
                                <span x-text="gateways.midtrans.is_active ? 'Disable' : 'Enable'"></span>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <p class="text-gray-500 mb-4">Not configured yet</p>
                            <button @click="editGateway('midtrans')"
                                class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-lg transition-colors font-medium">
                                Setup Midtrans
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Xendit Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border-2"
                :class="gateways.xendit?.is_active ? 'border-green-500' : 'border-gray-200'">
                <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
                    <div class="flex items-center justify-between text-white">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center">
                                <span class="text-purple-600 font-bold text-lg">X</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg">Xendit</h3>
                                <p class="text-sm opacity-90">Modern Payment Infrastructure</p>
                            </div>
                        </div>
                        <div x-show="gateways.xendit?.is_active"
                            class="bg-green-500 text-white text-xs px-3 py-1 rounded-full">
                            Active
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <?php if(isset($gateways['xendit'])): ?>
                        <div class="space-y-3 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Environment:</span>
                                <span class="font-medium"
                                    :class="gateways.xendit.environment === 'production' ? 'text-green-600' : 'text-orange-600'"
                                    x-text="gateways.xendit.environment === 'production' ? 'Production' : 'Sandbox'"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Last Verified:</span>
                                <span class="font-medium"
                                    x-text="gateways.xendit.last_verified_at || 'Not verified'"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Default:</span>
                                <span class="font-medium" x-text="gateways.xendit.is_default ? 'Yes' : 'No'"></span>
                            </div>
                        </div>

                        <div class="flex space-x-2">
                            <button @click="editGateway('xendit')"
                                class="flex-1 bg-purple-50 hover:bg-purple-100 text-purple-700 py-2 px-4 rounded-lg transition-colors text-sm font-medium">
                                Configure
                            </button>
                            <button @click="testGateway('xendit')"
                                class="flex-1 bg-gray-50 hover:bg-gray-100 text-gray-700 py-2 px-4 rounded-lg transition-colors text-sm font-medium">
                                Test
                            </button>
                            <button @click="toggleGateway('xendit')"
                                :class="gateways.xendit.is_active ? 'bg-red-50 hover:bg-red-100 text-red-700' :
                                    'bg-green-50 hover:bg-green-100 text-green-700'"
                                class="py-2 px-4 rounded-lg transition-colors text-sm font-medium">
                                <span x-text="gateways.xendit.is_active ? 'Disable' : 'Enable'"></span>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <p class="text-gray-500 mb-4">Not configured yet</p>
                            <button @click="editGateway('xendit')"
                                class="bg-purple-600 hover:bg-purple-700 text-white py-2 px-6 rounded-lg transition-colors font-medium">
                                Setup Xendit
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Configuration Modal -->
        <div x-show="showConfigModal" x-transition
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">

                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-4">
                    <div class="flex items-center justify-between text-white">
                        <h3 class="text-lg font-bold" x-text="'Configure ' + editingProvider"></h3>
                        <button @click="closeModal" class="text-white hover:text-gray-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4">

                    <!-- Environment Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Environment</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button @click="formData.environment = 'sandbox'"
                                :class="formData.environment === 'sandbox' ? 'ring-2 ring-blue-500 bg-blue-50' :
                                    'hover:bg-gray-50'"
                                class="p-4 border-2 border-gray-200 rounded-lg transition-all text-left">
                                <div class="font-semibold text-gray-900 mb-1">Sandbox</div>
                                <div class="text-xs text-gray-500">For testing</div>
                            </button>
                            <button @click="formData.environment = 'production'"
                                :class="formData.environment === 'production' ? 'ring-2 ring-green-500 bg-green-50' :
                                    'hover:bg-gray-50'"
                                class="p-4 border-2 border-gray-200 rounded-lg transition-all text-left">
                                <div class="font-semibold text-gray-900 mb-1">Production</div>
                                <div class="text-xs text-gray-500">Live transactions</div>
                            </button>
                        </div>
                    </div>

                    <!-- Midtrans Credentials -->
                    <template x-if="editingProvider === 'midtrans'">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Server Key</label>
                                <input type="password" x-model="formData.credentials.server_key"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="SB-Mid-server-xxxxxxxxxxxxx">
                                <p class="text-xs text-gray-500 mt-1">Found in Midtrans Dashboard → Settings → Access Keys
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Client Key</label>
                                <input type="password" x-model="formData.credentials.client_key"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="SB-Mid-client-xxxxxxxxxxxxx">
                            </div>
                        </div>
                    </template>

                    <!-- Xendit Credentials -->
                    <template x-if="editingProvider === 'xendit'">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Secret API Key</label>
                                <input type="password" x-model="formData.credentials.api_key"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                    placeholder="xnd_development_xxxxxxxxxxxxx">
                                <p class="text-xs text-gray-500 mt-1">Found in Xendit Dashboard → Developers → API Keys</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Public Key (Optional)</label>
                                <input type="password" x-model="formData.credentials.public_key"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                    placeholder="xnd_public_development_xxxxxxxxxxxxx">
                            </div>
                        </div>
                    </template>

                    <!-- Webhook Secret -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Webhook Secret (Optional)
                            <span class="text-xs text-gray-500 font-normal">- For enhanced security</span>
                        </label>
                        <input type="password" x-model="formData.webhook_secret"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="your-webhook-secret">
                    </div>

                    <!-- Set as Default -->
                    <div class="flex items-center">
                        <input type="checkbox" x-model="formData.is_default" id="set_default"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="set_default" class="ml-2 text-sm text-gray-700">
                            Set as default payment gateway
                        </label>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    <div class="flex space-x-3">
                        <button @click="closeModal"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors font-medium">
                            Cancel
                        </button>
                        <button @click="saveConfiguration" :disabled="saving"
                            class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-medium disabled:opacity-50 flex items-center justify-center space-x-2">
                            <svg x-show="saving" class="animate-spin h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span x-text="saving ? 'Saving...' : 'Save Configuration'"></span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Info Section -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
            <h3 class="font-semibold text-blue-900 mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                How to Get Credentials
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-800">
                <div>
                    <p class="font-medium mb-2">Midtrans:</p>
                    <ol class="list-decimal list-inside space-y-1 text-blue-700">
                        <li>Register at <a href="https://midtrans.com" target="_blank" class="underline">midtrans.com</a>
                        </li>
                        <li>Complete KYC verification</li>
                        <li>Go to Dashboard → Settings → Access Keys</li>
                        <li>Copy Server Key & Client Key</li>
                    </ol>
                </div>
                <div>
                    <p class="font-medium mb-2">Xendit:</p>
                    <ol class="list-decimal list-inside space-y-1 text-blue-700">
                        <li>Register at <a href="https://xendit.co" target="_blank" class="underline">xendit.co</a></li>
                        <li>Complete business verification</li>
                        <li>Go to Dashboard → Developers → API Keys</li>
                        <li>Copy Secret API Key</li>
                    </ol>
                </div>
            </div>
        </div>

    </div>

    <script>
        function gatewaySettings() {
            return {
                gateways: <?php echo json_encode($gateways ?? [], 15, 512) ?>,
                showConfigModal: false,
                editingProvider: null,
                saving: false,
                formData: {
                    provider: '',
                    environment: 'sandbox',
                    credentials: {},
                    webhook_secret: '',
                    is_default: false,
                    is_active: true
                },

                editGateway(provider) {
                    this.editingProvider = provider;
                    this.formData = {
                        provider: provider,
                        environment: this.gateways[provider]?.environment || 'sandbox',
                        credentials: {},
                        webhook_secret: '',
                        is_default: this.gateways[provider]?.is_default || false,
                        is_active: this.gateways[provider]?.is_active || true
                    };
                    this.showConfigModal = true;
                },

                async saveConfiguration() {
                    this.saving = true;

                    try {
                        const response = await fetch('/api/payment/gateways', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                            },
                            body: JSON.stringify(this.formData)
                        });

                        const result = await response.json();

                        if (result.success) {
                            alert('Configuration saved successfully!');
                            window.location.reload();
                        } else {
                            alert('Error: ' + result.error);
                        }

                    } catch (error) {
                        console.error('Save error:', error);
                        alert('Failed to save configuration');
                    } finally {
                        this.saving = false;
                    }
                },

                async testGateway(provider) {
                    try {
                        const response = await fetch('/api/payment/gateways/test', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                            },
                            body: JSON.stringify({
                                provider
                            })
                        });

                        const result = await response.json();

                        if (result.success) {
                            alert('✓ Credentials verified successfully!');
                        } else {
                            alert('✗ Verification failed: ' + result.error);
                        }

                    } catch (error) {
                        alert('Test failed: ' + error.message);
                    }
                },

                async toggleGateway(provider) {
                    const gatewayId = this.gateways[provider]?.id;
                    if (!gatewayId) return;

                    try {
                        const response = await fetch(`/api/payment/gateways/${gatewayId}/toggle`, {
                            method: 'POST',
                            headers: {
                                'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                            }
                        });

                        const result = await response.json();

                        if (result.success) {
                            window.location.reload();
                        }

                    } catch (error) {
                        alert('Failed to toggle gateway');
                    }
                },

                closeModal() {
                    this.showConfigModal = false;
                    this.editingProvider = null;
                }
            }
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\settings\payment-gateways.blade.php ENDPATH**/ ?>