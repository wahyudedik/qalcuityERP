

<?php $__env->startSection('title', 'Scheduled Reports'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Scheduled Reports</h1>
                    <p class="mt-2 text-sm text-gray-600">Automate report generation and email delivery</p>
                </div>
                <div class="flex gap-3">
                    <button onclick="openCreateModal()"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-plus mr-2"></i>Create Schedule
                    </button>
                    <a href="<?php echo e(route('analytics.advanced')); ?>"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Scheduled Reports List -->
        <?php if(count($schedules) > 0): ?>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Report Name</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Frequency</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Metrics</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recipients</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Format</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Next Run</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Last Run</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo e($schedule->name); ?></div>
                                        <?php if($schedule->description): ?>
                                            <div class="text-xs text-gray-500"><?php echo e($schedule->description); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php if($schedule->frequency == 'daily'): ?>
                                            <span
                                                class="px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">
                                                <i class="fas fa-calendar-day mr-1"></i>Daily
                                            </span>
                                        <?php elseif($schedule->frequency == 'weekly'): ?>
                                            <span
                                                class="px-2 py-1 text-xs font-semibold bg-purple-100 text-purple-800 rounded-full">
                                                <i class="fas fa-calendar-week mr-1"></i>Weekly
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="px-2 py-1 text-xs font-semibold bg-indigo-100 text-indigo-800 rounded-full">
                                                <i class="fas fa-calendar-alt mr-1"></i>Monthly
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            <?php $__currentLoopData = $schedule->metrics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">
                                                    <?php echo e(ucfirst($metric)); ?>

                                                </span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            <?php echo e(count($schedule->recipients)); ?> recipient(s)
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <?php echo e(implode(', ', array_slice($schedule->recipients, 0, 2))); ?>

                                            <?php if(count($schedule->recipients) > 2): ?>
                                                +<?php echo e(count($schedule->recipients) - 2); ?> more
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php if($schedule->format == 'pdf'): ?>
                                            <i class="fas fa-file-pdf text-red-500 text-xl"></i>
                                        <?php elseif($schedule->format == 'excel'): ?>
                                            <i class="fas fa-file-excel text-green-500 text-xl"></i>
                                        <?php else: ?>
                                            <i class="fas fa-file-csv text-blue-500 text-xl"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php if($schedule->is_active): ?>
                                            <span
                                                class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">
                                                Active
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-800 rounded-full">
                                                Inactive
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="text-sm text-gray-900">
                                            <?php if($schedule->next_run): ?>
                                                <?php echo e($schedule->next_run->format('M d, Y')); ?>

                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <?php if($schedule->next_run): ?>
                                                <?php echo e($schedule->next_run->format('H:i')); ?>

                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="text-sm text-gray-900">
                                            <?php if($schedule->last_run_at): ?>
                                                <?php echo e($schedule->last_run_at->diffForHumans()); ?>

                                            <?php else: ?>
                                                Never
                                            <?php endif; ?>
                                        </div>
                                        <?php if($schedule->last_status): ?>
                                            <div
                                                class="text-xs <?php echo e($schedule->last_status == 'success' ? 'text-green-600' : 'text-red-600'); ?>">
                                                <i
                                                    class="fas fa-<?php echo e($schedule->last_status == 'success' ? 'check-circle' : 'times-circle'); ?>"></i>
                                                <?php echo e(ucfirst($schedule->last_status)); ?>

                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button onclick="toggleSchedule(<?php echo e($schedule->id); ?>)"
                                                class="px-3 py-1 text-xs <?php echo e($schedule->is_active ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200' : 'bg-green-100 text-green-800 hover:bg-green-200'); ?> rounded transition">
                                                <i class="fas fa-<?php echo e($schedule->is_active ? 'pause' : 'play'); ?>"></i>
                                                <?php echo e($schedule->is_active ? 'Pause' : 'Resume'); ?>

                                            </button>
                                            <button onclick="deleteSchedule(<?php echo e($schedule->id); ?>)"
                                                class="px-3 py-1 text-xs bg-red-100 text-red-800 hover:bg-red-200 rounded transition">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <i class="fas fa-clock text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Scheduled Reports</h3>
                <p class="text-gray-500 mb-6">Create your first scheduled report to automate report delivery</p>
                <button onclick="openCreateModal()"
                    class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-plus mr-2"></i>Create Your First Schedule
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Create Schedule Modal -->
    <div id="createModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-900">Create Scheduled Report</h3>
                    <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <form action="<?php echo e(route('analytics.create-scheduled-report')); ?>" method="POST" class="p-6 space-y-6">
                <?php echo csrf_field(); ?>

                <!-- Report Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Report Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Weekly Sales Summary"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                    <textarea name="description" rows="2" placeholder="Brief description of this report"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <!-- Metrics -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Metrics *</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label
                            class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-indigo-300">
                            <input type="checkbox" name="metrics[]" value="revenue" class="text-indigo-600" checked>
                            <span class="ml-2">Revenue</span>
                        </label>
                        <label
                            class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-indigo-300">
                            <input type="checkbox" name="metrics[]" value="orders" class="text-indigo-600" checked>
                            <span class="ml-2">Orders</span>
                        </label>
                        <label
                            class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-indigo-300">
                            <input type="checkbox" name="metrics[]" value="customers" class="text-indigo-600">
                            <span class="ml-2">Customers</span>
                        </label>
                        <label
                            class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-indigo-300">
                            <input type="checkbox" name="metrics[]" value="inventory" class="text-indigo-600">
                            <span class="ml-2">Inventory</span>
                        </label>
                    </div>
                </div>

                <!-- Frequency -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Frequency *</label>
                    <select name="frequency" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="daily">Daily</option>
                        <option value="weekly" selected>Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>

                <!-- Recipients -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Recipients *</label>
                    <input type="text" name="recipients_input" required
                        placeholder="email1@example.com, email2@example.com"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">Separate multiple emails with commas</p>
                </div>

                <!-- Format -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Export Format *</label>
                    <div class="grid grid-cols-3 gap-3">
                        <label
                            class="flex items-center p-3 border-2 border-indigo-600 bg-indigo-50 rounded-lg cursor-pointer">
                            <input type="radio" name="format" value="pdf" class="text-indigo-600" checked>
                            <div class="ml-2">
                                <i class="fas fa-file-pdf text-red-500 mr-1"></i>
                                <span>PDF</span>
                            </div>
                        </label>
                        <label
                            class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-indigo-300">
                            <input type="radio" name="format" value="excel" class="text-indigo-600">
                            <div class="ml-2">
                                <i class="fas fa-file-excel text-green-500 mr-1"></i>
                                <span>Excel</span>
                            </div>
                        </label>
                        <label
                            class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-indigo-300">
                            <input type="radio" name="format" value="csv" class="text-indigo-600">
                            <div class="ml-2">
                                <i class="fas fa-file-csv text-blue-500 mr-1"></i>
                                <span>CSV</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex gap-3 pt-4">
                    <button type="submit"
                        class="flex-1 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-semibold">
                        <i class="fas fa-save mr-2"></i>Create Schedule
                    </button>
                    <button type="button" onclick="closeCreateModal()"
                        class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
        }

        function toggleSchedule(id) {
            if (confirm('Are you sure you want to toggle this schedule?')) {
                // TODO: Implement toggle endpoint
                alert('Toggle functionality will be implemented');
            }
        }

        function deleteSchedule(id) {
            if (confirm('Are you sure you want to delete this schedule? This action cannot be undone.')) {
                // TODO: Implement delete endpoint
                alert('Delete functionality will be implemented');
            }
        }

        // Close modal on outside click
        document.getElementById('createModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCreateModal();
            }
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\analytics\scheduled-reports.blade.php ENDPATH**/ ?>