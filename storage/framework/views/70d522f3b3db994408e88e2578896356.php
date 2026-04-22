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
     <?php $__env->slot('header', null, []); ?> Edit Claim BPJS - <?php echo e($bpjsClaim->claim_number); ?> <?php $__env->endSlot(); ?>

    
    <?php if (isset($component)) { $__componentOriginal360d002b1b676b6f84d43220f22129e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal360d002b1b676b6f84d43220f22129e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumbs','data' => ['items' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Claim BPJS', 'url' => route('healthcare.bpjs-claims.index')],
        ['label' => 'Edit Claim'],
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Claim BPJS', 'url' => route('healthcare.bpjs-claims.index')],
        ['label' => 'Edit Claim'],
    ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $attributes = $__attributesOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__attributesOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $component = $__componentOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__componentOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>

    <div class="py-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="<?php echo e(route('healthcare.bpjs-claims.update', $bpjsClaim)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="bpjs_number" class="block text-sm font-medium text-gray-700">BPJS Number
                                    *</label>
                                <input type="text" name="bpjs_number" required
                                    value="<?php echo e(old('bpjs_number', $bpjsClaim->bpjs_number)); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="pending"
                                        <?php echo e(old('status', $bpjsClaim->status) === 'pending' ? 'selected' : ''); ?>>Pending
                                    </option>
                                    <option value="submitted"
                                        <?php echo e(old('status', $bpjsClaim->status) === 'submitted' ? 'selected' : ''); ?>>
                                        Submitted</option>
                                    <option value="approved"
                                        <?php echo e(old('status', $bpjsClaim->status) === 'approved' ? 'selected' : ''); ?>>
                                        Approved</option>
                                    <option value="rejected"
                                        <?php echo e(old('status', $bpjsClaim->status) === 'rejected' ? 'selected' : ''); ?>>
                                        Rejected</option>
                                    <option value="paid"
                                        <?php echo e(old('status', $bpjsClaim->status) === 'paid' ? 'selected' : ''); ?>>Paid
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="claim_amount" class="block text-sm font-medium text-gray-700">Claim Amount
                                    (Rp) *</label>
                                <input type="number" name="claim_amount" required
                                    value="<?php echo e(old('claim_amount', $bpjsClaim->claim_amount)); ?>" min="0"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="approved_amount" class="block text-sm font-medium text-gray-700">Approved
                                    Amount (Rp)</label>
                                <input type="number" name="approved_amount"
                                    value="<?php echo e(old('approved_amount', $bpjsClaim->approved_amount)); ?>" min="0"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="diagnosis_code" class="block text-sm font-medium text-gray-700">Diagnosis
                                    Code (ICD-10)</label>
                                <input type="text" name="diagnosis_code"
                                    value="<?php echo e(old('diagnosis_code', $bpjsClaim->diagnosis_code)); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="submission_date" class="block text-sm font-medium text-gray-700">Submission
                                    Date</label>
                                <input type="date" name="submission_date"
                                    value="<?php echo e(old('submission_date', $bpjsClaim->submission_date ? $bpjsClaim->submission_date->format('Y-m-d') : '')); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="treatment_description" class="block text-sm font-medium text-gray-700">Treatment
                                Description *</label>
                            <textarea name="treatment_description" required rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo e(old('treatment_description', $bpjsClaim->treatment_description)); ?></textarea>
                        </div>

                        <div>
                            <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Rejection
                                Reason</label>
                            <textarea name="rejection_reason" rows="2"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="If rejected, provide reason..."><?php echo e(old('rejection_reason', $bpjsClaim->rejection_reason)); ?></textarea>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Additional
                                Notes</label>
                            <textarea name="notes" rows="2"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo e(old('notes', $bpjsClaim->notes)); ?></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="<?php echo e(route('healthcare.bpjs-claims.index')); ?>"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Update Claim</button>
                    </div>
                </form>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\bpjs-claims\edit.blade.php ENDPATH**/ ?>