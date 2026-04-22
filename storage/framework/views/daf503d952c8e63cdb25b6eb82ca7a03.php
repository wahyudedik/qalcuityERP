

<?php $__env->startSection('title', 'Book Teleconsultation'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-video text-primary"></i> Book Teleconsultation
            </h1>
            <p class="text-muted mb-0">Schedule online consultation with doctor</p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="<?php echo e(route('healthcare.telemedicine.book.store')); ?>" method="POST">
                        <?php echo csrf_field(); ?>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Doctor</label>
                            <div class="row">
                                <?php $__empty_1 = true; $__currentLoopData = $availableDoctors ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doctor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="col-md-6 mb-3">
                                        <div
                                            class="card border-2 <?php echo e(old('doctor_id') == $doctor->id ? 'border-primary' : ''); ?>">
                                            <div class="card-body">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="doctor_id"
                                                        id="doctor<?php echo e($doctor->id); ?>" value="<?php echo e($doctor->id); ?>"
                                                        <?php echo e(old('doctor_id') == $doctor->id ? 'checked' : ''); ?> required>
                                                    <label class="form-check-label w-100" for="doctor<?php echo e($doctor->id); ?>">
                                                        <div class="d-flex align-items-center">
                                                            <div class="me-3">
                                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                                                    style="width: 50px; height: 50px;">
                                                                    <i class="fas fa-user-md fa-lg"></i>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <strong><?php echo e($doctor->name); ?></strong>
                                                                <br><small
                                                                    class="text-muted"><?php echo e($doctor->specialty ?? 'General Practitioner'); ?></small>
                                                                <br><small class="text-success">
                                                                    <i class="fas fa-check-circle"></i> Available
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="col-12">
                                        <p class="text-muted text-center">No doctors available for teleconsultation</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Consultation Date</label>
                                <input type="date" name="consultation_date" class="form-control"
                                    value="<?php echo e(old('consultation_date', today()->format('Y-m-d'))); ?>" required
                                    min="<?php echo e(today()->format('Y-m-d')); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Preferred Time</label>
                                <select name="preferred_time" class="form-select" required>
                                    <option value="">Select time slot</option>
                                    <option value="09:00">09:00 - 09:30</option>
                                    <option value="09:30">09:30 - 10:00</option>
                                    <option value="10:00">10:00 - 10:30</option>
                                    <option value="10:30">10:30 - 11:00</option>
                                    <option value="11:00">11:00 - 11:30</option>
                                    <option value="13:00">13:00 - 13:30</option>
                                    <option value="13:30">13:30 - 14:00</option>
                                    <option value="14:00">14:00 - 14:30</option>
                                    <option value="14:30">14:30 - 15:00</option>
                                    <option value="15:00">15:00 - 15:30</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Consultation Type</label>
                            <select name="consultation_type" class="form-select" required>
                                <option value="video">Video Call</option>
                                <option value="audio">Audio Call</option>
                                <option value="chat">Chat Consultation</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Chief Complaint</label>
                            <textarea name="chief_complaint" class="form-control" rows="3"
                                placeholder="Describe your symptoms or reason for consultation..." required><?php echo e(old('chief_complaint')); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Additional Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Any additional information..."><?php echo e(old('notes')); ?></textarea>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Consultation Fee:</strong> Rp
                            <?php echo e(number_format($consultationFee ?? 150000, 0, ',', '.')); ?>

                            <br><small>Payment will be collected before the consultation begins.</small>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-calendar-check"></i> Book Consultation
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> How It Works
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex">
                            <div class="me-3">
                                <span class="badge bg-primary rounded-circle"
                                    style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">1</span>
                            </div>
                            <div>
                                <strong>Book Appointment</strong>
                                <br><small class="text-muted">Choose doctor and time slot</small>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex">
                            <div class="me-3">
                                <span class="badge bg-primary rounded-circle"
                                    style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">2</span>
                            </div>
                            <div>
                                <strong>Make Payment</strong>
                                <br><small class="text-muted">Pay consultation fee</small>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex">
                            <div class="me-3">
                                <span class="badge bg-primary rounded-circle"
                                    style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">3</span>
                            </div>
                            <div>
                                <strong>Join Consultation</strong>
                                <br><small class="text-muted">Click link at scheduled time</small>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex">
                            <div class="me-3">
                                <span class="badge bg-primary rounded-circle"
                                    style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">4</span>
                            </div>
                            <div>
                                <strong>Get Prescription</strong>
                                <br><small class="text-muted">Receive e-prescription</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-headset"></i> Need Help?
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><i class="fas fa-phone me-2"></i> <strong>Phone:</strong> (021) 1234-5678</p>
                    <p class="mb-2"><i class="fas fa-envelope me-2"></i> <strong>Email:</strong>
                        telemedicine@hospital.com</p>
                    <p class="mb-0"><i class="fas fa-clock me-2"></i> <strong>Hours:</strong> Mon-Sat, 08:00-17:00</p>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telemedicine\book.blade.php ENDPATH**/ ?>