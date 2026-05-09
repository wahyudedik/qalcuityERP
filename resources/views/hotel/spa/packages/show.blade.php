<x-app-layout title="Detail Paket Spa">
    <x-slot name="header">{{ $package->name }}</x-slot>

    <x-slot name="pageTitle">{{ $package->name }}</x-slot>

    <div class="max-w-2xl">
        <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-4">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $package->name }}</h2>
                    @if ($package->description)
                        <p class="text-sm text-gray-500 mt-1">{{ $package->description }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold text-gray-900">Rp {{ number_format($package->price ?? 0, 0, ',', '.') }}
                    </p>
                    @if ($package->duration)
                        <p class="text-xs text-gray-500">{{ $package->duration }} menit</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Treatments in Package --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Treatment dalam Paket</h3>
            </div>
            @if ($availableTreatments->isEmpty())
                <div class="p-6 text-center text-gray-500 text-sm">Belum ada treatment</div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach ($availableTreatments as $treatment)
                        <div class="px-6 py-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-900">{{ $treatment->name }}</p>
                                <p class="text-xs text-gray-500">{{ $treatment->duration ?? '-' }} menit</p>
                            </div>
                            <span class="text-xs text-gray-400">{{ ucfirst($treatment->category ?? '') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="mt-4">
            <a href="{{ route('hotel.spa.packages.index') }}" class="text-sm text-blue-600 hover:text-blue-700">&larr;
                Kembali ke Daftar Paket</a>
        </div>
    </div>
</x-app-layout>
