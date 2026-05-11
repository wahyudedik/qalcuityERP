<x-app-layout>
    <x-slot name="header">
        {{ __('Voucher Management') }}
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('Voucher Management') }}</h1>
                    <p class="text-gray-600 mt-1">
                        {{ __('Generate, print & manage internet vouchers') }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('telecom.vouchers.create') }}"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fas fa-plus"></i>
                        {{ __('Generate Vouchers') }}
                    </a>
                    <a href="{{ route('telecom.dashboard') }}"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                        {{ __('Back to Dashboard') }}
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-blue-500">
                    <p class="text-sm text-gray-600">{{ __('Total Vouchers') }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}
                    </p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-green-500">
                    <p class="text-sm text-gray-600">{{ __('Unused') }}</p>
                    <p class="text-2xl font-bold text-green-600">
                        {{ number_format($stats['unused']) }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-purple-500">
                    <p class="text-sm text-gray-600">{{ __('Used') }}</p>
                    <p class="text-2xl font-bold text-purple-600">
                        {{ number_format($stats['used']) }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-red-500">
                    <p class="text-sm text-gray-600">{{ __('Expired') }}</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($stats['expired']) }}
                    </p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 mb-6">
                <form method="GET" action="{{ route('telecom.vouchers.index') }}"
                    class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Status') }}</label>
                        <select name="status"
                            class="w-full border-gray-300 bg-white text-gray-900 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">{{ __('All Status') }}</option>
                            <option value="unused" {{ request('status') === 'unused' ? 'selected' : '' }}>
                                {{ __('Unused') }}</option>
                            <option value="used" {{ request('status') === 'used' ? 'selected' : '' }}>
                                {{ __('Used') }}</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>
                                {{ __('Expired') }}</option>
                            <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>
                                {{ __('Revoked') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Batch Number') }}</label>
                        <select name="batch_number"
                            class="w-full border-gray-300 bg-white text-gray-900 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">{{ __('All Batches') }}</option>
                            @foreach ($batches as $batch)
                                <option value="{{ $batch }}"
                                    {{ request('batch_number') === $batch ? 'selected' : '' }}>
                                    {{ $batch }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Package') }}</label>
                        <select name="package_id"
                            class="w-full border-gray-300 bg-white text-gray-900 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">{{ __('All Packages') }}</option>
                            @foreach ($packages as $package)
                                <option value="{{ $package->id }}"
                                    {{ request('package_id') == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Search Code') }}</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="{{ __('Search voucher code...') }}"
                            class="w-full border-gray-300 bg-white text-gray-900 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-filter mr-1"></i> {{ __('Filter') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Print Selected -->
            @if ($vouchers->count() > 0)
                <div class="mb-4 flex justify-between items-center">
                    <p class="text-sm text-gray-600">{{ __('Showing') }}
                        {{ $vouchers->firstItem() }} - {{ $vouchers->lastItem() }} {{ __('of') }}
                        {{ $vouchers->total() }} {{ __('vouchers') }}</p>
                    <form action="{{ route('telecom.vouchers.print') }}" method="GET" target="_blank" class="inline">
                        @if (request('batch_number'))
                            <input type="hidden" name="batch_number" value="{{ request('batch_number') }}">
                        @endif
                        <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                            <i class="fas fa-print"></i>
                            {{ __('Print Unused Vouchers') }}
                        </button>
                    </form>
                </div>
            @endif

            <!-- Vouchers Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Code') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Package') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Batch') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Validity') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Price') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Status') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Customer') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($vouchers as $voucher)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-mono font-bold text-gray-900">
                                            {{ $voucher->code }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $voucher->package?->name ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $voucher->package?->download_speed_mbps ?? 0 }}/{{ $voucher->package?->upload_speed_mbps ?? 0 }}
                                            Mbps
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-xs text-gray-600">{{ $voucher->batch_number ?? '-' }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-xs text-gray-900">
                                            {{ $voucher->valid_from?->format('d M Y') ?? '-' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ __('to') }}
                                            {{ $voucher->valid_until?->format('d M Y H:i') ?? '-' }}
                                        </div>
                                        @if ($voucher->isExpired())
                                            <span class="text-xs text-red-600 font-semibold">{{ __('EXPIRED') }}</span>
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
                                            <div class="text-sm text-gray-900">
                                                {{ $voucher->customer?->name }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ $voucher->used_at?->format('d M Y H:i') }}</div>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        @if ($voucher->status === 'unused' && !$voucher->isExpired())
                                            <form action="{{ route('telecom.vouchers.revoke', $voucher) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-red-600 hover:text-red-900"
                                                    data-confirm="{{ __('Revoke this voucher?') }}"
                                                    data-confirm-type="danger">{{ __('Revoke') }}</button>
                                            </form>

                                            <form action="{{ route('telecom.vouchers.extend', $voucher) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="hours" value="24">
                                                <button type="submit"
                                                    class="text-blue-600 hover:text-blue-900">+24h</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fas fa-ticket-alt text-gray-400 text-5xl mb-3"></i>
                                        <p class="mt-2 text-sm">{{ __('Tidak ada voucher ditemukan') }}</p>
                                        <a href="{{ route('telecom.vouchers.create') }}"
                                            class="text-blue-600 hover:text-blue-900 text-sm mt-2 inline-block">
                                            {{ __('Generate vouchers sekarang') }}
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
    </div>
</x-app-layout>
