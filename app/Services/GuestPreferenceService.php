<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Guest;
use App\Models\GuestPreference;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * GuestPreferenceService - Manages guest preferences, loyalty points, and personalization
 */
class GuestPreferenceService
{
    /**
     * Get all preferences for a guest
     */
    public function getGuestPreferences(Guest $guest, ?string $category = null): Collection
    {
        $query = $guest->preferences();

        if ($category) {
            $query->where('category', $category);
        }

        return $query->orderByDesc('priority')->orderByDesc('last_used_at')->get();
    }

    /**
     * Get high priority preferences that should be auto-applied
     */
    public function getAutoApplyPreferences(Guest $guest): Collection
    {
        return $guest->preferences()
            ->highPriority()
            ->autoApplied()
            ->orderByDesc('priority')
            ->get();
    }

    /**
     * Add or update a preference for a guest
     */
    public function setPreference(
        Guest $guest,
        string $category,
        string $key,
        ?string $value = null,
        int $priority = 1,
        bool $autoApply = true
    ): GuestPreference {
        $preference = GuestPreference::firstOrCreate(
            [
                'tenant_id' => $guest->tenant_id,
                'guest_id' => $guest->id,
                'category' => $category,
                'preference_key' => $key,
            ],
            [
                'preference_value' => $value,
                'priority' => $priority,
                'is_auto_applied' => $autoApply,
            ]
        );

        // Update if exists
        if ($preference->wasRecentlyCreated === false) {
            $preference->update([
                'preference_value' => $value,
                'priority' => $priority,
                'is_auto_applied' => $autoApply,
            ]);
        }

        // Update guest's JSON preferences cache
        $this->refreshGuestPreferencesCache($guest);

        return $preference;
    }

    /**
     * Record preferences from a reservation stay
     */
    public function recordStayPreferences(Guest $guest, array $preferences): void
    {
        DB::transaction(function () use ($guest, $preferences) {
            foreach ($preferences as $category => $items) {
                if (! is_array($items)) {
                    continue;
                }

                foreach ($items as $key => $value) {
                    $this->setPreference(
                        $guest,
                        $category,
                        (string) $key,
                        is_array($value) ? json_encode($value) : $value,
                        2, // Medium priority for observed preferences
                        true
                    );
                }
            }

            // Increment total stays
            $guest->increment('total_stays');
            $guest->update(['last_stay_at' => now()]);
        });
    }

    /**
     * Apply preferences to a reservation
     */
    public function applyPreferencesToReservation(Guest $guest, array &$reservationData): void
    {
        $preferences = $this->getAutoApplyPreferences($guest);

        $specialRequests = $reservationData['special_requests'] ?? '';
        $appliedPrefs = [];

        foreach ($preferences as $preference) {
            $prefText = "{$preference->preference_key}";
            if ($preference->preference_value) {
                $prefText .= ": {$preference->preference_value}";
            }
            $appliedPrefs[] = $prefText;

            // Mark preference as used
            $preference->markAsUsed();
        }

        if (! empty($appliedPrefs)) {
            $prefString = implode('; ', $appliedPrefs);
            $reservationData['special_requests'] = trim("$specialRequests\n[Guest Preferences] $prefString");
        }
    }

    /**
     * Award loyalty points to guest
     */
    public function awardPoints(Guest $guest, int $points, string $reason = ''): void
    {
        DB::transaction(function () use ($guest, $points, $reason) {
            $guest->addLoyaltyPoints($points);

            // Log the activity
            ActivityLog::record(
                'loyalty_points_awarded',
                "Awarded $points points to {$guest->name}: $reason",
                $guest,
                ['points' => $points, 'reason' => $reason]
            );
        });
    }

