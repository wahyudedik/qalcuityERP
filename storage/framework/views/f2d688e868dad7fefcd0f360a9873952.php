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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight"><?php echo e(__('Health Education Material')); ?> -
                <?php echo e($healthEducation->title); ?></h2>
            <a href="<?php echo e(route('healthcare.health-education.index')); ?>"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-info-circle mr-2 text-blue-600"></i>Material Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Title</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900"><?php echo e($healthEducation->title); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Category</dt>
                            <dd class="mt-1"><span
                                    class="px-2 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800"><?php echo e(ucfirst(str_replace('_', ' ', $healthEducation->category))); ?></span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full <?php echo e($healthEducation->status === 'published' ? 'bg-green-100 text-green-800' : ($healthEducation->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')); ?>"><?php echo e(ucfirst($healthEducation->status)); ?></span>
                            </dd>
                        </div>
                        <?php if($healthEducation->target_audience): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Target Audience</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($healthEducation->target_audience); ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if($healthEducation->language): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Language</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($healthEducation->language); ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-chart-line mr-2 text-green-600"></i>Statistics</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">View Count</dt>
                            <dd class="mt-1 text-2xl font-bold text-gray-900"><?php echo e($healthEducation->view_count); ?></dd>
                        </div>
                        <?php if($healthEducation->published_at): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Published At</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <?php echo e($healthEducation->published_at->format('d/m/Y H:i')); ?></dd>
                            </div>
                        <?php endif; ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created At</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo e($healthEducation->created_at->format('d/m/Y H:i')); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo e($healthEducation->updated_at->format('d/m/Y H:i')); ?></dd>
                        </div>
                    </dl>
                </div>

                <?php if($healthEducation->attachment_path): ?>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                                class="fas fa-paperclip mr-2 text-purple-600"></i>Attachment</h3>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">File Path</dt>
                                <dd class="mt-1 text-sm text-gray-900 break-all">
                                    <?php echo e($healthEducation->attachment_path); ?></dd>
                            </div>
                            <div class="pt-4">
                                <a href="<?php echo e($healthEducation->attachment_path); ?>" target="_blank"
                                    class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                                    <i class="fas fa-download mr-2"></i>Download Attachment
                                </a>
                            </div>
                        </dl>
                    </div>
                <?php endif; ?>
            </div>

            <?php if($healthEducation->summary): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-align-left mr-2 text-orange-600"></i>Summary</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-line"><?php echo e($healthEducation->summary); ?></p>
                </div>
            <?php endif; ?>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                        class="fas fa-book-open mr-2 text-indigo-600"></i>Full Content</h3>
                <div class="prose max-w-none">
                    <p class="text-sm text-gray-700 whitespace-pre-line"><?php echo e($healthEducation->content); ?></p>
                </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\health-education\show.blade.php ENDPATH**/ ?>