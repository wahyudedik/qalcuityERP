<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['totalSteps', 'draftKey' => null, 'showProgress' => true, 'allowStepJump' => false]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['totalSteps', 'draftKey' => null, 'showProgress' => true, 'allowStepJump' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>



<?php
    $draftKeyAttr = $draftKey ? "data-draft-key=\"{$draftKey}\"" : '';
?>

<form
    <?php echo e($attributes->merge(['class' => 'form-wizard', 'data-wizard' => '', 'data-steps' => $totalSteps, $draftKeyAttr => ''])); ?>>
    <?php echo csrf_field(); ?>

    
    <?php if($showProgress): ?>
        <div class="wizard-progress-container mb-6">
            <div class="flex items-center justify-between">
                <?php for($i = 1; $i <= $totalSteps; $i++): ?>
                    <div class="flex flex-col items-center flex-1">
                        <div class="wizard-step-indicator w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300"
                            data-step-indicator="<?php echo e($i); ?>">
                            <span class="step-number text-sm font-semibold"><?php echo e($i); ?></span>
                            <svg class="step-check w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="step-label mt-2 text-xs text-center text-gray-600">
                            <?php echo e($slot->where('number', $i)->first()?->attributes['title'] ?? "Step {$i}"); ?>

                        </div>
                    </div>

                    <?php if($i < $totalSteps): ?>
                        <div class="wizard-step-connector flex-1 h-0.5 bg-gray-200 mx-2 mt-[-20px] transition-all duration-300"
                            data-connector="<?php echo e($i); ?>">
                        </div>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        </div>
    <?php endif; ?>

    
    <div class="wizard-steps-container">
        <?php echo e($slot); ?>

    </div>

    
    <?php if(isset($navigation)): ?>
        <div class="wizard-navigation mt-6 pt-6 border-t border-gray-200">
            <?php echo e($navigation); ?>

        </div>
    <?php else: ?>
        <?php if (isset($component)) { $__componentOriginal668448a62f4894b5ee1a513c42f0d6f5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal668448a62f4894b5ee1a513c42f0d6f5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.wizard-navigation','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('wizard-navigation'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal668448a62f4894b5ee1a513c42f0d6f5)): ?>
<?php $attributes = $__attributesOriginal668448a62f4894b5ee1a513c42f0d6f5; ?>
<?php unset($__attributesOriginal668448a62f4894b5ee1a513c42f0d6f5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal668448a62f4894b5ee1a513c42f0d6f5)): ?>
<?php $component = $__componentOriginal668448a62f4894b5ee1a513c42f0d6f5; ?>
<?php unset($__componentOriginal668448a62f4894b5ee1a513c42f0d6f5); ?>
<?php endif; ?>
    <?php endif; ?>
</form>

<?php $__env->startPush('styles'); ?>
    <style>
        /* Wizard Progress Bar Styles */
        .wizard-step-indicator {
            @apply border-gray-300 bg-white text-gray-600;
        }

        .wizard-step-indicator.active {
            @apply border-blue-500 bg-blue-500 text-white;
        }

        .wizard-step-indicator.completed {
            @apply border-green-500 bg-green-500 text-white;
        }

        .wizard-step-connector.completed {
            @apply bg-green-500;
        }

        .wizard-error {
            @apply border-red-500 focus:ring-red-500;
        }

        .wizard-error-message {
            @apply text-red-500 text-xs mt-1;
        }

        .wizard-save-indicator {
            @apply fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg opacity-0 transition-opacity duration-300 z-50;
        }

        .wizard-save-indicator.show {
            @apply opacity-100;
        }

        /* Step transitions */
        [data-step] {
            @apply transition-all duration-300;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .step-label {
                @apply text-[10px];
            }

            .wizard-step-indicator {
                @apply w-8 h-8;
            }
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        // Initialize wizard when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('[data-wizard]');
            if (form && !form._wizardInitialized) {
                new FormWizard(form, {
                    allowStepJump: <?php echo e($allowStepJump ? 'true' : 'false'); ?>,
                    enableAutoSave: true,
                    autoSaveInterval: 30000
                });
                form._wizardInitialized = true;
            }
        });
    </script>
<?php $__env->stopPush(); ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\form-wizard.blade.php ENDPATH**/ ?>