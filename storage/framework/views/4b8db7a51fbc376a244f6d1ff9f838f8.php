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
     <?php $__env->slot('header', null, []); ?> Import & Export Data <?php $__env->endSlot(); ?>

    <div class="max-w-4xl space-y-6">

        
        <?php if(session('import_result')): ?>
        <?php $r = session('import_result'); ?>
        <div class="rounded-2xl border p-5 <?php echo e(count($r['errors'] ?? []) > 0 ? 'bg-amber-50 border-amber-200' : 'bg-green-50 border-green-200'); ?>">
            <p class="font-semibold text-sm <?php echo e(count($r['errors'] ?? []) > 0 ? 'text-amber-700' : 'text-green-700'); ?>">
                Import selesai: <?php echo e($r['created']); ?> dibuat<?php echo e(($r['updated'] ?? 0) > 0 ? ", {$r['updated']} diperbarui" : ''); ?>, <?php echo e($r['skipped']); ?> dilewati.
            </p>
            <?php if(count($r['errors'] ?? []) > 0): ?>
            <ul class="mt-2 space-y-1 max-h-32 overflow-y-auto">
                <?php $__currentLoopData = $r['errors']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="text-xs text-amber-600">• <?php echo e($err); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
        <div class="rounded-2xl border p-4 bg-red-50 border-red-200">
            <p class="text-sm text-red-700"><?php echo e(session('error')); ?></p>
        </div>
        <?php endif; ?>

        
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 flex gap-4">
            <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="text-sm text-blue-700">
                <p class="font-semibold mb-1">Cara import:</p>
                <ol class="list-decimal list-inside space-y-0.5 text-blue-600">
                    <li>Download template CSV/Excel sesuai jenis data</li>
                    <li>Isi data di spreadsheet, simpan sebagai CSV atau XLSX</li>
                    <li>Pilih mode: <span class="font-medium">Lewati</span> (skip duplikat) atau <span class="font-medium">Perbarui</span> (update data yang sudah ada)</li>
                    <li>Upload file di form di bawah</li>
                </ol>
            </div>
        </div>

        
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">Export Master Data (CSV)</h2>
            <div class="flex flex-wrap gap-2">
                <?php $__currentLoopData = [
                    ['route' => 'import.export.products',   'label' => 'Produk',    'icon' => '📦'],
                    ['route' => 'import.export.customers',  'label' => 'Customer',  'icon' => '👤'],
                    ['route' => 'import.export.suppliers',  'label' => 'Supplier',  'icon' => '🏭'],
                    ['route' => 'import.export.employees',  'label' => 'Karyawan',  'icon' => '👥'],
                    ['route' => 'import.export.warehouses', 'label' => 'Gudang',    'icon' => '🏢'],
                    ['route' => 'import.export.coa',        'label' => 'Akun (CoA)','icon' => '📊'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route($exp['route'])); ?>"
                   class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                    <span><?php echo e($exp['icon']); ?></span> <?php echo e($exp['label']); ?>

                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <?php
        $importTypes = [
            [
                'key'    => 'products',
                'label'  => 'Produk',
                'icon'   => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                'color'  => 'blue',
                'route'  => 'import.products',
                'cols'   => 'name*, sku, barcode, category, unit, price_sell, price_buy, stock_min, initial_stock',
                'accept' => '.csv,.txt,.xlsx,.xls',
            ],
            [
                'key'    => 'customers',
                'label'  => 'Customer',
                'icon'   => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                'color'  => 'green',
                'route'  => 'import.customers',
                'cols'   => 'name*, email, phone, company, address, npwp, credit_limit',
                'accept' => '.csv,.txt,.xlsx,.xls',
            ],
            [
                'key'    => 'suppliers',
                'label'  => 'Supplier',
                'icon'   => 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z',
                'color'  => 'amber',
                'route'  => 'import.suppliers',
                'cols'   => 'name*, email, phone, company, address, npwp, bank_name, bank_account, bank_holder',
                'accept' => '.csv,.txt,.xlsx,.xls',
            ],
            [
                'key'    => 'employees',
                'label'  => 'Karyawan',
                'icon'   => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                'color'  => 'purple',
                'route'  => 'import.employees',
                'cols'   => 'name*, employee_id, email, phone, position, department, join_date, salary',
                'accept' => '.csv,.txt,.xlsx,.xls',
            ],
            [
                'key'    => 'warehouses',
                'label'  => 'Gudang',
                'icon'   => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                'color'  => 'teal',
                'route'  => 'import.warehouses',
                'cols'   => 'name*, code, address',
                'accept' => '.csv,.txt,.xlsx,.xls',
            ],
            [
                'key'    => 'coa',
                'label'  => 'Chart of Accounts',
                'icon'   => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
                'color'  => 'rose',
                'route'  => 'import.coa',
                'cols'   => 'code*, name*, type* (asset/liability/equity/revenue/expense/cogs), is_header, description',
                'accept' => '.csv,.txt,.xlsx,.xls',
            ],
        ];

        $colorMap = [
            'blue'   => ['bg' => 'bg-blue-50',     'icon' => 'text-blue-500',   'btn' => 'bg-blue-600 hover:bg-blue-700'],
            'green'  => ['bg' => 'bg-green-50',    'icon' => 'text-green-500',  'btn' => 'bg-green-600 hover:bg-green-700'],
            'amber'  => ['bg' => 'bg-amber-50',    'icon' => 'text-amber-500',  'btn' => 'bg-amber-600 hover:bg-amber-700'],
            'purple' => ['bg' => 'bg-purple-50',  'icon' => 'text-purple-500', 'btn' => 'bg-purple-600 hover:bg-purple-700'],
            'teal'   => ['bg' => 'bg-teal-50',      'icon' => 'text-teal-500',   'btn' => 'bg-teal-600 hover:bg-teal-700'],
            'rose'   => ['bg' => 'bg-rose-50',      'icon' => 'text-rose-500',   'btn' => 'bg-rose-600 hover:bg-rose-700'],
        ];
        ?>

        <?php $__currentLoopData = $importTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $c = $colorMap[$type['color']]; ?>
        <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
            <div class="flex items-center gap-4 px-6 py-4 border-b border-gray-100">
                <div class="w-10 h-10 rounded-xl <?php echo e($c['bg']); ?> flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 <?php echo e($c['icon']); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="<?php echo e($type['icon']); ?>"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900">Import <?php echo e($type['label']); ?></p>
                    <p class="text-xs text-gray-400 mt-0.5 truncate">Kolom: <span class="font-mono"><?php echo e($type['cols']); ?></span> <span class="text-gray-300">(*wajib)</span></p>
                </div>
                <a href="<?php echo e(route('import.template', $type['key'])); ?>"
                   class="shrink-0 flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 px-3 py-1.5 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Template
                </a>
            </div>
            <form method="POST" action="<?php echo e(route($type['route'])); ?>" enctype="multipart/form-data" class="px-6 py-4">
                <?php echo csrf_field(); ?>
                <div class="flex flex-col sm:flex-row gap-3 items-end">
                    <div class="flex-1 w-full">
                        <label class="block text-xs text-gray-500 mb-1.5">File CSV atau Excel</label>
                        <input type="file" name="file" accept="<?php echo e($type['accept']); ?>" required
                            class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 file:cursor-pointer cursor-pointer">
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-gray-500">
                            <select name="mode" class="text-xs rounded-lg border-gray-200 bg-gray-50 text-gray-700 py-2 px-2 focus:ring-blue-500">
                                <option value="skip">Lewati duplikat</option>
                                <option value="update">Perbarui duplikat</option>
                            </select>
                        </label>
                        <button type="submit" class="px-5 py-2 rounded-xl <?php echo e($c['btn']); ?> text-white text-sm font-medium transition whitespace-nowrap">
                            Import
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\import\index.blade.php ENDPATH**/ ?>