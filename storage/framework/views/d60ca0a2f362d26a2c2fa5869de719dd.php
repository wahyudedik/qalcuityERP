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
     <?php $__env->slot('header', null, []); ?> Audit Trail <?php $__env->endSlot(); ?>

    <div class="space-y-4">

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase">Quick Filter:</span>
                <a href="<?php echo e(route('audit.index')); ?>"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition <?php echo e(!request()->hasAny(['is_ai','action','user_id','module']) ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-slate-300 hover:bg-gray-200 dark:hover:bg-white/15'); ?>">
                    Semua
                </a>
                <a href="<?php echo e(request()->fullUrlWithQuery(['is_ai' => '1', 'page' => null])); ?>"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition <?php echo e(request('is_ai') === '1' ? 'bg-purple-600 text-white' : 'bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300 hover:bg-purple-200'); ?>">
                    🤖 AI (<?php echo e($aiCount); ?> hari ini)
                </a>
                <a href="<?php echo e(request()->fullUrlWithQuery(['is_ai' => '0', 'page' => null])); ?>"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition <?php echo e(request('is_ai') === '0' ? 'bg-gray-600 text-white' : 'bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-slate-300 hover:bg-gray-200 dark:hover:bg-white/15'); ?>">
                    👤 Manual
                </a>
            </div>
            <form method="GET" class="flex flex-wrap gap-2">
                <?php if(request('is_ai') !== null): ?><input type="hidden" name="is_ai" value="<?php echo e(request('is_ai')); ?>"><?php endif; ?>
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari deskripsi..."
                    class="flex-1 min-w-[150px] px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="user_id" class="px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white">
                    <option value="">Semua User</option>
                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($u->id); ?>" <?php if(request('user_id') == $u->id): echo 'selected'; endif; ?>><?php echo e($u->name); ?> (<?php echo e($u->role); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select name="module" class="px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white">
                    <option value="">Semua Modul</option>
                    <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($m); ?>" <?php if(request('module') === $m): echo 'selected'; endif; ?>><?php echo e($m); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select name="action" class="px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white">
                    <option value="">Semua Aksi</option>
                    <?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($a); ?>" <?php if(request('action') === $a): echo 'selected'; endif; ?>><?php echo e($a); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" class="px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white">
                <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" class="px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm hover:bg-blue-700">Filter</button>
                <?php if(request()->hasAny(['search','user_id','module','action','date_from','date_to','is_ai'])): ?>
                <a href="<?php echo e(route('audit.index')); ?>" class="px-4 py-2 border border-gray-200 dark:border-white/10 rounded-xl text-sm text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Waktu</th>
                            <th class="px-4 py-3 text-left">User</th>
                            <th class="px-4 py-3 text-left">Aksi</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Modul</th>
                            <th class="px-4 py-3 text-left">Deskripsi</th>
                            <th class="px-4 py-3 text-center">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 <?php echo e($log->is_ai_action ? 'bg-purple-50/30 dark:bg-purple-900/5' : ''); ?>">
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400 whitespace-nowrap">
                                <?php echo e($log->created_at->format('d/m/Y')); ?><br>
                                <span class="text-gray-400 dark:text-slate-500"><?php echo e($log->created_at->format('H:i:s')); ?></span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full <?php echo e($log->is_ai_action ? 'bg-purple-100 dark:bg-purple-900/40' : 'bg-gray-100 dark:bg-white/10'); ?> flex items-center justify-center text-xs">
                                        <?php echo e($log->is_ai_action ? '🤖' : '👤'); ?>

                                    </span>
                                    <span class="text-xs font-medium text-gray-900 dark:text-white"><?php echo e($log->user?->name ?? 'System'); ?></span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($log->is_ai_action ? 'bg-purple-500/20 text-purple-400' : 'bg-blue-500/20 text-blue-400'); ?>">
                                    <?php echo e($log->ai_tool_name ?? $log->action); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell text-xs text-gray-500 dark:text-slate-400">
                                <?php echo e($log->model_type ? class_basename($log->model_type) : '—'); ?>

                                <?php if($log->model_id): ?> <span class="text-gray-400">#<?php echo e($log->model_id); ?></span> <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-700 dark:text-slate-300 max-w-xs">
                                <span class="line-clamp-2"><?php echo e($log->description); ?></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if($log->old_values || $log->new_values): ?>
                                <button onclick="openDiff(<?php echo e($log->id); ?>)"
                                    class="text-xs text-blue-500 hover:text-blue-400 hover:underline">Diff</button>
                                <?php else: ?>
                                <span class="text-xs text-gray-300 dark:text-slate-600">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Tidak ada log aktivitas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5"><?php echo e($logs->links()); ?></div>
        </div>
    </div>

    
    <div id="modal-diff" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Detail Perubahan</h3>
                <button onclick="document.getElementById('modal-diff').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <div id="diff-body" class="p-6"></div>
        </div>
    </div>

    <?php
        // Prepare diff data for JS
        $diffData = $logs->mapWithKeys(fn($log) => [
            $log->id => [
                'old' => $log->old_values,
                'new' => $log->new_values,
                'action' => $log->action,
                'is_ai' => $log->is_ai_action,
            ]
        ]);
    ?>

    <script>
    const diffData = <?php echo json_encode($diffData, 15, 512) ?>;

    function openDiff(id) {
        const data = diffData[id];
        if (!data) return;

        const body = document.getElementById('diff-body');
        const oldV = data.old || {};
        const newV = data.new || {};

        // For AI actions: old = args, new = result
        if (data.is_ai) {
            body.innerHTML = renderSection('📥 Input (Args)', oldV) + renderSection('📤 Output (Result)', newV);
        } else {
            // For regular actions: show side-by-side diff
            const allKeys = [...new Set([...Object.keys(oldV), ...Object.keys(newV)])].sort();

            if (allKeys.length === 0) {
                body.innerHTML = '<p class="text-sm text-gray-400 text-center py-4">Tidak ada data perubahan.</p>';
            } else {
                let html = '<table class="w-full text-xs"><thead><tr class="text-gray-500 dark:text-slate-400 border-b border-gray-100 dark:border-white/10"><th class="text-left py-2 px-2">Field</th><th class="text-left py-2 px-2">Sebelum</th><th class="text-left py-2 px-2">Sesudah</th></tr></thead><tbody>';

                for (const key of allKeys) {
                    const ov = formatVal(oldV[key]);
                    const nv = formatVal(newV[key]);
                    const changed = ov !== nv;
                    const rowClass = changed ? 'bg-amber-50 dark:bg-amber-500/5' : '';

                    html += `<tr class="${rowClass} border-b border-gray-50 dark:border-white/5">
                        <td class="py-2 px-2 font-medium text-gray-700 dark:text-slate-300 whitespace-nowrap">${key}</td>
                        <td class="py-2 px-2 ${changed ? 'text-red-500 line-through' : 'text-gray-500 dark:text-slate-400'}">${ov || '<span class="text-gray-300">—</span>'}</td>
                        <td class="py-2 px-2 ${changed ? 'text-green-500 font-medium' : 'text-gray-500 dark:text-slate-400'}">${nv || '<span class="text-gray-300">—</span>'}</td>
                    </tr>`;
                }

                html += '</tbody></table>';
                body.innerHTML = html;
            }
        }

        document.getElementById('modal-diff').classList.remove('hidden');
    }

    function renderSection(title, data) {
        if (!data || Object.keys(data).length === 0) return '';
        let html = `<p class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase mb-2">${title}</p>`;
        html += '<div class="bg-gray-50 dark:bg-white/5 rounded-xl p-3 mb-4 font-mono text-xs text-gray-700 dark:text-slate-300 overflow-auto max-h-48">';
        html += '<pre>' + JSON.stringify(data, null, 2) + '</pre></div>';
        return html;
    }

    function formatVal(v) {
        if (v === null || v === undefined) return '';
        if (typeof v === 'object') return JSON.stringify(v);
        return String(v);
    }
    </script>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\audit\index.blade.php ENDPATH**/ ?>