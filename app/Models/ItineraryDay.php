<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItineraryDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_package_id',
        'day_number',
        'title',
        'description',
        'activities',
        'accommodation',
        'meals',
        'transport_mode',
        'sort_order',
    ];

    protected $casts = [
        'day_number' => 'integer',
        'activities' => 'array',
        'meals' => 'array',
        'sort_order' => 'integer',
    ];

    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class);
    }

    public function getMealsLabelAttribute(): string
    {
        if (empty($this->meals)) {
            return 'None';
        }

        $mealLabels = [
            'breakfast' => 'Breakfast',
            'lunch' => 'Lunch',
            'dinner' => 'Dinner',
        ];

        return collect($this->meals)
            ->map(fn($meal) => $mealLabels[$meal] ?? $meal)
            ->join(', ');
    }
}
