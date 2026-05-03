<x-app-layout>
    <x-slot name="header">Check-out — Room {{ $reservation->room?->number ?? 'N/A' }}</x-slot>

    @php
        $guest = $reservation->guest;
        $room = $reservation->room;
        $checkIn = $reservation->checkInOuts->where('type', 'check_in')->first();
    @endphp

    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Guest Info Card --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">Guest
                Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-500">Guest Name</p>
                    <p class="font-medium text-gray-900">{{ $guest?->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Room</p>
                    <p class="font-medium text-gray-900">{{ $room?->number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Checked In</p>
                    <p class="font-medium text-gray-900">
                        {{ $checkIn?->processed_at?->format('d M Y H:i') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Stay Duration</p>
                    <p class="font-medium text-gray-900">{{ $reservation->nights }} night(s)</p>
                </div>
            </div>
        </div>

        {{-- Charges Summary Card --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">Charges
                Summary</h3>

            <div class="space-y-3">
                {{-- Room Charges --}}
                <div class="flex justify-between items-center py-2">
                    <div>
                        <p class="text-sm text-gray-900">Room Charges</p>
                        <p class="text-xs text-gray-500">{{ $charges['nights'] }} night(s) × Rp
                            {{ number_format($charges['rate_per_night'], 0, ',', '.') }}</p>
                    </div>
                    <p class="font-medium text-gray-900">Rp
                        {{ number_format($charges['room_charge'], 0, ',', '.') }}</p>
                </div>

                {{-- Mini-bar Charges --}}
                @if ($charges['minibar_charges'] > 0)
                    <div class="flex justify-between items-center py-2">
                        <div>
                            <p class="text-sm text-gray-900">Mini-bar Charges</p>
                            <p class="text-xs text-gray-500">{{ count($charges['minibar_items']) }}
                                item(s)</p>
                        </div>
                        <p class="font-medium text-gray-900">Rp
                            {{ number_format($charges['minibar_charges'], 0, ',', '.') }}</p>
                    </div>

                    {{-- Mini-bar Items Detail --}}
                    <div class="ml-4 space-y-1">
                        @foreach ($charges['minibar_items'] as $item)
                            <div class="flex justify-between text-xs text-gray-600">
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
                            <p class="text-sm text-gray-900">Additional Charges</p>
                        </div>
                        <p class="font-medium text-gray-900">Rp
                            {{ number_format($charges['additional_charges'], 0, ',', '.') }}</p>
                    </div>
                @endif

                {{-- Discount --}}
                @if ($charges['discount'] > 0)
                    <div class="flex justify-between items-center py-2 text-green-600">
                        <div>
                            <p class="text-sm">Discount</p>
                        </div>
                        <p class="font-medium">- Rp {{ number_format($charges['discount'], 0, ',', '.') }}</p>
                    </div>
                @endif

                {{-- Subtotal --}}
                <div class="flex justify-between items-center py-2 border-t border-gray-100">
                    <p class="text-sm text-gray-600">Subtotal</p>
                    <p class="font-medium text-gray-900">Rp
                        {{ number_format($charges['subtotal'], 0, ',', '.') }}</p>
                </div>

                {{-- Tax --}}
                <div class="flex justify-between items-center py-2">
                    <div>
                        <p class="text-sm text-gray-900">Tax</p>
                        <p class="text-xs text-gray-500">{{ $charges['tax_rate'] }}%</p>
                    </div>
                    <p class="font-medium text-gray-900">Rp
                        {{ number_format($charges['tax_amount'], 0, ',', '.') }}</p>
                </div>

                {{-- Deposit Paid --}}
                @if ($charges['deposit_paid'] > 0)
                    <div class="flex justify-between items-center py-2 border-t border-gray-100">
                        <div>
                            <p class="text-sm text-gray-900">Deposit Paid</p>
                            <p class="text-xs text-gray-500">Collected at check-in</p>
                        </div>
                        <p class="font-medium text-green-600">Rp
                            {{ number_format($charges['deposit_paid'], 0, ',', '.') }}</p>
                    </div>
                @endif

                {{-- Grand Total --}}
                <div
                    class="flex justify-between items-center py-3 border-t-2 border-gray-200 bg-gray-50 -mx-6 px-6 rounded-b-xl">
                    <p class="text-base font-semibold text-gray-900">Grand Total</p>
                    <p class="text-xl font-bold text-blue-600">Rp
                        {{ number_format($charges['grand_total'], 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Balance Due / Refund --}}
            @if ($charges['balance_due'] > 0)
                <div
                    class="mt-4 p-4 bg-red-50 rounded-xl border border-red-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-red-800">Balance Due</p>
                            <p class="text-xs text-red-600">Payment required at check-out</p>
                        </div>
                        <p class="text-lg font-bold text-red-600">Rp
                            {{ number_format($charges['balance_due'], 0, ',', '.') }}</p>
                    </div>
                </div>
            @elseif($charges['deposit_paid'] > $charges['grand_total'])
                @php $refundAmount = $charges['deposit_paid'] - $charges['grand_total']; @endphp
                <div
                    class="mt-4 p-4 bg-green-50 rounded-xl border border-green-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-green-800">Refund Due</p>
                            <p class="text-xs text-green-600">Return to guest</p>
                        </div>
                        <p class="text-lg font-bold text-green-600">Rp
                            {{ number_format($refundAmount, 0, ',', '.') }}</p>
                    </div>
                </div>
            @else
                <div
                    class="mt-4 p-4 bg-blue-50 rounded-xl border border-blue-200">
                    <p class="text-sm font-medium text-blue-800 text-center">
                        ✓ Payment settled — No balance due
                    </p>
                </div>
            @endif
        </div>

        {{-- Check-out Form --}}
        <form method="POST" action="{{ route('hotel.checkout.process', $reservation) }}"
            class="bg-white rounded-2xl border border-gray-200 p-6">
            @csrf
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">Payment &
                Check-out Details</h3>

            {{-- Payment Method --}}
            <div class="mb-6">
                <label class="block text-xs font-medium text-gray-600 mb-2">Payment Method *</label>
                <select name="payment_method" required
                    class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                <label class="block text-xs font-medium text-gray-600 mb-2">Amount Paid (Rp)
                    *</label>
                <input type="number" name="amount_paid" step="1000" min="0" required
                    value="{{ old('amount_paid', $charges['balance_due'] > 0 ? $charges['balance_due'] : $charges['grand_total']) }}"
                    placeholder="Enter amount paid"
                    class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @if ($charges['balance_due'] > 0)
                    <p class="mt-1 text-xs text-amber-600">
                        Minimum payment required: Rp {{ number_format($charges['balance_due'], 0, ',', '.') }}
                    </p>
                @endif
            </div>

            {{-- Transaction Reference --}}
            <div class="mb-6">
                <label class="block text-xs font-medium text-gray-600 mb-2">Transaction Reference
                    (Optional)</label>
                <input type="text" name="transaction_reference" value="{{ old('transaction_reference') }}"
                    placeholder="e.g., card number, transfer ID"
                    class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- Notes/Feedback --}}
            <div class="mb-6">
                <label class="block text-xs font-medium text-gray-600 mb-2">Notes / Guest
                    Feedback</label>
                <textarea name="notes" rows="3" placeholder="Any notes or feedback from the guest..."
                    class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
            </div>

            {{-- Action Buttons --}}
            <div
                class="flex flex-col sm:flex-row gap-3 justify-between items-center pt-4 border-t border-gray-100">
                <a href="{{ route('hotel.reservations.show', $reservation) }}"
                    class="text-sm text-gray-500 hover:text-gray-700">
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
