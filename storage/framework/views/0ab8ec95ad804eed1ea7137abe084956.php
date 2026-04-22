

<?php $__env->startSection('title', 'Yield Optimization'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Yield Optimization</h1>
                <p class="text-gray-600">Maximize revenue through strategic yield management</p>
            </div>
        </div>

        <!-- Overbooking Recommendation -->
        <?php if(isset($overbooking)): ?>
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-800">Overbooking Recommendation</h3>
                        <p class="text-sm text-gray-600 mt-1"><?php echo e($overbooking['recommendation']); ?></p>
                    </div>
                    <div class="text-right">
                        <div
                            class="text-2xl font-bold 
                    <?php echo e($overbooking['risk_level'] === 'high' ? 'text-red-600' : ($overbooking['risk_level'] === 'medium' ? 'text-yellow-600' : 'text-green-600')); ?>">
                            <?php echo e($overbooking['recommended_overbooking_percentage']); ?>%
                        </div>
                        <div class="text-sm text-gray-500">Cancellation rate:
                            <?php echo e($overbooking['average_cancellation_rate']); ?>%</div>
                    </div>
                </div>
                <div class="mt-3 p-3 bg-yellow-50 rounded text-sm text-yellow-800">
                    <strong>Caution:</strong> <?php echo e($overbooking['caution']); ?>

                </div>
            </div>
        <?php endif; ?>

        <!-- LOS Restrictions -->
        <?php if(isset($losRestrictions) && !empty($losRestrictions)): ?>
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-4 py-3 border-b">
                    <h3 class="font-semibold text-gray-800">Length of Stay Restrictions</h3>
                </div>
                <div class="p-4">
                    <div class="space-y-3">
                        <?php $__currentLoopData = $losRestrictions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $restriction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                                <div>
                                    <span
                                        class="font-medium"><?php echo e(ucfirst(str_replace('_', ' ', $restriction['type']))); ?></span>
                                    <span class="text-gray-600">: <?php echo e($restriction['value']); ?>

                                        <?php echo e($restriction['type'] === 'minimum_stay' ? 'nights' : '%'); ?></span>
                                </div>
                                <div class="text-sm text-gray-500"><?php echo e($restriction['reason']); ?></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Channel Mix Optimization -->
        <?php if(isset($channelMix) && isset($channelMix['channel_performance'])): ?>
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-4 py-3 border-b">
                    <h3 class="font-semibold text-gray-800">Channel Mix Optimization</h3>
                </div>
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="w-full mb-4">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">Channel</th>
                                    <th class="px-4 py-2 text-center">Bookings</th>
                                    <th class="px-4 py-2 text-center">Revenue</th>
                                    <th class="px-4 py-2 text-center">Commission</th>
                                    <th class="px-4 py-2 text-center">Net Revenue</th>
                                    <th class="px-4 py-2 text-center">Avg Booking</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $channelMix['channel_performance']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $channel => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="border-b">
                                        <td class="px-4 py-2 font-medium"><?php echo e(ucfirst($channel)); ?></td>
                                        <td class="px-4 py-2 text-center"><?php echo e($data['bookings']); ?></td>
                                        <td class="px-4 py-2 text-center">$<?php echo e(number_format($data['revenue'], 2)); ?></td>
                                        <td class="px-4 py-2 text-center">$<?php echo e(number_format($data['commission_cost'], 2)); ?>

                                            (<?php echo e(number_format($data['commission_percentage'], 1)); ?>%)</td>
                                        <td class="px-4 py-2 text-center font-medium">
                                            $<?php echo e(number_format($data['net_revenue'], 2)); ?></td>
                                        <td class="px-4 py-2 text-center">
                                            $<?php echo e(number_format($data['avg_booking_value'], 2)); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if(!empty($channelMix['recommendations'])): ?>
                        <div class="mt-4">
                            <h4 class="font-medium mb-2">Recommendations</h4>
                            <?php $__currentLoopData = $channelMix['recommendations']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="p-3 bg-blue-50 rounded mb-2">
                                    <div class="flex items-center mb-1">
                                        <span
                                            class="px-2 py-1 text-xs rounded <?php echo e($rec['priority'] === 'high' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'); ?>">
                                            <?php echo e(ucfirst($rec['priority'])); ?>

                                        </span>
                                        <span class="ml-2 font-medium"><?php echo e($rec['message']); ?></span>
                                    </div>
                                    <ul class="text-sm text-gray-600 ml-4">
                                        <?php $__currentLoopData = $rec['suggested_actions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li>• <?php echo e($action); ?></li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Yield Optimization by Room Type -->
        <?php if(isset($optimization)): ?>
            <div class="bg-white rounded-lg shadow">
                <div class="px-4 py-3 border-b">
                    <h3 class="font-semibold text-gray-800">Yield Optimization Analysis</h3>
                </div>
                <div class="p-4">
                    <?php $__currentLoopData = $optimization; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $roomTypeId => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="mb-6 p-4 border rounded">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="font-semibold text-lg"><?php echo e($data['room_type']); ?></h4>
                                <div class="text-right">
                                    <div class="text-sm text-gray-600">Projected Revenue Lift</div>
                                    <div
                                        class="text-2xl font-bold <?php echo e($data['revenue_lift_percentage'] > 0 ? 'text-green-600' : 'text-red-600'); ?>">
                                        <?php echo e($data['revenue_lift_percentage'] > 0 ? '+' : ''); ?><?php echo e(number_format($data['revenue_lift_percentage'], 1)); ?>%
                                    </div>
                                    <div class="text-sm">$<?php echo e(number_format($data['revenue_lift_amount'], 2)); ?></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div class="p-3 bg-gray-50 rounded">
                                    <div class="text-sm text-gray-600">Current Projected Revenue</div>
                                    <div class="text-xl font-medium">
                                        $<?php echo e(number_format($data['current_projected_revenue'], 2)); ?></div>
                                </div>
                                <div class="p-3 bg-green-50 rounded">
                                    <div class="text-sm text-gray-600">Optimized Projected Revenue</div>
                                    <div class="text-xl font-medium text-green-700">
                                        $<?php echo e(number_format($data['optimized_projected_revenue'], 2)); ?></div>
                                </div>
                            </div>

                            <?php if(!empty($data['recommendations'])): ?>
                                <div>
                                    <h5 class="font-medium mb-2">Daily Recommendations</h5>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-3 py-2 text-left">Date</th>
                                                    <th class="px-3 py-2 text-center">Current Rate</th>
                                                    <th class="px-3 py-2 text-center">Recommended</th>
                                                    <th class="px-3 py-2 text-center">Current Occ.</th>
                                                    <th class="px-3 py-2 text-center">Adj. Occ.</th>
                                                    <th class="px-3 py-2 text-center">Revenue Impact</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $__currentLoopData = array_slice($data['recommendations'], 0, 7); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr class="border-b">
                                                        <td class="px-3 py-2"><?php echo e($rec['date']); ?></td>
                                                        <td class="px-3 py-2 text-center">
                                                            $<?php echo e(number_format($rec['current_rate'], 2)); ?></td>
                                                        <td class="px-3 py-2 text-center font-medium">
                                                            $<?php echo e(number_format($rec['recommended_rate'], 2)); ?></td>
                                                        <td class="px-3 py-2 text-center">
                                                            <?php echo e(number_format($rec['current_occupancy'], 1)); ?>%</td>
                                                        <td class="px-3 py-2 text-center">
                                                            <?php echo e(number_format($rec['adjusted_occupancy'], 1)); ?>%</td>
                                                        <td
                                                            class="px-3 py-2 text-center <?php echo e($rec['revenue_impact'] > 0 ? 'text-green-600' : 'text-red-600'); ?>">
                                                            <?php echo e($rec['revenue_impact'] > 0 ? '+' : ''); ?>$<?php echo e(number_format($rec['revenue_impact'], 2)); ?>

                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\revenue\yield-optimization.blade.php ENDPATH**/ ?>