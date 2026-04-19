<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationConfig extends Model
{
use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'integration_id',
        'key',
        'value',
        'category',
        'is_encrypted',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get decrypted value
     */
    public function getDecryptedValue(): string
    {
        return $this->is_encrypted ? decrypt($this->value) : $this->value;
    }

    /**
     * Set encrypted value
     */
    public function setEncryptedValue(string $value): void
    {
        $this->value = encrypt($value);
        $this->is_encrypted = true;
    }

    /**
     * Set plain value
     */
    public function setPlainValue(string $value): void
    {
        $this->value = $value;
        $this->is_encrypted = false;
    }

    /**
     * Scope by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope API configs
     */
    public function scopeApiConfigs($query)
    {
        return $query->where('category', 'api');
    }

    /**
     * Scope sync configs
     */
    public function scopeSyncConfigs($query)
    {
        return $query->where('category', 'sync');
    }
}