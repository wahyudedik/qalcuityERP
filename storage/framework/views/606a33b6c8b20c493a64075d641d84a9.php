

<div x-data="widgetContainer({
    widgetId: '<?php echo e($widgetId); ?>',
    loading: <?php echo e($loading ? 'true' : 'false'); ?>,
    draggable: <?php echo e($draggable ? 'true' : 'false'); ?>,
    editable: <?php echo e($editable ? 'true' : 'false'); ?>

})"
    <?php echo e($attributes->merge([
        'class' =>
            'bg-white rounded-2xl border border-gray-200 overflow-hidden transition-shadow duration-200 ' . $sizeClasses,
        'role' => 'region',
    ])); ?>

    aria-label="<?php echo e($getAriaLabel()); ?>" :aria-busy="loading.toString()"
    :class="{
        'ring-2 ring-blue-300 shadow-lg': isDragging,
        'opacity-50': isDragging,
        'hover:shadow-md': !isDragging
    }"
    <?php if($draggable): ?> draggable="true"
        x-on:dragstart="handleDragStart($event)"
        x-on:dragend="handleDragEnd($event)"
        x-on:dragover.prevent="handleDragOver($event)"
        x-on:drop.prevent="handleDrop($event)"
        x-on:keydown.space.prevent="handleKeyboardDrag($event)"
        x-on:keydown.enter.prevent="handleKeyboardDrag($event)"
        x-on:keydown.escape="cancelKeyboardDrag()"
        x-on:keydown.arrow-up.prevent="handleKeyboardMove('up')"
        x-on:keydown.arrow-down.prevent="handleKeyboardMove('down')" <?php endif; ?>
    data-widget-id="<?php echo e($widgetId); ?>">
    
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
        <div class="flex items-center gap-2 min-w-0">
            
            <?php if($draggable): ?>
                <button type="button"
                    class="flex-shrink-0 p-1 text-gray-400 hover:text-gray-600 cursor-grab active:cursor-grabbing rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                    aria-label="Seret untuk mengatur ulang widget" title="Seret untuk mengatur ulang" tabindex="0"
                    x-on:mousedown="$el.closest('[draggable]').setAttribute('draggable', 'true')">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16">
                        </path>
                    </svg>
                </button>
            <?php endif; ?>

            
            <?php if($title): ?>
                <h3 class="text-sm font-semibold text-gray-700 truncate" id="widget-title-<?php echo e($widgetId); ?>">
                    <?php echo e($title); ?>

                </h3>
            <?php endif; ?>
        </div>

        
        <div class="flex items-center gap-1 flex-shrink-0">
            
            <?php if($editable): ?>
                <button type="button"
                    class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                    x-on:click="toggleEditMode()" :aria-expanded="editMode.toString()"
                    aria-controls="widget-settings-<?php echo e($widgetId); ?>" aria-label="Pengaturan widget"
                    title="Pengaturan widget">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </button>
            <?php endif; ?>

            
            <button type="button"
                class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                x-show="error" x-cloak x-on:click="retry()" aria-label="Muat ulang widget" title="Muat ulang widget">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
            </button>
        </div>
    </div>

    
    <?php if($editable): ?>
        <div id="widget-settings-<?php echo e($widgetId); ?>" x-show="editMode" x-cloak x-collapse
            class="border-b border-gray-100 bg-gray-50 px-4 py-3" role="region"
            aria-label="Pengaturan widget <?php echo e($title); ?>">
            <?php if(isset($settings) && $settings->isNotEmpty()): ?>
                <?php echo e($settings); ?>

            <?php else: ?>
                <p class="text-sm text-gray-500 italic">Tidak ada pengaturan tersedia.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    
    <div class="relative p-4">
        
        <div x-show="loading" x-cloak class="absolute inset-0 p-4" aria-hidden="true">
            <div class="animate-pulse space-y-3">
                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                <div class="h-8 bg-gray-200 rounded w-full"></div>
                <div class="h-4 bg-gray-200 rounded w-5/6"></div>
            </div>
        </div>

        
        <div x-show="error && !loading" x-cloak class="text-center py-8" role="alert" aria-live="assertive">
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

        
        <div x-show="!loading && !error">
            <?php echo e($slot); ?>

        </div>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('21deeb9f-7d2d-48eb-95d1-d74400fe0295')): $__env->markAsRenderedOnce('21deeb9f-7d2d-48eb-95d1-d74400fe0295'); ?>
    <?php $__env->startPush('scripts'); ?>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('widgetContainer', (config = {}) => ({
                    widgetId: config.widgetId || '',
                    loading: config.loading || false,
                    error: false,
                    editMode: false,
                    isDragging: false,
                    isKeyboardDragging: false,
                    draggable: config.draggable || false,
                    editable: config.editable || false,

                    init() {
                        // Listen for external loading/error state changes
                        this.$watch('loading', (value) => {
                            if (!value && !this.error) {
                                this.$dispatch('widget-loaded', {
                                    widgetId: this.widgetId
                                });
                            }
                        });
                    },

                    /**
                     * Toggle edit/settings mode
                     */
                    toggleEditMode() {
                        this.editMode = !this.editMode;
                        this.$dispatch('widget-edit-toggled', {
                            widgetId: this.widgetId,
                            editMode: this.editMode
                        });
                    },

                    /**
                     * Retry loading widget after error
                     */
                    retry() {
                        this.loading = true;
                        this.error = false;
                        this.$dispatch('widget-retry', {
                            widgetId: this.widgetId
                        });

                        // Simulate retry with timeout for graceful UX
                        setTimeout(() => {
                            if (this.loading) {
                                this.$dispatch('widget-refresh', {
                                    widgetId: this.widgetId
                                });
                            }
                        }, 300);
                    },

                    /**
                     * Set error state externally
                     */
                    setError(message = null) {
                        this.loading = false;
                        this.error = true;
                        this.$dispatch('widget-error', {
                            widgetId: this.widgetId,
                            message: message
                        });
                    },

                    /**
                     * Set loaded state externally
                     */
                    setLoaded() {
                        this.loading = false;
                        this.error = false;
                    },

                    // ─── Drag-and-Drop Handlers ──────────────────────────

                    handleDragStart(event) {
                        if (!this.draggable) return;
                        this.isDragging = true;
                        event.dataTransfer.effectAllowed = 'move';
                        event.dataTransfer.setData('text/plain', this.widgetId);
                        this.$dispatch('widget-drag-start', {
                            widgetId: this.widgetId
                        });
                    },

                    handleDragEnd(event) {
                        this.isDragging = false;
                        this.$dispatch('widget-drag-end', {
                            widgetId: this.widgetId
                        });
                    },

                    handleDragOver(event) {
                        if (!this.draggable) return;
                        event.dataTransfer.dropEffect = 'move';
                    },

                    handleDrop(event) {
                        if (!this.draggable) return;
                        const sourceWidgetId = event.dataTransfer.getData('text/plain');
                        if (sourceWidgetId && sourceWidgetId !== this.widgetId) {
                            this.$dispatch('widget-reorder', {
                                sourceId: sourceWidgetId,
                                targetId: this.widgetId
                            });
                        }
                    },

                    // ─── Keyboard Drag Support ───────────────────────────

                    handleKeyboardDrag(event) {
                        if (!this.draggable) return;
                        this.isKeyboardDragging = !this.isKeyboardDragging;
                        if (this.isKeyboardDragging) {
                            this.isDragging = true;
                            this.$dispatch('widget-keyboard-drag-start', {
                                widgetId: this.widgetId
                            });
                        } else {
                            this.isDragging = false;
                            this.$dispatch('widget-keyboard-drag-end', {
                                widgetId: this.widgetId
                            });
                        }
                    },

                    cancelKeyboardDrag() {
                        if (this.isKeyboardDragging) {
                            this.isKeyboardDragging = false;
                            this.isDragging = false;
                            this.$dispatch('widget-keyboard-drag-cancel', {
                                widgetId: this.widgetId
                            });
                        }
                    },

                    handleKeyboardMove(direction) {
                        if (!this.isKeyboardDragging || !this.draggable) return;
                        this.$dispatch('widget-keyboard-move', {
                            widgetId: this.widgetId,
                            direction: direction
                        });
                    }
                }));
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/components/layout/widget-container.blade.php ENDPATH**/ ?>