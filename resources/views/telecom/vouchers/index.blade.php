@extends('layouts.app')

@section('title', 'Voucher Management')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Voucher Management</h1>
                <p class="text-gray-600 mt-1">Generate, print & manage internet vouchers</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('telecom.vouchers.create') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Generate Vouchers
                </a>
                <a href="{{ route('telecom.dashboard') }}"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                    Back to Dashboard
                </a>
            </div>
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

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                <p class="text-sm text-gray-600">Total Vouchers</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                <p class="text-sm text-gray-600">Unused</p>
                <p class="text-2xl font-bold text-green-600">{{ number_format($stats['unused']) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
                <p class="text-sm text-gray-600">Used</p>
                <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['used']) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
                <p class="text-sm text-gray-600">Expired</p>
                <p class="text-2xl font-bold text-red-600">{{ number_format($stats['expired']) }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('telecom.vouchers.index') }}"
                class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="unused" {{ request('status') === 'unused' ? 'selected' : '' }}>Unused</option>
                        <option value="used" {{ request('status') === 'used' ? 'selected' : '' }}>Used</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>Revoked</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Batch Number</label>
                    <select name="batch_number"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Batches</option>
                        @foreach ($batches as $batch)
                            <option value="{{ $batch }}"
                                {{ request('batch_number') === $batch ? 'selected' : '' }}>
                                {{ $batch }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Package</label>
                    <select name="package_id"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Packages</option>
                        @foreach ($packages as $package)
                            <option value="{{ $package->id }}"
                                {{ request('package_id') == $package->id ? 'selected' : '' }}>
                                {{ $package->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search Code</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search voucher code..."
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Print Selected -->
        @if ($vouchers->count() > 0)
            <div class="mb-4 flex justify-between items-center">
                <p class="text-sm text-gray-600">Showing {{ $vouchers->firstItem() }} - {{ $vouchers->lastItem() }} of
                    {{ $vouchers->total() }} vouchers</p>
                <form action="{{ route('telecom.vouchers.print') }}" method="GET" target="_blank" class="inline">
                    @if (request('batch_number'))
                        <input type="hidden" name="batch_number" value="{{ request('batch_number') }}">
                    @endif
                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print Unused Vouchers
                    </button>
                </form>
            </div>
        @endif

        <!-- Vouchers Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Package</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Validity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($vouchers as $voucher)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-mono font-bold text-gray-900">{{ $voucher->code }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $voucher->package->name }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $voucher->package->download_speed_mbps }}/{{ $voucher->package->upload_speed_mbps }}
                                        Mbps
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-xs text-gray-600">{{ $voucher->batch_number ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs text-gray-900">
                                        {{ $voucher->valid_from->format('d M Y') }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        to {{ $voucher->valid_until->format('d M Y H:i') }}
                                    </div>
                                    @if ($voucher->isExpired())
                                        <span class="text-xs text-red-600 font-semibold">EXPIRED</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $voucher->sale_price ? 'Rp ' . number_format($voucher->sale_price, 0, ',', '.') : '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $voucher->status === 'unused'
                                        ? 'bg-green-100 text-green-800'
                                        : ($voucher->status === 'used'
                                            ? 'bg-purple-100 text-purple-800'
                                            : ($voucher->status === 'expired'
                                                ? 'bg-red-100 text-red-800'
                                                : 'bg-gray-100 text-gray-800')) }}">
                                        {{ ucfirst($voucher->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($voucher->customer)
                                        <div class="text-sm text-gray-900">{{ $voucher->customer->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $voucher->used_at?->format('d M Y H:i') }}
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    @if ($voucher->status === 'unused' && !$voucher->isExpired())
                                        <form action="{{ route('telecom.vouchers.revoke', $voucher) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            <button type="submit" class="text-red-600 hover:text-red-900"
                                                onclick="return confirm('Revoke this voucher?')">Revoke</button>
                                        </form>

                                        <form action="{{ route('telecom.vouchers.extend', $voucher) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            <input type="hidden" name="hours" value="24">
                                            <button type="submit" class="text-blue-600 hover:text-blue-900">+24h</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                    </svg>
                                    <p class="mt-2 text-sm">Tidak ada voucher ditemukan</p>
                                    <a href="{{ route('telecom.vouchers.create') }}"
                                        class="text-blue-600 hover:text-blue-900 text-sm mt-2 inline-block">
                                        Generate vouchers sekarang
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($vouchers->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $vouchers->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
