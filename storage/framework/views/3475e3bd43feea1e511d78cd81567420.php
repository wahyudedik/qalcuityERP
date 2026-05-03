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
     <?php $__env->slot('header', null, []); ?> Pengaturan Akuntansi <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600"><?php echo e(session('error')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600"><?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    
    <div class="flex gap-1 mb-5 bg-gray-100 rounded-xl p-1 w-fit flex-wrap">
        <?php $__currentLoopData = ['coa'=>'Chart of Accounts','bank'=>'Rekening Bank','tax'=>'Tarif Pajak','currency'=>'Mata Uang']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(request()->fullUrlWithQuery(['tab'=>$t])); ?>"
            class="px-4 py-2 text-sm rounded-lg font-medium transition
                <?php echo e($tab===$t ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'); ?>">
            <?php echo e($label); ?>

        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <?php if($tab === 'coa'): ?>
    <div class="flex flex-col lg:flex-row gap-5">

        
        <div class="lg:w-72 shrink-0">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-900 mb-4 text-sm">Tambah Akun</h3>
                <form method="POST" action="<?php echo e(route('accounting.coa.store')); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kode *</label>
                        <input type="text" name="code" required placeholder="1-1001"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Akun *</label>
                        <input type="text" name="name" required placeholder="Kas"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipe *</label>
                        <select name="type" id="coa-type" onchange="setNormalBalance(this.value)" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih --</option>
                            <option value="asset">Aset</option>
                            <option value="liability">Kewajiban</option>
                            <option value="equity">Ekuitas</option>
                            <option value="revenue">Pendapatan</option>
                            <option value="expense">Beban</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Saldo Normal *</label>
                        <select name="normal_balance" id="coa-normal" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="debit">Debit</option>
                            <option value="credit">Kredit</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Akun Induk</label>
                        <select name="parent_id"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Tidak ada --</option>
                            <?php $__currentLoopData = $headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($h->id); ?>"><?php echo e($h->code); ?> - <?php echo e($h->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_header" id="is_header" value="1" class="rounded">
                        <label for="is_header" class="text-xs text-gray-600">Akun Header</label>
                    </div>
                    <button type="submit" class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Tambah Akun</button>
                </form>
                <form method="POST" action="<?php echo e(route('accounting.coa.seed')); ?>" class="mt-3">
                    <?php echo csrf_field(); ?>
                    <button type="submit" onclick="return confirm('Load COA default Indonesia?')"
                        class="w-full py-2 text-sm border border-blue-500/30 text-blue-600 rounded-xl hover:bg-blue-50">
                        COA Default Indonesia
                    </button>
                </form>
            </div>
        </div>

        
        <div class="flex-1">
            <div class="flex gap-2 mb-3">
                <form method="GET" class="flex gap-2 flex-1">
                    <input type="hidden" name="tab" value="coa">
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari kode / nama..."
                        class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <select name="type" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
                        <option value="">Semua Tipe</option>
                        <?php $__currentLoopData = ['asset'=>'Aset','liability'=>'Kewajiban','equity'=>'Ekuitas','revenue'=>'Pendapatan','expense'=>'Beban']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($v); ?>" <?php if(request('type')===$v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
                </form>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Kode</th>
                                <th class="px-4 py-3 text-left">Nama</th>
                                <th class="px-4 py-3 text-left hidden sm:table-cell">Tipe</th>
                                <th class="px-4 py-3 text-center hidden md:table-cell">Saldo Normal</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__empty_1 = true; $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 <?php echo e($acc->is_header ? 'font-semibold' : ''); ?>">
                                <td class="px-4 py-3 font-mono text-xs text-gray-900"><?php echo e($acc->code); ?></td>
                                <td class="px-4 py-3 text-gray-900">
                                    <?php echo e($acc->is_header ? '' : '↳ '); ?><?php echo e($acc->name); ?>

                                    <?php if($acc->parent): ?> <span class="text-xs text-gray-400">(<?php echo e($acc->parent->code); ?>)</span> <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 hidden sm:table-cell">
                                    <?php $typeColors = ['asset'=>'blue','liability'=>'red','equity'=>'purple','revenue'=>'green','expense'=>'orange']; $tc = $typeColors[$acc->type] ?? 'gray'; ?>
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($tc); ?>-100 text-<?php echo e($tc); ?>-700 $tc }}-500/20 $tc }}-400">
                                        <?php echo e(['asset'=>'Aset','liability'=>'Kewajiban','equity'=>'Ekuitas','revenue'=>'Pendapatan','expense'=>'Beban'][$acc->type] ?? $acc->type); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center hidden md:table-cell text-xs text-gray-500"><?php echo e(ucfirst($acc->normal_balance)); ?></td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-xs <?php echo e($acc->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'); ?>">
                                        <?php echo e($acc->is_active ? 'Aktif' : 'Nonaktif'); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button onclick="openEditCoa(<?php echo e($acc->id); ?>,'<?php echo e(addslashes($acc->code)); ?>','<?php echo e(addslashes($acc->name)); ?>','<?php echo e($acc->type); ?>','<?php echo e($acc->normal_balance); ?>',<?php echo e($acc->parent_id ?? 'null'); ?>,<?php echo e($acc->is_header ? 'true' : 'false'); ?>,<?php echo e($acc->is_active ? 'true' : 'false'); ?>)"
                                            class="text-xs px-2 py-1 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">Edit</button>
                                        <form method="POST" action="<?php echo e(route('accounting.coa.destroy', $acc)); ?>" class="inline">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit" onclick="return confirm('Hapus akun <?php echo e(addslashes($acc->name)); ?>?')"
                                                class="text-xs px-2 py-1 text-red-500 hover:text-red-700">✕</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">Belum ada akun. Klik "COA Default Indonesia" untuk memuat data awal.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if($tab === 'bank'): ?>
    <div class="flex flex-col lg:flex-row gap-5">
        <div class="lg:w-72 shrink-0">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-900 mb-4 text-sm">Tambah Rekening</h3>
                <form method="POST" action="<?php echo e(route('bank-accounts.store')); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Bank *</label>
                        <input type="text" name="bank_name" required placeholder="BCA, Mandiri..."
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">No. Rekening *</label>
                        <input type="text" name="account_number" required placeholder="1234567890"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Pemilik *</label>
                        <input type="text" name="account_name" required placeholder="PT. Nama Perusahaan"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Saldo Awal</label>
                        <input type="number" name="balance" value="0" min="0" step="0.01"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Tambah Rekening</button>
                </form>
            </div>
        </div>
        <div class="flex-1">
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Bank</th>
                            <th class="px-4 py-3 text-left">No. Rekening</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Pemilik</th>
                            <th class="px-4 py-3 text-right hidden md:table-cell">Saldo</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__empty_1 = true; $__currentLoopData = $bankAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ba): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900"><?php echo e($ba->bank_name); ?></td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-700"><?php echo e($ba->account_number); ?></td>
                            <td class="px-4 py-3 hidden sm:table-cell text-gray-600"><?php echo e($ba->account_name); ?></td>
                            <td class="px-4 py-3 text-right hidden md:table-cell text-gray-900">Rp <?php echo e(number_format($ba->balance,0,',','.')); ?></td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs <?php echo e($ba->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'); ?>">
                                    <?php echo e($ba->is_active ? 'Aktif' : 'Nonaktif'); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button onclick="openEditBank(<?php echo e($ba->id); ?>,'<?php echo e(addslashes($ba->bank_name)); ?>','<?php echo e(addslashes($ba->account_name)); ?>',<?php echo e($ba->balance); ?>)"
                                        class="text-xs px-2 py-1 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">Edit</button>
                                    <form method="POST" action="<?php echo e(route('bank-accounts.toggle', $ba)); ?>" class="inline">
                                        <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                        <button type="submit" class="text-xs px-2 py-1 <?php echo e($ba->is_active ? 'text-amber-500' : 'text-green-500'); ?> hover:underline">
                                            <?php echo e($ba->is_active ? 'Nonaktif' : 'Aktif'); ?>

                                        </button>
                                    </form>
                                    <form method="POST" action="<?php echo e(route('bank-accounts.destroy', $ba)); ?>" class="inline">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit" onclick="return confirm('Hapus rekening ini?')" class="text-xs px-2 py-1 text-red-500 hover:text-red-700">✕</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">Belum ada rekening bank.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if($tab === 'tax'): ?>
    <div class="flex flex-col lg:flex-row gap-5">
        <div class="lg:w-72 shrink-0">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-900 mb-4 text-sm">Tambah Tarif Pajak</h3>
                <form method="POST" action="<?php echo e(route('taxes.store')); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama *</label>
                        <input type="text" name="name" required placeholder="PPN 11%"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kode *</label>
                        <input type="text" name="code" required placeholder="PPN11"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jenis Pajak *</label>
                        <select name="tax_type" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="ppn">PPN</option>
                            <option value="pph21">PPh 21</option>
                            <option value="pph23">PPh 23</option>
                            <option value="pph4ayat2">PPh 4 Ayat 2</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipe Kalkulasi *</label>
                        <select name="type" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="percentage">Persentase (%)</option>
                            <option value="fixed">Nominal Tetap</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tarif *</label>
                        <input type="number" name="rate" required min="0" max="100" step="0.01" placeholder="11"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kode Akun COA</label>
                        <input type="text" name="account_code" placeholder="2-1003"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_withholding" id="is_withholding" value="1" class="rounded">
                        <label for="is_withholding" class="text-xs text-gray-600">Pajak Pemotongan (WHT)</label>
                    </div>
                    <button type="submit" class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Tambah Pajak</button>
                </form>
            </div>
        </div>
        <div class="flex-1">
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-left">Kode</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Jenis</th>
                            <th class="px-4 py-3 text-right">Tarif</th>
                            <th class="px-4 py-3 text-center hidden md:table-cell">WHT</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__empty_1 = true; $__currentLoopData = $taxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900"><?php echo e($tax->name); ?></td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-600"><?php echo e($tax->code); ?></td>
                            <td class="px-4 py-3 hidden sm:table-cell text-gray-600"><?php echo e($tax->getTypeLabel()); ?></td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">
                                <?php echo e($tax->type === 'percentage' ? $tax->rate.'%' : 'Rp '.number_format($tax->rate,0,',','.')); ?>

                            </td>
                            <td class="px-4 py-3 text-center hidden md:table-cell">
                                <?php if($tax->is_withholding): ?><span class="text-xs text-amber-500">WHT</span><?php else: ?><span class="text-xs text-gray-400">-</span><?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button onclick="openEditTax(<?php echo e($tax->id); ?>,'<?php echo e(addslashes($tax->name)); ?>','<?php echo e($tax->code); ?>','<?php echo e($tax->tax_type); ?>','<?php echo e($tax->type); ?>',<?php echo e($tax->rate); ?>,'<?php echo e($tax->account_code); ?>',<?php echo e($tax->is_withholding ? 'true' : 'false'); ?>)"
                                        class="text-xs px-2 py-1 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">Edit</button>
                                    <form method="POST" action="<?php echo e(route('taxes.destroy', $tax)); ?>" class="inline">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit" onclick="return confirm('Hapus tarif pajak ini?')" class="text-xs px-2 py-1 text-red-500 hover:text-red-700">✕</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">Belum ada tarif pajak.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3 flex justify-end">
                <a href="<?php echo e(route('taxes.efaktur')); ?>" class="text-xs px-3 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                    Export e-Faktur CSV
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if($tab === 'currency'): ?>
    <div class="flex flex-col lg:flex-row gap-5">
        <div class="lg:w-72 shrink-0">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-900 mb-4 text-sm">Tambah Mata Uang</h3>
                <form method="POST" action="<?php echo e(route('settings.accounting.currencies.store')); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kode *</label>
                        <input type="text" name="code" required placeholder="USD" maxlength="10"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 uppercase">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama *</label>
                        <input type="text" name="name" required placeholder="US Dollar"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Simbol *</label>
                        <input type="text" name="symbol" required placeholder="$" maxlength="10"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kurs ke IDR *</label>
                        <input type="number" name="rate_to_idr" required min="0" step="0.0001" placeholder="16000"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="cur_active" value="1" checked class="rounded">
                        <label for="cur_active" class="text-xs text-gray-600">Aktif</label>
                    </div>
                    <button type="submit" class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Tambah Mata Uang</button>
                </form>
            </div>
        </div>
        <div class="flex-1">
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Kode</th>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-center">Simbol</th>
                            <th class="px-4 py-3 text-right">Kurs ke IDR</th>
                            <th class="px-4 py-3 text-center hidden sm:table-cell">Update</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__empty_1 = true; $__currentLoopData = $currencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cur): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono font-bold text-gray-900"><?php echo e($cur->code); ?></td>
                            <td class="px-4 py-3 text-gray-700"><?php echo e($cur->name); ?></td>
                            <td class="px-4 py-3 text-center text-gray-600"><?php echo e($cur->symbol); ?></td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">
                                <?php if($cur->is_base): ?>
                                <span class="text-xs text-green-500">Base (IDR)</span>
                                <?php else: ?>
                                Rp <?php echo e(number_format($cur->rate_to_idr, 2, ',', '.')); ?>

                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell text-xs text-gray-400">
                                <?php echo e($cur->rate_updated_at?->diffForHumans() ?? '-'); ?>

                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs <?php echo e($cur->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'); ?>">
                                    <?php echo e($cur->is_base ? 'Base' : ($cur->is_active ? 'Aktif' : 'Nonaktif')); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if(!$cur->is_base): ?>
                                <div class="flex items-center justify-center gap-1">
                                    <button onclick="openEditCurrency(<?php echo e($cur->id); ?>,'<?php echo e($cur->name); ?>','<?php echo e($cur->symbol); ?>',<?php echo e($cur->rate_to_idr); ?>,<?php echo e($cur->is_active ? 'true' : 'false'); ?>)"
                                        class="text-xs px-2 py-1 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">Edit</button>
                                    <form method="POST" action="<?php echo e(route('settings.accounting.currencies.destroy', $cur)); ?>" class="inline">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit" onclick="return confirm('Hapus mata uang <?php echo e($cur->code); ?>?')" class="text-xs px-2 py-1 text-red-500 hover:text-red-700">✕</button>
                                    </form>
                                </div>
                                <?php else: ?>
                                <span class="text-xs text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">Belum ada mata uang.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    

    
    <div id="modal-edit-coa" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Edit Akun</h3>
                <button onclick="document.getElementById('modal-edit-coa').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-edit-coa" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kode *</label>
                        <input type="text" name="code" id="edit-coa-code" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipe *</label>
                        <select name="type" id="edit-coa-type" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="asset">Aset</option>
                            <option value="liability">Kewajiban</option>
                            <option value="equity">Ekuitas</option>
                            <option value="revenue">Pendapatan</option>
                            <option value="expense">Beban</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Akun *</label>
                    <input type="text" name="name" id="edit-coa-name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Saldo Normal *</label>
                        <select name="normal_balance" id="edit-coa-normal" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="debit">Debit</option>
                            <option value="credit">Kredit</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Akun Induk</label>
                        <select name="parent_id" id="edit-coa-parent" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Tidak ada --</option>
                            <?php $__currentLoopData = $headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($h->id); ?>"><?php echo e($h->code); ?> - <?php echo e($h->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 text-xs text-gray-600">
                        <input type="checkbox" name="is_header" id="edit-coa-header" value="1" class="rounded"> Header
                    </label>
                    <label class="flex items-center gap-2 text-xs text-gray-600">
                        <input type="checkbox" name="is_active" id="edit-coa-active" value="1" class="rounded"> Aktif
                    </label>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-edit-coa').classList.add('hidden')" class="flex-1 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="flex-1 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-edit-bank" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Edit Rekening</h3>
                <button onclick="document.getElementById('modal-edit-bank').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-edit-bank" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Bank *</label>
                    <input type="text" name="bank_name" id="edit-bank-name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Pemilik *</label>
                    <input type="text" name="account_name" id="edit-bank-owner" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Saldo</label>
                    <input type="number" name="balance" id="edit-bank-balance" min="0" step="0.01" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-edit-bank').classList.add('hidden')" class="flex-1 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="flex-1 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-edit-tax" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Edit Tarif Pajak</h3>
                <button onclick="document.getElementById('modal-edit-tax').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-edit-tax" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama *</label>
                        <input type="text" name="name" id="edit-tax-name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kode *</label>
                        <input type="text" name="code" id="edit-tax-code" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jenis Pajak *</label>
                        <select name="tax_type" id="edit-tax-type" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="ppn">PPN</option><option value="pph21">PPh 21</option>
                            <option value="pph23">PPh 23</option><option value="pph4ayat2">PPh 4 Ayat 2</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipe *</label>
                        <select name="type" id="edit-tax-calc" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="percentage">Persentase</option><option value="fixed">Nominal Tetap</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tarif *</label>
                        <input type="number" name="rate" id="edit-tax-rate" required min="0" step="0.01" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kode Akun</label>
                        <input type="text" name="account_code" id="edit-tax-account" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 text-xs text-gray-600">
                        <input type="checkbox" name="is_withholding" id="edit-tax-wht" value="1" class="rounded"> WHT
                    </label>
                    <label class="flex items-center gap-2 text-xs text-gray-600">
                        <input type="checkbox" name="is_active" id="edit-tax-active" value="1" class="rounded"> Aktif
                    </label>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-edit-tax').classList.add('hidden')" class="flex-1 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="flex-1 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-edit-currency" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Edit Mata Uang</h3>
                <button onclick="document.getElementById('modal-edit-currency').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-edit-currency" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama *</label>
                    <input type="text" name="name" id="edit-cur-name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Simbol *</label>
                        <input type="text" name="symbol" id="edit-cur-symbol" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kurs ke IDR *</label>
                        <input type="number" name="rate_to_idr" id="edit-cur-rate" required min="0" step="0.0001" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <label class="flex items-center gap-2 text-xs text-gray-600">
                    <input type="checkbox" name="is_active" id="edit-cur-active" value="1" class="rounded"> Aktif
                </label>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-edit-currency').classList.add('hidden')" class="flex-1 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="flex-1 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

<script>
const BASE_URL = '<?php echo e(url("")); ?>';

function setNormalBalance(type) {
    const nb = document.getElementById('coa-normal');
    if (!nb) return;
    nb.value = ['asset','expense'].includes(type) ? 'debit' : 'credit';
}

function openEditCoa(id, code, name, type, normal, parentId, isHeader, isActive) {
    document.getElementById('form-edit-coa').action = BASE_URL + '/accounting/coa/' + id;
    document.getElementById('edit-coa-code').value = code;
    document.getElementById('edit-coa-name').value = name;
    document.getElementById('edit-coa-type').value = type;
    document.getElementById('edit-coa-normal').value = normal;
    document.getElementById('edit-coa-parent').value = parentId || '';
    document.getElementById('edit-coa-header').checked = isHeader;
    document.getElementById('edit-coa-active').checked = isActive;
    document.getElementById('modal-edit-coa').classList.remove('hidden');
}

function openEditBank(id, bankName, ownerName, balance) {
    document.getElementById('form-edit-bank').action = BASE_URL + '/bank-accounts/' + id;
    document.getElementById('edit-bank-name').value = bankName;
    document.getElementById('edit-bank-owner').value = ownerName;
    document.getElementById('edit-bank-balance').value = balance;
    document.getElementById('modal-edit-bank').classList.remove('hidden');
}

function openEditTax(id, name, code, taxType, calcType, rate, accountCode, isWht) {
    document.getElementById('form-edit-tax').action = BASE_URL + '/settings/taxes/' + id;
    document.getElementById('edit-tax-name').value = name;
    document.getElementById('edit-tax-code').value = code;
    document.getElementById('edit-tax-type').value = taxType;
    document.getElementById('edit-tax-calc').value = calcType;
    document.getElementById('edit-tax-rate').value = rate;
    document.getElementById('edit-tax-account').value = accountCode || '';
    document.getElementById('edit-tax-wht').checked = isWht;
    document.getElementById('edit-tax-active').checked = true;
    document.getElementById('modal-edit-tax').classList.remove('hidden');
}

function openEditCurrency(id, name, symbol, rate, isActive) {
    document.getElementById('form-edit-currency').action = BASE_URL + '/settings/accounting/currencies/' + id;
    document.getElementById('edit-cur-name').value = name;
    document.getElementById('edit-cur-symbol').value = symbol;
    document.getElementById('edit-cur-rate').value = rate;
    document.getElementById('edit-cur-active').checked = isActive;
    document.getElementById('modal-edit-currency').classList.remove('hidden');
}
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\settings\accounting.blade.php ENDPATH**/ ?>