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
     <?php $__env->slot('header', null, []); ?> Detail Surat Peringatan <?php $__env->endSlot(); ?>

    <div class="flex flex-col lg:flex-row gap-5">

        
        <div class="lg:w-64 shrink-0 space-y-4">

            
            <div class="bg-white rounded-2xl border border-gray-200 p-4 space-y-2">
                <button onclick="window.print()"
                    class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak / PDF
                </button>

                <?php if($letter->status === 'issued'): ?>
                <form method="POST" action="<?php echo e(route('hrm.disciplinary.acknowledge', $letter)); ?>">
                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                    <div class="mb-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggapan Karyawan</label>
                        <textarea name="employee_response" rows="2" placeholder="Opsional..."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                    <button type="submit" class="w-full py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">
                        ✓ Konfirmasi Penerimaan
                    </button>
                </form>
                <?php endif; ?>

                <?php if(in_array($letter->status, ['issued','acknowledged'])): ?>
                <form method="POST" action="<?php echo e(route('hrm.disciplinary.expire', $letter)); ?>">
                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                    <button type="submit" onclick="return confirm('Tandai SP ini sebagai expired?')"
                        class="w-full py-2 text-sm border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50">
                        Tandai Expired
                    </button>
                </form>
                <?php endif; ?>

                <a href="<?php echo e(route('hrm.disciplinary.index')); ?>"
                    class="block text-center py-2 text-sm border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50">
                    ← Kembali
                </a>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Riwayat SP — <?php echo e($letter->employee->name); ?></p>
                <div class="space-y-2">
                    <?php $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('hrm.disciplinary.show', $h)); ?>"
                        class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 <?php echo e($h->id === $letter->id ? 'bg-blue-50' : ''); ?>">
                        <span class="px-1.5 py-0.5 rounded text-xs font-bold <?php echo e($h->levelColor()); ?>"><?php echo e($h->levelLabel()); ?></span>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-700 truncate"><?php echo e($h->violation_type); ?></p>
                            <p class="text-xs text-gray-400"><?php echo e($h->issued_date->format('d M Y')); ?></p>
                        </div>
                    </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        
        <div class="flex-1 min-w-0">
            <div id="print-area" class="bg-white rounded-2xl border border-gray-200 p-8 print:shadow-none print:border-none print:rounded-none print:p-6">

                
                <div class="text-center border-b-2 border-gray-800 pb-4 mb-6 print:border-gray-800">
                    <p class="text-lg font-bold text-gray-900 uppercase tracking-wide">SURAT PERINGATAN</p>
                    <p class="text-2xl font-black text-gray-900 mt-1"><?php echo e($letter->levelLabel()); ?></p>
                    <p class="text-sm text-gray-500 mt-1">No: <?php echo e($letter->letter_number); ?></p>
                </div>

                
                <div class="mb-6 text-sm text-gray-700 space-y-1">
                    <p>Yang bertanda tangan di bawah ini:</p>
                    <div class="ml-4 space-y-1">
                        <div class="flex gap-2"><span class="w-32 shrink-0">Nama</span><span>: <?php echo e($letter->issuer->name ?? '-'); ?></span></div>
                        <div class="flex gap-2"><span class="w-32 shrink-0">Jabatan</span><span>: <?php echo e($letter->issuer?->role ? ucfirst($letter->issuer->role) : 'HRD'); ?></span></div>
                    </div>
                    <p class="mt-2">Dengan ini memberikan Surat Peringatan kepada:</p>
                    <div class="ml-4 space-y-1">
                        <div class="flex gap-2"><span class="w-32 shrink-0">Nama</span><span>: <?php echo e($letter->employee->name ?? '-'); ?></span></div>
                        <div class="flex gap-2"><span class="w-32 shrink-0">Jabatan</span><span>: <?php echo e($letter->employee->position ?? '-'); ?></span></div>
                        <div class="flex gap-2"><span class="w-32 shrink-0">Departemen</span><span>: <?php echo e($letter->employee->department ?? '-'); ?></span></div>
                    </div>
                </div>

                
                <div class="space-y-4 text-sm text-gray-700">
                    <div>
                        <p class="font-semibold text-gray-900 mb-1">Jenis Pelanggaran:</p>
                        <p class="ml-4"><?php echo e($letter->violation_type); ?></p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 mb-1">Uraian Pelanggaran:</p>
                        <p class="ml-4 whitespace-pre-line"><?php echo e($letter->violation_description); ?></p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 mb-1">Tindakan Perbaikan yang Diminta:</p>
                        <p class="ml-4 whitespace-pre-line"><?php echo e($letter->corrective_action); ?></p>
                    </div>
                    <?php if($letter->consequences): ?>
                    <div>
                        <p class="font-semibold text-gray-900 mb-1">Konsekuensi:</p>
                        <p class="ml-4 whitespace-pre-line"><?php echo e($letter->consequences); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if($letter->valid_until): ?>
                    <p>Surat peringatan ini berlaku hingga <strong><?php echo e($letter->valid_until->format('d F Y')); ?></strong>.</p>
                    <?php endif; ?>
                </div>

                
                <?php if($letter->employee_response): ?>
                <div class="mt-6 p-4 rounded-xl bg-gray-50 border border-gray-200">
                    <p class="text-xs font-semibold text-gray-500 mb-1">Tanggapan Karyawan:</p>
                    <p class="text-sm text-gray-700 whitespace-pre-line"><?php echo e($letter->employee_response); ?></p>
                </div>
                <?php endif; ?>

                
                <div class="mt-10 grid grid-cols-3 gap-6 text-sm text-center text-gray-700">
                    <div>
                        <p><?php echo e($letter->issued_date->format('d F Y')); ?></p>
                        <div class="h-16 border-b border-gray-400 mt-2 mb-1"></div>
                        <p class="font-semibold"><?php echo e($letter->issuer->name ?? 'HRD'); ?></p>
                        <p class="text-xs text-gray-500">Yang Menerbitkan</p>
                    </div>
                    <?php if($letter->witness): ?>
                    <div>
                        <p>&nbsp;</p>
                        <div class="h-16 border-b border-gray-400 mt-2 mb-1"></div>
                        <p class="font-semibold"><?php echo e($letter->witness->name); ?></p>
                        <p class="text-xs text-gray-500">Saksi</p>
                    </div>
                    <?php else: ?>
                    <div></div>
                    <?php endif; ?>
                    <div>
                        <?php if($letter->acknowledged_at): ?>
                        <p><?php echo e($letter->acknowledged_at->format('d F Y')); ?></p>
                        <?php else: ?>
                        <p>&nbsp;</p>
                        <?php endif; ?>
                        <div class="h-16 border-b border-gray-400 mt-2 mb-1"></div>
                        <p class="font-semibold"><?php echo e($letter->employee->name ?? '-'); ?></p>
                        <p class="text-xs text-gray-500">Yang Menerima</p>
                    </div>
                </div>

                
                <?php if($letter->source === 'ai_anomaly'): ?>
                <div class="mt-4 flex items-center gap-1.5 text-xs text-purple-400 print:hidden">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Draft dibuat oleh AI berdasarkan anomali absensi
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php $__env->startPush('head'); ?>
    <style>
    @media print {
        body > * { display: none !important; }
        #print-area { display: block !important; position: fixed; top: 0; left: 0; width: 100%; }
        .dark #print-area { background: white !important; color: black !important; }
        .dark #print-area * { color: black !important; border-color: #333 !important; }
    }
    </style>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hrm\disciplinary-show.blade.php ENDPATH**/ ?>