<?php if (isset($component)) { $__componentOriginalc3b5095756d720f8e08fe4b04d8995a8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc3b5095756d720f8e08fe4b04d8995a8 = $attributes; } ?>
<?php $component = App\View\Components\Widget\QuickActions::resolve(['actions' => $actions] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('widget.quick-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Widget\QuickActions::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc3b5095756d720f8e08fe4b04d8995a8)): ?>
<?php $attributes = $__attributesOriginalc3b5095756d720f8e08fe4b04d8995a8; ?>
<?php unset($__attributesOriginalc3b5095756d720f8e08fe4b04d8995a8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc3b5095756d720f8e08fe4b04d8995a8)): ?>
<?php $component = $__componentOriginalc3b5095756d720f8e08fe4b04d8995a8; ?>
<?php unset($__componentOriginalc3b5095756d720f8e08fe4b04d8995a8); ?>
<?php endif; ?><?php /**PATH C:\Users\HP\AppData\Local\Temp/lar2C33.blade.php ENDPATH**/ ?>