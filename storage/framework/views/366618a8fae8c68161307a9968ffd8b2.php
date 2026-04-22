

<?php $__env->startSection('title', 'Message Detail'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-envelope text-primary"></i> Message Detail
            </h1>
            <p class="text-muted mb-0">View message conversation</p>
        </div>
        <div>
            <a href="<?php echo e(route('portal.messages.inbox')); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Inbox
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#replyModal">
                <i class="fas fa-reply"></i> Reply
            </button>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <!-- Message Header -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="mb-1"><?php echo e($message->subject ?? 'No Subject'); ?></h4>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                    style="width: 40px; height: 40px;">
                                    <i class="fas fa-user-md"></i>
                                </div>
                                <div>
                                    <strong>From:</strong> <?php echo e($message->sender_name ?? 'Unknown'); ?>

                                    <br><small
                                        class="text-muted"><?php echo e($message->created_at->format('d/m/Y H:i') ?? '-'); ?></small>
                                </div>
                            </div>
                        </div>
                        <div>
                            <?php if($message->category == 'prescription'): ?>
                                <span class="badge bg-primary">Prescription</span>
                            <?php elseif($message->category == 'test_results'): ?>
                                <span class="badge bg-info">Test Results</span>
                            <?php elseif($message->category == 'appointment'): ?>
                                <span class="badge bg-success">Appointment</span>
                            <?php elseif($message->category == 'symptoms'): ?>
                                <span class="badge bg-warning">Symptoms</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?php echo e(ucfirst($message->category ?? 'General')); ?></span>
                            <?php endif; ?>

                            <?php if($message->priority == 'urgent'): ?>
                                <span class="badge bg-danger">Urgent</span>
                            <?php elseif($message->priority == 'high'): ?>
                                <span class="badge bg-warning">High</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if($message->status == 'read'): ?>
                        <span class="badge bg-success">
                            <i class="fas fa-check-double"></i> Read
                        </span>
                    <?php else: ?>
                        <span class="badge bg-primary">
                            <i class="fas fa-envelope"></i> Unread
                        </span>
                    <?php endif; ?>

                    <?php if($message->visit_id): ?>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i>
                                Related to visit on <?php echo e($message->visit_date ?? '-'); ?>

                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Message Thread -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-comments"></i> Conversation Thread
                    </h6>
                </div>
                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $message->thread ?? [$message]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="mb-4 <?php echo e($msg->is_from_patient ? 'ms-5' : 'me-5'); ?>">
                            <div class="d-flex align-items-center mb-2">
                                <?php if($msg->is_from_patient): ?>
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-2"
                                        style="width: 35px; height: 35px;">
                                        <i class="fas fa-user fa-sm"></i>
                                    </div>
                                    <strong>You</strong>
                                <?php else: ?>
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                        style="width: 35px; height: 35px;">
                                        <i class="fas fa-user-md fa-sm"></i>
                                    </div>
                                    <strong><?php echo e($msg->sender_name ?? 'Doctor'); ?></strong>
                                <?php endif; ?>
                                <small class="text-muted ms-2"><?php echo e($msg->created_at->diffForHumans() ?? '-'); ?></small>
                            </div>
                            <div class="p-3 rounded <?php echo e($msg->is_from_patient ? 'bg-light' : 'bg-primary text-white'); ?>">
                                <p class="mb-0" style="white-space: pre-wrap;"><?php echo e($msg->message ?? 'N/A'); ?></p>
                            </div>
                            <?php if($msg->attachments): ?>
                                <div class="mt-2">
                                    <strong>Attachments:</strong>
                                    <div class="d-flex flex-wrap gap-2 mt-1">
                                        <?php $__currentLoopData = $msg->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <a href="<?php echo e($attachment['url'] ?? '#'); ?>"
                                                class="btn btn-sm btn-outline-primary" download>
                                                <i class="fas fa-paperclip"></i> <?php echo e($attachment['name'] ?? 'File'); ?>

                                            </a>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-muted text-center">No messages in thread</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Reply -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-reply"></i> Quick Reply
                    </h6>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('portal.messages.reply', $message->id)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <textarea name="message" class="form-control" rows="5" required placeholder="Type your reply..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Attachments (Optional)</label>
                            <input type="file" name="attachments[]" class="form-control" multiple
                                accept="image/*,.pdf,.doc,.docx">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Reply
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reply Modal -->
    <div class="modal fade" id="replyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reply to Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?php echo e(route('portal.messages.reply', $message->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Original Message</label>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-0"><?php echo e(Str::limit($message->message ?? '', 200)); ?></p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Your Reply <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="6" required placeholder="Type your reply..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Reply
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\portal\message-detail.blade.php ENDPATH**/ ?>