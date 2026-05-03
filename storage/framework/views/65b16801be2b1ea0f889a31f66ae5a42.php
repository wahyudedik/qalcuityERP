

<?php $__env->startSection('title', 'Terlalu Banyak Permintaan'); ?>
<?php $__env->startSection('code', '429'); ?>
<?php $__env->startSection('icon', '🚦'); ?>
<?php $__env->startSection('icon-bg', 'bg-orange-500/10'); ?>
<?php $__env->startSection('heading', 'Terlalu Banyak Permintaan'); ?>
<?php $__env->startSection('message', 'Anda mengirim terlalu banyak permintaan dalam waktu singkat. Tunggu beberapa saat lalu coba lagi.'); ?>

<?php $__env->startSection('extra'); ?>
<div class="mt-4 bg-orange-50 border border-orange-200 rounded-xl p-4 text-sm text-orange-700">
    Jika ini terkait kuota AI, Anda bisa upgrade paket di menu <a href="<?php echo e(url('/subscription')); ?>" class="underline font-medium">Langganan</a>.
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('errors.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\errors\429.blade.php ENDPATH**/ ?>