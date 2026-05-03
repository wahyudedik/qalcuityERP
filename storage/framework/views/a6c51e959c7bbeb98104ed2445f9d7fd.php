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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('Laboratory Orders')); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('healthcare.lab-orders.create')); ?>"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>New Lab Order
            </a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                            <i class="fas fa-clock text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Pending</p>
                            <p class="text-2xl font-semibold text-yellow-600"><?php echo e($statistics['pending'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <i class="fas fa-vials text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">In Progress</p>
                            <p class="text-2xl font-semibold text-blue-600"><?php echo e($statistics['in_progress'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <i class="fas fa-check-circle text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Completed</p>
                            <p class="text-2xl font-semibold text-green-600"><?php echo e($statistics['completed'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                            <i class="fas fa-file-medical text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Orders</p>
                            <p class="text-2xl font-semibold text-purple-600"><?php echo e($statistics['total'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 mb-6">
                <form method="GET" action="<?php echo e(route('healthcare.lab-orders.index')); ?>" class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Status</option>
                            <option value="pending" <?php if(request('status') === 'pending'): echo 'selected'; endif; ?>>Pending</option>
                            <option value="in_progress" <?php if(request('status') === 'in_progress'): echo 'selected'; endif; ?>>In Progress</option>
                            <option value="completed" <?php if(request('status') === 'completed'): echo 'selected'; endif; ?>>Completed</option>
                            <option value="cancelled" <?php if(request('status') === 'cancelled'): echo 'selected'; endif; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <select name="priority"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Priority</option>
                            <option value="routine" <?php if(request('priority') === 'routine'): echo 'selected'; endif; ?>>Routine</option>
                            <option value="urgent" <?php if(request('priority') === 'urgent'): echo 'selected'; endif; ?>>Urgent</option>
                            <option value="stat" <?php if(request('priority') === 'stat'): echo 'selected'; endif; ?>>STAT (Emergency)</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Lab Orders Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Order Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Test(s)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ordered At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $labOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo e($order->order_number); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo e($order->patient->name ?? 'N/A'); ?>

                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo e($order->patient->medical_record_number ?? ''); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900"><?php echo e($order->test->name ?? 'N/A'); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    <?php if($order->priority === 'stat'): ?> bg-red-100 text-red-800
                                    <?php elseif($order->priority === 'urgent'): ?> bg-orange-100 text-orange-800
                                    <?php else: ?> bg-blue-100 text-blue-800 <?php endif; ?>">
                                        <?php echo e(ucfirst($order->priority)); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo e($order->ordered_at ? $order->ordered_at->format('d/m/Y H:i') : '-'); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    <?php if($order->status === 'pending'): ?> bg-yellow-100 text-yellow-800
                                    <?php elseif($order->status === 'in_progress'): ?> bg-blue-100 text-blue-800
                                    <?php elseif($order->status === 'completed'): ?> bg-green-100 text-green-800
                                    <?php else: ?> bg-red-100 text-red-800 <?php endif; ?>">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $order->status))); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="<?php echo e(route('healthcare.lab-orders.show', $order)); ?>"
                                        class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('healthcare.lab-orders.edit', $order)); ?>"
                                        class="text-yellow-600 hover:text-yellow-900 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="deleteOrder(<?php echo e($order->id); ?>)"
                                        class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="delete-order-<?php echo e($order->id); ?>"
                                        action="<?php echo e(route('healthcare.lab-orders.destroy', $order)); ?>" method="POST"
                                        class="hidden">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">No lab orders found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if($labOrders->hasPages()): ?>
                    <div class="px-6 py-4 border-t border-gray-200">
                        <?php echo e($labOrders->links()); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function deleteOrder(id) {
                if (confirm('Are you sure you want to delete this lab order?')) {
                    document.getElementById(`delete-order-${id}`).submit();
                }
            }
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\lab-orders\index.blade.php ENDPATH**/ ?>