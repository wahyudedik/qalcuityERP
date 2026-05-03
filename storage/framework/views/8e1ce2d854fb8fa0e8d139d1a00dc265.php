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
     <?php $__env->slot('header', null, []); ?> Billing History <?php $__env->endSlot(); ?>

    <?php
        $patient = auth()->user()->patient;
        $tid = auth()->user()->tenant_id;
    ?>

    <?php if(!$patient): ?>
        <div
            class="bg-red-50 border border-red-200 rounded-2xl p-6 text-center">
            <p class="text-sm text-red-700">Patient profile not found. Please contact reception.</p>
        </div>
    <?php else: ?>
        
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-4 border border-gray-200">
                <p class="text-xs text-gray-500">Total Bills</p>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e($statistics['total_bills'] ?? 0); ?>

                </p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-200">
                <p class="text-xs text-gray-500">Paid</p>
                <p class="text-2xl font-bold text-green-600 mt-1">
                    <?php echo e($statistics['paid_bills'] ?? 0); ?></p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-200">
                <p class="text-xs text-gray-500">Pending</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">
                    <?php echo e($statistics['pending_bills'] ?? 0); ?></p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-200">
                <p class="text-xs text-gray-500">Outstanding</p>
                <p class="text-lg font-bold text-red-600 mt-1">Rp
                    <?php echo e(number_format($statistics['total_outstanding'] ?? 0, 0, ',', '.')); ?></p>
            </div>
        </div>

        
        <div class="bg-white rounded-2xl border border-gray-200 mb-6">
            <div class="p-4">
                <form method="GET" class="flex flex-col sm:flex-row gap-3">
                    <select name="status"
                        class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        <option value="">All Status</option>
                        <option value="paid" <?php if(request('status') === 'paid'): echo 'selected'; endif; ?>>Paid</option>
                        <option value="pending" <?php if(request('status') === 'pending'): echo 'selected'; endif; ?>>Pending</option>
                        <option value="partial" <?php if(request('status') === 'partial'): echo 'selected'; endif; ?>>Partial</option>
                        <option value="overdue" <?php if(request('status') === 'overdue'): echo 'selected'; endif; ?>>Overdue</option>
                    </select>
                    <input type="date" name="from" value="<?php echo e(request('from')); ?>"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <input type="date" name="to" value="<?php echo e(request('to')); ?>"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                        Filter
                    </button>
                </form>
            </div>
        </div>

        
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Invoice No</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Tanggal</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">Description</th>
                            <th class="px-4 py-3 text-right hidden lg:table-cell">Total</th>
                            <th class="px-4 py-3 text-right hidden lg:table-cell">Paid</th>
                            <th class="px-4 py-3 text-right hidden lg:table-cell">Balance</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php
                            $bills = \App\Models\MedicalBill::where('patient_id', $patient->id)
                                ->when(request('status'), function ($q) {
                                    $q->where('status', request('status'));
                                })
                                ->when(request('from'), function ($q) {
                                    $q->whereDate('bill_date', '>=', request('from'));
                                })
                                ->when(request('to'), function ($q) {
                                    $q->whereDate('bill_date', '<=', request('to'));
                                })
                                ->orderBy('bill_date', 'desc')
                                ->paginate(10);
                        ?>
                        <?php $__empty_1 = true; $__currentLoopData = $bills; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <span class="font-mono text-sm font-bold text-blue-600">
                                        <?php echo e($bill->invoice_number ?? '-'); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-900 hidden sm:table-cell">
                                    <?php echo e($bill->bill_date ? \Carbon\Carbon::parse($bill->bill_date)->format('d M Y') : '-'); ?>

                                </td>
                                <td class="px-4 py-3 hidden md:table-cell">
                                    <p class="text-gray-900">
                                        <?php echo e(Str::limit($bill->description ?? 'Medical Services', 40)); ?></p>
                                    <p class="text-xs text-gray-500">
                                        <?php echo e($bill->visit ? $bill->visit->visit_date->format('d M Y') : ''); ?>

                                    </p>
                                </td>
                                <td class="px-4 py-3 text-right hidden lg:table-cell">
                                    <span class="font-medium text-gray-900">
                                        Rp <?php echo e(number_format($bill->total_amount, 0, ',', '.')); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right hidden lg:table-cell">
                                    <span class="text-green-600">
                                        Rp <?php echo e(number_format($bill->paid_amount ?? 0, 0, ',', '.')); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right hidden lg:table-cell">
                                    <span class="font-bold text-red-600">
                                        Rp <?php echo e(number_format($bill->outstanding_balance ?? 0, 0, ',', '.')); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if($bill->status === 'paid'): ?>
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">
                                            Paid
                                        </span>
                                    <?php elseif($bill->status === 'pending'): ?>
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700">
                                            Pending
                                        </span>
                                    <?php elseif($bill->status === 'partial'): ?>
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700">
                                            Partial
                                        </span>
                                    <?php elseif($bill->status === 'overdue'): ?>
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700">
                                            Overdue
                                        </span>
                                    <?php else: ?>
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700">
                                            <?php echo e(ucfirst($bill->status)); ?>

                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="<?php echo e(route('healthcare.portal.billing.show', $bill)); ?>"
                                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"
                                            title="View Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                        </a>
                                        <?php if($bill->status !== 'paid'): ?>
                                            <button onclick="payBill(<?php echo e($bill->id); ?>)"
                                                class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg"
                                                title="Pay Now">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                                    </path>
                                                </svg>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    <p>Belum ada tagihan</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($bills->hasPages()): ?>
                <div class="px-4 py-3 border-t border-gray-200">
                    <?php echo e($bills->links()); ?>

                </div>
            <?php endif; ?>
        </div>

        
        <div
            class="mt-6 bg-blue-50 border border-blue-200 rounded-2xl p-6">
            <h3 class="text-lg font-bold text-blue-900 mb-3">Payment Methods</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-blue-900">Cash</p>
                        <p class="text-xs text-blue-700">Pay at counter</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-blue-900">Credit/Debit Card</p>
                        <p class="text-xs text-blue-700">Visa, Mastercard</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-blue-900">Digital Wallet</p>
                        <p class="text-xs text-blue-700">GoPay, OVO, Dana</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function payBill(id) {
                window.location.href = `/healthcare/portal/billing/${id}/pay`;
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\patient-portal\billing.blade.php ENDPATH**/ ?>