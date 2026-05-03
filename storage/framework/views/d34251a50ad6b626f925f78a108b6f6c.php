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
                    ← Kembali
                </a>
        <button onclick="document.getElementById('estimateModal').classList.remove('hidden')"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium whitespace-nowrap">
                + Buat Estimasi
            </button>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <?php if($estimates->count() === 0): ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'document','title' => 'Belum ada estimasi','message' => 'Belum ada estimasi biaya cetak. Buat estimasi pertama Anda.','actionText' => 'Buat Estimasi']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'document','title' => 'Belum ada estimasi','message' => 'Belum ada estimasi biaya cetak. Buat estimasi pertama Anda.','actionText' => 'Buat Estimasi']); ?>
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
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                No. Estimasi</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Customer</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Produk</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Qty</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Total Biaya</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Harga Penawaran</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Status</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Berlaku Sampai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $__currentLoopData = $estimates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $estimate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-indigo-600">
                                    <?php echo e($estimate->estimate_number); ?>

                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    <?php echo e($estimate->customer?->name ?? 'N/A'); ?>

                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $estimate->product_type))); ?>

                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    <?php echo e(number_format($estimate->quantity)); ?>

                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    Rp <?php echo e(number_format($estimate->total_cost ?? 0, 0, ',', '.')); ?>

                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-900">
                                    Rp <?php echo e(number_format($estimate->quoted_price ?? 0, 0, ',', '.')); ?>

                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                        $statusColors = [
                                            'draft' => 'gray',
                                            'sent' => 'blue',
                                            'accepted' => 'green',
                                            'rejected' => 'red',
                                            'expired' => 'orange',
                                        ];
                                        $sColor = $statusColors[$estimate->status] ?? 'gray';
                                    ?>
                                    <span
                                        class="px-2 py-1 text-xs rounded-full bg-<?php echo e($sColor); ?>-100 text-<?php echo e($sColor); ?>-700 $sColor }}-500/20 $sColor }}-400">
                                        <?php echo e(ucfirst($estimate->status)); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    <?php if($estimate->valid_until): ?>
                                        <?php if($estimate->is_expired): ?>
                                            <span
                                                class="text-red-600"><?php echo e($estimate->valid_until->format('d M Y')); ?></span>
                                        <?php else: ?>
                                            <?php echo e($estimate->valid_until->format('d M Y')); ?>

                                        <?php endif; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                <?php echo e($estimates->links()); ?>

            </div>
        <?php endif; ?>
    </div>

    
    <div id="estimateModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50"
                onclick="document.getElementById('estimateModal').classList.add('hidden')"></div>

            <div
                class="relative bg-white rounded-2xl border border-gray-200 w-full max-w-2xl p-6 shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Buat Estimasi Baru</h2>
                    <button onclick="document.getElementById('estimateModal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="<?php echo e(route('printing.estimate.create')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Produk
                                *</label>
                            <select name="product_type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="">Pilih Tipe</option>
                                <option value="business_cards">Kartu Nama</option>
                                <option value="flyers">Flyer</option>
                                <option value="brochures">Brosur</option>
                                <option value="posters">Poster</option>
                                <option value="catalogs">Katalog</option>
                                <option value="books">Buku</option>
                                <option value="packaging">Kemasan</option>
                                <option value="labels">Label</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah
                                *</label>
                            <input type="number" name="quantity" required min="1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 text-sm focus:ring-2 focus:ring-indigo-500"
                                placeholder="1000">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kertas
                                *</label>
                            <select name="paper_type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="">Pilih Kertas</option>
                                <option value="art_paper_120gsm">Art Paper 120 gsm</option>
                                <option value="art_paper_150gsm">Art Paper 150 gsm</option>
                                <option value="art_carton_260gsm">Art Carton 260 gsm</option>
                                <option value="hvs_80gsm">HVS 80 gsm</option>
                                <option value="ivory_230gsm">Ivory 230 gsm</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Markup
                                (%)</label>
                            <input type="number" name="markup_percentage" value="30" min="0" max="100"
                                step="0.01"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lebar Kertas
                                (mm) *</label>
                            <input type="number" name="paper_size_width" required step="0.01"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 text-sm focus:ring-2 focus:ring-indigo-500"
                                placeholder="210">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tinggi
                                Kertas (mm) *</label>
                            <input type="number" name="paper_size_height" required step="0.01"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 text-sm focus:ring-2 focus:ring-indigo-500"
                                placeholder="297">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Warna
                                Depan</label>
                            <select name="colors_front"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="4" selected>4 (CMYK)</option>
                                <option value="1">1 Warna</option>
                                <option value="2">2 Warna</option>
                                <option value="5">5 Warna</option>
                                <option value="6">6 Warna</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Warna
                                Belakang</label>
                            <select name="colors_back"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="0" selected>0 (Tanpa Cetak)</option>
                                <option value="1">1 Warna</option>
                                <option value="4">4 (CMYK)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button"
                            onclick="document.getElementById('estimateModal').classList.add('hidden')"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-sm font-medium">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
                            Hitung Estimasi
                        </button>
                    </div>
                </form>
            </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\printing\estimates.blade.php ENDPATH**/ ?>