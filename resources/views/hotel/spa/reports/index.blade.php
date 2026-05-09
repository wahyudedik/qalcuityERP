<x-app-layout title="Laporan Spa">
    <x-slot name="header">Laporan Spa</x-slot>

    <x-slot name="pageTitle">Laporan Spa & Wellness</x-slot>

    {{-- Date Filter --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
            </div>
            <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
        </form>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500">Total Booking</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_bookings']) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500">Selesai</p>
            <p class="text-xl font-bold text-green-600 mt-1">{{ number_format($stats['completed_bookings']) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500">Total Pendapatan</p>
            <p class="text-xl font-bold text-gray-900 mt-1">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500">Rata-rata/Booking</p>
            <p class="text-xl font-bold text-gray-900 mt-1">Rp
                {{ number_format($stats['avg_booking_value'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Revenue by Treatment --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Pendapatan per Treatment</h3>
            </div>
            @if ($revenueByTreatment->isEmpty())
                <div class="p-6 text-center text-gray-500 text-sm">Tidak ada data</div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach ($revenueByTreatment as $item)
                        <div class="px-6 py-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-900">{{ $item->name }}</p>
                                <p class="text-xs text-gray-500">{{ $item->booking_count }} booking</p>
                            </div>
                            <span class="text-sm font-medium text-gray-900">Rp
                                {{ number_format($item->total_revenue, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Therapist Performance --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Performa Terapis</h3>
            </div>
            @if ($therapistPerformance->isEmpty())
                <div class="p-6 text-center text-gray-500 text-sm">Tidak ada data</div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach ($therapistPerformance as $therapist)
                        <div class="px-6 py-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-900">{{ $therapist->name }}</p>
                                <p class="text-xs text-gray-500">{{ $therapist->completed_count }} booking selesai</p>
                            </div>
                            <span class="text-sm font-medium text-gray-900">Rp
                                {{ number_format($therapist->total_revenue ?? 0, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
