@extends('layouts.app')

@section('title', 'Customer Usage Portal')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Customer Usage Portal</h1>
                <p class="text-gray-600 mt-1">Monitor & manage customer internet usage</p>
            </div>
            <a href="{{ route('telecom.dashboard') }}"
                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Dashboard
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Customers Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Customers with Active Subscriptions</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Package</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Device</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quota
                                Usage</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($customers as $customer)
                            @foreach ($customer->telecomSubscriptions as $subscription)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <span
                                                    class="text-blue-600 font-bold">{{ substr($customer->name, 0, 1) }}</span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $customer->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $customer->email ?? 'No email' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $subscription->package->name }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $subscription->package->download_speed_mbps }}/{{ $subscription->package->upload_speed_mbps }}
                                            Mbps
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $subscription->device->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $subscription->device->ip_address }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $subscription->status === 'active'
                                            ? 'bg-green-100 text-green-800'
                                            : ($subscription->status === 'suspended'
                                                ? 'bg-yellow-100 text-yellow-800'
                                                : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($subscription->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($subscription->package->quota_bytes)
                                            @php
                                                $used = $subscription->current_usage_bytes ?? 0;
                                                $total = $subscription->package->quota_bytes;
                                                $percent = min(100, round(($used / $total) * 100, 2));
                                                $color = $percent > 90 ? 'red' : ($percent > 70 ? 'yellow' : 'green');
                                            @endphp
                                            <div class="w-32">
                                                <div class="flex justify-between text-xs mb-1">
                                                    <span>{{ round($used / 1073741824, 2) }} GB</span>
                                                    <span>{{ round($total / 1073741824, 2) }} GB</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="bg-{{ $color }}-600 h-2 rounded-full"
                                                        style="width: {{ $percent }}%"></div>
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">{{ $percent }}% used</div>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-500">Unlimited</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <a href="{{ route('telecom.customers.show-usage', $customer) }}"
                                            class="text-blue-600 hover:text-blue-900">View Details</a>

                                        @if ($subscription->status === 'active')
                                            <form action="{{ route('telecom.customers.suspend', $customer) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-yellow-600 hover:text-yellow-900"
                                                    onclick="return confirm('Suspend subscription?')">Suspend</button>
                                            </form>
                                        @elseif($subscription->status === 'suspended')
                                            <form action="{{ route('telecom.customers.reactivate', $customer) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="text-green-600 hover:text-green-900">Reactivate</button>
                                            </form>
                                        @endif

                                        <form action="{{ route('telecom.customers.reset-quota', $customer) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-purple-600 hover:text-purple-900"
                                                onclick="return confirm('Reset quota for this customer?')">Reset
                                                Quota</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <p class="mt-2 text-sm">Tidak ada customer dengan subscription aktif</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($customers->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
