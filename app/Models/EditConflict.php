<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EditConflict extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'model_type',
        'model_id',
        'original_user_id',
        'conflicting_user_id',
        'original_data',
        'first_user_changes',
        'second_user_changes',
        'resolution_strategy',
        'status',
        'resolved_data',
        'resolved_by_user_id',
        'resolution_notes',
        'detected_at',
        'resolved_at',
    ];

    protected $casts = [
        'original_data' => 'array',
        'first_user_changes' => 'array',
        'second_user_changes' => 'array',
        'resolved_data' => 'array',
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function originalUser()
    {
        return $this->belongsTo(User::class, 'original_user_id');
    }
    public function conflictingUser()
    {
        return $this->belongsTo(User::class, 'conflicting_user_id');
    }
    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }

    /**
     * Resolve with first user's changes
     */
    public function resolveWithFirstUser(): void
    {
        $this->update([
            'status' => 'resolved',
            'resolution_strategy' => 'first_wins',
            'resolved_data' => $this->first_user_changes,
            'resolved_by_user_id' => auth()->id(),
            'resolved_at' => now(),
        ]);
    }

    /**
     * Resolve with second user's changes
     */
    public function resolveWithSecondUser(): void
    {
        $this->update([
            'status' => 'resolved',
            'resolution_strategy' => 'last_wins',
            'resolved_data' => $this->second_user_changes,
            'resolved_by_user_id' => auth()->id(),
            'resolved_at' => now(),
        ]);
    }

    /**
     * Merge both changes
     */
    public function resolveWithMerge(array $mergedData): void
    {
        $this->update([
            'status' => 'resolved',
            'resolution_strategy' => 'merge',
            'resolved_data' => $mergedData,
            'resolved_by_user_id' => auth()->id(),
            'resolved_at' => now(),
        ]);
    }
}
