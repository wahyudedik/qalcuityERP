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
     <?php $__env->slot('header', null, []); ?> CRM Pipeline <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('crm.index')); ?>"
                class="text-xs text-gray-500 hover:text-blue-600">Tampilan
                Tabel →</a>
    </div>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Pipeline</p>
            <p class="text-2xl font-bold text-blue-600 mt-1"><?php echo e($stageStats->sum('count')); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Nilai Pipeline</p>
            <p class="text-lg font-bold text-gray-900 mt-1">Rp
                <?php echo e(number_format($stageStats->sum('total_value') / 1000000, 1)); ?>jt</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Won Bulan Ini</p>
            <p class="text-lg font-bold text-green-600 mt-1">Rp
                <?php echo e(number_format($wonThisMonth / 1000000, 1)); ?>jt</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Follow-up Hari Ini</p>
            <p class="text-2xl font-bold text-amber-600 mt-1"><?php echo e($followUpToday); ?></p>
        </div>
    </div>

    
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500">Drag kartu untuk pindah stage</p>
        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'crm', 'create')): ?>
        <button onclick="document.getElementById('modal-add-lead').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Tambah Lead</button>
        <?php endif; ?>
    </div>

    <?php if(session('success')): ?>
        <div
            class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
            <?php echo e(session('success')); ?></div>
    <?php endif; ?>

    
    <?php
        $stageLabels = [
            'new' => 'Baru',
            'contacted' => 'Dihubungi',
            'qualified' => 'Qualified',
            'proposal' => 'Proposal',
            'negotiation' => 'Negosiasi',
            'won' => 'Won',
            'lost' => 'Lost',
        ];
        $stageColors = [
            'new' => 'gray',
            'contacted' => 'blue',
            'qualified' => 'indigo',
            'proposal' => 'purple',
            'negotiation' => 'amber',
            'won' => 'green',
            'lost' => 'red',
        ];
        $activeStages = ['new', 'contacted', 'qualified', 'proposal', 'negotiation'];
    ?>

    <div class="overflow-x-auto pb-4">
        <div class="flex gap-4 min-w-max">
            <?php $__currentLoopData = $activeStages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $stageLeads = $leads->get($stage, collect());
                    $sc = $stageColors[$stage];
                    $stageTotal = $stageLeads->sum('estimated_value');
                ?>
                <div class="w-64 flex flex-col" data-stage="<?php echo e($stage); ?>" ondragover="event.preventDefault()"
                    ondrop="dropLead(event, '<?php echo e($stage); ?>')">

                    
                    <div
                        class="flex items-center justify-between px-3 py-2 rounded-xl bg-<?php echo e($sc); ?>-50 $sc }}-500/10 border border-<?php echo e($sc); ?>-200 $sc }}-500/20 mb-3">
                        <div>
                            <p
                                class="text-sm font-semibold text-<?php echo e($sc); ?>-700 $sc }}-400">
                                <?php echo e($stageLabels[$stage]); ?></p>
                            <?php if($stageTotal > 0): ?>
                                <p class="text-xs text-<?php echo e($sc); ?>-600 $sc }}-500">Rp
                                    <?php echo e(number_format($stageTotal / 1000000, 1)); ?>jt</p>
                            <?php endif; ?>
                        </div>
                        <span
                            class="w-6 h-6 flex items-center justify-center rounded-full bg-<?php echo e($sc); ?>-100 $sc }}-500/20 text-xs font-bold text-<?php echo e($sc); ?>-700 $sc }}-400">
                            <?php echo e($stageLeads->count()); ?>

                        </span>
                    </div>

                    
                    <div class="flex flex-col gap-2 min-h-[200px]" id="col-<?php echo e($stage); ?>">
                        <?php $__currentLoopData = $stageLeads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="lead-card bg-white rounded-xl border border-gray-200 p-3 cursor-grab active:cursor-grabbing shadow-sm hover:shadow-md transition"
                                draggable="true" data-id="<?php echo e($lead->id); ?>"
                                ondragstart="dragStart(event, <?php echo e($lead->id); ?>)">
                                <div class="flex items-start justify-between gap-1 mb-2">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate">
                                            <?php echo e($lead->name); ?></p>
                                        <?php if($lead->company): ?>
                                            <p class="text-xs text-gray-500 truncate">
                                                <?php echo e($lead->company); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <span
                                        class="shrink-0 text-xs font-medium text-<?php echo e($sc); ?>-600 $sc }}-400"><?php echo e($lead->probability); ?>%</span>
                                </div>
                                <?php if($lead->estimated_value > 0): ?>
                                    <p class="text-xs font-semibold text-blue-600 mb-1">Rp
                                        <?php echo e(number_format($lead->estimated_value, 0, ',', '.')); ?></p>
                                <?php endif; ?>
                                <div class="flex items-center justify-between mt-2">
                                    <span
                                        class="text-xs text-gray-400"><?php echo e($lead->last_contact_at?->diffForHumans() ?? 'Belum dihubungi'); ?></span>
                                    <div class="flex gap-1">
                                        <span id="kb-score-<?php echo e($lead->id); ?>"
                                            class="text-xs px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-400"></span>
                                        <button
                                            onclick="openActivity(<?php echo e($lead->id); ?>, '<?php echo e(addslashes($lead->name)); ?>')"
                                            class="p-1 rounded text-blue-500 hover:bg-blue-50"
                                            title="Log Aktivitas">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                            </svg>
                                        </button>
                                        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'crm', 'delete')): ?>
                                        <form method="POST" action="<?php echo e(route('crm.destroy', $lead)); ?>"
                                            onsubmit="return confirm('Hapus lead?')">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit"
                                                class="p-1 rounded text-red-400 hover:bg-red-50">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            
            <?php $__currentLoopData = ['won', 'lost']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $stageLeads = $leads->get($stage, collect());
                    $sc = $stageColors[$stage];
                ?>
                <div class="w-48 flex flex-col">
                    <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-<?php echo e($sc); ?>-50 $sc }}-500/10 border border-<?php echo e($sc); ?>-200 $sc }}-500/20 mb-3"
                        ondragover="event.preventDefault()" ondrop="dropLead(event, '<?php echo e($stage); ?>')">
                        <p
                            class="text-sm font-semibold text-<?php echo e($sc); ?>-700 $sc }}-400">
                            <?php echo e($stageLabels[$stage]); ?></p>
                        <span
                            class="w-6 h-6 flex items-center justify-center rounded-full bg-<?php echo e($sc); ?>-100 $sc }}-500/20 text-xs font-bold text-<?php echo e($sc); ?>-700 $sc }}-400">
                            <?php echo e($stageLeads->count()); ?>

                        </span>
                    </div>
                    <div class="flex flex-col gap-2 min-h-[100px]" id="col-<?php echo e($stage); ?>"
                        ondragover="event.preventDefault()" ondrop="dropLead(event, '<?php echo e($stage); ?>')">
                        <?php $__currentLoopData = $stageLeads->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div
                                class="bg-white rounded-xl border border-gray-200 px-3 py-2 text-xs">
                                <p class="font-medium text-gray-900 truncate"><?php echo e($lead->name); ?></p>
                                <?php if($lead->estimated_value > 0): ?>
                                    <p class="text-<?php echo e($sc); ?>-600 $sc }}-400">Rp
                                        <?php echo e(number_format($lead->estimated_value / 1000000, 1)); ?>jt</p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if($stageLeads->count() > 5): ?>
                            <p class="text-xs text-center text-gray-400">
                                +<?php echo e($stageLeads->count() - 5); ?> lainnya</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    <div id="modal-add-lead" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div
                class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white">
                <h3 class="font-semibold text-gray-900">Tambah Lead</h3>
                <button onclick="document.getElementById('modal-add-lead').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('crm.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Kontak
                            *</label>
                        <input type="text" name="name" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-medium text-gray-600 mb-1">Perusahaan</label>
                        <input type="text" name="company"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">No.
                            Telepon</label>
                        <input type="text" name="phone"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Estimasi Nilai
                            (Rp)</label>
                        <input type="number" name="estimated_value" min="0" step="100000"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Sumber</label>
                        <select name="source"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih --</option>
                            <option value="referral">Referral</option>
                            <option value="website">Website</option>
                            <option value="cold_call">Cold Call</option>
                            <option value="social_media">Social Media</option>
                            <option value="exhibition">Pameran</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Produk
                            Diminati</label>
                        <input type="text" name="product_interest"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-lead').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-activity" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Log Aktivitas</h3>
                <button onclick="document.getElementById('modal-activity').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-activity" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <p id="activity-lead-name" class="text-sm font-medium text-gray-900"></p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tipe</label>
                    <select name="type"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="call">Telepon</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="email">Email</option>
                        <option value="meeting">Meeting</option>
                        <option value="demo">Demo</option>
                        <option value="proposal">Proposal</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan *</label>
                    <textarea name="description" rows="3" required
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Follow-up
                        Berikutnya</label>
                    <input type="date" name="next_follow_up"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-activity').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            let draggedId = null;

            // Batch load AI scores
            document.addEventListener('DOMContentLoaded', async () => {
                try {
                    const res = await fetch('<?php echo e(route('crm.ai.score-all')); ?>');
                    const data = await res.json();
                    const tierClasses = {
                        hot: 'bg-red-100 text-red-700',
                        warm: 'bg-amber-100 text-amber-700',
                        cold: 'bg-blue-100 text-blue-700',
                    };
                    Object.entries(data).forEach(([id, s]) => {
                        const el = document.getElementById('kb-score-' + id);
                        if (el) {
                            el.textContent = s.tier_label + ' ' + s.score;
                            el.className = 'text-xs px-1.5 py-0.5 rounded-full ' + (tierClasses[s.tier] ||
                                '');
                        }
                    });
                } catch (e) {}
            });

            function dragStart(event, id) {
                draggedId = id;
                event.dataTransfer.effectAllowed = 'move';
            }

            async function dropLead(event, stage) {
                event.preventDefault();
                if (!draggedId) return;

                const res = await fetch('<?php echo e(url('crm')); ?>/' + draggedId + '/stage-drag', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        stage
                    }),
                });

                if (res.ok) {
                    // Move card in DOM
                    const card = document.querySelector(`.lead-card[data-id="${draggedId}"]`);
                    const targetCol = document.getElementById('col-' + stage);
                    if (card && targetCol) {
                        targetCol.prepend(card);
                        card.classList.add('ring-2', 'ring-blue-500');
                        setTimeout(() => card.classList.remove('ring-2', 'ring-blue-500'), 1500);
                    }
                }
                draggedId = null;
            }

            function openActivity(id, name) {
                document.getElementById('form-activity').action = '<?php echo e(url('crm')); ?>/' + id + '/activity';
                document.getElementById('activity-lead-name').textContent = name;
                document.getElementById('modal-activity').classList.remove('hidden');
            }
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\crm\kanban.blade.php ENDPATH**/ ?>