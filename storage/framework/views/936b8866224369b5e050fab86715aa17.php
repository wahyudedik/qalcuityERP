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
        <div class="flex items-center justify-between">
            <span>RFQ Response Analysis</span>
            <a href="<?php echo e(url()->previous()); ?>"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>
     <?php $__env->endSlot(); ?>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Total Responses</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo e($analysis['total_responses']); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Price Range</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
                    Rp <?php echo e(number_format($analysis['price_range']['lowest'], 0, ',', '.')); ?> -
                    Rp <?php echo e(number_format($analysis['price_range']['highest'], 0, ',', '.')); ?>

                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Avg Lead Time</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo e(round($analysis['avg_lead_time'])); ?>

                    days</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Recommended Supplier</p>
                <p class="text-lg font-semibold text-green-600 dark:text-green-400 mt-1">
                    <?php echo e($analysis['recommended_supplier'] ?? 'N/A'); ?></p>
            </div>
        </div>
    </div>

    
    <div
        class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-gray-100 dark:border-white/5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Supplier Rankings (Scored)</h3>
        </div>

        <?php if(count($analysis['scored_responses']) === 0): ?>
            <div class="py-16 text-center">
                <svg class="mx-auto w-10 h-10 text-gray-300 dark:text-slate-600 mb-3" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada response untuk RFQ ini.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Rank</th>
                            <th class="px-4 py-3 text-left">Supplier</th>
                            <th class="px-4 py-3 text-left">Quoted Price</th>
                            <th class="px-4 py-3 text-left">Lead Time</th>
                            
                            <th class="px-4 py-3 text-left hidden lg:table-cell">Rating</th>
                            <th class="px-4 py-3 text-left hidden xl:table-cell">Delivery</th>
                            <th class="px-4 py-3 text-left hidden xl:table-cell">Payment</th>
                            
                            <th class="px-4 py-3 text-left">Price Score</th>
                            <th class="px-4 py-3 text-left">Time Score</th>
                            <th class="px-4 py-3 text-left">Overall</th>
                            <th class="px-4 py-3 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__currentLoopData = $analysis['scored_responses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $scoredResponse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $response = $scoredResponse['response'];
                                $isRecommended = $index === 0;
                            ?>
                            <tr
                                class="hover:bg-gray-50 dark:hover:bg-white/5 transition <?php echo e($isRecommended ? 'bg-green-50 dark:bg-green-500/10' : ''); ?>">
                                <td class="px-4 py-3">
                                    <?php if($isRecommended): ?>
                                        <span
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-500 text-white font-bold">1</span>
                                    <?php else: ?>
                                        <span
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold"><?php echo e($index + 1); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            <?php echo e($response->supplier->name); ?></p>
                                        <?php if($response->notes): ?>
                                            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                                                <?php echo e(Str::limit($response->notes, 50)); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-gray-900 dark:text-white">Rp
                                        <?php echo e(number_format($response->quoted_price, 0, ',', '.')); ?></span>
                                    <?php if($response->quoted_price === $analysis['price_range']['lowest']): ?>
                                        <span
                                            class="ml-2 px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">Lowest</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-slate-400"><?php echo e($response->lead_time_days); ?>

                                    days</td>
                                
                                <td class="px-4 py-3 hidden lg:table-cell">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 w-12">
                                            <div class="bg-yellow-600 h-2 rounded-full"
                                                style="width: <?php echo e($scoredResponse['supplier_rating_score']); ?>%"></div>
                                        </div>
                                        <span
                                            class="text-xs font-medium"><?php echo e(number_format($scoredResponse['supplier_rating_score'], 0)); ?></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 hidden xl:table-cell">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 w-12">
                                            <div class="bg-orange-600 h-2 rounded-full"
                                                style="width: <?php echo e($scoredResponse['delivery_performance_score']); ?>%">
                                            </div>
                                        </div>
                                        <span
                                            class="text-xs font-medium"><?php echo e(number_format($scoredResponse['delivery_performance_score'], 0)); ?></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 hidden xl:table-cell">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 w-12">
                                            <div class="bg-teal-600 h-2 rounded-full"
                                                style="width: <?php echo e($scoredResponse['payment_terms_score']); ?>%"></div>
                                        </div>
                                        <span
                                            class="text-xs font-medium"><?php echo e(number_format($scoredResponse['payment_terms_score'], 0)); ?></span>
                                    </div>
                                </td>
                                
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 w-16">
                                            <div class="bg-blue-600 h-2 rounded-full"
                                                style="width: <?php echo e($scoredResponse['price_score']); ?>%"></div>
                                        </div>
                                        <span
                                            class="text-xs font-medium"><?php echo e(number_format($scoredResponse['price_score'], 0)); ?></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 w-16">
                                            <div class="bg-purple-600 h-2 rounded-full"
                                                style="width: <?php echo e($scoredResponse['lead_time_score']); ?>%"></div>
                                        </div>
                                        <span
                                            class="text-xs font-medium"><?php echo e(number_format($scoredResponse['lead_time_score'], 0)); ?></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 w-20>
                                            <div class="bg-green-600
                                            h-2 rounded-full" style="width: <?php echo e($scoredResponse['overall_score']); ?>%">
                                        </div>
                                    </div>
                                    <span
                                        class="text-sm font-bold text-gray-900 dark:text-white"><?php echo e(number_format($scoredResponse['overall_score'], 1)); ?></span>
            </div>
            </td>
            <td class="px-4 py-3">
                <?php if($isRecommended): ?>
                    <button
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs bg-green-600 text-white rounded-xl hover:bg-green-700 transition">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        Select Winner
                    </button>
                <?php else: ?>
                    <button
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        View Details
                    </button>
                <?php endif; ?>
            </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
        </table>
    </div>
    <?php endif; ?>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Scoring Methodology</h2>

        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="p-4 bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 rounded-xl">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h4 class="font-medium text-gray-900 dark:text-white">Price Score (40%)</h4>
                </div>
                <p class="text-sm text-gray-600 dark:text-slate-400">
                    Lower prices receive higher scores. Calculated as ratio to lowest quoted price.
                </p>
            </div>

            <div
                class="p-4 bg-purple-50 dark:bg-purple-500/10 border border-purple-200 dark:border-purple-500/30 rounded-xl">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h4 class="font-medium text-gray-900 dark:text-white">Lead Time Score (25%)</h4>
                </div>
                <p class="text-sm text-gray-600 dark:text-slate-400">
                    Shorter lead times are better. Compared against average lead time of all responses.
                </p>
            </div>

            <div
                class="p-4 bg-yellow-50 dark:bg-yellow-500/10 border border-yellow-200 dark:border-yellow-500/30 rounded-xl">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                    <h4 class="font-medium text-gray-900 dark:text-white">Supplier Rating (15%)</h4>
                </div>
                <p class="text-sm text-gray-600 dark:text-slate-400">
                    Based on historical scorecard ratings (quality, delivery, cost, service metrics).
                </p>
            </div>

            <div
                class="p-4 bg-orange-50 dark:bg-orange-500/10 border border-orange-200 dark:border-orange-500/30 rounded-xl">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16m-6 3v-1a1 1 0 00-1-1h-1m-1 0v1a1 1 0 001 1h1m4-3V9m0 0L9 12m0-3l3-3" />
                    </svg>
                    <h4 class="font-medium text-gray-900 dark:text-white">Delivery Performance (10%)</h4>
                </div>
                <p class="text-sm text-gray-600 dark:text-slate-400">
                    On-time delivery track record from historical purchase order data.
                </p>
            </div>

            <div class="p-4 bg-teal-50 dark:bg-teal-500/10 border border-teal-200 dark:border-teal-500/30 rounded-xl">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <h4 class="font-medium text-gray-900 dark:text-white">Payment Terms (10%)</h4>
                </div>
                <p class="text-sm text-gray-600 dark:text-slate-400">
                    Better payment terms score higher (NET 60+ best, COD excellent).
                </p>
            </div>
        </div>

        <div class="mt-4 p-4 bg-gray-50 dark:bg-[#0f172a] rounded-xl">
            <p class="text-xs text-gray-600 dark:text-slate-400 font-mono">
                Overall Score = (Price × 0.40) + (Lead Time × 0.25) + (Rating × 0.15) + (Delivery × 0.10) + (Payment ×
                0.10)
            </p>
        </div>
    </div>

    
    <?php if(count($analysis['scored_responses']) > 1): ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Price Comparison</h3>

            <div class="space-y-3">
                <?php $__currentLoopData = $analysis['scored_responses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $scoredResponse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $response = $scoredResponse['response'];
                        $maxPrice = $analysis['price_range']['highest'];
                        $barWidth = $maxPrice > 0 ? ($response->quoted_price / $maxPrice) * 100 : 0;
                    ?>
                    <div class="flex items-center gap-4">
                        <div class="w-32 text-sm text-gray-700 dark:text-slate-300 truncate">
                            <?php echo e($response->supplier->name); ?>

                        </div>
                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-6 relative">
                            <div class="bg-indigo-600 h-6 rounded-full flex items-center justify-end pr-2 transition-all"
                                style="width: <?php echo e(max($barWidth, 10)); ?>%">
                                <span class="text-xs text-white font-medium whitespace-nowrap">
                                    Rp <?php echo e(number_format($response->quoted_price, 0, ',', '.')); ?>

                                </span>
                            </div>
                        </div>
                        <div class="w-20 text-xs text-gray-500 dark:text-slate-400 text-right">
                            <?php echo e($response->lead_time_days); ?> days
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\suppliers\rfq-analysis.blade.php ENDPATH**/ ?>