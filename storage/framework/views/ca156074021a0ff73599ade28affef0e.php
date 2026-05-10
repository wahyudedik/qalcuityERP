

<?php
    $normalizedActions = $getNormalizedActions();
?>

<div <?php echo e($attributes->merge(['class' => 'bg-white rounded-2xl border border-gray-200 overflow-hidden'])); ?>

    x-data="quickActionsWidget({
        actions: <?php echo e(json_encode($normalizedActions)); ?>,
        shortcuts: <?php echo e($getShortcutsJson()); ?>,
        loading: <?php echo e($loading ? 'true' : 'false'); ?>,
        error: <?php echo e($error ? 'true' : 'false'); ?>

    })" role="region" aria-label="<?php echo e($getAriaLabel()); ?>">

    
    <?php if($title): ?>
        <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700"><?php echo e($title); ?></h3>
        </div>
    <?php endif; ?>

    
    <div x-show="loading" x-cloak class="p-4" aria-hidden="true">
        <div class="grid <?php echo e($gridClasses); ?> gap-3">
            <?php for($i = 0; $i < max(count($normalizedActions), 3); $i++): ?>
                <div class="animate-pulse">
                    <div class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gray-50">
                        <div class="w-10 h-10 bg-gray-200 rounded-lg"></div>
                        <div class="h-3 bg-gray-200 rounded w-16"></div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    
    <div x-show="error && !loading" x-cloak class="p-6 text-center" role="alert" aria-live="assertive">
        <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z">
            </path>
        </svg>
        <p class="text-sm text-gray-500 mb-3"><?php echo e($getErrorMessage()); ?></p>
        <button type="button" x-on:click="retry()"
            class="text-blue-600 text-sm hover:underline focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded px-2 py-1">
            Coba Lagi
        </button>
    </div>

    
    <div x-show="!loading && !error" class="p-4">
        <?php if(count($normalizedActions) > 0): ?>
            <div class="grid <?php echo e($gridClasses); ?> gap-3" role="toolbar" aria-label="Tombol aksi cepat">
                <?php $__currentLoopData = $normalizedActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button type="button" x-on:click="executeAction(<?php echo e($index); ?>)"
                        :disabled="actionStates[<?php echo e($index); ?>]?.loading"
                        class="group relative flex flex-col items-center gap-2 p-4 rounded-xl bg-gray-50/50 border border-transparent hover:bg-blue-50 hover:border-blue-200 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        aria-label="<?php echo e($action['label']); ?><?php echo e($action['shortcut'] ? ' (' . $action['shortcut'] . ')' : ''); ?>"
                        <?php if($action['shortcut']): ?> title="<?php echo e($action['label']); ?> (<?php echo e($action['shortcut']); ?>)"
                        <?php else: ?>
                            title="<?php echo e($action['label']); ?>" <?php endif; ?>>
                        
                        <div x-show="actionStates[<?php echo e($index); ?>]?.loading" x-cloak
                            class="absolute inset-0 flex items-center justify-center rounded-xl bg-white/80"
                            aria-hidden="true">
                            <svg class="w-5 h-5 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </div>

                        
                        <div
                            class="flex-shrink-0 w-10 h-10 rounded-lg bg-blue-50 group-hover:bg-blue-100 flex items-center justify-center transition-colors">
                            <?php if($action['icon']): ?>
                                <?php echo $__env->make('components.widget.partials.stat-icon', [
                                    'icon' => $action['icon'],
                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php else: ?>
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            <?php endif; ?>
                        </div>

                        
                        <span
                            class="text-xs font-medium text-gray-700 group-hover:text-blue-700 text-center leading-tight transition-colors">
                            <?php echo e($action['label']); ?>

                        </span>

                        
                        <?php if($action['shortcut']): ?>
                            <span
                                class="absolute top-1.5 right-1.5 text-[10px] font-mono text-gray-400 bg-gray-100 px-1 py-0.5 rounded hidden sm:inline-block"
                                aria-hidden="true">
                                <?php echo e($action['shortcut']); ?>

                            </span>
                        <?php endif; ?>
                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div class="text-center py-6">
                <p class="text-sm text-gray-500">Tidak ada aksi tersedia</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('4e374b64-4ad8-4d3c-8652-fa8d1d41ab34')): $__env->markAsRenderedOnce('4e374b64-4ad8-4d3c-8652-fa8d1d41ab34'); ?>
    <?php $__env->startPush('scripts'); ?>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('quickActionsWidget', (config = {}) => ({
                    actions: config.actions || [],
                    shortcuts: config.shortcuts || [],
                    loading: config.loading || false,
                    error: config.error || false,
                    actionStates: {},

                    init() {
                        // Initialize action states
                        this.actions.forEach((action, index) => {
                            this.actionStates[index] = {
                                loading: false,
                                success: false,
                                error: false
                            };
                        });

                        // Register keyboard shortcuts
                        this.registerShortcuts();
                    },

                    /**
                     * Register keyboard shortcuts for actions
                     */
                    registerShortcuts() {
                        if (this.shortcuts.length === 0) return;

                        document.addEventListener('keydown', (e) => {
                            for (const shortcut of this.shortcuts) {
                                const ctrlMatch = shortcut.ctrl ? (e.ctrlKey || e.metaKey) : true;
                                const altMatch = shortcut.alt ? e.altKey : true;
                                const shiftMatch = shortcut.shift ? e.shiftKey : true;
                                const keyMatch = e.key.toLowerCase() === shortcut.key;

                                if (ctrlMatch && altMatch && shiftMatch && keyMatch) {
                                    // Only match if required modifiers are pressed
                                    if (shortcut.ctrl && !(e.ctrlKey || e.metaKey)) continue;
                                    if (shortcut.alt && !e.altKey) continue;
                                    if (shortcut.shift && !e.shiftKey) continue;

                                    e.preventDefault();
                                    this.executeAction(shortcut.index);
                                    break;
                                }
                            }
                        });
                    },

                    /**
                     * Execute an action by index
                     */
                    async executeAction(index) {
                        const action = this.actions[index];
                        if (!action || this.actionStates[index]?.loading) return;

                        // Handle confirmation dialog
                        if (action.confirm) {
                            const message = action.confirmMessage ||
                                'Apakah Anda yakin ingin melakukan aksi ini?';
                            if (!confirm(message)) return;
                        }

                        // GET requests navigate directly
                        if (action.method === 'GET') {
                            window.location.href = action.url;
                            return;
                        }

                        // Async actions (POST, PUT, DELETE, etc.)
                        this.actionStates[index] = {
                            loading: true,
                            success: false,
                            error: false
                        };

                        try {
                            const response = await fetch(action.url, {
                                method: action.method,
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]')?.getAttribute(
                                        'content') || '',
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                            });

                            if (response.ok) {
                                this.actionStates[index] = {
                                    loading: false,
                                    success: true,
                                    error: false
                                };
                                this.$dispatch('quick-action-success', {
                                    index,
                                    action
                                });

                                // Reset success state after 2 seconds
                                setTimeout(() => {
                                    this.actionStates[index] = {
                                        loading: false,
                                        success: false,
                                        error: false
                                    };
                                }, 2000);

                                // Handle redirect if response contains one
                                const data = await response.json().catch(() => null);
                                if (data?.redirect) {
                                    window.location.href = data.redirect;
                                }
                            } else {
                                throw new Error(`HTTP ${response.status}`);
                            }
                        } catch (err) {
                            this.actionStates[index] = {
                                loading: false,
                                success: false,
                                error: true
                            };
                            this.$dispatch('quick-action-error', {
                                index,
                                action,
                                error: err.message
                            });

                            // Reset error state after 3 seconds
                            setTimeout(() => {
                                this.actionStates[index] = {
                                    loading: false,
                                    success: false,
                                    error: false
                                };
                            }, 3000);
                        }
                    },

                    /**
                     * Retry loading widget after error
                     */
                    retry() {
                        this.loading = true;
                        this.error = false;
                        this.$dispatch('quick-actions-widget-retry');

                        setTimeout(() => {
                            if (this.loading) {
                                this.loading = false;
                            }
                        }, 5000);
                    },

                    setLoaded() {
                        this.loading = false;
                        this.error = false;
                    },

                    setError() {
                        this.loading = false;
                        this.error = true;
                    }
                }));
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/components/widget/quick-actions.blade.php ENDPATH**/ ?>