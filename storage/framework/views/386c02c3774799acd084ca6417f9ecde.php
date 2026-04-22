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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight"><?php echo e(__('Message Details')); ?></h2>
            <a href="<?php echo e(route('healthcare.patient-messages.inbox')); ?>"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back to Inbox</a>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2"><?php echo e($message->subject); ?></h3>
                        <div class="flex items-center space-x-4 text-sm text-gray-600">
                            <span><i class="fas fa-user mr-1"></i>From:
                                <strong><?php echo e($message->sender->name ?? 'Unknown'); ?></strong></span>
                            <span><i class="fas fa-user mr-1"></i>To:
                                <strong><?php echo e($message->recipient->name ?? 'Unknown'); ?></strong></span>
                            <span><i
                                    class="fas fa-calendar mr-1"></i><?php echo e($message->created_at->format('d/m/Y H:i')); ?></span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span
                            class="px-3 py-1 text-sm font-semibold rounded-full <?php echo e($message->priority === 'urgent' ? 'bg-red-100 text-red-800' : ($message->priority === 'high' ? 'bg-orange-100 text-orange-800' : ($message->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'))); ?>"><?php echo e(ucfirst($message->priority)); ?></span>
                        <span
                            class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800"><?php echo e(ucfirst(str_replace('_', ' ', $message->category))); ?></span>
                        <?php if($message->is_read): ?>
                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800"><i
                                    class="fas fa-check mr-1"></i>Read</span>
                        <?php else: ?>
                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800"><i
                                    class="fas fa-envelope mr-1"></i>Unread</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <div class="prose max-w-none">
                        <p class="text-gray-700 whitespace-pre-line"><?php echo e($message->message); ?></p>
                    </div>
                </div>
            </div>

            <?php if($message->replies && $message->replies->count() > 0): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-comments mr-2 text-blue-600"></i>Replies (<?php echo e($message->replies->count()); ?>)
                    </h3>
                    <div class="space-y-4">
                        <?php $__currentLoopData = $message->replies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div
                                class="border-l-4 border-blue-500 pl-4 py-3 <?php echo e($reply->sender_id === auth()->id() ? 'bg-blue-50' : 'bg-gray-50'); ?>">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center space-x-2">
                                        <span
                                            class="font-semibold text-gray-900"><?php echo e($reply->sender->name ?? 'Unknown'); ?></span>
                                        <?php if($reply->sender_id === auth()->id()): ?>
                                            <span class="text-xs text-gray-500">(You)</span>
                                        <?php endif; ?>
                                    </div>
                                    <span
                                        class="text-sm text-gray-500"><?php echo e($reply->created_at->format('d/m/Y H:i')); ?></span>
                                </div>
                                <p class="text-gray-700 whitespace-pre-line"><?php echo e($reply->message); ?></p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                        class="fas fa-reply mr-2 text-green-600"></i>Reply to Message</h3>
                <form id="replyForm">
                    <?php echo csrf_field(); ?>
                    <div class="mb-4">
                        <textarea name="message" id="replyMessage" required rows="5"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Type your reply..."></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"><i
                                class="fas fa-reply mr-2"></i>Send Reply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            document.getElementById('replyForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const replyMessage = document.getElementById('replyMessage').value;

                fetch('<?php echo e(route('healthcare.patient-messages.reply', $message->id)); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify({
                            message: replyMessage
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Reply sent successfully!');
                            window.location.reload();
                        } else {
                            alert('Failed to send reply. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to send reply. Please try again.');
                    });
            });
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\patient-messages\show.blade.php ENDPATH**/ ?>