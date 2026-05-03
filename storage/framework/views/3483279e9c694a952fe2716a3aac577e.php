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
     <?php $__env->slot('header', null, []); ?> Data Pasien <?php $__env->endSlot(); ?>

    
    <?php if (isset($component)) { $__componentOriginal360d002b1b676b6f84d43220f22129e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal360d002b1b676b6f84d43220f22129e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumbs','data' => ['items' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Data Pasien'],
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Data Pasien'],
    ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $attributes = $__attributesOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__attributesOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $component = $__componentOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__componentOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Pasien</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">
                <?php echo e(number_format($stats['total_patients'])); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Pasien Aktif</p>
            <p class="text-2xl font-bold text-green-600 mt-1">
                <?php echo e(number_format($stats['active_patients'])); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Janji Hari Ini</p>
            <p class="text-2xl font-bold text-blue-600 mt-1"><?php echo e($stats['today_appointments']); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Pasien Rawat Inap</p>
            <p class="text-2xl font-bold text-purple-600 mt-1"><?php echo e($stats['admitted_patients']); ?>

            </p>
        </div>
    </div>

    
    <?php if (isset($component)) { $__componentOriginal7b66a6cac55792fe5d5bab0e405aec41 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7b66a6cac55792fe5d5bab0e405aec41 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.healthcare.toolbar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('healthcare.toolbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
         <?php $__env->slot('filters', null, []); ?> 
            <form method="GET">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <?php if (isset($component)) { $__componentOriginalf4bdbcbae2287ad38463f4768ffa5e25 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf4bdbcbae2287ad38463f4768ffa5e25 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.healthcare.filter-input','data' => ['name' => 'search','label' => 'Pencarian','value' => ''.e(request('search')).'','placeholder' => 'Cari nama / NIK / RM...']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('healthcare.filter-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','label' => 'Pencarian','value' => ''.e(request('search')).'','placeholder' => 'Cari nama / NIK / RM...']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf4bdbcbae2287ad38463f4768ffa5e25)): ?>
<?php $attributes = $__attributesOriginalf4bdbcbae2287ad38463f4768ffa5e25; ?>
<?php unset($__attributesOriginalf4bdbcbae2287ad38463f4768ffa5e25); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf4bdbcbae2287ad38463f4768ffa5e25)): ?>
<?php $component = $__componentOriginalf4bdbcbae2287ad38463f4768ffa5e25; ?>
<?php unset($__componentOriginalf4bdbcbae2287ad38463f4768ffa5e25); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginale5edde137a3cc081e7a90ee6635c235e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5edde137a3cc081e7a90ee6635c235e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.healthcare.filter-select','data' => ['name' => 'status','label' => 'Status','value' => ''.e(request('status')).'','placeholder' => 'Semua Status','options' => [
                            'active' => 'Aktif',
                            'inactive' => 'Nonaktif',
                            'deceased' => 'Meninggal',
                        ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('healthcare.filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'status','label' => 'Status','value' => ''.e(request('status')).'','placeholder' => 'Semua Status','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                            'active' => 'Aktif',
                            'inactive' => 'Nonaktif',
                            'deceased' => 'Meninggal',
                        ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5edde137a3cc081e7a90ee6635c235e)): ?>
<?php $attributes = $__attributesOriginale5edde137a3cc081e7a90ee6635c235e; ?>
<?php unset($__attributesOriginale5edde137a3cc081e7a90ee6635c235e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5edde137a3cc081e7a90ee6635c235e)): ?>
<?php $component = $__componentOriginale5edde137a3cc081e7a90ee6635c235e; ?>
<?php unset($__componentOriginale5edde137a3cc081e7a90ee6635c235e); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginale5edde137a3cc081e7a90ee6635c235e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5edde137a3cc081e7a90ee6635c235e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.healthcare.filter-select','data' => ['name' => 'gender','label' => 'Gender','value' => ''.e(request('gender')).'','placeholder' => 'Semua Gender','options' => [
                            'male' => 'Laki-laki',
                            'female' => 'Perempuan',
                        ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('healthcare.filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'gender','label' => 'Gender','value' => ''.e(request('gender')).'','placeholder' => 'Semua Gender','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                            'male' => 'Laki-laki',
                            'female' => 'Perempuan',
                        ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5edde137a3cc081e7a90ee6635c235e)): ?>
