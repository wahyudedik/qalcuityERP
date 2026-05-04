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
        {{-- Left Column - Press Run Info --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Current Run --}}
            @if ($currentRun)
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Press Run Aktif</h2>
                        @php
                            $runStatusColors = [
                                'setup' => 'yellow',
                                'running' => 'green',
                                'paused' => 'orange',
                                'stopped' => 'red',
                                'completed' => 'blue',
                            ];
                            $runColor = $runStatusColors[$currentRun->current_status] ?? 'gray';
                        @endphp
                        <span
                            class="px-3 py-1 text-xs rounded-full bg-{{ $runColor  }}-100 text-{{ $runColor }}-700 $runColor }}-500/20 $runColor }}-400 font-medium">
                            {{ ucfirst($currentRun->current_status) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Mesin</p>
                            <p class="text-sm font-medium text-gray-900">
                                {{ $currentRun->press_machine ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Operator</p>
                            <p class="text-sm font-medium text-gray-900">
                                {{ $currentRun->operator?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Mulai</p>
                            <p class="text-sm font-medium text-gray-900">
                                {{ $currentRun->run_start?->format('d M Y H:i') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Kecepatan</p>
                            <p class="text-sm font-medium text-gray-900">
                                {{ number_format($currentRun->production_speed ?? 0) }} lbr/jam</p>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="mb-4">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-500">Produksi</span>
                            <span class="font-medium text-gray-900">
                                {{ number_format($currentRun->produced_quantity ?? 0) }} /
                                {{ number_format($currentRun->target_quantity ?? 0) }}
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-indigo-600 h-3 rounded-full transition-all duration-500"
                                style="width: {{ min($currentRun->target_quantity > 0 ? ($currentRun->produced_quantity / $currentRun->target_quantity) * 100 : 0, 100) }}%">
                            </div>
                        </div>
                    </div>

                    {{-- Waste Info --}}
                    @if ($currentRun->waste_quantity > 0)
                        <div class="flex items-center gap-2 text-sm text-orange-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                            <span>Waste: {{ number_format($currentRun->waste_quantity) }} lembar
                                ({{ number_format($currentRun->waste_percentage ?? 0, 1) }}%)</span>
                        </div>
                    @endif

                    {{-- Update Production Form --}}
                    <form action="{{ route('printing.update-production', $currentRun->id) }}" method="POST"
                        class="mt-4 pt-4 border-t border-gray-200">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah
                                    Produksi</label>
                                <input type="number" name="produced_quantity" min="0"
                                    value="{{ $currentRun->produced_quantity ?? 0 }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 text-sm focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1">Waste</label>
                                <input type="number" name="waste_quantity" min="0"
                                    value="{{ $currentRun->waste_quantity ?? 0 }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 text-sm focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="flex items-end">
                                <button type="submit"
                                    class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
                                    Update Produksi
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @else
                {{-- Start New Press Run --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Mulai Press Run Baru</h2>

                    <form action="{{ route('printing.start-press', $job) }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mesin
                                    Cetak *</label>
                                <input type="text" name="machine" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 text-sm focus:ring-2 focus:ring-indigo-500"
                                    placeholder="e.g., Heidelberg SM 52">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Operator
                                    *</label>
                                <select name="operator_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 text-sm focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Pilih Operator</option>
                                    @foreach (\App\Models\User::where('tenant_id', auth()->user()->tenant_id)->get() as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit"
                                class="px-6 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium">
                                Mulai Press Run
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Press Run History --}}
            @if ($job->pressRuns->count() > 0)
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Press Run</h2>

                    <div class="space-y-3">
                        @foreach ($job->pressRuns->sortByDesc('created_at') as $run)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                                <div class="flex items-center gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $run->press_machine ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $run->run_start?->format('d M Y H:i') ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ number_format($run->produced_quantity ?? 0) }} /
                                        {{ number_format($run->target_quantity ?? 0) }}</p>
                                    @php
                                        $rColor = $runStatusColors[$run->current_status] ?? 'gray';
                                    @endphp
                                    <span
                                        class="text-xs text-{{ $rColor }}-600 $rColor }}-400">{{ ucfirst($run->current_status) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
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
                        <p class="text-xs text-gray-500">Target Quantity</p>
                        <p class="text-sm font-medium text-gray-900">
                            {{ number_format($job->quantity) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Kertas</p>
                        <p class="text-sm font-medium text-gray-900">{{ $job->paper_type ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Warna</p>
                        <p class="text-sm font-medium text-gray-900">
                            {{ $job->colors_front ?? 4 }}/{{ $job->colors_back ?? 0 }}</p>
                    </div>
                </div>
            </div>

            {{-- Ink Levels (if current run) --}}
            @if (
                $currentRun &&
                    ($currentRun->ink_levels_c ||
                        $currentRun->ink_levels_m ||
                        $currentRun->ink_levels_y ||
                        $currentRun->ink_levels_k))
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Level Tinta</h2>
                    <div class="space-y-3">
                        @foreach (['c' => ['Cyan', 'cyan'], 'm' => ['Magenta', 'pink'], 'y' => ['Yellow', 'yellow'], 'k' => ['Black', 'gray']] as $key => [$label, $color])
                            @php $level = $currentRun->{"ink_levels_{$key}"} ?? 0; @endphp
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-500">{{ $label }}</span>
                                    <span
                                        class="text-gray-900 font-medium">{{ $level }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-{{ $color  }}-500 h-2 rounded-full"
                                        style="width: {{ $level }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
