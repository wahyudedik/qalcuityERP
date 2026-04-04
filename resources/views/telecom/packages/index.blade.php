@extends('layouts.app')

@section('title', 'Internet Packages')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Internet Packages</h1>
                <p class="text-gray-600 mt-1">Kelola paket internet dan pricing</p>
            </div>
            <a href="{{ route('telecom.packages.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Package
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Packages</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Active</p>
                        <p class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Inactive</p>
                        <p class="text-2xl font-bold text-red-600">{{ $stats['inactive'] }}</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Unlimited</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $stats['unlimited'] }}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('telecom.packages.index') }}"
                class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari package..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <select name="status"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div>
                    <select name="quota_type"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Tipe</option>
                        <option value="unlimited" {{ request('quota_type') == 'unlimited' ? 'selected' : '' }}>Unlimited
                        </option>
                        <option value="limited" {{ request('quota_type') == 'limited' ? 'selected' : '' }}>Limited Quota
                        </option>
                    </select>
                </div>

                <div class="md:col-span-4 flex gap-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                        Filter
                    </button>
                    <a href="{{ route('telecom.packages.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Success/Error Messages -->
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

        <!-- Packages Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($packages as $package)
                <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow">
                    <!-- Package Header -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 text-white">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-xl font-bold">{{ $package->name }}</h3>
                                <p class="text-blue-100 text-sm mt-1">{{ ucfirst($package->billing_cycle) }}</p>
                            </div>
                            @if ($package->is_active)
                                <span class="px-2 py-1 text-xs bg-green-500 rounded-full">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-500 rounded-full">Inactive</span>
                            @endif
                        </div>
                    </div>

                    <!-- Package Details -->
                    <div class="p-6">
                        <!-- Speed -->
                        <div class="mb-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-600 text-sm">Download</span>
                                <span class="font-bold text-gray-900">{{ $package->download_speed_mbps }} Mbps</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full"
                                    style="width: {{ min(100, ($package->download_speed_mbps / 100) * 100) }}%"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-600 text-sm">Upload</span>
                                <span class="font-bold text-gray-900">{{ $package->upload_speed_mbps }} Mbps</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full"
                                    style="width: {{ min(100, ($package->upload_speed_mbps / 100) * 100) }}%"></div>
                            </div>
                        </div>

                        <!-- Quota -->
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 text-sm">Quota</span>
                                <span class="font-semibold text-gray-900">
                                    @if ($package->isUnlimited())
                                        <span class="text-blue-600">∞ Unlimited</span>
                                    @else
                                        {{ number_format($package->quota_bytes / 1073741824, 0) }}
                                        GB/{{ $package->quota_period }}
                                    @endif
                                </span>
                            </div>
                        </div>

                        <!-- Pricing -->
                        <div class="mb-4">
                            <div class="flex items-baseline gap-1">
                                <span class="text-3xl font-bold text-gray-900">Rp
                                    {{ number_format($package->price, 0, ',', '.') }}</span>
                                <span class="text-gray-600 text-sm">/bulan</span>
                            </div>
                            @if ($package->setup_fee > 0)
                                <p class="text-xs text-gray-500 mt-1">Setup fee: Rp
                                    {{ number_format($package->setup_fee, 0, ',', '.') }}</p>
                            @endif
                        </div>

                        <!-- Subscriptions Count -->
                        <div class="mb-4 text-sm text-gray-600">
                            <span class="font-semibold">{{ $package->subscriptions_count }}</span> active subscriptions
                        </div>

                        <!-- Description -->
                        @if ($package->description)
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $package->description }}</p>
                        @endif

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <a href="{{ route('telecom.packages.edit', $package) }}"
                                class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-center text-sm">
                                Edit
                            </a>
                            <form action="{{ route('telecom.packages.toggle-status', $package) }}" method="POST"
                                class="flex-1">
                                @csrf
                                <button type="submit"
                                    class="w-full {{ $package->is_active ? 'bg-gray-500 hover:bg-gray-600' : 'bg-green-500 hover:bg-green-600' }} text-white px-4 py-2 rounded-lg text-sm">
                                    {{ $package->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            <form action="{{ route('telecom.packages.destroy', $package) }}" method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus package ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <div class="text-gray-400">
                        <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <p class="mt-2 text-sm">Belum ada package yang dibuat</p>
                        <a href="{{ route('telecom.packages.create') }}"
                            class="mt-2 inline-block text-blue-600 hover:text-blue-800">
                            Buat package pertama →
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($packages->hasPages())
            <div class="mt-6">
                {{ $packages->links() }}
            </div>
        @endif
    </div>
@endsection
