@props(['position' => 'right'])

<div class="relative" x-data="notificationBell()" x-init="init()">
    {{-- Notification Bell Icon --}}
    <button @click="isOpen = !isOpen" class="relative p-2 text-gray-600 hover:text-gray-900 transition"
        :class="{ 'notification-pulse': unreadCount > 0 }">
        <i class="fas fa-bell fa-lg"></i>
        @if ($unreadCount > 0)
            <span
                class="absolute -top-1 -right-1 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-xs font-bold text-white bg-red-500 rounded-full"
                x-text="unreadCount > 99 ? '99+' : unreadCount" x-show="unreadCount > 0">
            </span>
        @endif
    </button>

    {{-- Notification Dropdown --}}
    <div class="absolute {{ $position === 'right' ? 'right-0' : 'left-0' }} top-full mt-2 w-96 max-h-[500px] bg-white rounded-xl shadow-lg border border-gray-200 z-50 overflow-hidden"
        x-show="isOpen" @click.away="isOpen = false" x-cloak>

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
            <h6 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                <i class="fas fa-bell text-blue-600"></i>
                Notifikasi
            </h6>
            @if ($unreadCount > 0)
                <button @click="markAllRead()"
                    class="text-xs text-blue-600 hover:text-blue-800 transition flex items-center gap-1">
                    <i class="fas fa-check-double"></i> Tandai Semua Dibaca
                </button>
            @endif
        </div>

        {{-- Filter Tabs --}}
        <div class="px-3 py-2 bg-gray-50 border-b border-gray-200">
            <div class="flex gap-1">
                <button @click="filter = 'all'"
                    :class="filter === 'all' ? 'bg-blue-600 text-white' :
                        'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50'"
                    class="flex-1 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                    Semua
                </button>
                <button @click="filter = 'unread'"
                    :class="filter === 'unread' ? 'bg-blue-600 text-white' :
                        'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50'"
                    class="flex-1 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                    Belum Dibaca
                </button>
                <button @click="loadNotifications()"
                    class="px-3 py-1.5 bg-white text-gray-600 border border-gray-300 hover:bg-gray-50 rounded-lg text-xs transition">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>

        {{-- Notification List --}}
        <div class="overflow-y-auto" style="max-height: 350px;">
            <template x-if="loading">
                <div class="flex flex-col items-center justify-center py-8 text-gray-500">
                    <svg class="animate-spin h-6 w-6 text-blue-600 mb-2" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                        </path>
                    </svg>
                    <p class="text-sm text-gray-500">Memuat notifikasi...</p>
                </div>
            </template>

            <template x-if="!loading && notifications.length === 0">
                <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                    <i class="fas fa-bell-slash fa-3x mb-3 opacity-25"></i>
                    <p class="text-sm">Tidak ada notifikasi</p>
                </div>
            </template>

            <template x-for="notification in filteredNotifications" :key="notification.id">
                <a :href="getNotificationUrl(notification)"
                    class="flex items-start gap-3 px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition"
                    :class="{ 'bg-blue-50': !notification.read_at }" @click="markAsRead(notification)">
                    {{-- Icon based on module --}}
                    <div class="shrink-0">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center"
                            :class="getModuleBgClass(notification.module)">
                            <i :class="getModuleIconClass(notification.module)" class="text-white text-sm"></i>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <h6 class="text-xs font-bold text-gray-900 truncate" x-text="notification.title"></h6>
                            <template x-if="!notification.read_at">
                                <span
                                    class="shrink-0 px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Baru</span>
                            </template>
                        </div>
                        <p class="text-xs text-gray-500 truncate mt-0.5" x-text="notification.body"></p>
                        <small class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                            <i class="far fa-clock"></i>
                            <span x-text="getTimeAgo(notification.created_at)"></span>
                        </small>
                    </div>
                </a>
            </template>
        </div>

        {{-- Footer --}}
        <div class="p-3 border-t border-gray-200">
            <a href="{{ route('notifications.index') }}"
                class="block w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium text-center rounded-lg transition">
                <i class="fas fa-bell mr-1"></i> Lihat Semua Notifikasi
            </a>
        </div>
    </div>
</div>

@push('styles')
    <style>
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
    </style>
@endpush

@push('scripts')
    <script>
        function notificationBell() {
            return {
                isOpen: false,
                loading: false,
                filter: 'all',
                notifications: [],
                unreadCount: {{ $unreadCount ?? 0 }},

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
                        'inventory': 'bg-emerald-500',
                        'finance': 'bg-amber-500',
                        'hrm': 'bg-sky-500',
                        'ai': 'bg-blue-600',
                        'system': 'bg-gray-500',
                        'ecommerce': 'bg-red-500',
                        'healthcare': 'bg-emerald-500',
                    };
                    return classes[module] || 'bg-gray-500';
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
@endpush
