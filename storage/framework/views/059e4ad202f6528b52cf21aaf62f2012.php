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
     <?php $__env->slot('header', null, []); ?> <?php echo e($guest->name); ?> - Preferences <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('hotel.guests.show', $guest)); ?>"
                class="text-gray-600 hover:text-blue-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
    </div>

    <div class="max-w-6xl mx-auto">
        
        <?php if(session('success')): ?>
            <div
                class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200">
                <p class="text-green-800 text-sm"><?php echo e(session('success')); ?></p>
            </div>
        <?php endif; ?>

        
        <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div
                        class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-2xl font-bold text-blue-600 shrink-0">
                        <?php echo e(substr($guest->name ?? '?', 0, 1)); ?>

                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900"><?php echo e($guest->name); ?></h2>
                        <p class="text-sm text-gray-500"><?php echo e($guest->email ?? 'No email provided'); ?>

                        </p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs text-gray-500">Loyalty Points:</span>
                            <span
                                class="text-sm font-semibold text-blue-600"><?php echo e(number_format($guest->loyalty_points ?? 0)); ?></span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="openRedeemPointsModal()"
                        class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium transition">
                        Redeem Points
                    </button>
                    <button onclick="openAwardPointsModal()"
                        class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium transition">
                        Award Points
                    </button>
                    <button onclick="openAddPreferenceModal()"
                        class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
                        Add Preference
                    </button>
                </div>
            </div>
        </div>

        
        <div id="suggestions-section"
            class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-2xl border border-blue-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Smart Suggestions
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">AI-powered preferences based on stay
                        history</p>
                </div>
                <button onclick="loadSuggestions()"
                    class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium transition">
                    Refresh Suggestions
                </button>
            </div>
            <div id="suggestions-loading" class="text-center py-8">
                <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <p class="text-sm text-gray-600 mt-2">Analyzing guest history...</p>
            </div>
            <div id="suggestions-content" class="hidden">
                <div id="suggestions-list" class="space-y-3">
                    <!-- Suggestions will be loaded here -->
                </div>
            </div>
        </div>

        
        <div id="loyalty-section"
            class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl border border-amber-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                        Loyalty Rewards
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Available rewards for your VIP level</p>
                </div>
            </div>
            <div id="loyalty-rewards-list" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <!-- Rewards will be loaded here -->
            </div>
            <div id="next-tier-info" class="mt-4 p-4 bg-white/50 rounded-lg hidden">
                <!-- Next tier requirements will be shown here -->
            </div>
        </div>

        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Room Preferences
                    </h3>
                    <button onclick="openAddPreferenceModal('room')"
                        class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        + Add
                    </button>
                </div>
                <div class="space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $preferences->where('category', 'room'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $preference): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-start justify-between p-3 rounded-xl bg-gray-50">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-sm font-medium text-gray-900"><?php echo e(ucwords(str_replace('_', ' ', $preference->preference_key))); ?></span>
                                    <?php if($preference->priority >= 3): ?>
                                        <span
                                            class="px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">High</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">
                                    <?php echo e($preference->preference_value ?? 'Not specified'); ?></p>
                                <?php if($preference->is_auto_applied): ?>
                                    <span class="text-xs text-green-600 mt-1 block">Auto-applied to
                                        reservations</span>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center gap-1">
                                <button
                                    onclick="editPreference(<?php echo e($preference->id); ?>, '<?php echo e($preference->preference_key); ?>', '<?php echo e($preference->preference_value); ?>', <?php echo e($preference->priority); ?>, <?php echo e($preference->is_auto_applied ? 'true' : 'false'); ?>)"
                                    class="p-1.5 rounded-lg hover:bg-gray-200 transition">
                                    <svg class="w-4 h-4 text-gray-500" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <form
                                    action="<?php echo e(route('hotel.guests.destroy-preference', [$guest->id, $preference->id])); ?>"
                                    method="POST" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit"
                                        class="p-1.5 rounded-lg hover:bg-red-100 transition"
                                        onclick="return confirm('Delete this preference?')">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-gray-500 text-center py-4">No room preferences yet
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                        Amenity Preferences
                    </h3>
                    <button onclick="openAddPreferenceModal('amenity')"
                        class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        + Add
                    </button>
                </div>
                <div class="space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $preferences->where('category', 'amenity'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $preference): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-start justify-between p-3 rounded-xl bg-gray-50">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-sm font-medium text-gray-900"><?php echo e(ucwords(str_replace('_', ' ', $preference->preference_key))); ?></span>
                                    <?php if($preference->priority >= 3): ?>
                                        <span
                                            class="px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">High</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">
                                    <?php echo e($preference->preference_value ?? 'Not specified'); ?></p>
                            </div>
                            <div class="flex items-center gap-1">
                                <button
                                    onclick="editPreference(<?php echo e($preference->id); ?>, '<?php echo e($preference->preference_key); ?>', '<?php echo e($preference->preference_value); ?>', <?php echo e($preference->priority); ?>, <?php echo e($preference->is_auto_applied ? 'true' : 'false'); ?>)"
                                    class="p-1.5 rounded-lg hover:bg-gray-200 transition">
                                    <svg class="w-4 h-4 text-gray-500" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <form
                                    action="<?php echo e(route('hotel.guests.destroy-preference', [$guest->id, $preference->id])); ?>"
                                    method="POST" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit"
                                        class="p-1.5 rounded-lg hover:bg-red-100 transition"
                                        onclick="return confirm('Delete this preference?')">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-gray-500 text-center py-4">No amenity preferences
                            yet</p>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Dietary Preferences
                    </h3>
                    <button onclick="openAddPreferenceModal('dietary')"
                        class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        + Add
                    </button>
                </div>
                <div class="space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $preferences->where('category', 'dietary'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $preference): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-start justify-between p-3 rounded-xl bg-gray-50">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-sm font-medium text-gray-900"><?php echo e(ucwords(str_replace('_', ' ', $preference->preference_key))); ?></span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">
                                    <?php echo e($preference->preference_value ?? 'Not specified'); ?></p>
                            </div>
                            <div class="flex items-center gap-1">
                                <button
                                    onclick="editPreference(<?php echo e($preference->id); ?>, '<?php echo e($preference->preference_key); ?>', '<?php echo e($preference->preference_value); ?>', <?php echo e($preference->priority); ?>, <?php echo e($preference->is_auto_applied ? 'true' : 'false'); ?>)"
                                    class="p-1.5 rounded-lg hover:bg-gray-200 transition">
                                    <svg class="w-4 h-4 text-gray-500" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <form
                                    action="<?php echo e(route('hotel.guests.destroy-preference', [$guest->id, $preference->id])); ?>"
                                    method="POST" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit"
                                        class="p-1.5 rounded-lg hover:bg-red-100 transition"
                                        onclick="return confirm('Delete this preference?')">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-gray-500 text-center py-4">No dietary preferences
                            yet</p>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                        Communication Preferences
                    </h3>
                    <button onclick="openAddPreferenceModal('communication')"
                        class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        + Add
                    </button>
                </div>
                <div class="space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $preferences->where('category', 'communication'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $preference): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-start justify-between p-3 rounded-xl bg-gray-50">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-sm font-medium text-gray-900"><?php echo e(ucwords(str_replace('_', ' ', $preference->preference_key))); ?></span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">
                                    <?php echo e($preference->preference_value ?? 'Not specified'); ?></p>
                            </div>
                            <div class="flex items-center gap-1">
                                <button
                                    onclick="editPreference(<?php echo e($preference->id); ?>, '<?php echo e($preference->preference_key); ?>', '<?php echo e($preference->preference_value); ?>', <?php echo e($preference->priority); ?>, <?php echo e($preference->is_auto_applied ? 'true' : 'false'); ?>)"
                                    class="p-1.5 rounded-lg hover:bg-gray-200 transition">
                                    <svg class="w-4 h-4 text-gray-500" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <form
                                    action="<?php echo e(route('hotel.guests.destroy-preference', [$guest->id, $preference->id])); ?>"
                                    method="POST" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit"
                                        class="p-1.5 rounded-lg hover:bg-red-100 transition"
                                        onclick="return confirm('Delete this preference?')">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-gray-500 text-center py-4">No communication
                            preferences yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <div id="modal-preference" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <form id="form-preference" method="POST" action="">
                <?php echo csrf_field(); ?>
                <input type="hidden" id="preference-id" name="_method" value="POST">

                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4" id="modal-title">Add
                        Preference</h3>

                    <input type="hidden" name="category" id="pref-category-input" value="room">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Preference
                                Key *</label>
                            <input type="text" name="preference_key" id="pref-key" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g., high_floor, extra_pillow">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Preference
                                Value</label>
                            <textarea name="preference_value" id="pref-value" rows="2"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g., Yes, 2 pillows"></textarea>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-medium text-gray-600 mb-1">Priority</label>
                            <select name="priority" id="pref-priority"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="1">Low</option>
                                <option value="2">Medium</option>
                                <option value="3">High</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_auto_applied" id="pref-auto-apply" value="1"
                                checked class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                            <label for="pref-auto-apply" class="text-sm text-gray-700">
                                Auto-apply to future reservations
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-6 pb-6">
                    <button type="button" onclick="closePreferenceModal()"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                        Save Preference
                    </button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-award-points" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <form action="<?php echo e(route('hotel.guests.award-points', $guest)); ?>" method="POST">
                <?php echo csrf_field(); ?>

                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Award Loyalty Points</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Points to
                                Award *</label>
                            <input type="number" name="points" required min="1"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g., 100">
                        </div>

                        <div>
                            <label
                                class="block text-xs font-medium text-gray-600 mb-1">Reason</label>
                            <textarea name="reason" rows="2"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Reason for awarding points"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-6 pb-6">
                    <button type="button"
                        onclick="document.getElementById('modal-award-points').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700">
                        Award Points
                    </button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-redeem-points" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <form action="<?php echo e(route('hotel.guests.redeem-points', $guest)); ?>" method="POST">
                <?php echo csrf_field(); ?>

                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Redeem Loyalty Points</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Reward Name
                                *</label>
                            <input type="text" name="reward_name" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g., Late Checkout">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Points to
                                Redeem *</label>
                            <input type="number" name="points" required min="1"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g., 200">
                        </div>

                        <div>
                            <label
                                class="block text-xs font-medium text-gray-600 mb-1">Reason</label>
                            <textarea name="reason" rows="2"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Additional details"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-6 pb-6">
                    <button type="button"
                        onclick="document.getElementById('modal-redeem-points').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-amber-600 text-white rounded-xl hover:bg-amber-700">
                        Redeem Points
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            const guestId = <?php echo e($guest->id); ?>;

            function openAddPreferenceModal(category = 'room') {
                document.getElementById('pref-category-input').value = category;
                document.getElementById('modal-title').textContent = 'Add Preference';
                document.getElementById('form-preference').action = "<?php echo e(route('hotel.guests.store-preference', $guest)); ?>";
                document.getElementById('preference-id').value = 'POST';
                document.getElementById('pref-key').value = '';
                document.getElementById('pref-value').value = '';
                document.getElementById('pref-priority').value = '1';
                document.getElementById('pref-auto-apply').checked = true;
                document.getElementById('modal-preference').classList.remove('hidden');
            }

            function editPreference(id, key, value, priority, autoApply) {
                document.getElementById('modal-title').textContent = 'Edit Preference';
                document.getElementById('form-preference').action =
                    "<?php echo e(route('hotel.guests.update-preference', [$guest->id, '__ID__'])); ?>".replace('__ID__', id);
                document.getElementById('preference-id').value = 'PATCH';
                document.getElementById('pref-key').value = key;
                document.getElementById('pref-value').value = value || '';
                document.getElementById('pref-priority').value = priority;
                document.getElementById('pref-auto-apply').checked = autoApply;
                document.getElementById('modal-preference').classList.remove('hidden');
            }

            function closePreferenceModal() {
                document.getElementById('modal-preference').classList.add('hidden');
            }

            function openAwardPointsModal() {
                document.getElementById('modal-award-points').classList.remove('hidden');
            }

            function openRedeemPointsModal() {
                document.getElementById('modal-redeem-points').classList.remove('hidden');
            }

            // Load smart suggestions
            function loadSuggestions() {
                document.getElementById('suggestions-loading').classList.remove('hidden');
                document.getElementById('suggestions-content').classList.add('hidden');

                fetch(`/hotel/guests/${guestId}/suggestions`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            renderSuggestions(data.data.suggestions);
                            renderLoyaltyRecommendations(data.data.loyalty_recommendations);
                        }
                    })
                    .catch(error => {
                        console.error('Error loading suggestions:', error);
                        document.getElementById('suggestions-list').innerHTML =
                            '<p class="text-sm text-red-600">Failed to load suggestions</p>';
                    })
                    .finally(() => {
                        document.getElementById('suggestions-loading').classList.add('hidden');
                        document.getElementById('suggestions-content').classList.remove('hidden');
                    });
            }

            function renderSuggestions(suggestions) {
                const container = document.getElementById('suggestions-list');

                if (!suggestions || suggestions.length === 0) {
                    container.innerHTML =
                        '<p class="text-sm text-gray-600 text-center py-4">No suggestions available yet. More data will be collected after guest stays.</p>';
                    return;
                }

                container.innerHTML = suggestions.map(suggestion => `
                    <div class="p-4 bg-white rounded-lg border border-gray-200">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-semibold text-blue-600 uppercase">${suggestion.category}</span>
                                    <span class="text-xs text-gray-500">•</span>
                                    <span class="text-xs text-gray-600 font-medium">${suggestion.preference_key.replace(/_/g, ' ')}</span>
                                </div>
                                <p class="text-sm text-gray-900 mb-2">${suggestion.preference_value}</p>
                                <div class="flex items-center gap-3 text-xs text-gray-500">
                                    <span>Confidence: ${suggestion.confidence}%</span>
                                    <span>•</span>
                                    <span>Used ${suggestion.frequency} times</span>
                                    <span>•</span>
                                    <span>${suggestion.source.replace(/_/g, ' ')}</span>
                                </div>
                            </div>
                            <form action="/hotel/guests/${guestId}/apply-suggestion" method="POST" class="ml-3">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="category" value="${suggestion.category}">
                                <input type="hidden" name="preference_key" value="${suggestion.preference_key}">
                                <input type="hidden" name="preference_value" value="${suggestion.preference_value}">
                                <button type="submit" class="px-3 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                    Apply
                                </button>
                            </form>
                        </div>
                    </div>
                `).join('');
            }

            function renderLoyaltyRecommendations(recommendations) {
                const rewardsContainer = document.getElementById('loyalty-rewards-list');
                const nextTierContainer = document.getElementById('next-tier-info');

                // Render rewards
                if (recommendations.available_rewards && recommendations.available_rewards.length > 0) {
                    rewardsContainer.innerHTML = recommendations.available_rewards.map(reward => `
                        <div class="p-3 bg-white rounded-lg border border-amber-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">${reward.name}</p>
                                    <p class="text-xs text-gray-500">${reward.points} points</p>
                                </div>
                                <button onclick="redeemReward('${reward.name}', ${reward.points})" 
                                    class="px-3 py-1.5 text-xs bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition">
                                    Redeem
                                </button>
                            </div>
                        </div>
                    `).join('');
                } else {
                    rewardsContainer.innerHTML =
                        '<p class="text-sm text-gray-600 col-span-2 text-center py-4">No rewards available for current VIP level</p>';
                }

                // Render next tier info
                if (recommendations.next_tier_requirements) {
                    const req = recommendations.next_tier_requirements;
                    nextTierContainer.classList.remove('hidden');
                    nextTierContainer.innerHTML = `
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">Next Tier: ${req.level.toUpperCase()}</h4>
                        <div class="flex items-center gap-4 text-xs text-gray-600">
                            <span>Requires ${req.requires_stays} stays OR ${req.requires_points} points</span>
                        </div>
                    `;
                } else {
                    nextTierContainer.classList.add('hidden');
                }
            }

            function redeemReward(name, points) {
                document.querySelector('#modal-redeem-points input[name="reward_name"]').value = name;
                document.querySelector('#modal-redeem-points input[name="points"]').value = points;
                document.getElementById('modal-redeem-points').classList.remove('hidden');
            }

            // Load suggestions on page load
            document.addEventListener('DOMContentLoaded', loadSuggestions);
        </script>
    <?php $__env->stopPush(); ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\guests\preferences.blade.php ENDPATH**/ ?>