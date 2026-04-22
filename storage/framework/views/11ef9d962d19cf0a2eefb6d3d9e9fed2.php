
<?php if(!$isCompleted): ?>
    <div class="fixed bottom-0 left-0 right-0 z-30 bg-gray-900/95 backdrop-blur border-t border-white/10 p-4 safe-area-bottom"
        id="batch-action-bar" style="display:none;">
        <div class="flex items-center justify-between gap-2 mb-3">
            <span class="text-xs text-slate-400 font-medium"><span id="batch-count-label">0</span> item dipilih</span>
            <button type="button" onclick="toggleBatchMode(false)"
                class="text-xs text-slate-300 hover:text-white underline">Batal</button>
        </div>
        <form method="POST" action="<?php echo e(route('mobile.opname.batch-update', $s)); ?>" id="batch-form">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="_method" value="POST">
            <div id="batch-items-json"></div>
            <button type="submit"
                class="flex items-center justify-center gap-2 w-full h-14 bg-emerald-600 hover:bg-emerald-500 active:scale-[0.98] text-white font-bold rounded-2xl transition touch-manipulation text-base">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                Kirim Semua (<span id="batch-btn-count">0</span>)
            </button>
        </form>
    </div>

    <div class="fixed bottom-[5.5rem] right-4 z-30">
        <button type="button" onclick="toggleBatchMode(true)" id="btn-toggle-batch"
            class="h-12 px-4 bg-blue-600 hover:bg-blue-500 active:scale-95 text-white text-sm font-semibold rounded-xl shadow-lg transition touch-manipulation">
            📦 Batch Mode
        </button>
    </div>
<?php endif; ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\mobile\partials\opname-batch-ui.blade.php ENDPATH**/ ?>