<?php $attributes = $__attributesOriginale5edde137a3cc081e7a90ee6635c235e; ?>
<?php unset($__attributesOriginale5edde137a3cc081e7a90ee6635c235e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5edde137a3cc081e7a90ee6635c235e)): ?>
<?php $component = $__componentOriginale5edde137a3cc081e7a90ee6635c235e; ?>
<?php unset($__componentOriginale5edde137a3cc081e7a90ee6635c235e); ?>
<?php endif; ?>
                    <div class="flex items-end">
                        <?php if (isset($component)) { $__componentOriginala1798947881d7e44428f9fd6a827b9ad = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala1798947881d7e44428f9fd6a827b9ad = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.healthcare.button','data' => ['type' => 'submit','icon' => 'search','fullWidth' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('healthcare.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','icon' => 'search','full-width' => true]); ?>
                            Cari
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala1798947881d7e44428f9fd6a827b9ad)): ?>
<?php $attributes = $__attributesOriginala1798947881d7e44428f9fd6a827b9ad; ?>
<?php unset($__attributesOriginala1798947881d7e44428f9fd6a827b9ad); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala1798947881d7e44428f9fd6a827b9ad)): ?>
<?php $component = $__componentOriginala1798947881d7e44428f9fd6a827b9ad; ?>
<?php unset($__componentOriginala1798947881d7e44428f9fd6a827b9ad); ?>
<?php endif; ?>
                    </div>
                </div>
            </form>
         <?php $__env->endSlot(); ?>

         <?php $__env->slot('actions', null, []); ?> 
            <div class="flex items-center gap-2">
                <?php if (isset($component)) { $__componentOriginala1798947881d7e44428f9fd6a827b9ad = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala1798947881d7e44428f9fd6a827b9ad = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.healthcare.button','data' => ['type' => 'secondary','href' => ''.e(route('healthcare.dashboard')).'','icon' => 'home']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('healthcare.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'secondary','href' => ''.e(route('healthcare.dashboard')).'','icon' => 'home']); ?>
                    Dashboard
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala1798947881d7e44428f9fd6a827b9ad)): ?>
<?php $attributes = $__attributesOriginala1798947881d7e44428f9fd6a827b9ad; ?>
<?php unset($__attributesOriginala1798947881d7e44428f9fd6a827b9ad); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala1798947881d7e44428f9fd6a827b9ad)): ?>
<?php $component = $__componentOriginala1798947881d7e44428f9fd6a827b9ad; ?>
<?php unset($__componentOriginala1798947881d7e44428f9fd6a827b9ad); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginala1798947881d7e44428f9fd6a827b9ad = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala1798947881d7e44428f9fd6a827b9ad = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.healthcare.button','data' => ['type' => 'primary','icon' => 'plus','onclick' => 'document.getElementById(\'modal-add-patient\').classList.remove(\'hidden\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('healthcare.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'primary','icon' => 'plus','onclick' => 'document.getElementById(\'modal-add-patient\').classList.remove(\'hidden\')']); ?>
                    Tambah Pasien
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala1798947881d7e44428f9fd6a827b9ad)): ?>
<?php $attributes = $__attributesOriginala1798947881d7e44428f9fd6a827b9ad; ?>
<?php unset($__attributesOriginala1798947881d7e44428f9fd6a827b9ad); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala1798947881d7e44428f9fd6a827b9ad)): ?>
<?php $component = $__componentOriginala1798947881d7e44428f9fd6a827b9ad; ?>
<?php unset($__componentOriginala1798947881d7e44428f9fd6a827b9ad); ?>
<?php endif; ?>
            </div>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7b66a6cac55792fe5d5bab0e405aec41)): ?>
