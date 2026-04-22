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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <?php echo e(__('Telemedicine Settings')); ?>

            </h2>
            <div class="flex gap-2">
                <button type="button" onclick="resetToDefault()"
                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    <i class="fas fa-undo mr-2"></i>Reset to Default
                </button>
                <a href="<?php echo e(route('healthcare.telemedicine.index')); ?>"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <?php if(session('success')): ?>
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                    role="alert">
                    <span class="block sm:inline"><?php echo e(session('success')); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('healthcare.telemedicine.settings.update')); ?>" id="settings-form">
                <?php echo csrf_field(); ?>

                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-server text-blue-600 mr-2"></i>Jitsi Meet Configuration
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Configure your Jitsi Meet server. Leave as default to use the free public server (meet.jit.si).
                    </p>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jitsi Server URL *</label>
                            <div class="flex gap-2">
                                <input type="url" name="jitsi_server_url" id="jitsi_server_url"
                                    value="<?php echo e(old('jitsi_server_url', $settings->jitsi_server_url)); ?>"
                                    placeholder="https://meet.jit.si"
                                    class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required />
                                <button type="button" onclick="testConnection()"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 whitespace-nowrap">
                                    <i class="fas fa-plug mr-2"></i>Test Connection
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Default: https://meet.jit.si (free). For self-hosted: https://your-jitsi-domain.com
                            </p>
                            <div id="connection-test-result" class="mt-2 hidden"></div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jitsi App ID
                                    (optional)</label>
                                <input type="text" name="jitsi_app_id"
                                    value="<?php echo e(old('jitsi_app_id', $settings->jitsi_app_id)); ?>"
                                    placeholder="Leave empty for public server"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                <p class="mt-1 text-xs text-gray-500">For self-hosted Jitsi with authentication</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jitsi Secret
                                    (optional)</label>
                                <input type="password" name="jitsi_secret"
                                    value="<?php echo e(old('jitsi_secret', $settings->jitsi_secret)); ?>"
                                    placeholder="JWT secret key"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                <p class="mt-1 text-xs text-gray-500">JWT secret for token-based authentication</p>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-toggle-on text-green-600 mr-2"></i>Features
                    </h3>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Enable Recording</p>
                                <p class="text-sm text-gray-600">Allow consultation recording</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="enable_recording" value="1"
                                    <?php echo e(old('enable_recording', $settings->enable_recording) ? 'checked' : ''); ?>

                                    class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                </div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Virtual Waiting Room</p>
                                <p class="text-sm text-gray-600">Patients wait before being admitted</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="enable_waiting_room" value="1"
                                    <?php echo e(old('enable_waiting_room', $settings->enable_waiting_room) ? 'checked' : ''); ?>

                                    class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                </div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Chat</p>
                                <p class="text-sm text-gray-600">Enable in-call text chat</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="enable_chat" value="1"
                                    <?php echo e(old('enable_chat', $settings->enable_chat) ? 'checked' : ''); ?>

                                    class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                </div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Screen Sharing</p>
                                <p class="text-sm text-gray-600">Allow participants to share screen</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="enable_screen_share" value="1"
                                    <?php echo e(old('enable_screen_share', $settings->enable_screen_share) ? 'checked' : ''); ?>

                                    class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-bell text-yellow-600 mr-2"></i>Appointment Reminders
                    </h3>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Enable Reminders</p>
                                <p class="text-sm text-gray-600">Send email reminders before consultations</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="reminder_enabled" value="1"
                                    <?php echo e(old('reminder_enabled', $settings->reminder_enabled) ? 'checked' : ''); ?>

                                    class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                </div>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Reminder Time (minutes
                                    before)</label>
                                <input type="number" name="reminder_minutes_before"
                                    value="<?php echo e(old('reminder_minutes_before', $settings->reminder_minutes_before)); ?>"
                                    min="5" max="1440"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                <p class="mt-1 text-xs text-gray-500">Default: 30 minutes</p>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">Email Reminders</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="send_email_reminder" value="1"
                                        <?php echo e(old('send_email_reminder', $settings->send_email_reminder) ? 'checked' : ''); ?>

                                        class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-star text-purple-600 mr-2"></i>Feedback & Ratings
                    </h3>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Enable Feedback</p>
                                <p class="text-sm text-gray-600">Allow patients to submit feedback</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="enable_feedback" value="1"
                                    <?php echo e(old('enable_feedback', $settings->enable_feedback) ? 'checked' : ''); ?>

                                    class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                </div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Require Feedback</p>
                                <p class="text-sm text-gray-600">Mandatory feedback after consultation</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="require_feedback" value="1"
                                    <?php echo e(old('require_feedback', $settings->require_feedback) ? 'checked' : ''); ?>

                                    class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-clock text-red-600 mr-2"></i>Consultation Limits
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Timeout (minutes)</label>
                            <input type="number" name="consultation_timeout_minutes"
                                value="<?php echo e(old('consultation_timeout_minutes', $settings->consultation_timeout_minutes)); ?>"
                                min="15" max="240"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            <p class="mt-1 text-xs text-gray-500">Default: 60 minutes</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Max Participants</label>
                            <input type="number" name="max_participants"
                                value="<?php echo e(old('max_participants', $settings->max_participants)); ?>" min="2"
                                max="50"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            <p class="mt-1 text-xs text-gray-500">Default: 10 participants</p>
                        </div>
                    </div>
                </div>

                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-end gap-3">
                        <a href="<?php echo e(route('healthcare.telemedicine.index')); ?>"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                            <i class="fas fa-save mr-2"></i>Save Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function testConnection() {
                const url = document.getElementById('jitsi_server_url').value;
                const resultDiv = document.getElementById('connection-test-result');

                if (!url) {
                    alert('Please enter a Jitsi server URL first');
                    return;
                }

                resultDiv.innerHTML =
                    '<p class="text-sm text-blue-600"><i class="fas fa-spinner fa-spin mr-2"></i>Testing connection...</p>';
                resultDiv.classList.remove('hidden');

                fetch('<?php echo e(route('healthcare.telemedicine.settings.test-connection')); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify({
                            jitsi_server_url: url
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            resultDiv.innerHTML =
                                `<p class="text-sm text-green-600"><i class="fas fa-check-circle mr-2"></i>${data.message}</p>`;
                        } else {
                            resultDiv.innerHTML =
                                `<p class="text-sm text-red-600"><i class="fas fa-times-circle mr-2"></i>${data.message}</p>`;
                        }
                    })
                    .catch(error => {
                        resultDiv.innerHTML =
                            `<p class="text-sm text-red-600"><i class="fas fa-times-circle mr-2"></i>Connection failed</p>`;
                    });
            }

            function resetToDefault() {
                if (confirm('Are you sure you want to reset all settings to default?')) {
                    fetch('<?php echo e(route('healthcare.telemedicine.settings.reset')); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                            }
                        })
                        .then(response => {
                            window.location.reload();
                        });
                }
            }
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\telemedicine\settings.blade.php ENDPATH**/ ?>