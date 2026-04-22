

<?php $__env->startSection('title', 'Sedang Maintenance'); ?>
<?php $__env->startSection('code', '503'); ?>
<?php $__env->startSection('icon', '🛠️'); ?>
<?php $__env->startSection('icon-bg', 'bg-indigo-500/10'); ?>
<?php $__env->startSection('heading', 'Sedang Maintenance'); ?>
<?php $__env->startSection('message', 'Qalcuity ERP sedang dalam pemeliharaan terjadwal. Kami akan kembali dalam beberapa menit.'); ?>

<?php $__env->startSection('extra'); ?>
<div class="mt-4 bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/20 rounded-xl p-4 text-sm text-indigo-700 dark:text-indigo-400">
    <p class="font-medium mb-1">Estimasi selesai:</p>
    <p>Biasanya kurang dari 15 menit. Halaman ini akan otomatis refresh.</p>
</div>
<script>setTimeout(() => location.reload(), 30000);</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('errors.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\errors\503.blade.php ENDPATH**/ ?>