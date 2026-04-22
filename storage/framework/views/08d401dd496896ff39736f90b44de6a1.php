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
     <?php $__env->slot('header', null, []); ?> Transfer Stok <?php $__env->endSlot(); ?>

    <style>
        .transfer-page {
            min-height: 100vh;
            background: #030712;
            padding-bottom: 6rem;
        }

        .mob-card {
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.25rem;
            padding: 1.25rem;
            margin-bottom: 1rem;
        }

        .mob-input {
            width: 100%;
            h-14 text-lg bg-[#0f172a] border border-white/15 rounded-xl px-4 text-white focus: outline-none focus:border-blue-500 touch-manipulation;
        }

        .mob-select {
            width: 100%;
            h-14 text-lg bg-[#0f172a] border border-white/15 rounded-xl px-4 text-white focus: outline-none focus:border-blue-500 touch-manipulation;
        }

        .mob-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 0.5rem;
        }

        .mob-submit-btn {
            width: 100%;
            h-14 bg-blue-600 hover: bg-blue-500 active:scale-95 text-white font-bold rounded-2xl transition touch-manipulation;
        }
    </style>

    <div class="transfer-page">
        
        <div class="sticky top-0 z-20 bg-gray-900/95 backdrop-blur border-b border-white/10 p-4">
            <div class="flex items-center gap-3">
                <a href="<?php echo e(route('mobile.hub')); ?>"
                    class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/5 hover:bg-white/10 active:scale-95 transition">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div class="flex-1">
                    <h1 class="text-base font-bold text-white">Transfer Stok</h1>
                    <p class="text-xs text-slate-400">Pindah stok antar bin / gudang</p>
                </div>
            </div>
        </div>

        <div class="p-4 space-y-4">
            <?php if(session('success')): ?>
                <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-4 text-sm text-green-400">
                    ✓ <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('mobile.transfer.store')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>

                
                <div class="mob-card">
                    <label class="mob-label">Produk</label>
                    <select name="product_id" required class="mob-select">
                        <option value="">-- Pilih Produk --</option>
                        <?php $__currentLoopData = \App\Models\Product::where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($p->id); ?>"><?php echo e($p->name); ?> (<?php echo e($p->sku); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div class="mob-card">
                    <label class="mob-label">Dari Bin</label>
                    <select name="from_bin_id" required class="mob-select from-bin-select">
                        <option value="">-- Pilih Bin Asal --</option>
                        <?php $__currentLoopData = $bins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($bin->id); ?>"><?php echo e($bin->code); ?> (<?php echo e($bin->warehouse->name ?? '-'); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div class="mob-card">
                    <label class="mob-label">Ke Bin</label>
                    <select name="to_bin_id" required class="mob-select to-bin-select">
                        <option value="">-- Pilih Bin Tujuan --</option>
                        <?php $__currentLoopData = $bins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($bin->id); ?>"><?php echo e($bin->code); ?>

                                (<?php echo e($bin->warehouse->name ?? '-'); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div class="mob-card">
                    <label class="mob-label">Jumlah Transfer</label>
                    <input type="number" name="quantity" min="1" step="1" placeholder="0" required
                        class="mob-input">
                </div>

                
                <div class="mob-card">
                    <label class="mob-label">Catatan (opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Contoh: Restock untuk promo" class="mob-input"></textarea>
                </div>

                
                <button type="submit" class="mob-submit-btn">
                    📦 Transfer Stok
                </button>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            // Prevent same bin selection
            document.querySelectorAll('.from-bin-select, .to-bin-select').forEach(sel => {
                sel.addEventListener('change', function() {
                    const from = document.querySelector('.from-bin-select').value;
                    const to = document.querySelector('.to-bin-select').value;
                    if (from && to && from === to) {
                        alert('Bin asal dan tujuan harus berbeda!');
                        this.value = '';
                    }
                });
            });
        </script>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\mobile\transfer.blade.php ENDPATH**/ ?>