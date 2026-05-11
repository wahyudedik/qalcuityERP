@extends('layouts.app')

@section('title', 'Integration Ecosystem')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="integrationDashboard()">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">🔌 Integration Ecosystem</h1>
            <p class="mt-2 text-sm text-gray-600">Connect and manage all your business integrations in one place</p>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                        <span class="text-2xl">💳</span>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Payment Gateways</dt>
                            <dd class="text-2xl font-semibold text-gray-900" x-text="stats.payment_gateways || 0"></dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                        <span class="text-2xl">🛒</span>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">E-commerce Platforms</dt>
                            <dd class="text-2xl font-semibold text-gray-900" x-text="stats.ecommerce_platforms || 0"></dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                        <span class="text-2xl">📦</span>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Shipments</dt>
                            <dd class="text-2xl font-semibold text-gray-900" x-text="stats.active_shipments || 0"></dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                        <span class="text-2xl">💬</span>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Comm. Channels</dt>
                            <dd class="text-2xl font-semibold text-gray-900" x-text="stats.communication_channels || 0">
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Integration Cards Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            <!-- Payment Gateways -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-cyan-50">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">💳 Payment Gateways</h2>
                        <button @click="showPaymentModal = true" class="text-sm text-blue-600 hover:text-blue-800">
                            + Configure
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Midtrans -->
                        <div class="border rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <img src="https://midtrans.com/assets/images/logo-midtrans.png" alt="Midtrans"
                                        class="h-8 mr-3">
                                    <div>
                                        <h3 class="font-semibold">Midtrans</h3>
                                        <p class="text-xs text-gray-500">Credit Card, VA, E-Wallet</p>
                                    </div>
                                </div>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Available
                                </span>
                            </div>
                        </div>

                        <!-- Xendit -->
                        <div class="border rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div
                                        class="w-8 h-8 bg-indigo-600 rounded flex items-center justify-center text-white font-bold mr-3">
                                        X</div>
                                    <div>
                                        <h3 class="font-semibold">Xendit</h3>
                                        <p class="text-xs text-gray-500">Invoice, Disbursement</p>
                                    </div>
                                </div>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Available
                                </span>
                            </div>
                        </div>

                        <!-- Duitku -->
                        <div class="border rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div
                                        class="w-8 h-8 bg-orange-500 rounded flex items-center justify-center text-white font-bold mr-3">
                                        D</div>
                                    <div>
                                        <h3 class="font-semibold">Duitku</h3>
                                        <p class="text-xs text-gray-500">Multi-payment gateway</p>
                                    </div>
                                </div>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Available
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- E-commerce Platforms -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">🛒 E-commerce Platforms</h2>
                        <button @click="showEcommerceModal = true" class="text-sm text-green-600 hover:text-green-800">
                            + Connect
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Shopify -->
                        <div class="border rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div
                                        class="w-8 h-8 bg-green-600 rounded flex items-center justify-center text-white font-bold mr-3">
                                        S</div>
                                    <div>
                                        <h3 class="font-semibold">Shopify</h3>
                                        <p class="text-xs text-gray-500">Order & inventory sync</p>
                                    </div>
                                </div>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Not Connected
                                </span>
                            </div>
                        </div>

                        <!-- WooCommerce -->
                        <div class="border rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div
                                        class="w-8 h-8 bg-purple-600 rounded flex items-center justify-center text-white font-bold mr-3">
                                        W</div>
                                    <div>
                                        <h3 class="font-semibold">WooCommerce</h3>
                                        <p class="text-xs text-gray-500">WordPress integration</p>
                                    </div>
                                </div>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Not Connected
                                </span>
                            </div>
                        </div>

                        <!-- Tokopedia -->
                        <div class="border rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div
                                        class="w-8 h-8 bg-orange-500 rounded flex items-center justify-center text-white font-bold mr-3">
                                        T</div>
                                    <div>
                                        <h3 class="font-semibold">Tokopedia</h3>
                                        <p class="text-xs text-gray-500">Marketplace integration</p>
                                    </div>
                                </div>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Not Connected
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logistics Providers -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-pink-50">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">📦 Logistics & Shipping</h2>
                        <button @click="showLogisticsModal = true" class="text-sm text-purple-600 hover:text-purple-800">
                            + Add Provider
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- JNE -->
                        <div class="border rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div
                                        class="w-8 h-8 bg-red-600 rounded flex items-center justify-center text-white font-bold mr-3">
                                        J</div>
                                    <div>
                                        <h3 class="font-semibold">JNE</h3>
                                        <p class="text-xs text-gray-500">REG, YES, OKE services</p>
                                    </div>
                                </div>
                                <button class="text-xs text-blue-600 hover:text-blue-800">Configure</button>
                            </div>
                        </div>

                        <!-- J&T -->
                        <div class="border rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div
                                        class="w-8 h-8 bg-red-500 rounded flex items-center justify-center text-white font-bold mr-3">
                                        JT</div>
                                    <div>
                                        <h3 class="font-semibold">J&T Express</h3>
                                        <p class="text-xs text-gray-500">Regular & Express</p>
                                    </div>
                                </div>
                                <button class="text-xs text-blue-600 hover:text-blue-800">Configure</button>
                            </div>
                        </div>

                        <!-- SiCepat -->
                        <div class="border rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div
                                        class="w-8 h-8 bg-orange-600 rounded flex items-center justify-center text-white font-bold mr-3">
                                        SC</div>
                                    <div>
                                        <h3 class="font-semibold">SiCepat</h3>
                                        <p class="text-xs text-gray-500">REG, BEST, GOKIL</p>
                                    </div>
                                </div>
                                <button class="text-xs text-blue-600 hover:text-blue-800">Configure</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Communication Channels -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-yellow-50 to-amber-50">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">💬 Communication</h2>
                        <button @click="showCommunicationModal = true"
                            class="text-sm text-yellow-600 hover:text-yellow-800">
                            + Connect
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- WhatsApp Business -->
                        <div class="border rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div
                                        class="w-8 h-8 bg-green-500 rounded flex items-center justify-center text-white mr-3">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold">WhatsApp Business</h3>
                                        <p class="text-xs text-gray-500">Automated messaging</p>
                                    </div>
                                </div>
                                <button @click="connectWhatsApp()"
                                    class="text-xs text-green-600 hover:text-green-800">Connect</button>
                            </div>
                        </div>

                        <!-- Telegram Bot -->
                        <div class="border rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div
                                        class="w-8 h-8 bg-blue-500 rounded flex items-center justify-center text-white mr-3">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold">Telegram Bot</h3>
                                        <p class="text-xs text-gray-500">Bot notifications</p>
                                    </div>
                                </div>
                                <button class="text-xs text-blue-600 hover:text-blue-800">Connect</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accounting Integration -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-blue-50">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">📊 Accounting Sync</h2>
                        <button @click="showAccountingModal = true" class="text-sm text-indigo-600 hover:text-indigo-800">
                            + Connect
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Jurnal.id -->
                        <div class="border rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div
                                        class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center text-white font-bold mr-3">
                                        J</div>
                                    <div>
                                        <h3 class="font-semibold">Jurnal.id</h3>
                                        <p class="text-xs text-gray-500">Auto-sync invoices & payments</p>
                                    </div>
                                </div>
                                <button class="text-xs text-blue-600 hover:text-blue-800">Connect</button>
                            </div>
                        </div>

                        <!-- Accurate Online -->
                        <div class="border rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div
                                        class="w-8 h-8 bg-green-600 rounded flex items-center justify-center text-white font-bold mr-3">
                                        A</div>
                                    <div>
                                        <h3 class="font-semibold">Accurate Online</h3>
                                        <p class="text-xs text-gray-500">Financial data sync</p>
                                    </div>
                                </div>
                                <button class="text-xs text-blue-600 hover:text-blue-800">Connect</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banking Integration -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-teal-50 to-cyan-50">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">🏦 Banking</h2>
                        <button @click="showBankingModal = true" class="text-sm text-teal-600 hover:text-teal-800">
                            + Add Account
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="text-center py-8">
                        <div class="text-6xl mb-4">🏦</div>
                        <p class="text-gray-600 mb-4">No bank accounts connected yet</p>
                        <button @click="showBankingModal = true"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700">
                            Add Bank Account
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <h2 class="text-xl font-bold mb-4">⚡ Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white/20 rounded-lg p-4 opacity-50 cursor-not-allowed" x-data="{ showTooltip: false }"
                    @mouseenter="showTooltip = true" @mouseleave="showTooltip = false">
                    <div class="relative">
                        <div class="text-2xl mb-2">🧪</div>
                        <div class="font-semibold">Test Payment Gateway</div>
                        <div class="text-xs opacity-80">Verify configuration</div>
                        <span x-show="showTooltip" x-transition
                            class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1 text-xs text-white bg-gray-800 rounded-lg whitespace-nowrap z-50">
                            Fitur akan segera tersedia
                        </span>
                    </div>
                </div>

                <button @click="syncAllPlatforms()"
                    class="bg-white/20 hover:bg-white/30 rounded-lg p-4 transition-colors">
                    <div class="text-2xl mb-2">🔄</div>
                    <div class="font-semibold">Sync All Platforms</div>
                    <div class="text-xs opacity-80">Pull latest orders</div>
                </button>

                <div class="bg-white/20 rounded-lg p-4 opacity-50 cursor-not-allowed" x-data="{ showTooltip: false }"
                    @mouseenter="showTooltip = true" @mouseleave="showTooltip = false">
                    <div class="relative">
                        <div class="text-2xl mb-2">📋</div>
                        <div class="font-semibold">View Integration Logs</div>
                        <div class="text-xs opacity-80">Check sync history</div>
                        <span x-show="showTooltip" x-transition
                            class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1 text-xs text-white bg-gray-800 rounded-lg whitespace-nowrap z-50">
                            Fitur akan segera tersedia
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function integrationDashboard() {
            return {
                stats: {},
                showPaymentModal: false,
                showEcommerceModal: false,
                showLogisticsModal: false,
                showCommunicationModal: false,
                showAccountingModal: false,
                showBankingModal: false,

                init() {
                    this.loadStats();
                },

                async loadStats() {
                    try {
                        const response = await fetch('/integrations');
                        const data = await response.json();
                        this.stats = data;
                    } catch (error) {
                        console.error('Failed to load stats:', error);
                    }
                },

                async connectWhatsApp() {
                    const phoneNumber = await Dialog.prompt('Enter WhatsApp Business number (e.g., 6281234567890):');
                    if (!phoneNumber) return;

                    const apiKey = await Dialog.prompt('Enter WhatsApp API key:');
                    if (!apiKey) return;

                    try {
                        const response = await fetch('/integrations/communication/whatsapp/connect', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                phone_number: phoneNumber,
                                api_key: apiKey
                            })
                        });

                        const result = await response.json();
                        if (result.success) {
                            Dialog.success('WhatsApp connected successfully!');
                            this.loadStats();
                        } else {
                            Dialog.warning('Failed to connect WhatsApp');
                        }
                    } catch (error) {
                        Dialog.warning('Error: ' + error.message);
                    }
                },

                async syncAllPlatforms() {
                    Dialog.alert('Syncing all platforms - this may take a few minutes...');
                }
            }
        }
    </script>
@endsection
