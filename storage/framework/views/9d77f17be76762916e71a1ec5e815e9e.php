

<?php $__env->startSection('title', 'Create Workflow'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="<?php echo e(route('automation.workflows.index')); ?>" class="text-blue-600 hover:text-blue-900 text-sm">
                ← Back to Workflows
            </a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Create New Workflow</h1>
        </div>

        <form action="<?php echo e(route('automation.workflows.store')); ?>" method="POST" x-data="{ triggerType: 'event' }">
            <?php echo csrf_field(); ?>

            <!-- Basic Information -->
            <div class="bg-white shadow sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Basic Information</h3>

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Workflow Name</label>
                            <input type="text" name="name" id="name" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="e.g., Auto-Create PO When Stock Low">
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Describe what this workflow does..."></textarea>
                        </div>

                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700">Priority (0-100)</label>
                            <input type="number" name="priority" id="priority" value="0" min="0"
                                max="100"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <p class="mt-1 text-xs text-gray-500">Higher priority workflows execute first</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trigger Configuration -->
            <div class="bg-white shadow sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Trigger Configuration</h3>

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Trigger Type</label>
                            <div class="space-y-2">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="trigger_type" value="event" x-model="triggerType" checked
                                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                    <span class="ml-2">Event-Based</span>
                                </label>
                                <label class="inline-flex items-center ml-6">
                                    <input type="radio" name="trigger_type" value="schedule" x-model="triggerType"
                                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                    <span class="ml-2">Scheduled</span>
                                </label>
                                <label class="inline-flex items-center ml-6">
                                    <input type="radio" name="trigger_type" value="condition" x-model="triggerType"
                                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                    <span class="ml-2">Condition-Based</span>
                                </label>
                            </div>
                        </div>

                        <!-- Event Trigger Config -->
                        <div x-show="triggerType === 'event'" class="border-t pt-4">
                            <label for="event_name" class="block text-sm font-medium text-gray-700">Event Name</label>
                            <select name="trigger_config[event]" id="event_name"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Select an event...</option>
                                <option value="inventory.stock_low">Inventory Stock Low</option>
                                <option value="inventory.stock_updated">Inventory Stock Updated</option>
                                <option value="sales.order_completed">Sales Order Completed</option>
                                <option value="invoice.overdue">Invoice Overdue</option>
                                <option value="invoice.paid">Invoice Paid</option>
                                <option value="customer.created">Customer Created</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Choose the event that will trigger this workflow</p>
                        </div>

                        <!-- Schedule Trigger Config -->
                        <div x-show="triggerType === 'schedule'" class="border-t pt-4">
                            <label for="schedule_type" class="block text-sm font-medium text-gray-700">Schedule</label>
                            <select name="trigger_config[schedule]" id="schedule_type"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Select schedule...</option>
                                <option value="every_minute">Every Minute</option>
                                <option value="hourly">Hourly</option>
                                <option value="daily_9am">Daily at 9 AM</option>
                                <option value="daily_midnight">Daily at Midnight</option>
                                <option value="weekly_monday">Weekly on Monday</option>
                                <option value="monthly_first">Monthly on 1st</option>
                                <option value="invoice_overdue_check">Invoice Overdue Check (Daily 9 AM)</option>
                                <option value="monthly_bonus_calculation">Monthly Bonus Calculation</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Status -->
            <div class="bg-white shadow sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Activate workflow immediately
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-3">
                <a href="<?php echo e(route('automation.workflows.index')); ?>"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Create Workflow
                </button>
            </div>
        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\automation\workflows\create.blade.php ENDPATH**/ ?>