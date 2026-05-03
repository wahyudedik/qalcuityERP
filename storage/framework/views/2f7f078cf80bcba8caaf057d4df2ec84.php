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
     <?php $__env->slot('header', null, []); ?> Tiket Support <?php $__env->endSlot(); ?>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex gap-2">
            <select name="status"
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
                <option value="">Semua Status</option>
                <?php $__currentLoopData = ['open' => 'Terbuka', 'in_progress' => 'Diproses', 'resolved' => 'Selesai', 'closed' => 'Ditutup']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($v); ?>" <?php if(request('status') === $v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
        </form>
        <button onclick="document.getElementById('modal-new-ticket').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Buat Tiket</button>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No. Tiket</th>
                        <th class="px-4 py-3 text-left">Subjek</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Prioritas</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $tc = match ($ticket->status) {
                                'open' => 'amber',
                                'in_progress' => 'blue',
                                'resolved' => 'green',
                                'closed' => 'gray',
                                default => 'gray',
                            };
                            $pc = match ($ticket->priority ?? 'medium') {
                                'urgent' => 'red',
                                'high' => 'orange',
                                'medium' => 'blue',
                                'low' => 'gray',
                                default => 'gray',
                            };
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs text-gray-900">
                                <?php echo e($ticket->ticket_number ?? '#' . $ticket->id); ?></td>
                            <td class="px-4 py-3 text-gray-900"><?php echo e(Str::limit($ticket->subject, 40)); ?>

                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($pc); ?>-100 text-<?php echo e($pc); ?>-700 $pc }}-500/20 $pc }}-400"><?php echo e(ucfirst($ticket->priority ?? 'medium')); ?></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($tc); ?>-100 text-<?php echo e($tc); ?>-700 $tc }}-500/20 $tc }}-400"><?php echo e(ucfirst(str_replace('_', ' ', $ticket->status))); ?></span>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell text-gray-500 text-xs">
                                <?php echo e($ticket->created_at?->format('d/m/Y')); ?></td>
                            <td class="px-4 py-3 text-center">
                                <a href="<?php echo e(route('customer-portal.tickets.show', $ticket)); ?>"
                                    class="text-blue-600 hover:underline text-xs">Lihat</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-400">Belum
                                ada tiket support.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($tickets->hasPages()): ?>
            <div class="px-4 py-3 border-t border-gray-100"><?php echo e($tickets->links()); ?></div>
        <?php endif; ?>
    </div>

    
    <div id="modal-new-ticket" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Buat Tiket Baru</h3>
                <button onclick="document.getElementById('modal-new-ticket').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('customer-portal.tickets.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Subjek *</label>
                    <input type="text" name="subject" required
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi *</label>
                    <textarea name="description" required rows="4"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Prioritas</label>
                    <select name="priority"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="low">Rendah</option>
                        <option value="medium" selected>Sedang</option>
                        <option value="high">Tinggi</option>
                        <option value="urgent">Mendesak</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-new-ticket').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Kirim
                        Tiket</button>
                </div>
            </form>
        </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\customer-portal\tickets\index.blade.php ENDPATH**/ ?>