<?php $attributes = $__attributesOriginal7b66a6cac55792fe5d5bab0e405aec41; ?>
<?php unset($__attributesOriginal7b66a6cac55792fe5d5bab0e405aec41); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7b66a6cac55792fe5d5bab0e405aec41)): ?>
<?php $component = $__componentOriginal7b66a6cac55792fe5d5bab0e405aec41; ?>
<?php unset($__componentOriginal7b66a6cac55792fe5d5bab0e405aec41); ?>
<?php endif; ?>

    
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Pasien</th>
                        <th class="px-4 py-3 text-left">No. RM</th>
                        <th class="px-4 py-3 text-left">NIK</th>
                        <th class="px-4 py-3 text-center">Gender</th>
                        <th class="px-4 py-3 text-left">Telepon</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $patients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-blue-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900"><?php echo e($patient->full_name); ?>

                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo e($patient->birth_date ? $patient->birth_date->age . ' tahun' : '-'); ?>

                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="font-mono text-xs bg-gray-100 px-2 py-1 rounded-lg"><?php echo e($patient->medical_record_number); ?></span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                <?php echo e($patient->nik ?? '-'); ?></td>
                            <td class="px-4 py-3 text-center">
                                <?php if($patient->gender === 'male'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700">L</span>
                                <?php elseif($patient->gender === 'female'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-pink-100 text-pink-700">P</span>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                <?php echo e($patient->phone_primary ?? '-'); ?></td>
                            <td class="px-4 py-3 text-center">
                                <?php if($patient->status === 'active'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Aktif</span>
                                <?php elseif($patient->status === 'inactive'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700">Nonaktif</span>
                                <?php elseif($patient->status === 'deceased'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700">Meninggal</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="<?php echo e(route('healthcare.patients.show', $patient)); ?>"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"
                                        title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </a>
                                    <button onclick="editPatient(<?php echo e($patient->id); ?>)"
                                        class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7">
                                <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'users','title' => 'Belum ada data pasien','message' => 'Belum ada data pasien. Klik tombol di atas untuk menambah.','actionText' => 'Tambah Pasien','actionUrl' => ''.e(route('healthcare.patients.create')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'users','title' => 'Belum ada data pasien','message' => 'Belum ada data pasien. Klik tombol di atas untuk menambah.','actionText' => 'Tambah Pasien','actionUrl' => ''.e(route('healthcare.patients.create')).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <div class="md:hidden divide-y divide-gray-100">
            <?php $__empty_1 = true; $__currentLoopData = $patients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div
                                class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-blue-600" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-gray-900 truncate">
                                    <?php echo e($patient->full_name); ?></p>
                                <p class="text-xs text-gray-500">
                                    <?php echo e($patient->birth_date ? $patient->birth_date->age . ' tahun' : '-'); ?>

                                </p>
                            </div>
                        </div>
                        <?php if($patient->status === 'active'): ?>
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 shrink-0">Aktif</span>
                        <?php elseif($patient->status === 'inactive'): ?>
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 shrink-0">Nonaktif</span>
                        <?php elseif($patient->status === 'deceased'): ?>
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 shrink-0">Meninggal</span>
                        <?php endif; ?>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                        <div>
                            <p class="text-gray-400">No. RM</p>
                            <p class="font-mono text-gray-700">
                                <?php echo e($patient->medical_record_number); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-400">Gender</p>
                            <p class="text-gray-700">
                                <?php if($patient->gender === 'male'): ?>
                                    <span class="text-blue-600">Laki-laki</span>
                                <?php elseif($patient->gender === 'female'): ?>
                                    <span class="text-pink-600">Perempuan</span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </p>
                        </div>
                        <?php if($patient->nik): ?>
                            <div class="col-span-2">
                                <p class="text-gray-400">NIK</p>
                                <p class="text-gray-700"><?php echo e($patient->nik); ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="col-span-2">
                            <p class="text-gray-400">Telepon</p>
                            <a href="tel:<?php echo e($patient->phone_primary); ?>"
                                class="text-blue-600 hover:underline"><?php echo e($patient->phone_primary ?? '-'); ?></a>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                        <a href="<?php echo e(route('healthcare.patients.show', $patient)); ?>"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                </path>
                            </svg>
                            Detail
                        </a>
                        <button onclick="editPatient(<?php echo e($patient->id); ?>)"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                            Edit
                        </button>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'users','title' => 'Belum ada data pasien','message' => 'Belum ada data pasien. Klik tombol di atas untuk menambah.','actionText' => 'Tambah Pasien','actionUrl' => ''.e(route('healthcare.patients.create')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'users','title' => 'Belum ada data pasien','message' => 'Belum ada data pasien. Klik tombol di atas untuk menambah.','actionText' => 'Tambah Pasien','actionUrl' => ''.e(route('healthcare.patients.create')).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
            <?php endif; ?>
        </div>

        
        <?php if($patients->hasPages()): ?>
            <div class="px-4 py-3 border-t border-gray-200">
                <?php echo e($patients->links()); ?>

            </div>
        <?php endif; ?>
    </div>

    
    <div id="modal-add-patient"
        class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Tambah Pasien Baru</h3>
                <button onclick="document.getElementById('modal-add-patient').classList.add('hidden')"
                    class="p-2 hover:bg-gray-100 rounded-xl">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form action="<?php echo e(route('healthcare.patients.store')); ?>" method="POST" x-data="{ loading: false }"
                @submit="loading = true" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap
                            *</label>
                        <input type="text" name="full_name" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">NIK</label>
                        <input type="text" name="nik"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Lahir
                            *</label>
                        <input type="date" name="birth_date" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gender
                            *</label>
                        <select name="gender" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Gender</option>
                            <option value="male">Laki-laki</option>
                            <option value="female">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
                        <input type="tel" name="phone_primary"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                        <textarea name="address_street" rows="3"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button"
                        onclick="document.getElementById('modal-add-patient').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit" :disabled="loading"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center">
                        <template x-if="loading">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </template>
                        <span x-text="loading ? 'Memproses...' : 'Simpan'"></span>
                    </button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\patients\index.blade.php ENDPATH**/ ?>