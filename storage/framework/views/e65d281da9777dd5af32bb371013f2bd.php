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
     <?php $__env->slot('header', null, []); ?> Room Types <?php $__env->endSlot(); ?>

    <div x-data="roomTypeManager()" class="space-y-6">
        
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Room Types</h2>
                <p class="text-sm text-gray-500">Define room categories and pricing</p>
            </div>
            <button @click="openAddModal"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Room Type
            </button>
        </div>

        
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Room Type</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Code</th>
                            <th class="px-4 py-3 text-right">Base Rate</th>
                            <th class="px-4 py-3 text-center hidden md:table-cell">Occupancy</th>
                            <th class="px-4 py-3 text-center hidden lg:table-cell">Amenities</th>
                            <th class="px-4 py-3 text-center">Rooms</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__empty_1 = true; $__currentLoopData = $roomTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900"><?php echo e($rt->name); ?></p>
                                    <?php if($rt->description): ?>
                                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-1">
                                            <?php echo e(Str::limit($rt->description, 50)); ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 hidden sm:table-cell">
                                    <span
                                        class="px-2 py-0.5 text-xs font-mono bg-gray-100 rounded text-gray-600">
                                        <?php echo e($rt->code); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900">
                                    Rp <?php echo e(number_format($rt->base_rate, 0, ',', '.')); ?>

                                </td>
                                <td
                                    class="px-4 py-3 text-center hidden md:table-cell text-gray-600">
                                    <span class="text-sm"><?php echo e($rt->base_occupancy ?? 1); ?></span>
                                    <?php if($rt->max_occupancy && $rt->max_occupancy > $rt->base_occupancy): ?>
                                        <span class="text-gray-400">-
                                            <?php echo e($rt->max_occupancy); ?></span>
                                    <?php endif; ?>
                                    <span class="text-xs text-gray-400 ml-1">guests</span>
                                </td>
                                <td class="px-4 py-3 hidden lg:table-cell">
                                    <div class="flex flex-wrap gap-1">
                                        <?php $__currentLoopData = $rt->amenities ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $amenity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span
                                                class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full">
                                                <?php echo e($amenity); ?>

                                            </span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(empty($rt->amenities) || count($rt->amenities) === 0): ?>
                                            <span class="text-xs text-gray-400">-</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-sm font-medium text-gray-700">
                                        <?php echo e($rt->rooms_count); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-xs <?php echo e($rt->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'); ?>">
                                        <?php echo e($rt->is_active ? 'Active' : 'Inactive'); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button @click="openEditModal(<?php echo e($rt->id); ?>)"
                                            class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100"
                                            title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <form method="POST" action="<?php echo e(route('hotel.room-types.destroy', $rt)); ?>"
                                            class="inline" onsubmit="return confirm('Delete this room type?')">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit"
                                                class="p-1.5 rounded-lg text-red-500 hover:bg-red-50"
                                                title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                                    No room types defined yet. <button @click="openAddModal"
                                        class="text-blue-500 hover:underline">Create the first one</button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div @click.away="showModal = false"
                class="bg-white rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">

                
                <div
                    class="sticky top-0 bg-white flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900"
                        x-text="isEdit ? 'Edit Room Type' : 'Add Room Type'"></h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                
                <form
                    :action="isEdit ? '<?php echo e(url('hotel/room-types')); ?>/' + form.id : '<?php echo e(route('hotel.room-types.store')); ?>'"
                    method="POST" class="p-6 space-y-4">
                    <?php echo csrf_field(); ?>
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Name
                                *</label>
                            <input type="text" name="name" x-model="form.name" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g. Deluxe Suite">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Code
                                *</label>
                            <input type="text" name="code" x-model="form.code" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g. DLX">
                        </div>

                        <div class="sm:col-span-2">
                            <label
                                class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                            <textarea name="description" x-model="form.description" rows="2"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900"
                                placeholder="Brief description..."></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Base
                                Occupancy</label>
                            <input type="number" name="base_occupancy" x-model="form.base_occupancy" min="1"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Max
                                Occupancy</label>
                            <input type="number" name="max_occupancy" x-model="form.max_occupancy" min="1"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Base Rate
                                (IDR) *</label>
                            <input type="number" name="base_rate" x-model="form.base_rate" min="0"
                                step="1000" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        </div>

                        <div class="sm:col-span-2">
                            <label
                                class="block text-xs font-medium text-gray-600 mb-1">Amenities</label>
                            <div class="flex flex-wrap gap-2 mb-2">
                                <template x-for="(amenity, index) in form.amenities" :key="index">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-full">
                                        <span x-text="amenity"></span>
                                        <button type="button" @click="form.amenities.splice(index, 1)"
                                            class="hover:text-red-500">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </span>
                                </template>
                            </div>
                            <div class="flex gap-2">
                                <input type="text" x-model="newAmenity" @keyup.enter="addAmenity"
                                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900"
                                    placeholder="Add amenity...">
                                <button type="button" @click="addAmenity"
                                    class="px-3 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                                    Add
                                </button>
                            </div>
                            
                            <template x-for="(amenity, index) in form.amenities" :key="'hidden-' + index">
                                <input type="hidden" name="amenities[]" :value="amenity">
                            </template>
                        </div>

                        <div class="sm:col-span-2 flex items-center gap-2" x-show="isEdit">
                            <input type="checkbox" name="is_active" id="rt_is_active" value="1"
                                x-model="form.is_active" class="rounded border-gray-300">
                            <label for="rt_is_active" class="text-sm text-gray-700">Room type is
                                active</label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showModal = false"
                            class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                            <span x-text="isEdit ? 'Update' : 'Create'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <script>
        window.roomTypeManager = function() {
            return {
                showModal: false,
                isEdit: false,
                newAmenity: '',
                form: {
                    id: '',
                    name: '',
                    code: '',
                    description: '',
                    base_occupancy: 2,
                    max_occupancy: 4,
                    base_rate: 0,
                    amenities: [],
                    is_active: true,
                },
                roomTypes: <?php echo json_encode($roomTypes, 15, 512) ?>,

                openAddModal() {
                    this.isEdit = false;
                    this.newAmenity = '';
                    this.form = {
                        id: '',
                        name: '',
                        code: '',
                        description: '',
                        base_occupancy: 2,
                        max_occupancy: 4,
                        base_rate: 0,
                        amenities: [],
                        is_active: true,
                    };
                    this.showModal = true;
                },

                openEditModal(rtId) {
                    const rt = this.roomTypes.find(r => r.id === rtId);
                    if (!rt) return;

                    this.isEdit = true;
                    this.newAmenity = '';
                    this.form = {
                        id: rt.id,
                        name: rt.name,
                        code: rt.code,
                        description: rt.description || '',
                        base_occupancy: rt.base_occupancy || 2,
                        max_occupancy: rt.max_occupancy || 4,
                        base_rate: rt.base_rate,
                        amenities: rt.amenities || [],
                        is_active: rt.is_active,
                    };
                    this.showModal = true;
                },

                addAmenity() {
                    const amenity = this.newAmenity.trim();
                    if (amenity && !this.form.amenities.includes(amenity)) {
                        this.form.amenities.push(amenity);
                        this.newAmenity = '';
                    }
                }
            }
        };

        function showToast(message, type = 'success') {
            const colors = {
                success: 'bg-green-600',
                error: 'bg-red-600',
                warning: 'bg-yellow-500',
                info: 'bg-blue-600',
            };
            const icons = {
                success: '✓',
                error: '✕',
                warning: '⚠',
                info: 'ℹ'
            };
            const toast = document.createElement('div');
            toast.className =
                `fixed bottom-6 right-6 z-[9999] flex items-center gap-3 px-4 py-3 rounded-2xl text-white text-sm font-medium shadow-xl transition-all duration-300 translate-y-4 opacity-0 ${colors[type] || colors.success}`;
            toast.innerHTML = `<span class="text-base">${icons[type] || icons.success}</span><span>${message}</span>`;
            document.body.appendChild(toast);
            requestAnimationFrame(() => toast.classList.remove('translate-y-4', 'opacity-0'));
            setTimeout(() => {
                toast.classList.add('translate-y-4', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        <?php if(session('success')): ?>
            showToast(<?php echo json_encode(session('success'), 15, 512) ?>, 'success');
        <?php endif; ?>
        <?php if(session('error')): ?>
            showToast(<?php echo json_encode(session('error'), 15, 512) ?>, 'error');
        <?php endif; ?>
        <?php if($errors->any()): ?>
            showToast(<?php echo json_encode($errors->first(), 15, 512) ?>, 'error');
        <?php endif; ?>
    </script>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\room-types\index.blade.php ENDPATH**/ ?>