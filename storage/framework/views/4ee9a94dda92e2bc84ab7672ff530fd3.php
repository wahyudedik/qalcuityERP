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
     <?php $__env->slot('header', null, []); ?> 📦 Export Documentation <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
        <div
            class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
            <?php echo e(session('success')); ?></div>
    <?php endif; ?>

    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Izin Ekspor Aktif</p>
            <p class="text-2xl font-bold text-blue-600"><?php echo e($stats['active_permits'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Sertifikat Kesehatan</p>
            <p class="text-2xl font-bold text-emerald-600"><?php echo e($stats['health_certificates'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Deklarasi Bea Cukai</p>
            <p class="text-2xl font-bold text-purple-600"><?php echo e($stats['customs_declarations'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Pengiriman Bulan Ini</p>
            <p class="text-2xl font-bold text-orange-600"><?php echo e($stats['shipments_this_month'] ?? 0); ?></p>
        </div>
    </div>

    
    <div class="bg-white rounded-xl border border-gray-200"
        x-data="{ tab: '<?php echo e(request('tab', 'permits')); ?>' }">
        <div class="flex border-b border-gray-200 overflow-x-auto">
            <button @click="tab = 'permits'; window.location.href = '?tab=permits'"
                :class="tab === 'permits' ? 'border-b-2 border-orange-600 text-orange-600' :
                    'text-gray-500'"
                class="px-4 py-3 text-sm font-medium whitespace-nowrap transition">
                📄 Izin Ekspor
            </button>
            <button @click="tab = 'certificates'; window.location.href = '?tab=certificates'"
                :class="tab === 'certificates' ? 'border-b-2 border-orange-600 text-orange-600' :
                    'text-gray-500'"
                class="px-4 py-3 text-sm font-medium whitespace-nowrap transition">
                🏥 Sertifikat Kesehatan
            </button>
            <button @click="tab = 'customs'; window.location.href = '?tab=customs'"
                :class="tab === 'customs' ? 'border-b-2 border-orange-600 text-orange-600' :
                    'text-gray-500'"
                class="px-4 py-3 text-sm font-medium whitespace-nowrap transition">
                🛃 Bea Cukai
            </button>
            <button @click="tab = 'shipments'; window.location.href = '?tab=shipments'"
                :class="tab === 'shipments' ? 'border-b-2 border-orange-600 text-orange-600' :
                    'text-gray-500'"
                class="px-4 py-3 text-sm font-medium whitespace-nowrap transition">
                🚢 Pengiriman
            </button>
        </div>

        
        <div x-show="tab === 'permits'" class="p-4">
            <div class="flex items-center justify-between mb-4">
                <form class="flex items-center gap-2">
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari izin..."
                        class="px-3 py-2 text-sm rounded-lg border border-gray-200 bg-white text-gray-900 w-48">
                    <select name="status" onchange="this.form.submit()"
                        class="px-3 py-2 text-sm rounded-lg border border-gray-200 bg-white text-gray-900">
                        <option value="">Semua Status</option>
                        <option value="pending" <?php if(request('status') === 'pending'): echo 'selected'; endif; ?>">Pending</option>
                        <option value="approved" <?php if(request('status') === 'approved'): echo 'selected'; endif; ?>">Disetujui</option>
                        <option value="rejected" <?php if(request('status') === 'rejected'): echo 'selected'; endif; ?>">Ditolak</option>
                        <option value="expired" <?php if(request('status') === 'expired'): echo 'selected'; endif; ?>">Kadaluarsa</option>
                    </select>
                </form>
                <button onclick="document.getElementById('addPermitModal').classList.remove('hidden')"
                    class="px-4 py-2 text-sm bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition flex items-center gap-2">
                    <span>📝</span> Ajukan Izin Baru
                </button>
            </div>

            <?php if(empty($permits) || count($permits) === 0): ?>
                <div
                    class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
                    <p class="text-4xl mb-3">📄</p>
                    <p class="text-sm text-gray-500">Belum ada izin ekspor. Ajukan izin pertama
                        Anda.</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php $__currentLoopData = $permits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $statusColors = [
                                'pending' => 'yellow',
                                'approved' => 'green',
                                'rejected' => 'red',
                                'expired' => 'gray',
                            ];
                            $color = $statusColors[$permit->status] ?? 'gray';
                        ?>
                        <div
                            class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg transition">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-base font-bold text-gray-900">
                                            <?php echo e($permit->permit_number); ?></h4>
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full bg-<?php echo e($color); ?>-100 text-<?php echo e($color); ?>-700 $color }}-500/20 $color }}-400">
                                            <?php echo e(ucfirst($permit->status)); ?>

                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <?php echo e($permit->permit_type); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">Berlaku Sampai</p>
                                    <p
                                        class="text-sm font-medium <?php echo e($permit->isExpired() ? 'text-red-600' : 'text-gray-700'); ?>">
                                        <?php echo e($permit->valid_until->format('d M Y')); ?>

                                        <?php if($permit->isExpired()): ?>
                                            <span class="text-xs">(Kadaluarsa)</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                <?php if($permit->destination_country): ?>
                                    <div>
                                        <span class="text-gray-400 text-xs block">Negara Tujuan</span>
                                        <span
                                            class="text-gray-700 font-medium"><?php echo e($permit->destination_country); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if($permit->commodity): ?>
                                    <div>
                                        <span class="text-gray-400 text-xs block">Komoditas</span>
                                        <span
                                            class="text-gray-700 font-medium"><?php echo e($permit->commodity); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if($permit->quantity_kg): ?>
                                    <div>
                                        <span class="text-gray-400 text-xs block">Kuantitas</span>
                                        <span
                                            class="text-gray-700 font-medium"><?php echo e(number_format($permit->quantity_kg, 1)); ?>

                                            kg</span>
                                    </div>
                                <?php endif; ?>
                                <?php if($permit->issuing_authority): ?>
                                    <div>
                                        <span class="text-gray-400 text-xs block">Penerbit</span>
                                        <span
                                            class="text-gray-700 font-medium"><?php echo e($permit->issuing_authority); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if($permit->notes): ?>
                                <p
                                    class="text-xs text-gray-500 mt-3 pt-3 border-t border-gray-100">
                                    <?php echo e(Str::limit($permit->notes, 150)); ?>

                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="mt-4"><?php echo e($permits->links()); ?></div>
            <?php endif; ?>
        </div>

        
        <div x-show="tab === 'certificates'" class="p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-700">Sertifikat Kesehatan Ikan</h3>
                <button onclick="document.getElementById('addCertificateModal').classList.remove('hidden')"
                    class="px-4 py-2 text-sm bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition flex items-center gap-2">
                    <span>🏥</span> Buat Sertifikat Baru
                </button>
            </div>

            <?php if(empty($certificates) || count($certificates) === 0): ?>
                <div
                    class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
                    <p class="text-4xl mb-3">🏥</p>
                    <p class="text-sm text-gray-500">Belum ada sertifikat kesehatan. Buat sertifikat
                        pertama Anda.</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php $__currentLoopData = $certificates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div
                            class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg transition">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h4 class="text-base font-bold text-gray-900">
                                        <?php echo e($cert->certificate_number); ?></h4>
                                    <p class="text-sm text-gray-500 mt-1">Issued:
                                        <?php echo e($cert->issued_date->format('d M Y')); ?></p>
                                </div>
                                <span
                                    class="text-xs px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">
                                    Valid
                                </span>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
                                <?php if($cert->veterinarian_name): ?>
                                    <div>
                                        <span class="text-gray-400 text-xs block">Dokter Hewan</span>
                                        <span
                                            class="text-gray-700 font-medium"><?php echo e($cert->veterinarian_name); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if($cert->species_tested): ?>
                                    <div>
                                        <span class="text-gray-400 text-xs block">Spesies Diuji</span>
                                        <span
                                            class="text-gray-700 font-medium"><?php echo e($cert->species_tested); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if($cert->test_results): ?>
                                    <div class="col-span-2 md:col-span-3">
                                        <span class="text-gray-400 text-xs block">Hasil Tes</span>
                                        <span
                                            class="text-gray-700"><?php echo e(Str::limit($cert->test_results, 200)); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>

        
        <div x-show="tab === 'customs'" class="p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-700">Deklarasi Bea Cukai</h3>
                <button onclick="document.getElementById('addCustomsModal').classList.remove('hidden')"
                    class="px-4 py-2 text-sm bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition flex items-center gap-2">
                    <span>🛃</span> Buat Deklarasi Baru
                </button>
            </div>

            <?php if(empty($customsDeclarations) || count($customsDeclarations) === 0): ?>
                <div
                    class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
                    <p class="text-4xl mb-3">🛃</p>
                    <p class="text-sm text-gray-500">Belum ada deklarasi bea cukai. Buat deklarasi
                        pertama Anda.</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php $__currentLoopData = $customsDeclarations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $declaration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div
                            class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg transition">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h4 class="text-base font-bold text-gray-900">
                                        <?php echo e($declaration->declaration_number); ?></h4>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <?php echo e($declaration->declaration_type); ?></p>
                                </div>
                                <span
                                    class="text-xs px-2 py-1 rounded-full bg-purple-100 text-purple-700">
                                    <?php echo e(ucfirst($declaration->status)); ?>

                                </span>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                <?php if($declaration->hs_code): ?>
                                    <div>
                                        <span class="text-gray-400 text-xs block">HS Code</span>
                                        <span
                                            class="text-gray-700 font-medium"><?php echo e($declaration->hs_code); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if($declaration->declared_value_usd): ?>
                                    <div>
                                        <span class="text-gray-400 text-xs block">Nilai Deklarasi</span>
                                        <span
                                            class="text-gray-700 font-medium">$<?php echo e(number_format($declaration->declared_value_usd, 2)); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if($declaration->weight_kg): ?>
                                    <div>
                                        <span class="text-gray-400 text-xs block">Berat</span>
                                        <span
                                            class="text-gray-700 font-medium"><?php echo e(number_format($declaration->weight_kg, 1)); ?>

                                            kg</span>
                                    </div>
                                <?php endif; ?>
                                <?php if($declaration->country_of_origin): ?>
                                    <div>
                                        <span class="text-gray-400 text-xs block">Negara Asal</span>
                                        <span
                                            class="text-gray-700 font-medium"><?php echo e($declaration->country_of_origin); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>

        
        <div x-show="tab === 'shipments'" class="p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-700">Pengiriman Ekspor</h3>
                <button onclick="document.getElementById('addShipmentModal').classList.remove('hidden')"
                    class="px-4 py-2 text-sm bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition flex items-center gap-2">
                    <span>🚢</span> Buat Pengiriman Baru
                </button>
            </div>

            <?php if(empty($shipments) || count($shipments) === 0): ?>
                <div
                    class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
                    <p class="text-4xl mb-3">🚢</p>
                    <p class="text-sm text-gray-500">Belum ada pengiriman ekspor. Buat pengiriman
                        pertama Anda.</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php $__currentLoopData = $shipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $statusColors = [
                                'preparing' => 'gray',
                                'ready_to_ship' => 'blue',
                                'in_transit' => 'yellow',
                                'customs_clearance' => 'purple',
                                'delivered' => 'green',
                                'cancelled' => 'red',
                            ];
                            $color = $statusColors[$shipment->status] ?? 'gray';
                        ?>
                        <div
                            class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg transition">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-base font-bold text-gray-900">
                                            <?php echo e($shipment->shipment_number); ?></h4>
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full bg-<?php echo e($color); ?>-100 text-<?php echo e($color); ?>-700 $color }}-500/20 $color }}-400">
                                            <?php echo e(str_replace('_', ' ', ucfirst($shipment->status))); ?>

                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <?php echo e($shipment->origin); ?> → <?php echo e($shipment->destination); ?>

                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">Estimasi Tiba</p>
                                    <p class="text-sm font-medium text-gray-700">
                                        <?php echo e($shipment->estimated_arrival ? $shipment->estimated_arrival->format('d M Y') : '-'); ?>

                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                <?php if($shipment->carrier): ?>
                                    <div>
                                        <span class="text-gray-400 text-xs block">Ekspedisi</span>
                                        <span
                                            class="text-gray-700 font-medium"><?php echo e($shipment->carrier); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if($shipment->tracking_number): ?>
                                    <div>
                                        <span class="text-gray-400 text-xs block">No. Tracking</span>
                                        <span
                                            class="text-gray-700 font-medium"><?php echo e($shipment->tracking_number); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if($shipment->total_weight_kg): ?>
                                    <div>
                                        <span class="text-gray-400 text-xs block">Berat Total</span>
                                        <span
                                            class="text-gray-700 font-medium"><?php echo e(number_format($shipment->total_weight_kg, 1)); ?>

                                            kg</span>
                                    </div>
                                <?php endif; ?>
                                <?php if($shipment->declared_value_usd): ?>
                                    <div>
                                        <span class="text-gray-400 text-xs block">Nilai</span>
                                        <span
                                            class="text-gray-700 font-medium">$<?php echo e(number_format($shipment->declared_value_usd, 2)); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="mt-4"><?php echo e($shipments->links()); ?></div>
            <?php endif; ?>
        </div>
    </div>

    
    <div id="addPermitModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white rounded-2xl border border-gray-200 w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-semibold text-gray-900">Ajukan Izin Ekspor Baru</h2>
                <button onclick="document.getElementById('addPermitModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('fisheries.export.store-permit')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900'; ?>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipe Izin
                            *</label>
                        <select name="permit_type" required class="<?php echo e($cls); ?>">
                            <option value="export_license">Export License</option>
                            <option value="catch_certificate">Catch Certificate</option>
                            <option value="processing_statement">Processing Statement</option>
                            <option value="re_export_certificate">Re-export Certificate</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Negara Tujuan
                            *</label>
                        <input type="text" name="destination_country" required placeholder="United States"
                            class="<?php echo e($cls); ?>">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Komoditas
                            *</label>
                        <input type="text" name="commodity" required placeholder="Frozen Shrimp"
                            class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kuantitas (kg)
                            *</label>
                        <input type="number" name="quantity_kg" required step="0.01" min="0"
                            placeholder="5000" class="<?php echo e($cls); ?>">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Berlaku
                            *</label>
                        <input type="date" name="valid_from" required class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kadaluarsa
                            *</label>
                        <input type="date" name="valid_until" required class="<?php echo e($cls); ?>">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Otoritas
                        Penerbit</label>
                    <input type="text" name="issuing_authority"
                        placeholder="Ministry of Marine Affairs and Fisheries" class="<?php echo e($cls); ?>">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                    <textarea name="notes" rows="3" placeholder="Persyaratan khusus, regulasi, dll."
                        class="<?php echo e($cls); ?>"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 text-sm bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition">
                        📤 Ajukan Izin
                    </button>
                    <button type="button" onclick="document.getElementById('addPermitModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-lg transition">
                        Batal
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\fisheries\export.blade.php ENDPATH**/ ?>