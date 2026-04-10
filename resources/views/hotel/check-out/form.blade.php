<x-app-layout>
    <x-slot name="header">Check-out — Room {{ $reservation->room?->number ?? 'N/A' }}</x-slot>

    @php
        $guest = $reservation->guest;
        $room = $reservation->room;
        $checkIn = $reservation->checkInOuts->where('type', 'check_in')->first();
    @endphp

    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Guest Info Card --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-4">Guest
                Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Guest Name</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $guest?->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Room</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $room?->number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Checked In</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                        {{ $checkIn?->processed_at?->format('d M Y H:i') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Stay Duration</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $reservation->nights }} night(s)</p>
                </div>
            </div>
        </div>

        {{-- Charges Summary Card --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-4">Charges
                Summary</h3>

            <div class="space-y-3">
                {{-- Room Charges --}}
                <div class="flex justify-between items-center py-2">
                    <div>
                        <p class="text-sm text-gray-900 dark:text-white">Room Charges</p>
                        <p class="text-xs text-gray-500 dark:text-slate-400">{{ $charges['nights'] }} night(s) × Rp
                            {{ number_format($charges['rate_per_night'], 0, ',', '.') }}</p>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">Rp
                        {{ number_format($charges['room_charge'], 0, ',', '.') }}</p>
                </div>

                {{-- Mini-bar Charges --}}
                @if ($charges['minibar_charges'] > 0)
                    <div class="flex justify-between items-center py-2">
                        <div>
                            <p class="text-sm text-gray-900 dark:text-white">Mini-bar Charges</p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">{{ count($charges['minibar_items']) }}
                                item(s)</p>
                        </div>
                        <p class="font-medium text-gray-900 dark:text-white">Rp
                            {{ number_format($charges['minibar_charges'], 0, ',', '.') }}</p>
                    </div>

                    {{-- Mini-bar Items Detail --}}
                    <div class="ml-4 space-y-1">
                        @foreach ($charges['minibar_items'] as $item)
                            <div class="flex justify-between text-xs text-gray-600 dark:text-slate-400">
                                <span>• {{ $item['item'] }} × {{ $item['quantity'] }}</span>
                                <span>Rp {{ number_format($item['total'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Additional Charges --}}
                @if ($charges['additional_charges'] > 0)
                    <div class="flex justify-between items-center py-2">
                        <div>
                            <p class="text-sm text-gray-900 dark:text-white">Additional Charges</p>
                        </div>
                        <p class="font-medium text-gray-900 dark:text-white">Rp
                            {{ number_format($charges['additional_charges'], 0, ',', '.') }}</p>
                    </div>
                @endif

                {{-- Discount --}}
                @if ($charges['discount'] > 0)
                    <div class="flex justify-between items-center py-2 text-green-600 dark:text-green-400">
                        <div>
                            <p class="text-sm">Discount</p>
                        </div>
                        <p class="font-medium">- Rp {{ number_format($charges['discount'], 0, ',', '.') }}</p>
                    </div>
                @endif

                {{-- Subtotal --}}
                <div class="flex justify-between items-center py-2 border-t border-gray-100 dark:border-white/10">
                    <p class="text-sm text-gray-600 dark:text-slate-400">Subtotal</p>
                    <p class="font-medium text-gray-900 dark:text-white">Rp
                        {{ number_format($charges['subtotal'], 0, ',', '.') }}</p>
                </div>

                {{-- Tax --}}
                <div class="flex justify-between items-center py-2">
                    <div>
                        <p class="text-sm text-gray-900 dark:text-white">Tax</p>
                        <p class="text-xs text-gray-500 dark:text-slate-400">{{ $charges['tax_rate'] }}%</p>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">Rp
                        {{ number_format($charges['tax_amount'], 0, ',', '.') }}</p>
                </div>

                {{-- Deposit Paid --}}
                @if ($charges['deposit_paid'] > 0)
                    <div class="flex justify-between items-center py-2 border-t border-gray-100 dark:border-white/10">
                        <div>
                            <p class="text-sm text-gray-900 dark:text-white">Deposit Paid</p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">Collected at check-in</p>
                        </div>
                        <p class="font-medium text-green-600 dark:text-green-400">Rp
                            {{ number_format($charges['deposit_paid'], 0, ',', '.') }}</p>
                    </div>
                @endif

                {{-- Grand Total --}}
                <div
                    class="flex justify-between items-center py-3 border-t-2 border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 -mx-6 px-6 rounded-b-xl">
                    <p class="text-base font-semibold text-gray-900 dark:text-white">Grand Total</p>
                    <p class="text-xl font-bold text-blue-600 dark:text-blue-400">Rp
                        {{ number_format($charges['grand_total'], 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Balance Due / Refund --}}
            @if ($charges['balance_due'] > 0)
                <div
                    class="mt-4 p-4 bg-red-50 dark:bg-red-500/10 rounded-xl border border-red-200 dark:border-red-500/20">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-red-800 dark:text-red-300">Balance Due</p>
                            <p class="text-xs text-red-600 dark:text-red-400">Payment required at check-out</p>
                        </div>
                        <p class="text-lg font-bold text-red-600 dark:text-red-400">Rp
                            {{ number_format($charges['balance_due'], 0, ',', '.') }}</p>
                    </div>
                </div>
            @elseif($charges['deposit_paid'] > $charges['grand_total'])
                @php $refundAmount = $charges['deposit_paid'] - $charges['grand_total']; @endphp
                <div
                    class="mt-4 p-4 bg-green-50 dark:bg-green-500/10 rounded-xl border border-green-200 dark:border-green-500/20">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-green-800 dark:text-green-300">Refund Due</p>
                            <p class="text-xs text-green-600 dark:text-green-400">Return to guest</p>
                        </div>
                        <p class="text-lg font-bold text-green-600 dark:text-green-400">Rp
                            {{ number_format($refundAmount, 0, ',', '.') }}</p>
                    </div>
                </div>
            @else
                <div
                    class="mt-4 p-4 bg-blue-50 dark:bg-blue-500/10 rounded-xl border border-blue-200 dark:border-blue-500/20">
                    <p class="text-sm font-medium text-blue-800 dark:text-blue-300 text-center">
                        ✓ Payment settled — No balance due
                    </p>
                </div>
            @endif
        </div>

        {{-- Check-out Form --}}
        <form method="POST" action="{{ route('hotel.checkout.process', $reservation) }}"
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            @csrf
            <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-4">Payment &
                Check-out Details</h3>

            {{-- Payment Method --}}
            <div class="mb-6">
                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Payment Method *</label>
                <select name="payment_method" required
                    class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select payment method</option>
                    <option value="cash">Cash</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="debit_card">Debit Card</option>
                    <option value="transfer">Bank Transfer</option>
                    <option value="qris">QRIS</option>
                </select>
            </div>

            {{-- Amount Paid --}}
            <div class="mb-6">
                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Amount Paid (Rp)
                    *</label>
                <input type="number" name="amount_paid" step="1000" min="0" required
                    value="{{ old('amount_paid', $charges['balance_due'] > 0 ? $charges['balance_due'] : $charges['grand_total']) }}"
                    placeholder="Enter amount paid"
                    class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                @if ($charges['balance_due'] > 0)
                    <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                        Minimum payment required: Rp {{ number_format($charges['balance_due'], 0, ',', '.') }}
                    </p>
                @endif
            </div>

            {{-- Transaction Reference --}}
            <div class="mb-6">
                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Transaction Reference
                    (Optional)</label>
                <input type="text" name="transaction_reference" value="{{ old('transaction_reference') }}"
                    placeholder="e.g., card number, transfer ID"
                    class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- Notes/Feedback --}}
            <div class="mb-6">
                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Notes / Guest
                    Feedback</label>
                <textarea name="notes" rows="3" placeholder="Any notes or feedback from the guest..."
                    class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
            </div>

            {{-- Action Buttons --}}
            <div
                class="flex flex-col sm:flex-row gap-3 justify-between items-center pt-4 border-t border-gray-100 dark:border-white/10">
                <a href="{{ route('hotel.reservations.show', $reservation) }}"
                    class="text-sm text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-white">
                    ← Back to Reservation
                </a>
                <button type="submit"
                    class="w-full sm:w-auto px-8 py-3 text-base font-medium bg-green-600 hover:bg-green-700 text-white rounded-xl transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Process Check-out & Generate Receipt
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
