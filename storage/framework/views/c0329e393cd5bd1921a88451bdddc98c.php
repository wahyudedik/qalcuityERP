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
     <?php $__env->slot('header', null, []); ?> Manajemen Shift & Jadwal Kerja <?php $__env->endSlot(); ?>

    <?php
    $days     = ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
    $prevWeek = $weekStart->copy()->subWeek()->format('Y-m-d');
    $nextWeek = $weekStart->copy()->addWeek()->format('Y-m-d');
    ?>

    <div class="flex flex-col lg:flex-row gap-5">

        
        <div class="lg:w-64 shrink-0 space-y-4">

            
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Template Shift</p>
                    <button onclick="document.getElementById('modal-add-shift').classList.remove('hidden')"
                        class="text-xs px-2 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700">+ Baru</button>
                </div>
                <div class="space-y-1.5" id="shift-palette">
                    <?php $__empty_1 = true; $__currentLoopData = $shifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    
                    <div draggable="true"
                         data-shift-id="<?php echo e($shift->id); ?>"
                         data-shift-name="<?php echo e($shift->name); ?>"
                         data-shift-color="<?php echo e($shift->color); ?>"
                         data-shift-time="<?php echo e($shift->timeLabel()); ?>"
                         ondragstart="onPaletteDragStart(event)"
                         class="shift-palette-item flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-100 dark:border-white/10 cursor-grab active:cursor-grabbing hover:bg-gray-50 dark:hover:bg-white/5 group select-none">
                        <div class="w-3 h-3 rounded-full shrink-0" style="background:<?php echo e($shift->color); ?>"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 dark:text-white truncate"><?php echo e($shift->name); ?></p>
                            <p class="text-xs text-gray-400 dark:text-slate-500"><?php echo e($shift->timeLabel()); ?></p>
                        </div>
                        <button onclick="openEditShift(<?php echo e($shift->id); ?>, <?php echo e(json_encode($shift->only(['name','color','start_time','end_time','break_minutes','crosses_midnight','description']))); ?>)"
                            class="opacity-0 group-hover:opacity-100 p-1 rounded text-gray-400 hover:text-white transition shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-xs text-gray-400 dark:text-slate-500">Belum ada shift.</p>
                    <?php endif; ?>
                </div>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-3 text-center">↑ Drag ke sel jadwal</p>
            </div>

            
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-6 h-6 rounded-lg bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">AI Conflict Detection</p>
                </div>
                <button onclick="runConflictDetection()" id="conflict-btn"
                    class="w-full py-2 text-sm bg-orange-600 text-white rounded-xl hover:bg-orange-700 flex items-center justify-center gap-1.5 disabled:opacity-50">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    Analisis Konflik
                </button>
                <div id="conflict-summary" class="hidden mt-3 space-y-1.5 text-xs"></div>
            </div>

            
            <form method="POST" action="<?php echo e(route('hrm.shifts.copy-week')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="week_start" value="<?php echo e($weekStart->format('Y-m-d')); ?>">
                <button type="submit" onclick="return confirm('Salin jadwal minggu ini ke minggu depan?')"
                    class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5 text-center">
                    📋 Salin ke Minggu Depan
                </button>
            </form>
        </div>

        
        <div class="flex-1 min-w-0 space-y-4">

            
            <div class="flex items-center justify-between">
                <a href="<?php echo e(route('hrm.shifts.index', ['week' => $prevWeek])); ?>"
                   class="p-2 rounded-xl border border-gray-200 dark:border-white/10 text-gray-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-white/5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        <?php echo e($weekStart->format('d M')); ?> – <?php echo e($weekEnd->format('d M Y')); ?>

                    </p>
                    <?php if($weekStart->isCurrentWeek()): ?>
                    <p class="text-xs text-blue-400">Minggu Ini</p>
                    <?php endif; ?>
                </div>
                <a href="<?php echo e(route('hrm.shifts.index', ['week' => $nextWeek])); ?>"
                   class="p-2 rounded-xl border border-gray-200 dark:border-white/10 text-gray-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-white/5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            
            <div id="conflict-panel" class="hidden bg-white dark:bg-[#1e293b] rounded-2xl border border-orange-200 dark:border-orange-500/30 p-4">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Hasil Analisis Konflik Jadwal</p>
                    <button onclick="document.getElementById('conflict-panel').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white text-xs">✕ Tutup</button>
                </div>
                <div id="conflict-loading" class="hidden py-4 text-center">
                    <div class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-slate-400">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Menganalisis jadwal...
                    </div>
                </div>
                <div id="conflict-content"></div>
            </div>

            
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse" id="scheduler-table">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-white/5">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase w-40 border-b border-gray-100 dark:border-white/10 sticky left-0 bg-gray-50 dark:bg-[#1e293b] z-10">Karyawan</th>
                                <?php $__currentLoopData = $weekDays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th class="px-2 py-3 text-center text-xs font-semibold border-b border-gray-100 dark:border-white/10 min-w-[90px]
                                    <?php echo e($day->isToday() ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-slate-400'); ?>">
                                    <p><?php echo e($days[$i]); ?></p>
                                    <p class="text-base font-bold <?php echo e($day->isToday() ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-white'); ?>">
                                        <?php echo e($day->format('d')); ?>

                                    </p>
                                </th>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] group" data-emp-id="<?php echo e($emp->id); ?>">
                                <td class="px-4 py-2 border-r border-gray-100 dark:border-white/10 sticky left-0 bg-white dark:bg-[#1e293b] group-hover:bg-gray-50/50 dark:group-hover:bg-white/[0.02] z-10">
                                    <p class="font-medium text-gray-900 dark:text-white text-xs truncate max-w-[140px]"><?php echo e($emp->name); ?></p>
                                    <p class="text-xs text-gray-400 dark:text-slate-500 truncate"><?php echo e($emp->department ?? $emp->position ?? '-'); ?></p>
                                </td>
                                <?php $__currentLoopData = $weekDays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $dateStr  = $day->format('Y-m-d');
                                    $schedule = $schedules[$emp->id][$dateStr] ?? null;
                                    $isWeekend = $day->isWeekend();
                                ?>
                                <td class="px-1 py-1 text-center <?php echo e($isWeekend ? 'bg-gray-50/50 dark:bg-white/[0.02]' : ''); ?>"
                                    data-emp="<?php echo e($emp->id); ?>" data-date="<?php echo e($dateStr); ?>"
                                    ondragover="onDragOver(event)"
                                    ondragleave="onDragLeave(event)"
                                    ondrop="onDrop(event)">
                                    <button
                                        onclick="openShiftPicker(<?php echo e($emp->id); ?>, '<?php echo e($dateStr); ?>', <?php echo e($schedule?->work_shift_id ?? 'null'); ?>)"
                                        draggable="<?php echo e($schedule ? 'true' : 'false'); ?>"
                                        ondragstart="onCellDragStart(event, <?php echo e($emp->id); ?>, '<?php echo e($dateStr); ?>', <?php echo e($schedule?->work_shift_id ?? 'null'); ?>)"
                                        data-emp="<?php echo e($emp->id); ?>" data-date="<?php echo e($dateStr); ?>"
                                        class="shift-cell w-full min-h-[52px] rounded-lg text-xs transition flex flex-col items-center justify-center gap-0.5 px-1 relative
                                            <?php echo e($schedule ? 'cursor-grab active:cursor-grabbing' : ($isWeekend ? 'cursor-default' : 'hover:bg-gray-100 dark:hover:bg-white/5 cursor-pointer')); ?>"
                                        <?php if($schedule): ?>
                                        style="background-color: <?php echo e($schedule->shift->color); ?>22; border: 1px solid <?php echo e($schedule->shift->color); ?>55; color: <?php echo e($schedule->shift->color); ?>"
                                        <?php endif; ?>
                                    >
                                        <?php if($schedule): ?>
                                        <span class="font-semibold leading-tight"><?php echo e($schedule->shift->name); ?></span>
                                        <span class="opacity-75 leading-tight text-[10px]"><?php echo e($schedule->shift->timeLabel()); ?></span>
                                        <?php else: ?>
                                        <span class="text-lg leading-none text-gray-300 dark:text-slate-700"><?php echo e($isWeekend ? '—' : '+'); ?></span>
                                        <?php endif; ?>
                                    </button>
                                </td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada karyawan aktif.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    
    <div id="modal-shift-picker" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-sm shadow-2xl">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white text-sm">Pilih Shift</p>
                    <p id="picker-label" class="text-xs text-gray-400 dark:text-slate-500 mt-0.5"></p>
                </div>
                <button onclick="document.getElementById('modal-shift-picker').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <div class="p-4 space-y-2">
                <?php $__empty_1 = true; $__currentLoopData = $shifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <button onclick="assignShift(pickerEmpId, pickerDate, <?php echo e($shift->id); ?>)"
                    data-shift-id="<?php echo e($shift->id); ?>"
                    class="picker-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl border border-gray-100 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5 text-left transition">
                    <div class="w-4 h-4 rounded-full shrink-0" style="background:<?php echo e($shift->color); ?>"></div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($shift->name); ?></p>
                        <p class="text-xs text-gray-400 dark:text-slate-500"><?php echo e($shift->timeLabel()); ?> · <?php echo e(round($shift->workMinutes()/60,1)); ?> jam</p>
                    </div>
                </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-gray-400 dark:text-slate-500 text-center py-4">Belum ada shift.</p>
                <?php endif; ?>
            </div>
            <div class="px-4 pb-4">
                <button onclick="assignShift(pickerEmpId, pickerDate, null)"
                    class="w-full px-4 py-2.5 text-sm border border-red-500/30 text-red-400 rounded-xl hover:bg-red-500/10 transition">
                    🗑 Hapus Jadwal
                </button>
            </div>
        </div>
    </div>

    
    <div id="modal-add-shift" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-md shadow-2xl">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
                <p class="font-semibold text-gray-900 dark:text-white text-sm">Tambah Template Shift</p>
                <button onclick="document.getElementById('modal-add-shift').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('hrm.shifts.shift.store')); ?>" class="p-5 space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Shift</label>
                        <input type="text" name="name" required placeholder="cth: Shift Pagi"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Mulai</label>
                        <input type="time" name="start_time" required value="08:00"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Selesai</label>
                        <input type="time" name="end_time" required value="17:00"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Istirahat (menit)</label>
                        <input type="number" name="break_minutes" value="60" min="0" max="480"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Warna</label>
                        <input type="color" name="color" value="#3b82f6"
                            class="w-full h-10 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] cursor-pointer">
                    </div>
                    <div class="col-span-2 flex items-center gap-2">
                        <input type="checkbox" name="crosses_midnight" id="add-crosses-midnight" value="1" class="rounded">
                        <label for="add-crosses-midnight" class="text-sm text-gray-600 dark:text-slate-400">Melewati tengah malam</label>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi (opsional)</label>
                        <input type="text" name="description" placeholder="Keterangan tambahan..."
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-shift').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-edit-shift" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-md shadow-2xl">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
                <p class="font-semibold text-gray-900 dark:text-white text-sm">Edit Shift</p>
                <button onclick="document.getElementById('modal-edit-shift').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-edit-shift" method="POST" class="p-5 space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Shift</label>
                        <input type="text" name="name" id="edit-name" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Mulai</label>
                        <input type="time" name="start_time" id="edit-start"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Selesai</label>
                        <input type="time" name="end_time" id="edit-end"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Istirahat (menit)</label>
                        <input type="number" name="break_minutes" id="edit-break" min="0" max="480"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Warna</label>
                        <input type="color" name="color" id="edit-color"
                            class="w-full h-10 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] cursor-pointer">
                    </div>
                    <div class="col-span-2 flex items-center gap-2">
                        <input type="checkbox" name="crosses_midnight" id="edit-crosses-midnight" value="1" class="rounded">
                        <label for="edit-crosses-midnight" class="text-sm text-gray-600 dark:text-slate-400">Melewati tengah malam</label>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi (opsional)</label>
                        <input type="text" name="description" id="edit-description"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-between items-center pt-2">
                    <button type="button" id="btn-delete-shift"
                        class="px-4 py-2 text-sm border border-red-500/30 text-red-400 rounded-xl hover:bg-red-500/10">Nonaktifkan</button>
                    <div class="flex gap-2">
                        <button type="button" onclick="document.getElementById('modal-edit-shift').classList.add('hidden')"
                            class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                        <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

<?php $__env->startPush('scripts'); ?>
<script>
const ASSIGN_URL   = '<?php echo e(route("hrm.shifts.assign")); ?>';
const CONFLICT_URL = '<?php echo e(route("hrm.shifts.conflicts")); ?>';
const WEEK_START   = '<?php echo e($weekStart->format("Y-m-d")); ?>';
const CSRF         = document.querySelector('meta[name="csrf-token"]').content;

// ── Picker state ───────────────────────────────────────────────
let pickerEmpId = null, pickerDate = null;

// ── Drag state ─────────────────────────────────────────────────
let dragShiftId    = null;  // shift being dragged
let dragShiftName  = null;
let dragShiftColor = null;
let dragShiftTime  = null;
let dragFromEmp    = null;  // source cell (for cell→cell move)
let dragFromDate   = null;

// ── Palette drag start (shift tile → cell) ─────────────────────
function onPaletteDragStart(e) {
    const el = e.currentTarget;
    dragShiftId    = parseInt(el.dataset.shiftId);
    dragShiftName  = el.dataset.shiftName;
    dragShiftColor = el.dataset.shiftColor;
    dragShiftTime  = el.dataset.shiftTime;
    dragFromEmp    = null;
    dragFromDate   = null;
    e.dataTransfer.effectAllowed = 'copy';
    e.dataTransfer.setData('text/plain', dragShiftId);
}

// ── Cell drag start (move existing shift) ─────────────────────
function onCellDragStart(e, empId, date, shiftId) {
    if (!shiftId
) return;
    dragShiftId    = shiftId;
    dragFromEmp    = empId;
    dragFromDate   = date;
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', shiftId);
}

// ── Drag over / leave ──────────────────────────────────────────
function onDragOver(e) {
    e.preventDefault();
    e.currentTarget.classList.add('ring-2', 'ring-blue-400', 'ring-inset');
    e.dataTransfer.dropEffect = dragFromEmp ? 'move' : 'copy';
}
function onDragLeave(e) {
    e.currentTarget.classList.remove('ring-2', 'ring-blue-400', 'ring-inset');
}

// ── Drop ───────────────────────────────────────────────────────
function onDrop(e) {
    e.preventDefault();
    const td   = e.currentTarget;
    td.classList.remove('ring-2', 'ring-blue-400', 'ring-inset');
    const empId = parseInt(td.dataset.emp);
    const date  = td.dataset.date;
    if (!dragShiftId) return;
    // If moving from another cell, clear source first
    if (dragFromEmp && (dragFromEmp !== empId || dragFromDate !== date)) {
        doAssign(dragFromEmp, dragFromDate, null);
    }
    doAssign(empId, date, dragShiftId);
}

// ── Shift picker ───────────────────────────────────────────────
function openShiftPicker(empId, date, currentShiftId) {
    pickerEmpId = empId;
    pickerDate  = date;
    document.getElementById('picker-label').textContent = date;
    // Highlight current
    document.querySelectorAll('.picker-btn').forEach(btn => {
        btn.classList.toggle('ring-2', parseInt(btn.dataset.shiftId) === currentShiftId);
        btn.classList.toggle('ring-blue-500', parseInt(btn.dataset.shiftId) === currentShiftId);
    });
    document.getElementById('modal-shift-picker').classList.remove('hidden');
}

function assignShift(empId, date, shiftId) {
    document.getElementById('modal-shift-picker').classList.add('hidden');
    doAssign(empId, date, shiftId);
}

// ── Core assign (AJAX) ─────────────────────────────────────────
function doAssign(empId, date, shiftId) {
    fetch(ASSIGN_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ employee_id: empId, date, shift_id: shiftId }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
    })
    .catch(() => alert('Gagal menyimpan jadwal.'));
}

