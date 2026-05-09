<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomInspectionChecklist extends Model
{
    use AuditsChanges, SoftDeletes;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'room_type_id',
        'name',
        'description',
        'items',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Get default checklist for room type
     */
    public static function getDefaultForRoomType(int $tenantId, ?int $roomTypeId = null): ?self
    {
        if ($roomTypeId) {
            $checklist = static::where('tenant_id', $tenantId)
                ->where('room_type_id', $roomTypeId)
                ->where('is_active', true)
                ->first();

            if ($checklist) {
                return $checklist;
            }
        }

        // Fallback to global default
        return static::where('tenant_id', $tenantId)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Create default inspection checklist templates
     */
    public static function createDefaultTemplates(int $tenantId): void
    {
        $templates = [
            [
                'name' => 'Standard Room Inspection',
                'description' => 'Comprehensive room inspection checklist',
                'items' => [
                    'Living Area' => [
                        'Floors clean and undamaged',
                        'Walls free of marks and damage',
                        'Lights functioning properly',
                        'Windows clean and operational',
                        'Furniture in good condition',
                        'TV and remote working',
                        'AC temperature appropriate',
                    ],
                    'Bedroom' => [
                        'Beds made with clean linens',
                        'Pillows fluffed and positioned',
                        'Nightstands clean',
                        'Closet organized with hangers',
                        'Safe functioning',
                        'Blackout curtains working',
                    ],
                    'Bathroom' => [
                        'Toilet sanitized and functioning',
                        'Shower/tub clean with no hair',
                        'Sink and counter clean',
                        'Mirror spotless',
                        'Towels properly folded',
                        'Amenities stocked',
                        'Floor dry and clean',
                        'Exhaust fan working',
                    ],
                    'Safety' => [
                        'Smoke detector functioning',
                        'Fire extinguisher present',
                        'Emergency exit map visible',
                        'Door locks working',
                        'Peephole clear',
                    ],
                ],
                'is_default' => true,
            ],
            [
                'name' => 'Deep Clean Checklist',
                'description' => 'Detailed deep cleaning tasks',
                'items' => [
                    'Deep Cleaning Tasks' => [
                        'Clean behind furniture',
                        'Wash windows inside/out',
                        'Shampoo carpets',
                        'Mop hard floors',
                        'Dust ceiling fans',
                        'Clean air vents',
                        'Disinfect high-touch surfaces',
                        'Clean baseboards',
                    ],
                ],
                'is_default' => false,
            ],
        ];

        foreach ($templates as $template) {
            static::create([
                'tenant_id' => $tenantId,
                'name' => $template['name'],
                'description' => $template['description'] ?? null,
                'items' => $template['items'],
                'is_default' => $template['is_default'] ?? false,
                'is_active' => true,
            ]);
        }
    }
}
