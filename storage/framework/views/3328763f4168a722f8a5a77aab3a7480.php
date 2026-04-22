

<?php $__env->startSection('title', 'Consultation Feedback'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-star text-primary"></i> Consultation Feedback
            </h1>
            <p class="text-muted mb-0">Rate and review your teleconsultation experience</p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-md-8">
            <?php $__empty_1 = true; $__currentLoopData = $consultations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $consultation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Consultation #<?php echo e($consultation->consultation_number); ?></strong>
                                <br><small
                                    class="text-muted"><?php echo e($consultation->consultation_date?->format('d/m/Y H:i') ?? '-'); ?></small>
                            </div>
                            <?php if($consultation->feedback): ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i> Feedback Submitted
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning">
                                    <i class="fas fa-clock"></i> Pending Feedback
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <small class="text-muted">Doctor</small>
                                <br><strong><?php echo e($consultation->doctor?->name ?? '-'); ?></strong>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Consultation Type</small>
                                <br><strong><?php echo e(ucfirst($consultation->consultation_type ?? '-')); ?></strong>
                            </div>
                        </div>

                        <?php if($consultation->feedback): ?>
                            <div class="bg-light p-3 rounded">
                                <div class="mb-2">
                                    <strong>Your Rating:</strong>
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i
                                            class="fas fa-star <?php echo e($i <= ($consultation->feedback['rating'] ?? 0) ? 'text-warning' : 'text-muted'); ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="mb-1"><strong>Comment:</strong>
                                    <?php echo e($consultation->feedback['comment'] ?? 'N/A'); ?></p>
                                <small class="text-muted">Submitted on
                                    <?php echo e($consultation->feedback['submitted_at'] ?? '-'); ?></small>
                            </div>
                        <?php else: ?>
                            <form action="<?php echo e(route('healthcare.telemedicine.feedback.store', $consultation)); ?>"
                                method="POST">
                                <?php echo csrf_field(); ?>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Overall Rating</label>
                                    <div class="rating-stars" data-rating-input="rating<?php echo e($consultation->id); ?>">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star fa-2x text-muted rating-star"
                                                data-rating="<?php echo e($i); ?>" style="cursor: pointer;"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <input type="hidden" name="rating" id="rating<?php echo e($consultation->id); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">What did you like?</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="liked[]"
                                                    value="doctor_knowledge">
                                                <label class="form-check-label">Doctor's Knowledge</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="liked[]"
                                                    value="communication">
                                                <label class="form-check-label">Communication</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="liked[]"
                                                    value="wait_time">
                                                <label class="form-check-label">Wait Time</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="liked[]"
                                                    value="platform_ease">
                                                <label class="form-check-label">Platform Ease of Use</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="liked[]"
                                                    value="video_quality">
                                                <label class="form-check-label">Video/Audio Quality</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="liked[]"
                                                    value="overall_experience">
                                                <label class="form-check-label">Overall Experience</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Your Comments</label>
                                    <textarea name="comment" class="form-control" rows="3" placeholder="Share your experience..." required></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Would you recommend this doctor?</label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="recommend"
                                                id="recommend_yes<?php echo e($consultation->id); ?>" value="yes" required>
                                            <label class="form-check-label"
                                                for="recommend_yes<?php echo e($consultation->id); ?>">Yes</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="recommend"
                                                id="recommend_no<?php echo e($consultation->id); ?>" value="no">
                                            <label class="form-check-label"
                                                for="recommend_no<?php echo e($consultation->id); ?>">No</label>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Submit Feedback
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-star fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No consultations to review</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Your Feedback Stats
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h2 class="text-warning">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo e($i <= ($avgRating ?? 0) ? '' : 'text-muted'); ?>"></i>
                            <?php endfor; ?>
                        </h2>
                        <small class="text-muted">Average Rating: <?php echo e(number_format($avgRating ?? 0, 1)); ?>/5.0</small>
                    </div>
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-success"><?php echo e($feedbackSubmitted ?? 0); ?></h4>
                            <small class="text-muted">Submitted</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-warning"><?php echo e($pendingFeedback ?? 0); ?></h4>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            document.querySelectorAll('.rating-star').forEach(star => {
                star.addEventListener('click', function() {
                    const rating = this.dataset.rating;
                    const container = this.closest('.rating-stars');
                    const input = document.getElementById(container.dataset.ratingInput);

                    input.value = rating;

                    container.querySelectorAll('.rating-star').forEach(s => {
                        if (s.dataset.rating <= rating) {
                            s.classList.remove('text-muted');
                            s.classList.add('text-warning');
                        } else {
                            s.classList.remove('text-warning');
                            s.classList.add('text-muted');
                        }
                    });
                });
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telemedicine\feedback.blade.php ENDPATH**/ ?>