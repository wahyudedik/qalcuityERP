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
     <?php $__env->slot('header', null, []); ?> Pre-Arrival Form — Reservation #<?php echo e($reservation->reservation_number); ?> <?php $__env->endSlot(); ?>

    <?php
        $guest = $reservation->guest;
        $roomType = $reservation->roomType;
    ?>

    <div class="max-w-5xl mx-auto space-y-6">
        
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-blue-900 dark:text-blue-300">Pre-Arrival Registration</h3>
                    <p class="text-sm text-blue-700 dark:text-blue-400 mt-1">
                        Please complete this form before your arrival to speed up the check-in process.
                    </p>
                </div>
            </div>
        </div>

        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-white/10 p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-4">Guest
                    Information</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Name</p>
                        <p class="font-medium text-gray-900 dark:text-white"><?php echo e($guest?->name ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Email</p>
                        <p class="font-medium text-gray-900 dark:text-white"><?php echo e($guest?->email ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Phone</p>
                        <p class="font-medium text-gray-900 dark:text-white"><?php echo e($guest?->phone ?? '-'); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-white/10 p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-4">
                    Reservation Details</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Room Type</p>
                        <p class="font-medium text-gray-900 dark:text-white"><?php echo e($roomType?->name ?? '-'); ?></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-slate-400">Check-in</p>
                            <p class="font-medium text-gray-900 dark:text-white">
                                <?php echo e($reservation->check_in_date->format('d M Y')); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-slate-400">Check-out</p>
                            <p class="font-medium text-gray-900 dark:text-white">
                                <?php echo e($reservation->check_out_date->format('d M Y')); ?></p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Nights</p>
                        <p class="font-medium text-gray-900 dark:text-white"><?php echo e($reservation->nights); ?></p>
                    </div>
                </div>
            </div>
        </div>

        
        <form method="POST" action="<?php echo e(route('hotel.checkin.pre-arrival.submit', $reservation)); ?>"
            class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-white/10 p-6">
            <?php echo csrf_field(); ?>

            
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                    Identification Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ID Type</label>
                        <select name="id_type" value="<?php echo e(old('id_type', $form->id_type ?? '')); ?>"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                            <option value="">Select ID Type</option>
                            <option value="passport">Passport</option>
                            <option value="ktp">KTP (National ID)</option>
                            <option value="sim">Driver's License</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ID Number</label>
                        <input type="text" name="id_number" value="<?php echo e(old('id_number', $form->id_number ?? '')); ?>"
                            placeholder="Enter your ID number"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ID Expiry
                            Date</label>
                        <input type="date" name="id_expiry" value="<?php echo e(old('id_expiry', $form->id_expiry ?? '')); ?>"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nationality</label>
                        <input type="text" name="nationality"
                            value="<?php echo e(old('nationality', $form->nationality ?? 'Indonesian')); ?>"
                            placeholder="e.g., Indonesian"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date of
                            Birth</label>
                        <input type="date" name="date_of_birth"
                            value="<?php echo e(old('date_of_birth', $form->date_of_birth ?? '')); ?>"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gender</label>
                        <select name="gender"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
            </div>

            
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    Emergency Contact
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Contact
                            Name</label>
                        <input type="text" name="emergency_contact_name"
                            value="<?php echo e(old('emergency_contact_name', $form->emergency_contact_name ?? '')); ?>"
                            placeholder="Full name"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Contact
                            Phone</label>
                        <input type="text" name="emergency_contact_phone"
                            value="<?php echo e(old('emergency_contact_phone', $form->emergency_contact_phone ?? '')); ?>"
                            placeholder="+62 xxx xxxx xxxx"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Relationship</label>
                        <input type="text" name="emergency_contact_relationship"
                            value="<?php echo e(old('emergency_contact_relationship', $form->emergency_contact_relationship ?? '')); ?>"
                            placeholder="e.g., Spouse, Parent"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    Room & Bed Preferences
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Room
                            Preference</label>
                        <select name="room_preference"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                            <option value="">No Preference</option>
                            <option value="high_floor">High Floor</option>
                            <option value="low_floor">Low Floor</option>
                            <option value="near_elevator">Near Elevator</option>
                            <option value="away_from_elevator">Away from Elevator</option>
                            <option value="ocean_view">Ocean View</option>
                            <option value="city_view">City View</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Bed
                            Preference</label>
                        <select name="bed_preference"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                            <option value="">No Preference</option>
                            <option value="twin">Twin Beds</option>
                            <option value="king">King Bed</option>
                            <option value="queen">Queen Bed</option>
                        </select>
                    </div>
                </div>
            </div>

            
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Arrival Details
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estimated
                            Arrival Time</label>
                        <input type="time" name="estimated_arrival_time"
                            value="<?php echo e(old('estimated_arrival_time', $form->estimated_arrival_time ?? '')); ?>"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Transportation
                            Method</label>
                        <select name="transportation_method"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Method</option>
                            <option value="taxi">Taxi</option>
                            <option value="airport_shuttle">Airport Shuttle</option>
                            <option value="private_car">Private Car</option>
                            <option value="public_transport">Public Transport</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Flight Number
                            (if applicable)</label>
                        <input type="text" name="flight_number"
                            value="<?php echo e(old('flight_number', $form->flight_number ?? '')); ?>" placeholder="e.g., GA123"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="airport_pickup_required" value="1"
                            <?php echo e(old('airport_pickup_required', $form->airport_pickup_required ?? false) ? 'checked' : ''); ?>

                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Require Airport Pickup
                        </label>
                    </div>
                </div>
            </div>

            
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Special Requests & Dietary Requirements
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dietary
                            Requirements</label>
                        <textarea name="dietary_requirements" rows="3"
                            placeholder="Any allergies, dietary restrictions, or special meal requests..."
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"><?php echo e(old('dietary_requirements', $form->dietary_requirements ?? '')); ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Special
                            Requests</label>
                        <textarea name="special_requests" rows="3" placeholder="Any special requests or notes for your stay..."
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"><?php echo e(old('special_requests', is_array($form->special_requests ?? null) ? implode("\n", $form->special_requests) : $form->special_requests ?? '')); ?></textarea>
                    </div>
                </div>
            </div>

            
            <div
                class="mb-8 p-4 bg-gray-50 dark:bg-slate-900/50 rounded-lg border border-gray-200 dark:border-white/10">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Terms & Consent</h3>
                <div class="space-y-3">
                    <label class="flex items-start">
                        <input type="checkbox" name="terms_accepted" required
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-1">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            I agree to the hotel's <a href="#" class="text-blue-600 hover:underline">Terms &
                                Conditions</a> and <a href="#"
                                class="text-blue-600 hover:underline">Cancellation Policy</a> *
                        </span>
                    </label>
                    <label class="flex items-start">
                        <input type="checkbox" name="data_processing_consent" required
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-1">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            I consent to the processing of my personal data in accordance with the <a href="#"
                                class="text-blue-600 hover:underline">Privacy Policy</a> *
                        </span>
                    </label>
                    <label class="flex items-start">
                        <input type="checkbox" name="marketing_consent" value="1"
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-1">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            I would like to receive promotional offers and updates (optional)
                        </span>
                    </label>
                </div>
            </div>

            
            <div
                class="flex flex-col sm:flex-row gap-3 justify-between items-center pt-6 border-t border-gray-200 dark:border-white/10">
                <a href="<?php echo e(route('hotel.checkin-out.index')); ?>"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    ← Back to Dashboard
                </a>
                <button type="submit"
                    class="w-full sm:w-auto px-8 py-3 text-base font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Submit Pre-Arrival Form
                </button>
            </div>
        </form>
    </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\check-in\pre-arrival.blade.php ENDPATH**/ ?>