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
                📋 Mix Design Version History - <?php echo e($mixDesign->grade); ?>

            </h2>
            <a href="<?php echo e(route('manufacturing.mix-design')); ?>"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                ← Back to Mix Design
            </a>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-slate-400">Grade</div>
                        <div class="text-xl font-bold text-gray-900 dark:text-white"><?php echo e($mixDesign->grade); ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-slate-400">Current Version</div>
                        <div class="text-xl font-bold text-blue-600">v<?php echo e($versions->count()); ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-slate-400">Total Changes</div>
                        <div class="text-xl font-bold text-purple-600"><?php echo e($versions->count()); ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-slate-400">Last Modified</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white">
                            <?php echo e($versions->first()?->created_at?->format('d M Y H:i') ?? '-'); ?>

                        </div>
                    </div>
                </div>
            </div>

            
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-6">📜 Version Timeline</h3>

                <div class="space-y-6">
                    <?php $__currentLoopData = $versions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $version): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div
                            class="relative pl-8 border-l-2 <?php echo e($index === 0 ? 'border-blue-500' : 'border-gray-300 dark:border-gray-600'); ?>">
                            
                            <div class="absolute -left-3 top-0">
                                <span
                                    class="inline-flex items-center justify-center w-6 h-6 rounded-full <?php echo e($index === 0 ? 'bg-blue-600' : 'bg-gray-400'); ?> text-white text-xs font-bold">
                                    <?php echo e($version->version_number); ?>

                                </span>
                            </div>

                            
                            <div class="bg-gray-50 dark:bg-white/5 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h4 class="font-bold text-lg text-gray-900 dark:text-white">
                                            Version <?php echo e($version->version_number); ?>

                                            <?php if($version->isApproved()): ?>
                                                <span
                                                    class="ml-2 px-2 py-1 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">✓
                                                    Approved</span>
                                            <?php else: ?>
                                                <span
                                                    class="ml-2 px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400">⏳
                                                    Pending</span>
                                            <?php endif; ?>
                                        </h4>
                                        <p class="text-sm text-gray-500 dark:text-slate-400">
                                            <?php echo e($version->created_at->format('d M Y, H:i')); ?> by
                                            <?php echo e($version->createdBy?->name ?? 'Unknown'); ?>

                                        </p>
                                    </div>
                                    <?php if(!$version->isApproved()): ?>
                                        <form method="POST"
                                            action="<?php echo e(route('manufacturing.mix-design.versions.approve', $version)); ?>"
                                            class="inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit"
                                                class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                                                ✓ Approve
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>

                                
                                <div
                                    class="mb-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded">
                                    <div class="text-xs text-blue-600 dark:text-blue-400 font-semibold mb-1">CHANGE
                                        REASON:</div>
                                    <p class="text-sm text-gray-700 dark:text-slate-300"><?php echo e($version->change_reason); ?>

                                    </p>
                                </div>

                                
                                <?php if($version->version_number > 1): ?>
                                    <div class="mb-3">
                                        <div class="text-xs text-gray-600 dark:text-slate-400 font-semibold mb-2">
                                            CHANGES FROM PREVIOUS VERSION:</div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            <?php
                                                $changes = $version->getChanges();
                                            ?>
                                            <?php if(is_array($changes) && !isset($changes['message'])): ?>
                                                <?php $__currentLoopData = $changes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $change): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div
                                                        class="flex items-center justify-between p-2 bg-white dark:bg-gray-800 rounded text-sm">
                                                        <span
                                                            class="text-gray-600 dark:text-slate-400"><?php echo e(str_replace('_', ' ', ucfirst($field))); ?></span>
                                                        <div class="flex items-center gap-2">
                                                            <span
                                                                class="text-red-600 line-through"><?php echo e($change['old']); ?></span>
                                                            <span class="text-gray-400">→</span>
                                                            <span
                                                                class="text-green-600 font-semibold"><?php echo e($change['new']); ?></span>
                                                            <?php if($change['diff'] !== null): ?>
                                                                <span
                                                                    class="text-xs px-2 py-0.5 rounded <?php echo e($change['diff'] > 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'); ?>">
                                                                    <?php echo e($change['diff'] > 0 ? '+' : ''); ?><?php echo e($change['diff']); ?>

                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php else: ?>
                                                <p class="text-sm text-gray-500 italic">
                                                    <?php echo e($changes['message'] ?? 'No changes recorded'); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                
                                <div class="grid grid-cols-2 md:grid-cols-5 gap-2 text-sm">
                                    <div class="p-2 bg-white dark:bg-gray-800 rounded">
                                        <div class="text-xs text-gray-500">Cement</div>
                                        <div class="font-semibold"><?php echo e($version->cement_kg); ?> kg</div>
                                    </div>
                                    <div class="p-2 bg-white dark:bg-gray-800 rounded">
                                        <div class="text-xs text-gray-500">Water</div>
                                        <div class="font-semibold"><?php echo e($version->water_liter); ?> L</div>
                                    </div>
                                    <div class="p-2 bg-white dark:bg-gray-800 rounded">
                                        <div class="text-xs text-gray-500">Fine Agg</div>
                                        <div class="font-semibold"><?php echo e($version->fine_agg_kg); ?> kg</div>
                                    </div>
                                    <div class="p-2 bg-white dark:bg-gray-800 rounded">
                                        <div class="text-xs text-gray-500">Coarse Agg</div>
                                        <div class="font-semibold"><?php echo e($version->coarse_agg_kg); ?> kg</div>
                                    </div>
                                    <div class="p-2 bg-white dark:bg-gray-800 rounded">
                                        <div class="text-xs text-gray-500">W/C Ratio</div>
                                        <div class="font-semibold"><?php echo e($version->water_cement_ratio); ?></div>
                                    </div>
                                </div>

                                
                                <?php if($version->isApproved()): ?>
                                    <div
                                        class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700 text-sm text-gray-500 dark:text-slate-400">
                                        Approved by <?php echo e($version->approvedBy?->name ?? 'Unknown'); ?> on
                                        <?php echo e($version->approved_at->format('d M Y, H:i')); ?>

                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">🔍 Compare Versions</h3>
                <div class="flex gap-4 mb-4">
                    <select id="compareVersion1" class="border rounded px-3 py-2 flex-1">
                        <option value="">Select Version 1</option>
                        <?php $__currentLoopData = $versions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($v->id); ?>">Version <?php echo e($v->version_number); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <select id="compareVersion2" class="border rounded px-3 py-2 flex-1">
                        <option value="">Select Version 2</option>
                        <?php $__currentLoopData = $versions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($v->id); ?>">Version <?php echo e($v->version_number); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <button onclick="compareVersions()"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                        Compare
                    </button>
                </div>
                <div id="comparisonResult" class="hidden"></div>
            </div>
        </div>
    </div>

    <script>
        function compareVersions() {
            const v1 = document.getElementById('compareVersion1').value;
            const v2 = document.getElementById('compareVersion2').value;

            if (!v1 || !v2) {
                alert('Please select both versions to compare');
                return;
            }

            if (v1 === v2) {
                alert('Please select different versions');
                return;
            }

            // TODO: Implement AJAX comparison
            alert('Comparison feature - Will show detailed diff between versions ' + v1 + ' and ' + v2);
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\manufacturing\mix-design-versions.blade.php ENDPATH**/ ?>