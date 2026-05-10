<?php if (isset($component)) { $__componentOriginalb1c0bae2b59b5693c063693a98ab2bbf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb1c0bae2b59b5693c063693a98ab2bbf = $attributes; } ?>
<?php $component = App\View\Components\Widget\Statistics::resolve(['stats' => $stats] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('widget.statistics'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Widget\Statistics::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb1c0bae2b59b5693c063693a98ab2bbf)): ?>
<?php $attributes = $__attributesOriginalb1c0bae2b59b5693c063693a98ab2bbf; ?>
<?php unset($__attributesOriginalb1c0bae2b59b5693c063693a98ab2bbf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb1c0bae2b59b5693c063693a98ab2bbf)): ?>
<?php $component = $__componentOriginalb1c0bae2b59b5693c063693a98ab2bbf; ?>
<?php unset($__componentOriginalb1c0bae2b59b5693c063693a98ab2bbf); ?>
<?php endif; ?><?php /**PATH C:\Users\HP\AppData\Local\Temp/lar4D60.blade.php ENDPATH**/ ?>