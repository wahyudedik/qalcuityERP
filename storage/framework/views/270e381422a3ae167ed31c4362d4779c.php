<?php if(!empty($data['insights'])): ?>
<div class="h-full">
    <div class="flex items-center justify-between gap-2 mb-3">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-lg bg-indigo-500/20 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">Insight AI</p>
            <span class="text-xs text-gray-400 dark:text-slate-500" id="insights-updated-at">— diperbarui otomatis setiap jam</span>
        </div>
        <button id="btn-refresh-insights"
            onclick="refreshDashboardInsights()"
            class="flex items-center gap-1.5 text-xs text-indigo-400 hover:text-indigo-300 transition font-medium">
            <svg id="refresh-icon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Refresh
        </button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3" id="insights-grid">
        <?php $__currentLoopData = array_slice($data['insights'], 0, 6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $borderColor = match($insight['severity']) {
                'critical' => 'border-red-500/40 bg-red-500/5',
                'warning'  => 'border-yellow-500/40 bg-yellow-500/5',
                default    => 'border-blue-500/20 bg-blue-500/5',
            };
            $badgeColor = match($insight['severity']) {
                'critical' => 'bg-red-500/20 text-red-400',
                'warning'  => 'bg-yellow-500/20 text-yellow-400',
                default    => 'bg-blue-500/20 text-blue-400',
            };
            $badgeLabel = match($insight['severity']) {
                'critical' => 'Kritis',
                'warning'  => 'Perhatian',
                default    => 'Info',
            };
        ?>
        <div class="rounded-xl border <?php echo e($borderColor); ?> p-4 flex flex-col gap-2">
            <div class="flex items-start justify-between gap-2">
                <p class="text-sm font-semibold text-gray-900 dark:text-white leading-snug"><?php echo e($insight['title']); ?></p>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full shrink-0 <?php echo e($badgeColor); ?>"><?php echo e($badgeLabel); ?></span>
            </div>
            <p class="text-xs text-gray-500 dark:text-slate-400 leading-relaxed"><?php echo e($insight['body']); ?></p>
            <?php if(!empty($insight['action'])): ?>
            <a href="<?php echo e(route('chat.index')); ?>?q=<?php echo e(urlencode($insight['action'])); ?>"
               class="text-xs text-indigo-400 hover:text-indigo-300 font-medium mt-auto">
                Tanya AI → <?php echo e($insight['action']); ?>

            </a>
            <?php endif; ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php else: ?>
<div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5 h-full flex items-center justify-center">
    <div class="text-center text-gray-400 dark:text-slate-500">
        <svg class="w-8 h-8 mx-auto mb-2 text-indigo-500/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
        <p class="text-sm">Belum ada insight. Data akan dianalisis otomatis.</p>
    </div>
</div>
<?php endif; ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\dashboard\widgets\ai-insights.blade.php ENDPATH**/ ?>