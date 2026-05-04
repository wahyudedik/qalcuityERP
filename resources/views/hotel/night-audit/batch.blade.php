<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Audit Batch') }} - {{ $batch->batch_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            {{-- Batch Info --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Batch Number</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $batch->batch_number }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Audit Date</p>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ $batch->audit_date->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Status</p>
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $batch->status === 'completed'
                                ? 'bg-green-100 text-green-800'
                                : ($batch->status === 'in_progress'
                                    ? 'bg-yellow-100 text-yellow-800'
                                    : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst(str_replace('_', ' ', $batch->status)) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Started At</p>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ $batch->started_at?->format('H:i') ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Batch Processing Steps --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Processing Steps</h3>
                </div>

                <div class="p-6 space-y-4">
                    {{-- Step 1: Post Room Charges --}}
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                                <svg class="w-5 h-5 text-blue-600" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Post Room Charges</h4>
                                <p class="text-sm text-gray-500">Automatically post room charges for
                                    all occupied rooms</p>
                            </div>
                        </div>
                        @if ($batch->status !== 'completed')
                            <form action="{{ route('hotel.night-audit.post-room-charges', $batch->id) }}"
                                method="POST">
                                @csrf
                                <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                    Execute
                                </button>
                            </form>
                        @else
                            <span class="text-green-600 text-sm font-medium">✓ Completed</span>
                        @endif
                    </div>

                    {{-- Step 2: Post F&B Revenue --}}
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div
                                class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center mr-4">
                                <svg class="w-5 h-5 text-purple-600" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Post F&B Revenue</h4>
                                <p class="text-sm text-gray-500">Post restaurant and room service
                                    revenue</p>
                            </div>
                        </div>
                        @if ($batch->status !== 'completed')
                            <form action="{{ route('hotel.night-audit.post-fb-revenue', $batch->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 text-sm">
                                    Execute
                                </button>
                            </form>
                        @else
                            <span class="text-green-600 text-sm font-medium">✓ Completed</span>
                        @endif
                    </div>

                    {{-- Step 3: Post Minibar Charges --}}
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div
                                class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center mr-4">
                                <svg class="w-5 h-5 text-orange-600" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Post Minibar Charges</h4>
                                <p class="text-sm text-gray-500">Post minibar consumption charges
                                </p>
                            </div>
                        </div>
                        @if ($batch->status !== 'completed')
                            <form action="{{ route('hotel.night-audit.post-minibar', $batch->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 text-sm">
                                    Execute
                                </button>
                            </form>
                        @else
                            <span class="text-green-600 text-sm font-medium">✓ Completed</span>
                        @endif
                    </div>

                    {{-- Step 4: Calculate Occupancy --}}
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div
                                class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mr-4">
                                <svg class="w-5 h-5 text-green-600" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Calculate Occupancy Statistics
                                </h4>
                                <p class="text-sm text-gray-500">Calculate occupancy rate and
                                    statistics</p>
                            </div>
                        </div>
                        @if ($batch->status !== 'completed')
                            <form action="{{ route('hotel.night-audit.calculate-occupancy', $batch->id) }}"
                                method="POST">
                                @csrf
                                <button type="submit"
                                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
                                    Execute
                                </button>
                            </form>
                        @else
                            <span class="text-green-600 text-sm font-medium">✓ Completed</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Batch Summary --}}
            @if ($batch->total_revenue > 0 || $batch->occupied_rooms > 0)
                <div
                    class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Batch Summary</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-xs text-gray-500">Total Rooms</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $batch->total_rooms }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Occupied Rooms</p>
                            <p class="text-2xl font-bold text-blue-600">{{ $batch->occupied_rooms }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Occupancy Rate</p>
                            <p class="text-2xl font-bold text-green-600">
                                {{ number_format($batch->occupancy_rate, 1) }}%</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">ADR</p>
                            <p class="text-2xl font-bold text-purple-600">Rp
                                {{ number_format($batch->adr, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <p class="text-xs text-gray-500">Room Revenue</p>
                                <p class="text-lg font-semibold text-gray-900">Rp
                                    {{ number_format($batch->total_room_revenue, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">F&B Revenue</p>
                                <p class="text-lg font-semibold text-gray-900">Rp
                                    {{ number_format($batch->total_fb_revenue, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Other Revenue</p>
                                <p class="text-lg font-semibold text-gray-900">Rp
                                    {{ number_format($batch->total_other_revenue, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Total Revenue</p>
                                <p class="text-lg font-bold text-indigo-600">Rp
                                    {{ number_format($batch->total_revenue, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Complete Audit Button --}}
            @if ($batch->status === 'in_progress')
                <div class="flex justify-end">
                    <form action="{{ route('hotel.night-audit.complete', $batch->id) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to complete this audit batch?')">
                        @csrf
                        <button type="submit"
                            class="px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 font-semibold">
                            Complete Audit Batch
                        </button>
                    </form>
                </div>
            @endif

            {{-- Audit Logs --}}
            @if ($batch->auditLogs->count() > 0)
                <div
                    class="bg-white rounded-2xl border border-gray-200 overflow-hidden mt-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Audit Log</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Time</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Operation</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Description</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        By</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($batch->auditLogs as $log)
                                    <tr>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $log->performed_at->format('H:i:s') }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ str_replace('_', ' ', $log->operation) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{ $log->description }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $log->status === 'success'
                                            ? 'bg-green-100 text-green-800'
                                            : ($log->status === 'failed'
                                                ? 'bg-red-100 text-red-800'
                                                : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ ucfirst($log->status) }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $log->performedBy?->name ?? 'System' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
