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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight"><?php echo e(__('Backup Details')); ?> -
                #<?php echo e($backup->id); ?></h2>
            <a href="<?php echo e(route('healthcare.backups.index')); ?>"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <?php if(session('success')): ?>
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-info-circle mr-2 text-blue-600"></i>Backup Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Backup ID</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">#<?php echo e($backup->id); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Backup Type</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-sm font-semibold rounded-full <?php echo e($backup->backup_type === 'full' ? 'bg-blue-100 text-blue-800' : ($backup->backup_type === 'database' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800')); ?>"><?php echo e(ucfirst($backup->backup_type)); ?></span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <?php if($backup->status === 'completed'): ?>
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800"><i
                                            class="fas fa-check mr-1"></i>Completed</span>
                                <?php elseif($backup->status === 'in_progress'): ?>
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800"><i
                                            class="fas fa-spinner fa-spin mr-1"></i>In Progress</span>
                                <?php else: ?>
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800"><i
                                            class="fas fa-times mr-1"></i>Failed</span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">File Size</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo e($backup->size_bytes ? round($backup->size_bytes / 1048576, 2) . ' MB (' . number_format($backup->size_bytes) . ' bytes)' : 'N/A'); ?>

                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-clock mr-2 text-purple-600"></i>Timing Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created At</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($backup->created_at->format('d/m/Y H:i:s')); ?></dd>
                        </div>
                        <?php if($backup->started_at): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Started At</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($backup->started_at->format('d/m/Y H:i:s')); ?>

                                </dd>
                            </div>
                        <?php endif; ?>
                        <?php if($backup->completed_at): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Completed At</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <?php echo e($backup->completed_at->format('d/m/Y H:i:s')); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Duration</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <?php echo e($backup->started_at->diffForHumans($backup->completed_at, true)); ?></dd>
                            </div>
                        <?php endif; ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Initiated By</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($backup->initiatedBy->name ?? 'N/A'); ?></dd>
                        </div>
                    </dl>
                </div>
            </div>

            <?php if($backup->notes): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-sticky-note mr-2 text-yellow-600"></i>Notes</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-line"><?php echo e($backup->notes); ?></p>
                </div>
            <?php endif; ?>

            <?php if($backup->error_message): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6 border-2 border-red-200">
                    <h3 class="text-lg font-semibold text-red-900 mb-4"><i
                            class="fas fa-exclamation-triangle mr-2 text-red-600"></i>Error Message</h3>
                    <pre class="text-sm bg-red-50 p-4 rounded overflow-x-auto text-red-800"><?php echo e($backup->error_message); ?></pre>
                </div>
            <?php endif; ?>

            <?php if($backup->status === 'completed'): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-tasks mr-2 text-indigo-600"></i>Actions</h3>
                    <div class="flex space-x-3">
                        <a href="<?php echo e(route('healthcare.backups.download', $backup)); ?>"
                            class="px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700"><i
                                class="fas fa-download mr-2"></i>Download Backup</a>
                        <button onclick="restoreBackup()"
                            class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-undo mr-2"></i>Restore Database</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function restoreBackup() {
            if (confirm(
                    'WARNING: This will restore the database to this backup point. All current data will be lost. Are you sure?'
                    )) {
                if (confirm('FINAL CONFIRMATION: Type OK to proceed with database restore')) {
                    fetch('<?php echo e(route('healthcare.backups.restore', $backup)); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                            },
                            body: JSON.stringify({
                                confirm: 'OK'
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);
                            if (data.success) {
                                window.location.href = '<?php echo e(route('healthcare.backups.index')); ?>';
                            }
                        })
                        .catch(error => alert('Restore failed'));
                }
            }
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\backups\show.blade.php ENDPATH**/ ?>