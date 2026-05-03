<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> <?php echo e(isset($reservation) ? 'Edit Reservation' : 'New Reservation'); ?> <?php $__env->endSlot(); ?>

    <div x-data="reservationForm()" class="max-w-4xl mx-auto">
        <form method="POST"
            action="<?php echo e(isset($reservation) ? route('hotel.reservations.update', $reservation) : route('hotel.reservations.store')); ?>"
            @submit="formSubmitted = true" class="space-y-6">
            <?php echo csrf_field(); ?>
            <?php if(isset($reservation)): ?>
                <?php echo method_field('PUT'); ?>
            <?php endif; ?>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Guest Information
                </h3>

                <input type="hidden" name="guest_id" x-model="selectedGuestId" id="selected_guest_id">
                <input type="hidden" name="room_type_id" x-model="selectedRoomTypeId" id="selected_room_type_id">

                <?php if(isset($reservation)): ?>
                    <input type="hidden" id="reservation_id" value="<?php echo e($reservation->id); ?>">
                <?php endif; ?>

                <div class="flex items-center gap-4 mb-4">
                    <label class="relative flex-1">
                        <input type="text" x-model="guestSearch" @input.debounce.300ms="searchGuests()"
                            @focus="showGuestDropdown = true" @click.away="showGuestDropdown = false"
                            placeholder="Search guest by name, email, or phone..."
                            class="w-full px-4 py-3 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">

                        <div x-show="showGuestDropdown && guestResults.length > 0" x-transition
                            class="absolute z-20 w-full mt-1 bg-white rounded-xl border border-gray-200 shadow-xl max-h-60 overflow-y-auto">
                            <template x-for="guest in guestResults" :key="guest.id">
                                <button type="button" @click="selectGuest(guest)"
                                    class="w-full px-4 py-3 text-left hover:bg-gray-50 flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900" x-text="guest.name"></p>
                                        <p class="text-xs text-gray-500"
                                            x-text="guest.email + ' · ' + guest.phone"></p>
                                    </div>
                                    <span class="text-xs text-gray-400" x-text="guest.guest_code"></span>
                                </button>
                            </template>
                        </div>
                        <div x-show="showGuestDropdown && guestSearch.length >= 2 && guestResults.length === 0 && !isLoadingGuests"
                            class="absolute z-20 w-full mt-1 bg-white rounded-xl border border-gray-200 shadow-xl p-4 text-center text-gray-500 text-sm">
                            No guests found. <button type="button"
                                @click="showNewGuestForm = true; showGuestDropdown = false"
                                class="text-blue-500 hover:underline">Add new guest</button>
                        </div>
                    </label>

                    <button type="button" @click="showNewGuestForm = !showNewGuestForm"
                        class="px-4 py-3 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 whitespace-nowrap">
                        <span x-text="showNewGuestForm ? 'Cancel' : '+ New Guest'"></span>
                    </button>
                </div>

                
                <div x-show="selectedGuestId && !showNewGuestForm"
                    class="mb-4 p-4 rounded-xl bg-blue-50 border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900" x-text="selectedGuest?.name"></p>
                            <p class="text-sm text-gray-600" x-text="selectedGuest?.email"></p>
                            <p class="text-sm text-gray-600" x-text="selectedGuest?.phone"></p>
                        </div>
                        <button type="button" @click="clearGuest()" class="text-gray-400 hover:text-red-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                
                <div x-show="showNewGuestForm" x-transition
                    class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 p-4 rounded-xl bg-gray-50 border border-gray-200">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Guest Name
                            *</label>
                        <input type="text" name="new_guest_name" x-model="newGuest.name"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                        <input type="email" name="new_guest_email" x-model="newGuest.email"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                        <input type="tel" name="new_guest_phone" x-model="newGuest.phone"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">ID Type</label>
                        <select name="new_guest_id_type" x-model="newGuest.id_type"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select...</option>
                            <option value="ktp">KTP</option>
                            <option value="passport">Passport</option>
                            <option value="sim">SIM</option>
                            <option value="kitas">KITAS</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">ID
                            Number</label>
                        <input type="text" name="new_guest_id_number" x-model="newGuest.id_number"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Room Details
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Room Type
                            *</label>
                        <select name="room_type_id_select" @change="onRoomTypeChange()" x-model="selectedRoomTypeId"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Room Type...</option>
                            <?php $__currentLoopData = $roomTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($rt->id); ?>" data-rate="<?php echo e($rt->base_rate); ?>">
                                    <?php echo e($rt->name); ?> - Rp <?php echo e(number_format($rt->base_rate, 0, ',', '.')); ?>/night
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Room
                            (Optional)</label>
                        <select name="room_id" x-model="selectedRoomId" :disabled="!selectedRoomTypeId"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">Auto-assign at check-in</option>
                            <template x-for="room in availableRooms" :key="room.id">
                                <option :value="room.id" x-text="room.number + ' (' + room.floor + ')'"
                                    :selected="selectedRoomId == room.id"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Stay Dates
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Check-in Date
                            *</label>
                        <input type="date" name="check_in_date" x-model="checkInDate" @change="calculateNights()"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Check-out Date
                            *</label>
                        <input type="date" name="check_out_date" x-model="checkOutDate"
                            @change="calculateNights()"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nights</label>
                        <input type="text" :value="nights" readonly
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-100 text-gray-900 text-center font-medium">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Adults</label>
                        <input type="number" name="adults" x-model="adults" min="1" max="10"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-medium text-gray-600 mb-1">Children</label>
                        <input type="number" name="children" x-model="children" min="0" max="10"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Rate & Payment
                </h3>

                <div x-show="isLoadingRate" class="text-center py-4 text-gray-500 text-sm">
                    <span class="animate-pulse">Calculating rates...</span>
                </div>

                <div x-show="!isLoadingRate" class="space-y-4">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <label
                                class="block text-xs font-medium text-gray-600 mb-1">Rate/Night</label>
                            <input type="number" name="rate_per_night" x-model="ratePerNight"
                                @input="recalculateTotal()" step="1000" min="0"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label
                                class="block text-xs font-medium text-gray-600 mb-1">Subtotal</label>
                            <input type="text" :value="'Rp ' + formatNumber(subtotal)" readonly
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-100 text-gray-900 font-medium">
                        </div>
                        <div>
                            <label
                                class="block text-xs font-medium text-gray-600 mb-1">Discount</label>
                            <input type="number" name="discount" x-model="discount" @input="recalculateTotal()"
                                min="0" step="1000"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Tax</label>
                            <input type="text" :value="'Rp ' + formatNumber(tax)" readonly
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-100 text-gray-900">
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-between p-4 rounded-xl bg-green-50 border border-green-200">
                        <span class="font-medium text-gray-900">Grand Total</span>
                        <span class="text-2xl font-bold text-green-600"
                            x-text="'Rp ' + formatNumber(grandTotal)"></span>
                    </div>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Additional Information
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Booking
                            Source</label>
                        <select name="source" x-model="source"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="direct">Direct / Walk-in</option>
                            <option value="website">Website</option>
                            <option value="bookingcom">Booking.com</option>
                            <option value="agoda">Agoda</option>
                            <option value="expedia">Expedia</option>
                            <option value="airbnb">Airbnb</option>
                            <option value="tripadvisor">TripAdvisor</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Special
                            Requests</label>
                        <textarea name="special_requests" x-model="specialRequests" rows="2"
                            placeholder="Room preferences, special occasions, etc."
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
            </div>

            
            <div class="flex items-center justify-end gap-3">
                <a href="<?php echo e(route('hotel.reservations.index')); ?>"
                    class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Cancel</a>
                <button type="submit" :disabled="formSubmitted"
                    class="px-6 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span
                        x-text="formSubmitted ? 'Processing...' : (<?php echo e(isset($reservation) ? 'true' : 'false'); ?> ? 'Update Reservation' : 'Create Reservation')"></span>
                </button>
            </div>
        </form>
    </div>

    
    <script>
        window.reservationForm = function() {
            return {
                // State
                guestSearch: '',
                guestResults: [],
                selectedGuestId: <?php echo e(isset($reservation) && $reservation->guest ? $reservation->guest->id : 'null'); ?>,
                selectedGuest: <?php echo e(isset($reservation) && $reservation->guest ? json_encode($reservation->guest->only(['id', 'name', 'email', 'phone', 'guest_code'])) : 'null'); ?>,
                showGuestDropdown: false,
                showNewGuestForm: false,
                isLoadingGuests: false,
                formSubmitted: false,

                // Room
                selectedRoomTypeId: <?php echo e(isset($reservation) && $reservation->roomType ? $reservation->roomType->id : 'null'); ?>,
                selectedRoomId: <?php echo e(isset($reservation) && $reservation->room ? $reservation->room->id : 'null'); ?>,
                availableRooms: [],

                // Dates
                checkInDate: '<?php echo e(isset($reservation) ? $reservation->check_in_date : date('Y-m-d')); ?>',
                checkOutDate: '<?php echo e(isset($reservation) ? $reservation->check_out_date : date('Y-m-d', strtotime('+1 day'))); ?>',
                nights: 1,
                adults: <?php echo e(isset($reservation) ? $reservation->adults : 1); ?>,
                children: <?php echo e(isset($reservation) ? $reservation->children : 0); ?>,

                // Rates
                ratePerNight: <?php echo e(isset($reservation) ? $reservation->rate_per_night : 0); ?>,
                subtotal: <?php echo e(isset($reservation) ? $reservation->total_amount : 0); ?>,
                discount: <?php echo e(isset($reservation) ? $reservation->discount : 0); ?>,
                taxRate: 11, // Default 11% tax
                tax: <?php echo e(isset($reservation) ? $reservation->tax : 0); ?>,
                grandTotal: <?php echo e(isset($reservation) ? $reservation->grand_total : 0); ?>,
                isLoadingRate: false,

                // Other
                source: '<?php echo e(isset($reservation) ? $reservation->source : 'direct'); ?>',
                specialRequests: '<?php echo e(isset($reservation) ? addslashes($reservation->special_requests ?? '') : ''); ?>',

                // New guest form
                newGuest: {
                    name: '',
                    email: '',
                    phone: '',
                    id_type: '',
                    id_number: ''
                },

                init() {
                    this.calculateNights();
                    if (this.selectedRoomTypeId) {
                        this.loadAvailableRooms();
                    }
                    if (this.selectedRoomTypeId && this.checkInDate && this.checkOutDate) {
                        this.fetchRate();
                    }
                },

                async searchGuests() {
                    if (this.guestSearch.length < 2) {
                        this.guestResults = [];
                        return;
                    }
                    this.isLoadingGuests = true;
                    try {
                        const res = await fetch('/hotel/guests/search?q=' + encodeURIComponent(this.guestSearch));
                        const data = await res.json();
                        this.guestResults = data.data || [];
                    } catch (e) {
                        console.error(e);
                    }
                    this.isLoadingGuests = false;
                },

                selectGuest(guest) {
                    this.selectedGuestId = guest.id;
                    this.selectedGuest = guest;
                    this.guestSearch = guest.name;
                    this.showGuestDropdown = false;
                    this.showNewGuestForm = false;
                    document.getElementById('selected_guest_id').value = guest.id;
                },

                clearGuest() {
                    this.selectedGuestId = null;
                    this.selectedGuest = null;
                    this.guestSearch = '';
                    document.getElementById('selected_guest_id').value = '';
                },

                async onRoomTypeChange() {
                    document.getElementById('selected_room_type_id').value = this.selectedRoomTypeId;
                    await this.loadAvailableRooms();
                    await this.fetchRate();
                },

                async loadAvailableRooms() {
                    if (!this.selectedRoomTypeId) {
                        this.availableRooms = [];
                        return;
                    }
                    try {
                        const res = await fetch('/hotel/rooms/by-type/' + this.selectedRoomTypeId);
                        const data = await res.json();
                        this.availableRooms = data.rooms || [];
                    } catch (e) {
                        console.error(e);
                    }
                },

                calculateNights() {
                    if (this.checkInDate && this.checkOutDate) {
                        const ci = new Date(this.checkInDate);
                        const co = new Date(this.checkOutDate);
                        this.nights = Math.max(1, Math.round((co - ci) / (1000 * 60 * 60 * 24)));
                        this.fetchRate();
                    }
                },

                async fetchRate() {
                    if (!this.selectedRoomTypeId || !this.checkInDate || !this.checkOutDate) {
                        return;
                    }
                    this.isLoadingRate = true;
                    try {
                        const res = await fetch('/hotel/reservations/calculate-rate', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute(
                                        'content')
                            },
                            body: JSON.stringify({
                                room_type_id: this.selectedRoomTypeId,
                                check_in_date: this.checkInDate,
                                check_out_date: this.checkOutDate
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.ratePerNight = data.data.rate_per_night;
                            this.subtotal = data.data.subtotal;
                            this.tax = data.data.tax;
                            this.grandTotal = data.data.grand_total;
                        }
                    } catch (e) {
                        console.error(e);
                    }
                    this.isLoadingRate = false;
                },

                recalculateTotal() {
                    this.subtotal = (parseFloat(this.ratePerNight) || 0) * (parseInt(this.nights) || 1);
                    const afterDiscount = this.subtotal - (parseFloat(this.discount) || 0);
                    this.tax = afterDiscount * (this.taxRate / 100);
                    this.grandTotal = afterDiscount + this.tax;
                },

                formatNumber(num) {
                    return new Intl.NumberFormat('id-ID').format(Math.round(num || 0));
                }
            }
        };

        function showToast(message, type = 'success') {
            const colors = {
                success: 'bg-green-600',
                error: 'bg-red-600',
                warning: 'bg-yellow-500',
                info: 'bg-blue-600'
            };
            const icons = {
                success: '✓',
                error: '✕',
                warning: '⚠',
                info: 'ℹ'
            };
            const toast = document.createElement('div');
            toast.className =
                `fixed bottom-6 right-6 z-[9999] flex items-center gap-3 px-4 py-3 rounded-2xl text-white text-sm font-medium shadow-xl transition-all duration-300 translate-y-4 opacity-0 ${colors[type] || colors.success}`;
            toast.innerHTML = `<span>${icons[type]}</span><span>${message}</span>`;
            document.body.appendChild(toast);
            requestAnimationFrame(() => toast.classList.remove('translate-y-4', 'opacity-0'));
            setTimeout(() => {
                toast.classList.add('translate-y-4', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        <?php if(session('success')): ?>
            showToast(<?php echo json_encode(session('success'), 15, 512) ?>, 'success');
        <?php endif; ?>
        <?php if(session('error')): ?>
            showToast(<?php echo json_encode(session('error'), 15, 512) ?>, 'error');
        <?php endif; ?>
        <?php if($errors->any()): ?>
            showToast(<?php echo json_encode($errors->first(), 15, 512) ?>, 'error');
        <?php endif; ?>
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\reservations\create.blade.php ENDPATH**/ ?>