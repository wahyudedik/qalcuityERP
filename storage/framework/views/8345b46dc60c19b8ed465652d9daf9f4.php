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
     <?php $__env->slot('header', null, []); ?> Sync Logs <?php $__env->endSlot(); ?>

    <?php
        $channelInfo = [
            'bookingcom' => 'Booking.com',
            'agoda' => 'Agoda',
            'expedia' => 'Expedia',
            'airbnb' => 'Airbnb',
            'tripadvisor' => 'TripAdvisor',
            'direct' => 'Direct Booking',
        ];
    ?>

    <div x-data="syncLogs()" class="space-y-6">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Sync Logs</h1>
                <p class="text-sm text-gray-500 dark:text-slate-400">View channel synchronization history</p>
            </div>
            <a href="<?php echo e(route('hotel.channels.index')); ?>"
                class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Channels
            </a>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <select name="channel"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Channels</option>
                    <?php $__currentLoopData = $channels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($ch); ?>" <?php if(request('channel') === $ch): echo 'selected'; endif; ?>>
                            <?php echo e($channelInfo[$ch] ?? ucfirst($ch)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <select name="status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Statuses</option>
                    <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($st); ?>" <?php if(request('status') === $st): echo 'selected'; endif; ?>><?php echo e(ucfirst($st)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <select name="action"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Actions</option>
                    <?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $act): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($act); ?>" <?php if(request('action') === $act): echo 'selected'; endif; ?>>
                            <?php echo e(str_replace('_', ' ', ucfirst($act))); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" placeholder="From"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">

                <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" placeholder="To"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">

                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>

                <a href="<?php echo e(route('hotel.channels.logs')); ?>"
                    class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Clear</a>
            </form>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Timestamp</th>
                            <th class="px-4 py-3 text-left">Channel</th>
                            <th class="px-4 py-3 text-left">Action</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-left">Error Message</th>
                            <th class="px-4 py-3 text-center">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        <?php echo e($log->created_at->format('d M Y')); ?></p>
                                    <p class="text-xs text-gray-500 dark:text-slate-400">
                                        <?php echo e($log->created_at->format('H:i:s')); ?></p>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="font-medium text-gray-900 dark:text-white"><?php echo e($channelInfo[$log->channel] ?? ucfirst($log->channel)); ?></span>
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-slate-400">
                                    <?php echo e(str_replace('_', ' ', ucfirst($log->action))); ?>

                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if($log->status === 'success'): ?>
                                        <span
                                            class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-600 dark:bg-green-500/20 dark:text-green-400">Success</span>
                                    <?php elseif($log->status === 'failed'): ?>
                                        <span
                                            class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400">Failed</span>
                                    <?php else: ?>
                                        <span
                                            class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-600 dark:bg-yellow-500/20 dark:text-yellow-400">Partial</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if($log->error_message): ?>
                                        <span class="text-xs text-red-500" title="<?php echo e($log->error_message); ?>">
                                            <?php echo e(Str::limit($log->error_message, 40)); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if($log->request_data || $log->response_data): ?>
                                        <button @click="showDetails(<?php echo e($log->id); ?>)"
                                            class="text-blue-600 dark:text-blue-400 hover:underline text-xs">
                                            View
                                        </button>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">
                                    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-slate-600 mb-3" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    No logs found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if($logs->hasPages()): ?>
                <div class="px-4 py-3 border-t border-gray-100 dark:border-white/10">
                    <?php echo e($logs->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @click.self="showModal = false">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-hidden"
            @click.stop>
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Log Details</h3>
                <button @click="showModal = false"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <div x-show="selectedLog.request_data" class="mb-4">
                    <h4 class="text-xs font-medium text-gray-500 dark:text-slate-400 uppercase mb-2">Request Data</h4>
                    <pre class="bg-gray-50 dark:bg-[#0f172a] p-4 rounded-xl text-xs overflow-x-auto text-gray-900 dark:text-white"><code x-text="formatJson(selectedLog.request_data)"></code></pre>
                </div>
                <div x-show="selectedLog.response_data">
                    <h4 class="text-xs font-medium text-gray-500 dark:text-slate-400 uppercase mb-2">Response Data</h4>
                    <pre class="bg-gray-50 dark:bg-[#0f172a] p-4 rounded-xl text-xs overflow-x-auto text-gray-900 dark:text-white"><code x-text="formatJson(selectedLog.response_data)"></code></pre>
                </div>
            </div>
        </div>
    </div>

    
    <script>
        <?php
            $logsData = $logs
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'request_data' => $log->request_data,
                        'response_data' => $log->response_data,
                    ];
                })
                ->keyBy('id');
        ?>
        window.syncLogs = function() {
            return {
                showModal: false,
                selectedLog: {},
                logsData: <?php echo e(\Illuminate\Support\Js::from($logsData)); ?>,

                showDetails(logId) {
                    this.selectedLog = this.logsData[logId] || {};
                    this.showModal = true;
                },

                formatJson(data) {
                    if (!data) return '';
                    try {
                        return JSON.stringify(data, null, 2);
                    } catch (e) {
                        return data;
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\channels\logs.blade.php ENDPATH**/ ?>