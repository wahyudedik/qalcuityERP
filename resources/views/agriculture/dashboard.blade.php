@extends('layouts.app')

@section('title', 'Agriculture Dashboard')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">🌾 Agriculture Dashboard</h1>
            <p class="mt-2 text-sm text-gray-600">Smart farming management system</p>
        </div>

        <!-- Weather Widget -->
        @if ($weather)
            <div class="bg-gradient-to-r from-blue-500 to-cyan-500 rounded-lg shadow-lg p-6 mb-8 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold">{{ $weather->location_name ?? 'Farm Location' }}</h2>
                        <p class="text-4xl font-bold mt-2">{{ round($weather->temperature) }}°C</p>
                        <p class="text-lg">{{ ucfirst($weather->weather_description) }}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-6xl">
                            @if (stripos($weather->weather_condition, 'rain') !== false)
                                🌧️
                            @elseif(stripos($weather->weather_condition, 'cloud') !== false)
                                ☁️
                            @else
                                ☀️
                            @endif
                        </div>
                        <p class="mt-2">Humidity: {{ $weather->humidity }}%</p>
                        <p>Wind: {{ $weather->wind_speed }} m/s</p>
                    </div>
                </div>

                @if (count($recommendations) > 0)
                    <div class="mt-4 bg-white/20 rounded-lg p-4">
                        <h3 class="font-semibold mb-2">🌱 Farming Recommendations:</h3>
                        <ul class="space-y-1">
                            @foreach ($recommendations as $rec)
                                <li class="flex items-start">
                                    <span class="mr-2">
                                        @if ($rec['type'] === 'warning')
                                            ⚠️
                                        @elseif($rec['type'] === 'alert')
                                            🔴
                                        @elseif($rec['type'] === 'success')
                                            ✅
                                        @else
                                            ℹ️
                                        @endif
                                    </span>
                                    <span>{{ $rec['message'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Active Crops -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                        <span class="text-2xl">🌾</span>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Crops</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $activeCrops->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Upcoming Irrigations -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                        <span class="text-2xl">💧</span>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Upcoming Irrigations</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ count($upcomingIrrigations) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Market Prices Tracked -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                        <span class="text-2xl">💰</span>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Commodities Tracked</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ count($marketSummary) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Weather Status -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                        <span class="text-2xl">🌤️</span>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Weather Status</dt>
                            <dd class="text-lg font-semibold text-gray-900">
                                @if ($weather)
                                    @if ($weather->isSuitableForFarming())
                                        <span class="text-green-600">✅ Suitable</span>
                                    @else
                                        <span class="text-red-600">⚠️ Not Ideal</span>
                                    @endif
                                @else
                                    <span class="text-gray-600">No Data</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Crops List -->
        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Active Crop Cycles</h2>
            </div>
            <div class="p-6">
                @if ($activeCrops->count() > 0)
                    <div class="space-y-4">
                        @foreach ($activeCrops as $crop)
                            <div class="border rounded-lg p-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ $crop->crop_name }}</h3>
                                        <p class="text-sm text-gray-600">{{ $crop->area_hectares }} hectares • Planted:
                                            {{ $crop->planting_date->format('d M Y') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                @if ($crop->growth_stage === 'ready_to_harvest') bg-green-100 text-green-800
                                @elseif($crop->growth_stage === 'flowering') bg-yellow-100 text-yellow-800
                                @else bg-blue-100 text-blue-800 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $crop->growth_stage)) }}
                                        </span>
                                        <p class="text-xs text-gray-500 mt-1">Day {{ $crop->days_since_planted }}</p>
                                    </div>
                                </div>

                                @if ($crop->pest_detections_count > 0)
                                    <div class="mt-2 text-sm text-orange-600">
                                        ⚠️ {{ $crop->pest_detections_count }} pest detection(s)
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">No active crops. Start a new crop cycle to begin tracking.</p>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="#" class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <span class="text-3xl mr-4">📸</span>
                    <div>
                        <h3 class="font-semibold text-gray-900">Detect Pests</h3>
                        <p class="text-sm text-gray-600">Upload plant photo for AI analysis</p>
                    </div>
                </div>
            </a>

            <a href="#" class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <span class="text-3xl mr-4">💧</span>
                    <div>
                        <h3 class="font-semibold text-gray-900">Manage Irrigation</h3>
                        <p class="text-sm text-gray-600">View and adjust schedules</p>
                    </div>
                </div>
            </a>

            <a href="#" class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <span class="text-3xl mr-4">💰</span>
                    <div>
                        <h3 class="font-semibold text-gray-900">Market Prices</h3>
                        <p class="text-sm text-gray-600">Check commodity prices & trends</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
@endsection
