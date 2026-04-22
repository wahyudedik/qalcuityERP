

<?php $__env->startSection('title', 'Onboarding Dashboard'); ?>

<?php
    // Map step_key → { icon, url, label, ajax }
    // ajax: true = mark complete in-place (no navigation); false = navigate to URL
    $stepConfig = [
        'complete_profile'    => ['icon' => '👤', 'url' => route('profile.edit'),              'label' => 'Complete Profile', 'ajax' => false],
        'generate_sample_data'=> ['icon' => '🗂️', 'url' => route('onboarding.sample-data'),    'label' => 'Load Sample Data', 'ajax' => false],
        'explore_dashboard'   => ['icon' => '📊', 'url' => route('dashboard'),                 'label' => 'Explore',          'ajax' => true],
        'create_first_record' => ['icon' => '➕', 'url' => null,                               'label' => 'Mark Done',        'ajax' => true],
        'invite_team_member'  => ['icon' => '👥', 'url' => route('dashboard'),                 'label' => 'Invite',           'ajax' => true],
        // Retail
        'add_first_product'   => ['icon' => '📦', 'url' => route('products.index'),            'label' => 'Add Product',      'ajax' => false],
        'process_first_sale'  => ['icon' => '🛒', 'url' => route('pos.index'),                 'label' => 'Open POS',         'ajax' => false],
        // Restaurant / F&B
        'create_menu'         => ['icon' => '🍽️', 'url' => route('fnb.recipes.index'),         'label' => 'Create Menu',      'ajax' => false],
        'setup_tables'        => ['icon' => '🪑', 'url' => route('fnb.tables.index'),          'label' => 'Setup Tables',     'ajax' => false],
        'take_first_order'    => ['icon' => '📋', 'url' => route('fnb.kds.index'),             'label' => 'Take Order',       'ajax' => false],
        // Hotel
        'setup_rooms'         => ['icon' => '🛏️', 'url' => route('hotel.rooms.index'),         'label' => 'Setup Rooms',      'ajax' => false],
        'create_booking'      => ['icon' => '📅', 'url' => route('hotel.reservations.index'),  'label' => 'Create Booking',   'ajax' => false],
        'check_in_guest'      => ['icon' => '🏨', 'url' => route('hotel.reservations.index'),  'label' => 'Check-in Guest',   'ajax' => false],
        // Construction
        'create_project'      => ['icon' => '🏗️', 'url' => route('projects.index'),            'label' => 'Create Project',   'ajax' => false],
        'add_materials'       => ['icon' => '🧱', 'url' => route('inventory.index'),            'label' => 'Add Materials',    'ajax' => false],
        // Agriculture
        'add_crop_cycle'      => ['icon' => '🌱', 'url' => route('agriculture.dashboard'),      'label' => 'Add Crop Cycle',   'ajax' => false],
        'setup_irrigation'    => ['icon' => '💧', 'url' => route('agriculture.dashboard'),      'label' => 'Setup Irrigation', 'ajax' => false],
    ];
