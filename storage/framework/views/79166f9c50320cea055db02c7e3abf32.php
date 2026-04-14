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
     <?php $__env->slot('header', null, []); ?> Custom Fields <?php $__env->endSlot(); ?>

    <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if(session('success')): ?>
            <div class="mb-4 p-3 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg text-sm"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Tambah Field -->
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Tambah Field Baru</h3>
                <form method="POST" action="<?php echo e(route('custom-fields.store')); ?>" x-data="{ type: 'text' }" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Modul</label>
                        <select name="module" class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-slate-800 dark:text-white text-sm">
                            <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>" <?php echo e($module === $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Label Field</label>
                        <input type="text" name="label" required placeholder="Contoh: Nomor Kontrak"
                               class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-[#0f172a] dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe</label>
                        <select name="type" x-model="type" class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-slate-800 dark:text-white text-sm">
                            <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div x-show="type === 'select'">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Pilihan (satu per baris)</label>
                        <textarea name="options" rows="4" placeholder="Opsi 1&#10;Opsi 2&#10;Opsi 3"
                                  class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-[#0f172a] dark:text-white text-sm"></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="required" id="required" value="1"
                               class="rounded border-gray-300 text-blue-600">
                        <label for="required" class="text-sm text-gray-600 dark:text-slate-400">Wajib diisi</label>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Urutan</label>
                        <input type="number" name="sort_order" value="0" min="0"
                               class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-[#0f172a] dark:text-white text-sm">
                    </div>
                    <button type="submit"
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                        Tambah Field
                    </button>
                </form>
            </div>

            <!-- Daftar Field -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Module Tabs -->
                <div class="flex flex-wrap gap-2">
                    <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('custom-fields.index', ['module' => $key])); ?>"
                           class="px-3 py-1.5 rounded-full text-xs font-medium transition
                               <?php echo e($module === $key
                                   ? 'bg-blue-600 text-white'
                                   : 'bg-gray-100 text-gray-600 dark:bg-[#0f172a] dark:text-slate-300 hover:bg-gray-200'); ?>">
                            <?php echo e($label); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <?php if($fields->isEmpty()): ?>
                    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-8 text-center text-gray-500 dark:text-slate-400">
                        <p class="text-sm">Belum ada custom field untuk modul ini.</p>
                    </div>
                <?php else: ?>
                    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 divide-y divide-gray-100 dark:divide-gray-700">
                        <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="p-4 flex items-center justify-between gap-4" x-data="{ editing: false }">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-gray-900 dark:text-white text-sm"><?php echo e($field->label); ?></span>
                                        <?php if($field->required): ?>
                                            <span class="text-xs text-red-500">*wajib</span>
                                        <?php endif; ?>
                                        <?php if(!$field->is_active): ?>
                                            <span class="text-xs px-1.5 py-0.5 bg-gray-100 dark:bg-[#0f172a] text-gray-500 rounded">nonaktif</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                                        Key: <code class="font-mono"><?php echo e($field->key); ?></code> ·
                                        Tipe: <?php echo e($types[$field->type] ?? $field->type); ?>

                                        <?php if($field->type === 'select' && $field->options): ?>
                                            · <?php echo e(count($field->options)); ?> pilihan
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="flex gap-2 shrink-0">
                                    <button @click="editing = !editing"
                                            class="text-xs text-blue-500 hover:text-blue-700">Edit</button>
                                    <form method="POST" action="<?php echo e(route('custom-fields.destroy', $field)); ?>"
                                          onsubmit="return confirm('Hapus field ini? Semua nilai yang tersimpan akan ikut terhapus.')">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                                    </form>
                                </div>
                            </div>
                            <!-- Edit Form -->
                            <div x-show="editing" class="px-4 pb-4 bg-gray-50 dark:bg-[#0f172a]/50">
                                <form method="POST" action="<?php echo e(route('custom-fields.update', $field)); ?>" class="grid grid-cols-2 gap-3">
                                    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Label</label>
                                        <input type="text" name="label" value="<?php echo e($field->label); ?>" required
                                               class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-[#0f172a] dark:text-white text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Urutan</label>
                                        <input type="number" name="sort_order" value="<?php echo e($field->sort_order); ?>" min="0"
                                               class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-[#0f172a] dark:text-white text-sm">
                                    </div>
                                    <?php if($field->type === 'select'): ?>
                                        <div class="col-span-2">
                                            <label class="block text-xs text-gray-500 mb-1">Pilihan (satu per baris)</label>
                                            <textarea name="options" rows="3"
                                                      class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-[#0f172a] dark:text-white text-sm"><?php echo e($field->options ? implode("\n", $field->options) : ''); ?></textarea>
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex items-center gap-4 col-span-2">
                                        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-slate-400">
                                            <input type="checkbox" name="required" value="1" <?php echo e($field->required ? 'checked' : ''); ?>

                                                   class="rounded border-gray-300 text-blue-600">
                                            Wajib
                                        </label>
                                        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-slate-400">
                                            <input type="checkbox" name="is_active" value="1" <?php echo e($field->is_active ? 'checked' : ''); ?>

                                                   class="rounded border-gray-300 text-blue-600">
                                            Aktif
                                        </label>
                                        <button type="submit"
                                                class="ml-auto px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700">
                                            Simpan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/settings/custom-fields.blade.php ENDPATH**/ ?>