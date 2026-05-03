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
     <?php $__env->slot('header', null, []); ?> Tiket — <?php echo e($ticket->ticket_number ?? '#' . $ticket->id); ?> <?php $__env->endSlot(); ?>

    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <a href="<?php echo e(route('customer-portal.tickets.index')); ?>"
            class="hover:text-blue-600">Tiket</a>
        <span>/</span>
        <span class="text-gray-900"><?php echo e($ticket->ticket_number ?? '#' . $ticket->id); ?></span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-4">
            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-900"><?php echo e($ticket->subject); ?></h3>
                    <?php
                        $tc = match ($ticket->status) {
                            'open' => 'amber',
                            'in_progress' => 'blue',
                            'resolved' => 'green',
                            'closed' => 'gray',
                            default => 'gray',
                        };
                    ?>
                    <span
                        class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($tc); ?>-100 text-<?php echo e($tc); ?>-700 $tc }}-500/20 $tc }}-400"><?php echo e(ucfirst(str_replace('_', ' ', $ticket->status))); ?></span>
                </div>
                <p class="text-sm text-gray-700 whitespace-pre-line"><?php echo e($ticket->description); ?></p>
                <p class="text-xs text-gray-400 mt-3">
                    <?php echo e($ticket->created_at?->format('d/m/Y H:i')); ?></p>
            </div>

            
            <?php $__currentLoopData = $ticket->replies ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div
                    class="bg-white rounded-2xl border border-gray-200 p-6 <?php echo e($reply->is_internal ? 'opacity-50' : ''); ?>">
                    <div class="flex items-center gap-2 mb-2">
                        <span
                            class="text-sm font-medium text-gray-900"><?php echo e($reply->user?->name ?? 'Anda'); ?></span>
                        <?php if($reply->user && $reply->user->role !== 'customer'): ?>
                            <span
                                class="px-1.5 py-0.5 text-xs rounded bg-blue-100 text-blue-700">Staff</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-sm text-gray-700 whitespace-pre-line"><?php echo e($reply->body); ?></p>
                    <p class="text-xs text-gray-400 mt-2">
                        <?php echo e($reply->created_at?->format('d/m/Y H:i')); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            
            <?php if(!in_array($ticket->status, ['closed'])): ?>
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <form method="POST" action="<?php echo e(route('customer-portal.tickets.reply', $ticket)); ?>">
                        <?php echo csrf_field(); ?>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Balas</label>
                        <textarea name="message" required rows="3"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 mb-3"
                            placeholder="Tulis balasan..."></textarea>
                        <button type="submit"
                            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Kirim
                            Balasan</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        
        <div>
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Info Tiket</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-xs text-gray-500">No. Tiket</p>
                        <p class="font-medium text-gray-900">
                            <?php echo e($ticket->ticket_number ?? '#' . $ticket->id); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Prioritas</p>
                        <?php
                            $pc = match ($ticket->priority ?? 'medium') {
                                'urgent' => 'red',
                                'high' => 'orange',
                                'medium' => 'blue',
                                'low' => 'gray',
                                default => 'gray',
                            };
                        ?>
                        <span
                            class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($pc); ?>-100 text-<?php echo e($pc); ?>-700 $pc }}-500/20 $pc }}-400"><?php echo e(ucfirst($ticket->priority ?? 'medium')); ?></span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Kategori</p>
                        <p class="font-medium text-gray-900">
                            <?php echo e(ucfirst($ticket->category ?? 'general')); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Dibuat</p>
                        <p class="font-medium text-gray-900">
                            <?php echo e($ticket->created_at?->format('d/m/Y H:i')); ?></p>
                    </div>
                </div>
            </div>
        </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\customer-portal\tickets\show.blade.php ENDPATH**/ ?>