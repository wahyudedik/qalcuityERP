
<?php $__env->startSection('title', 'Pengaturan API & Webhook'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6 space-y-8 max-w-4xl">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">API & Webhook</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola token REST API dan webhook outbound untuk integrasi pihak ketiga.</p>
    </div>

    <?php if(session('success')): ?>
    <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">
        <?php echo e(session('success')); ?>

    </div>
    <?php endif; ?>

    <?php if(session('new_token')): ?>
    <div class="p-4 rounded-xl bg-amber-50 border border-amber-300">
        <p class="text-sm font-semibold text-amber-800 mb-2">Token baru — salin sekarang, tidak akan ditampilkan lagi:</p>
        <div class="flex items-center gap-2">
            <code class="flex-1 text-xs bg-white border border-amber-200 rounded-lg px-3 py-2 font-mono text-amber-900 break-all"><?php echo e(session('new_token')); ?></code>
            <button onclick="navigator.clipboard.writeText('<?php echo e(session('new_token')); ?>')"
                    class="px-3 py-2 text-xs bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition shrink-0">Salin</button>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <p class="text-sm font-semibold text-blue-800 mb-1">Base URL REST API</p>
        <code class="text-xs text-blue-700 font-mono"><?php echo e(url('/api/v1')); ?></code>
        <p class="text-xs text-blue-600 mt-2">Autentikasi: <code class="bg-blue-100 px-1 rounded">Authorization: Bearer &lt;token&gt;</code> atau header <code class="bg-blue-100 px-1 rounded">X-API-Token</code></p>
        <a href="/api-docs/" target="_blank" class="inline-flex items-center gap-1.5 mt-3 text-xs font-medium text-blue-600 hover:underline">
            📖 Lihat Dokumentasi API Lengkap (Swagger) →
        </a>
        <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs text-blue-700">
            <?php $__currentLoopData = ['GET /stats','GET /products','GET /orders','POST /orders','GET /invoices','GET /customers','POST /customers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <code class="bg-blue-100 px-2 py-1 rounded"><?php echo e($ep); ?></code>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700">Token API</h2>
            <button onclick="document.getElementById('addTokenModal').classList.remove('hidden')"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Token
            </button>
        </div>
        <?php if($tokens->isEmpty()): ?>
        <div class="p-8 text-center text-sm text-gray-400">Belum ada token API.</div>
        <?php else: ?>
        <div class="divide-y divide-gray-100">
            <?php $__currentLoopData = $tokens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $token): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="px-5 py-4 flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-medium text-gray-900"><?php echo e($token->name); ?></p>
                        <?php if(!$token->is_active): ?>
                        <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">Dicabut</span>
                        <?php elseif($token->expires_at && $token->expires_at->isPast()): ?>
                        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Kadaluarsa</span>
                        <?php else: ?>
                        <span class="text-xs bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded-full">Aktif</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Izin: <?php echo e(implode(', ', $token->abilities ?? [])); ?>

                        <?php if($token->expires_at): ?> · Exp: <?php echo e($token->expires_at->format('d M Y')); ?> <?php endif; ?>
                        <?php if($token->last_used_at): ?> · Terakhir: <?php echo e($token->last_used_at->diffForHumans()); ?> <?php endif; ?>
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <?php if($token->is_active): ?>
                    <form method="POST" action="<?php echo e(route('api-settings.tokens.revoke', $token)); ?>">
                        <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                        <button type="submit" class="text-xs text-amber-500 hover:text-amber-600 transition">Cabut</button>
                    </form>
                    <?php endif; ?>
                    <form method="POST" action="<?php echo e(route('api-settings.tokens.destroy', $token)); ?>" onsubmit="return confirm('Hapus token?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="text-xs text-red-400 hover:text-red-500 transition">Hapus</button>
                    </form>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>
    </div>

    
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h2 class="text-sm font-semibold text-gray-700">Webhook Outbound</h2>
                <a href="<?php echo e(route('api-settings.webhooks.log')); ?>" class="text-xs text-blue-500 hover:text-blue-600 transition">Delivery Log →</a>
            </div>
            <button onclick="document.getElementById('addWebhookModal').classList.remove('hidden')"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Webhook
            </button>
        </div>
        <?php if($webhooks->isEmpty()): ?>
        <div class="p-8 text-center text-sm text-gray-400">Belum ada webhook subscription.</div>
        <?php else: ?>
        <div class="divide-y divide-gray-100">
            <?php $__currentLoopData = $webhooks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="px-5 py-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-medium text-gray-900"><?php echo e($wh->name); ?></p>
                            <span class="text-xs px-2 py-0.5 rounded-full <?php echo e($wh->is_active ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-500'); ?>">
                                <?php echo e($wh->is_active ? 'Aktif' : 'Nonaktif'); ?>

                            </span>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5 truncate max-w-xs"><?php echo e($wh->url); ?></p>
                        <p class="text-xs text-gray-400 mt-0.5">Events: <?php echo e(implode(', ', $wh->events ?? [])); ?></p>
                        <div class="flex items-center gap-3 mt-0.5">
                            <?php if($wh->last_triggered_at): ?>
                            <p class="text-xs text-gray-400">Terakhir: <?php echo e($wh->last_triggered_at->diffForHumans()); ?></p>
                            <?php endif; ?>
                            <?php if($wh->retry_count > 0): ?>
                            <p class="text-xs text-amber-500"><?php echo e($wh->retry_count); ?> gagal berturut</p>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Secret: <code class="bg-gray-100 px-1 rounded text-[10px]"><?php echo e(Str::mask($wh->secret, '*', 4)); ?></code>
                        </p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <form method="POST" action="<?php echo e(route('api-settings.webhooks.test', $wh)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="text-xs text-blue-400 hover:text-blue-500 transition">Test</button>
                        </form>
                        <form method="POST" action="<?php echo e(route('api-settings.webhooks.toggle', $wh)); ?>">
                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                            <button type="submit" class="text-xs text-amber-500 hover:text-amber-600 transition">
                                <?php echo e($wh->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?>

                            </button>
                        </form>
                        <form method="POST" action="<?php echo e(route('api-settings.webhooks.destroy', $wh)); ?>" onsubmit="return confirm('Hapus webhook?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="text-xs text-red-400 hover:text-red-500 transition">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>
    </div>

</div>


<div id="addTokenModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl border border-gray-200 w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-semibold text-gray-900">Buat Token API</h3>
            <button onclick="document.getElementById('addTokenModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="<?php echo e(route('api-settings.tokens.store')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nama Token</label>
                <input type="text" name="name" required placeholder="Contoh: Integrasi Tokopedia"
                       class="w-full px-3 py-2 rounded-lg text-sm bg-gray-50 border border-gray-200 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-2">Izin</label>
                <div class="space-y-2">
                    <?php $__currentLoopData = ['read' => 'Read — baca data', 'write' => 'Write — buat & update data', 'delete' => 'Delete — hapus data', '*' => 'Full Access']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="abilities[]" value="<?php echo e($val); ?>" <?php echo e($val === 'read' ? 'checked' : ''); ?>

                               class="rounded border-gray-300 text-blue-600">
                        <span class="text-sm text-gray-700"><?php echo e($label); ?></span>
                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Kadaluarsa (opsional)</label>
                <input type="date" name="expires_at"
                       class="w-full px-3 py-2 rounded-lg text-sm bg-gray-50 border border-gray-200 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('addTokenModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 text-gray-700 hover:bg-gray-50 transition">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2 rounded-lg text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium transition">Buat Token</button>
            </div>
        </form>
    </div>
</div>


<div id="addWebhookModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl border border-gray-200 w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-semibold text-gray-900">Tambah Webhook</h3>
            <button onclick="document.getElementById('addWebhookModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="<?php echo e(route('api-settings.webhooks.store')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nama</label>
                <input type="text" name="name" required placeholder="Contoh: Notifikasi Order ke Slack"
                       class="w-full px-3 py-2 rounded-lg text-sm bg-gray-50 border border-gray-200 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">URL Endpoint</label>
                <input type="url" name="url" required placeholder="https://hooks.example.com/..."
                       class="w-full px-3 py-2 rounded-lg text-sm bg-gray-50 border border-gray-200 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-2">Events</label>
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    <?php $__currentLoopData = $availableEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $events): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1"><?php echo e($group); ?></p>
                        <div class="space-y-1">
                            <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="events[]" value="<?php echo e($ev); ?>"
                                       class="rounded border-gray-300 text-blue-600">
                                <span class="text-sm text-gray-700 font-mono text-xs"><?php echo e($ev); ?></span>
                            </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('addWebhookModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 text-gray-700 hover:bg-gray-50 transition">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2 rounded-lg text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium transition">Simpan</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\settings\api.blade.php ENDPATH**/ ?>