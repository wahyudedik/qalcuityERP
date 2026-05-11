@extends('layouts.app')

@section('title', 'Integration Marketplace')

@section('content')
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Integration Marketplace</h1>
                        <p class="mt-2 text-sm text-gray-600">Connect your favorite tools and marketplaces</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('integrations.webhook-logs') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Webhook Logs
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <a href="{{ route('integrations.index', ['type' => 'all']) }}"
                        class="{{ $type === 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        All ({{ count($availableIntegrations) }})
                    </a>
                    @foreach ($availableIntegrations as $typeKey => $integrations)
                        <a href="{{ route('integrations.index', ['type' => $typeKey]) }}"
                            class="{{ $type === $typeKey ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm capitalize">
                            {{ str_replace('-', ' ', $typeKey) }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Your Integrations -->
            @if ($integrations->count() > 0)
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Your Integrations</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($integrations as $integration)
                            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center">
                                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                                <span
                                                    class="text-2xl font-bold text-blue-600">{{ strtoupper(substr($integration->slug, 0, 1)) }}</span>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-lg font-semibold text-gray-900">{{ $integration->name }}
                                                </h3>
                                                <p class="text-sm text-gray-500 capitalize">{{ $integration->type }}</p>
                                            </div>
                                        </div>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $integration->status === 'active' ? 'bg-green-100 text-green-800' : ($integration->status === 'error' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ ucfirst($integration->status) }}
                                        </span>
                                    </div>

                                    @if ($integration->last_sync_at)
                                        <div class="text-sm text-gray-600 mb-4">
                                            <p>Last sync: {{ $integration->last_sync_at->diffForHumans() }}</p>
                                            <p>Frequency: {{ ucfirst($integration->sync_frequency) }}</p>
                                        </div>
                                    @endif

                                    <div class="flex space-x-2">
                                        <a href="{{ route('integrations.show', $integration) }}"
                                            class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                            View Details
                                        </a>
                                        @if ($integration->isConnected())
                                            <button onclick="triggerSync('{{ $integration->id }}')"
                                                class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                                Sync Now
                                            </button>
                                        @else
                                            <a href="{{ route('integrations.setup', $integration) }}"
                                                class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                                Setup
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $integrations->links() }}
                    </div>
                </div>
            @endif

            <!-- Available Integrations -->
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Available Integrations</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($availableIntegrations as $typeKey => $availableList)
                        @if ($type === 'all' || $type === $typeKey)
                            @foreach ($availableList as $available)
                                <div
                                    class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow {{ isset($available['coming_soon']) ? 'opacity-75' : '' }}">
                                    <div class="p-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="flex items-center">
                                                <div
                                                    class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                                                    <span
                                                        class="text-2xl font-bold text-white">{{ strtoupper(substr($available['slug'], 0, 1)) }}</span>
                                                </div>
                                                <div class="ml-3">
                                                    <h3 class="text-lg font-semibold text-gray-900">
                                                        {{ $available['name'] }}</h3>
                                                    <p class="text-sm text-gray-500 capitalize">
                                                        {{ str_replace('-', ' ', $typeKey) }}</p>
                                                </div>
                                            </div>
                                            @if (isset($available['coming_soon']))
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Coming Soon
                                                </span>
                                            @endif
                                        </div>

                                        <p class="text-sm text-gray-600 mb-4">{{ $available['description'] }}</p>

                                        @if (!isset($available['coming_soon']))
                                            <form action="{{ route('integrations.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="name" value="{{ $available['name'] }}">
                                                <input type="hidden" name="slug" value="{{ $available['slug'] }}">
                                                <input type="hidden" name="type" value="{{ $typeKey }}">
                                                <input type="hidden" name="sync_frequency" value="hourly">

                                                <button type="submit"
                                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                                    Install Integration
                                                </button>
                                            </form>
                                        @else
                                            <button disabled
                                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-100 cursor-not-allowed">
                                                Coming Soon
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            async function triggerSync(integrationId) {
                const confirmed = await Dialog.confirm('Trigger sync for this integration?');
                if (!confirmed) return;

                fetch(`/integrations/${integrationId}/sync`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            type: 'all'
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Dialog.success('Sync job queued successfully!');
                        } else {
                            Dialog.warning('Failed to trigger sync: ' + data.error);
                        }
                    })
                    .catch(error => {
                        Dialog.warning('Error: ' + error.message);
                    });
            }
        </script>
    @endpush
@endsection