?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">

        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-gray-900 mb-3">🎯 Getting Started</h1>
            <p class="text-lg text-gray-600">Complete these steps to get the most out of Qalcuity ERP</p>
        </div>

        <!-- Progress Overview -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <span class="inline-block bg-blue-100 text-blue-700 text-sm font-semibold px-3 py-1 rounded-full capitalize mb-2">
                        <?php echo e($profile->industry_label ?? ucfirst($profile->industry)); ?>

                    </span>
                    <h2 class="text-xl font-bold text-gray-900">Your Setup Progress</h2>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold text-blue-600" id="pct-display"><?php echo e($progress['completion_percentage']); ?>%</div>
                    <div class="text-sm text-gray-500" id="step-display">
                        <?php echo e($progress['completed_steps']); ?> / <?php echo e($progress['total_steps']); ?> steps
                    </div>
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-blue-600 h-3 rounded-full transition-all duration-500" id="progress-bar"
                    style="width: <?php echo e($progress['completion_percentage']); ?>%"></div>
            </div>

            <?php if($progress['completion_percentage'] >= 100): ?>
            <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-xl text-center">
                <span class="text-green-700 font-semibold">🎉 All steps complete! Your workspace is fully set up.</span>
                <a href="<?php echo e(route('dashboard')); ?>" class="ml-4 inline-block px-5 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-colors">
                    Go to Dashboard →
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Steps Checklist -->
        <?php if($progress['steps']->count() > 0): ?>
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Setup Checklist</h3>

            <?php $categories = $progress['steps']->groupBy('category') ?>

            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $steps): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="mb-6 last:mb-0">
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                    <?php echo e($steps->first()->category_label); ?>

                </div>
                <div class="space-y-3">
                    <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $cfg = $stepConfig[$step->step_key] ?? ['icon' => '📌', 'url' => null, 'label' => 'Start', 'ajax' => true];
                        $isCompleted = $step->completed;
                    ?>
                    <div class="flex items-center gap-4 p-4 rounded-xl border transition-all
                        <?php echo e($isCompleted ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50 hover:border-blue-300 hover:bg-blue-50'); ?>"
                        id="step-row-<?php echo e($step->step_key); ?>">

                        <!-- Status icon -->
                        <div class="flex-shrink-0">
                            <?php if($isCompleted): ?>
                                <div class="w-9 h-9 rounded-full bg-green-500 text-white flex items-center justify-center text-base font-bold">✓</div>
                            <?php else: ?>
                                <div class="w-9 h-9 rounded-full bg-white border-2 border-gray-300 text-gray-500 flex items-center justify-center text-lg">
                                    <?php echo e($cfg['icon']); ?>

                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Step info -->
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-gray-900 <?php echo e($isCompleted ? 'line-through text-gray-400' : ''); ?>">
                                <?php echo e($step->step_name); ?>

                            </div>
                            <?php if(!empty($step->description)): ?>
                            <div class="text-sm text-gray-500 mt-0.5"><?php echo e($step->description); ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Action button -->
                        <?php if(!$isCompleted): ?>
                            <?php if($cfg['ajax']): ?>
                                <button
                                    onclick="markComplete('<?php echo e($step->step_key); ?>')"
                                    class="flex-shrink-0 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                    <?php echo e($cfg['label']); ?> →
                                </button>
                            <?php elseif($cfg['url'] && $cfg['url'] !== '#'): ?>
                                <a href="<?php echo e($cfg['url']); ?>"
                                    onclick="markCompleteAndNavigate(event, '<?php echo e($step->step_key); ?>', '<?php echo e($cfg['url']); ?>')"
                                    class="flex-shrink-0 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                    <?php echo e($cfg['label']); ?> →
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="flex-shrink-0 text-green-600 text-sm font-medium">Done</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>

        <!-- Tips -->
        <?php if($tips->count() > 0): ?>
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8" id="tips-section">
            <h3 class="text-lg font-bold text-gray-900 mb-4">💡 Tips for You</h3>
            <div class="space-y-3" id="tips-list">
                <?php $__currentLoopData = $tips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-start gap-3 p-4 bg-yellow-50 border border-yellow-200 rounded-xl" id="tip-<?php echo e($tip->id); ?>">
                    <div class="flex-1 text-sm text-gray-700"><?php echo e($tip->message ?? $tip->content ?? ''); ?></div>
                    <button onclick="dismissTip(<?php echo e($tip->id); ?>)"
                        class="flex-shrink-0 text-gray-400 hover:text-gray-600 text-xl leading-none font-bold"
                        title="Dismiss">×</button>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Footer actions -->
        <div class="flex justify-between items-center">
            <a href="<?php echo e(route('dashboard')); ?>" class="text-sm text-gray-500 hover:text-gray-700 underline">
                Skip for now
            </a>
            <a href="<?php echo e(route('dashboard')); ?>" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                Go to Dashboard →
            </a>
        </div>

    </div>
</div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // Auto-redirect if already 100% on page load
    <?php if($progress['completion_percentage'] >= 100): ?>
    window.location.href = '<?php echo e(route('dashboard')); ?>';
    <?php endif; ?>

    async function markComplete(stepKey) {
        try {
            const res = await fetch(`/onboarding/complete-step/${stepKey}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (res.ok) {
                updateStepUI(stepKey);
                if (data.redirect) {
                    // All done — redirect to dashboard
                    setTimeout(() => { window.location.href = data.redirect; }, 600);
                    return;
                }
                refreshProgress();
            }
        } catch (e) { console.error(e); }
    }

    async function markCompleteAndNavigate(event, stepKey, url) {
        event.preventDefault();
        try {
            const res = await fetch(`/onboarding/complete-step/${stepKey}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.redirect) {
                window.location.href = data.redirect;
                return;
            }
        } catch (e) { /* silent — still navigate */ }
        window.location.href = url;
    }

    function updateStepUI(stepKey) {
        const row = document.getElementById(`step-row-${stepKey}`);
        if (!row) return;

        row.classList.remove('border-gray-200', 'bg-gray-50', 'hover:border-blue-300', 'hover:bg-blue-50');
        row.classList.add('border-green-200', 'bg-green-50');

        const iconEl = row.querySelector('.flex-shrink-0:first-child > div');
        if (iconEl) {
            iconEl.className = 'w-9 h-9 rounded-full bg-green-500 text-white flex items-center justify-center text-base font-bold';
            iconEl.textContent = '✓';
        }

        const titleEl = row.querySelector('.font-semibold');
        if (titleEl) titleEl.classList.add('line-through', 'text-gray-400');

        const btn = row.querySelector('button, a.bg-blue-600');
        if (btn) {
            const done = document.createElement('span');
            done.className = 'flex-shrink-0 text-green-600 text-sm font-medium';
            done.textContent = 'Done';
            btn.replaceWith(done);
        }
    }

    async function refreshProgress() {
        try {
            const res = await fetch('/onboarding/progress', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            const pct       = data.completion_percentage ?? 0;
            const completed = data.completed_steps ?? 0;
            const total     = data.total_steps ?? 0;

            document.getElementById('pct-display').textContent = `${pct}%`;
            document.getElementById('step-display').textContent = `${completed} / ${total} steps`;
            document.getElementById('progress-bar').style.width = `${pct}%`;

            if (pct >= 100) {
                setTimeout(() => { window.location.href = '<?php echo e(route('dashboard')); ?>'; }, 800);
            }
        } catch (e) { /* silent */ }
    }

    async function dismissTip(tipId) {
        try {
            await fetch(`/onboarding/tips/${tipId}/dismiss`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken }
            });
        } catch (e) { /* silent */ }
        const el = document.getElementById(`tip-${tipId}`);
        if (el) el.remove();

        const list = document.getElementById('tips-list');
        if (list && list.children.length === 0) {
            document.getElementById('tips-section')?.remove();
        }
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\onboarding\dashboard.blade.php ENDPATH**/ ?>