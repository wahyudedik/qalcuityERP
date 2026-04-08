@extends('layouts.app')

@section('title', 'Setup ' . $integration->name)

@section('content')
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center">
                    <a href="{{ route('integrations.index') }}" class="text-gray-500 hover:text-gray-700 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Setup {{ $integration->name }}</h1>
                        <p class="mt-1 text-sm text-gray-500">Configure your integration settings</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @if (session('info'))
                <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">{{ session('info') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <ul class="text-sm text-red-700 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Setup Form -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Integration Configuration</h2>
                </div>
                <form action="{{ route('integrations.update', $integration) }}" method="POST" class="p-6 space-y-6">
                    @csrf
                    @method('PATCH')

                    <!-- Basic Settings -->
                    <div>
                        <h3 class="text-md font-medium text-gray-900 mb-4">Basic Settings</h3>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Integration
                                    Name</label>
                                <input type="text" name="name" id="name"
                                    value="{{ old('name', $integration->name) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required>
                            </div>

                            <div>
                                <label for="sync_frequency" class="block text-sm font-medium text-gray-700">Sync
                                    Frequency</label>
                                <select name="sync_frequency" id="sync_frequency"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="realtime"
                                        {{ $integration->sync_frequency === 'realtime' ? 'selected' : '' }}>Real-time (every
                                        5 minutes)</option>
                                    <option value="hourly"
                                        {{ $integration->sync_frequency === 'hourly' ? 'selected' : '' }}>Hourly</option>
                                    <option value="daily" {{ $integration->sync_frequency === 'daily' ? 'selected' : '' }}>
                                        Daily</option>
                                    <option value="weekly"
                                        {{ $integration->sync_frequency === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Shopify Configuration -->
                    @if ($integration->slug === 'shopify')
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-md font-medium text-gray-900 mb-4">Shopify Configuration</h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="shop_domain" class="block text-sm font-medium text-gray-700">
                                        Shop Domain
                                        <span class="text-gray-500">(e.g., your-store.myshopify.com)</span>
                                    </label>
                                    <input type="text" name="shop_domain" id="shop_domain"
                                        value="{{ old('shop_domain', $integration->getConfigValue('shop_domain')) }}"
                                        placeholder="your-store.myshopify.com"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <p class="mt-1 text-xs text-gray-500">You can find this in your Shopify admin URL</p>
                                </div>

                                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h4 class="text-sm font-medium text-blue-800">OAuth Authentication</h4>
                                            <p class="mt-1 text-sm text-blue-700">
                                                After saving, you'll be redirected to Shopify to authorize the connection.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    Save & Connect to Shopify
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- WooCommerce Configuration -->
                    @if ($integration->slug === 'woocommerce')
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-md font-medium text-gray-900 mb-4">WooCommerce Configuration</h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="store_url" class="block text-sm font-medium text-gray-700">
                                        Store URL
                                        <span class="text-gray-500">(e.g., https://your-store.com)</span>
                                    </label>
                                    <input type="url" name="store_url" id="store_url"
                                        value="{{ old('store_url', $integration->getConfigValue('store_url')) }}"
                                        placeholder="https://your-store.com"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label for="consumer_key" class="block text-sm font-medium text-gray-700">
                                        Consumer Key
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input type="password" name="consumer_key" id="consumer_key"
                                        value="{{ old('consumer_key', $integration->getConfigValue('consumer_key')) }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <p class="mt-1 text-xs text-gray-500">
                                        Generate in WooCommerce → Settings → Advanced → REST API
                                    </p>
                                </div>

                                <div>
                                    <label for="consumer_secret" class="block text-sm font-medium text-gray-700">
                                        Consumer Secret
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input type="password" name="consumer_secret" id="consumer_secret"
                                        value="{{ old('consumer_secret') }}"
                                        placeholder="Enter new secret (leave blank to keep existing)"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label for="webhook_secret" class="block text-sm font-medium text-gray-700">
                                        Webhook Secret (Optional)
                                    </label>
                                    <input type="password" name="webhook_secret" id="webhook_secret"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <p class="mt-1 text-xs text-gray-500">Used to verify webhook signatures</p>
                                </div>

                                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h4 class="text-sm font-medium text-yellow-800">Important</h4>
                                            <p class="mt-1 text-sm text-yellow-700">
                                                Make sure to create webhooks in WooCommerce pointing to:
                                                <code
                                                    class="bg-yellow-100 px-1 rounded">{{ config('app.url') }}/api/integrations/webhooks/woocommerce</code>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    Save & Test Connection
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Generic Configuration -->
                    @if (!in_array($integration->slug, ['shopify', 'woocommerce']))
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-md font-medium text-gray-900 mb-4">API Configuration</h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="api_key" class="block text-sm font-medium text-gray-700">API Key</label>
                                    <input type="password" name="config[api_key]" id="api_key"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label for="api_secret" class="block text-sm font-medium text-gray-700">API
                                        Secret</label>
                                    <input type="password" name="config[api_secret]" id="api_secret"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <button type="submit"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    Save Configuration
                                </button>
                            </div>
                        </div>
                    @endif
                </form>
            </div>

            <!-- Setup Guide -->
            <div class="mt-8 bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Setup Guide</h2>
                </div>
                <div class="p-6">
                    @if ($integration->slug === 'shopify')
                        <div class="space-y-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <span
                                        class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100 text-blue-600 font-semibold">1</span>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-sm font-medium text-gray-900">Create Custom App in Shopify</h4>
                                    <p class="mt-1 text-sm text-gray-600">Go to Shopify Admin → Settings → Apps and sales
                                        channels → Develop apps</p>
                                </div>
                            </div>
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <span
                                        class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100 text-blue-600 font-semibold">2</span>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-sm font-medium text-gray-900">Configure API Permissions</h4>
                                    <p class="mt-1 text-sm text-gray-600">Enable: read_products, write_products,
                                        read_orders, write_orders, read_inventory, write_inventory</p>
                                </div>
                            </div>
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <span
                                        class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100 text-blue-600 font-semibold">3</span>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-sm font-medium text-gray-900">Install App & Authorize</h4>
                                    <p class="mt-1 text-sm text-gray-600">Click "Install app" and authorize the connection
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif($integration->slug === 'woocommerce')
                        <div class="space-y-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <span
                                        class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100 text-blue-600 font-semibold">1</span>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-sm font-medium text-gray-900">Enable REST API</h4>
                                    <p class="mt-1 text-sm text-gray-600">Go to WooCommerce → Settings → Advanced → REST
                                        API</p>
                                </div>
                            </div>
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <span
                                        class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100 text-blue-600 font-semibold">2</span>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-sm font-medium text-gray-900">Generate API Keys</h4>
                                    <p class="mt-1 text-sm text-gray-600">Click "Add key", set permissions to Read/Write,
                                        and generate</p>
                                </div>
                            </div>
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <span
                                        class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100 text-blue-600 font-semibold">3</span>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-sm font-medium text-gray-900">Create Webhooks</h4>
                                    <p class="mt-1 text-sm text-gray-600">Go to WooCommerce → Settings → Advanced →
                                        Webhooks and create webhooks for orders and products</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