    /**
     * Redeem loyalty points
     */
    public function redeemPoints(Guest $guest, int $points, string $reason = ''): bool
    {
        if ($guest->loyalty_points < $points) {
            return false;
        }

        DB::transaction(function () use ($guest, $points, $reason) {
            $guest->decrement('loyalty_points', $points);

            ActivityLog::record(
                'loyalty_points_redeemed',
                "{$guest->name} redeemed $points points: $reason",
                $guest,
                ['points' => $points, 'reason' => $reason]
            );
        });

        return true;
    }

    /**
     * Get guest's VIP status based on stays and points
     */
    public function calculateVipLevel(Guest $guest): string
    {
        $totalStays = $guest->total_stays ?? 0;
        $loyaltyPoints = $guest->loyalty_points ?? 0;

        // Calculate based on stays OR points (whichever is higher)
        $score = max($totalStays, floor($loyaltyPoints / 100));

        if ($score >= 50) {
            return 'platinum';
        } elseif ($score >= 20) {
            return 'gold';
        } elseif ($score >= 5) {
            return 'silver';
        }

        return 'regular';
    }

    /**
     * Update guest's VIP level
     */
    public function updateVipLevel(Guest $guest): void
    {
        $newLevel = $this->calculateVipLevel($guest);

        if ($guest->vip_level !== $newLevel) {
            $oldLevel = $guest->vip_level;
            $guest->update(['vip_level' => $newLevel]);

            ActivityLog::record(
                'vip_level_updated',
                "{$guest->name} VIP level changed from $oldLevel to $newLevel",
                $guest,
                ['old_level' => $oldLevel, 'new_level' => $newLevel]
            );

            // Award bonus points for level upgrade
            $bonusPoints = [
                'silver' => 500,
                'gold' => 1000,
                'platinum' => 2000,
            ];

            if (isset($bonusPoints[$newLevel])) {
                $this->awardPoints($guest, $bonusPoints[$newLevel], "VIP upgrade to $newLevel");
            }
        }
    }

    /**
     * Refresh guest's JSON preferences cache
     */
    public function refreshGuestPreferencesCache(Guest $guest): void
    {
        $preferences = $guest->preferences()
            ->select('category', 'preference_key', 'preference_value', 'priority')
            ->orderByDesc('priority')
            ->get()
            ->groupBy('category')
            ->map(function ($group) {
                return $group->pluck('preference_value', 'preference_key')->toArray();
            })
            ->toArray();

        $guest->update(['preferences' => $preferences]);
    }

    /**
     * Get guest communication preference
     */
    public function getCommunicationPreference(Guest $guest): string
    {
        return $guest->communication_preference ?? 'email';
    }

    /**
     * Send communication to guest based on preference
     */
    public function sendCommunication(Guest $guest, string $subject, string $message): bool
    {
        $method = $this->getCommunicationPreference($guest);

        // This would integrate with your notification system
        // For now, just log it
        ActivityLog::record(
            'guest_communication',
            "Sent $method to {$guest->name}: $subject",
            $guest,
            ['method' => $method, 'subject' => $subject]
        );

        return true;
    }

    /**
     * Delete a preference
     */
    public function deletePreference(Guest $guest, string $category, string $key): bool
    {
        $deleted = GuestPreference::where('guest_id', $guest->id)
            ->where('category', $category)
            ->where('preference_key', $key)
            ->delete();

        if ($deleted) {
            $this->refreshGuestPreferencesCache($guest);
        }

        return $deleted > 0;
    }

