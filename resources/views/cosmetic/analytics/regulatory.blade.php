@extends('layouts.app')

@section('title', 'Regulatory Dashboard')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Regulatory Compliance Dashboard</h1>
                    <p class="mt-1 text-sm text-gray-500">BPOM registration and regulatory overview</p>
                </div>
                <a href="{{ route('cosmetic.analytics.dashboard') }}" class="text-blue-600 hover:text-blue-800">← Back to
                    Analytics</a>
            </div>
        </div>

        <!-- Registration Stats -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Total Registrations</div>
                <div class="mt-2 text-3xl font-bold text-gray-900">{{ $registrationStats['total'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Approved</div>
                <div class="mt-2 text-3xl font-bold text-green-600">{{ $registrationStats['approved'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Pending</div>
                <div class="mt-2 text-3xl font-bold text-yellow-600">{{ $registrationStats['pending'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Expired</div>
                <div class="mt-2 text-3xl font-bold text-red-600">{{ $registrationStats['expired'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Expiring Soon (90d)</div>
                <div class="mt-2 text-3xl font-bold text-orange-600">{{ $registrationStats['expiring_soon'] }}</div>
            </div>
        </div>

        <!-- Compliance Metrics -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4">Compliance Health Metrics</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 bg-green-50 rounded-lg">
                    <div class="text-sm text-gray-600">Products with Valid Registration</div>
                    <div class="mt-2 text-2xl font-bold text-green-700">
                        {{ $complianceMetrics['products_with_valid_registration'] }}</div>
                </div>
                <div class="p-4 bg-red-50 rounded-lg">
                    <div class="text-sm text-gray-600">Products Missing Registration</div>
                    <div class="mt-2 text-2xl font-bold text-red-700">
                        {{ $complianceMetrics['products_missing_registration'] }}</div>
                    @if ($complianceMetrics['products_missing_registration'] > 0)
                        <div class="text-xs text-red-600 mt-1">⚠️ Action Required</div>
                    @endif
                </div>
                <div class="p-4 bg-yellow-50 rounded-lg">
                    <div class="text-sm text-gray-600">Restricted Ingredients in Use</div>
                    <div class="mt-2 text-2xl font-bold text-yellow-700">
                        {{ $complianceMetrics['restricted_ingredients_in_use'] }}</div>
                </div>
                <div class="p-4 bg-blue-50 rounded-lg">
                    <div class="text-sm text-gray-600">SDS Up to Date</div>
                    <div class="mt-2 text-2xl font-bold text-blue-700">{{ $complianceMetrics['sds_up_to_date'] }}</div>
                </div>
            </div>
        </div>

        <!-- Upcoming Expirations -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold">Upcoming Registration Expirations (180 days)</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registration No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days Remaining</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($upcomingExpirations as $reg)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $reg->formula?->formula_name ?? 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $reg->registration_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $reg->expiry_date->format('d M Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php $days = now()->diffInDays($reg->expiry_date, false); @endphp
                                <span
                                    class="text-sm font-semibold {{ $days < 30 ? 'text-red-600' : ($days < 90 ? 'text-orange-600' : 'text-green-600') }}">
                                    {{ $days }} days
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Renewal
                                    Needed</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">No upcoming expirations</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Recent Submissions -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold">Recent Submissions</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($recentSubmissions as $reg)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $reg->formula?->formula_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $reg->submitted_at?->format('d M Y') ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $reg->status === 'approved' ? 'bg-green-100 text-green-800' : ($reg->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($reg->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
