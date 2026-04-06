@extends('layouts.app')
@section('title', 'Churn Risk Prediction')
@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('analytics.dashboard') }}" class="text-blue-600 hover:text-blue-900 text-sm">← Back</a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Churn Risk Prediction</h1>
        </div>
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                <div class="text-sm text-red-600 font-medium">High Risk</div>
                <div class="text-2xl font-bold text-red-700">{{ $churnData['summary']['high_risk'] ?? 0 }}</div>
            </div>
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                <div class="text-sm text-yellow-600 font-medium">Medium Risk</div>
                <div class="text-2xl font-bold text-yellow-700">{{ $churnData['summary']['medium_risk'] ?? 0 }}</div>
            </div>
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <div class="text-sm text-blue-600 font-medium">Total Revenue at Risk</div>
                <div class="text-xl font-bold text-blue-700">Rp
                    {{ number_format($churnData['summary']['total_revenue_at_risk'] ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Purchase</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days Inactive</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Risk Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Risk Level</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($churnData['at_risk_customers'] as $customer)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $customer['customer_name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $customer['email'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $customer['last_purchase_date'] ? \Carbon\Carbon::parse($customer['last_purchase_date'])->format('d M Y') : 'Never' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $customer['days_since_last_purchase'] ?? '-' }} days</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-{{ $customer['risk_level'] === 'high' ? 'red' : ($customer['risk_level'] === 'medium' ? 'yellow' : 'green') }}-600 h-2.5 rounded-full"
                                        style="width: {{ $customer['risk_score'] }}%"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">{{ $customer['risk_score'] }}/100</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $customer['risk_level'] === 'high' ? 'bg-red-100 text-red-800' : ($customer['risk_level'] === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                    {{ ucfirst($customer['risk_level']) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No at-risk customers
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
