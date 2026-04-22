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
     <?php $__env->slot('header', null, []); ?> Detail Claim Asuransi - <?php echo e($insuranceClaim->claim_number); ?> <?php $__env->endSlot(); ?>

    
    <?php if (isset($component)) { $__componentOriginal360d002b1b676b6f84d43220f22129e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal360d002b1b676b6f84d43220f22129e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumbs','data' => ['items' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Claim Asuransi', 'url' => route('healthcare.insurance-claims.index')],
        ['label' => 'Detail Claim'],
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
        ['label' => 'Claim Asuransi', 'url' => route('healthcare.insurance-claims.index')],
        ['label' => 'Detail Claim'],
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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-info-circle mr-2 text-blue-600"></i>Claim Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Claim Number</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900"><?php echo e($insuranceClaim->claim_number); ?>

                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Patient</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($insuranceClaim->patient->name ?? 'N/A'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Insurance Provider</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($insuranceClaim->insurance_provider); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Policy Number</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($insuranceClaim->policy_number); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Diagnosis Code</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($insuranceClaim->diagnosis_code ?? '-'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full <?php echo e($insuranceClaim->status === 'approved' ? 'bg-green-100 text-green-800' : ($insuranceClaim->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')); ?>"><?php echo e(ucfirst($insuranceClaim->status)); ?></span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-money-bill-wave mr-2 text-green-600"></i>Financial Details</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Claim Amount</dt>
                            <dd class="mt-1 text-2xl font-bold text-gray-900">Rp
                                <?php echo e(number_format($insuranceClaim->claim_amount, 0, ',', '.')); ?></dd>
                        </div>
                        <?php if($insuranceClaim->approved_amount): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Approved Amount</dt>
                                <dd class="mt-1 text-2xl font-bold text-green-600">Rp
                                    <?php echo e(number_format($insuranceClaim->approved_amount, 0, ',', '.')); ?></dd>
                            </div>
                        <?php endif; ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Submitted At</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo e($insuranceClaim->submitted_at ? $insuranceClaim->submitted_at->format('d/m/Y') : '-'); ?>

                            </dd>
                        </div>
                        <?php if($insuranceClaim->processed_at): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Processed At</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <?php echo e($insuranceClaim->processed_at->format('d/m/Y')); ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                    <?php if($insuranceClaim->description): ?>
                        <div class="mt-4 pt-4 border-t">
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-700 whitespace-pre-line">
                                <?php echo e($insuranceClaim->description); ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if($insuranceClaim->rejection_reason): ?>
                        <div class="mt-4 pt-4 border-t">
                            <dt class="text-sm font-medium text-gray-500">Rejection Reason</dt>
                            <dd class="mt-1 text-sm text-red-600 whitespace-pre-line">
                                <?php echo e($insuranceClaim->rejection_reason); ?></dd>
                        </div>
                    <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\insurance-claims\show.blade.php ENDPATH**/ ?>