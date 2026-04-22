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
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
                🤖 Predictive MRP - AI Demand Forecasting
            </h2>
            <div class="flex gap-2">
                <form method="POST" action="<?php echo e(route('manufacturing.mrp.predictive.refresh')); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="months" value="<?php echo e($months); ?>">
                    <?php if($productId): ?>
                        <input type="hidden" name="product_id" value="<?php echo e($productId); ?>">
                    <?php endif; ?>
                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        🔄 Refresh Forecast
                    </button>
                </form>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <form method="GET" class="flex gap-4 flex-wrap">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium mb-1">Forecast Period</label>
                        <select name="months" onchange="this.form.submit()" class="w-full border rounded-lg px-3 py-2">
                            <option value="1" <?php echo e($months == 1 ? 'selected' : ''); ?>>1 Month</option>
                            <option value="3" <?php echo e($months == 3 ? 'selected' : ''); ?>>3 Months</option>
                            <option value="6" <?php echo e($months == 6 ? 'selected' : ''); ?>>6 Months</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium mb-1">Product Filter</label>
                        <select name="product_id" onchange="this.form.submit()"
                            class="w-full border rounded-lg px-3 py-2">
                            <option value="">All Products</option>
                            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($product->id); ?>" <?php echo e($productId == $product->id ? 'selected' : ''); ?>>
                                    <?php echo e($product->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </form>
            </div>

            
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl p-6 text-white">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-xl font-bold mb-2">AI Forecast Status</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                            <div>
                                <div class="text-sm opacity-80">Model</div>
                                <div class="font-bold">
                                    <?php echo e(($forecast['model'] ?? '') === 'gemini-2.5-flash' ? '🧠 Gemini AI' : '📊 Statistical'); ?>

                                </div>
                            </div>
                            <div>
                                <div class="text-sm opacity-80">Confidence</div>
                                <div class="font-bold"><?php echo e(ucfirst($forecast['confidence'] ?? 'medium')); ?></div>
                            </div>
                            <div>
                                <div class="text-sm opacity-80">Products Analyzed</div>
                                <div class="font-bold"><?php echo e($insights['total_products_analyzed'] ?? 0); ?></div>
                            </div>
                            <div>
                                <div class="text-sm opacity-80">Generated At</div>
                                <div class="font-bold text-sm">
                                    <?php echo e(\Carbon\Carbon::parse($forecast['generated_at'] ?? now())->format('d M Y H:i')); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <?php if(($insights['critical_stock_products'] ?? 0) > 0): ?>
                <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 rounded-r-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-400">
                                ⚠️ <?php echo e($insights['critical_stock_products']); ?> Products with Critical Stock
                            </h3>
                            <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                Immediate action required to prevent stockouts
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            
            <?php if(!empty($insights['recommendations'])): ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-4">💡 AI Recommendations</h3>
                    <ul class="space-y-2">
                        <?php $__currentLoopData = $insights['recommendations']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 mt-1">→</span>
                                <span class="text-gray-700 dark:text-slate-300"><?php echo e($rec); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            
            <?php if(!empty($forecast['forecast']['products'])): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php $__currentLoopData = $forecast['forecast']['products']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div
                            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6 hover:shadow-lg transition-shadow">
                            
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white"><?php echo e($product['product_name']); ?>

                                    </h4>
                                    <p class="text-xs text-gray-500">ID: <?php echo e($product['product_id']); ?></p>
                                </div>
                                <?php
                                    $urgencyColor = match ($product['reorder_urgency']) {
                                        'critical' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
                                        'high'
                                            => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                                        'medium'
                                            => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400',
                                        default
                                            => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
                                    };
                                ?>
                                <span class="px-2 py-1 rounded-full text-xs font-bold <?php echo e($urgencyColor); ?>">
                                    <?php echo e(strtoupper($product['reorder_urgency'])); ?>

                                </span>
                            </div>

                            
                            <div class="mb-4 p-3 bg-gray-50 dark:bg-white/5 rounded-lg">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-500">Current Stock:</span>
                                    <span class="font-bold"><?php echo e(number_format($product['current_stock'], 0)); ?></span>
                                </div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-500">Forecasted Demand:</span>
                                    <span
                                        class="font-bold"><?php echo e(number_format($product['total_forecasted_demand'], 0)); ?></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Stock Status:</span>
                                    <span
                                        class="font-bold <?php echo e($product['stock_status'] === 'sufficient' ? 'text-green-600' : 'text-red-600'); ?>">
                                        <?php echo e(ucfirst($product['stock_status'])); ?>

                                    </span>
                                </div>
                            </div>

                            
                            <canvas id="chart-<?php echo e($product['product_id']); ?>" height="150" class="mb-4"></canvas>

                            
                            <div
                                class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                <div class="text-xs text-blue-600 dark:text-blue-400 font-semibold mb-1">RECOMMENDED
                                    ACTION:</div>
                                <div class="text-sm text-gray-700 dark:text-slate-300">
                                    Order
                                    <strong><?php echo e(number_format($product['recommended_order_quantity'], 0)); ?></strong>
                                    units by
                                    <strong><?php echo e(\Carbon\Carbon::parse($product['recommended_order_date'])->format('d M Y')); ?></strong>
                                </div>
                            </div>

                            
                            <div class="mt-3 text-xs text-gray-500 dark:text-slate-400 italic">
                                <?php echo e(Str::limit($product['reasoning'], 100)); ?>

                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-12 text-center">
                    <div class="text-6xl mb-4">📊</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Forecast Data Available</h3>
                    <p class="text-gray-500 dark:text-slate-400 mb-4">
                        Insufficient historical sales data. Need at least 3 months of completed sales orders.
                    </p>
                    <p class="text-sm text-gray-400">
                        Message: <?php echo e($forecast['message'] ?? 'Click "Refresh Forecast" to try again'); ?>

                    </p>
                </div>
            <?php endif; ?>

            
            <?php if(!empty($insights['risk_factors'])): ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-4">⚠️ Risk Factors</h3>
                    <ul class="space-y-2">
                        <?php $__currentLoopData = $insights['risk_factors']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $risk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-start gap-2">
                                <span class="text-orange-500 mt-1">⚠</span>
                                <span class="text-gray-700 dark:text-slate-300"><?php echo e($risk); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>

    
    <?php if(!empty($forecast['forecast']['products'])): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            <?php $__currentLoopData = $forecast['forecast']['products']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                new Chart(document.getElementById('chart-<?php echo e($product['product_id']); ?>'), {
                    type: 'bar',
                    data: {
                        labels: [
                            <?php $__currentLoopData = $product['forecasted_demand']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month => $demand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                'Month <?php echo e(str_replace('month_', '', $month)); ?>',
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        ],
                        datasets: [{
                            label: 'Forecasted Demand',
                            data: [<?php echo e(implode(',', array_values($product['forecasted_demand']))); ?>],
                            backgroundColor: 'rgba(99, 102, 241, 0.7)',
                            borderColor: 'rgb(99, 102, 241)',
                            borderWidth: 2,
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </script>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\manufacturing\predictive-mrp.blade.php ENDPATH**/ ?>