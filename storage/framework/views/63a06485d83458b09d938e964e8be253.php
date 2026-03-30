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
     <?php $__env->slot('header', null, []); ?> Workflow Persetujuan <?php $__env->endSlot(); ?>

    <div class="space-y-6">

        
        <a href="<?php echo e(route('approvals.index')); ?>" class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Persetujuan
        </a>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-5">Buat Workflow Baru</h2>
            <form method="POST" action="<?php echo e(route('approvals.workflows.store')); ?>"
                  class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Nama Workflow *</label>
                    <input type="text" name="name" required placeholder="cth: Persetujuan Pembelian > 5 Juta"
                        class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Tipe Dokumen</label>
                    <select name="model_type"
                        class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                        <option value="">Umum (semua)</option>
                        <option value="App\Models\Invoice">Invoice</option>
                        <option value="App\Models\PurchaseOrder">Purchase Order</option>
                        <option value="App\Models\Quotation">Penawaran</option>
                        <option value="App\Models\Payroll">Penggajian</option>
                        <option value="App\Models\Budget">Anggaran</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Jumlah Minimum (Rp)</label>
                    <input type="number" name="min_amount" min="0" step="1000" placeholder="0"
                        class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Jumlah Maksimum (Rp, kosongkan = tidak terbatas)</label>
                    <input type="number" name="max_amount" min="0" step="1000" placeholder="Tidak terbatas"
                        class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-2">Role yang Bisa Menyetujui *</label>
                    <div class="flex flex-wrap gap-3">
                        <?php $__currentLoopData = ['admin' => 'Admin', 'manager' => 'Manajer', 'staff' => 'Staff', 'kasir' => 'Kasir', 'gudang' => 'Gudang']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="approver_roles[]" value="<?php echo e($role); ?>"
                                <?php echo e(in_array($role, ['admin','manager']) ? 'checked' : ''); ?>

                                class="w-4 h-4 rounded border-gray-300 dark:border-white/20 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700 dark:text-slate-300"><?php echo e($label); ?></span>
                        </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <div class="sm:col-span-2 flex justify-end">
                    <button type="submit"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl transition">
                        Buat Workflow
                    </button>
                </div>
            </form>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10">
                <h2 class="font-semibold text-gray-900 dark:text-white">Daftar Workflow</h2>
            </div>
            <?php if($workflows->isEmpty()): ?>
                <div class="px-6 py-12 text-center text-gray-400 dark:text-slate-500 text-sm">Belum ada workflow. Buat workflow pertama di atas.</div>
            <?php else: ?>
            <div class="divide-y divide-gray-100 dark:divide-white/5">
                <?php $__currentLoopData = $workflows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="px-6 py-4" x-data="{ editing: false }">
                    
                    <div x-show="!editing" class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-medium text-gray-900 dark:text-white text-sm"><?php echo e($wf->name); ?></p>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    <?php echo e($wf->is_active ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400'); ?>">
                                    <?php echo e($wf->is_active ? 'Aktif' : 'Nonaktif'); ?>

                                </span>
                            </div>
                            <div class="flex flex-wrap gap-3 mt-1.5 text-xs text-gray-400 dark:text-slate-500">
                                <?php if($wf->model_type): ?>
                                <span>📄 <?php echo e(class_basename($wf->model_type)); ?></span>
                                <?php endif; ?>
                                <span>💰 Rp <?php echo e(number_format($wf->min_amount, 0, ',', '.')); ?>

                                    <?php if($wf->max_amount): ?> – Rp <?php echo e(number_format($wf->max_amount, 0, ',', '.')); ?> <?php else: ?> + <?php endif; ?>
                                </span>
                                <span>👥 <?php echo e(implode(', ', $wf->approver_roles ?? [])); ?></span>
                            </div>
                            
                            <div class="flex items-center gap-1 mt-2 flex-wrap">
                                <?php $__currentLoopData = $wf->approver_roles ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($i > 0): ?>
                                <svg class="w-3 h-3 text-gray-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                <?php endif; ?>
                                <span class="px-2 py-0.5 bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-full text-xs font-medium">
                                    <?php echo e(ucfirst($role)); ?>

                                </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <div class="flex gap-2 sm:shrink-0">
                            <button @click="editing = true"
                                class="px-3 py-1.5 border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 text-xs font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition">
                                Edit
                            </button>
                            <form method="POST" action="<?php echo e(route('approvals.workflows.destroy', $wf)); ?>"
                                  onsubmit="return confirm('Hapus workflow ini?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit"
                                    class="px-3 py-1.5 bg-red-500/20 hover:bg-red-500/30 text-red-400 text-xs font-medium rounded-lg transition">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>

                    
                    <div x-show="editing" x-cloak>
                        <form method="POST" action="<?php echo e(route('approvals.workflows.update', $wf)); ?>"
                              class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Nama *</label>
                                <input type="text" name="name" required value="<?php echo e($wf->name); ?>"
                                    class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Tipe Dokumen</label>
                                <select name="model_type"
                                    class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                                    <option value="" <?php echo e(!$wf->model_type ? 'selected' : ''); ?>>Umum</option>
                                    <option value="App\Models\Invoice" <?php echo e($wf->model_type === 'App\Models\Invoice' ? 'selected' : ''); ?>>Invoice</option>
                                    <option value="App\Models\PurchaseOrder" <?php echo e($wf->model_type === 'App\Models\PurchaseOrder' ? 'selected' : ''); ?>>Purchase Order</option>
                                    <option value="App\Models\Quotation" <?php echo e($wf->model_type === 'App\Models\Quotation' ? 'selected' : ''); ?>>Penawaran</option>
                                    <option value="App\Models\Payroll" <?php echo e($wf->model_type === 'App\Models\Payroll' ? 'selected' : ''); ?>>Penggajian</option>
                                    <option value="App\Models\Budget" <?php echo e($wf->model_type === 'App\Models\Budget' ? 'selected' : ''); ?>>Anggaran</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Min Amount</label>
                                <input type="number" name="min_amount" min="0" step="1000" value="<?php echo e($wf->min_amount); ?>"
                                    class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Max Amount</label>
                                <input type="number" name="max_amount" min="0" step="1000" value="<?php echo e($wf->max_amount); ?>"
                                    class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-2">Role Approver *</label>
                                <div class="flex flex-wrap gap-3">
                                    <?php $__currentLoopData = ['admin' => 'Admin', 'manager' => 'Manajer', 'staff' => 'Staff', 'kasir' => 'Kasir', 'gudang' => 'Gudang']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="approver_roles[]" value="<?php echo e($role); ?>"
                                            <?php echo e(in_array($role, $wf->approver_roles ?? []) ? 'checked' : ''); ?>

                                            class="w-4 h-4 rounded border-gray-300 dark:border-white/20 text-blue-600 focus:ring-blue-500">
                                        <span class="text-sm text-gray-700 dark:text-slate-300"><?php echo e($label); ?></span>
                                    </label>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                            <div class="sm:col-span-2 flex items-center justify-between">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1"
                                        <?php echo e($wf->is_active ? 'checked' : ''); ?>

                                        class="w-4 h-4 rounded border-gray-300 dark:border-white/20 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-700 dark:text-slate-300">Aktif</span>
                                </label>
                                <div class="flex gap-2">
                                    <button type="button" @click="editing = false"
                                        class="px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 text-sm font-medium rounded-xl hover:bg-gray-100 dark:hover:bg-white/5 transition">
                                        Batal
                                    </button>
                                    <button type="submit"
                                        class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl transition">
                                        Simpan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\approvals\workflows.blade.php ENDPATH**/ ?>