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
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex items-center gap-3">
            CRM & Pipeline Penjualan
            <a href="<?php echo e(route('crm.kanban')); ?>" class="text-xs text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400">Tampilan Kanban →</a>
        </div>
     <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <?php
            $tid = auth()->user()->tenant_id;
            $totalLeads = \App\Models\CrmLead::where('tenant_id',$tid)->count();
            $activeLeads = \App\Models\CrmLead::where('tenant_id',$tid)->whereNotIn('stage',['won','lost'])->count();
        ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Lead</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo e($totalLeads); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Pipeline Aktif</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1"><?php echo e($activeLeads); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Won Bulan Ini</p>
            <p class="text-xl font-bold text-green-600 dark:text-green-400 mt-1">Rp <?php echo e(number_format($wonThisMonth,0,',','.')); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Follow-up Hari Ini</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1"><?php echo e($followUpToday); ?></p>
        </div>
    </div>

    
    <?php if($pipeline->count() > 0): ?>
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        <?php $__currentLoopData = ['new'=>'Baru','contacted'=>'Dihubungi','qualified'=>'Qualified','proposal'=>'Proposal','negotiation'=>'Negosiasi']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($pipeline->has($stage)): ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl p-3 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400"><?php echo e($label); ?></p>
            <p class="text-lg font-bold text-gray-900 dark:text-white"><?php echo e($pipeline[$stage]->count); ?></p>
            <p class="text-xs text-gray-500 dark:text-slate-400">Rp <?php echo e(number_format($pipeline[$stage]->total_value/1000000,1)); ?>jt</p>
        </div>
        <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nama / perusahaan..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="stage" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Stage</option>
                <?php $__currentLoopData = ['new'=>'Baru','contacted'=>'Dihubungi','qualified'=>'Qualified','proposal'=>'Proposal','negotiation'=>'Negosiasi','won'=>'Won','lost'=>'Lost']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($v); ?>" <?php if(request('stage')===$v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'crm', 'create')): ?>
        <button onclick="document.getElementById('modal-add-lead').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Tambah Lead</button>
        <?php endif; ?>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Lead</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Kontak</th>
                        <th class="px-4 py-3 text-center">Stage</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Nilai</th>
                        <th class="px-4 py-3 text-center hidden md:table-cell">Prob.</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Last Contact</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">AI Score</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $leads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $stageColors = ['new'=>'gray','contacted'=>'blue','qualified'=>'indigo','proposal'=>'purple','negotiation'=>'amber','won'=>'green','lost'=>'red'];
                        $stageLabels = ['new'=>'Baru','contacted'=>'Dihubungi','qualified'=>'Qualified','proposal'=>'Proposal','negotiation'=>'Negosiasi','won'=>'Won','lost'=>'Lost'];
                        $c = $stageColors[$lead->stage] ?? 'gray';
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900 dark:text-white"><?php echo e($lead->name); ?></p>
                            <p class="text-xs text-gray-500 dark:text-slate-400"><?php echo e($lead->company ?? '-'); ?></p>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <p class="text-xs text-gray-500 dark:text-slate-400"><?php echo e($lead->phone ?? '-'); ?></p>
                            <p class="text-xs text-gray-500 dark:text-slate-400"><?php echo e($lead->product_interest ?? ''); ?></p>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($c); ?>-100 text-<?php echo e($c); ?>-700 dark:bg-<?php echo e($c); ?>-500/20 dark:text-<?php echo e($c); ?>-400">
                                <?php echo e($stageLabels[$lead->stage] ?? $lead->stage); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-gray-900 dark:text-white">
                            <?php echo e($lead->estimated_value > 0 ? 'Rp '.number_format($lead->estimated_value,0,',','.') : '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-center hidden md:table-cell text-gray-500 dark:text-slate-400"><?php echo e($lead->probability); ?>%</td>
                        <td class="px-4 py-3 hidden lg:table-cell text-xs text-gray-500 dark:text-slate-400"><?php echo e($lead->last_contact_at?->diffForHumans() ?? '-'); ?></td>
                        <td class="px-4 py-3 text-center hidden sm:table-cell">
                            <span id="score-badge-<?php echo e($lead->id); ?>" class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400">...</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button onclick="openAiModal(<?php echo e($lead->id); ?>, '<?php echo e(addslashes($lead->name)); ?>')"
                                    class="p-1.5 rounded-lg text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-500/10" title="AI Score & Follow-up">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.347.347a3.5 3.5 0 01-4.95 0l-.347-.347z"/></svg>
                                </button>
                                <button onclick="openActivity(<?php echo e($lead->id); ?>, '<?php echo e(addslashes($lead->name)); ?>')"
                                    class="p-1.5 rounded-lg text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-500/10" title="Log Aktivitas">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                </button>
                                <button onclick="openStage(<?php echo e($lead->id); ?>, '<?php echo e(addslashes($lead->name)); ?>', '<?php echo e($lead->stage); ?>', <?php echo e($lead->probability); ?>)"
                                    class="p-1.5 rounded-lg text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/10" title="Update Stage">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <?php if($lead->stage === 'won' && !$lead->converted_to_customer_id): ?>
                                <form method="POST" action="<?php echo e(route('crm.convert-customer', $lead)); ?>" class="inline"
                                    onsubmit="return confirm('Konversi lead \"<?php echo e(addslashes($lead->name)); ?>\" menjadi Customer?')">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="p-1.5 rounded-lg text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-500/10" title="Konversi ke Customer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                    </button>
                                </form>
                                <?php elseif($lead->converted_to_customer_id): ?>
                                <span class="p-1.5 text-green-500 dark:text-green-400" title="Sudah dikonversi ke Customer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </span>
                                <?php endif; ?>
                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'crm', 'delete')): ?>
                                <form method="POST" action="<?php echo e(route('crm.destroy', $lead)); ?>" onsubmit="return confirm('Hapus lead ini?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada lead. Tambahkan prospek pertama Anda.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($leads->hasPages()): ?>
        <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5"><?php echo e($leads->links()); ?></div>
        <?php endif; ?>
    </div>

    
    <div id="modal-ai" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.347.347a3.5 3.5 0 01-4.95 0l-.347-.347z"/></svg>
                    AI Insight Lead
                </h3>
                <button onclick="document.getElementById('modal-ai').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <div id="ai-modal-body" class="p-6">
                <div class="flex items-center justify-center py-8">
                    <svg class="animate-spin w-6 h-6 text-purple-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                </div>
            </div>
        </div>
    </div>

    
    <div id="modal-add-lead" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 class="font-semibold text-gray-900 dark:text-white">Tambah Lead</h3>
                <button onclick="document.getElementById('modal-add-lead').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('crm.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Kontak *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Perusahaan</label>
                        <input type="text" name="company" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Sumber</label>
                        <select name="source" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih --</option>
                            <option value="referral">Referral</option>
                            <option value="website">Website</option>
                            <option value="cold_call">Cold Call</option>
                            <option value="social_media">Social Media</option>
                            <option value="exhibition">Pameran</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">No. Telepon</label>
                        <input type="text" name="phone" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Email</label>
                        <input type="email" name="email" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Produk Diminati</label>
                        <input type="text" name="product_interest" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Estimasi Nilai (Rp)</label>
                        <input type="number" name="estimated_value" min="0" step="100000" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Target Closing</label>
                        <input type="date" name="expected_close_date" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-lead').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-stage" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Update Stage</h3>
                <button onclick="document.getElementById('modal-stage').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-stage" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <p id="stage-lead-name" class="text-sm font-medium text-gray-900 dark:text-white"></p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Stage</label>
                    <select id="stage-select" name="stage" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php $__currentLoopData = ['new'=>'Baru','contacted'=>'Dihubungi','qualified'=>'Qualified','proposal'=>'Proposal','negotiation'=>'Negosiasi','won'=>'Won','lost'=>'Lost']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($v); ?>"><?php echo e($l); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Probabilitas (%)</label>
                    <input type="number" id="stage-prob" name="probability" min="0" max="100" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-stage').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Update</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-activity" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Log Aktivitas</h3>
                <button onclick="document.getElementById('modal-activity').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-activity" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <p id="activity-lead-name" class="text-sm font-medium text-gray-900 dark:text-white"></p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe Aktivitas</label>
                    <select name="type" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="call">Telepon</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="email">Email</option>
                        <option value="meeting">Meeting</option>
                        <option value="demo">Demo</option>
                        <option value="proposal">Proposal</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan *</label>
                    <textarea name="description" rows="3" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Hasil</label>
                    <select name="outcome" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih --</option>
                        <option value="interested">Tertarik</option>
                        <option value="follow_up">Perlu Follow-up</option>
                        <option value="not_interested">Tidak Tertarik</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Follow-up Berikutnya</label>
                    <input type="date" name="next_follow_up" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-activity').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Batch load AI scores on page load
    document.addEventListener('DOMContentLoaded', async () => {
        try {
            const res = await fetch('<?php echo e(route("crm.ai.score-all")); ?>');
            const data = await res.json();
            const tierClasses = {
                hot:  'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
                warm: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
                cold: 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
            };
            Object.entries(data).forEach(([id, s]) => {
                const el = document.getElementById('score-badge-' + id);
                if (el) {
                    el.textContent = s.tier_label + ' ' + s.score;
                    el.className = 'px-2 py-0.5 rounded-full text-xs ' + (tierClasses[s.tier] || '');
                }
            });
        } catch(e) {}
    });

    async function openAiModal(id, name) {
        document.getElementById('modal-ai').classList.remove('hidden');
        document.getElementById('ai-modal-body').innerHTML = `
            <div class="flex items-center justify-center py-8">
                <svg class="animate-spin w-6 h-6 text-purple-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
            </div>`;

        const [scoreRes, followRes] = await Promise.all([
            fetch('<?php echo e(url("crm/ai/score")); ?>/' + id),
            fetch('<?php echo e(url("crm/ai/follow-up")); ?>/' + id),
        ]);
        const score = await scoreRes.json();
        const follow = await followRes.json();

        const tierClasses = {
            hot:  'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
            warm: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
            cold: 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
        };
        const priorityClasses = {
            high:   'bg-red-50 border-red-200 dark:bg-red-500/10 dark:border-red-500/20 text-red-700 dark:text-red-400',
            normal: 'bg-blue-50 border-blue-200 dark:bg-blue-500/10 dark:border-blue-500/20 text-blue-700 dark:text-blue-400',
            low:    'bg-gray-50 border-gray-200 dark:bg-white/5 dark:border-white/10 text-gray-600 dark:text-slate-400',
        };

        const breakdownRows = score.breakdown.map(b =>
            `<tr class="border-t border-gray-100 dark:border-white/5">
                <td class="py-1.5 text-gray-600 dark:text-slate-400">${b.label}</td>
                <td class="py-1.5 text-gray-500 dark:text-slate-500 text-xs">${b.value}</td>
                <td class="py-1.5 text-right font-medium text-gray-900 dark:text-white">+${b.points}</td>
            </tr>`
        ).join('');

        const suggestionItems = (follow.suggestions || []).map(s =>
            `<li class="flex items-start gap-2"><span class="text-purple-400 mt-0.5">•</span><span>${s}</span></li>`
        ).join('');

        document.getElementById('ai-modal-body').innerHTML = `
            <p class="text-sm font-semibold text-gray-900 dark:text-white mb-4">${name}</p>

            <div class="flex items-center gap-3 mb-4">
                <div class="w-14 h-14 rounded-full flex items-center justify-center text-xl font-bold border-4 ${score.score >= 70 ? 'border-red-400 text-red-600 dark:text-red-400' : score.score >= 40 ? 'border-amber-400 text-amber-600 dark:text-amber-400' : 'border-blue-400 text-blue-600 dark:text-blue-400'}">
                    ${score.score}
                </div>
                <div>
                    <span class="px-2 py-0.5 rounded-full text-xs ${tierClasses[score.tier]}">${score.tier_label}</span>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Lead Score</p>
                </div>
            </div>

            <table class="w-full text-sm mb-5">
                <thead><tr class="text-xs text-gray-400 dark:text-slate-500 uppercase">
                    <th class="text-left pb-1">Faktor</th><th class="text-left pb-1">Detail</th><th class="text-right pb-1">Poin</th>
                </tr></thead>
                <tbody>${breakdownRows}</tbody>
            </table>

            <div class="border-t border-gray-100 dark:border-white/10 pt-4">
                <p class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase mb-2">Saran Follow-up AI</p>
                <div class="p-3 rounded-xl border ${priorityClasses[follow.priority] || priorityClasses.normal} mb-3">
                    <p class="text-sm font-medium">${follow.action_label}: ${follow.message}</p>
                    ${follow.days_since_last !== null ? `<p class="text-xs mt-1 opacity-75">Terakhir kontak: ${follow.days_since_last} hari lalu</p>` : ''}
                </div>
                <ul class="space-y-1 text-sm text-gray-600 dark:text-slate-300">${suggestionItems}</ul>
            </div>`;
    }

    function openStage(id, name, stage, prob) {
        document.getElementById('form-stage').action = '<?php echo e(url("crm")); ?>/' + id + '/stage';
        document.getElementById('stage-lead-name').textContent = name;
        document.getElementById('stage-select').value = stage;
        document.getElementById('stage-prob').value = prob;
        document.getElementById('modal-stage').classList.remove('hidden');
    }
    function openActivity(id, name) {
        document.getElementById('form-activity').action = '<?php echo e(url("crm")); ?>/' + id + '/activity';
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\crm\index.blade.php ENDPATH**/ ?>