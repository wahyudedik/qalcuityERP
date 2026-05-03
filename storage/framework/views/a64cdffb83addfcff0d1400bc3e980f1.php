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
     <?php $__env->slot('title', null, []); ?> <?php echo e($plan->exists ? 'Edit Paket' : 'Tambah Paket'); ?> — Qalcuity ERP <?php $__env->endSlot(); ?>
     <?php $__env->slot('header', null, []); ?> <?php echo e($plan->exists ? 'Edit Paket: ' . $plan->name : 'Tambah Paket Baru'); ?> <?php $__env->endSlot(); ?>
     <?php $__env->slot('pageHeader', null, []); ?> 
        <a href="<?php echo e(route('super-admin.plans.index')); ?>"
           class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 px-3 py-2 rounded-xl hover:bg-gray-100 transition">
            ← Kembali
        </a>
     <?php $__env->endSlot(); ?>

    
    <div class="sm:hidden mb-4">
        <a href="<?php echo e(route('super-admin.plans.index')); ?>" class="text-sm text-gray-500">← Kembali ke daftar paket</a>
    </div>

    <div class="max-w-2xl">
        <form method="POST"
              action="<?php echo e($plan->exists ? route('super-admin.plans.update', $plan) : route('super-admin.plans.store')); ?>"
              class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5">
            <?php echo csrf_field(); ?>
            <?php if($plan->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

            <?php if($errors->any()): ?>
            <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">
                <ul class="list-disc list-inside space-y-1">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php $cls = 'w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition'; ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Nama Paket</label>
                    <input type="text" name="name" value="<?php echo e(old('name', $plan->name)); ?>" required class="<?php echo e($cls); ?>">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Slug</label>
                    <input type="text" name="slug" value="<?php echo e(old('slug', $plan->slug)); ?>" required class="<?php echo e($cls); ?> font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Harga Bulanan (Rp)</label>
                    <input type="number" name="price_monthly" value="<?php echo e(old('price_monthly', $plan->price_monthly)); ?>" required min="0" class="<?php echo e($cls); ?>">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Harga Tahunan (Rp)</label>
                    <input type="number" name="price_yearly" value="<?php echo e(old('price_yearly', $plan->price_yearly)); ?>" required min="0" class="<?php echo e($cls); ?>">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Maks. User</label>
                    <input type="number" name="max_users" value="<?php echo e(old('max_users', $plan->max_users ?? 5)); ?>" required min="-1" class="<?php echo e($cls); ?>">
                    <p class="text-xs text-gray-400 mt-1">-1 = tak terbatas</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Maks. Pesan AI/Bln</label>
                    <input type="number" name="max_ai_messages" value="<?php echo e(old('max_ai_messages', $plan->max_ai_messages ?? 100)); ?>" required min="-1" class="<?php echo e($cls); ?>">
                    <p class="text-xs text-gray-400 mt-1">-1 = tak terbatas</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Hari Trial</label>
                    <input type="number" name="trial_days" value="<?php echo e(old('trial_days', $plan->trial_days ?? 14)); ?>" required min="0" class="<?php echo e($cls); ?>">
                </div>
            </div>

            
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Fitur Paket (centang yang tersedia)</label>
                <?php
                $allFeatures = ['POS Kasir','Inventori & Stok','Penjualan & Invoice','Laporan Dasar','Pembelian & Supplier','Piutang & Hutang (AR/AP)','Multi Gudang','Quotation → SO → Invoice','CRM & Pipeline','Konsinyasi (Stok Titipan)','Komisi Sales & Target','Reimbursement Karyawan','Helpdesk & Tiket Support','Subscription Billing (Recurring)','Laporan Keuangan (Neraca, Laba Rugi)','Export Excel & PDF','HRM & Payroll','Aset & Depresiasi','Budget vs Aktual','Rekonsiliasi Bank + AI','Multi Currency','Approval Workflow','Manufaktur (BOM & MRP)','Fleet Management','Manajemen Kontrak & SLA','Landed Cost (Biaya Impor)','Project Billing (T&M/Milestone)','AI Forecasting Dashboard','POS Thermal Printer & Scanner','E-Commerce (Shopee/Tokopedia)','AI Anomaly Detection','Simulasi Bisnis (What If)','Multi Company & Konsolidasi','Zero Input OCR','Custom Integrasi API','WhatsApp Bot Notifikasi','Digital Signature','Prioritas Support'];
                $currentFeatures = old('features_list', $plan->features ?? []);
                ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 max-h-64 overflow-y-auto border border-gray-200 rounded-xl p-3 bg-gray-50">
                    <?php $__currentLoopData = $allFeatures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="flex items-center gap-2 cursor-pointer py-1 px-1 rounded hover:bg-gray-100">
                        <input type="checkbox" name="features_list[]" value="<?php echo e($feat); ?>" <?php echo e(in_array($feat, $currentFeatures) ? 'checked' : ''); ?>

                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-xs text-gray-700"><?php echo e($feat); ?></span>
                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <input type="text" name="features_custom" placeholder="Fitur tambahan (pisah koma)" class="<?php echo e($cls); ?> text-xs mt-2">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Urutan Tampil</label>
                    <input type="number" name="sort_order" value="<?php echo e(old('sort_order', $plan->sort_order ?? 0)); ?>" required min="0" class="<?php echo e($cls); ?>">
                </div>
                <div class="flex items-end pb-2.5">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" <?php echo e(old('is_active', $plan->is_active ?? true) ? 'checked' : ''); ?>

                            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700 font-medium">Paket aktif</span>
                    </label>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-2 border-t border-gray-200">
                <a href="<?php echo e(route('super-admin.plans.index')); ?>" class="w-full sm:w-auto text-center text-sm text-gray-600 px-4 py-2.5 rounded-xl hover:bg-gray-100 transition">Batal</a>
                <button type="submit" class="w-full sm:w-auto text-sm bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-xl transition">
                    <?php echo e($plan->exists ? 'Simpan Perubahan' : 'Buat Paket'); ?>

                </button>
            </div>
        </form>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\super-admin\plans\form.blade.php ENDPATH**/ ?>