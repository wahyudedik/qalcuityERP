<x-app-layout>
    <x-slot name="header">|</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('printing.dashboard') }}"
                    class="text-gray-500 hover:text-gray-700 transition text-sm">
                    ← Back
                </a>
        <form action="{{ route('printing.status', $job) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="status" value="prepress">
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium whitespace-nowrap">
                            Start Pre-Press
                        </button>
        <a href="{{ route('printing.finishing', $job) }}"
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition text-sm font-medium whitespace-nowrap">
                        Manage Finishing
                    </a>
    </div>

    {{-- Job Header Info --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <p class="text-xs text-gray-500 mb-1">Job Number</p>
                <p class="text-xl font-bold text-gray-900">{{ $job->job_number }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Status</p>
                @php
                    $statusColors = [
                        'queued' => 'gray',
                        'prepress' => 'blue',
                        'platemaking' => 'indigo',
                        'on_press' => 'purple',
                        'finishing' => 'orange',
                        'quality_check' => 'yellow',
                        'completed' => 'green',
                    ];
                    $color = $statusColors[$job->status] ?? 'gray';
                @endphp
                <span
                    class="px-3 py-1.5 text-sm rounded-full bg-{{ $color }}-100 text-{{ $color }}-700 $color }}-500/20 $color }}-400 font-medium">
                    {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                </span>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Priority</p>
                @php
                    $priorityColors = ['low' => 'gray', 'normal' => 'blue', 'high' => 'orange', 'urgent' => 'red'];
                    $pColor = $priorityColors[$job->priority] ?? 'blue';
                @endphp
                <span
                    class="px-3 py-1.5 text-sm rounded-full bg-{{ $pColor }}-100 text-{{ $pColor }}-700 $pColor }}-500/20 $pColor }}-400 font-medium">
                    {{ ucfirst($job->priority) }}
                </span>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Due Date</p>
                @if ($job->due_date)
                    @if ($job->due_date->isPast() && !$job->completed_at)
                        <span
                            class="text-red-600 font-semibold">{{ $job->due_date->format('d M Y') }}</span>
                    @else
                        <span
                            class="text-gray-900 font-semibold">{{ $job->due_date->format('d M Y') }}</span>
                    @endif
                @else
                    <span class="text-gray-400">Not set</span>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column - Job Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Basic Information --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Job Information</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Job Name</p>
                        <p class="text-sm font-medium text-gray-900">{{ $job->job_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Customer</p>
                        <p class="text-sm font-medium text-gray-900">
                            {{ $job->customer?->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Product Type</p>
                        <p class="text-sm font-medium text-gray-900">
                            {{ ucfirst(str_replace('_', ' ', $job->product_type)) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Quantity</p>
                        <p class="text-sm font-medium text-gray-900">
                            {{ number_format($job->quantity) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Paper Type</p>
                        <p class="text-sm font-medium text-gray-900">
                            {{ $job->paper_type ?? 'Not specified' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Paper Size</p>
                        <p class="text-sm font-medium text-gray-900">
                            {{ $job->paper_size ?? 'Not specified' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Colors (Front)</p>
                        <p class="text-sm font-medium text-gray-900">{{ $job->colors_front ?? 4 }}
                            Colors</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Colors (Back)</p>
                        <p class="text-sm font-medium text-gray-900">{{ $job->colors_back ?? 0 }}
                            Colors</p>
                    </div>
                </div>

                @if ($job->special_instructions)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-500 mb-1">Special Instructions</p>
                        <p class="text-sm text-gray-700">{{ $job->special_instructions }}</p>
                    </div>
                @endif
            </div>

            {{-- Progress Tracker --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Production Progress</h2>

                @php
                    $stages = [
                        ['key' => 'queued', 'label' => 'Queued'],
                        ['key' => 'prepress', 'label' => 'Pre-Press'],
                        ['key' => 'platemaking', 'label' => 'Plate Making'],
                        ['key' => 'on_press', 'label' => 'On Press'],
                        ['key' => 'finishing', 'label' => 'Finishing'],
                        ['key' => 'quality_check', 'label' => 'Quality Check'],
                        ['key' => 'completed', 'label' => 'Completed'],
                    ];

                    $currentStageIndex = array_search($job->status, array_column($stages, 'key'));
                    if ($currentStageIndex === false) {
                        $currentStageIndex = 0;
                    }
                @endphp

                <div class="relative">
                    <div class="absolute top-1/2 left-0 right-0 h-1 bg-gray-200 -translate-y-1/2">
                    </div>
                    <div class="absolute top-1/2 left-0 h-1 bg-indigo-600 -translate-y-1/2 transition-all duration-500"
                        style="width: {{ ($currentStageIndex / (count($stages) - 1)) * 100 }}%"></div>

                    <div class="relative flex justify-between">
                        @foreach ($stages as $index => $stage)
                            @php
                                $isActive = $index <= $currentStageIndex;
                                $isCurrent = $index === $currentStageIndex;
                            @endphp
                            <div class="flex flex-col items-center">
                                <div
                                    class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold
                                    {{ $isActive ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-400' }}
                                    {{ $isCurrent ? 'ring-4 ring-indigo-200' : '' }}">
                                    {{ $index + 1 }}
                                </div>
                                <p
                                    class="mt-2 text-xs font-medium {{ $isActive ? 'text-indigo-600' : 'text-gray-500' }}">
                                    {{ $stage['label'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Assigned Operator --}}
            @if ($job->assignedOperator)
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Assigned Operator</h2>
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold text-lg">
                            {{ substr($job->assignedOperator->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $job->assignedOperator->name }}</p>
                            <p class="text-sm text-gray-500">{{ $job->assignedOperator->email }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Timeline --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h2>

                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-green-500 mt-2"></div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">Created</p>
                            <p class="text-xs text-gray-500">
                                {{ $job->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>

                    @if ($job->started_at)
                        <div class="flex items-start gap-3">
                            <div class="w-2 h-2 rounded-full bg-blue-500 mt-2"></div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Production Started</p>
                                <p class="text-xs text-gray-500">
                                    {{ $job->started_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($job->completed_at)
                        <div class="flex items-start gap-3">
                            <div class="w-2 h-2 rounded-full bg-green-500 mt-2"></div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Completed</p>
                                <p class="text-xs text-gray-500">
                                    {{ $job->completed_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column - Pricing & Actions --}}
        <div class="space-y-6">
            {{-- Pricing Summary --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Pricing</h2>

                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Estimated Cost</span>
                        <span class="font-medium text-gray-900">Rp
                            {{ number_format($job->estimated_cost ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Actual Cost</span>
                        <span class="font-medium text-gray-900">Rp
                            {{ number_format($job->actual_cost ?? 0, 0, ',', '.') }}</span>
                    </div>
                    @if ($job->quoted_price)
                        <div class="pt-3 border-t border-gray-200">
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-900">Quoted Price</span>
                                <span class="text-lg font-bold text-indigo-600">Rp
                                    {{ number_format($job->quoted_price, 0, ',', '.') }}</span>
                            </div>
                            @if ($job->quoted_price > $job->estimated_cost)
                                <p class="text-xs text-green-600 mt-1">
                                    Profit: Rp
                                    {{ number_format($job->quoted_price - $job->estimated_cost, 0, ',', '.') }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>

                <div class="space-y-2">
                    @if ($job->status === 'prepress')
                        <form action="{{ route('printing.approve-proof', $job) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium">
                                Approve Proof
                            </button>
                        </form>
                    @endif

                    @if ($job->status === 'platemaking')
                        <a href="#"
                            class="block w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-medium text-center">
                            Start Press Run
                        </a>
                    @endif

                    @if ($job->status !== 'completed')
                        <form action="{{ route('printing.assign', $job) }}" method="POST">
                            @csrf
                            <select name="operator_id" onchange="this.form.submit()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 text-sm">
                                <option value="">Assign Operator...</option>
                                @foreach (\App\Models\User::where('tenant_id', auth()->user()->tenant_id)->get() as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif

                    <a href="#"
                        class="block w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-sm font-medium text-center">
                        Print Job Ticket
                    </a>
                </div>
            </div>

            {{-- Notes --}}
            <div
                class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                <h4 class="text-sm font-semibold text-yellow-800 mb-2">Notes</h4>
                <textarea placeholder="Add notes about this job..."
                    class="w-full px-3 py-2 border border-yellow-300 rounded-lg bg-white text-gray-900 text-sm focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                    rows="3"></textarea>
            </div>
        </div>
    </div>
</x-app-layout>
