

<?php $__env->startSection('title', 'Kesalahan Server'); ?>
<?php $__env->startSection('code', '500'); ?>
<?php $__env->startSection('icon', '🔧'); ?>
<?php $__env->startSection('icon-bg', 'bg-red-500/10'); ?>
<?php $__env->startSection('heading', 'Terjadi Kesalahan'); ?>
<?php $__env->startSection('message', 'Maaf, terjadi kesalahan pada server kami. Tim teknis sudah diberitahu dan sedang menangani masalah ini.'); ?>

<?php $__env->startSection('extra'); ?>
<div class="mt-4 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl p-4 text-left space-y-2">
    <p class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase">Yang bisa Anda lakukan:</p>
    <ul class="text-sm text-gray-600 dark:text-slate-400 space-y-1.5">
        <li class="flex items-start gap-2"><span class="text-blue-500 shrink-0">→</span> Muat ulang halaman</li>
        <li class="flex items-start gap-2"><span class="text-blue-500 shrink-0">→</span> Coba beberapa menit lagi</li>
        <li class="flex items-start gap-2"><span class="text-blue-500 shrink-0">→</span> Hubungi admin jika masalah berlanjut</li>
    </ul>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('errors.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\errors\500.blade.php ENDPATH**/ ?>