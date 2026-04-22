

<?php $__env->startSection('title', 'HL7 Messages'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-exchange-alt text-primary"></i> HL7 Messages
            </h1>
            <p class="text-muted mb-0">Health Level 7 message monitoring and tracking</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sendMessageModal">
                <i class="fas fa-paper-plane"></i> Send Message
            </button>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success"><?php echo e($stats['sent'] ?? 0); ?></h3>
                    <small class="text-muted">Messages Sent</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info"><?php echo e($stats['received'] ?? 0); ?></h3>
                    <small class="text-muted">Messages Received</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning"><?php echo e($stats['pending'] ?? 0); ?></h3>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger"><?php echo e($stats['errors'] ?? 0); ?></h3>
                    <small class="text-muted">Errors</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Message Log</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Direction</th>
                                    <th>Message Type</th>
                                    <th>Event</th>
                                    <th>Sender</th>
                                    <th>Receiver</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <small><?php echo e($message->created_at->format('d/m/Y H:i:s')); ?></small>
                                            <br><small
                                                class="text-muted"><?php echo e($message->created_at->diffForHumans()); ?></small>
                                        </td>
                                        <td>
                                            <?php if($message->direction == 'outbound'): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-arrow-up"></i> Out
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-info">
                                                    <i class="fas fa-arrow-down"></i> In
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo e($message->message_type ?? '-'); ?></strong>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-light text-dark"><?php echo e($message->trigger_event ?? '-'); ?></span>
                                        </td>
                                        <td>
                                            <small><?php echo e($message->sending_app ?? '-'); ?></small>
                                        </td>
                                        <td>
                                            <small><?php echo e($message->receiving_app ?? '-'); ?></small>
                                        </td>
                                        <td>
                                            <?php if($message->status == 'sent' || $message->status == 'received'): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle"></i> <?php echo e(ucfirst($message->status)); ?>

                                                </span>
                                            <?php elseif($message->status == 'pending'): ?>
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock"></i> Pending
                                                </span>
                                            <?php elseif($message->status == 'error'): ?>
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times-circle"></i> Error
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?php echo e(ucfirst($message->status)); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                data-bs-target="#viewMessageModal<?php echo e($message->id); ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if($message->status == 'error'): ?>
                                                <button class="btn btn-sm btn-warning" title="Retry">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <!-- View Message Modal -->
                                    <div class="modal fade" id="viewMessageModal<?php echo e($message->id); ?>" tabindex="-1">
                                        <div class="modal-dialog modal-xl">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">HL7 Message Details</h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Message Type:</strong>
                                                            <p><?php echo e($message->message_type ?? '-'); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Trigger Event:</strong>
                                                            <p><?php echo e($message->trigger_event ?? '-'); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Sending Application:</strong>
                                                            <p><?php echo e($message->sending_app ?? '-'); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Receiving Application:</strong>
                                                            <p><?php echo e($message->receiving_app ?? '-'); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Message Content:</strong>
                                                        <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto; font-size: 0.85rem;"><code><?php echo e($message->message_content ?? 'N/A'); ?></code></pre>
                                                    </div>
                                                    <?php if($message->acknowledgment): ?>
                                                        <div class="mb-3">
                                                            <strong>Acknowledgment (ACK):</strong>
                                                            <pre class="bg-light p-3 rounded" style="max-height: 200px; overflow-y: auto; font-size: 0.85rem;"><code><?php echo e($message->acknowledgment); ?></code></pre>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if($message->error_message): ?>
                                                        <div class="alert alert-danger">
                                                            <strong>Error:</strong> <?php echo e($message->error_message); ?>

                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button class="btn btn-success" onclick="window.print()">
                                                        <i class="fas fa-print"></i> Print
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No HL7 messages found</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if(isset($messages) && $messages->hasPages()): ?>
                        <div class="mt-3">
                            <?php echo e($messages->links()); ?>

                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Send Message Modal -->
    <div class="modal fade" id="sendMessageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Send HL7 Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?php echo e(route('integration.hl7-messages.send')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Message Type <span class="text-danger">*</span></label>
                            <select name="message_type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="ADT">ADT - Admit, Discharge, Transfer</option>
                                <option value="ORM">ORM - Order Message</option>
                                <option value="ORU">ORU - Observation Result</option>
                                <option value="SIU">SIU - Scheduling Information</option>
                                <option value="DFT">DFT - Detailed Financial Transaction</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Receiving Application <span class="text-danger">*</span></label>
                            <input type="text" name="receiving_app" class="form-control" required
                                placeholder="e.g., LAB_SYSTEM, RIS_SYSTEM">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message Content <span class="text-danger">*</span></label>
                            <textarea name="message_content" class="form-control" rows="10" required
                                placeholder="Enter HL7 message segments (MSH, PID, PV1, etc.)..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\integration\hl7-messages.blade.php ENDPATH**/ ?>