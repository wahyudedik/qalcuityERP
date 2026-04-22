<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['topic', 'field' => null]));

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

foreach (array_filter((['topic', 'field' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>



<div class="help-field-wrapper" data-help-topic="<?php echo e($topic); ?>" <?php echo e($attributes); ?>>
    <div class="flex items-center gap-2 mb-1">
        <?php echo e($slot); ?>


        <?php if($topic): ?>
            <?php if (isset($component)) { $__componentOriginalf80c6e4882377f1e95404ca80788f6ed = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf80c6e4882377f1e95404ca80788f6ed = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-icon','data' => ['topic' => $topic,'class' => 'flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['topic' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($topic),'class' => 'flex-shrink-0']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf80c6e4882377f1e95404ca80788f6ed)): ?>
<?php $attributes = $__attributesOriginalf80c6e4882377f1e95404ca80788f6ed; ?>
<?php unset($__attributesOriginalf80c6e4882377f1e95404ca80788f6ed); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf80c6e4882377f1e95404ca80788f6ed)): ?>
<?php $component = $__componentOriginalf80c6e4882377f1e95404ca80788f6ed; ?>
<?php unset($__componentOriginalf80c6e4882377f1e95404ca80788f6ed); ?>
<?php endif; ?>
        <?php endif; ?>
    </div>

    <?php if($field): ?>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" id="<?php echo e($field); ?>-help">
            Klik ikon <span class="text-blue-600">❓</span> untuk bantuan
        </p>
    <?php endif; ?>
</div>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\help-field.blade.php ENDPATH**/ ?>