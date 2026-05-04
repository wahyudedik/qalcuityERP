<x-app-layout>
    <x-slot name="header">{{ $deferredItem->typeLabel() }}: {{ $deferredItem->number }}</x-slot>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Info Card --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $deferredItem->description }}</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ $deferredItem->start_date->format('d M Y') }} – {{ $deferredItem->end_date->format('d M Y') }} · {{ $deferredItem->total_periods }} bulan</p>
                </div>
                @if($deferredItem->isActive())
                <form method="POST" action="{{ route('deferred.cancel', $deferredItem) }}" onsubmit="return confirm('Batalkan item ini?')">
                    @csrf @method('PATCH')
                    <button type="submit" class="px-3 py-1.5 text-xs border border-red-200 text-red-600 rounded-lg hover:bg-red-50">Batalkan</button>
                </form>
                @endif
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
                <div class="bg-gray-50 rounded-xl p-3">
                    <p class="text-xs text-gray-500">Total</p>
                    <p class="font-semibold text-gray-900 mt-0.5">Rp {{ number_format($deferredItem->total_amount,0,',','.') }}</p>
                </div>
                <div class="bg-green-50 rounded-xl p-3">
                    <p class="text-xs text-gray-500">Diakui</p>
                    <p class="font-semibold text-green-700 mt-0.5">Rp {{ number_format($deferredItem->recognized_amount,0,',','.') }}</p>
                </div>
                <div class="bg-blue-50 rounded-xl p-3">
                    <p class="text-xs text-gray-500">Sisa</p>
                    <p class="font-semibold text-blue-700 mt-0.5">Rp {{ number_format($deferredItem->remaining_amount,0,',','.') }}</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3">
                    <p class="text-xs text-gray-500">Progress</p>
                    <p class="font-semibold text-gray-900 mt-0.5">{{ $deferredItem->recognized_periods }}/{{ $deferredItem->total_periods }}</p>
                </div>
            </div>

            {{-- Progress bar --}}
            <div class="mb-4">
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>Progress Amortisasi</span>
                    <span>{{ $deferredItem->progressPercent() }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3">
                    <div class="bg-blue-500 h-3 rounded-full transition-all" style="width:{{ $deferredItem->progressPercent() }}%"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="text-gray-500">Akun Deferred:</span>
                    <span class="ml-1 text-gray-900">{{ $deferredItem->deferredAccount?->code }} - {{ $deferredItem->deferredAccount?->name }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Akun Pengakuan:</span>
                    <span class="ml-1 text-gray-900">{{ $deferredItem->recognitionAccount?->code }} - {{ $deferredItem->recognitionAccount?->name }}</span>
                </div>
                @if($deferredItem->reference_number)
                <div>
                    <span class="text-gray-500">Referensi:</span>
                    <span class="ml-1 text-gray-900">{{ $deferredItem->reference_number }}</span>
                </div>
                @endif
                <div>
                    <span class="text-gray-500">Dibuat oleh:</span>
                    <span class="ml-1 text-gray-900">{{ $deferredItem->user?->name }}</span>
                </div>
            </div>
        </div>

        {{-- Status Card --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Status</h3>
            @php
                $statusColor = match($deferredItem->status) {
                    'active'    => 'bg-blue-100 text-blue-700',
                    'completed' => 'bg-green-100 text-green-700',
                    default     => 'bg-gray-100 text-gray-500',
                };
            @endphp
            <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }}">{{ ucfirst($deferredItem->status) }}</span>

            @php $nextPending = $deferredItem->schedules->where('status', 'pending')->first(); @endphp
            @if($nextPending)
            <div class="mt-4 p-3 bg-amber-50 rounded-xl border border-amber-200">
                <p class="text-xs font-medium text-amber-700">Jadwal Berikutnya</p>
                <p class="text-sm font-semibold text-amber-800 mt-1">{{ $nextPending->recognition_date->format('d M Y') }}</p>
                <p class="text-xs text-amber-600">Rp {{ number_format($nextPending->amount,0,',','.') }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Amortization Schedule --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Jadwal Amortisasi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-center">Periode</th>
                        <th class="px-4 py-3 text-left">Tanggal Pengakuan</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Jurnal</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($deferredItem->schedules->sortBy('period_number') as $schedule)
                    @php
                        $sc = match($schedule->status) {
                            'posted'  => 'bg-green-100 text-green-700',
                            'skipped' => 'bg-gray-100 text-gray-500',
                            default   => 'bg-amber-100 text-amber-700',
                        };
                        $isDue = $schedule->isPending() && $schedule->recognition_date->lte(today());
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $isDue ? 'bg-amber-50/50' : '' }}">
                        <td class="px-4 py-3 text-center font-medium text-gray-900">{{ $schedule->period_number }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $schedule->recognition_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900">Rp {{ number_format($schedule->amount,0,',','.') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sc }}">{{ ucfirst($schedule->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($schedule->journalEntry)
                            <a href="{{ route('journals.show', $schedule->journalEntry) }}" class="text-xs text-blue-600 hover:underline">{{ $schedule->journalEntry?->number }}</a>
                            @else
                            <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($schedule->isPending() && $deferredItem->isActive())
                            <form method="POST" action="{{ route('deferred.schedule.post', $schedule) }}">
                                @csrf
                                <button type="submit" class="px-2 py-1 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700">Post Jurnal</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
