<x-app-layout title="Spa Packages">
    <x-slot name="header">Spa Packages</x-slot>

    <x-slot name="pageTitle">Paket Spa</x-slot>

    {{-- Packages Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($packages as $package)
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">{{ $package->name }}</h3>
                        @if ($package->description)
                            <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $package->description }}</p>
                        @endif
                    </div>
                    @if (!($package->is_active ?? true))
                        <span
                            class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600">Nonaktif</span>
                    @endif
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                    <div class="text-xs text-gray-500">
                        <span class="font-medium text-gray-900">{{ $package->items_count }}</span> treatment
                    </div>
                    <div class="text-sm font-semibold text-gray-900">
                        Rp {{ number_format($package->price ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                @if ($package->duration)
                    <div class="text-xs text-gray-400 mt-1">{{ $package->duration }} menit</div>
                @endif
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-gray-200 p-8 text-center text-gray-500">
                <p>Belum ada paket spa</p>
            </div>
        @endforelse
    </div>
</x-app-layout>
