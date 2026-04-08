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
     <?php $__env->slot('header', null, []); ?> <?php echo e($simulation->name); ?> <?php $__env->endSlot(); ?>

    <?php $__env->startPush('head'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <?php $__env->stopPush(); ?>

    <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <?php
            $results = $simulation->results ?? [];
            $fmt = fn($n) => 'Rp ' . number_format(abs($n ?? 0), 0, ',', '.');
            $labels = [
                'price_increase' => '📈 Kenaikan Harga',
                'new_branch'     => '🏪 Cabang Baru',
                'stock_out'      => '📦 Stok Habis',
                'cost_reduction' => '✂️ Efisiensi Biaya',
                'demand_change'  => '📊 Perubahan Demand',
            ];
        ?>

        
        <div class="flex items-center gap-3 flex-wrap">
            <span class="px-3 py-1.5 bg-indigo-500/10 text-indigo-400 text-sm rounded-full border border-indigo-500/20 font-medium">
                <?php echo e($labels[$simulation->scenario_type] ?? $simulation->scenario_type); ?>

            </span>
            <span class="text-xs text-gray-400 dark:text-slate-500"><?php echo e($simulation->created_at->translatedFormat('d M Y H:i')); ?></span>
        </div>

        <!-- AI Narrative -->
        <?php if($simulation->ai_narrative): ?>
            <div class="bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/20 rounded-2xl p-5">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-indigo-500/20 flex items-center justify-center text-lg shrink-0">🤖</div>
                    <div>
                        <p class="font-semibold text-indigo-800 dark:text-indigo-300 text-sm mb-1">Analisis AI</p>
                        <p class="text-sm text-indigo-700 dark:text-indigo-300/80 leading-relaxed whitespace-pre-line"><?php echo e($simulation->ai_narrative); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <?php
            $chartBefore = [];
            $chartAfter  = [];
            $chartLabels = [];

            if ($simulation->scenario_type === 'price_increase') {
                $chartLabels = ['Pendapatan', 'Demand (unit)'];
                $chartBefore = [$results['current_revenue'] ?? 0, $results['current_orders'] ?? 0];
                $chartAfter  = [$results['projected_revenue_with_elasticity'] ?? 0, round(($results['current_orders'] ?? 0) * (1 + ($results['demand_change_pct'] ?? 0) / 100))];
            } elseif ($simulation->scenario_type === 'cost_reduction') {
                $chartLabels = ['Pengeluaran', 'Laba Bersih'];
                $chartBefore = [$results['total_expense'] ?? 0, $results['current_profit'] ?? 0];
                $chartAfter  = [($results['total_expense'] ?? 0) - ($results['saved_cost'] ?? 0), $results['new_profit'] ?? 0];
            } elseif ($simulation->scenario_type === 'demand_change') {
                $chartLabels = ['Pendapatan', 'Jumlah Order'];
                $chartBefore = [$results['current_revenue'] ?? 0, $results['current_orders'] ?? 0];
                $chartAfter  = [$results['projected_revenue'] ?? 0, $results['projected_orders'] ?? 0];
            } elseif ($simulation->scenario_type === 'new_branch') {
                $months = $results['months'] ?? 12;
                $chartLabels = ['Biaya Total', 'Omzet Total', 'Laba Bersih'];
                $chartBefore = [0, 0, 0];
                $chartAfter  = [($results['fixed_cost_monthly'] ?? 0) * $months, ($results['revenue_projection'] ?? 0) * $months, $results['net_profit'] ?? 0];
            } elseif ($simulation->scenario_type === 'stock_out') {
                $chartLabels = ['Omzet Normal', 'Kehilangan Omzet'];
                $chartBefore = [($results['total_lost_revenue'] ?? 0) + ($results['daily_lost'] ?? 0) * ($simulation->parameters['days'] ?? 30), 0];
                $chartAfter  = [($results['daily_lost'] ?? 0) * ($simulation->parameters['days'] ?? 30), $results['total_lost_revenue'] ?? 0];
            }
        ?>

        <?php if(!empty($chartLabels)): ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-4">Perbandingan Sebelum vs Sesudah</h3>
            <div class="h-64">
                <canvas id="comparison-chart"></canvas>
            </div>
        </div>
        <?php endif; ?>

        <!-- Detail Results -->
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Detail Hasil Simulasi</h3>

            <?php if($simulation->scenario_type === 'price_increase'): ?>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Pendapatan Saat Ini</span>
                        <span class="font-medium"><?php echo e($fmt($results['current_revenue'] ?? 0)); ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Proyeksi (tanpa elastisitas)</span>
                        <span class="font-medium text-green-600"><?php echo e($fmt($results['projected_revenue_no_elasticity'] ?? 0)); ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Proyeksi (dengan elastisitas harga)</span>
                        <span class="font-medium text-blue-600"><?php echo e($fmt($results['projected_revenue_with_elasticity'] ?? 0)); ?></span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600 dark:text-gray-400">Estimasi Perubahan Demand</span>
                        <span class="font-medium <?php echo e(($results['demand_change_pct'] ?? 0) < 0 ? 'text-red-500' : 'text-green-500'); ?>">
                            <?php echo e($results['demand_change_pct'] ?? 0); ?>%
                        </span>
                    </div>
                </div>

            <?php elseif($simulation->scenario_type === 'new_branch'): ?>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Biaya Tetap/Bulan</span>
                        <span class="font-medium"><?php echo e($fmt($results['fixed_cost_monthly'] ?? 0)); ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Proyeksi Omzet/Bulan</span>
                        <span class="font-medium"><?php echo e($fmt($results['revenue_projection'] ?? 0)); ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Laba Bersih (<?php echo e($results['months'] ?? 12); ?> bulan)</span>
                        <span class="font-medium <?php echo e(($results['net_profit'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-500'); ?>">
                            <?php echo e(($results['net_profit'] ?? 0) >= 0 ? '+' : '-'); ?><?php echo e($fmt($results['net_profit'] ?? 0)); ?>

                        </span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600 dark:text-gray-400">Break-even</span>
                        <span class="font-medium"><?php echo e($results['break_even_months'] ?? '-'); ?> bulan</span>
                    </div>
                </div>

            <?php elseif($simulation->scenario_type === 'stock_out'): ?>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Total Potensi Kehilangan Omzet</span>
                        <span class="font-medium text-red-500"><?php echo e($fmt($results['total_lost_revenue'] ?? 0)); ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Rata-rata Kehilangan/Hari</span>
                        <span class="font-medium"><?php echo e($fmt($results['daily_lost'] ?? 0)); ?></span>
                    </div>
                    <?php if(!empty($results['products'])): ?>
                        <div class="mt-3">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Produk yang Terdampak:</p>
                            <table class="w-full text-xs">
                                <thead><tr class="text-gray-500 dark:text-gray-400">
                                    <th class="text-left py-1">Produk</th>
                                    <th class="text-right py-1">Qty</th>
                                    <th class="text-right py-1">Omzet</th>
                                </tr></thead>
                                <tbody>
                                    <?php $__currentLoopData = $results['products']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="border-t border-gray-100 dark:border-gray-700">
                                            <td class="py-1"><?php echo e($p['name']); ?></td>
                                            <td class="text-right py-1"><?php echo e(number_format($p['qty'])); ?></td>
                                            <td class="text-right py-1"><?php echo e($fmt($p['revenue'])); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif($simulation->scenario_type === 'cost_reduction'): ?>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Total Pengeluaran</span>
                        <span class="font-medium"><?php echo e($fmt($results['total_expense'] ?? 0)); ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Penghematan Biaya</span>
                        <span class="font-medium text-green-600"><?php echo e($fmt($results['saved_cost'] ?? 0)); ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Laba Sebelum Efisiensi</span>
                        <span class="font-medium <?php echo e(($results['current_profit'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-500'); ?>">
                            <?php echo e($fmt($results['current_profit'] ?? 0)); ?>

                        </span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600 dark:text-gray-400">Laba Setelah Efisiensi</span>
                        <span class="font-medium <?php echo e(($results['new_profit'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-500'); ?>">
                            <?php echo e($fmt($results['new_profit'] ?? 0)); ?>

                        </span>
                    </div>
                </div>

            <?php elseif($simulation->scenario_type === 'demand_change'): ?>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Pendapatan Saat Ini</span>
                        <span class="font-medium"><?php echo e($fmt($results['current_revenue'] ?? 0)); ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Proyeksi Pendapatan</span>
                        <span class="font-medium <?php echo e(($results['projected_revenue'] ?? 0) >= ($results['current_revenue'] ?? 0) ? 'text-green-600' : 'text-red-500'); ?>">
                            <?php echo e($fmt($results['projected_revenue'] ?? 0)); ?>

                        </span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Order Saat Ini</span>
                        <span class="font-medium"><?php echo e(number_format($results['current_orders'] ?? 0)); ?></span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600 dark:text-gray-400">Proyeksi Order</span>
                        <span class="font-medium"><?php echo e(number_format($results['projected_orders'] ?? 0)); ?></span>
                    </div>
                    <?php if(!empty($results['stock_note'])): ?>
                        <div class="mt-2 p-3 bg-yellow-50 dark:bg-yellow-900/30 rounded-lg text-xs text-yellow-700 dark:text-yellow-300">
                            📦 <?php echo e($results['stock_note']); ?>

                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Parameters -->
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-3 text-sm">Parameter Input</h3>
            <div class="grid grid-cols-2 gap-2 text-sm">
                <?php $__currentLoopData = $simulation->parameters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex justify-between py-1.5 border-b border-gray-100 dark:border-white/5">
                        <span class="text-gray-500 dark:text-slate-400 capitalize"><?php echo e(str_replace('_', ' ', $key)); ?></span>
                        <span class="font-medium text-gray-900 dark:text-white"><?php echo e(is_array($val) ? implode(', ', $val) : $val); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="flex justify-between">
            <a href="<?php echo e(route('simulations.index')); ?>" class="text-sm text-gray-400 hover:text-gray-600 dark:hover:text-white">← Kembali ke daftar</a>
            <form method="POST" action="<?php echo e(route('simulations.destroy', $simulation)); ?>" onsubmit="return confirm('Hapus simulasi ini?')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button class="text-sm text-red-400 hover:text-red-600">Hapus simulasi</button>
            </form>
        </div>
    </div>

    <?php if(!empty($chartLabels)): ?>
    <?php $__env->startPush('scripts'); ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('comparison-chart');
        if (!ctx) return;

        const isDark = document.documentElement.classList.contains('dark');
        const labels = <?php echo json_encode($chartLabels, 15, 512) ?>;
        const before = <?php echo json_encode($chartBefore, 15, 512) ?>;
        const after  = <?php echo json_encode($chartAfter, 15, 512) ?>;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Sebelum',
                        data: before,
                        backgroundColor: isDark ? 'rgba(148,163,184,0.4)' : 'rgba(148,163,184,0.6)',
                        borderColor: 'rgba(148,163,184,0.8)',
                        borderWidth: 1,
                        borderRadius: 6,
                    },
                    {
                        label: 'Sesudah (Proyeksi)',
                        data: after,
                        backgroundColor: isDark ? 'rgba(99,102,241,0.5)' : 'rgba(99,102,241,0.7)',
                        borderColor: 'rgba(99,102,241,0.9)',
                        borderWidth: 1,
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: isDark ? '#94a3b8' : '#64748b', font: { size: 11 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const v = ctx.raw;
                                if (Math.abs(v) >= 1000) return ctx.dataset.label + ': Rp ' + Math.round(v).toLocaleString('id-ID');
                                return ctx.dataset.label + ': ' + v;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: isDark ? '#475569' : '#94a3b8',
                            callback: function(v) {
                                if (Math.abs(v) >= 1000000) return 'Rp ' + (v/1000000).toFixed(1) + 'jt';
                                if (Math.abs(v) >= 1000) return 'Rp ' + (v/1000).toFixed(0) + 'rb';
                                return v;
                            }
                        },
                        grid: { color: isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        ticks: { color: isDark ? '#94a3b8' : '#64748b', font: { size: 11 } },
                        grid: { display: false }
                    }
                }
            }
        });
    });
    </script>
    <?php $__env->stopPush(); ?>
    <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/simulations/show.blade.php ENDPATH**/ ?>