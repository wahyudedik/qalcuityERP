
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'action',
    'method' => 'POST',
    'module' => 'general',
    'redirect' => null,
    'offlineMessage' => 'Disimpan offline. Akan disinkronisasi saat online.',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'action',
    'method' => 'POST',
    'module' => 'general',
    'redirect' => null,
    'offlineMessage' => 'Disimpan offline. Akan disinkronisasi saat online.',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<form <?php echo e($attributes->merge(['class' => 'offline-form'])); ?> action="<?php echo e($action); ?>"
    method="<?php echo e(strtoupper($method) === 'GET' ? 'GET' : 'POST'); ?>" data-offline-module="<?php echo e($module); ?>"
    data-offline-redirect="<?php echo e($redirect); ?>" data-offline-message="<?php echo e($offlineMessage); ?>"
    data-real-method="<?php echo e(strtoupper($method)); ?>">
    <?php echo csrf_field(); ?>
    <?php if(!in_array(strtoupper($method), ['GET', 'POST'])): ?>
        <?php echo method_field($method); ?>
    <?php endif; ?>

    <?php echo e($slot); ?>

</form>

<?php if (! $__env->hasRenderedOnce('344dec30-17e0-4387-ab54-14e6c52f36b4')): $__env->markAsRenderedOnce('344dec30-17e0-4387-ab54-14e6c52f36b4'); ?>
    <?php $__env->startPush('scripts'); ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.offline-form').forEach(form => {
                    form.addEventListener('submit', async function(e) {
                        // If online, let the form submit normally
                        if (navigator.onLine) return;

                        e.preventDefault();

                        const module = this.dataset.offlineModule;
                        const action = this.action;
                        const method = this.dataset.realMethod || 'POST';
                        const message = this.dataset.offlineMessage;
                        const redirect = this.dataset.offlineRedirect;

                        // Collect form data as JSON
                        const formData = new FormData(this);
                        const payload = {};
                        for (const [key, value] of formData.entries()) {
                            if (key === '_token' || key === '_method') continue;
                            // Handle array fields (e.g. items[0][qty])
                            if (key.includes('[')) {
                                setNestedValue(payload, key, value);
                            } else {
                                payload[key] = value;
                            }
                        }

                        try {
                            const queueId = await window.ErpOffline.queue(module, action, method,
                                payload);

                            // Show success feedback
                            if (typeof window.showToast === 'function') {
                                window.showToast(message, 'warning');
                            } else if (typeof window.ErpOffline !== 'undefined') {
                                // Use offline manager toast
                                const toast = document.createElement('div');
                                toast.className =
                                    'fixed bottom-4 right-4 z-[9999] px-4 py-3 rounded-xl text-white text-sm font-medium shadow-lg bg-amber-500/90 transition-all duration-300';
                                toast.innerHTML = message;
                                document.body.appendChild(toast);
                                setTimeout(() => toast.remove(), 4000);
                            }

                            // Redirect if specified
                            if (redirect) {
                                setTimeout(() => window.location.href = redirect, 1000);
                            }
                        } catch (err) {
                            console.error('Offline queue error:', err);
                            alert('Gagal menyimpan data offline. Silakan coba lagi.');
                        }
                    });
                });

                // Helper: set nested object value from bracket notation
                function setNestedValue(obj, path, value) {
                    const keys = path.replace(/\]/g, '').split('[');
                    let current = obj;
                    for (let i = 0; i < keys.length - 1; i++) {
                        const key = keys[i];
                        if (!current[key]) {
                            current[key] = isNaN(keys[i + 1]) ? {} : [];
                        }
                        current = current[key];
                    }
                    current[keys[keys.length - 1]] = value;
                }
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\offline-form.blade.php ENDPATH**/ ?>