<div class="bg-white rounded-2xl border border-gray-200 p-5 h-full">
    <div class="flex items-start justify-between mb-4">
        <p class="text-xs font-medium text-gray-500 leading-tight">Kehadiran Hari Ini</p>
        <div class="w-9 h-9 rounded-xl bg-purple-500/20 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
    </div>
    <p class="text-2xl font-bold text-gray-900"><?php echo e($data['present_today'] ?? 0); ?></p>
    <p class="text-xs text-gray-400 mt-1">Dari <?php echo e($data['total_employees'] ?? 0); ?> karyawan aktif</p>
</div>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/dashboard/widgets/kpi-attendance.blade.php ENDPATH**/ ?>