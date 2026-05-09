<x-app-layout title="Spa Therapists">
    <x-slot name="header">Terapis Spa</x-slot>

    <x-slot name="pageTitle">Daftar Terapis</x-slot>

    {{-- Therapists Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($therapists as $therapist)
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center shrink-0">
                        <span class="text-lg font-semibold text-purple-600">
                            {{ strtoupper(substr($therapist->name, 0, 1)) }}
                        </span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $therapist->name }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $therapist->specialization ?? '-' }}</p>

                        <div class="flex items-center gap-2 mt-2">
                            @php
                                $statusColors = [
                                    'available' => 'bg-green-100 text-green-700',
                                    'busy' => 'bg-yellow-100 text-yellow-700',
                                    'off_duty' => 'bg-gray-100 text-gray-700',
                                    'on_leave' => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <span
                                class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full {{ $statusColors[$therapist->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst(str_replace('_', ' ', $therapist->status ?? 'unknown')) }}
                            </span>
                            @if (!$therapist->is_active)
                                <span
                                    class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700">
                                    Nonaktif
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                            <div class="text-xs text-gray-500">
                                <span class="font-medium text-gray-900">{{ $therapist->today_bookings_count }}</span>
                                booking hari ini
                            </div>
                            @if ($therapist->phone)
                                <span class="text-xs text-gray-400">{{ $therapist->phone }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-gray-200 p-8 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p>Belum ada data terapis</p>
            </div>
        @endforelse
    </div>
</x-app-layout>
