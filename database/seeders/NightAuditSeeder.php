<?php

namespace Database\Seeders;

use App\Models\NightAuditBatch;
use App\Models\DailyOccupancyStat;
use App\Models\DailyRateStat;
use Illuminate\Database\Seeder;

class NightAuditSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenantId = 1; // Adjust as needed

        // Create sample audit batches for last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);

            $batch = NightAuditBatch::create([
                'tenant_id' => $tenantId,
                'batch_number' => NightAuditBatch::generateBatchNumber($date),
                'audit_date' => $date,
                'started_at' => $date->copy()->addHours(23)->addMinutes(30),
                'completed_at' => $date->copy()->addDays(1),
                'auditor_id' => 1,
                'status' => 'completed',
                'total_rooms' => 50,
                'occupied_rooms' => rand(30, 45),
                'total_room_revenue' => rand(15000000, 25000000),
                'total_fb_revenue' => rand(3000000, 8000000),
                'total_other_revenue' => rand(500000, 2000000),
            ]);

            // Calculate totals and metrics
            $batch->update([
                'total_revenue' => $batch->total_room_revenue + $batch->total_fb_revenue + $batch->total_other_revenue,
            ]);

            $batch->calculateADR();
            $batch->calculateRevPAR();
        }

        // Create daily occupancy statistics for last 30 days
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $totalRooms = 50;
            $occupiedRooms = rand(25, 45);

            DailyOccupancyStat::create([
                'tenant_id' => $tenantId,
                'stat_date' => $date,
                'total_rooms' => $totalRooms,
                'available_rooms' => $totalRooms - rand(2, 5), // Some rooms out of order
                'occupied_rooms' => $occupiedRooms,
                'out_of_order_rooms' => rand(2, 5),
                'check_ins' => rand(5, 15),
                'check_outs' => rand(5, 15),
                'stay_over' => max(0, $occupiedRooms - rand(5, 15)),
                'no_shows' => rand(0, 3),
                'cancellations' => rand(0, 2),
            ]);
        }

        // Create daily rate statistics for last 30 days
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $roomsSold = rand(25, 45);
            $totalRevenue = $roomsSold * rand(400000, 600000);

            $rateStat = DailyRateStat::create([
                'tenant_id' => $tenantId,
                'stat_date' => $date,
                'total_room_revenue' => $totalRevenue,
                'total_available_rooms' => 50,
                'rooms_sold' => $roomsSold,
            ]);

            // Calculate ADR and RevPAR
            $rateStat->calculateMetrics();
        }

        $this->command->info('Night Audit seed data created successfully!');
        $this->command->info('Created:');
        $this->command->info('- 7 audit batches (last 7 days)');
        $this->command->info('- 30 days of occupancy statistics');
        $this->command->info('- 30 days of rate statistics (ADR & RevPAR)');
    }
}
