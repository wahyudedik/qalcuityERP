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
     <?php $__env->slot('header', null, []); ?> Room Management <?php $__env->endSlot(); ?>

    <div x-data="roomManager()" class="space-y-6">
        
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Rooms</h2>
                <p class="text-sm text-gray-500">Manage hotel rooms and their status</p>
            </div>
            <button @click="openAddModal"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Room
            </button>
        </div>

        
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                        placeholder="Search room number..."
                        class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">

                    <select name="type"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        <option value="">All Types</option>
                        <?php $__currentLoopData = $roomTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($rt->id); ?>" <?php if(request('type') == $rt->id): echo 'selected'; endif; ?>><?php echo e($rt->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <select name="floor"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        <option value="">All Floors</option>
                        <?php $__currentLoopData = $floors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($f); ?>" <?php if(request('floor') == $f): echo 'selected'; endif; ?>><?php echo e($f); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <select name="status"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        <option value="">All Status</option>
                        <?php $__currentLoopData = ['available', 'occupied', 'cleaning', 'maintenance', 'out_of_order']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($s); ?>" <?php if(request('status') == $s): echo 'selected'; endif; ?>>
                                <?php echo e(ucfirst(str_replace('_', ' ', $s))); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
                    <a href="<?php echo e(route('hotel.rooms.index')); ?>"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 text-center">Reset</a>
                </form>
            </div>
        </div>

        
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
            <?php $__empty_1 = true; $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $statusConfig = [
                        'available' => [
                            'bg' => 'bg-green-500',
                            'border' => 'border-green-500',
                            'text' => 'text-green-600',
                        ],
                        'occupied' => [
                            'bg' => 'bg-red-500',
                            'border' => 'border-red-500',
                            'text' => 'text-red-600',
                        ],
                        'cleaning' => [
                            'bg' => 'bg-yellow-500',
                            'border' => 'border-yellow-500',
                            'text' => 'text-yellow-600',
                        ],
                        'maintenance' => [
                            'bg' => 'bg-orange-500',
                            'border' => 'border-orange-500',
                            'text' => 'text-orange-600',
                        ],
                        'out_of_order' => [
                            'bg' => 'bg-gray-500',
                            'border' => 'border-gray-500',
                            'text' => 'text-gray-600',
                        ],
                    ];
                    $cfg = $statusConfig[$room->status] ?? $statusConfig['available'];
                ?>
                <div
                    class="bg-white rounded-2xl border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
                    
                    <div class="<?php echo e($cfg['bg']); ?> h-1.5"></div>

                    <div class="p-4">
                        
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-xl font-bold text-gray-900"><?php echo e($room->number); ?></h3>
                            <span class="w-2.5 h-2.5 rounded-full <?php echo e($cfg['bg']); ?>"
                                title="<?php echo e(ucfirst($room->status)); ?>"></span>
                        </div>

                        
                        <p class="text-xs text-gray-500 mb-1">
                            <?php echo e($room->roomType?->name ?? 'No Type'); ?>

                        </p>
                        <p class="text-xs text-gray-400">
                            <?php if($room->floor): ?>
                                Floor <?php echo e($room->floor); ?>

                            <?php endif; ?>
                            <?php if($room->building): ?>
                                · <?php echo e($room->building); ?>

                            <?php endif; ?>
                        </p>

                        
                        <div class="mt-3">
                            <span
                                class="px-2 py-0.5 rounded-full text-xs <?php echo e($cfg['text']); ?> bg-opacity-10 bg-current">
                                <?php echo e(ucfirst(str_replace('_', ' ', $room->status))); ?>

                            </span>
                        </div>

                        
                        <div class="mt-3 flex items-center gap-1">
                            
                            <select @change="changeStatus(<?php echo e($room->id); ?>, $el.value)"
                                class="flex-1 text-xs px-2 py-1.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-700 cursor-pointer">
                                <?php $__currentLoopData = ['available', 'occupied', 'cleaning', 'maintenance', 'out_of_order']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($s); ?>" <?php if($room->status === $s): echo 'selected'; endif; ?>>
                                        <?php echo e(ucfirst(str_replace('_', ' ', $s))); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>

                            
                            <button @click="openEditModal(<?php echo e($room->id); ?>)"
                                class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100"
                                title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>

                            
                            <form method="POST" action="<?php echo e(route('hotel.rooms.destroy', $room)); ?>" class="inline"
                                onsubmit="return confirm('Delete this room?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit"
                                    class="p-1.5 rounded-lg text-red-500 hover:bg-red-50"
                                    title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-full">
                    <div
                        class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m4-4h1m-1 4h1" />
                        </svg>
                        <p class="text-gray-500">No rooms found.</p>
                        <button @click="openAddModal" class="mt-4 text-blue-500 hover:underline">Add your first
                            room</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        
        <?php if($rooms->hasPages()): ?>
            <div class="bg-white rounded-2xl border border-gray-200 px-4 py-3">
                <?php echo e($rooms->links()); ?>

            </div>
        <?php endif; ?>

        
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div @click.away="showModal = false"
                class="bg-white rounded-2xl w-full max-w-md shadow-xl"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">

                
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900"
                        x-text="isEdit ? 'Edit Room' : 'Add Room'"></h3>
                    <button @click="showModal = false"
                        class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                
                <form :action="isEdit ? '<?php echo e(url('hotel/rooms')); ?>/' + form.id : '<?php echo e(route('hotel.rooms.store')); ?>'"
                    method="POST" class="p-6 space-y-4">
                    <?php echo csrf_field(); ?>
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Room Number
                                *</label>
                            <input type="text" name="number" x-model="form.number" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Room Type
                                *</label>
                            <select name="room_type_id" x-model="form.room_type_id" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                                <option value="">Select type...</option>
                                <?php $__currentLoopData = $roomTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($rt->id); ?>"><?php echo e($rt->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-medium text-gray-600 mb-1">Floor</label>
                            <input type="text" name="floor" x-model="form.floor"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        </div>

                        <div>
                            <label
                                class="block text-xs font-medium text-gray-600 mb-1">Building</label>
                            <input type="text" name="building" x-model="form.building"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Status
                                *</label>
                            <select name="status" x-model="form.status" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                                <?php $__currentLoopData = ['available', 'occupied', 'cleaning', 'maintenance', 'out_of_order']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($s); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $s))); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="col-span-2">
                            <label
                                class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
                            <textarea name="description" x-model="form.description" rows="2"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900"></textarea>
                        </div>

                        <?php if(isset($editRoom) && $editRoom): ?>
                            <div class="col-span-2 flex items-center gap-2">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                    x-model="form.is_active" class="rounded border-gray-300">
                                <label for="is_active" class="text-sm text-gray-700">Room is
                                    active</label>
                            </div>
                        <?php endif; ?>
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
        window.roomManager = function() {
            return {
                showModal: false,
                isEdit: false,
                form: {
                    id: '',
                    number: '',
                    room_type_id: '',
                    floor: '',
                    building: '',
                    status: 'available',
                    description: '',
                    is_active: true,
                },
                rooms: <?php echo json_encode($rooms->items(), 15, 512) ?>,

                openAddModal() {
                    this.isEdit = false;
                    this.form = {
                        id: '',
                        number: '',
                        room_type_id: '',
                        floor: '',
                        building: '',
                        status: 'available',
                        description: '',
                        is_active: true,
                    };
                    this.showModal = true;
                },

                openEditModal(roomId) {
                    const room = this.rooms.find(r => r.id === roomId);
                    if (!room) return;

                    this.isEdit = true;
                    this.form = {
                        id: room.id,
                        number: room.number,
                        room_type_id: String(room.room_type_id),
                        floor: room.floor || '',
                        building: room.building || '',
                        status: room.status,
                        description: room.description || '',
                        is_active: room.is_active,
                    };
                    this.showModal = true;
                },

                changeStatus(roomId, newStatus) {
                    fetch(`<?php echo e(url('hotel/rooms')); ?>/${roomId}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                status: newStatus
                            }),
                        })
                        .then(res => {
                            if (!res.ok) throw new Error(`HTTP ${res.status}`);
                            return res.json();
                        })
                        .then(data => {
                            if (data.success) {
                                showToast(data.message || 'Room status updated', 'success');
                                setTimeout(() => location.reload(), 500);
                            } else {
                                showToast(data.message || 'Failed to update status', 'error');
                            }
                        })
                        .catch(err => showToast('Failed to update status: ' + err.message, 'error'));
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\rooms\index.blade.php ENDPATH**/ ?>