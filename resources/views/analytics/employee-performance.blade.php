@extends('layouts.app')
@section('title', 'Employee Performance')
@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('analytics.dashboard') }}" class="text-blue-600 hover:text-blue-900 text-sm">← Back</a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Employee Performance Metrics</h1>
        </div>
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orders</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Avg Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trend</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($performanceData['employees'] as $emp)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($emp['rank'] == 1)
                                    🥇
                                @elseif($emp['rank'] == 2)
                                    🥈
                                @elseif($emp['rank'] == 3)
                                    🥉
                                @else
                                    #{{ $emp['rank'] }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $emp['employee_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($emp['total_orders']) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">Rp
                                {{ number_format($emp['total_revenue'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp
                                {{ number_format($emp['avg_order_value'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $emp['performance_score'] >= 80 ? 'bg-green-100 text-green-800' : ($emp['performance_score'] >= 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $emp['performance_score'] }}/100
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if ($emp['trend'] === 'up')
                                    <span class="text-green-600">↑ Up</span>
                                @elseif($emp['trend'] === 'down')
                                    <span class="text-red-600">↓ Down</span>
                                @else
                                    <span class="text-gray-500">→ Stable</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">No employee data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
