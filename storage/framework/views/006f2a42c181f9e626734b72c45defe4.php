<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['position' => 'right']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['position' => 'right']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="dropdown" x-data="notificationBell()" x-init="init()">
    <!-- Notification Bell Icon -->
    <button @click="isOpen = !isOpen" class="btn btn-link nav-link position-relative p-2"
        :class="{ 'notification-pulse': unreadCount > 0 }">
        <i class="fas fa-bell fa-lg"></i>
        <?php if($unreadCount > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                x-text="unreadCount > 99 ? '99+' : unreadCount" x-show="unreadCount > 0">
            </span>
        <?php endif; ?>
    </button>

    <!-- Notification Dropdown -->
    <div class="dropdown-menu dropdown-menu-end notification-dropdown" :class="{ 'show': isOpen }"
        @click.away="isOpen = false" style="width: 380px; max-height: 500px;">

        <!-- Header -->
        <div class="dropdown-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="fas fa-bell text-primary me-2"></i>
                Notifikasi
            </h6>
            <?php if($unreadCount > 0): ?>
                <button @click="markAllRead()" class="btn btn-sm btn-link text-decoration-none">
                    <i class="fas fa-check-double me-1"></i> Tandai Semua Dibaca
                </button>
            <?php endif; ?>
        </div>

        <div class="dropdown-divider"></div>

        <!-- Filter Tabs -->
        <div class="px-3 py-2 bg-light">
            <div class="btn-group btn-group-sm w-100" role="group">
                <button @click="filter = 'all'" :class="{ 'active': filter === 'all' }"
                    class="btn btn-outline-primary">
                    Semua
                </button>
                <button @click="filter = 'unread'" :class="{ 'active': filter === 'unread' }"
                    class="btn btn-outline-primary">
                    Belum Dibaca
                </button>
                <button @click="loadNotifications()" class="btn btn-outline-secondary">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>

        <!-- Notification List -->
        <div class="overflow-auto" style="max-height: 350px;">
            <template x-if="loading">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted small">Memuat notifikasi...</p>
                </div>
            </template>

            <template x-if="!loading && notifications.length === 0">
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-bell-slash fa-3x mb-3 opacity-25"></i>
                    <p class="mb-0">Tidak ada notifikasi</p>
                </div>
            </template>

            <template x-for="notification in filteredNotifications" :key="notification.id">
                <a :href="getNotificationUrl(notification)"
                    class="dropdown-item notification-item py-3 px-3 border-bottom"
                    :class="{ 'bg-light': !notification.read_at }" @click="markAsRead(notification)">
                    <div class="d-flex">
                        <!-- Icon based on module -->
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                :class="getModuleBgClass(notification.module)" style="width: 40px; height: 40px;">
                                <i :class="getModuleIconClass(notification.module)" class="text-white"></i>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <h6 class="mb-1 small fw-bold" x-text="notification.title"></h6>
                                <template x-if="!notification.read_at">
                                    <span class="badge bg-primary rounded-pill">Baru</span>
                                </template>
                            </div>
                            <p class="mb-1 text-muted small text-truncate" style="max-width: 250px;"
                                x-text="notification.body"></p>
                            <small class="text-muted">
                                <i class="far fa-clock me-1"></i>
                                <span x-text="getTimeAgo(notification.created_at)"></span>
                            </small>
                        </div>
                    </div>
                </a>
            </template>
        </div>

        <!-- Footer -->
        <div class="dropdown-divider"></div>
        <div class="dropdown-footer p-2 text-center">
            <a href="<?php echo e(route('notifications.index')); ?>" class="btn btn-sm btn-primary w-100">
                <i class="fas fa-bell me-2"></i> Lihat Semua Notifikasi
            </a>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
    <style>
        .notification-dropdown {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem;
        }

        .notification-item {
            transition: background-color 0.2s;
        }

        .notification-item:hover {
            background-color: #f8f9fa;
        }

        .notification-pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .dropdown-menu.show {
            display: block;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function notificationBell() {
            return {
                isOpen: false,
                loading: false,
                filter: 'all',
                notifications: [],
                unreadCount: <?php echo e($unreadCount ?? 0); ?>,

                init() {
                    this.loadNotifications();

                    // Auto-refresh every 30 seconds
                    setInterval(() => {
                        this.loadNotifications();
                    }, 30000);
                },

                async loadNotifications() {
                    this.loading = true;
                    try {
                        const response = await fetch('/api/notifications?limit=10');
                        const data = await response.json();
                        this.notifications = data.notifications || [];
                        this.unreadCount = data.unread_count || 0;
                    } catch (error) {
                        console.error('Failed to load notifications:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                get filteredNotifications() {
                    if (this.filter === 'unread') {
                        return this.notifications.filter(n => !n.read_at);
                    }
                    return this.notifications;
                },

                async markAsRead(notification) {
                    if (notification.read_at) return;

                    try {
                        await fetch(`/notifications/${notification.id}/read`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            }
                        });
                        notification.read_at = new Date().toISOString();
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    } catch (error) {
                        console.error('Failed to mark as read:', error);
                    }
                },

                async markAllRead() {
                    try {
                        await fetch('/notifications/read-all', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            }
                        });

                        this.notifications.forEach(n => n.read_at = new Date().toISOString());
                        this.unreadCount = 0;
                    } catch (error) {
                        console.error('Failed to mark all as read:', error);
                    }
                },

                getNotificationUrl(notification) {
                    if (notification.data && notification.data.url) {
                        return notification.data.url;
                    }
                    return '/notifications';
                },

                getModuleIconClass(module) {
                    const icons = {
                        'inventory': 'fas fa-box',
                        'finance': 'fas fa-dollar-sign',
                        'hrm': 'fas fa-users',
                        'ai': 'fas fa-robot',
                        'system': 'fas fa-cog',
                        'ecommerce': 'fas fa-shopping-cart',
                        'healthcare': 'fas fa-hospital',
                    };
                    return icons[module] || 'fas fa-bell';
                },

                getModuleBgClass(module) {
                    const classes = {
                        'inventory': 'bg-success',
                        'finance': 'bg-warning',
                        'hrm': 'bg-info',
                        'ai': 'bg-primary',
                        'system': 'bg-secondary',
                        'ecommerce': 'bg-danger',
                        'healthcare': 'bg-success',
                    };
                    return classes[module] || 'bg-secondary';
                },

                getTimeAgo(dateString) {
                    const date = new Date(dateString);
                    const now = new Date();
                    const seconds = Math.floor((now - date) / 1000);

                    if (seconds < 60) return 'Baru saja';
                    if (seconds < 3600) return Math.floor(seconds / 60) + ' menit lalu';
                    if (seconds < 86400) return Math.floor(seconds / 3600) + ' jam lalu';
                    return Math.floor(seconds / 86400) + ' hari lalu';
                }
            };
        }
    </script>
<?php $__env->stopPush(); ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\notification-bell.blade.php ENDPATH**/ ?>