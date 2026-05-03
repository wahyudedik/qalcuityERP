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
     <?php $__env->slot('header', null, []); ?> | <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('printing.dashboard')); ?>"
                    class="text-gray-500 hover:text-gray-700 transition text-sm">
                    ← Back
                </a>
    </div>

    <div class="max-w-4xl mx-auto">
        <form action="<?php echo e(route('printing.store')); ?>" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Job Name
                            *</label>
                        <input type="text" name="job_name" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="e.g., Business Cards - ABC Company">
                        <?php $__errorArgs = ['job_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Customer</label>
                        <select name="customer_id"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Select Customer (Optional)</option>
                            <?php $__currentLoopData = \App\Models\Customer::where('tenant_id', auth()->user()->tenant_id)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($customer->id); ?>"><?php echo e($customer->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Product Type
                            *</label>
                        <select name="product_type" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Select Product Type</option>
                            <option value="business_cards">Business Cards</option>
                            <option value="flyers">Flyers</option>
                            <option value="brochures">Brochures</option>
                            <option value="posters">Posters</option>
                            <option value="banners">Banners</option>
                            <option value="catalogs">Catalogs</option>
                            <option value="magazines">Magazines</option>
                            <option value="books">Books</option>
                            <option value="packaging">Packaging</option>
                            <option value="labels">Labels</option>
                            <option value="other">Other</option>
                        </select>
                        <?php $__errorArgs = ['product_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quantity
                            *</label>
                        <input type="number" name="quantity" required min="1"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="e.g., 1000">
                        <?php $__errorArgs = ['quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                        <select name="priority"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="normal" selected>Normal</option>
                            <option value="low">Low</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                        <input type="date" name="due_date"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Specifications</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Paper
                            Type</label>
                        <select name="paper_type"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Select Paper Type</option>
                            <option value="art_paper_120gsm">Art Paper 120 gsm</option>
                            <option value="art_paper_150gsm">Art Paper 150 gsm</option>
                            <option value="art_paper_210gsm">Art Paper 210 gsm</option>
                            <option value="art_carton_260gsm">Art Carton 260 gsm</option>
                            <option value="art_carton_310gsm">Art Carton 310 gsm</option>
                            <option value="hvs_70gsm">HVS 70 gsm</option>
                            <option value="hvs_80gsm">HVS 80 gsm</option>
                            <option value="bookpaper_52gsm">Bookpaper 52 gsm</option>
                            <option value="bookpaper_57gsm">Bookpaper 57 gsm</option>
                            <option value="ivory_210gsm">Ivory 210 gsm</option>
                            <option value="ivory_230gsm">Ivory 230 gsm</option>
                            <option value="ivory_250gsm">Ivory 250 gsm</option>
                            <option value="sticker_paper">Sticker Paper</option>
                            <option value="synthetic_paper">Synthetic Paper</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Paper
                            Size</label>
                        <select name="paper_size"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Select Size</option>
                            <option value="A4">A4 (210 x 297 mm)</option>
                            <option value="A3">A3 (297 x 420 mm)</option>
                            <option value="A5">A5 (148 x 210 mm)</option>
                            <option value="F4">F4 (215 x 330 mm)</option>
                            <option value="Letter">Letter (216 x 279 mm)</option>
                            <option value="Legal">Legal (216 x 356 mm)</option>
                            <option value="Custom">Custom Size</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Colors
                            (Front)</label>
                        <select name="colors_front"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="0">0 (Black & White)</option>
                            <option value="1">1 Color</option>
                            <option value="2">2 Colors</option>
                            <option value="4" selected>4 Colors (CMYK)</option>
                            <option value="5">5 Colors (CMYK + Spot)</option>
                            <option value="6">6 Colors</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Colors
                            (Back)</label>
                        <select name="colors_back"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="0" selected>0 (No Print)</option>
                            <option value="1">1 Color</option>
                            <option value="2">2 Colors</option>
                            <option value="4">4 Colors (CMYK)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Finishing
                            Options</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="finishing[]" value="lamination"
                                    class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">Lamination</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="finishing[]" value="binding"
                                    class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">Binding</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="finishing[]" value="cutting"
                                    class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">Cutting</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="finishing[]" value="folding"
                                    class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">Folding</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Special
                            Instructions</label>
                        <textarea name="special_instructions" rows="4"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Any special requirements or notes..."></textarea>
                    </div>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Pricing (Optional)</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quoted
                            Price</label>
                        <input type="number" name="quoted_price" step="0.01"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="0.00">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estimated
                            Cost</label>
                        <input type="number" name="estimated_cost" step="0.01"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="0.00">
                    </div>
                </div>
            </div>

            
            <div class="flex items-center justify-end gap-3">
                <a href="<?php echo e(route('printing.dashboard')); ?>"
                    class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-sm font-medium">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
                    Create Print Job
                </button>
            </div>
        </form>
    </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\printing\create-job.blade.php ENDPATH**/ ?>