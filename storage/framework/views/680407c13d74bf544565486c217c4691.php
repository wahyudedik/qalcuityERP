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
     <?php $__env->slot('header', null, []); ?> Sales Order <?php $__env->endSlot(); ?>

    <div class="space-y-5">

        <?php if(session('success')): ?>
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <?php
                $statCards = [
                    ['label' => 'Pending',   'value' => $stats['pending'],   'color' => 'yellow'],
                    ['label' => 'Confirmed', 'value' => $stats['confirmed'], 'color' => 'blue'],
                    ['label' => 'Shipped',   'value' => $stats['shipped'],   'color' => 'purple'],
                    ['label' => 'Completed', 'value' => $stats['completed'], 'color' => 'green'],
                    ['label' => 'Revenue Bulan Ini', 'value' => 'Rp ' . number_format($stats['this_month'], 0, ',', '.'), 'color' => 'emerald', 'wide' => true],
                ];
            ?>
            <?php $__currentLoopData = $statCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4 <?php echo e(($card['wide'] ?? false) ? 'col-span-2 sm:col-span-1' : ''); ?>">
                    <p class="text-xs text-gray-500 dark:text-slate-400"><?php echo e($card['label']); ?></p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white mt-1"><?php echo e($card['value']); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nomor / customer..."
                    class="flex-1 min-w-[180px] bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="status" class="bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none">
                    <option value="">Semua Status</option>
                    <?php $__currentLoopData = ['pending','confirmed','processing','shipped','completed','cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s); ?>" <?php echo e(request('status') === $s ? 'selected' : ''); ?>><?php echo e(ucfirst($s)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>"
                    class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none">
                <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>"
                    class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none">
                <button type="submit" class="px-4 py-2 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-white rounded-xl text-sm hover:bg-gray-200 dark:hover:bg-white/20 transition">Filter</button>
                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'sales', 'create')): ?>
                <a href="<?php echo e(route('sales.create')); ?>" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">+ Buat SO</a>
                <?php endif; ?>
            </form>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <?php if($orders->isEmpty()): ?>
                <div class="px-6 py-16 text-center text-gray-400 dark:text-slate-500 text-sm">Belum ada Sales Order.</div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-white/5 text-xs text-gray-500 dark:text-slate-400">
                                <th class="px-4 py-3 text-left">Nomor</th>
                                <th class="px-4 py-3 text-left">Customer</th>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-right">Total</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Pembayaran</th>
                                <th class="px-4 py-3 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                            <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $statusColors = [
                                        'pending'    => 'bg-yellow-500/20 text-yellow-400',
                                        'confirmed'  => 'bg-blue-500/20 text-blue-400',
                                        'processing' => 'bg-purple-500/20 text-purple-400',
                                        'shipped'    => 'bg-indigo-500/20 text-indigo-400',
                                        'completed'  => 'bg-green-500/20 text-green-400',
                                        'cancelled'  => 'bg-red-500/20 text-red-400',
                                    ];
                                ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                    <td class="px-4 py-3 font-mono text-xs text-blue-400">
                                        <a href="<?php echo e(route('sales.show', $order)); ?>"><?php echo e($order->number); ?></a>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-slate-300"><?php echo e($order->customer->name ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-slate-400 text-xs"><?php echo e($order->date->format('d/m/Y')); ?></td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">
                                        Rp <?php echo e(number_format($order->total, 0, ',', '.')); ?>

                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-full text-xs <?php echo e($statusColors[$order->status] ?? ''); ?>">
                                            <?php echo e(ucfirst($order->status)); ?>

                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400">
                                        <?php echo e($order->payment_type === 'credit' ? 'Kredit' : 'Tunai'); ?>

                                        <?php if($order->due_date && $order->payment_type === 'credit'): ?>
                                            <br><span class="<?php echo e($order->due_date->isPast() ? 'text-red-400' : ''); ?>">
                                                Jatuh tempo: <?php echo e($order->due_date->format('d/m/Y')); ?>

                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <a href="<?php echo e(route('sales.show', $order)); ?>"
                                                class="text-xs px-2 py-1 bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-slate-300 rounded-lg hover:bg-gray-200 dark:hover:bg-white/20 transition">
                                                Detail
                                            </a>
                                            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'sales', 'edit')): ?>
                                            <?php if(!in_array($order->status, ['completed', 'cancelled'])): ?>
                                                <form method="POST" action="<?php echo e(route('sales.status', $order)); ?>">
                                                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                                    <select name="status" onchange="this.form.submit()"
                                                        class="text-xs bg-gray-100 dark:bg-white/10 border-0 rounded-lg px-2 py-1 text-gray-700 dark:text-slate-300 cursor-pointer">
                                                        <option value="">Ubah status...</option>
                                                        <?php $__currentLoopData = ['pending','confirmed','processing','shipped','completed','cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <?php if($s !== $order->status): ?>
                                                                <option value="<?php echo e($s); ?>"><?php echo e(ucfirst($s)); ?></option>
                                                            <?php endif; ?>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                </form>
                                            <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <?php if($orders->hasPages()): ?>
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-white/5"><?php echo e($orders->links()); ?></div>
                <?php endif; ?>
            <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\sales\index.blade.php ENDPATH**/ ?>