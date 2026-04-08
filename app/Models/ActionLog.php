<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionLog extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'action_type',
        'model_type',
        'model_id',
        'before_state',
        'after_state',
        'metadata',
        'can_undo',
        'undone',
        'undone_at',
        'undone_by_user_id',
        'expires_at',
    ];

    protected $casts = [
        'before_state' => 'array',
        'after_state' => 'array',
        'metadata' => 'array',
        'can_undo' => 'boolean',
        'undone' => 'boolean',
        'undone_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function undoneBy()
    {
        return $this->belongsTo(User::class, 'undone_by_user_id');
    }

    /**
     * Undo this action
     */
    public function undo(): bool
    {
        if (!$this->can_undo || $this->undone) {
            return false;
        }

        try {
            // Restore before state
            if ($this->before_state && $this->model_type && $this->model_id) {
                $model = app($this->model_type)->find($this->model_id);

                if ($model) {
                    if ($this->action_type === 'delete') {
                        // Recreate deleted record
                        app($this->model_type)::create($this->before_state);
                    } else {
                        // Restore previous values
                        $model->update($this->before_state);
                    }
                }
            }

            $this->update([
                'undone' => true,
                'undone_at' => now(),
                'undone_by_user_id' => auth()->id(),
            ]);

            return true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Undo failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if action is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return now()->greaterThan($this->expires_at);
    }
}
