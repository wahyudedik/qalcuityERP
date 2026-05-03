<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="text-xl font-semibold text-gray-900">Recovery Codes 2FA</h2>
     <?php $__env->endSlot(); ?>

    <div class="py-6 max-w-lg mx-auto px-4">
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 mb-6">
            <p class="text-sm font-semibold text-yellow-800 mb-1">⚠️ Simpan kode ini sekarang!</p>
            <p class="text-sm text-yellow-700">
                Recovery codes hanya ditampilkan sekali. Simpan di tempat yang aman.
                Setiap kode hanya bisa digunakan satu kali.
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="grid grid-cols-2 gap-2 mb-5">
                <?php $__currentLoopData = $recoveryCodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <code class="font-mono text-sm bg-gray-100 px-3 py-2 rounded-lg text-center tracking-widest text-gray-900">
                        <?php echo e($code); ?>

                    </code>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="flex gap-3">
                <button onclick="copyAll()"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">
                    📋 Salin Semua
                </button>
                <a href="<?php echo e(route('dashboard')); ?>"
                   class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm text-center font-medium hover:bg-blue-700">
                    Selesai
                </a>
            </div>
        </div>
    </div>

    <script>
        function copyAll() {
            const codes = <?php echo json_encode($recoveryCodes, 15, 512) ?>;
            navigator.clipboard.writeText(codes.join('\n'));
            alert('Recovery codes disalin!');
        }
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\auth\two-factor\recovery-codes.blade.php ENDPATH**/ ?>