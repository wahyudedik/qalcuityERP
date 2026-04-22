

<?php $__env->startSection('title', 'Pengaturan Modul'); ?>

 <?php $__env->slot('header', null, []); ?> Pengaturan Modul <?php $__env->endSlot(); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-4xl mx-auto space-y-6">

        
        <?php if(session('success')): ?>
        <div class="flex items-start gap-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 text-green-800 dark:text-green-300 rounded-2xl px-5 py-4 text-sm">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span><?php echo e(session('success')); ?></span>
        </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
        <div class="flex items-start gap-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-800 dark:text-red-300 rounded-2xl px-5 py-4 text-sm">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div class="flex-1">
                <p class="font-medium">Tidak dapat menyimpan pengaturan</p>
                <p class="mt-0.5 text-red-700 dark:text-red-400"><?php echo e(session('error')); ?></p>
                <?php if(session('upgrade_required')): ?>
                <a href="<?php echo e(route('subscription.index')); ?>" class="inline-flex items-center gap-1.5 mt-2 text-xs font-semibold bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    Upgrade Paket Sekarang
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Modul Aktif</h2>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                        Aktifkan atau nonaktifkan modul sesuai kebutuhan bisnis Anda. Modul yang dinonaktifkan tidak akan
                        muncul di menu.
                    </p>
                </div>
                <div class="flex flex-col items-end gap-2 shrink-0">
                    <span class="text-xs bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300 px-3 py-1 rounded-full font-medium">
                        <?php echo e(count($enabled)); ?> / <?php echo e(count($all)); ?> aktif
                    </span>
                    <?php
                        $planLabels = [
                            'trial'        => ['label' => 'Trial 14 Hari', 'color' => 'yellow'],
                            'starter'      => ['label' => 'Starter',       'color' => 'gray'],
                            'business'     => ['label' => 'Business',      'color' => 'blue'],
                            'professional' => ['label' => 'Professional',  'color' => 'purple'],
                            'enterprise'   => ['label' => 'Enterprise',    'color' => 'green'],
                        ];
                        $planInfo = $planLabels[$planSlug] ?? ['label' => strtoupper($planSlug ?? 'Unknown'), 'color' => 'gray'];
                        $colorMap = [
                            'yellow' => 'bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-300',
                            'gray'   => 'bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-slate-300',
                            'blue'   => 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300',
                            'purple' => 'bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300',
                            'green'  => 'bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-300',
                        ];
                    ?>
                    <span class="text-xs px-3 py-1 rounded-full font-medium <?php echo e($colorMap[$planInfo['color']]); ?>">
                        Paket: <?php echo e($planInfo['label']); ?>

                    </span>
                </div>
            </div>

            
            <?php if($planSlug === 'trial'): ?>
            <div class="mt-4 flex items-center gap-3 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 rounded-xl px-4 py-3">
                <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm text-amber-800 dark:text-amber-300 flex-1">
                    Paket trial hanya mencakup modul dasar. Modul dengan ikon 🔒 memerlukan upgrade paket.
                </p>
                <a href="<?php echo e(route('subscription.index')); ?>" class="shrink-0 text-xs font-semibold bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-lg transition">
                    Upgrade
                </a>
            </div>
            <?php endif; ?>
        </div>

        <form method="POST" action="<?php echo e(route('settings.modules.update')); ?>" id="module-form">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Strategi Cleanup Data</h2>
                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                            Pilih bagaimana data modul yang dinonaktifkan akan ditangani.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label
                        class="relative flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition
                        <?php echo e(old('cleanup_strategy', 'keep') === 'keep' ? 'border-blue-500 bg-blue-50 dark:bg-blue-500/10' : 'border-gray-200 dark:border-white/10 hover:border-gray-300'); ?>"
                        onclick="selectStrategy('keep')">
                        <input type="radio" name="cleanup_strategy" value="keep" class="sr-only"
                            <?php echo e(old('cleanup_strategy', 'keep') === 'keep' ? 'checked' : ''); ?>>
                        <div class="text-2xl">💾</div>
                        <div class="flex-1">
                            <div class="font-medium text-sm text-gray-900 dark:text-white">Simpan Data</div>
                            <div class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Data tetap di database, hanya hide
                                dari UI</div>
                        </div>
                    </label>

                    <label
                        class="relative flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition
                        <?php echo e(old('cleanup_strategy') === 'archive' ? 'border-blue-500 bg-blue-50 dark:bg-blue-500/10' : 'border-gray-200 dark:border-white/10 hover:border-gray-300'); ?>"
                        onclick="selectStrategy('archive')">
                        <input type="radio" name="cleanup_strategy" value="archive" class="sr-only"
                            <?php echo e(old('cleanup_strategy') === 'archive' ? 'checked' : ''); ?>>
                        <div class="text-2xl">📦</div>
                        <div class="flex-1">
                            <div class="font-medium text-sm text-gray-900 dark:text-white">Archive Data</div>
                            <div class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Pindah ke tabel archive, bisa
                                di-restore</div>
                        </div>
                    </label>

                    <label
                        class="relative flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition
                        <?php echo e(old('cleanup_strategy') === 'soft_delete' ? 'border-blue-500 bg-blue-50 dark:bg-blue-500/10' : 'border-gray-200 dark:border-white/10 hover:border-gray-300'); ?>"
                        onclick="selectStrategy('soft_delete')">
                        <input type="radio" name="cleanup_strategy" value="soft_delete" class="sr-only"
                            <?php echo e(old('cleanup_strategy') === 'soft_delete' ? 'checked' : ''); ?>>
                        <div class="text-2xl">🗑️</div>
                        <div class="flex-1">
                            <div class="font-medium text-sm text-gray-900 dark:text-white">Soft Delete</div>
                            <div class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Mark as deleted, tetap bisa
                                di-restore</div>
                        </div>
                    </label>
                </div>
            </div>

            <?php
                $groups = [
                    'Penjualan & Operasional' => [
                        'pos',
                        'sales',
                        'invoicing',
                        'crm',
                        'contracts',
                        'ecommerce',
                        'loyalty',
                        'commission',
                        'helpdesk',
                        'subscription_billing',
                    ],
                    'Inventori & Produksi' => [
                        'inventory',
                        'purchasing',
                        'production',
                        'manufacturing',
                        'fleet',
                        'consignment',
                        'wms',
                    ],
                    'SDM & Keuangan' => [
                        'hrm',
                        'payroll',
                        'accounting',
                        'budget',
                        'bank_reconciliation',
                        'assets',
                        'landed_cost',
                        'reimbursement',
                    ],
                    'Manajemen & Analitik' => ['projects', 'reports', 'project_billing'],
                    'Industri & Vertikal' => ['hotel', 'fnb', 'spa', 'agriculture', 'livestock', 'telecom'],
                ];
            ?>

            <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupName => $keys): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-4">
                        <?php echo e($groupName); ?></h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <?php $__currentLoopData = $keys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $m = $meta[$key];
                                $isEnabled = in_array($key, $enabled);
                                $isLocked = !in_array($key, $allowedByPlan);
                            ?>
                            <label
                                class="module-card flex items-center gap-4 p-4 rounded-xl border-2 transition
                                <?php echo e($isLocked
                                    ? 'border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 opacity-70 cursor-not-allowed'
                                    : 'cursor-pointer ' . ($isEnabled ? 'border-blue-500 bg-blue-50 dark:bg-blue-500/10 dark:border-blue-500/50' : 'border-gray-200 dark:border-white/10 hover:border-gray-300 dark:hover:border-white/20')); ?>">
                                <input type="checkbox" name="modules[]" value="<?php echo e($key); ?>"
                                    class="sr-only module-checkbox"
                                    <?php echo e($isEnabled && !$isLocked ? 'checked' : ''); ?>

                                    <?php echo e($isLocked ? 'disabled' : ''); ?>

                                    onchange="updateCard(this)">
                                <span class="text-2xl shrink-0"><?php echo e($m['icon']); ?></span>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-sm text-gray-900 dark:text-white flex items-center gap-1.5">
                                        <?php echo e($m['label']); ?>

                                        <?php if($isLocked): ?>
                                        <span class="text-xs bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 px-1.5 py-0.5 rounded font-medium">Upgrade</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-slate-400 mt-0.5"><?php echo e($m['desc']); ?></div>
                                </div>
                                <div class="shrink-0">
                                    <?php if($isLocked): ?>
                                    <svg class="w-5 h-5 text-gray-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <?php else: ?>
                                    <div
                                        class="w-10 h-5 rounded-full transition-colors <?php echo e($isEnabled ? 'bg-blue-600' : 'bg-gray-300 dark:bg-white/20'); ?> relative toggle-track">
                                        <div
                                            class="absolute top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform <?php echo e($isEnabled ? 'translate-x-5' : 'translate-x-0.5'); ?> toggle-thumb">
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <div class="flex items-center justify-between">
                <div class="flex gap-4">
                    <button type="button" onclick="enableAll()"
                        class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                        Aktifkan semua
                    </button>
                    <button type="button" onclick="disableAll()"
                        class="text-sm text-gray-500 dark:text-slate-400 hover:underline">
                        Nonaktifkan semua
                    </button>
                </div>
                <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                    Simpan Pengaturan
                </button>
            </div>
        </form>

    </div>

    <script>
        // BUG-SET-002 FIX: Strategy selector
        function selectStrategy(strategy) {
            document.querySelectorAll('input[name="cleanup_strategy"]').forEach(radio => {
                radio.checked = radio.value === strategy;
            });

            // Update UI
            document.querySelectorAll('[onclick^="selectStrategy"]').forEach(label => {
                const isSelected = label.getAttribute('onclick').includes(strategy);
                if (isSelected) {
                    label.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-500/10');
                    label.classList.remove('border-gray-200', 'dark:border-white/10');
                } else {
                    label.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-500/10');
                    label.classList.add('border-gray-200', 'dark:border-white/10');
                }
            });
        }

        function updateCard(checkbox) {
            const label = checkbox.closest('label');
            const track = label.querySelector('.toggle-track');
            const thumb = label.querySelector('.toggle-thumb');
            if (checkbox.checked) {
                label.classList.add('border-blue-500', 'bg-blue-50');
                label.classList.remove('border-gray-200');
                label.setAttribute('data-enabled', '1');
                track.classList.replace('bg-gray-300', 'bg-blue-600');
                track.classList.remove('dark:bg-white/20');
                thumb.classList.replace('translate-x-0.5', 'translate-x-5');
            } else {
                label.classList.remove('border-blue-500', 'bg-blue-50');
                label.classList.add('border-gray-200');
                label.removeAttribute('data-enabled');
                track.classList.replace('bg-blue-600', 'bg-gray-300');
                track.classList.add('dark:bg-white/20');
                thumb.classList.replace('translate-x-5', 'translate-x-0.5');
            }
        }

        function enableAll() {
            document.querySelectorAll('.module-checkbox:not(:disabled)').forEach(cb => {
                if (!cb.checked) {
                    cb.checked = true;
                    updateCard(cb);
                }
            });
        }

        function disableAll() {
            document.querySelectorAll('.module-checkbox:not(:disabled)').forEach(cb => {
                if (cb.checked) {
                    cb.checked = false;
                    updateCard(cb);
                }
            });
        }

        // BUG-SET-002 FIX: Show impact analysis on form submit
        document.getElementById('module-form').addEventListener('submit', function(e) {
            const checkedModules = Array.from(document.querySelectorAll('.module-checkbox:checked'))
                .map(cb => cb.value);

            const currentModules = <?php echo json_encode($enabled, 15, 512) ?>;
            const disabledModules = currentModules.filter(m => !checkedModules.includes(m));

            if (disabledModules.length > 0) {
                const message = `Anda akan menonaktifkan ${disabledModules.length} modul:\n` +
                    disabledModules.map(m => `• ${m}`).join('\n') +
                    '\n\nData akan ditangani sesuai strategi yang dipilih.';

                if (!confirm(message)) {
                    e.preventDefault();
                }
            }
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\settings\modules.blade.php ENDPATH**/ ?>