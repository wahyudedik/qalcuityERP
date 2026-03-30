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
     <?php $__env->slot('header', null, []); ?> Konfigurasi Bot WA/Telegram <?php $__env->endSlot(); ?>

    <div class="max-w-2xl mx-auto space-y-6">

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 bg-blue-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-400" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.248l-2.04 9.613c-.15.67-.54.835-1.094.52l-3.02-2.226-1.46 1.404c-.16.16-.296.296-.607.296l.216-3.063 5.57-5.03c.242-.215-.053-.335-.375-.12L7.04 14.6l-2.97-.927c-.645-.2-.658-.645.135-.955l11.59-4.47c.537-.196 1.007.13.767.999z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-semibold text-white">Telegram Bot</h2>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Kirim notifikasi & terima perintah via Telegram</p>
                </div>
                <?php if($telegram?->is_active): ?>
                    <span class="ml-auto px-2 py-1 bg-green-500/20 text-green-400 text-xs rounded-full font-medium">Aktif</span>
                <?php endif; ?>
            </div>

            <form method="POST" action="<?php echo e(route('bot.save')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="platform" value="telegram">

                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1.5">Bot Token</label>
                    <input type="text" name="token" value="<?php echo e($telegram?->token); ?>" placeholder="1234567890:ABCdef..."
                        class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2.5 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-blue-500 transition">
                    <p class="text-xs text-gray-400 dark:text-slate-500 mt-1.5">Dapatkan token dari <a href="https://t.me/BotFather" target="_blank" class="text-blue-400 hover:underline">@BotFather</a></p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <?php $__currentLoopData = ['is_active' => 'Aktifkan Bot', 'notify_new_order' => 'Order Baru', 'notify_low_stock' => 'Stok Rendah', 'notify_payment' => 'Pembayaran', 'notify_approval' => 'Persetujuan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="<?php echo e($field); ?>" value="1" <?php echo e($telegram?->$field ? 'checked' : ''); ?>

                            class="w-4 h-4 rounded border-white/20 bg-gray-50 dark:bg-[#0f172a] text-blue-500 focus:ring-blue-500 focus:ring-offset-0">
                        <span class="text-sm text-gray-700 dark:text-slate-300"><?php echo e($label); ?></span>
                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="pt-1">
                    <p class="text-xs text-gray-400 dark:text-slate-500 mb-2">Webhook URL (daftarkan ke Telegram):</p>
                    <code class="text-xs bg-gray-50 dark:bg-[#0f172a] text-gray-700 dark:text-slate-300 px-3 py-2 rounded-lg block break-all border border-gray-200 dark:border-white/10"><?php echo e(url('/webhook/telegram')); ?></code>
                </div>

                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-sm font-medium transition">
                    Simpan Konfigurasi Telegram
                </button>
            </form>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 bg-green-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-400" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-semibold text-white">WhatsApp Bot</h2>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Integrasi via WhatsApp Business API (Meta)</p>
                </div>
                <?php if($whatsapp?->is_active): ?>
                    <span class="ml-auto px-2 py-1 bg-green-500/20 text-green-400 text-xs rounded-full font-medium">Aktif</span>
                <?php endif; ?>
            </div>

            <form method="POST" action="<?php echo e(route('bot.save')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="platform" value="whatsapp">

                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1.5">Access Token (Meta)</label>
                    <input type="text" name="token" value="<?php echo e($whatsapp?->token); ?>" placeholder="EAABs..."
                        class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2.5 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-green-500 transition">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <?php $__currentLoopData = ['is_active' => 'Aktifkan Bot', 'notify_new_order' => 'Order Baru', 'notify_low_stock' => 'Stok Rendah', 'notify_payment' => 'Pembayaran', 'notify_approval' => 'Persetujuan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="<?php echo e($field); ?>" value="1" <?php echo e($whatsapp?->$field ? 'checked' : ''); ?>

                            class="w-4 h-4 rounded border-white/20 bg-gray-50 dark:bg-[#0f172a] text-green-500 focus:ring-green-500 focus:ring-offset-0">
                        <span class="text-sm text-gray-700 dark:text-slate-300"><?php echo e($label); ?></span>
                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="pt-1">
                    <p class="text-xs text-gray-400 dark:text-slate-500 mb-2">Webhook URL (daftarkan ke Meta Developer):</p>
                    <code class="text-xs bg-gray-50 dark:bg-[#0f172a] text-gray-700 dark:text-slate-300 px-3 py-2 rounded-lg block break-all border border-gray-200 dark:border-white/10"><?php echo e(url('/webhook/whatsapp')); ?></code>
                </div>

                <button type="submit" class="px-5 py-2.5 bg-green-600 hover:bg-green-500 text-white rounded-xl text-sm font-medium transition">
                    Simpan Konfigurasi WhatsApp
                </button>
            </form>
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

<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\settings\bot.blade.php ENDPATH**/ ?>