

<?php $__env->startSection('title', 'Sesi Kedaluwarsa'); ?>
<?php $__env->startSection('code', '419'); ?>
<?php $__env->startSection('icon', '⏰'); ?>
<?php $__env->startSection('icon-bg', 'bg-amber-500/10'); ?>
<?php $__env->startSection('heading', 'Sesi Kedaluwarsa'); ?>
<?php $__env->startSection('message', 'Sesi Anda telah berakhir karena tidak aktif terlalu lama. Silakan muat ulang halaman dan coba lagi.'); ?>

<?php $__env->startSection('extra'); ?>
<div class="mt-4">
    <button onclick="location.reload()"
        class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-xl transition">
        Muat Ulang Halaman
    </button>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('errors.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/errors/419.blade.php ENDPATH**/ ?>