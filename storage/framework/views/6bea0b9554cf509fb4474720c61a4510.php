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
     <?php $__env->slot('header', null, []); ?> Channel Manager <?php $__env->endSlot(); ?>

    <?php
        $channelInfo = [
            'bookingcom' => ['name' => 'Booking.com', 'icon' => 'B', 'color' => 'bg-blue-600'],
            'agoda' => ['name' => 'Agoda', 'icon' => 'A', 'color' => 'bg-orange-500'],
            'expedia' => ['name' => 'Expedia', 'icon' => 'E', 'color' => 'bg-yellow-500'],
            'airbnb' => ['name' => 'Airbnb', 'icon' => 'Air', 'color' => 'bg-rose-500'],
            'tripadvisor' => ['name' => 'TripAdvisor', 'icon' => 'TA', 'color' => 'bg-green-600'],
            'direct' => ['name' => 'Direct Booking', 'icon' => 'DB', 'color' => 'bg-purple-600'],
        ];

        $recentLogs = \App\Models\ChannelManagerLog::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
    ?>

    <div x-data="channelManager()" class="space-y-6">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Channel Manager</h1>
                <p class="text-sm text-gray-500">Manage OTA channels and sync settings</p>
            </div>
            <a href="<?php echo e(route('hotel.channels.logs')); ?>"
                class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                View All Logs
            </a>
        </div>

        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php $__currentLoopData = $channels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $channel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $config = $configs->get($channel);
                    $info = $channelInfo[$channel] ?? [
                        'name' => ucfirst($channel),
                        'icon' => '?',
                        'color' => 'bg-gray-500',
                    ];
                    $isConnected = $config && $config->is_active;
                ?>
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="<?php echo e($info['color']); ?> w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold text-sm">
                                <?php echo e($info['icon']); ?>

                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900"><?php echo e($info['name']); ?></h3>
                                <?php if($isConnected): ?>
                                    <span
                                        class="inline-flex items-center gap-1 text-xs text-green-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                        Connected
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400">Not Configured</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if($config): ?>
                        <div class="space-y-2 mb-4 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Property ID</span>
                                <span
                                    class="text-gray-900 font-mono"><?php echo e($config->property_id ?? '-'); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Last Synced</span>
                                <span
                                    class="text-gray-900"><?php echo e($config->last_synced_at?->diffForHumans() ?? 'Never'); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="flex gap-2">
                        <a href="<?php echo e(route('hotel.channels.configure', $channel)); ?>"
                            class="flex-1 px-3 py-2 text-sm text-center border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                            Configure
                        </a>
                        <?php if($isConnected): ?>
                            <button @click="syncChannel('<?php echo e($channel); ?>')"
                                :disabled="syncing === '<?php echo e($channel); ?>'"
                                class="flex-1 px-3 py-2 text-sm text-center bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white rounded-xl flex items-center justify-center gap-2">
                                <svg x-show="syncing !== '<?php echo e($channel); ?>'" class="w-4 h-4" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <svg x-show="syncing === '<?php echo e($channel); ?>'" class="w-4 h-4 animate-spin"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span x-text="syncing === '<?php echo e($channel); ?>' ? 'Syncing...' : 'Sync Now'"></span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Recent Sync Activity</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Channel</th>
                            <th class="px-4 py-3 text-left">Action</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-left">Timestamp</th>
                            <th class="px-4 py-3 text-left">Error</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__empty_1 = true; $__currentLoopData = $recentLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <span
                                        class="font-medium text-gray-900"><?php echo e($channelInfo[$log->channel]['name'] ?? ucfirst($log->channel)); ?></span>
                                </td>
                                <td class="px-4 py-3 text-gray-500">
                                    <?php echo e(str_replace('_', ' ', ucfirst($log->action))); ?>

                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if($log->status === 'success'): ?>
                                        <span
                                            class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-600">Success</span>
                                    <?php elseif($log->status === 'failed'): ?>
                                        <span
                                            class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-600">Failed</span>
                                    <?php else: ?>
                                        <span
                                            class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-600">Partial</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-gray-500">
                                    <?php echo e($log->created_at->format('d M Y H:i')); ?>

                                </td>
                                <td class="px-4 py-3">
                                    <?php if($log->error_message): ?>
                                        <span class="text-xs text-red-500" title="<?php echo e($log->error_message); ?>">
                                            <?php echo e(Str::limit($log->error_message, 30)); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                    No sync activity yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <script>
        window.channelManager = function() {
            return {
                syncing: null,

                async syncChannel(channel) {
                    this.syncing = channel;

                    try {
                        const response = await fetch('<?php echo e(url('hotel/channels')); ?>/' + channel + '/sync', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                                'Accept': 'application/json',
                            },
                        });

                        const data = await response.json();

                        if (data.success) {
                            alert(channel + ' synced successfully!');
                            window.location.reload();
                        } else {
                            alert('Sync failed: ' + (data.message || 'Unknown error'));
                        }
                    } catch (error) {
                        alert('Sync failed: ' + error.message);
                    } finally {
                        this.syncing = null;
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\channels\index.blade.php ENDPATH**/ ?>