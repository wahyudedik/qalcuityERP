
<div x-data="offlineSyncPanel()" x-init="init()" x-show="show" x-cloak
    class="mb-4 rounded-xl border overflow-hidden transition-all duration-300"
    :class="online ? 'bg-blue-500/5 border-blue-500/20' : 'bg-amber-500/10 border-amber-500/20'">

    
    <div class="flex items-center justify-between px-4 py-3 cursor-pointer" @click="expanded = !expanded">
        <div class="flex items-center gap-2.5">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                    :class="online ? 'bg-green-400' : 'bg-amber-400'"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5"
                    :class="online ? 'bg-green-500' : 'bg-amber-500'"></span>
            </span>
            <span class="text-sm font-medium"
                :class="online ? 'text-green-700 dark:text-green-400' : 'text-amber-700 dark:text-amber-400'">
                <template x-if="!online">
                    <span>Offline — <span x-text="count"></span> perubahan menunggu sinkronisasi</span>
                </template>
                <template x-if="online && count > 0">
                    <span>Online — menyinkronkan <span x-text="count"></span> perubahan...</span>
                </template>
                <template x-if="online && count === 0">
                    <span>Semua data tersinkronisasi</span>
                </template>
            </span>
        </div>
        <div class="flex items-center gap-2">
            <button x-show="online && count > 0" @click.stop="syncNow()" :disabled="syncing"
                class="text-xs px-3 py-1 rounded-lg bg-blue-500 text-white hover:bg-blue-600 disabled:opacity-50 transition">
                <span x-show="!syncing">Sinkronkan</span>
                <span x-show="syncing">Menyinkronkan...</span>
            </button>
            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="expanded && 'rotate-180'" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>

    
    <div x-show="expanded" class="px-4 pb-3 border-t border-white/10">
        <div class="mt-2 space-y-1.5 max-h-40 overflow-y-auto">
            <template x-for="item in items" :key="item.id">
                <div class="flex items-center justify-between text-xs py-1.5 px-2 rounded-lg bg-white/5">
                    <div class="flex items-center gap-2">
                        <span class="px-1.5 py-0.5 rounded bg-slate-700 text-slate-300 uppercase text-[10px] font-bold"
                            x-text="item.method"></span>
                        <span class="text-slate-400" x-text="item.module"></span>
                    </div>
                    <span class="text-slate-500" x-text="timeAgo(item.queued_at)"></span>
                </div>
            </template>
            <div x-show="items.length === 0" class="text-xs text-slate-500 py-2 text-center">
                Tidak ada perubahan tertunda
            </div>
        </div>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('4efb7edd-be0c-4e12-8320-7ae4fd588aa2')): $__env->markAsRenderedOnce('4efb7edd-be0c-4e12-8320-7ae4fd588aa2'); ?>
    <?php $__env->startPush('scripts'); ?>
        <script>
            function offlineSyncPanel() {
                return {
                    online: navigator.onLine,
                    count: 0,
                    items: [],
                    expanded: false,
                    syncing: false,
                    show: false,

                    async init() {
                        await this.refresh();
                        window.addEventListener('online', () => {
                            this.online = true;
                            this.refresh();
                        });
                        window.addEventListener('offline', () => {
                            this.online = false;
                            this.refresh();
                        });

                        // Refresh on SW messages
                        if ('serviceWorker' in navigator) {
                            navigator.serviceWorker.addEventListener('message', () => this.refresh());
                        }

                        // Periodic refresh
                        setInterval(() => this.refresh(), 10000);
                    },

                    async refresh() {
                        if (!window.ErpOffline) return;
                        this.count = await window.ErpOffline.pendingCount();
                        this.items = await window.ErpOffline.pendingItems();
                        this.show = this.count > 0 || !this.online;
                    },

                    async syncNow() {
                        if (!window.ErpOffline || this.syncing) return;
                        this.syncing = true;
                        try {
                            await window.ErpOffline.flush();
                            await this.refresh();
                        } finally {
                            this.syncing = false;
                        }
                    },

                    timeAgo(ts) {
                        const diff = Math.floor((Date.now() - ts) / 1000);
                        if (diff < 60) return 'baru saja';
                        if (diff < 3600) return Math.floor(diff / 60) + ' menit lalu';
                        if (diff < 86400) return Math.floor(diff / 3600) + ' jam lalu';
                        return Math.floor(diff / 86400) + ' hari lalu';
                    },
                };
            }
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/components/offline-sync-status.blade.php ENDPATH**/ ?>