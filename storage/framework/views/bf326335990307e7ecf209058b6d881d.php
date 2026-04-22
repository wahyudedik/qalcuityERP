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
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight"><?php echo e(__('HL7 Messages')); ?></h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-md p-3"><i
                                class="fas fa-envelope text-blue-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Messages</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo e($statistics['total']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-md p-3"><i
                                class="fas fa-paper-plane text-green-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Sent</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo e($statistics['sent']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-md p-3"><i
                                class="fas fa-inbox text-purple-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Received</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo e($statistics['received']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3"><i
                                class="fas fa-clock text-yellow-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Pending</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo e($statistics['pending']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-100 rounded-md p-3"><i
                                class="fas fa-exclamation-triangle text-red-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Errors</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo e($statistics['errors']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="<?php echo e(route('healthcare.hl7.index')); ?>"
                        class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Direction</label>
                            <select name="direction" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">All Directions</option>
                                <option value="inbound" <?php echo e(request('direction') === 'inbound' ? 'selected' : ''); ?>>
                                    Inbound</option>
                                <option value="outbound" <?php echo e(request('direction') === 'outbound' ? 'selected' : ''); ?>>
                                    Outbound</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>>Pending
                                </option>
                                <option value="sent" <?php echo e(request('status') === 'sent' ? 'selected' : ''); ?>>Sent
                                </option>
                                <option value="received" <?php echo e(request('status') === 'received' ? 'selected' : ''); ?>>
                                    Received</option>
                                <option value="error" <?php echo e(request('status') === 'error' ? 'selected' : ''); ?>>Error
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Message Type</label>
                            <select name="message_type" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">All Types</option>
                                <option value="ADT" <?php echo e(request('message_type') === 'ADT' ? 'selected' : ''); ?>>ADT
                                    (Admission)</option>
                                <option value="ORM" <?php echo e(request('message_type') === 'ORM' ? 'selected' : ''); ?>>ORM
                                    (Order)</option>
                                <option value="ORU" <?php echo e(request('message_type') === 'ORU' ? 'selected' : ''); ?>>ORU
                                    (Result)</option>
                                <option value="SIU" <?php echo e(request('message_type') === 'SIU' ? 'selected' : ''); ?>>SIU
                                    (Scheduling)</option>
                                <option value="DFT" <?php echo e(request('message_type') === 'DFT' ? 'selected' : ''); ?>>DFT
                                    (Billing)</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit"
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                    class="fas fa-search mr-2"></i>Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">HL7 Message Log</h3>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Message
                                        ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Direction</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Receiving App</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Retry
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-mono font-medium text-gray-900">
                                                #<?php echo e($message->id); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if($message->direction === 'inbound'): ?>
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800"><i
                                                        class="fas fa-arrow-down mr-1"></i>Inbound</span>
                                            <?php else: ?>
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800"><i
                                                        class="fas fa-arrow-up mr-1"></i>Outbound</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800"><?php echo e($message->message_type); ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <?php echo e($message->receiving_app ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <?php echo e($message->created_at->format('d/m/Y H:i')); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if($message->status === 'sent' || $message->status === 'received'): ?>
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800"><i
                                                        class="fas fa-check mr-1"></i><?php echo e(ucfirst($message->status)); ?></span>
                                            <?php elseif($message->status === 'pending'): ?>
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800"><i
                                                        class="fas fa-clock mr-1"></i>Pending</span>
                                            <?php else: ?>
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800"><i
                                                        class="fas fa-times mr-1"></i>Error</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <?php echo e($message->retry_count); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="<?php echo e(route('healthcare.hl7.show', $message)); ?>"
                                                class="text-blue-600 hover:text-blue-900 mr-3"><i
                                                    class="fas fa-eye"></i></a>
                                            <?php if($message->status === 'error'): ?>
                                                <button onclick="retryMessage(<?php echo e($message->id); ?>)"
                                                    class="text-yellow-600 hover:text-yellow-900 mr-3"><i
                                                        class="fas fa-redo"></i></button>
                                            <?php endif; ?>
                                            <button onclick="deleteMessage(<?php echo e($message->id); ?>)"
                                                class="text-red-600 hover:text-red-900"><i
                                                    class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">No HL7 messages
                                            found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <?php echo e($messages->links()); ?>

            </div>
        </div>
    </div>

    <script>
        function retryMessage(id) {
            if (confirm('Retry sending this message?')) {
                fetch(`<?php echo e(route('healthcare.hl7.retry', '')); ?>/${id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        location.reload();
                    })
                    .catch(error => alert('Retry failed'));
            }
        }

        function deleteMessage(id) {
            if (confirm('Are you sure you want to delete this message?')) {
                fetch(`<?php echo e(route('healthcare.hl7.destroy', '')); ?>/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        location.reload();
                    })
                    .catch(error => alert('Delete failed'));
            }
        }
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\hl7\index.blade.php ENDPATH**/ ?>