// ── Edit shift modal ───────────────────────────────────────────
function openEditShift(id, data) {
    const form = document.getElementById('form-edit-shift');
    form.action = '<?php echo e(url("hrm/shifts/shifts")); ?>/' + id;
    document.getElementById('edit-name').value        = data.name;
    document.getElementById('edit-start').value       = data.start_time;
    document.getElementById('edit-end').value         = data.end_time;
    document.getElementById('edit-break').value       = data.break_minutes;
    document.getElementById('edit-color').value       = data.color;
    document.getElementById('edit-description').value = data.description ?? '';
    document.getElementById('edit-crosses-midnight').checked = !!data.crosses_midnight;
    document.getElementById('btn-delete-shift').onclick = () => {
        if (confirm('Nonaktifkan shift ini?')) {
            fetch('<?php echo e(url("hrm/shifts/shifts")); ?>/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            }).then(() => location.reload());
        }
    };
    document.getElementById('modal-add-shift').classList.add('hidden');
    document.getElementById('modal-edit-shift').classList.remove('hidden');
}

// ── AI Conflict Detection ──────────────────────────────────────
function runConflictDetection() {
    const panel = document.getElementById('conflict-panel');
    const loading = document.getElementById('conflict-loading');
    const content = document.getElementById('conflict-content');
    const btn = document.getElementById('conflict-btn');

    panel.classList.remove('hidden');
    loading.classList.remove('hidden');
    content.innerHTML = '';
    btn.disabled = true;

    fetch(CONFLICT_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ week_start: WEEK_START }),
    })
    .then(r => r.json())
    .then(data => {
        loading.classList.add('hidden');
        btn.disabled = false;
        if (data.conflicts && data.conflicts.length > 0) {
            content.innerHTML = data.conflicts.map(c => `
                <div class="flex items-start gap-2 p-2 rounded-lg bg-orange-50 dark:bg-orange-500/10 border border-orange-200 dark:border-orange-500/20">
                    <span class="text-orange-500 mt-0.5">⚠</span>
                    <p class="text-xs text-orange-700 dark:text-orange-300">${c}</p>
                </div>`).join('');
        } else {
            content.innerHTML = '<p class="text-xs text-green-600 dark:text-green-400 text-center py-2">✓ Tidak ada konflik jadwal ditemukan.</p>';
        }
        if (data.summary) {
            document.getElementById('conflict-summary').innerHTML =
                `<p class="text-xs text-gray-500 dark:text-slate-400">${data.summary}</p>`;
            document.getElementById('conflict-summary').classList.remove('hidden');
        }
    })
    .catch(() => {
        loading.classList.add('hidden');
        btn.disabled = false;
        content.innerHTML = '<p class="text-xs text-red-400 text-center py-2">Gagal menganalisis konflik.</p>';
    });
}
</script>
<?php $__env->stopPush(); ?>

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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/hrm/shifts.blade.php ENDPATH**/ ?>