    /**
     * Get suggested preferences based on guest's stay history
     * Analyzes past reservations to detect patterns and suggest preferences
     */
    public function getSuggestedPreferences(Guest $guest, int $limit = 10): array
    {
        $reservations = $guest->reservations()
            ->with(['roomType', 'checkInOuts'])
            ->where('status', 'checked_out')
            ->orderBy('check_in_date', 'desc')
            ->get();

        if ($reservations->isEmpty()) {
            return [];
        }

        $suggestions = [];

        // Analyze room type preferences
        $roomTypeCounts = $reservations->pluck('room_type_id')
            ->filter()
            ->countBy()
            ->sortDesc();

        if ($roomTypeCounts->isNotEmpty()) {
            $mostFrequentRoomType = $reservations
                ->where('room_type_id', $roomTypeCounts->keys()->first())
                ->first()?->roomType;

            if ($mostFrequentRoomType) {
                $suggestions[] = [
                    'category' => 'room',
                    'preference_key' => 'preferred_room_type',
                    'preference_value' => $mostFrequentRoomType->name,
                    'confidence' => round(($roomTypeCounts->first() / $reservations->count()) * 100, 2),
                    'source' => 'stay_history',
                    'frequency' => $roomTypeCounts->first(),
                ];
            }
        }

        // Analyze floor preferences from room assignments
        $floorNumbers = $reservations->flatMap(function ($reservation) {
            return $reservation->checkInOuts->pluck('room_id')
                ->filter()
                ->map(function ($roomId) {
                    $room = Room::find($roomId);

                    return $room ? (int) filter_var($room->number, FILTER_SANITIZE_NUMBER_INT) : null;
                })
                ->filter();
        })->countBy()->sortDesc();

        if ($floorNumbers->isNotEmpty()) {
            $preferredFloor = $floorNumbers->keys()->first();
            $suggestions[] = [
                'category' => 'room',
                'preference_key' => 'preferred_floor',
                'preference_value' => "Floor {$preferredFloor}",
                'confidence' => round(($floorNumbers->first() / $floorNumbers->sum()) * 100, 2),
                'source' => 'stay_history',
                'frequency' => $floorNumbers->first(),
            ];
        }

        // Analyze special requests patterns
        $specialRequests = $reservations->pluck('special_requests')
            ->filter()
            ->flatMap(function ($request) {
                // Extract common keywords
                $keywords = ['high floor', 'low floor', 'near elevator', 'quiet', 'view', 'sea view', 'city view', 'extra bed', 'late checkout', 'early checkin'];
                $found = [];
                foreach ($keywords as $keyword) {
                    if (stripos($request, $keyword) !== false) {
                        $found[] = $keyword;
                    }
                }

                return $found;
            })->countBy()->sortDesc();

        foreach ($specialRequests->take(3) as $keyword => $count) {
            $suggestions[] = [
                'category' => 'room',
                'preference_key' => str_replace(' ', '_', $keyword),
                'preference_value' => 'true',
                'confidence' => round(($count / $reservations->count()) * 100, 2),
                'source' => 'special_requests_analysis',
                'frequency' => $count,
            ];
        }

        // Analyze check-in time patterns
        $arrivalTimes = $reservations->pluck('expected_arrival_time')
            ->filter()
            ->map(function ($time) {
                $hour = (int) date('H', strtotime($time));
                if ($hour >= 6 && $hour < 12) {
                    return 'morning';
                } elseif ($hour >= 12 && $hour < 17) {
                    return 'afternoon';
                } elseif ($hour >= 17 && $hour < 22) {
                    return 'evening';
                }

                return 'night';
            })->countBy()->sortDesc();

        if ($arrivalTimes->isNotEmpty()) {
            $suggestions[] = [
                'category' => 'service',
                'preference_key' => 'preferred_arrival_time',
                'preference_value' => $arrivalTimes->keys()->first(),
                'confidence' => round(($arrivalTimes->first() / $arrivalTimes->sum()) * 100, 2),
                'source' => 'arrival_pattern',
                'frequency' => $arrivalTimes->first(),
            ];
        }

        // Analyze purpose of stay
        $purposes = $reservations->pluck('purpose_of_stay')
            ->filter()
            ->countBy()->sortDesc();

        if ($purposes->isNotEmpty()) {
            $suggestions[] = [
                'category' => 'service',
                'preference_key' => 'primary_purpose',
                'preference_value' => $purposes->keys()->first(),
                'confidence' => round(($purposes->first() / $purposes->sum()) * 100, 2),
                'source' => 'stay_purpose',
                'frequency' => $purposes->first(),
            ];
        }

        // Sort by confidence and limit
        usort($suggestions, function ($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        });

        return array_slice($suggestions, 0, $limit);
    }

