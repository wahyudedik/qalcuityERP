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
     <?php $__env->slot('header', null, []); ?> Timesheet <?php $__env->endSlot(); ?>

    <div class="space-y-6">

        
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Total Jam (filter)</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo e(number_format($totalHours, 1)); ?> <span class="text-sm font-normal text-gray-400">jam</span></p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Total Biaya (filter)</p>
                <p class="text-2xl font-bold text-gray-900">Rp <?php echo e(number_format($totalCost, 0, ',', '.')); ?></p>
            </div>
        </div>

        
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Catat Waktu Kerja</h2>
            <form method="POST" action="<?php echo e(route('timesheets.store')); ?>"
                  class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Proyek *</label>
                    <select name="project_id" required
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:border-blue-500">
                        <option value="">Pilih proyek...</option>
                        <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($project->id); ?>"><?php echo e($project->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal *</label>
                    <input type="date" name="date" required value="<?php echo e(date('Y-m-d')); ?>" max="<?php echo e(date('Y-m-d')); ?>"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Jam *</label>
                    <input type="number" name="hours" required min="0.25" max="24" step="0.25" placeholder="8"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tarif/Jam (Rp)</label>
                    <input type="number" name="hourly_rate" min="0" step="1000" placeholder="0"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:border-blue-500">
                </div>
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Deskripsi *</label>
                    <input type="text" name="description" required placeholder="Pekerjaan yang dilakukan..."
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:border-blue-500">
                </div>
                <div class="sm:col-span-2 lg:col-span-5 flex justify-end">
                    <button type="submit"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl transition">
                        Simpan Entri
                    </button>
                </div>
            </form>
        </div>

        
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="project_id"
                class="bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:border-blue-500">
                <option value="">Semua Proyek</option>
                <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($project->id); ?>" <?php echo e(request('project_id') == $project->id ? 'selected' : ''); ?>><?php echo e($project->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php if($users->isNotEmpty()): ?>
            <select name="user_id"
                class="bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:border-blue-500">
                <option value="">Semua Anggota</option>
                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($user->id); ?>" <?php echo e(request('user_id') == $user->id ? 'selected' : ''); ?>><?php echo e($user->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php endif; ?>
            <input type="month" name="month" value="<?php echo e(request('month')); ?>"
                class="bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:border-blue-500">
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl transition">Filter</button>
            <?php if(request()->hasAny(['project_id','user_id','month'])): ?>
            <a href="<?php echo e(route('timesheets.index')); ?>" class="px-4 py-2 border border-gray-200 text-gray-600 text-sm font-medium rounded-xl hover:bg-gray-100 transition">Reset</a>
            <?php endif; ?>
        </form>

        
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <?php if($timesheets->isEmpty()): ?>
                <div class="px-6 py-16 text-center text-gray-400 text-sm">Belum ada entri timesheet.</div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Tanggal</th>
                            <th class="px-6 py-3 text-left">Proyek</th>
                            <th class="px-6 py-3 text-left hidden sm:table-cell">Anggota</th>
                            <th class="px-6 py-3 text-left">Deskripsi</th>
                            <th class="px-6 py-3 text-right">Jam</th>
                            <th class="px-6 py-3 text-right hidden md:table-cell">Biaya</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__currentLoopData = $timesheets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ts): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-gray-500 whitespace-nowrap"><?php echo e($ts->date->format('d M Y')); ?></td>
                            <td class="px-6 py-3 font-medium text-gray-900"><?php echo e($ts->project?->name ?? '-'); ?></td>
                            <td class="px-6 py-3 text-gray-500 hidden sm:table-cell"><?php echo e($ts->user?->name ?? '-'); ?></td>
                            <td class="px-6 py-3 text-gray-600 max-w-xs truncate"><?php echo e($ts->description); ?></td>
                            <td class="px-6 py-3 text-right font-medium text-gray-900"><?php echo e(number_format($ts->hours, 1)); ?></td>
                            <td class="px-6 py-3 text-right text-gray-500 hidden md:table-cell">
                                <?php if($ts->hourly_rate > 0): ?>
                                Rp <?php echo e(number_format($ts->laborCost(), 0, ',', '.')); ?>

                                <?php else: ?>
                                -
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <?php if(auth()->user()->hasRole(['admin','manager']) || $ts->user_id === auth()->id()): ?>
                                <form method="POST" action="<?php echo e(route('timesheets.destroy', $ts)); ?>"
                                      onsubmit="return confirm('Hapus entri ini?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit"
                                        class="text-red-400 hover:text-red-300 text-xs font-medium transition">Hapus</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-100">
                <?php echo e($timesheets->links()); ?>

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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\timesheets\index.blade.php ENDPATH**/ ?>