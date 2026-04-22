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
     <?php $__env->slot('header', null, []); ?> Laporan Laboratorium <?php $__env->endSlot(); ?>

    
    <?php if (isset($component)) { $__componentOriginal360d002b1b676b6f84d43220f22129e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal360d002b1b676b6f84d43220f22129e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumbs','data' => ['items' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Laboratorium', 'url' => route('healthcare.laboratory.orders')],
        ['label' => 'Laporan'],
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Laboratorium', 'url' => route('healthcare.laboratory.orders')],
        ['label' => 'Laporan'],
    ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $attributes = $__attributesOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__attributesOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $component = $__componentOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__componentOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>

    <?php $tid = auth()->user()->tenant_id; ?>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 mb-6">
        <div class="p-4">
            <form method="GET" class="flex flex-col lg:flex-row gap-3">
                <input type="date" name="start_date" value="<?php echo e(request('start_date')); ?>"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                <input type="date" name="end_date" value="<?php echo e(request('end_date')); ?>"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                <select name="test_type"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">Semua Jenis Test</option>
                    <option value="blood_test" <?php if(request('test_type') === 'blood_test'): echo 'selected'; endif; ?>>Blood Test</option>
                    <option value="urine_test" <?php if(request('test_type') === 'urine_test'): echo 'selected'; endif; ?>>Urine Test</option>
                    <option value="cbc" <?php if(request('test_type') === 'cbc'): echo 'selected'; endif; ?>>CBC</option>
                    <option value="liver_function" <?php if(request('test_type') === 'liver_function'): echo 'selected'; endif; ?>>Liver Function</option>
                    <option value="kidney_function" <?php if(request('test_type') === 'kidney_function'): echo 'selected'; endif; ?>>Kidney Function</option>
                </select>
                <select name="status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="completed" <?php if(request('status') === 'completed'): echo 'selected'; endif; ?>>Completed</option>
                    <option value="pending" <?php if(request('status') === 'pending'): echo 'selected'; endif; ?>>Pending</option>
                </select>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Generate</button>
            </form>
        </div>
    </div>

    
    <?php if(request('start_date') || request('end_date')): ?>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <?php
                $query = \App\Models\LabOrder::where('tenant_id', $tid);
                if (request('start_date')) {
                    $query->whereDate('order_date', '>=', request('start_date'));
                }
                if (request('end_date')) {
                    $query->whereDate('order_date', '<=', request('end_date'));
                }
                if (request('test_type')) {
                    $query->where('test_type', request('test_type'));
                }
                if (request('status')) {
                    $query->where('status', request('status'));
                }
                $reports = $query->orderBy('order_date', 'desc')->get();

                $totalTests = $reports->count();
                $completedTests = $reports->where('status', 'completed')->count();
                $pendingTests = $reports->where('status', 'pending')->count();
                $abnormalResults = $reports
                    ->where('status', 'completed')
                    ->filter(function ($r) {
                        return isset($r->results['abnormal']) && $r->results['abnormal'];
                    })
                    ->count();
            ?>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
                <p class="text-xs text-gray-500 dark:text-slate-400">Total Test</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo e(number_format($totalTests)); ?></p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
                <p class="text-xs text-gray-500 dark:text-slate-400">Selesai</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1"><?php echo e($completedTests); ?></p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
                <p class="text-xs text-gray-500 dark:text-slate-400">Pending</p>
                <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1"><?php echo e($pendingTests); ?></p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
                <p class="text-xs text-gray-500 dark:text-slate-400">Abnormal</p>
                <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1"><?php echo e($abnormalResults); ?></p>
            </div>
        </div>

        
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Hasil Laporan</h3>
                <div class="flex items-center gap-2">
                    <button onclick="exportPDF()"
                        class="px-4 py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700">
                        Export PDF
                    </button>
                    <button onclick="exportExcel()"
                        class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">
                        Export Excel
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">No. Order</th>
                            <th class="px-4 py-3 text-left">Pasien</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">Jenis Test</th>
                            <th class="px-4 py-3 text-left hidden lg:table-cell">Tanggal</th>
                            <th class="px-4 py-3 text-center hidden sm:table-cell">Hasil</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__empty_1 = true; $__currentLoopData = $reports ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-4 py-3">
                                    <span
                                        class="font-mono text-sm font-bold text-blue-600 dark:text-blue-400"><?php echo e($report->order_number ?? '-'); ?></span>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        <?php echo e($report->patient ? $report->patient->full_name : '-'); ?></p>
                                    <p class="text-xs text-gray-500 dark:text-slate-400">
                                        <?php echo e($report->patient ? $report->patient->medical_record_number : '-'); ?></p>
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell">
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                                        <?php echo e(str_replace('_', ' ', ucfirst($report->test_type ?? '-'))); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-slate-300 hidden lg:table-cell">
                                    <?php echo e($report->order_date ? \Carbon\Carbon::parse($report->order_date)->format('d M Y') : '-'); ?>

                                </td>
                                <td class="px-4 py-3 text-center hidden sm:table-cell">
                                    <?php if($report->status === 'completed' && isset($report->results['abnormal'])): ?>
                                        <?php if($report->results['abnormal']): ?>
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Abnormal</span>
                                        <?php else: ?>
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Normal</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-500 dark:text-slate-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if($report->status === 'completed'): ?>
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Completed</span>
                                    <?php else: ?>
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="<?php echo e(route('healthcare.laboratory.reports.show', $report)); ?>"
                                            class="p-1.5 text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30 rounded-lg"
                                            title="Lihat Laporan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                        </a>
                                        <button onclick="printReport(<?php echo e($report->id); ?>)"
                                            class="p-1.5 text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700 rounded-lg"
                                            title="Print">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-slate-400">
                                    <p>Pilih filter dan klik Generate untuk melihat laporan</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function exportPDF() {
                alert('Export PDF akan tersedia setelah backend diimplementasikan');
            }

            function exportExcel() {
                alert('Export Excel akan tersedia setelah backend diimplementasikan');
            }

            function printReport(id) {
                window.open(`/healthcare/laboratory/reports/${id}/print`, '_blank');
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\laboratory\reports.blade.php ENDPATH**/ ?>