    /**
     * Get loyalty-based preference recommendations
     * Suggests preferences and rewards based on VIP level
     */
    public function getLoyaltyRecommendations(Guest $guest): array
    {
        $vipLevel = $guest->vip_level ?? 'regular';
        $loyaltyPoints = $guest->loyalty_points ?? 0;

        // Base recommendations for all guests
        $recommendations = [
            'available_rewards' => [],
            'preference_suggestions' => [],
            'next_tier_requirements' => null,
        ];

        // VIP-specific rewards
        $rewardTiers = [
            'silver' => [
                'rewards' => [
                    ['name' => 'Late Checkout (2 PM)', 'points' => 200, 'category' => 'service'],
                    ['name' => 'Room Upgrade (1 Category)', 'points' => 500, 'category' => 'room'],
                ],
                'next_tier' => ['level' => 'gold', 'requires_stays' => 20, 'requires_points' => 2000],
            ],
            'gold' => [
                'rewards' => [
                    ['name' => 'Late Checkout (4 PM)', 'points' => 300, 'category' => 'service'],
                    ['name' => 'Room Upgrade (2 Categories)', 'points' => 800, 'category' => 'room'],
                    ['name' => 'Free Breakfast', 'points' => 400, 'category' => 'dining'],
                ],
                'next_tier' => ['level' => 'platinum', 'requires_stays' => 50, 'requires_points' => 5000],
            ],
            'platinum' => [
                'rewards' => [
                    ['name' => 'Late Checkout (6 PM)', 'points' => 500, 'category' => 'service'],
                    ['name' => 'Suite Upgrade', 'points' => 1500, 'category' => 'room'],
                    ['name' => 'Free Breakfast & Dinner', 'points' => 1000, 'category' => 'dining'],
                    ['name' => 'Airport Transfer', 'points' => 800, 'category' => 'transport'],
                ],
                'next_tier' => null,
            ],
        ];

        if (isset($rewardTiers[$vipLevel])) {
            $recommendations['available_rewards'] = $rewardTiers[$vipLevel]['rewards'];
            $recommendations['next_tier_requirements'] = $rewardTiers[$vipLevel]['next_tier'];
        }

        // Preference suggestions based on VIP level
        if (in_array($vipLevel, ['gold', 'platinum'])) {
            $recommendations['preference_suggestions'] = [
                ['category' => 'service', 'key' => 'priority_check_in', 'value' => 'true', 'reason' => 'VIP priority service'],
                ['category' => 'service', 'key' => 'welcome_amenity', 'value' => 'complimentary', 'reason' => 'VIP welcome benefit'],
                ['category' => 'room', 'key' => 'auto_upgrade', 'value' => 'true', 'reason' => 'Automatic room upgrade when available'],
            ];
        }

        return $recommendations;
    }

    /**
     * Auto-apply preferences from stay history after checkout
     */
    public function autoApplyStayPreferences(Guest $guest, Reservation $reservation): void
    {
        $suggestions = $this->getSuggestedPreferences($guest, 5);

        // Only auto-apply high confidence suggestions (>70%)
        $highConfidence = array_filter($suggestions, function ($suggestion) {
            return $suggestion['confidence'] >= 70;
        });

        foreach ($highConfidence as $suggestion) {
            // Check if preference already exists
            $existing = GuestPreference::where('guest_id', $guest->id)
                ->where('category', $suggestion['category'])
                ->where('preference_key', $suggestion['preference_key'])
                ->first();

            if (! $existing) {
                $this->setPreference(
                    $guest,
                    $suggestion['category'],
                    $suggestion['preference_key'],
                    $suggestion['preference_value'],
                    2, // Medium priority for auto-detected
                    true
                );
            }
        }
    }
}
