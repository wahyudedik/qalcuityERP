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
     <?php $__env->slot('header', null, []); ?> Memori AI <?php $__env->endSlot(); ?>

    <div class="py-6 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <?php if(session('success')): ?>
            <div class="p-3 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg text-sm">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <!-- Suggestions -->
        <?php if(!empty($suggestions)): ?>
            <div class="bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded-xl p-5">
                <h3 class="font-semibold text-indigo-800 dark:text-indigo-200 text-sm mb-3">💡 Saran Berdasarkan Kebiasaan Anda</h3>
                <ul class="space-y-2">
                    <?php $__currentLoopData = $suggestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="text-sm text-indigo-700 dark:text-indigo-300 flex items-start gap-2">
                            <span class="mt-0.5">→</span>
                            <span><?php echo e($s); ?></span>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Memory List -->
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10">
            <div class="p-5 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-white">Preferensi yang Dipelajari</h3>
                <form method="POST" action="<?php echo e(route('ai-memory.reset')); ?>"
                      onsubmit="return confirm('Reset semua memori AI? Preferensi yang dipelajari akan dihapus.')">
                    <?php echo csrf_field(); ?>
                    <button class="text-sm text-red-500 hover:text-red-700 dark:hover:text-red-400">
                        Reset Semua
                    </button>
                </form>
            </div>

            <?php if($memories->isEmpty()): ?>
                <div class="p-8 text-center text-gray-500 dark:text-slate-400">
                    <div class="text-4xl mb-3">🧠</div>
                    <p class="text-sm">AI belum mempelajari preferensi Anda.</p>
                    <p class="text-xs mt-1">Preferensi akan dipelajari secara otomatis saat Anda menggunakan sistem.</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php $__currentLoopData = $memories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $memory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-700 dark:text-slate-300 capitalize">
                                    <?php echo e(str_replace('_', ' ', $memory->key)); ?>

                                </p>
                                <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                                    <?php if(is_array($memory->value)): ?>
                                        <?php echo e(implode(', ', array_slice($memory->value, 0, 3))); ?>

                                    <?php else: ?>
                                        <?php echo e($memory->value); ?>

                                    <?php endif; ?>
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Frekuensi: <?php echo e($memory->frequency); ?>x ·
                                    Terakhir: <?php echo e($memory->last_seen_at?->diffForHumans() ?? '-'); ?>

                                </p>
                            </div>
                            <form method="POST" action="<?php echo e(route('ai-memory.destroy', $memory)); ?>">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button class="text-xs text-red-400 hover:text-red-600 ml-4">Hapus</button>
                            </form>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-gray-50 dark:bg-[#1e293b]/50 rounded-xl p-4 text-xs text-gray-500 dark:text-slate-400">
            <p class="font-medium mb-1">Tentang Memori AI</p>
            <p>AI mempelajari kebiasaan Anda secara otomatis: metode pembayaran favorit, gudang default, customer yang sering digunakan, dan langkah yang sering dilewati. Data ini digunakan untuk memberikan saran yang lebih relevan dan mempercepat alur kerja Anda.</p>
        </div>
    </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\settings\ai-memory.blade.php ENDPATH**/ ?>