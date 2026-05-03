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
     <?php $__env->slot('header', null, []); ?> Permintaan Stok <?php $__env->endSlot(); ?>

    <?php $tid = auth()->user()->tenant_id; ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <?php
            $totalRequests = \App\Models\StockRequest::where('tenant_id', $tid)->count();
            $pendingRequests = \App\Models\StockRequest::where('tenant_id', $tid)->where('status', 'pending')->count();
            $approvedRequests = \App\Models\StockRequest::where('tenant_id', $tid)
                ->where('status', 'approved')
                ->count();
            $fulfilledToday = \App\Models\StockRequest::where('tenant_id', $tid)
                ->where('status', 'fulfilled')
                ->whereDate('fulfilled_at', today())
                ->count();
        ?>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Requests</p>
            <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e(number_format($totalRequests)); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-amber-600 mt-1"><?php echo e($pendingRequests); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Disetujui</p>
            <p class="text-2xl font-bold text-blue-600 mt-1"><?php echo e($approvedRequests); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Fulfilled Hari Ini</p>
            <p class="text-2xl font-bold text-green-600 mt-1"><?php echo e($fulfilledToday); ?></p>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Request ID</th>
                        <th class="px-4 py-3 text-left">Department</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Items</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Requested By</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Priority</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $requests ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span
                                    class="font-mono text-sm font-bold text-blue-600"><?php echo e($request->request_number ?? '-'); ?></span>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-purple-100 text-purple-700">
                                    <?php echo e($request->department ?? '-'); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <p class="text-gray-900"><?php echo e(count($request->items ?? [])); ?> items</p>
                                <p class="text-xs text-gray-500">
                                    <?php echo e($request->items ? collect($request->items)->pluck('name')->take(2)->join(', ') : '-'); ?>

                                </p>
                            </td>
                            <td class="px-4 py-3 text-gray-600 hidden lg:table-cell">
                                <?php echo e($request->requestedBy ? $request->requestedBy->name : '-'); ?></td>
                            <td class="px-4 py-3 hidden sm:table-cell">
                                <p class="text-gray-900">
                                    <?php echo e($request->request_date ? \Carbon\Carbon::parse($request->request_date)->format('d M Y') : '-'); ?>

                                </p>
                                <p class="text-xs text-gray-500">
                                    <?php echo e($request->request_date ? \Carbon\Carbon::parse($request->request_date)->format('H:i') : '-'); ?>

                                </p>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                <?php if($request->priority === 'urgent'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700">Urgent</span>
                                <?php elseif($request->priority === 'high'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-orange-100 text-orange-700">High</span>
                                <?php else: ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700">Normal</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if($request->status === 'pending'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700">Pending</span>
                                <?php elseif($request->status === 'approved'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700">Approved</span>
                                <?php elseif($request->status === 'fulfilled'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Fulfilled</span>
                                <?php elseif($request->status === 'rejected'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="<?php echo e(route('healthcare.inventory.requests.show', $request)); ?>"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"
                                        title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </a>
                                    <?php if($request->status === 'pending'): ?>
                                        <button onclick="approveRequest(<?php echo e($request->id); ?>)"
                                            class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg"
                                            title="Approve">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                <p>Belum ada permintaan stok</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(isset($requests) && $requests->hasPages()): ?>
            <div class="px-4 py-3 border-t border-gray-200">
                <?php echo e($requests->links()); ?>

            </div>
        <?php endif; ?>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function approveRequest(id) {
                if (confirm('Setujui permintaan ini?')) {
                    fetch(`/healthcare/inventory/requests/${id}/approve`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        }
                    }).then(() => location.reload());
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\healthcare-inventory\requests.blade.php ENDPATH**/ ?>