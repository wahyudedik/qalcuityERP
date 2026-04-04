<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PopupAd extends Model
{
    protected $fillable = [
        'title',
        'body',
        'image_path',
        'button_label',
        'button_url',
        'target',
        'tenant_ids',
        'frequency',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'tenant_ids' => 'array',
        'starts_at'  => 'date',
        'ends_at'    => 'date',
        'is_active'  => 'boolean',
    ];

    public function views(): HasMany
    {
        return $this->hasMany(PopupAdView::class);
    }

    /**
     * Check if this ad is within date range and targets the given user's tenant.
     */
    public function isVisibleTo(User $user): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $today = today();

        if ($this->starts_at && $this->starts_at->gt($today)) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->lt($today)) {
            return false;
        }

        if ($this->target === 'specific') {
            $ids = $this->tenant_ids ?? [];
            if (!in_array($user->tenant_id, $ids)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the popup should be shown to the user based on frequency rules.
     */
    public function shouldShowTo(User $user): bool
    {
        if (!$this->isVisibleTo($user)) {
            return false;
        }

        if ($this->frequency === 'always') {
            return true;
        }

        $view = PopupAdView::where('popup_ad_id', $this->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$view) {
            return true;
        }

        if ($this->frequency === 'once') {
            return false;
        }

        // daily — show again if last view was not today
        if ($this->frequency === 'daily') {
            return !Carbon::parse($view->viewed_at)->isToday();
        }

        return false;
    }

    /**
     * Return a human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) return 'Nonaktif';
        $today = today();
        if ($this->starts_at && $this->starts_at->gt($today)) return 'Terjadwal';
        if ($this->ends_at && $this->ends_at->lt($today)) return 'Kedaluwarsa';
        return 'Aktif';
    }
}
