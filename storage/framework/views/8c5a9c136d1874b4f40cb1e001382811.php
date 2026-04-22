

<?php $__env->startSection('title', 'Notification Settings'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-bell text-primary"></i> Notification Settings
            </h1>
            <p class="text-muted mb-0">Configure system notifications and alerts</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNotificationModal">
                <i class="fas fa-plus"></i> Add Notification Rule
            </button>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success"><?php echo e($stats['active_rules'] ?? 0); ?></h3>
                    <small class="text-muted">Active Rules</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info"><?php echo e($stats['sent_today'] ?? 0); ?></h3>
                    <small class="text-muted">Sent Today</small>
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
                    <h3 class="text-danger"><?php echo e($stats['failed'] ?? 0); ?></h3>
                    <small class="text-muted">Failed</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Notification Rules</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Rule Name</th>
                                    <th>Trigger Event</th>
                                    <th>Channels</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Notifications Sent</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $rules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo e($rule->name ?? '-'); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo e($rule->trigger_event ?? '-'); ?></span>
                                        </td>
                                        <td>
                                            <?php if($rule->channels): ?>
                                                <?php $__currentLoopData = explode(',', $rule->channels); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $channel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php if(trim($channel) == 'email'): ?>
                                                        <span class="badge bg-primary"><i class="fas fa-envelope"></i>
                                                            Email</span>
                                                    <?php elseif(trim($channel) == 'sms'): ?>
                                                        <span class="badge bg-success"><i class="fas fa-sms"></i> SMS</span>
                                                    <?php elseif(trim($channel) == 'push'): ?>
                                                        <span class="badge bg-warning"><i class="fas fa-bell"></i>
                                                            Push</span>
                                                    <?php elseif(trim($channel) == 'in_app'): ?>
                                                        <span class="badge bg-secondary"><i class="fas fa-comment"></i>
                                                            In-App</span>
                                                    <?php endif; ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($rule->priority == 'critical'): ?>
                                                <span class="badge bg-danger">Critical</span>
                                            <?php elseif($rule->priority == 'high'): ?>
                                                <span class="badge bg-warning">High</span>
                                            <?php elseif($rule->priority == 'medium'): ?>
                                                <span class="badge bg-info">Medium</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Low</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($rule->is_active): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo e($rule->notifications_sent ?? 0); ?></strong>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-info" title="Edit" data-bs-toggle="modal"
                                                    data-bs-target="#editRuleModal<?php echo e($rule->id); ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if($rule->is_active): ?>
                                                    <button class="btn btn-sm btn-warning" title="Deactivate">
                                                        <i class="fas fa-pause"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-success" title="Activate">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-sm btn-primary" title="Test">
                                                    <i class="fas fa-vial"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-bell fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No notification rules configured</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Notifications</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Sent At</th>
                                    <th>Recipient</th>
                                    <th>Channel</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <small><?php echo e($notification->created_at->format('d/m/Y H:i') ?? '-'); ?></small>
                                            <br><small
                                                class="text-muted"><?php echo e($notification->created_at->diffForHumans() ?? '-'); ?></small>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo e($notification->recipient_name ?? '-'); ?></strong>
                                                <br><small
                                                    class="text-muted"><?php echo e($notification->recipient_email ?? '-'); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if($notification->channel == 'email'): ?>
                                                <span class="badge bg-primary"><i class="fas fa-envelope"></i> Email</span>
                                            <?php elseif($notification->channel == 'sms'): ?>
                                                <span class="badge bg-success"><i class="fas fa-sms"></i> SMS</span>
                                            <?php elseif($notification->channel == 'push'): ?>
                                                <span class="badge bg-warning"><i class="fas fa-bell"></i> Push</span>
                                            <?php else: ?>
                                                <span
                                                    class="badge bg-secondary"><?php echo e(ucfirst($notification->channel ?? '-')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?php echo e(Str::limit($notification->subject ?? '-', 50)); ?></small>
                                        </td>
                                        <td>
                                            <?php if($notification->status == 'sent'): ?>
                                                <span class="badge bg-success">Sent</span>
                                            <?php elseif($notification->status == 'pending'): ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php elseif($notification->status == 'failed'): ?>
                                                <span class="badge bg-danger">Failed</span>
                                            <?php else: ?>
                                                <span
                                                    class="badge bg-secondary"><?php echo e(ucfirst($notification->status)); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                data-bs-target="#viewNotificationModal<?php echo e($notification->id); ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-bell fa-2x text-muted mb-2"></i>
                                            <p class="text-muted">No notifications sent</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Notification Rule Modal -->
    <div class="modal fade" id="addNotificationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Notification Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?php echo e(route('integration.notifications.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rule Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required
                                    placeholder="e.g., Appointment Reminder">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Trigger Event <span class="text-danger">*</span></label>
                                <select name="trigger_event" class="form-select" required>
                                    <option value="">Select Event</option>
                                    <option value="appointment_reminder">Appointment Reminder</option>
                                    <option value="lab_result_ready">Lab Result Ready</option>
                                    <option value="prescription_due">Prescription Due</option>
                                    <option value="low_stock">Low Stock Alert</option>
                                    <option value="critical_result">Critical Lab Result</option>
                                    <option value="payment_overdue">Payment Overdue</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Notification Channels <span class="text-danger">*</span></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="channels[]" value="email"
                                        id="channel_email" checked>
                                    <label class="form-check-label" for="channel_email">Email</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="channels[]" value="sms"
                                        id="channel_sms">
                                    <label class="form-check-label" for="channel_sms">SMS</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="channels[]" value="push"
                                        id="channel_push">
                                    <label class="form-check-label" for="channel_push">Push Notification</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="channels[]" value="in_app"
                                        id="channel_in_app" checked>
                                    <label class="form-check-label" for="channel_in_app">In-App</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority <span class="text-danger">*</span></label>
                                <select name="priority" class="form-select" required>
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject Template <span class="text-danger">*</span></label>
                            <input type="text" name="subject_template" class="form-control" required
                                placeholder="e.g., Appointment Reminder: <?php echo e(appointment_date); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message Template <span class="text-danger">*</span></label>
                            <textarea name="message_template" class="form-control" rows="4" required
                                placeholder="e.g., Dear <?php echo e(patient_name); ?>, you have an appointment on <?php echo e(appointment_date); ?>..."></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                            <label class="form-check-label" for="is_active">
                                Activate this rule
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Rule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\integration\notifications.blade.php ENDPATH**/ ?>