<?php

namespace Tests\Property;

use App\Models\NotificationPreference;
use App\Models\Tenant;
use App\Models\User;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Tests\TestCase;

/**
 * Property-Based Tests for Notification Preference Round-Trip.
 *
 * Feature: erp-comprehensive-audit-fix
 *
 * **Validates: Requirements 7.3**
 */
class NotificationPreferencePropertyTest extends TestCase
{
    use TestTrait;

    /**
     * Property 5: Notification Preference Round-Trip
     *
     * For any combination of notification type and channel (in_app, email, push),
     * saving a preference and then reading it back must return the identical value.
     *
     * **Validates: Requirements 7.3**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_notification_preference_round_trip(): void
    {
        $this
            ->forAll(
                Generators::elements([
                    'low_stock',
                    'product_expiry',
                    'invoice_overdue',
                    'budget_alert',
                    'missing_report',
                    'asset_maintenance_due',
                    'ai_advisor',
                    'ai_digest',
                    'trial_expiry',
                    'reminder',
                ]),
                Generators::bool(), // in_app enabled
                Generators::bool(), // email enabled
                Generators::bool()  // push enabled
            )
            ->then(function ($notificationType, $inAppEnabled, $emailEnabled, $pushEnabled) {
                // Create tenant and user
                $tenant = $this->createTenant();
                $user = $this->createAdminUser($tenant);

                // Save notification preference
                $preference = NotificationPreference::create([
                    'user_id' => $user->id,
                    'notification_type' => $notificationType,
                    'in_app' => $inAppEnabled,
                    'email' => $emailEnabled,
                    'push' => $pushEnabled,
                ]);

                // Verify preference was saved
                $this->assertNotNull($preference->id, 'Preference should be saved to database');

                // Read preference back from database
                $retrieved = NotificationPreference::where('user_id', $user->id)
                    ->where('notification_type', $notificationType)
                    ->first();

                // Verify round-trip: retrieved values match saved values
                $this->assertNotNull($retrieved, 'Preference should be retrievable from database');

                $this->assertEquals(
                    $inAppEnabled,
                    $retrieved->in_app,
                    'in_app preference must match after round-trip. '.
                    'Saved: '.($inAppEnabled ? 'true' : 'false').', '.
                    'Retrieved: '.($retrieved->in_app ? 'true' : 'false')
                );

                $this->assertEquals(
                    $emailEnabled,
                    $retrieved->email,
                    'email preference must match after round-trip. '.
                    'Saved: '.($emailEnabled ? 'true' : 'false').', '.
                    'Retrieved: '.($retrieved->email ? 'true' : 'false')
                );

                $this->assertEquals(
                    $pushEnabled,
                    $retrieved->push,
                    'push preference must match after round-trip. '.
                    'Saved: '.($pushEnabled ? 'true' : 'false').', '.
                    'Retrieved: '.($retrieved->push ? 'true' : 'false')
                );

                $this->assertEquals(
                    $notificationType,
                    $retrieved->notification_type,
                    'notification_type must match after round-trip'
                );

                $this->assertEquals(
                    $user->id,
                    $retrieved->user_id,
                    'user_id must match after round-trip'
                );
            });
    }

    /**
     * Property 5 (variant): Preference Update Consistency
     *
     * For any existing preference, updating it and reading it back
     * must return the updated values.
     *
     * **Validates: Requirements 7.3**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_preference_update_consistency(): void
    {
        $this
            ->forAll(
                Generators::elements([
                    'low_stock',
                    'invoice_overdue',
                    'ai_advisor',
                ]),
                Generators::bool(), // initial in_app
                Generators::bool(), // initial email
                Generators::bool(), // updated in_app
                Generators::bool()  // updated email
            )
            ->then(function ($notificationType, $initialInApp, $initialEmail, $updatedInApp, $updatedEmail) {
                // Create tenant and user
                $tenant = $this->createTenant();
                $user = $this->createAdminUser($tenant);

                // Create initial preference
                $preference = NotificationPreference::create([
                    'user_id' => $user->id,
                    'notification_type' => $notificationType,
                    'in_app' => $initialInApp,
                    'email' => $initialEmail,
                    'push' => false,
                ]);

                // Update preference
                $preference->update([
                    'in_app' => $updatedInApp,
                    'email' => $updatedEmail,
                ]);

                // Read preference back
                $retrieved = NotificationPreference::find($preference->id);

                // Verify updated values match
                $this->assertEquals(
                    $updatedInApp,
                    $retrieved->in_app,
                    'Updated in_app preference must match. '.
                    'Initial: '.($initialInApp ? 'true' : 'false').', '.
                    'Updated: '.($updatedInApp ? 'true' : 'false').', '.
                    'Retrieved: '.($retrieved->in_app ? 'true' : 'false')
                );

                $this->assertEquals(
                    $updatedEmail,
                    $retrieved->email,
                    'Updated email preference must match. '.
                    'Initial: '.($initialEmail ? 'true' : 'false').', '.
                    'Updated: '.($updatedEmail ? 'true' : 'false').', '.
                    'Retrieved: '.($retrieved->email ? 'true' : 'false')
                );
            });
    }

    /**
     * Property 5 (variant): isEnabled Static Method Consistency
     *
     * For any saved preference, the static isEnabled() method must
     * return the same value as the stored preference.
     *
     * **Validates: Requirements 7.3**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_is_enabled_method_consistency(): void
    {
        $this
            ->forAll(
                Generators::elements([
                    'low_stock',
                    'product_expiry',
                    'invoice_overdue',
                ]),
                Generators::elements(['in_app', 'email', 'push']),
                Generators::bool() // enabled value
            )
            ->then(function ($notificationType, $channel, $enabled) {
                // Create tenant and user
                $tenant = $this->createTenant();
                $user = $this->createAdminUser($tenant);

                // Create preference with specific channel enabled/disabled
                $preferenceData = [
                    'user_id' => $user->id,
                    'notification_type' => $notificationType,
                    'in_app' => false,
                    'email' => false,
                    'push' => false,
                ];
                $preferenceData[$channel] = $enabled;

                $preference = NotificationPreference::create($preferenceData);

                // Check using static isEnabled method
                $isEnabled = NotificationPreference::isEnabled(
                    $user->id,
                    $notificationType,
                    $channel
                );

                // Verify isEnabled returns the same value as stored
                $this->assertEquals(
                    $enabled,
                    $isEnabled,
                    'isEnabled() must return the same value as stored preference. '.
                    "Type: {$notificationType}, Channel: {$channel}, ".
                    'Stored: '.($enabled ? 'true' : 'false').', '.
                    'isEnabled(): '.($isEnabled ? 'true' : 'false')
                );

                // Also verify by reading the preference directly
                $retrieved = NotificationPreference::where('user_id', $user->id)
                    ->where('notification_type', $notificationType)
                    ->first();

                $this->assertEquals(
                    $enabled,
                    $retrieved->{$channel},
                    'Direct database read must match stored value'
                );
            });
    }

    /**
     * Property 5 (variant): Default Preference Behavior
     *
     * For any notification type without a saved preference,
     * isEnabled() should return true (default enabled).
     *
     * **Validates: Requirements 7.3**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_default_preference_behavior(): void
    {
        $this
            ->forAll(
                Generators::elements([
                    'low_stock',
                    'invoice_overdue',
                    'ai_advisor',
                ]),
                Generators::elements(['in_app', 'email', 'push'])
            )
            ->then(function ($notificationType, $channel) {
                // Create tenant and user
                $tenant = $this->createTenant();
                $user = $this->createAdminUser($tenant);

                // Do NOT create any preference for this user/type combination

                // Check using static isEnabled method
                $isEnabled = NotificationPreference::isEnabled(
                    $user->id,
                    $notificationType,
                    $channel
                );

                // Verify default is enabled (true)
                $this->assertTrue(
                    $isEnabled,
                    'isEnabled() must return true (default enabled) when no preference exists. '.
                    "Type: {$notificationType}, Channel: {$channel}"
                );

                // Verify no preference record exists
                $preference = NotificationPreference::where('user_id', $user->id)
                    ->where('notification_type', $notificationType)
                    ->first();

                $this->assertNull(
                    $preference,
                    'No preference record should exist for this test case'
                );
            });
    }
}
