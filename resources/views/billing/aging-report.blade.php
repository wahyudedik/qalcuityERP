<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-clock text-blue-600"></i> Accounts Receivable Aging
            </h1>
            <p class="text-gray-500">Outstanding invoices by age category</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div class="w-full md:w-1/6">
                            <h4 class="text-blue-600">Rp {{ number_format($stats['total_outstanding'] ?? 0, 0, ',', '.') }}
                            </h4>
                            <small class="text-gray-500">Total Outstanding</small>
                        </div>
                        <div class="w-full md:w-1/6">
                            <h4 class="text-emerald-600">Rp {{ number_format($stats['current'] ?? 0, 0, ',', '.') }}</h4>
                            <small class="text-gray-500">Current</small>
                        </div>
                        <div class="w-full md:w-1/6">
                            <h4 class="text-sky-600">Rp {{ number_format($stats['days_30'] ?? 0, 0, ',', '.') }}</h4>
                            <small class="text-gray-500">1-30 Days</small>
                        </div>
                        <div class="w-full md:w-1/6">
                            <h4 class="text-amber-600">Rp {{ number_format($stats['days_60'] ?? 0, 0, ',', '.') }}</h4>
                            <small class="text-gray-500">31-60 Days</small>
                        </div>
                        <div class="w-full md:w-1/6">
                            <h4 class="text-red-600">Rp {{ number_format($stats['days_90'] ?? 0, 0, ',', '.') }}</h4>
                            <small class="text-gray-500">61-90 Days</small>
                        </div>
                        <div class="w-full md:w-1/6">
                            <h4 class="text-dark">Rp {{ number_format($stats['over_90'] ?? 0, 0, ',', '.') }}</h4>
                            <small class="text-gray-500">90+ Days</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Patient</th>
                                    <th>Invoice Date</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Days Overdue</th>
                                    <th>Category</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $invoice)
                                    <tr>
                                        <td><code>{{ $invoice->invoice_number }}</code></td>
                                        <td>{{ $invoice->patient?->name ?? '-' }}</td>
                                        <td>{{ $invoice->invoice_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td>{{ $invoice->due_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td><strong>Rp
                                                {{ number_format($invoice->outstanding_amount ?? 0, 0, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            @php
                                                $daysOverdue = $invoice->due_date?->diffInDays(now()) ?? 0;
                                            @endphp
                                            @if ($daysOverdue > 0)
                                                <span class="text-red-600 font-bold">{{ $daysOverdue }} days</span>
                                            @else
                                                <span class="text-emerald-600">Not due</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($daysOverdue <= 0)
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Current</span>
                                            @elseif($daysOverdue <= 30)
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-700">1-30 Days</span>
                                            @elseif($daysOverdue <= 60)
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">31-60 Days</span>
                                            @elseif($daysOverdue <= 90)
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">61-90 Days</span>
                                            @else
                                                <span class="badge bg-gray-900">90+ Days</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex gap-1">
                                                <button class="px-3 py-1.5 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-xs transition">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="px-3 py-1.5 border border-emerald-500 text-emerald-600 hover:bg-emerald-50 rounded-lg text-xs transition">
                                                    <i class="fas fa-bell"></i> Remind
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-6 text-gray-400">No outstanding invoices</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $invoices->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
