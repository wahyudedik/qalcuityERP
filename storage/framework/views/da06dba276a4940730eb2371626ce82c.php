

<?php $__env->startSection('title', 'Competitor Rate Tracking'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Competitor Rate Tracking</h1>
                <p class="text-gray-600">Monitor and analyze competitor pricing strategies</p>
            </div>
            <button onclick="document.getElementById('addRateModal').classList.remove('hidden')"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                + Add Competitor Rate
            </button>
        </div>

        <!-- Competitors Summary -->
        <?php if(isset($analysis) && isset($analysis['competitors'])): ?>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-600">Competitors Tracked</div>
                    <div class="text-2xl font-bold text-blue-600"><?php echo e($analysis['competitor_count']); ?></div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-600">Market Average</div>
                    <div class="text-2xl font-bold text-green-600">$<?php echo e(number_format($analysis['market_average'], 2)); ?>

                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-600">Data Points</div>
                    <div class="text-2xl font-bold text-purple-600"><?php echo e($analysis['total_data_points']); ?></div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-600">Period</div>
                    <div class="text-lg font-bold text-gray-800">
                        <?php echo e(\Carbon\Carbon::parse($analysis['period']['start'])->format('M d')); ?> -
                        <?php echo e(\Carbon\Carbon::parse($analysis['period']['end'])->format('M d')); ?>

                    </div>
                </div>
            </div>

            <!-- Competitor Analysis -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-4 py-3 border-b">
                    <h3 class="font-semibold text-gray-800">Competitor Analysis</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Competitor</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Avg Rate</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Lowest</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Highest</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Range</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Trend</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Data Points</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php $__currentLoopData = $analysis['competitors']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $competitorName => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium"><?php echo e($competitorName); ?></td>
                                    <td class="px-4 py-3 text-center">$<?php echo e(number_format($data['average_rate'], 2)); ?></td>
                                    <td class="px-4 py-3 text-center text-green-600">
                                        $<?php echo e(number_format($data['lowest_rate'], 2)); ?></td>
                                    <td class="px-4 py-3 text-center text-red-600">
                                        $<?php echo e(number_format($data['highest_rate'], 2)); ?></td>
                                    <td class="px-4 py-3 text-center">$<?php echo e(number_format($data['rate_range'], 2)); ?></td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if($data['trend']['direction'] === 'increasing'): ?>
                                            <span class="text-green-600">↑
                                                <?php echo e(number_format($data['trend']['change_percentage'], 1)); ?>%</span>
                                        <?php elseif($data['trend']['direction'] === 'decreasing'): ?>
                                            <span class="text-red-600">↓
                                                <?php echo e(number_format(abs($data['trend']['change_percentage']), 1)); ?>%</span>
                                        <?php else: ?>
                                            <span class="text-gray-500">→ Stable</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center"><?php echo e($data['data_points']); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Positioning Report -->
        <?php if(isset($positioning) && isset($positioning['positioning'])): ?>
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-4 py-3 border-b">
                    <h3 class="font-semibold text-gray-800">Competitive Positioning</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Room Type</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Our Rate</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Market Avg</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Position</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Recommendation</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php $__currentLoopData = $positioning['positioning']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $roomType => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium"><?php echo e($roomType); ?></td>
                                    <td class="px-4 py-3 text-center">$<?php echo e(number_format($data['our_rate'], 2)); ?></td>
                                    <td class="px-4 py-3 text-center">$<?php echo e(number_format($data['market_average'], 2)); ?></td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if($data['position_vs_market'] > 10): ?>
                                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">Premium
                                                (+<?php echo e(number_format($data['position_vs_market'], 1)); ?>%)</span>
                                        <?php elseif($data['position_vs_market'] < -10): ?>
                                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Discount
                                                (<?php echo e(number_format($data['position_vs_market'], 1)); ?>%)</span>
                                        <?php else: ?>
                                            <span
                                                class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs">Competitive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm"><?php echo e($data['recommendation']); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Recent Rates -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b">
                <h3 class="font-semibold text-gray-800">Recent Rate Entries</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Competitor</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Room Type</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Rate</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Source</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Recorded</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php $__empty_1 = true; $__currentLoopData = $recentRates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3"><?php echo e($rate->rate_date->format('M d, Y')); ?></td>
                                <td class="px-4 py-3 font-medium"><?php echo e($rate->competitor_name); ?></td>
                                <td class="px-4 py-3"><?php echo e($rate->room_type ?? 'N/A'); ?></td>
                                <td class="px-4 py-3 text-center font-medium">$<?php echo e(number_format($rate->rate, 2)); ?></td>
                                <td class="px-4 py-3"><?php echo e(ucfirst($rate->source)); ?></td>
                                <td class="px-4 py-3 text-sm text-gray-500"><?php echo e($rate->created_at->diffForHumans()); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    No competitor rates recorded yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Rate Modal -->
    <div id="addRateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-lg max-w-lg w-full">
                <div class="px-6 py-4 border-b flex justify-between items-center">
                    <h3 class="text-lg font-semibold">Add Competitor Rate</h3>
                    <button onclick="document.getElementById('addRateModal').classList.add('hidden')"
                        class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form action="<?php echo e(route('revenue.competitor-rates.store')); ?>" method="POST" class="p-6">
                    <?php echo csrf_field(); ?>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Competitor Name</label>
                            <input type="text" name="competitor_name" required class="w-full border rounded px-3 py-2"
                                placeholder="e.g., Hotel Grand">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Source</label>
                            <select name="source" required class="w-full border rounded px-3 py-2">
                                <option value="manual">Manual Entry</option>
                                <option value="bookingcom">Booking.com</option>
                                <option value="expedia">Expedia</option>
                                <option value="agoda">Agoda</option>
                                <option value="website">Competitor Website</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rate Date</label>
                            <input type="date" name="rate_date" required class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rate ($)</label>
                            <input type="number" name="rate" step="0.01" min="0" required
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Room Type</label>
                            <input type="text" name="room_type" class="w-full border rounded px-3 py-2"
                                placeholder="e.g., Standard Room">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('addRateModal').classList.add('hidden')"
                            class="px-4 py-2 border rounded hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Add
                            Rate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\revenue\competitor-rates.blade.php ENDPATH**/ ?>