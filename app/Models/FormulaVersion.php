<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormulaVersion extends Model
{
    protected $fillable = [
        'tenant_id',
        'formula_id',
        'version_number',
        'changes_summary',
        'reason_for_change',
        'changed_by',
        'approval_notes',
    ];

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function formula(): BelongsTo
    {
        return $this->belongsTo(CosmeticFormula::class, 'formula_id');
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Check if this is a major version
     */
    public function isMajorVersion(): bool
    {
        $parts = explode('.', str_replace('v', '', $this->version_number));
        return isset($parts[1]) && $parts[1] == '0';
    }

    /**
     * Get version number formatted
     */
    public function getVersionFormattedAttribute(): string
    {
        if (strpos($this->version_number, 'v') !== 0) {
            return 'v' . $this->version_number;
        }
        return $this->version_number;
    }

    /**
     * Compare with another version
     */
    public function isNewerThan(string $otherVersion): bool
    {
        $current = str_replace('v', '', $this->version_number);
        $other = str_replace('v', '', $otherVersion);

        return version_compare($current, $other, '>');
    }

    /**
     * Get next version number
     */
    public static function getNextVersion(string $currentVersion, bool $major = false): string
    {
        $parts = explode('.', str_replace('v', '', $currentVersion));

        if ($major) {
            $parts[0] = (int) $parts[0] + 1;
            $parts[1] = 0;
        } else {
            if (!isset($parts[1])) {
                $parts[1] = 0;
            }
            $parts[1] = (int) $parts[1] + 1;
        }

        return 'v' . implode('.', $parts);
    }

    /**
     * Scopes
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderByDesc('created_at')->limit($limit);
    }

    public function scopeByVersion($query, string $version)
    {
        return $query->where('version_number', $version);
    }
}
