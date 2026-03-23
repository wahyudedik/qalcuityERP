<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\PermissionService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'avatar',
        'phone',
        'bio',
        'two_factor_secret',
        'two_factor_enabled',
        'two_factor_confirmed_at',
        'two_factor_recovery_codes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'          => 'datetime',
            'password'                   => 'hashed',
            'is_active'                  => 'boolean',
            'two_factor_enabled'         => 'boolean',
            'two_factor_recovery_codes'  => 'array',
            'two_factor_confirmed_at'    => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function userPermissions(): HasMany
    {
        return $this->hasMany(UserPermission::class);
    }

    // ─── Role Helpers ─────────────────────────────────────────────

    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }
    public function isAdmin(): bool      { return $this->role === 'admin'; }
    public function isManager(): bool    { return $this->role === 'manager'; }
    public function isStaff(): bool      { return $this->role === 'staff'; }
    public function isKasir(): bool      { return $this->role === 'kasir'; }
    public function isGudang(): bool     { return $this->role === 'gudang'; }

    public function hasRole(string|array $roles): bool
    {
        return in_array($this->role, (array) $roles);
    }

    /**
     * Label tampilan untuk role.
     */
    public function roleLabel(): string
    {
        return match($this->role) {
            'super_admin' => 'Super Admin',
            'admin'       => 'Admin',
            'manager'     => 'Manajer',
            'staff'       => 'Staff',
            'kasir'       => 'Kasir',
            'gudang'      => 'Gudang',
            default       => ucfirst($this->role),
        };
    }

    /**
     * Daftar AI tool yang diizinkan berdasarkan role.
     * null = semua tool diizinkan (admin/manager).
     */
    public function allowedAiTools(): ?array
    {
        return match($this->role) {
            'admin', 'manager', 'super_admin' => null, // semua tools

            'kasir' => [
                // POS & penjualan
                'get_pos_products', 'create_quick_sale', 'get_sales_summary',
                'get_customers', 'create_customer',
                // Inventory read-only
                'get_products', 'get_stock_alerts',
                // Loyalty
                'get_loyalty_info', 'add_loyalty_points', 'redeem_loyalty_points',
                // Notifikasi & info
                'get_notifications', 'get_dashboard_summary',
                // Panduan aplikasi
                'get_app_guide',
            ],

            'gudang' => [
                // Inventory full
                'get_products', 'create_product', 'update_product', 'update_product_image',
                'add_stock', 'get_stock_alerts', 'get_stock_movements',
                // Warehouse
                'get_warehouses', 'create_warehouse', 'transfer_stock', 'receive_transfer', 'adjust_stock',
                // Purchasing read
                'get_purchase_orders', 'get_suppliers',
                // Notifikasi
                'get_notifications', 'get_dashboard_summary',
                // Panduan aplikasi
                'get_app_guide',
            ],

            'staff' => [
                // Read-only untuk sebagian besar modul
                'get_products', 'get_stock_alerts', 'get_customers',
                'get_sales_summary', 'get_dashboard_summary',
                'get_notifications', 'get_pos_products',
                'create_quick_sale', 'get_employees',
                // Panduan aplikasi
                'get_app_guide',
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
     * Granular permission check — delegates to PermissionService.
     */
    public function hasPermission(string $module, string $action = 'view'): bool
    {
        return app(PermissionService::class)->check($this, $module, $action);
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
}
