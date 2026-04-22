
<?php $__env->startSection('title', 'Preferensi Notifikasi'); ?>

<?php $__env->startSection('content'); ?>
    <div class="p-6 space-y-8 max-w-4xl">

        
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="<?php echo e(route('notifications.index')); ?>"
                        class="text-sm text-blue-500 hover:text-blue-600 transition">← Pusat Notifikasi</a>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Preferensi Notifikasi</h1>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Pilih jenis notifikasi yang ingin Anda terima dan
                    melalui channel apa.</p>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div
                class="p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-sm text-emerald-700 dark:text-emerald-300">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('notifications.preferences.update')); ?>" class="space-y-6">
            <?php echo csrf_field(); ?>

            <?php
                $moduleLabels = [
                    'inventory' => ['label' => 'Inventori', 'icon' => '📦'],
                    'finance' => ['label' => 'Keuangan', 'icon' => '💰'],
                    'hrm' => ['label' => 'HRM', 'icon' => '👥'],
                    'ai' => ['label' => 'AI', 'icon' => '🤖'],
                    'system' => ['label' => 'Sistem', 'icon' => '⚙️'],
                ];
            ?>

            
            <?php $__currentLoopData = $availableTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module => $types): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div
                    class="bg-white dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/[0.03]">
                        <h2 class="text-sm font-semibold text-gray-700 dark:text-slate-300 flex items-center gap-2">
                            <span><?php echo e($moduleLabels[$module]['icon'] ?? '🔔'); ?></span>
                            <span><?php echo e($moduleLabels[$module]['label'] ?? ucfirst($module)); ?></span>
                        </h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-white/5">
                                    <th
                                        class="text-left px-5 py-2.5 text-xs font-medium text-gray-500 dark:text-slate-400 w-full">
                                        Tipe Notifikasi</th>
                                    <th
                                        class="px-4 py-2.5 text-xs font-medium text-gray-500 dark:text-slate-400 text-center whitespace-nowrap">
                                        In-App</th>
                                    <th
                                        class="px-4 py-2.5 text-xs font-medium text-gray-500 dark:text-slate-400 text-center whitespace-nowrap">
                                        Email</th>
                                    <th
                                        class="px-4 py-2.5 text-xs font-medium text-gray-500 dark:text-slate-400 text-center whitespace-nowrap">
                                        Push</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-white/[0.04]">
                                <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $pref = $userPrefs->get($type);
                                        $inApp = $pref ? $pref->in_app : true;
                                        $email = $pref ? $pref->email : true;
                                        $push = $pref ? $pref->push : true;
                                    ?>
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                                        <td class="px-5 py-3 text-gray-800 dark:text-slate-200 font-medium">
                                            <?php echo e($label); ?></td>
                                        <td class="px-4 py-3 text-center">
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="preferences[<?php echo e($type); ?>][in_app]"
                                                    value="1" <?php echo e($inApp ? 'checked' : ''); ?>

                                                    class="w-4 h-4 rounded border-gray-300 dark:border-white/20 text-blue-600 focus:ring-blue-500 focus:ring-offset-0">
                                            </label>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="preferences[<?php echo e($type); ?>][email]"
                                                    value="1" <?php echo e($email ? 'checked' : ''); ?>

                                                    class="w-4 h-4 rounded border-gray-300 dark:border-white/20 text-blue-600 focus:ring-blue-500 focus:ring-offset-0">
                                            </label>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="preferences[<?php echo e($type); ?>][push]"
                                                    value="1" <?php echo e($push ? 'checked' : ''); ?>

                                                    class="w-4 h-4 rounded border-gray-300 dark:border-white/20 text-blue-600 focus:ring-blue-500 focus:ring-offset-0">
                                            </label>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            
            <div class="bg-white dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/[0.03]">
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-slate-300 flex items-center gap-2">
                        <span>📋</span>
                        <span>Pengaturan Ringkasan (Digest)</span>
                    </h2>
                    <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">Terima ringkasan aktivitas secara berkala
                        melalui email.</p>
                </div>

                <div class="p-5 space-y-5">
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">Frekuensi</label>
                        <select name="digest_frequency" id="digestFrequency" onchange="toggleDigestDay(this.value)"
                            class="w-full sm:w-64 px-3 py-2 rounded-lg text-sm bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="daily"
                                <?php echo e(($user->digest_frequency ?? 'weekly') === 'daily' ? 'selected' : ''); ?>>Harian</option>
                            <option value="weekly"
                                <?php echo e(($user->digest_frequency ?? 'weekly') === 'weekly' ? 'selected' : ''); ?>>Mingguan
                            </option>
                            <option value="monthly"
                                <?php echo e(($user->digest_frequency ?? 'weekly') === 'monthly' ? 'selected' : ''); ?>>Bulanan
                            </option>
                            <option value="off"
                                <?php echo e(($user->digest_frequency ?? 'weekly') === 'off' ? 'selected' : ''); ?>>Nonaktif
                            </option>
                        </select>
                    </div>

                    
                    <div id="digestDayWrapper"
                        class="<?php echo e(($user->digest_frequency ?? 'weekly') !== 'weekly' ? 'hidden' : ''); ?>">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">Hari
                            Pengiriman</label>
                        <select name="digest_day"
                            class="w-full sm:w-64 px-3 py-2 rounded-lg text-sm bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php $__currentLoopData = ['monday' => 'Senin', 'tuesday' => 'Selasa', 'wednesday' => 'Rabu', 'thursday' => 'Kamis', 'friday' => 'Jumat', 'saturday' => 'Sabtu', 'sunday' => 'Minggu']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $dayLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($val); ?>"
                                    <?php echo e(($user->digest_day ?? 'friday') === $val ? 'selected' : ''); ?>><?php echo e($dayLabel); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    
                    <div id="digestTimeWrapper"
                        class="<?php echo e(($user->digest_frequency ?? 'weekly') === 'off' ? 'hidden' : ''); ?>">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">Waktu
                            Pengiriman</label>
                        <input type="time" name="digest_time" value="<?php echo e($user->digest_time ?? '17:00'); ?>"
                            class="w-full sm:w-64 px-3 py-2 rounded-lg text-sm bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Zona waktu server digunakan.</p>
                    </div>
                </div>
            </div>

            
            <div class="flex justify-end gap-3 pb-4">
                <a href="<?php echo e(route('notifications.index')); ?>"
                    class="px-5 py-2.5 rounded-lg text-sm border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5 transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-2.5 rounded-lg text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium transition">
                    Simpan Preferensi
                </button>
            </div>

        </form>
    </div>

    <script>
        function toggleDigestDay(value) {
            const dayWrapper = document.getElementById('digestDayWrapper');
            const timeWrapper = document.getElementById('digestTimeWrapper');
            dayWrapper.classList.toggle('hidden', value !== 'weekly');
            timeWrapper.classList.toggle('hidden', value === 'off');
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\notifications\preferences.blade.php ENDPATH**/ ?>