<?php

namespace Database\Seeders;

use App\Models\GroupBooking;
use App\Models\Guest;
use App\Models\GuestPreference;
use Illuminate\Database\Seeder;

class FrontOfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenantId = 1; // Default tenant

        // Create sample guests with different VIP levels
        $guests = [
            [
                'guest_code' => 'GST-00001',
                'name' => 'John VIP Guest',
                'email' => 'john.vip@example.com',
                'phone' => '+6281234567890',
                'vip_level' => 'platinum',
                'loyalty_points' => 5000,
                'total_stays' => 50,
                'membership_since' => '2024-01-01',
                'preferred_language' => 'English',
                'communication_preference' => 'email',
            ],
            [
                'guest_code' => 'GST-00002',
                'name' => 'Jane Gold Member',
                'email' => 'jane.gold@example.com',
                'phone' => '+6281234567891',
                'vip_level' => 'gold',
                'loyalty_points' => 2000,
                'total_stays' => 20,
                'membership_since' => '2024-06-01',
                'preferred_language' => 'Indonesian',
                'communication_preference' => 'whatsapp',
            ],
            [
                'guest_code' => 'GST-00003',
                'name' => 'Bob Silver Traveler',
                'email' => 'bob.silver@example.com',
                'phone' => '+6281234567892',
                'vip_level' => 'silver',
                'loyalty_points' => 500,
                'total_stays' => 5,
                'preferred_language' => 'English',
                'communication_preference' => 'sms',
            ],
        ];

        foreach ($guests as $guestData) {
            $guestData['tenant_id'] = $tenantId;
            $guest = Guest::create($guestData);

            // Add preferences for each guest
            $this->addGuestPreferences($guest);
        }

        // Create sample group booking
        $organizer = Guest::where('vip_level', 'platinum')->first();

        if ($organizer) {
            $groupBooking = GroupBooking::create([
                'tenant_id' => $tenantId,
                'organizer_guest_id' => $organizer->id,
                'group_name' => 'Corporate Retreat 2026',
                'group_code' => GroupBooking::generateGroupCode($tenantId),
                'type' => 'corporate',
                'start_date' => now()->addDays(30),
                'end_date' => now()->addDays(35),
                'total_rooms' => 10,
                'total_guests' => 20,
                'total_amount' => 50000000,
                'paid_amount' => 25000000,
                'payment_status' => 'partial',
                'status' => 'confirmed',
                'notes' => 'Annual company retreat with team building activities',
                'benefits' => [
                    'Free breakfast for all attendees',
                    'Complimentary meeting room',
                    'Late checkout on departure day',
                    'Welcome drink voucher',
                ],
                'created_by' => 1,
            ]);
        }

        $this->command->info('Front Office sample data seeded successfully!');
    }

    /**
     * Add sample preferences to a guest
     */
    private function addGuestPreferences(Guest $guest): void
    {
        $preferences = [
            [
                'category' => 'room',
                'preference_key' => 'high_floor',
                'preference_value' => 'yes',
                'priority' => 3,
                'is_auto_applied' => true,
            ],
            [
                'category' => 'room',
                'preference_key' => 'room_type',
                'preference_value' => 'suite',
                'priority' => 2,
                'is_auto_applied' => true,
            ],
            [
                'category' => 'amenity',
                'preference_key' => 'extra_pillow',
                'preference_value' => '2 pillows',
                'priority' => 2,
                'is_auto_applied' => true,
            ],
            [
                'category' => 'dietary',
                'preference_key' => 'breakfast_preference',
                'preference_value' => 'continental',
                'priority' => 1,
                'is_auto_applied' => false,
            ],
            [
                'category' => 'communication',
                'preference_key' => 'contact_time',
                'preference_value' => 'morning',
                'priority' => 1,
                'is_auto_applied' => true,
            ],
        ];

        foreach ($preferences as $prefData) {
            GuestPreference::create([
                'tenant_id' => $guest->tenant_id,
                'guest_id' => $guest->id,
                ...$prefData,
            ]);
        }

        // Update guest preferences cache
        $guest->update([
            'preferences' => collect($preferences)
                ->groupBy('category')
                ->map(fn($group) => $group->pluck('preference_value', 'preference_key')->toArray())
                ->toArray(),
        ]);
    }
}
