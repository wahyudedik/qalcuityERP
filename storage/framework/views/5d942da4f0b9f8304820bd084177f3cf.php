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
     <?php $__env->slot('header', null, []); ?> Rekonsiliasi Bank <?php $__env->endSlot(); ?>

    <?php $__env->startPush('styles'); ?>
        <style>
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }

                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            .animate-slide-in {
                animation: slideIn 0.3s ease-out;
            }
        </style>
    <?php $__env->stopPush(); ?>

    <div class="space-y-6">

        
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400">Total Mutasi</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo e($summary['total']); ?></p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400">Matched</p>
                <p class="text-2xl font-bold text-green-500 mt-1"><?php echo e($summary['matched']); ?></p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400">Unmatched</p>
                <p class="text-2xl font-bold text-amber-500 mt-1"><?php echo e($summary['unmatched']); ?></p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400">Total Kredit</p>
                <p class="text-lg font-bold text-green-500 mt-1">Rp <?php echo e(number_format($summary['credit'], 0, ',', '.')); ?>

                </p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400">Total Debit</p>
                <p class="text-lg font-bold text-red-500 mt-1">Rp <?php echo e(number_format($summary['debit'], 0, ',', '.')); ?>

                </p>
            </div>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Rekening</label>
                    <select name="account_id"
                        class="bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Rekening</option>
                        <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($acc->id); ?>" <?php if(request('account_id') == $acc->id): echo 'selected'; endif; ?>><?php echo e($acc->bank_name); ?> —
                                <?php echo e($acc->account_number); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Status</label>
                    <select name="status"
                        class="bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua</option>
                        <option value="unmatched" <?php if(request('status') === 'unmatched'): echo 'selected'; endif; ?>>Unmatched</option>
                        <option value="matched" <?php if(request('status') === 'matched'): echo 'selected'; endif; ?>>Matched</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Dari</label>
                    <input type="date" name="from" value="<?php echo e(request('from')); ?>"
                        class="bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Sampai</label>
                    <input type="date" name="to" value="<?php echo e(request('to')); ?>"
                        class="bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm hover:bg-blue-700">Filter</button>
                <?php if(request()->hasAny(['account_id', 'status', 'from', 'to'])): ?>
                    <a href="<?php echo e(route('bank.reconciliation')); ?>"
                        class="px-4 py-2 border border-gray-200 dark:border-white/10 rounded-xl text-sm text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Import Mutasi Rekening</h2>
            <form method="POST" action="<?php echo e(route('bank.import')); ?>" enctype="multipart/form-data" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1.5">Rekening Bank <span
                                class="text-red-500">*</span></label>
                        <select name="bank_account_id" required
                            class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                            <option value="">Pilih rekening</option>
                            <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($acc->id); ?>"><?php echo e($acc->bank_name); ?> —
                                    <?php echo e($acc->account_number); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1.5">Format Bank</label>
                        <select name="bank_format" id="bank_format"
                            class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                            <option value="">Auto-Detect</option>
                            <option value="bca">BCA KlikBCA</option>
                            <option value="mandiri">Mandiri CIB</option>
                            <option value="bni">BNI Online</option>
                            <option value="bri">BRI Internet Banking</option>
                            <option value="generic">Generic/Universal</option>
                        </select>
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Kosongkan untuk auto-detect (CSV only)
                        </p>
                    </div>

                    
                    <div class="sm:col-span-2">
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1.5">File Mutasi <span
                                class="text-red-500">*</span></label>

                        
                        <div id="drop-zone"
                            class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-6 text-center cursor-pointer hover:border-blue-500 dark:hover:border-blue-400 transition-colors"
                            onclick="document.getElementById('file-input').click()">

                            <input type="file" name="csv_file" id="file-input"
                                accept=".csv,.txt,.pdf,.jpg,.jpeg,.png" required class="hidden"
                                onchange="handleFileSelect(this)">

                            <div id="drop-zone-content">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                    viewBox="0 0 48 48">
                                    <path
                                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-medium text-blue-600 dark:text-blue-400">Klik untuk upload</span>
                                    atau drag & drop
                                </p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-slate-500">
                                    CSV, TXT, PDF, JPG, PNG (Max 10MB)
                                </p>
                            </div>

                            
                            <div id="file-preview" class="hidden">
                                <div class="flex items-center justify-center gap-3">
                                    <svg id="file-icon" class="h-8 w-8 text-blue-500" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    <div class="text-left">
                                        <p id="file-name" class="text-sm font-medium text-gray-900 dark:text-white">
                                        </p>
                                        <p id="file-size" class="text-xs text-gray-500 dark:text-slate-400"></p>
                                    </div>
                                    <button type="button" onclick="clearFile(event)"
                                        class="ml-4 text-red-500 hover:text-red-700">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div
                    class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-blue-700 dark:text-blue-300 mb-2">Format File yang
                                Didukung:</p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2">
                                <a href="<?php echo e(route('bank.sample', 'bca')); ?>"
                                    class="text-xs px-3 py-2 bg-white dark:bg-white/5 border border-blue-200 dark:border-blue-500/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-500/20 transition text-center">
                                    📄 BCA CSV
                                </a>
                                <a href="<?php echo e(route('bank.sample', 'mandiri')); ?>"
                                    class="text-xs px-3 py-2 bg-white dark:bg-white/5 border border-blue-200 dark:border-blue-500/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-500/20 transition text-center">
                                    📄 Mandiri CSV
                                </a>
                                <div
                                    class="text-xs px-3 py-2 bg-white dark:bg-white/5 border border-blue-200 dark:border-blue-500/20 rounded-lg text-center">
                                    📑 PDF Bank
                                </div>
                                <div
                                    class="text-xs px-3 py-2 bg-white dark:bg-white/5 border border-blue-200 dark:border-blue-500/20 rounded-lg text-center">
                                    🖼️ Screenshot
                                </div>
                                <div
                                    class="text-xs px-3 py-2 bg-white dark:bg-white/5 border border-blue-200 dark:border-blue-500/20 rounded-lg text-center">
                                    📸 Foto Mutasi
                                </div>
                            </div>
                            <p class="text-xs text-blue-600 dark:text-blue-400 mt-2">💡 PDF dan gambar akan diproses
                                dengan AI OCR (Gemini Vision)</p>
                        </div>
                    </div>
                </div>

                
                <div class="flex justify-end">
                    <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Import Mutasi
                    </button>
                </div>
            </form>
        </div>

        
        <?php $unmatchedCount = $statements->where('status','unmatched')->count(); ?>
        <?php if($unmatchedCount > 0): ?>
            <div id="ai-banner"
                class="bg-purple-50 dark:bg-purple-500/10 border border-purple-200 dark:border-purple-500/20 rounded-2xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-purple-500 shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.347.347a3.5 3.5 0 01-4.95 0l-.347-.347z" />
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-purple-700 dark:text-purple-300"><?php echo e($unmatchedCount); ?>

                            transaksi belum dicocokkan</p>
                        <p class="text-xs text-purple-600 dark:text-purple-400">AI mencocokkan berdasarkan jumlah,
                            tanggal, deskripsi, dan referensi</p>
                    </div>
                </div>
                <button id="btn-auto-match" onclick="runAutoMatch()"
                    class="shrink-0 px-4 py-2 bg-purple-600 text-white text-sm rounded-xl hover:bg-purple-700 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Auto-Match AI
                </button>
            </div>
        <?php endif; ?>

        
        <?php $processableCount = $statements->whereIn('status', ['unmatched', 'matched'])->count(); ?>
        <?php if($processableCount > 0): ?>
            <div id="auto-generate-banner"
                class="bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/20 rounded-2xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-indigo-500 shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-indigo-700 dark:text-indigo-300"><?php echo e($processableCount); ?>

                            statements siap di-generate journals</p>
                        <p class="text-xs text-indigo-600 dark:text-indigo-400">AI akan generate dan post journals
                            otomatis di background</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="autoGenerateAllJournals(false)"
                        class="shrink-0 px-4 py-2 bg-indigo-600 text-white text-sm rounded-xl hover:bg-indigo-700 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Generate Draft
                    </button>
                    <button onclick="autoGenerateAllJournals(true)"
                        class="shrink-0 px-4 py-2 bg-green-600 text-white text-sm rounded-xl hover:bg-green-700 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        Generate & Post
                    </button>
                </div>
            </div>

            
            <div id="auto-generate-progress"
                class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
                <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl">
                    <div
                        class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                        <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                            </svg>
                            Processing Journals...
                        </h3>
                    </div>

                    <div class="p-6 space-y-4">
                        
                        <div>
                            <div class="flex justify-between text-sm mb-2">
                                <span id="progress-status"
                                    class="text-gray-600 dark:text-gray-400">Initializing...</span>
                                <span id="progress-percentage"
                                    class="font-medium text-indigo-600 dark:text-indigo-400">0%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                                <div id="progress-bar-main"
                                    class="bg-indigo-600 h-3 rounded-full transition-all duration-500"
                                    style="width: 0%"></div>
                            </div>
                        </div>

                        
                        <div class="grid grid-cols-3 gap-3">
                            <div class="bg-gray-50 dark:bg-white/5 rounded-xl p-3 text-center">
                                <p class="text-xs text-gray-500 dark:text-slate-400">Processed</p>
                                <p id="stat-processed" class="text-2xl font-bold text-gray-900 dark:text-white">0</p>
                            </div>
                            <div class="bg-green-50 dark:bg-green-500/10 rounded-xl p-3 text-center">
                                <p class="text-xs text-green-600 dark:text-green-400">Success</p>
                                <p id="stat-success" class="text-2xl font-bold text-green-600 dark:text-green-400">0
                                </p>
                            </div>
                            <div class="bg-red-50 dark:bg-red-500/10 rounded-xl p-3 text-center">
                                <p class="text-xs text-red-600 dark:text-red-400">Failed</p>
                                <p id="stat-failed" class="text-2xl font-bold text-red-600 dark:text-red-400">0</p>
                            </div>
                        </div>

                        
                        <div class="text-xs text-gray-500 dark:text-slate-400">
                            <p>Job ID: <span id="job-id-display" class="font-mono"></span></p>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10">
                        <button id="btn-view-results" onclick="viewJobResults()"
                            class="hidden w-full py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition">
                            View Results
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div
                class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between flex-wrap gap-2">
                <h2 class="font-semibold text-gray-900 dark:text-white">Mutasi Rekening</h2>
                <div class="flex gap-2 text-xs">
                    <span
                        class="px-2 py-1 bg-green-500/20 text-green-400 rounded-full"><?php echo e($statements->where('status', 'matched')->count()); ?>

                        matched</span>
                    <span
                        class="px-2 py-1 bg-amber-500/20 text-amber-400 rounded-full"><?php echo e($statements->where('status', 'unmatched')->count()); ?>

                        unmatched</span>
                </div>
            </div>

            
            <div id="bulk-toolbar"
                class="hidden px-6 py-3 bg-blue-50 dark:bg-blue-500/10 border-b border-blue-200 dark:border-blue-500/20">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span id="selected-count" class="text-sm font-medium text-blue-700 dark:text-blue-300">0
                            dipilih</span>
                        <button onclick="generateSelectedJournals()"
                            class="px-4 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Generate Journals
                        </button>
                        <button onclick="approveSelectedJournals()"
                            class="px-4 py-1.5 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Approve & Post
                        </button>
                    </div>
                    <button onclick="clearSelection()"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                        Batal
                    </button>
                </div>
                
                <div id="bulk-progress" class="hidden mt-3">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                            style="width: 0%"></div>
                    </div>
                    <p id="progress-text" class="text-xs text-gray-600 dark:text-gray-400 mt-1">Memproses...</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-center w-10">
                                <input type="checkbox" id="select-all" onchange="toggleSelectAll(this)"
                                    class="rounded border-gray-300 dark:border-gray-600">
                            </th>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Rekening</th>
                            <th class="px-4 py-3 text-left">Deskripsi</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Tipe</th>
                            <th class="px-4 py-3 text-right">Jumlah</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-left">AI Match</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__empty_1 = true; $__currentLoopData = $statements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stmt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr id="row-<?php echo e($stmt->id); ?>"
                                class="hover:bg-gray-50 dark:hover:bg-white/5 transition"
                                data-statement-id="<?php echo e($stmt->id); ?>" data-status="<?php echo e($stmt->status); ?>">
                                <td class="px-4 py-3 text-center">
                                    <?php if($stmt->status !== 'journalized'): ?>
                                        <input type="checkbox"
                                            class="stmt-checkbox rounded border-gray-300 dark:border-gray-600"
                                            value="<?php echo e($stmt->id); ?>" onchange="updateBulkToolbar()">
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-slate-400 whitespace-nowrap text-xs">
                                    <?php echo e($stmt->transaction_date->format('d M Y')); ?>

                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-slate-400 text-xs hidden sm:table-cell">
                                    <?php echo e($stmt->bankAccount?->bank_name); ?>

                                </td>
                                <td class="px-4 py-3 text-gray-900 dark:text-white max-w-xs">
                                    <p class="truncate text-sm"><?php echo e($stmt->description); ?></p>
                                    <?php if($stmt->reference): ?>
                                        <p class="text-xs text-gray-400 dark:text-slate-500">Ref:
                                            <?php echo e($stmt->reference); ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 hidden sm:table-cell">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($stmt->type === 'credit' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'); ?>">
                                        <?php echo e($stmt->type === 'credit' ? 'Kredit' : 'Debit'); ?>

                                    </span>
                                </td>
                                <td
                                    class="px-4 py-3 text-right font-medium <?php echo e($stmt->type === 'credit' ? 'text-green-400' : 'text-red-400'); ?>">
                                    Rp <?php echo e(number_format($stmt->amount, 0, ',', '.')); ?>

                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span id="status-<?php echo e($stmt->id); ?>"
                                        class="px-2 py-0.5 rounded-full text-xs font-medium
                                        <?php if($stmt->status === 'matched'): ?> bg-green-500/20 text-green-400
                                        <?php elseif($stmt->status === 'journalized'): ?> bg-blue-500/20 text-blue-400
                                        <?php else: ?> bg-amber-500/20 text-amber-400 <?php endif; ?>">
                                        <?php if($stmt->status === 'matched'): ?>
                                            Matched
                                        <?php elseif($stmt->status === 'journalized'): ?>
                                            Journalized
                                        <?php else: ?>
                                            Unmatched
                                        <?php endif; ?>
                                    </span>
                                </td>
                                
                                <td class="px-4 py-3 min-w-[200px]">
                                    <?php if($stmt->status === 'matched' || $stmt->status === 'journalized'): ?>
                                        <span class="text-xs text-gray-400 dark:text-slate-500">—</span>
                                    <?php else: ?>
                                        <div id="ai-cell-<?php echo e($stmt->id); ?>" class="text-xs text-slate-400 italic">
                                            Menunggu...</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <?php if($stmt->status === 'unmatched' || $stmt->status === 'matched'): ?>
                                            <button onclick="openJournalPreview(<?php echo e($stmt->id); ?>)"
                                                id="btn-generate-<?php echo e($stmt->id); ?>"
                                                class="text-xs text-indigo-400 hover:text-indigo-300 hover:underline flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                Generate
                                            </button>
                                            <button
                                                onclick="openManualMatch(<?php echo e($stmt->id); ?>, '<?php echo e(addslashes($stmt->description)); ?>', <?php echo e($stmt->amount); ?>)"
                                                class="text-xs text-blue-400 hover:underline">Manual</button>
                                        <?php elseif($stmt->status === 'journalized'): ?>
                                            <span class="text-xs text-green-400 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Done
                                            </span>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-600">—</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="9" class="px-6 py-10 text-center text-gray-400 dark:text-slate-500">
                                    Belum ada data mutasi. Import file CSV terlebih dahulu.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10">
                <?php echo e($statements->links()); ?>

            </div>
        </div>
    </div>

    
    <div id="modal-ai-match" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div
                class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.347.347a3.5 3.5 0 01-4.95 0l-.347-.347z" />
                    </svg>
                    AI Match Detail
                </h3>
                <button onclick="document.getElementById('modal-ai-match').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <div id="modal-ai-body" class="p-6">
                <div class="flex items-center justify-center py-8">
                    <svg class="animate-spin w-6 h-6 text-purple-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    
    <div id="modal-manual-match" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div
                class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 class="font-semibold text-gray-900 dark:text-white">Manual Match</h3>
                <button onclick="document.getElementById('modal-manual-match').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <div class="p-6">
                <div
                    class="mb-4 bg-gray-50 dark:bg-white/5 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                    <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Mutasi Bank</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white" id="manual-stmt-desc"></p>
                    <p class="text-sm font-bold text-blue-500 mt-1" id="manual-stmt-amount"></p>
                </div>
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-2 uppercase font-semibold">Pilih Transaksi ERP
                </p>
                <div class="space-y-2 max-h-60 overflow-y-auto" id="manual-erp-list">
                    <?php $__empty_1 = true; $__currentLoopData = $unmatchedErp ?? collect(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $erp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <button onclick="applyManualMatch(<?php echo e($erp['id']); ?>)"
                            class="w-full flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:border-blue-400 dark:hover:border-blue-500/40 transition text-left">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    <?php echo e($erp['number']); ?></p>
                                <p class="text-xs text-gray-400 truncate"><?php echo e($erp['date']); ?> ·
                                    <?php echo e(Str::limit($erp['description'], 50)); ?></p>
                            </div>
                            <span
                                class="text-sm font-medium shrink-0 ml-3 <?php echo e($erp['type'] === 'debit' ? 'text-green-500' : 'text-red-500'); ?>">
                                Rp <?php echo e(number_format($erp['amount'], 0, ',', '.')); ?>

                            </span>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-gray-400 dark:text-slate-500 text-center py-4">Tidak ada transaksi ERP
                            yang tersedia. Pastikan sudah ada jurnal yang diposting.</p>
                    <?php endif; ?>
                </div>
                <form id="form-manual-match" method="POST" class="mt-4 hidden">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="transaction_id" id="manual-tx-id">
                </form>
            </div>
        </div>
    </div>

    
    <div id="modal-journal-preview"
        class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-3xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div
                class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b] z-10">
                <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Preview Journal Entry
                </h3>
                <button onclick="closeJournalModal()"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>

            <div id="modal-journal-body" class="p-6">
                <!-- Loading State -->
                <div id="journal-loading" class="flex items-center justify-center py-12">
                    <div class="text-center">
                        <svg class="animate-spin w-10 h-10 text-indigo-500 mx-auto mb-3" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                        </svg>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Generating journal with AI...</p>
                    </div>
                </div>

                <!-- Preview Content -->
                <div id="journal-content" class="hidden space-y-4">
                    <!-- Statement Info -->
                    <div class="bg-gray-50 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-slate-400">Tanggal</p>
                                <p class="font-medium text-gray-900 dark:text-white" id="journal-date"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-slate-400">Jumlah</p>
                                <p class="font-bold text-blue-500" id="journal-amount"></p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-xs text-gray-500 dark:text-slate-400">Deskripsi</p>
                                <p class="font-medium text-gray-900 dark:text-white" id="journal-description"></p>
                            </div>
                        </div>
                    </div>

                    <!-- AI Confidence Badge -->
                    <div class="flex items-center gap-3">
                        <span id="journal-confidence"
                            class="px-3 py-1 rounded-full text-sm font-medium border"></span>
                        <p class="text-xs text-gray-600 dark:text-gray-400" id="journal-ai-basis"></p>
                    </div>

                    <!-- Warnings -->
                    <div id="journal-warnings"
                        class="hidden bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl p-4">
                        <p class="text-xs font-semibold text-amber-600 dark:text-amber-400 mb-2">⚠️ Peringatan:</p>
                        <ul id="warning-list" class="space-y-1 text-sm text-amber-700 dark:text-amber-300"></ul>
                    </div>

                    <!-- Journal Lines (Editable) -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Journal Lines</p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">Klik account untuk edit</p>
                        </div>
                        <div id="journal-lines" class="space-y-2"></div>
                    </div>

                    <!-- Balance Check -->
                    <div id="balance-check"
                        class="bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-sm font-medium text-green-700 dark:text-green-300">Journal
                                    Balance</span>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500 dark:text-slate-400">Total Debit = Total Credit</p>
                                <p class="text-lg font-bold text-green-600 dark:text-green-400" id="balance-amount">
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div id="journal-footer"
                class="hidden px-6 py-4 border-t border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 rounded-b-2xl">
                <div class="flex items-center justify-end gap-3">
                    <button onclick="closeJournalModal()"
                        class="px-4 py-2 border border-gray-200 dark:border-white/10 rounded-xl text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 transition">
                        Batal
                    </button>
                    <button onclick="regenerateJournal()"
                        class="px-4 py-2 border border-indigo-200 dark:border-indigo-500/20 rounded-xl text-sm text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition">
                        Regenerate
                    </button>
                    <button id="btn-approve-post" onclick="approveAndPostJournal()"
                        class="px-6 py-2 bg-green-600 text-white rounded-xl text-sm font-medium hover:bg-green-700 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        Approve & Post
                    </button>
                </div>
            </div>
        </div>
    </div>

    
    <div id="toast-container" class="fixed top-4 right-4 z-[100] space-y-2"></div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            let aiResults = {};
            let currentManualStmtId = null;

            function openManualMatch(stmtId, desc, amount) {
                currentManualStmtId = stmtId;
                document.getElementById('manual-stmt-desc').textContent = desc;
                document.getElementById('manual-stmt-amount').textContent = 'Rp ' + parseInt(amount).toLocaleString('id-ID');
                document.getElementById('form-manual-match').action = '<?php echo e(url('bank/statements')); ?>/' + stmtId + '/match';
                document.getElementById('modal-manual-match').classList.remove('hidden');
            }

            function applyManualMatch(txId) {
                if (!currentManualStmtId) return;
                document.getElementById('manual-tx-id').value = txId;
                document.getElementById('form-manual-match').submit();
            }

            // ── Auto-match all unmatched ──────────────────────────────────────
            async function runAutoMatch() {
                const btn = document.getElementById('btn-auto-match');
                btn.disabled = true;
                btn.innerHTML =
                    `<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> Memproses...`;

                document.querySelectorAll('[id^="ai-cell-"]').forEach(el => {
                    el.innerHTML = '<span class="animate-pulse text-slate-400">Menganalisis...</span>';
                });

                try {
                    const res = await fetch('<?php echo e(route('bank.ai.match-all')); ?>');
                    aiResults = await res.json();
                    let autoApplied = 0;

                    for (const [id, result] of Object.entries(aiResults)) {
                        renderCell(id, result);
                        if (result.status === 'matched' && result.confidence >= 85 && result.transaction) {
                            await applyMatch(id, result.transaction.id, true);
                            autoApplied++;
                        }
                    }

                    btn.disabled = false;
                    btn.innerHTML =
                        `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Selesai (${autoApplied} auto-applied)`;
                    btn.classList.replace('bg-purple-600', 'bg-green-600');
                    btn.classList.replace('hover:bg-purple-700', 'hover:bg-green-700');
                } catch (e) {
                    btn.disabled = false;
                    btn.innerHTML = 'Auto-Match AI';
                }
            }

            function renderCell(id, result) {
                const cell = document.getElementById('ai-cell-' + id);
                const detailBtn = document.getElementById('btn-detail-' + id);
                if (!cell) return;

                if (result.status === 'matched') {
                    const tx = result.transaction;
                    cell.innerHTML =
                        `
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="px-2 py-0.5 rounded-full text-xs bg-green-500/20 text-green-400 border border-green-500/20 shrink-0">✓ ${result.confidence}%</span>
                    <span class="text-gray-700 dark:text-slate-300 truncate max-w-[130px] text-xs">${tx.number ?? tx.description ?? ''}</span>
                </div>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5 leading-tight">${result.reasons.slice(0,2).join(' · ')}</p>`;
                } else if (result.status === 'suggestion') {
                    const tx = result.transaction;
                    cell.innerHTML =
                        `
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="px-2 py-0.5 rounded-full text-xs bg-amber-500/20 text-amber-400 border border-amber-500/20 shrink-0">~ ${result.confidence}%</span>
                    <span class="text-gray-700 dark:text-slate-300 truncate max-w-[130px] text-xs">${tx.number ?? tx.description ?? ''}</span>
                </div>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5 leading-tight">${result.reasons.slice(0,2).join(' · ')}</p>`;
                } else {
                    const flag = result.flags?.[0] ?? 'Tidak ditemukan kecocokan';
                    cell.innerHTML = `
                <span class="px-2 py-0.5 rounded-full text-xs bg-red-500/20 text-red-400 border border-red-500/20">✗ Tidak cocok</span>
                <p class="text-xs text-red-400/80 mt-0.5 leading-tight">${flag}</p>`;
                }

                if (detailBtn) detailBtn.classList.remove('hidden');
            }

            // ── Apply match ───────────────────────────────────────────────────
            async function applyMatch(stmtId, txId, silent = false) {
                try {
                    await fetch('<?php echo e(url('bank/ai/apply-match')); ?>/' + stmtId, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            transaction_id: txId
                        }),
                    });
                    if (!silent) {
                        const statusEl = document.getElementById('status-' + stmtId);
                        if (statusEl) {
                            statusEl.textContent = 'Matched';
                            statusEl.className =
                                'px-2 py-0.5 rounded-full text-xs font-medium bg-green-500/20 text-green-400';
                        }
                        document.getElementById('modal-ai-match').classList.add('hidden');
                    }
                } catch (e) {}
            }

            // ── Modal detail ──────────────────────────────────────────────────
            async function openMatchModal(id) {
                document.getElementById('modal-ai-match').classList.remove('hidden');

                const cached = aiResults[id];
                if (cached) {
                    renderModal(id, cached);
                    return;
                }

                document.getElementById('modal-ai-body').innerHTML = `
            <div class="flex items-center justify-center py-8">
                <svg class="animate-spin w-6 h-6 text-purple-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
            </div>`;

                try {
                    const res = await fetch('<?php echo e(url('bank/ai/match')); ?>/' + id);
                    const data = await res.json();
                    aiResults[id] = data;
                    renderCell(id, data);
                    renderModal(id, data);
                } catch (e) {
                    document.getElementById('modal-ai-body').innerHTML =
                        '<p class="text-red-400 text-sm p-4">Gagal memuat data AI.</p>';
                }
            }

            function renderModal(id, result) {
                const tierBadge = {
                    high: 'bg-green-500/20 text-green-400 border-green-500/20',
                    medium: 'bg-amber-500/20 text-amber-400 border-amber-500/20',
                    none: 'bg-red-500/20 text-red-400 border-red-500/20',
                };
                const tierLabel = {
                    high: 'Confidence Tinggi',
                    medium: 'Perlu Konfirmasi',
                    none: 'Tidak Cocok'
                };

                let txBlock = '';
                if (result.transaction) {
                    const tx = result.transaction;
                    const canApply = result.status !== 'unmatched';
                    txBlock = `
                <div class="bg-gray-50 dark:bg-white/5 rounded-xl p-4 mb-4 border border-gray-200 dark:border-white/10">
                    <p class="text-xs text-gray-500 dark:text-slate-400 mb-2 uppercase font-semibold">Kandidat Terbaik</p>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div><p class="text-xs text-gray-400">No. Transaksi</p><p class="font-medium text-gray-900 dark:text-white">${tx.number ?? '—'}</p></div>
                        <div><p class="text-xs text-gray-400">Tanggal</p><p class="font-medium text-gray-900 dark:text-white">${tx.date ?? '—'}</p></div>
                        <div><p class="text-xs text-gray-400">Jumlah</p><p class="font-medium text-gray-900 dark:text-white">Rp ${Number(tx.amount).toLocaleString('id-ID')}</p></div>
                        <div><p class="text-xs text-gray-400">Tipe</p><p class="font-medium text-gray-900 dark:text-white">${tx.type ?? '—'}</p></div>
                        <div class="col-span-2"><p class="text-xs text-gray-400">Deskripsi</p><p class="font-medium text-gray-900 dark:text-white">${tx.description ?? '—'}</p></div>
                    </div>
                    ${canApply ? `<button onclick="applyMatch(${id}, ${tx.id})"
                                                        class="mt-3 w-full py-2 bg-green-600 text-white text-sm rounded-xl hover:bg-green-700 transition font-medium">
                                                        ✓ Terapkan Match Ini
                                                    </button>` : ''}
                </div>`;
                }

                const reasonsBlock = result.reasons?.length ? `
            <div class="mb-4">
                <p class="text-xs text-gray-500 dark:text-slate-400 uppercase font-semibold mb-2">Alasan Kecocokan</p>
                <ul class="space-y-1">
                    ${result.reasons.map(r => `<li class="flex items-center gap-2 text-sm text-gray-700 dark:text-slate-300"><span class="text-green-400">✓</span>${r}</li>`).join('')}
                </ul>
            </div>` : '';

                const flagsBlock = (result.flags?.length || result.explanation) ? `
            <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-xl p-4 mb-4">
                <p class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase mb-2">Alasan Tidak Cocok</p>
                ${(result.flags ?? []).map(f => `<p class="text-sm text-red-700 dark:text-red-300 flex items-start gap-2 mb-1"><span class="shrink-0">⚠</span>${f}</p>`).join('')}
                ${result.explanation ? `<p class="text-sm text-red-600 dark:text-red-400 mt-2 italic">${result.explanation}</p>` : ''}
            </div>` : '';

                const altBlock = result.alternatives?.length ? `
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400 uppercase font-semibold mb-2">Alternatif Lain</p>
                <div class="space-y-2">
                    ${result.alternatives.map(alt => `
                                                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                                                        <div class="text-sm min-w-0 flex-1 mr-3">
                                                            <p class="font-medium text-gray-900 dark:text-white truncate">${alt.number ?? alt.description ?? '—'}</p>
                                                            <p class="text-xs text-gray-400">${alt.date} · Rp ${Number(alt.amount).toLocaleString('id-ID')}</p>
                                                        </div>
                                                        <div class="flex items-center gap-2 shrink-0">
                                                            <span class="text-xs text-amber-400">${alt.score}%</span>
                                                            <button onclick="applyMatch(${id}, ${alt.id})"
                                                                class="text-xs px-2 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Pilih</button>
                                                        </div>
                                                    </div>`).join('')}
                </div>
            </div>` : '';

                document.getElementById('modal-ai-body').innerHTML = `
            <div class="flex items-center gap-3 mb-5">
                <span class="px-3 py-1 rounded-full text-sm border ${tierBadge[result.tier] ?? tierBadge.none}">
                    ${result.confidence}% — ${tierLabel[result.tier] ?? 'Tidak Diketahui'}
                </span>
            </div>
            ${txBlock}${reasonsBlock}${flagsBlock}${altBlock}`;
            }
        </script>

        
        <script>
            let currentJournalStmtId = null;
            let currentJournalPreview = null;

            // ── Toast Notifications ──────────────────────────────────────
            function showToast(message, type = 'success', duration = 3000) {
                const container = document.getElementById('toast-container');
                const toast = document.createElement('div');

                const colors = {
                    success: 'bg-green-500',
                    error: 'bg-red-500',
                    warning: 'bg-amber-500',
                    info: 'bg-blue-500'
                };

                const icons = {
                    success: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
                    error: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
                    warning: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
                    info: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                };

                toast.className =
                    `${colors[type]} text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 min-w-[300px] animate-slide-in`;
                toast.innerHTML = `
                    ${icons[type]}
                    <p class="text-sm flex-1">${message}</p>
                    <button onclick="this.parentElement.remove()" class="text-white/80 hover:text-white">✕</button>
                `;

                container.appendChild(toast);

                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transition = 'opacity 0.3s';
                    setTimeout(() => toast.remove(), 300);
                }, duration);
            }

            // ── Bulk Selection ──────────────────────────────────────────
            function toggleSelectAll(checkbox) {
                const checkboxes = document.querySelectorAll('.stmt-checkbox:not([disabled])');
                checkboxes.forEach(cb => cb.checked = checkbox.checked);
                updateBulkToolbar();
            }

            function updateBulkToolbar() {
                const checkboxes = document.querySelectorAll('.stmt-checkbox:checked');
                const toolbar = document.getElementById('bulk-toolbar');
                const count = checkboxes.length;

                document.getElementById('selected-count').textContent = `${count} dipilih`;

                if (count > 0) {
                    toolbar.classList.remove('hidden');
                } else {
                    toolbar.classList.add('hidden');
                }
            }

            function clearSelection() {
                document.querySelectorAll('.stmt-checkbox').forEach(cb => cb.checked = false);
                document.getElementById('select-all').checked = false;
                updateBulkToolbar();
            }

            function getSelectedStatementIds() {
                return Array.from(document.querySelectorAll('.stmt-checkbox:checked'))
                    .map(cb => parseInt(cb.value));
            }

            // ── Journal Preview Modal ───────────────────────────────────
            async function openJournalPreview(stmtId) {
                currentJournalStmtId = stmtId;
                document.getElementById('modal-journal-preview').classList.remove('hidden');
                document.getElementById('journal-loading').classList.remove('hidden');
                document.getElementById('journal-content').classList.add('hidden');
                document.getElementById('journal-footer').classList.add('hidden');

                try {
                    const res = await fetch(`<?php echo e(url('bank/ai/preview-journal')); ?>/${stmtId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await res.json();

                    if (!data.success) {
                        showToast(data.message || 'Gagal generate preview', 'error');
                        closeJournalModal();
                        return;
                    }

                    renderJournalPreview(data.preview);

                } catch (e) {
                    showToast('Gagal memuat preview: ' + e.message, 'error');
                    closeJournalModal();
                }
            }

            function renderJournalPreview(preview) {
                currentJournalPreview = preview;

                // Hide loading, show content
                document.getElementById('journal-loading').classList.add('hidden');
                document.getElementById('journal-content').classList.remove('hidden');
                document.getElementById('journal-footer').classList.remove('hidden');

                // Fill statement info
                document.getElementById('journal-date').textContent = preview.date;
                document.getElementById('journal-amount').textContent = 'Rp ' + Number(preview.total_debit).toLocaleString(
                    'id-ID');
                document.getElementById('journal-description').textContent = preview.description;

                // AI Confidence
                const confidenceEl = document.getElementById('journal-confidence');
                const confColors = {
                    high: 'bg-green-500/20 text-green-400 border-green-500/20',
                    medium: 'bg-amber-500/20 text-amber-400 border-amber-500/20',
                    low: 'bg-red-500/20 text-red-400 border-red-500/20'
                };
                const confLabels = {
                    high: '✓ High Confidence',
                    medium: '~ Medium Confidence',
                    low: '✗ Low Confidence'
                };
                confidenceEl.className = `px-3 py-1 rounded-full text-sm font-medium border ${confColors[preview.confidence]}`;
                confidenceEl.textContent = confLabels[preview.confidence];
                document.getElementById('journal-ai-basis').textContent = preview.ai_basis;

                // Warnings
                if (preview.warnings && preview.warnings.length > 0) {
                    document.getElementById('journal-warnings').classList.remove('hidden');
                    const warningList = document.getElementById('warning-list');
                    warningList.innerHTML = preview.warnings.map(w => `<li>⚠ ${w}</li>`).join('');
                } else {
                    document.getElementById('journal-warnings').classList.add('hidden');
                }

                // Journal Lines
                renderJournalLines(preview.lines);

                // Balance check
                const balanceEl = document.getElementById('balance-amount');
                balanceEl.textContent = 'Rp ' + Number(preview.total_debit).toLocaleString('id-ID');

                if (!preview.is_balanced) {
                    document.getElementById('balance-check').className =
                        'bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-xl p-4';
                    document.getElementById('balance-check').querySelector('span').textContent = 'Journal TIDAK Balance!';
                    document.getElementById('balance-check').querySelector('span').className =
                        'text-sm font-medium text-red-700 dark:text-red-300';
                } else {
                    document.getElementById('balance-check').className =
                        'bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl p-4';
                    document.getElementById('balance-check').querySelector('span').textContent = 'Journal Balance';
                    document.getElementById('balance-check').querySelector('span').className =
                        'text-sm font-medium text-green-700 dark:text-green-300';
                }
            }

            function renderJournalLines(lines) {
                const container = document.getElementById('journal-lines');
                container.innerHTML = lines.map((line, index) => `
                    <div class="grid grid-cols-12 gap-2 p-3 bg-gray-50 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-white/10" data-line-index="${index}">
                        <div class="col-span-5">
                            <label class="text-xs text-gray-500 dark:text-slate-400">Account</label>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">${line.account_code} - ${line.account_name}</p>
                        </div>
                        <div class="col-span-3">
                            <label class="text-xs text-gray-500 dark:text-slate-400">Debit</label>
                            <p class="text-sm font-mono ${line.debit > 0 ? 'text-green-500' : 'text-gray-400'}">
                                ${line.debit > 0 ? 'Rp ' + Number(line.debit).toLocaleString('id-ID') : '-'}
                            </p>
                        </div>
                        <div class="col-span-3">
                            <label class="text-xs text-gray-500 dark:text-slate-400">Credit</label>
                            <p class="text-sm font-mono ${line.credit > 0 ? 'text-red-500' : 'text-gray-400'}">
                                ${line.credit > 0 ? 'Rp ' + Number(line.credit).toLocaleString('id-ID') : '-'}
                            </p>
                        </div>
                        <div class="col-span-1 flex items-end">
                            <button onclick="editJournalLine(${index})" class="text-xs text-blue-400 hover:text-blue-300">Edit</button>
                        </div>
                    </div>
                `).join('');
            }

            function closeJournalModal() {
                document.getElementById('modal-journal-preview').classList.add('hidden');
                currentJournalStmtId = null;
                currentJournalPreview = null;
            }

            async function regenerateJournal() {
                if (!currentJournalStmtId) return;
                await openJournalPreview(currentJournalStmtId);
            }

            async function approveAndPostJournal() {
                if (!currentJournalStmtId) return;

                const btn = document.getElementById('btn-approve-post');
                btn.disabled = true;
                btn.innerHTML = `
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    Processing...
                `;

                try {
                    const res = await fetch(`<?php echo e(url('bank/ai/approve-and-post')); ?>/${currentJournalStmtId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await res.json();

                    if (data.success) {
                        showToast('Journal berhasil di-post! (No: ' + (data.journal_number || '-') + ')', 'success');
                        closeJournalModal();
                        updateRowStatus(currentJournalStmtId, 'journalized');
                    } else {
                        showToast(data.message || 'Gagal approve & post', 'error');
                    }

                } catch (e) {
                    showToast('Gagal approve & post: ' + e.message, 'error');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = `
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Approve & Post
                    `;
                }
            }

            function updateRowStatus(stmtId, status) {
                const statusEl = document.getElementById('status-' + stmtId);
                const row = document.getElementById('row-' + stmtId);
                const actionsCell = row.querySelector('td:last-child');
                const checkbox = row.querySelector('.stmt-checkbox');

                if (statusEl) {
                    const colors = {
                        journalized: 'bg-blue-500/20 text-blue-400',
                        matched: 'bg-green-500/20 text-green-400',
                        unmatched: 'bg-amber-500/20 text-amber-400'
                    };
                    const labels = {
                        journalized: 'Journalized',
                        matched: 'Matched',
                        unmatched: 'Unmatched'
                    };
                    statusEl.className = `px-2 py-0.5 rounded-full text-xs font-medium ${colors[status]}`;
                    statusEl.textContent = labels[status];
                }

                // Update actions
                if (status === 'journalized') {
                    if (actionsCell) {
                        actionsCell.innerHTML = `
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-xs text-green-400 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Done
                                </span>
                            </div>
                        `;
                    }
                    if (checkbox) {
                        checkbox.disabled = true;
                        checkbox.checked = false;
                    }
                }
            }

            // ── Bulk Actions ─────────────────────────────────────────────
            async function generateSelectedJournals() {
                const statementIds = getSelectedStatementIds();
                if (statementIds.length === 0) {
                    showToast('Pilih minimal 1 statement', 'warning');
                    return;
                }

                const progressDiv = document.getElementById('bulk-progress');
                const progressBar = document.getElementById('progress-bar');
                const progressText = document.getElementById('progress-text');

                progressDiv.classList.remove('hidden');
                progressBar.style.width = '0%';
                progressText.textContent = `Memproses 0/${statementIds.length}...`;

                let success = 0;
                let failed = 0;

                for (let i = 0; i < statementIds.length; i++) {
                    try {
                        const res = await fetch(`<?php echo e(url('bank/ai/generate-journal')); ?>/${statementIds[i]}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        });

                        const data = await res.json();
                        if (data.success) {
                            success++;
                            updateRowStatus(statementIds[i], 'journalized');
                        } else {
                            failed++;
                        }
                    } catch (e) {
                        failed++;
                    }

                    // Update progress
                    const percent = ((i + 1) / statementIds.length * 100).toFixed(0);
                    progressBar.style.width = percent + '%';
                    progressText.textContent = `Memproses ${i + 1}/${statementIds.length}...`;
                }

                progressText.textContent = `Selesai: ${success} berhasil, ${failed} gagal`;
                showToast(`Bulk generate selesai: ${success} success, ${failed} failed`, success > 0 ? 'success' : 'error');

                setTimeout(() => {
                    progressDiv.classList.add('hidden');
                    clearSelection();
                }, 2000);
            }

            async function approveSelectedJournals() {
                const statementIds = getSelectedStatementIds();
                if (statementIds.length === 0) {
                    showToast('Pilih minimal 1 statement', 'warning');
                    return;
                }

                const progressDiv = document.getElementById('bulk-progress');
                const progressBar = document.getElementById('progress-bar');
                const progressText = document.getElementById('progress-text');

                progressDiv.classList.remove('hidden');
                progressBar.style.width = '0%';
                progressText.textContent = `Memproses 0/${statementIds.length}...`;

                try {
                    const res = await fetch(`<?php echo e(url('bank/ai/approve-and-post/bulk')); ?>`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            statement_ids: statementIds
                        })
                    });

                    const data = await res.json();

                    if (data.success) {
                        // Update all rows
                        statementIds.forEach(id => updateRowStatus(id, 'journalized'));
                        showToast(`Bulk approve selesai: ${data.success_count} berhasil`, 'success');
                    } else {
                        showToast(data.message || 'Gagal bulk approve', 'error');
                    }

                } catch (e) {
                    showToast('Gagal bulk approve: ' + e.message, 'error');
                } finally {
                    progressBar.style.width = '100%';
                    progressText.textContent = 'Selesai!';
                    setTimeout(() => {
                        progressDiv.classList.add('hidden');
                        clearSelection();
                    }, 2000);
                }
            }

            function editJournalLine(index) {
                showToast('Edit account feature coming soon!', 'info');
                // Future: Open account selector modal
            }

            // ── Auto-Generate All Journals ─────────────────────────────
            let currentJobId = null;
            let progressPolling = null;

            async function autoGenerateAllJournals(autoPost = false) {
                const action = autoPost ? 'Generate & Post' : 'Generate Draft';

                if (!confirm(
                        `Auto ${action} semua journals?\n\nIni akan memproses semua unmatched/matched statements di background.`
                    )) {
                    return;
                }

                try {
                    const res = await fetch(`<?php echo e(url('bank/ai/auto-generate-all')); ?>`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            auto_post: autoPost
                        })
                    });

                    const data = await res.json();

                    if (data.success) {
                        currentJobId = data.job_id;
                        showToast(`Background job dimulai: ${data.total_statements} statements`, 'success');

                        // Show progress modal
                        showProgressModal(data.job_id);

                        // Start polling progress
                        startProgressPolling(data.job_id);
                    } else {
                        showToast(data.message || 'Gagal memulai job', 'error');
                    }

                } catch (e) {
                    showToast('Gagal: ' + e.message, 'error');
                }
            }

            function showProgressModal(jobId) {
                document.getElementById('auto-generate-progress').classList.remove('hidden');
                document.getElementById('job-id-display').textContent = jobId;
                document.getElementById('btn-view-results').classList.add('hidden');

                // Reset stats
                document.getElementById('stat-processed').textContent = '0';
                document.getElementById('stat-success').textContent = '0';
                document.getElementById('stat-failed').textContent = '0';
                document.getElementById('progress-bar-main').style.width = '0%';
                document.getElementById('progress-percentage').textContent = '0%';
                document.getElementById('progress-status').textContent = 'Initializing...';
            }

            function startProgressPolling(jobId) {
                // Poll setiap 2 detik
                progressPolling = setInterval(async () => {
                    try {
                        const res = await fetch(`<?php echo e(url('bank/ai/job-progress')); ?>/${jobId}`);
                        const data = await res.json();

                        if (data.success) {
                            const progress = data.progress;
                            updateProgressUI(progress);

                            // If completed, stop polling and show results button
                            if (progress.status === 'completed' || progress.status === 'completed_with_errors' ||
                                progress.status === 'failed') {
                                stopProgressPolling();
                                document.getElementById('btn-view-results').classList.remove('hidden');

                                // Auto reload page setelah 3 detik
                                setTimeout(() => {
                                    window.location.reload();
                                }, 3000);
                            }
                        }
                    } catch (e) {
                        console.error('Progress polling error:', e);
                    }
                }, 2000);
            }

            function stopProgressPolling() {
                if (progressPolling) {
                    clearInterval(progressPolling);
                    progressPolling = null;
                }
            }

            function updateProgressUI(progress) {
                document.getElementById('progress-status').textContent = progress.message;
                document.getElementById('progress-percentage').textContent = progress.percentage + '%';
                document.getElementById('progress-bar-main').style.width = progress.percentage + '%';
                document.getElementById('stat-processed').textContent = progress.processed;

                // Update stats dari results jika available
                const results = JSON.parse(localStorage.getItem(`job_results_${currentJobId}`) || '{}');
                if (results.summary) {
                    document.getElementById('stat-success').textContent = results.summary.success || 0;
                    document.getElementById('stat-failed').textContent = results.summary.failed || 0;
                }
            }

            async function viewJobResults() {
                if (!currentJobId) return;

                try {
                    const res = await fetch(`<?php echo e(url('bank/ai/job-results')); ?>/${currentJobId}`);
                    const data = await res.json();

                    if (data.success) {
                        const results = data.results;

                        // Save to localStorage untuk akses nanti
                        localStorage.setItem(`job_results_${currentJobId}`, JSON.stringify(results));

                        // Build summary message
                        let message = `Job Completed!\n\n`;
                        message += `Total: ${results.summary.total}\n`;
                        message += `✓ Success: ${results.summary.success}\n`;
                        message += `✗ Failed: ${results.summary.failed}\n`;

                        if (results.errors && results.errors.length > 0) {
                            message += `\nErrors:\n`;
                            results.errors.slice(0, 3).forEach(err => {
                                message += `- Statement #${err.statement_id}: ${err.error}\n`;
                            });
                            if (results.errors.length > 3) {
                                message += `... dan ${results.errors.length - 3} errors lainnya\n`;
                            }
                        }

                        alert(message);

                        // Cleanup
                        await fetch(`<?php echo e(url('bank/ai/job-cleanup')); ?>/${currentJobId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });

                        // Close modal
                        document.getElementById('auto-generate-progress').classList.add('hidden');

                        // Reload page
                        window.location.reload();
                    }

                } catch (e) {
                    showToast('Gagal load results: ' + e.message, 'error');
                }
            }

            // ── Drag & Drop File Upload ─────────────────────────────
            const dropZone = document.getElementById('drop-zone');
            const fileInput = document.getElementById('file-input');
            const dropZoneContent = document.getElementById('drop-zone-content');
            const filePreview = document.getElementById('file-preview');

            if (dropZone && fileInput) {
                // Prevent default drag behaviors
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, preventDefaults, false);
                    document.body.addEventListener(eventName, preventDefaults, false);
                });

                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                // Highlight drop zone when dragging over it
                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZone.addEventListener(eventName, highlight, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, unhighlight, false);
                });

                function highlight(e) {
                    dropZone.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-500/10');
                }

                function unhighlight(e) {
                    dropZone.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-500/10');
                }

                // Handle dropped files
                dropZone.addEventListener('drop', handleDrop, false);

                function handleDrop(e) {
                    const dt = e.dataTransfer;
                    const files = dt.files;

                    if (files.length > 0) {
                        fileInput.files = files;
                        handleFileSelect(fileInput);
                    }
                }

                function handleFileSelect(input) {
                    const file = input.files[0];
                    if (!file) return;

                    // Validate file size (10MB)
                    if (file.size > 10 * 1024 * 1024) {
                        showToast('Ukuran file terlalu besar. Maksimal 10MB', 'error');
                        input.value = '';
                        return;
                    }

                    // Show file preview
                    dropZoneContent.classList.add('hidden');
                    filePreview.classList.remove('hidden');

                    document.getElementById('file-name').textContent = file.name;
                    document.getElementById('file-size').textContent = formatFileSize(file.size);

                    // Update icon based on file type
                    const fileIcon = document.getElementById('file-icon');
                    const extension = file.name.split('.').pop().toLowerCase();

                    if (['pdf'].includes(extension)) {
                        fileIcon.innerHTML =
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />';
                        fileIcon.classList.remove('text-blue-500');
                        fileIcon.classList.add('text-red-500');
                    } else if (['jpg', 'jpeg', 'png'].includes(extension)) {
                        fileIcon.innerHTML =
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />';
                        fileIcon.classList.remove('text-blue-500');
                        fileIcon.classList.add('text-green-500');
                    } else {
                        fileIcon.innerHTML =
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />';
                        fileIcon.classList.remove('text-red-500', 'text-green-500');
                        fileIcon.classList.add('text-blue-500');
                    }

                    showToast(`File "${file.name}" siap diupload`, 'success');
                }

                function clearFile(e) {
                    e.stopPropagation();
                    fileInput.value = '';
                    dropZoneContent.classList.remove('hidden');
                    filePreview.classList.add('hidden');
                }

                function formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
                }

                // Make clearFile globally accessible
                window.clearFile = clearFile;
                window.handleFileSelect = handleFileSelect;
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/bank/reconciliation.blade.php ENDPATH**/ ?>