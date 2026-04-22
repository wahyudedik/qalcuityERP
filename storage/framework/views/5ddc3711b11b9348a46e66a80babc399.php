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
     <?php $__env->slot('header', null, []); ?> Zero Input ERP <?php $__env->endSlot(); ?>

    <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <?php if(session('success')): ?>
            <div class="p-3 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg text-sm"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="p-3 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg text-sm"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <!-- Input Methods -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Foto Nota -->
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">📷 Foto Nota / Struk</h3>
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-4">Upload foto nota, kwitansi, atau dokumen. AI akan mengekstrak data otomatis.</p>
                <form method="POST" action="<?php echo e(route('zero-input.photo')); ?>" enctype="multipart/form-data" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <div class="border-2 border-dashed border-gray-300 dark:border-white/10 rounded-xl p-6 text-center cursor-pointer hover:border-blue-400 transition"
                         onclick="document.getElementById('photo-input').click()">
                        <input type="file" id="photo-input" name="photo" accept="image/*,.pdf" class="hidden"
                               onchange="this.closest('form').querySelector('.file-name').textContent = this.files[0]?.name || 'Pilih file'">
                        <p class="text-sm text-gray-500 dark:text-slate-400">📎 Klik untuk pilih foto</p>
                        <p class="file-name text-xs text-blue-500 mt-1">JPG, PNG, PDF — maks 10MB</p>
                    </div>
                    <button type="submit"
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                        Proses Foto
                    </button>
                </form>
            </div>

            <!-- Teks / Voice / WhatsApp -->
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">💬 Teks / Voice / WhatsApp</h3>
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-4">Ketik atau paste teks transaksi. AI akan mapping ke modul yang tepat.</p>
                <form method="POST" action="<?php echo e(route('zero-input.text')); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <select name="channel" class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-slate-800 dark:text-white text-sm">
                        <option value="manual">Manual / Ketik</option>
                        <option value="voice">Voice Transcript</option>
                        <option value="whatsapp">WhatsApp</option>
                    </select>
                    <textarea name="text" rows="4" required
                              placeholder="Contoh: beli bahan baku tepung 50kg dari PT Maju seharga 500rb, bayar cash hari ini"
                              class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-[#0f172a] dark:text-white text-sm"></textarea>
                    <button type="submit"
                            class="w-full px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">
                        Proses Teks
                    </button>
                </form>
            </div>
        </div>

        <!-- Log History -->
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10">
            <div class="p-5 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Riwayat Input</h3>
            </div>
            <?php if($logs->isEmpty()): ?>
                <div class="p-8 text-center text-gray-500 dark:text-slate-400 text-sm">Belum ada riwayat input.</div>
            <?php else: ?>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $channelIcon = match($log->channel) {
                                'photo' => '📷', 'voice' => '🎤', 'whatsapp' => '💬', default => '📝'
                            };
                            $statusClass = match($log->status) {
                                'mapped', 'created' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                                'failed' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                                default => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
                            };
                        ?>
                        <div class="p-4 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <span class="text-xl"><?php echo e($channelIcon); ?></span>
                                <div>
                                    <p class="text-sm font-medium text-gray-700 dark:text-slate-300">
                                        <?php echo e($log->mapped_module ? ucfirst(str_replace('_', ' ', $log->mapped_module)) : 'Belum dipetakan'); ?>

                                    </p>
                                    <p class="text-xs text-gray-400"><?php echo e($log->created_at->diffForHumans()); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <?php if($log->confidence_score): ?>
                                <span class="text-xs text-gray-400 tabular-nums"><?php echo e($log->confidence_score); ?>%</span>
                                <?php endif; ?>
                                <?php if($log->was_corrected): ?>
                                <span class="text-xs px-1.5 py-0.5 rounded bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400">✏️</span>
                                <?php endif; ?>
                                <span class="text-xs px-2 py-0.5 rounded-full <?php echo e($statusClass); ?>"><?php echo e($log->status); ?></span>
                                <a href="<?php echo e(route('zero-input.show', $log)); ?>"
                                   class="text-xs text-blue-500 hover:text-blue-700">Detail</a>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="p-4"><?php echo e($logs->links()); ?></div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\zero-input\index.blade.php ENDPATH**/ ?>