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
        <h1 class="text-base font-semibold text-gray-900">Tour Packages</h1>
     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="mb-4 flex justify-end">
                <a href="<?php echo e(route('tour-travel.packages.create')); ?>"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                    + New Package
                </a>
            </div>

            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <p class="text-xs text-gray-500">Total Packages</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e($stats['total_packages']); ?></p>
                </div>
                <div class="bg-white rounded-xl border border-green-200 p-4">
                    <p class="text-xs text-gray-500">Active Packages</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">
                        <?php echo e($stats['active_packages']); ?></p>
                </div>
                <div class="bg-white rounded-xl border border-blue-200 p-4">
                    <p class="text-xs text-gray-500">Total Bookings</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1"><?php echo e($stats['total_bookings']); ?>

                    </p>
                </div>
                <div
                    class="bg-white rounded-xl border border-purple-200 p-4">
                    <p class="text-xs text-gray-500">Upcoming Departures</p>
                    <p class="text-2xl font-bold text-purple-600 mt-1">
                        <?php echo e($stats['upcoming_departures']); ?>

                    </p>
                </div>
                <div
                    class="bg-white rounded-xl border border-orange-200 p-4">
                    <p class="text-xs text-gray-500">Pending Visas</p>
                    <p class="text-2xl font-bold text-orange-600 mt-1">
                        <?php echo e($stats['pending_visas']); ?></p>
                </div>
            </div>

            
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">📦 Tour Packages</h3>
                    <div class="flex gap-2">
                        <select
                            class="text-sm border border-gray-300 rounded px-3 py-1.5 bg-white text-gray-900">
                            <option value="">All Categories</option>
                            <option value="domestic">Domestic</option>
                            <option value="international">International</option>
                            <option value="adventure">Adventure</option>
                            <option value="luxury">Luxury</option>
                            <option value="cultural">Cultural</option>
                        </select>
                        <select
                            class="text-sm border border-gray-300 rounded px-3 py-1.5 bg-white text-gray-900">
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <?php if($packages->count() === 0): ?>
                    <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'calendar','title' => 'Belum ada paket tour','message' => 'Belum ada paket tour travel. Buat paket pertama Anda.','actionText' => 'Buat Paket Tour','actionUrl' => ''.e(route('tour-travel.packages.create')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'calendar','title' => 'Belum ada paket tour','message' => 'Belum ada paket tour travel. Buat paket pertama Anda.','actionText' => 'Buat Paket Tour','actionUrl' => ''.e(route('tour-travel.packages.create')).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Package Code</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Name</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Destination</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Category</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Duration</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Price</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Bookings</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $package): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4">
                                            <a href="<?php echo e(route('tour-travel.packages.show', $package)); ?>"
                                                class="font-medium text-indigo-600 hover:underline">
                                                <?php echo e($package->package_code); ?>

                                            </a>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700 font-medium">
                                            <?php echo e($package->name); ?>

                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            <?php echo e($package->destination); ?>

                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">
                                                <?php echo e($package->category_label); ?>

                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            <?php echo e($package->duration_days); ?>D/<?php echo e($package->duration_nights); ?>N
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm">
                                                <p class="font-medium text-gray-900">Rp
                                                    <?php echo e(number_format($package->price_per_person, 0, ',', '.')); ?></p>
                                                <?php if($package->profit_margin > 0): ?>
                                                    <p class="text-xs text-green-600">
                                                        <?php echo e($package->profit_margin); ?>% margin</p>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            <?php echo e($package->bookings_count); ?> bookings
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php
                                                $color = match ($package->status) {
                                                    'draft' => 'gray',
                                                    'active' => 'green',
                                                    'inactive' => 'yellow',
                                                    'archived' => 'red',
                                                    default => 'gray',
                                                };
                                            ?>
                                            <span
                                                class="px-2 py-1 text-xs rounded-full bg-<?php echo e($color); ?>-100 text-<?php echo e($color); ?>-700 $color }}-500/20 $color }}-400">
                                                <?php echo e(ucfirst($package->status)); ?>

                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex gap-2">
                                                <a href="<?php echo e(route('tour-travel.packages.show', $package)); ?>"
                                                    class="text-indigo-600 hover:underline text-xs">View</a>
                                                <a href="<?php echo e(route('tour-travel.packages.edit', $package)); ?>"
                                                    class="text-blue-600 hover:underline text-xs">Edit</a>
                                                <form
                                                    action="<?php echo e(route('tour-travel.packages.toggle-status', $package)); ?>"
                                                    method="POST" class="inline">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit"
                                                        class="text-orange-600 hover:underline text-xs">
                                                        <?php echo e($package->status === 'active' ? 'Deactivate' : 'Activate'); ?>

                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200">
                        <?php echo e($packages->links()); ?>

                    </div>
                <?php endif; ?>
            </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\tour-travel\packages\index.blade.php ENDPATH**/ ?>