<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\GuestPreference;
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
                if (!is_array($items)) {
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

        if (!empty($appliedPrefs)) {
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
            \App\Models\ActivityLog::record(
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

            \App\Models\ActivityLog::record(
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

            \App\Models\ActivityLog::record(
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
        \App\Models\ActivityLog::record(
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
}
