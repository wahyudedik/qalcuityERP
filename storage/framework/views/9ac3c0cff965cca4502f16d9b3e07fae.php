

<?php $__env->startSection('title', 'Kitchen Display System — KOT'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Kitchen Display System</h1>
            <p class="mt-1 text-sm text-gray-600">Manajemen pesanan dapur secara real-time</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-yellow-50 rounded-lg shadow p-4 border-l-4 border-yellow-500">
                <div class="text-sm text-yellow-600">Menunggu</div>
                <div class="text-2xl font-bold text-yellow-700"><?php echo e($stats['pending'] ?? 0); ?></div>
            </div>
            <div class="bg-blue-50 rounded-lg shadow p-4 border-l-4 border-blue-500">
                <div class="text-sm text-blue-600">Diproses</div>
                <div class="text-2xl font-bold text-blue-700"><?php echo e($stats['preparing'] ?? 0); ?></div>
            </div>
            <div class="bg-green-50 rounded-lg shadow p-4 border-l-4 border-green-500">
                <div class="text-sm text-green-600">Siap</div>
                <div class="text-2xl font-bold text-green-700"><?php echo e($stats['ready'] ?? 0); ?></div>
            </div>
            <div class="bg-red-50 rounded-lg shadow p-4 border-l-4 border-red-500">
                <div class="text-sm text-red-600">Terlambat</div>
                <div class="text-2xl font-bold text-red-700"><?php echo e($stats['overdue'] ?? 0); ?></div>
            </div>
            <div class="bg-purple-50 rounded-lg shadow p-4 border-l-4 border-purple-500">
                <div class="text-sm text-purple-600">Rata-rata Waktu</div>
                <div class="text-2xl font-bold text-purple-700"><?php echo e(round($stats['avg_prep_time'] ?? 0)); ?>m</div>
            </div>
        </div>

        <!-- Station Filter -->
        <div class="mb-6 flex flex-wrap gap-2">
            <?php $__currentLoopData = $stations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('fnb.kds.index', ['station' => $s === 'all' ? null : $s])); ?>"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo e(($station ?? 'all') === $s ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'); ?>">
                    <?php echo e(ucfirst($s === 'all' ? 'Semua' : $s)); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <?php if(session('success')): ?>
            <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 rounded text-green-700">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <!-- Tickets Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden <?php echo e($ticket->isOverdue() ? 'border-4 border-red-500' : 'border border-gray-200'); ?>"
                    data-ticket-id="<?php echo e($ticket->id); ?>">
                    <!-- Header -->
                    <div
                        class="p-4 <?php echo e($ticket->priority === 'vip' ? 'bg-purple-100' : ($ticket->priority === 'rush' ? 'bg-red-100' : 'bg-gray-50')); ?>">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="text-lg font-bold text-gray-900"><?php echo e($ticket->ticket_number); ?></div>
                                <div class="text-xs text-gray-600">Pesanan #<?php echo e($ticket->fb_order_id); ?></div>
                            </div>
                            <span
                                class="px-2 py-1 text-xs rounded-full <?php echo e($ticket->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800'); ?>">
                                <?php switch($ticket->status):
                                    case ('pending'): ?> Menunggu <?php break; ?>
                                    <?php case ('preparing'): ?> Diproses <?php break; ?>
                                    <?php case ('ready'): ?> Siap <?php break; ?>
                                    <?php case ('served'): ?> Disajikan <?php break; ?>
                                    <?php case ('cancelled'): ?> Dibatalkan <?php break; ?>
                                    <?php default: ?> <?php echo e(ucfirst($ticket->status)); ?>

                                <?php endswitch; ?>
                            </span>
                        </div>
                        <?php if($ticket->priority !== 'normal'): ?>
                            <span class="inline-block mt-1 px-2 py-0.5 text-xs rounded bg-red-600 text-white">
                                <?php echo e($ticket->priority === 'rush' ? 'SEGERA' : 'VIP'); ?>

                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Items -->
                    <div class="p-4">
                        <div class="space-y-2">
                            <?php $__currentLoopData = $ticket->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-medium text-gray-900"><?php echo e($item->quantity); ?>x <?php echo e($item->menuItem?->name ?? $item->item_name ?? 'Item tidak diketahui'); ?></div>
                                        <?php if($item->special_instructions): ?>
                                            <div class="text-xs text-gray-500 italic"><?php echo e($item->special_instructions); ?></div>
                                        <?php endif; ?>
                                        <?php if(!empty($item->modifiers)): ?>
                                            <div class="text-xs text-blue-600">
                                                <?php echo e(implode(', ', $item->modifiers)); ?>

                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if($item->is_completed): ?>
                                        <span class="text-green-500 text-xs">✓</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                        <?php if($ticket->chef_notes): ?>
                            <div class="mt-3 p-2 bg-yellow-50 rounded text-xs text-yellow-800">
                                <strong>Catatan Chef:</strong> <?php echo e($ticket->chef_notes); ?>

                            </div>
                        <?php endif; ?>

                        <!-- Timer -->
                        <?php if($ticket->started_at): ?>
                            <div class="mt-3 flex items-center text-sm">
                                <svg class="w-4 h-4 mr-1 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="<?php echo e($ticket->isOverdue() ? 'text-red-600 font-bold' : 'text-gray-600'); ?>">
                                    <?php echo e($ticket->getElapsedTime()); ?>m / <?php echo e($ticket->estimated_time ?? '?'); ?>m
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Actions -->
                    <div class="p-4 border-t border-gray-200 bg-gray-50 flex space-x-2">
                        <?php if($ticket->status === 'pending'): ?>
                            <button onclick="startTicket(<?php echo e($ticket->id); ?>)"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm font-medium transition-colors min-h-[44px]">
                                Mulai Proses
                            </button>
                        <?php elseif($ticket->status === 'preparing'): ?>
                            <button onclick="completeTicket(<?php echo e($ticket->id); ?>)"
                                class="flex-1 bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm font-medium transition-colors min-h-[44px]">
                                Tandai Siap
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-full text-center py-12 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p>Tidak ada tiket aktif saat ini</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function startTicket(ticketId) {
            fetch(`/fnb/kds/tickets/${ticketId}/start`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'Accept': 'application/json',
                }
            }).then(res => res.json()).then(() => location.reload()).catch(() => location.reload());
        }

        function completeTicket(ticketId) {
            fetch(`/fnb/kds/tickets/${ticketId}/complete`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'Accept': 'application/json',
                }
            }).then(res => res.json()).then(() => location.reload()).catch(() => location.reload());
        }

        // Auto-refresh setiap 30 detik
        setTimeout(() => location.reload(), 30000);
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\fnb\kds\index.blade.php ENDPATH**/ ?>