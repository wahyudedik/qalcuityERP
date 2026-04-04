<?php

namespace Database\Seeders;

use App\Models\RatePlan;
use App\Models\DynamicPricingRule;
use App\Models\OccupancyForecast;
use App\Models\CompetitorRate;
use App\Models\SpecialEvent;
use App\Models\PricingRecommendation;
use App\Models\RevenueSnapshot;
use App\Models\RoomType;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RevenueManagementSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();

        if (!$tenant) {
            $this->command->warn('No tenant found. Please run TenantSeeder first.');
            return;
        }

        $roomTypes = RoomType::where('tenant_id', $tenant->id)->get();

        if ($roomTypes->isEmpty()) {
            $this->command->warn('No room types found. Please run RoomTypeSeeder first.');
            return;
        }

        $this->seedRatePlans($tenant->id, $roomTypes);
        $this->seedDynamicPricingRules($tenant->id);
        $this->seedSpecialEvents($tenant->id);
        $this->seedCompetitorRates($tenant->id);
        $this->seedOccupancyForecasts($tenant->id, $roomTypes);
        $this->seedRevenueSnapshots($tenant->id);
        $this->seedPricingRecommendations($tenant->id, $roomTypes);

        $this->command->info('Revenue Management data seeded successfully!');
    }

    /**
     * Seed rate plans
     */
    private function seedRatePlans(int $tenantId, $roomTypes): void
    {
        $this->command->info('Seeding rate plans...');

        foreach ($roomTypes as $roomType) {
            $baseRate = $roomType->base_rate ?? 100;

            // Standard Rate
            RatePlan::create([
                'tenant_id' => $tenantId,
                'room_type_id' => $roomType->id,
                'name' => $roomType->name . ' - Standard Rate',
                'code' => strtoupper(substr($roomType->code ?? $roomType->name, 0, 3)) . '-STD',
                'description' => 'Our standard flexible rate with free cancellation',
                'type' => 'standard',
                'base_rate' => $baseRate,
                'min_stay' => 1,
                'max_stay' => null,
                'advance_booking_days' => null,
                'is_refundable' => true,
                'cancellation_hours' => 24,
                'includes_breakfast' => false,
                'inclusions' => ['WiFi', 'Parking'],
                'is_active' => true,
                'valid_from' => null,
                'valid_to' => null,
            ]);

            // Non-Refundable Rate
            RatePlan::create([
                'tenant_id' => $tenantId,
                'room_type_id' => $roomType->id,
                'name' => $roomType->name . ' - Non-Refundable',
                'code' => strtoupper(substr($roomType->code ?? $roomType->name, 0, 3)) . '-NR',
                'description' => 'Best available rate - non-refundable',
                'type' => 'non_refundable',
                'base_rate' => $baseRate * 0.85, // 15% discount
                'min_stay' => 1,
                'max_stay' => null,
                'advance_booking_days' => null,
                'is_refundable' => false,
                'cancellation_hours' => 0,
                'includes_breakfast' => false,
                'inclusions' => ['WiFi', 'Parking'],
                'is_active' => true,
                'valid_from' => null,
                'valid_to' => null,
            ]);

            // Breakfast Included
            RatePlan::create([
                'tenant_id' => $tenantId,
                'room_type_id' => $roomType->id,
                'name' => $roomType->name . ' - With Breakfast',
                'code' => strtoupper(substr($roomType->code ?? $roomType->name, 0, 3)) . '-BB',
                'description' => 'Includes daily breakfast buffet',
                'type' => 'package',
                'base_rate' => $baseRate + 15,
                'min_stay' => 1,
                'max_stay' => null,
                'advance_booking_days' => null,
                'is_refundable' => true,
                'cancellation_hours' => 24,
                'includes_breakfast' => true,
                'inclusions' => ['WiFi', 'Parking', 'Breakfast'],
                'is_active' => true,
                'valid_from' => null,
                'valid_to' => null,
            ]);

            // Long Stay Rate
            RatePlan::create([
                'tenant_id' => $tenantId,
                'room_type_id' => $roomType->id,
                'name' => $roomType->name . ' - Long Stay (7+ nights)',
                'code' => strtoupper(substr($roomType->code ?? $roomType->name, 0, 3)) . '-LS',
                'description' => 'Special rate for stays of 7 nights or more',
                'type' => 'promotional',
                'base_rate' => $baseRate * 0.80, // 20% discount
                'min_stay' => 7,
                'max_stay' => null,
                'advance_booking_days' => null,
                'is_refundable' => true,
                'cancellation_hours' => 48,
                'includes_breakfast' => false,
                'inclusions' => ['WiFi', 'Parking', 'Weekly Housekeeping'],
                'is_active' => true,
                'valid_from' => null,
                'valid_to' => null,
            ]);
        }

        $this->command->info('Created ' . ($roomTypes->count() * 4) . ' rate plans');
    }

    /**
     * Seed dynamic pricing rules
     */
    private function seedDynamicPricingRules(int $tenantId): void
    {
        $this->command->info('Seeding dynamic pricing rules...');

        $rules = [
            [
                'name' => 'Weekend Premium',
                'rule_type' => 'day_of_week',
                'conditions' => ['days' => ['friday', 'saturday']],
                'adjustment_type' => 'percentage',
                'adjustment_value' => 15,
                'priority' => 'high',
            ],
            [
                'name' => 'Early Bird Discount',
                'rule_type' => 'advance_booking',
                'conditions' => ['min_days' => 60, 'max_days' => 365],
                'adjustment_type' => 'percentage',
                'adjustment_value' => -10,
                'priority' => 'medium',
            ],
            [
                'name' => 'Last Minute Premium',
                'rule_type' => 'advance_booking',
                'conditions' => ['min_days' => 0, 'max_days' => 2],
                'adjustment_type' => 'percentage',
                'adjustment_value' => 10,
                'priority' => 'high',
            ],
            [
                'name' => 'Summer Season',
                'rule_type' => 'seasonal',
                'conditions' => ['season_start' => '2026-06-01', 'season_end' => '2026-08-31'],
                'adjustment_type' => 'percentage',
                'adjustment_value' => 20,
                'priority' => 'medium',
            ],
            [
                'name' => 'Low Season Discount',
                'rule_type' => 'seasonal',
                'conditions' => ['season_start' => '2026-01-01', 'season_end' => '2026-02-28'],
                'adjustment_type' => 'percentage',
                'adjustment_value' => -15,
                'priority' => 'low',
            ],
        ];

        foreach ($rules as $rule) {
            DynamicPricingRule::create(array_merge($rule, [
                'tenant_id' => $tenantId,
                'rate_plan_id' => null,
                'description' => 'Auto-generated pricing rule',
                'is_active' => true,
                'valid_from' => null,
                'valid_to' => null,
            ]));
        }

        $this->command->info('Created ' . count($rules) . ' pricing rules');
    }

    /**
     * Seed special events
     */
    private function seedSpecialEvents(int $tenantId): void
    {
        $this->command->info('Seeding special events...');

        $events = [
            [
                'name' => 'New Year\'s Eve',
                'start_date' => '2026-12-31',
                'end_date' => '2027-01-01',
                'impact_level' => 'very_high',
                'expected_demand_increase' => 50,
            ],
            [
                'name' => 'Summer Music Festival',
                'start_date' => '2026-07-15',
                'end_date' => '2026-07-20',
                'impact_level' => 'high',
                'expected_demand_increase' => 40,
            ],
            [
                'name' => 'International Conference',
                'start_date' => '2026-09-10',
                'end_date' => '2026-09-15',
                'impact_level' => 'high',
                'expected_demand_increase' => 35,
            ],
            [
                'name' => 'Valentine\'s Day',
                'start_date' => '2026-02-14',
                'end_date' => '2026-02-14',
                'impact_level' => 'medium',
                'expected_demand_increase' => 25,
            ],
            [
                'name' => 'Local Marathon',
                'start_date' => '2026-05-20',
                'end_date' => '2026-05-21',
                'impact_level' => 'medium',
                'expected_demand_increase' => 20,
            ],
        ];

        foreach ($events as $event) {
            SpecialEvent::create(array_merge($event, [
                'tenant_id' => $tenantId,
                'description' => 'Special event affecting demand',
                'affects_pricing' => true,
            ]));
        }

        $this->command->info('Created ' . count($events) . ' special events');
    }

    /**
     * Seed competitor rates
     */
    private function seedCompetitorRates(int $tenantId): void
    {
        $this->command->info('Seeding competitor rates...');

        $competitors = ['Hotel Grand', 'Royal Plaza', 'City Center Hotel', 'Seaside Resort'];
        $roomTypes = ['Standard Room', 'Deluxe Room', 'Suite'];

        $rates = [];
        $baseDate = now();

        foreach ($competitors as $competitor) {
            foreach ($roomTypes as $roomType) {
                // Generate rates for next 30 days
                for ($i = 0; $i < 30; $i++) {
                    $date = $baseDate->copy()->addDays($i);

                    // Base rate varies by room type
                    $baseRate = match ($roomType) {
                        'Standard Room' => 80,
                        'Deluxe Room' => 120,
                        'Suite' => 200,
                        default => 100,
                    };

                    // Add some variation
                    $variation = rand(-10, 20);
                    $weekendMultiplier = in_array($date->dayOfWeek, [5, 6]) ? 1.2 : 1.0;

                    $rate = ($baseRate + $variation) * $weekendMultiplier;

                    $rates[] = [
                        'tenant_id' => $tenantId,
                        'competitor_name' => $competitor,
                        'source' => 'manual',
                        'rate_date' => $date->format('Y-m-d'),
                        'rate' => round($rate, 2),
                        'room_type' => $roomType,
                        'amenities' => ['WiFi', 'Parking'],
                        'notes' => null,
                        'recorded_by' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        CompetitorRate::insert($rates);
        $this->command->info('Created ' . count($rates) . ' competitor rate records');
    }

    /**
     * Seed occupancy forecasts
     */
    private function seedOccupancyForecasts(int $tenantId, $roomTypes): void
    {
        $this->command->info('Seeding occupancy forecasts...');

        $forecasts = [];
        $baseDate = now();

        foreach ($roomTypes as $roomType) {
            $totalRooms = rand(10, 50);

            for ($i = 0; $i < 60; $i++) {
                $date = $baseDate->copy()->addDays($i);

                // Generate realistic occupancy pattern
                $baseOccupancy = 60;
                $dayOfWeekFactor = in_array($date->dayOfWeek, [5, 6]) ? 20 : -10;
                $seasonalFactor = in_array($date->month, [6, 7, 8]) ? 15 : 0;
                $randomFactor = rand(-15, 15);

                $occupancyRate = max(20, min(95, $baseOccupancy + $dayOfWeekFactor + $seasonalFactor + $randomFactor));
                $projectedBooked = (int) round($totalRooms * ($occupancyRate / 100));

                // Confidence decreases as we look further out
                $confidence = max(40, 95 - ($i * 0.8));

                $baseAdr = $roomType->base_rate ?? 100;
                $adr = $baseAdr * (0.8 + ($occupancyRate / 100) * 0.4);
                $revpar = $adr * ($occupancyRate / 100);

                $forecasts[] = [
                    'tenant_id' => $tenantId,
                    'room_type_id' => $roomType->id,
                    'forecast_date' => $date->format('Y-m-d'),
                    'total_rooms' => $totalRooms,
                    'projected_booked' => $projectedBooked,
                    'projected_available' => $totalRooms - $projectedBooked,
                    'projected_occupancy_rate' => round($occupancyRate, 2),
                    'projected_adr' => round($adr, 2),
                    'projected_revpar' => round($revpar, 2),
                    'confidence_level' => round($confidence, 2),
                    'factors' => json_encode([
                        'day_of_week' => $date->format('l'),
                        'is_weekend' => in_array($date->dayOfWeek, [5, 6]),
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        OccupancyForecast::insert($forecasts);
        $this->command->info('Created ' . count($forecasts) . ' occupancy forecasts');
    }

    /**
     * Seed revenue snapshots
     */
    private function seedRevenueSnapshots(int $tenantId): void
    {
        $this->command->info('Seeding revenue snapshots...');

        $snapshots = [];
        $baseDate = now()->subDays(30);

        $totalRooms = 100; // Assume 100 total rooms

        for ($i = 0; $i < 30; $i++) {
            $date = $baseDate->copy()->addDays($i);

            // Generate realistic daily data
            $baseOccupancy = 65;
            $dayOfWeekFactor = in_array($date->dayOfWeek, [5, 6, 0]) ? 15 : -5;
            $randomFactor = rand(-10, 10);

            $occupancyRate = max(30, min(95, $baseOccupancy + $dayOfWeekFactor + $randomFactor));
            $occupiedRooms = (int) round($totalRooms * ($occupancyRate / 100));

            $adr = 100 + rand(-20, 30);
            $totalRevenue = $occupiedRooms * $adr;
            $revpar = $totalRevenue / $totalRooms;

            $newBookings = rand(5, 25);
            $cancellations = rand(0, 5);

            $snapshots[] = [
                'tenant_id' => $tenantId,
                'snapshot_date' => $date->format('Y-m-d'),
                'total_rooms' => $totalRooms,
                'occupied_rooms' => $occupiedRooms,
                'occupancy_rate' => round($occupancyRate, 2),
                'adr' => round($adr, 2),
                'revpar' => round($revpar, 2),
                'total_revenue' => round($totalRevenue, 2),
                'total_reservations' => $occupiedRooms + rand(5, 15),
                'new_bookings_today' => $newBookings,
                'cancellations_today' => $cancellations,
                'breakdown_by_room_type' => json_encode([
                    'standard' => ['revenue' => $totalRevenue * 0.5, 'bookings' => (int) ($occupiedRooms * 0.5)],
                    'deluxe' => ['revenue' => $totalRevenue * 0.3, 'bookings' => (int) ($occupiedRooms * 0.3)],
                    'suite' => ['revenue' => $totalRevenue * 0.2, 'bookings' => (int) ($occupiedRooms * 0.2)],
                ]),
                'breakdown_by_channel' => json_encode([
                    'direct' => ['revenue' => $totalRevenue * 0.4, 'bookings' => (int) ($newBookings * 0.4)],
                    'bookingcom' => ['revenue' => $totalRevenue * 0.35, 'bookings' => (int) ($newBookings * 0.35)],
                    'expedia' => ['revenue' => $totalRevenue * 0.25, 'bookings' => (int) ($newBookings * 0.25)],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        RevenueSnapshot::insert($snapshots);
        $this->command->info('Created ' . count($snapshots) . ' revenue snapshots');
    }

    /**
     * Seed pricing recommendations
     */
    private function seedPricingRecommendations(int $tenantId, $roomTypes): void
    {
        $this->command->info('Seeding pricing recommendations...');

        $recommendations = [];

        foreach ($roomTypes->take(3) as $roomType) {
            $currentRate = $roomType->base_rate ?? 100;

            // Create a few pending recommendations
            for ($i = 0; $i < 3; $i++) {
                $changePercentage = [-10, 5, 15][$i];
                $recommendedRate = $currentRate * (1 + ($changePercentage / 100));

                $reasoning = match (true) {
                    $changePercentage > 0 => 'High forecasted occupancy suggests opportunity for rate increase.',
                    $changePercentage < 0 => 'Low demand period - consider promotional pricing.',
                    default => 'Market conditions suggest maintaining current rates.',
                };

                $recommendations[] = [
                    'tenant_id' => $tenantId,
                    'room_type_id' => $roomType->id,
                    'recommendation_date' => now()->addDays(rand(1, 14))->format('Y-m-d'),
                    'current_rate' => $currentRate,
                    'recommended_rate' => round($recommendedRate, 2),
                    'suggested_change_percentage' => $changePercentage,
                    'reasoning' => $reasoning,
                    'supporting_data' => json_encode([
                        'forecasted_occupancy' => 60 + $changePercentage,
                        'competitor_avg' => $currentRate * 1.05,
                    ]),
                    'status' => 'pending',
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        PricingRecommendation::insert($recommendations);
        $this->command->info('Created ' . count($recommendations) . ' pricing recommendations');
    }
}
