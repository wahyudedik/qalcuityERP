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
     <?php $__env->slot('header', null, []); ?> Configure <?php echo e(ucfirst($channel)); ?> <?php $__env->endSlot(); ?>

    <?php
        $channelInfo = [
            'bookingcom' => 'Booking.com',
            'agoda' => 'Agoda',
            'expedia' => 'Expedia',
            'airbnb' => 'Airbnb',
            'tripadvisor' => 'TripAdvisor',
            'direct' => 'Direct Booking',
        ];
        $channelName = $channelInfo[$channel] ?? ucfirst($channel);
        $syncSettings = $config?->sync_settings ?? [];
    ?>

    <div x-data="channelConfig()" class="max-w-2xl mx-auto space-y-6">
        
        <div class="flex items-center gap-4">
            <a href="<?php echo e(route('hotel.channels.index')); ?>"
                class="p-2 rounded-xl border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Configure <?php echo e($channelName); ?></h1>
                <p class="text-sm text-gray-500 dark:text-slate-400">Set up API credentials and sync settings</p>
            </div>
        </div>

        
        <form method="POST" action="<?php echo e(route('hotel.channels.update-config', $channel)); ?>"
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 space-y-6">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-4">API
                    Credentials</h3>

                <div class="space-y-4">
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">API Key
                            *</label>
                        <div class="relative">
                            <input :type="showApiKey ? 'text' : 'password'" name="api_key" required
                                value="<?php echo e(old('api_key', $config?->api_key)); ?>"
                                class="w-full px-3 py-2.5 pr-10 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                            <button type="button" @click="showApiKey = !showApiKey"
                                class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-white">
                                <svg x-show="!showApiKey" class="w-4 h-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showApiKey" class="w-4 h-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">API
                            Secret</label>
                        <div class="relative">
                            <input :type="showApiSecret ? 'text' : 'password'" name="api_secret"
                                value="<?php echo e(old('api_secret', $config?->api_secret)); ?>"
                                class="w-full px-3 py-2.5 pr-10 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                            <button type="button" @click="showApiSecret = !showApiSecret"
                                class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-white">
                                <svg x-show="!showApiSecret" class="w-4 h-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showApiSecret" class="w-4 h-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Property ID
                            *</label>
                        <input type="text" name="property_id" required
                            value="<?php echo e(old('property_id', $config?->property_id)); ?>" placeholder="e.g., 1234567"
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            
            <div class="flex items-center justify-between py-4 border-t border-gray-100 dark:border-white/10">
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Active</p>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Enable sync with this channel</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1"
                        <?php echo e($config?->is_active ?? false ? 'checked' : ''); ?> class="sr-only peer">
                    <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                    </div>
                </label>
            </div>

            
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-4">Sync
                    Settings</h3>

                <div class="space-y-3">
                    <label
                        class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer">
                        <input type="checkbox" name="settings[sync_availability]" value="1"
                            <?php echo e($syncSettings['sync_availability'] ?? true ? 'checked' : ''); ?>

                            class="rounded text-blue-600">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">Sync Availability</p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">Push room availability to the channel
                            </p>
                        </div>
                    </label>

                    <label
                        class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer">
                        <input type="checkbox" name="settings[sync_rates]" value="1"
                            <?php echo e($syncSettings['sync_rates'] ?? true ? 'checked' : ''); ?> class="rounded text-blue-600">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">Sync Rates</p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">Push room rates to the channel</p>
                        </div>
                    </label>

                    <label
                        class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer">
                        <input type="checkbox" name="settings[pull_reservations]" value="1"
                            <?php echo e($syncSettings['pull_reservations'] ?? true ? 'checked' : ''); ?>

                            class="rounded text-blue-600">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">Auto-pull Reservations</p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">Automatically import new reservations
                                from the channel</p>
                        </div>
                    </label>
                </div>
            </div>

            
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-white/10">
                <a href="<?php echo e(route('hotel.channels.index')); ?>"
                    class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Cancel</a>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Save
                    Configuration</button>
            </div>
        </form>

        
        <?php if($config && $config->is_active): ?>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-4">
                    Connection Test</h3>
                <button @click="testConnection()" :disabled="testing"
                    class="w-full px-4 py-3 text-sm bg-gray-100 dark:bg-white/10 hover:bg-gray-200 dark:hover:bg-white/20 disabled:opacity-50 text-gray-700 dark:text-slate-300 rounded-xl flex items-center justify-center gap-2">
                    <svg x-show="!testing" class="w-5 h-5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <svg x-show="testing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                        </path>
                    </svg>
                    <span x-text="testing ? 'Testing...' : 'Test Connection'"></span>
                </button>
                <div x-show="testResult" x-html="testResult" class="mt-4 p-4 rounded-xl text-sm"></div>
            </div>
        <?php endif; ?>
    </div>

    
    <script>
        window.channelConfig = function() {
            return {
                showApiKey: false,
                showApiSecret: false,
                testing: false,
                testResult: null,

                async testConnection() {
                    this.testing = true;
                    this.testResult = null;

                    try {
                        const response = await fetch('<?php echo e(route('hotel.channels.sync', $channel)); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                                'Accept': 'application/json',
                            },
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.testResult =
                                '<div class="bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-300 rounded-lg p-3">✓ Connection successful! ' +
                                (data.message || '') + '</div>';
                        } else {
                            this.testResult =
                                '<div class="bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-300 rounded-lg p-3">✕ Connection failed: ' +
                                (data.message || 'Unknown error') + '</div>';
                        }
                    } catch (error) {
                        this.testResult =
                            '<div class="bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-300 rounded-lg p-3">✕ Connection failed: ' +
                            error.message + '</div>';
                    } finally {
                        this.testing = false;
                    }
                },
            }
        };
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\channels\configure.blade.php ENDPATH**/ ?>