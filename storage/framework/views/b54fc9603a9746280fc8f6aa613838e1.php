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
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <?php echo e(__('Tentang Qalcuity')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-3xl font-bold mb-6">Tentang Qalcuity ERP</h1>

                    <div class="prose dark:prose-invert max-w-none">
                        <p class="text-lg mb-4">
                            Qalcuity adalah platform ERP berbasis AI yang dirancang khusus untuk bisnis Indonesia.
                            Kami membantu perusahaan mengelola semua aspek bisnis mereka melalui percakapan natural.
                        </p>

                        <h2 class="text-2xl font-semibold mt-8 mb-4">Misi Kami</h2>
                        <p class="mb-4">
                            Membuat teknologi ERP yang mudah diakses dan digunakan oleh semua bisnis, dari startup
                            hingga enterprise, dengan kekuatan artificial intelligence.
                        </p>

                        <h2 class="text-2xl font-semibold mt-8 mb-4">Kenapa Qalcuity?</h2>
                        <ul class="list-disc list-inside space-y-2 mb-6">
                            <li>AI-Powered: Kelola bisnis dengan percakapan natural</li>
                            <li>Multi-Industry: Support untuk berbagai industri</li>
                            <li>Cloud-Native: Akses dari mana saja, kapan saja</li>
                            <li>Enterprise-Grade: Security dan compliance terbaik</li>
                            <li>Local Support: Tim support berbasis di Indonesia</li>
                        </ul>

                        <div class="mt-8 p-6 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <h3 class="text-xl font-semibold mb-2">Tertarik untuk bergabung?</h3>
                            <p class="mb-4">Lihat lowongan karir kami atau hubungi tim sales.</p>
                            <div class="flex gap-4">
                                <a href="<?php echo e(route('about.careers')); ?>"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    Lihat Karir
                                </a>
                                <a href="https://wa.me/6281654932383" target="_blank"
                                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    Hubungi Sales
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\pages\about\index.blade.php ENDPATH**/ ?>