<?php

namespace App\Models;

use App\Services\PermissionService;
use App\Services\PlanModuleMap;
use App\Services\UnifiedPermissionService;
use App\Traits\AuditsChanges;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use AuditsChanges, HasFactory, Notifiable;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'avatar',
        'avatar_url',
        'phone',
        'bio',
        'google_id',
        // FIX BUG-008: two_factor_secret dan recovery_codes TIDAK boleh di fillable
        // untuk mencegah mass assignment attack yang bisa menimpa secret 2FA
        // Set hanya via TwoFactorService dengan explicit assignment
        'two_factor_enabled',
        'two_factor_confirmed_at',
        'digest_frequency',
        'digest_day',
        'digest_time',
        'gamification_points',
        'gamification_level',
    ];

    protected $guarded = [
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'two_factor_recovery_codes' => 'array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function affiliate(): HasOne
    {
        return $this->hasOne(Affiliate::class);
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class, 'user_id');
    }

    public function userPermissions(): HasMany
    {
        return $this->hasMany(UserPermission::class);
    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function pointsLog(): HasMany
    {
        return $this->hasMany(UserPointsLog::class);
    }

    // ─── Gamification Helpers ──────────────────────────────────────

    public function totalPoints(): int
    {
        return $this->gamification_points;
    }

    public function currentLevel(): int
    {
        return $this->gamification_level;
    }

    // ─── Role Helpers ─────────────────────────────────────────────

    const ROLE_SUPER_ADMIN = 'super_admin';

    const ROLE_ADMIN = 'admin';

    const ROLE_MANAGER = 'manager';

    const ROLE_STAFF = 'staff';

    const ROLE_KASIR = 'kasir';

    const ROLE_GUDANG = 'gudang';

    const ROLE_AFFILIATE = 'affiliate';

    const ROLES = [
        self::ROLE_SUPER_ADMIN,
        self::ROLE_ADMIN,
        self::ROLE_MANAGER,
        self::ROLE_STAFF,
        self::ROLE_KASIR,
        self::ROLE_GUDANG,
        self::ROLE_AFFILIATE,
    ];

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    public function isGudang(): bool
    {
        return $this->role === 'gudang';
    }

    public function isAffiliate(): bool
    {
        return $this->role === 'affiliate';
    }

    public function hasRole(string|array $roles): bool
    {
        return in_array($this->role, (array) $roles);
    }

    /**
     * Cek apakah user menggunakan custom role.
     */
    public function isCustomRole(): bool
    {
        return str_starts_with($this->role, 'custom:');
    }

    /**
     * Ambil instance CustomRole jika user menggunakan custom role.
     */
    public function customRole(): ?CustomRole
    {
        if (! $this->isCustomRole()) {
            return null;
        }

        $roleId = (int) substr($this->role, strlen('custom:'));

        return CustomRole::find($roleId);
    }

    /**
     * Nama tampilan role — custom role name atau label hardcoded.
     */
    public function roleDisplayName(): string
    {
        if ($this->isCustomRole()) {
            $customRole = $this->customRole();

            return $customRole ? $customRole->name : 'Unknown Role';
        }

        return match ($this->role) {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'manager' => 'Manager',
            'staff' => 'Staff',
            'kasir' => 'Kasir',
            'gudang' => 'Gudang',
            'affiliate' => 'Affiliate',
            default => ucfirst($this->role),
        };
    }

    /**
     * Label tampilan untuk role.
     */
    public function roleLabel(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'manager' => 'Manager',
            'staff' => 'Staff',
            'kasir' => 'Kasir',
            'gudang' => 'Gudang',
            'affiliate' => 'Affiliate',
            default => ucfirst($this->role),
        };
    }

    /**
     * Daftar AI tool yang diizinkan berdasarkan role.
     * null = semua tool diizinkan (admin/manager).
     */
    public function allowedAiTools(): ?array
    {
        return match ($this->role) {
            'admin', 'manager', 'super_admin' => null, // semua tools

            'kasir' => [
                // POS & penjualan
                'get_pos_products',
                'create_quick_sale',
                'get_sales_summary',
                'get_customers',
                'create_customer',
                // Inventory read-only
                'get_products',
                'get_stock_alerts',
                // Loyalty
                'get_loyalty_info',
                'add_loyalty_points',
                'redeem_loyalty_points',
                // Notifikasi & info
                'get_notifications',
                'get_dashboard_summary',
                // Panduan & Advisor
                'get_app_guide',
                'get_ai_advisor',
            ],

            'gudang' => [
                // Inventory full
                'get_products',
                'create_product',
                'update_product',
                'update_product_image',
                'add_stock',
                'get_stock_alerts',
                'get_stock_movements',
                // Warehouse
                'get_warehouses',
                'create_warehouse',
                'transfer_stock',
                'receive_transfer',
                'adjust_stock',
                // Purchasing read
                'get_purchase_orders',
                'get_suppliers',
                // Farm / Agriculture
                'create_farm_plot',
                'get_farm_plots',
                'update_plot_status',
                'record_farm_activity',
                'start_crop_cycle',
                'get_crop_cycles',
                'advance_crop_phase',
                'log_harvest',
                'get_farm_cost_analysis',
                // Livestock
                'add_livestock',
                'get_livestock',
                'record_livestock_movement',
                'record_livestock_health',
                'get_livestock_health',
                'record_feed',
                'get_fcr',
                // Notifikasi
                'get_notifications',
                'get_dashboard_summary',
                // Panduan & Advisor
                'get_app_guide',
                'get_ai_advisor',
            ],

            'staff' => [
                // Read-only untuk sebagian besar modul
                'get_products',
                'get_stock_alerts',
                'get_customers',
                'get_sales_summary',
                'get_dashboard_summary',
                'get_notifications',
                'get_pos_products',
                'create_quick_sale',
                'get_employees',
                // Panduan & Advisor
                'get_app_guide',
                'get_ai_advisor',
            ],

            default => [], // role tidak dikenal: blokir semua
        };
    }

    /**
     * Super admin tidak terikat tenant manapun.
     */
    public function belongsToTenant(int $tenantId): bool
    {
        return $this->isSuperAdmin() || $this->tenant_id === $tenantId;
    }

    /**
     * Granular permission check — delegates to UnifiedPermissionService.
     */
    public function hasPermission(string $module, string $action = 'view'): bool
    {
        return app(UnifiedPermissionService::class)->check($this, $module, $action);
    }

    /**
     * TASK 8.2 & 8.3: Check if user can access a module considering:
     * 1. Subscription plan (via PlanModuleMap)
     * 2. Tenant module settings (enabled_modules)
     * 3. User role permissions (via PermissionService)
     *
     * @param  string  $moduleKey  Module key from ModuleRecommendationService::ALL_MODULES
     * @return bool True if user can access the module
     */
    public function canAccessModule(string $moduleKey): bool
    {
        // SuperAdmin bypasses all checks
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Check if tenant exists
        if (! $this->tenant) {
            return false;
        }

        // 1. Check subscription plan allows this module
        $planSlug = $this->tenant->subscriptionPlan->slug ?? $this->tenant->plan ?? null;
        if (! PlanModuleMap::isModuleAllowedForPlan($moduleKey, $planSlug)) {
            return false;
        }

        // 2. Check tenant has enabled this module
        if (! $this->tenant->isModuleEnabled($moduleKey)) {
            return false;
        }

        // 3. Check user role has permission to view this module
        // Map module keys to permission module names (some differ)
        $permissionModule = $this->mapModuleKeyToPermission($moduleKey);
        if ($permissionModule && ! $this->hasPermission($permissionModule, 'view')) {
            return false;
        }

        return true;
    }

    /**
     * Map module key to permission module name.
     * Some modules use different keys in PermissionService.
     */
    private function mapModuleKeyToPermission(string $moduleKey): ?string
    {
        // Direct mapping for most modules
        $map = [
            'invoicing' => 'invoices',
            'bank_reconciliation' => 'bank',
            'subscription_billing' => 'subscription_billing',
            'project_billing' => 'project_billing',
            'fnb' => null, // No specific permission, covered by general access
            'spa' => null,
            'hotel' => null,
            'telecom' => null,
        ];

        return $map[$moduleKey] ?? $moduleKey;
    }

    /**
     * URL avatar — fallback ke initials avatar jika belum upload.
     */
    public function avatarUrl(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        // UI Avatars fallback
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name)
            . '&background=3b82f6&color=fff&size=128&bold=true';
    }

    // ─── Notification Preferences ──────────────────────────────────

    /**
     * Get notification channels based on user preferences.
     *
     * @param  string  $notificationClass  Fully qualified notification class name
     * @return array Array of channels: 'database', 'mail', 'broadcast'
     */
    public function getNotificationChannels(string $notificationClass): array
    {
        // Extract notification type from class name
        $notificationType = $this->extractNotificationType($notificationClass);

        $channels = [];

        if (NotificationPreference::isEnabled($this->id, $notificationType, 'in_app')) {
            $channels[] = 'database';
        }
        if (NotificationPreference::isEnabled($this->id, $notificationType, 'email')) {
            $channels[] = 'mail';
        }
        if (NotificationPreference::isEnabled($this->id, $notificationType, 'push')) {
            $channels[] = 'broadcast';
        }

        // Fallback to in-app if no channels enabled
        return $channels ?: ['database'];
    }

    /**
     * Extract notification type from class name.
     * Example: App\Notifications\LeaveApprovedNotification -> leave_approved
     */
    private function extractNotificationType(string $notificationClass): string
    {
        $className = class_basename($notificationClass);
        // Remove "Notification" suffix and convert to snake_case
        $type = str_replace('Notification', '', $className);

        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $type));
    }
}
