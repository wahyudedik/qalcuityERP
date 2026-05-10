<?php if (isset($component)) { $__componentOriginalf104b4ed2aae1820587a9b456e8c25ab = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf104b4ed2aae1820587a9b456e8c25ab = $attributes; } ?>
<?php $component = App\View\Components\Widget\Chart::resolve(['type' => 'line','data' => $data] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('widget.chart'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Widget\Chart::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf104b4ed2aae1820587a9b456e8c25ab)): ?>
<?php $attributes = $__attributesOriginalf104b4ed2aae1820587a9b456e8c25ab; ?>
<?php unset($__attributesOriginalf104b4ed2aae1820587a9b456e8c25ab); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf104b4ed2aae1820587a9b456e8c25ab)): ?>
<?php $component = $__componentOriginalf104b4ed2aae1820587a9b456e8c25ab; ?>
<?php unset($__componentOriginalf104b4ed2aae1820587a9b456e8c25ab); ?>
<?php endif; ?><?php /**PATH C:\Users\HP\AppData\Local\Temp/lar1079.blade.php ENDPATH**/ ?>