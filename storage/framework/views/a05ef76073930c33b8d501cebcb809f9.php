<?php if (isset($component)) { $__componentOriginal82f34037d8c83b044121907a067574d3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal82f34037d8c83b044121907a067574d3 = $attributes; } ?>
<?php $component = App\View\Components\Layout\WidgetContainer::resolve(['widgetId' => 'my-widget','title' => 'Test'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.widget-container'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Layout\WidgetContainer::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>Content <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal82f34037d8c83b044121907a067574d3)): ?>
<?php $attributes = $__attributesOriginal82f34037d8c83b044121907a067574d3; ?>
<?php unset($__attributesOriginal82f34037d8c83b044121907a067574d3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal82f34037d8c83b044121907a067574d3)): ?>
<?php $component = $__componentOriginal82f34037d8c83b044121907a067574d3; ?>
<?php unset($__componentOriginal82f34037d8c83b044121907a067574d3); ?>
<?php endif; ?><?php /**PATH C:\Users\HP\AppData\Local\Temp/larE732.blade.php ENDPATH**/ ?>