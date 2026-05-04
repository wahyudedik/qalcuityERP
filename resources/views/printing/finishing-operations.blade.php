<x-app-layout>
    <x-slot name="header">|</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('printing.show', $job) }}"
                    class="text-gray-500 hover:text-gray-700 transition text-sm">
                    ← Kembali ke Job
                </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column - Finishing Operations --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Operations List --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Operasi Finishing</h2>

                @if ($job->finishingOperations->count() === 0)
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="text-gray-500 text-sm">Belum ada operasi finishing untuk job ini.
                        </p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($job->finishingOperations->sortBy('sequence_order') as $operation)
                            <div class="border border-gray-200 rounded-xl p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-sm font-semibold">
                                            {{ $operation->sequence_order }}
                                        </span>
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-900">
                                                {{ ucfirst(str_replace('_', ' ', $operation->operation_type)) }}
                                            </h3>
                                            <p class="text-xs text-gray-500">
                                                Operator: {{ $operation->operator?->name ?? 'Belum ditugaskan' }}
                                            </p>
                                        </div>
                                    </div>
                                    @php
                                        $opStatusColors = [
                                            'pending' => 'gray',
                                            'in_progress' => 'blue',
                                            'completed' => 'green',
                                            'failed' => 'red',
                                        ];
                                        $opColor = $opStatusColors[$operation->status] ?? 'gray';
                                    @endphp
                                    <span
                                        class="px-2 py-1 text-xs rounded-full bg-{{ $opColor  }}-100 text-{{ $opColor }}-700 $opColor }}-500/20 $opColor }}-400">
                                        {{ ucfirst(str_replace('_', ' ', $operation->status)) }}
                                    </span>
                                </div>

                                {{-- Progress --}}
                                <div class="mb-2">
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-500">Progress</span>
                                        <span class="text-gray-900">
                                            {{ number_format($operation->completed_quantity ?? 0) }} /
                                            {{ number_format($operation->target_quantity ?? 0) }}
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-indigo-600 h-2 rounded-full transition-all"
                                            style="width: {{ $operation->completion_percentage ?? 0 }}%"></div>
                                    </div>
                                </div>

                                {{-- Waste --}}
                                @if ($operation->waste_quantity > 0)
                                    <p class="text-xs text-orange-600">
                                        Waste: {{ number_format($operation->waste_quantity) }} lembar
                                    </p>
                                @endif

                                {{-- Timing --}}
                                <div class="flex gap-4 mt-2 text-xs text-gray-500">
                                    @if ($operation->started_at)
                                        <span>Mulai: {{ $operation->started_at->format('d M H:i') }}</span>
                                    @endif
                                    @if ($operation->completed_at)
                                        <span>Selesai: {{ $operation->completed_at->format('d M H:i') }}</span>
                                    @endif
                                </div>

                                {{-- Quality Notes --}}
                                @if ($operation->quality_notes)
                                    <div
                                        class="mt-2 p-2 bg-yellow-50 rounded text-xs text-yellow-800">
                                        {{ $operation->quality_notes }}
                                    </div>
                                @endif

                                {{-- Issues --}}
                                @if ($operation->issues)
                                    <div
                                        class="mt-2 p-2 bg-red-50 rounded text-xs text-red-800">
                                        {{ $operation->issues }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Right Column - Job Summary --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Info Job</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500">Job Number</p>
                        <p class="text-sm font-medium text-gray-900">{{ $job->job_number }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Nama Job</p>
                        <p class="text-sm font-medium text-gray-900">{{ $job->job_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Status</p>
                        @php
                            $statusColors = [
                                'queued' => 'gray',
                                'prepress' => 'blue',
                                'platemaking' => 'indigo',
                                'on_press' => 'purple',
                                'finishing' => 'orange',
                                'quality_check' => 'yellow',
                                'completed' => 'green',
                                'cancelled' => 'red',
                            ];
                            $color = $statusColors[$job->status] ?? 'gray';
                        @endphp
                        <span
                            class="px-2 py-1 text-xs rounded-full bg-{{ $color  }}-100 text-{{ $color }}-700 $color }}-500/20 $color }}-400">
                            {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Quantity</p>
                        <p class="text-sm font-medium text-gray-900">
                            {{ number_format($job->quantity) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Finishing Type</p>
                        <p class="text-sm font-medium text-gray-900">{{ $job->finishing_type ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Complete Finishing --}}
            @if ($job->status === 'finishing')
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Aksi</h2>
                    <form action="{{ route('printing.status', $job) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="quality_check">
                        <button type="submit"
                            class="w-full px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition text-sm font-medium">
                            Kirim ke Quality Check
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
