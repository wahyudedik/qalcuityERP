<?php

namespace App\Services\DemoData\Generators;

use App\Services\DemoData\BaseIndustryGenerator;
use App\Services\DemoData\CoreDataContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HotelGenerator extends BaseIndustryGenerator
{
    public function getIndustryName(): string
    {
        return 'hotel';
    }

    public function generate(CoreDataContext $ctx): array
    {
        $tenantId = $ctx->tenantId;
        $recordsCreated = 0;
        $generatedData = [];

        // 1. Room Types (3: Standard, Deluxe, Suite)
        $roomTypeIds = [];
        try {
            $roomTypeIds = $this->seedRoomTypes($tenantId);
            $recordsCreated += count($roomTypeIds);
            $generatedData['room_types'] = count($roomTypeIds);
        } catch (\Throwable $e) {
            $this->logWarning('HotelGenerator: failed to seed room types', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
            $generatedData['room_types'] = 0;
        }

        // 2. Rooms (15 rooms: 8 on floor 1, 7 on floor 2)
        $roomIds = [];
        try {
            $roomIds = $this->seedRooms($tenantId, $roomTypeIds);
            $recordsCreated += count($roomIds);
            $generatedData['rooms'] = count($roomIds);
        } catch (\Throwable $e) {
            $this->logWarning('HotelGenerator: failed to seed rooms', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
            $generatedData['rooms'] = 0;
        }

        // 3. Guests (10 guests with complete information)
        $guestIds = [];
        try {
            $guestIds = $this->seedGuests($tenantId);
            $recordsCreated += count($guestIds);
            $generatedData['guests'] = count($guestIds);
        } catch (\Throwable $e) {
            $this->logWarning('HotelGenerator: failed to seed guests', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
            $generatedData['guests'] = 0;
        }

        // 4. Reservations (10: 4 confirmed, 3 checked_in, 3 checked_out)
        try {
            $reservationCount = $this->seedReservations($tenantId, $guestIds, $roomTypeIds, $roomIds);
            $recordsCreated += $reservationCount;
            $generatedData['reservations'] = $reservationCount;
        } catch (\Throwable $e) {
            $this->logWarning('HotelGenerator: failed to seed reservations', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
            $generatedData['reservations'] = 0;
        }

        // 5. Housekeeping Tasks
        try {
            $taskCount = $this->seedHousekeepingTasks($tenantId, $roomIds);
            $recordsCreated += $taskCount;
            $generatedData['housekeeping_tasks'] = $taskCount;
        } catch (\Throwable $e) {
            $this->logWarning('HotelGenerator: failed to seed housekeeping tasks', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
            $generatedData['housekeeping_tasks'] = 0;
        }

        return [
            'records_created' => $recordsCreated,
            'generated_data' => $generatedData,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  Room Types — 3 types: Standard, Deluxe, Suite
    // ─────────────────────────────────────────────────────────────

    private function seedRoomTypes(int $tenantId): array
    {
        $types = [
            [
                'name' => 'Standard',
                'code' => 'STD',
                'description' => 'Kamar standar dengan fasilitas lengkap dan nyaman',
                'base_occupancy' => 2,
                'max_occupancy' => 2,
                'base_rate' => 350000,
                'amenities' => json_encode(['AC', 'TV', 'WiFi', 'Kamar Mandi Dalam']),
            ],
            [
                'name' => 'Deluxe',
                'code' => 'DLX',
                'description' => 'Kamar deluxe dengan pemandangan indah dan fasilitas premium',
                'base_occupancy' => 2,
                'max_occupancy' => 3,
                'base_rate' => 550000,
                'amenities' => json_encode(['AC', 'TV LED', 'WiFi', 'Bathtub', 'Mini Bar', 'City View']),
            ],
            [
                'name' => 'Suite',
                'code' => 'STE',
                'description' => 'Suite mewah dengan ruang tamu terpisah dan fasilitas eksklusif',
                'base_occupancy' => 2,
                'max_occupancy' => 4,
                'base_rate' => 850000,
                'amenities' => json_encode(['AC', 'Smart TV', 'WiFi', 'Jacuzzi', 'Mini Bar', 'Ruang Tamu', 'Panoramic View']),
            ],
        ];

        $ids = [];

        foreach ($types as $type) {
            $existing = DB::table('room_types')
                ->where('tenant_id', $tenantId)
                ->where('code', $type['code'])
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                $ids[$type['code']] = (int) $existing->id;

                continue;
            }

            $id = DB::table('room_types')->insertGetId(array_merge($type, [
                'tenant_id' => $tenantId,
                'is_active' => true,
                'images' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            $ids[$type['code']] = (int) $id;
        }

        return $ids;
    }

    // ─────────────────────────────────────────────────────────────
    //  Rooms — 15 rooms: 8 on floor 1, 7 on floor 2
    // ─────────────────────────────────────────────────────────────

    private function seedRooms(int $tenantId, array $roomTypeIds): array
    {
        if (empty($roomTypeIds)) {
            $this->logWarning('HotelGenerator: no room type IDs available, skipping rooms', [
                'tenant_id' => $tenantId,
            ]);

            return [];
        }

        $stdId = $roomTypeIds['STD'] ?? array_values($roomTypeIds)[0];
        $dlxId = $roomTypeIds['DLX'] ?? (array_values($roomTypeIds)[1] ?? $stdId);
        $steId = $roomTypeIds['STE'] ?? (array_values($roomTypeIds)[2] ?? $dlxId);

        // Floor 1: rooms 101–108 (8 rooms), Floor 2: rooms 201–207 (7 rooms)
        $rooms = [
            ['number' => '101', 'floor' => '1', 'room_type_id' => $stdId, 'status' => 'available'],
            ['number' => '102', 'floor' => '1', 'room_type_id' => $stdId, 'status' => 'occupied'],
            ['number' => '103', 'floor' => '1', 'room_type_id' => $stdId, 'status' => 'available'],
            ['number' => '104', 'floor' => '1', 'room_type_id' => $stdId, 'status' => 'dirty'],
            ['number' => '105', 'floor' => '1', 'room_type_id' => $dlxId, 'status' => 'available'],
            ['number' => '106', 'floor' => '1', 'room_type_id' => $dlxId, 'status' => 'occupied'],
            ['number' => '107', 'floor' => '1', 'room_type_id' => $dlxId, 'status' => 'available'],
            ['number' => '108', 'floor' => '1', 'room_type_id' => $steId, 'status' => 'available'],
            ['number' => '201', 'floor' => '2', 'room_type_id' => $stdId, 'status' => 'available'],
            ['number' => '202', 'floor' => '2', 'room_type_id' => $stdId, 'status' => 'occupied'],
            ['number' => '203', 'floor' => '2', 'room_type_id' => $stdId, 'status' => 'dirty'],
            ['number' => '204', 'floor' => '2', 'room_type_id' => $dlxId, 'status' => 'available'],
            ['number' => '205', 'floor' => '2', 'room_type_id' => $dlxId, 'status' => 'occupied'],
            ['number' => '206', 'floor' => '2', 'room_type_id' => $steId, 'status' => 'available'],
            ['number' => '207', 'floor' => '2', 'room_type_id' => $steId, 'status' => 'maintenance'],
        ];

        $ids = [];

        foreach ($rooms as $room) {
            $existing = DB::table('rooms')
                ->where('tenant_id', $tenantId)
                ->where('number', $room['number'])
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                $ids[] = (int) $existing->id;

                continue;
            }

            $id = DB::table('rooms')->insertGetId([
                'tenant_id' => $tenantId,
                'room_type_id' => $room['room_type_id'],
                'number' => $room['number'],
                'floor' => $room['floor'],
                'building' => 'Main Building',
                'status' => $room['status'],
                'is_active' => true,
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $ids[] = (int) $id;
        }

        return $ids;
    }

    // ─────────────────────────────────────────────────────────────
    //  Guests — 10 guests with complete information
    // ─────────────────────────────────────────────────────────────

    private function seedGuests(int $tenantId): array
    {
        $guests = [
            ['name' => 'Budi Santoso',    'email' => 'budi.santoso@demo-hotel.com',    'phone' => '081234567001', 'id_type' => 'ktp',      'id_number' => '3201010101800001', 'city' => 'Jakarta',    'country' => 'ID', 'nationality' => 'Indonesian',   'dob' => '1980-01-01'],
            ['name' => 'Siti Rahayu',     'email' => 'siti.rahayu@demo-hotel.com',     'phone' => '081234567002', 'id_type' => 'ktp',      'id_number' => '3201010285900002', 'city' => 'Bandung',    'country' => 'ID', 'nationality' => 'Indonesian',   'dob' => '1990-02-15'],
            ['name' => 'Ahmad Fauzi',     'email' => 'ahmad.fauzi@demo-hotel.com',     'phone' => '081234567003', 'id_type' => 'ktp',      'id_number' => '3201010375850003', 'city' => 'Surabaya',   'country' => 'ID', 'nationality' => 'Indonesian',   'dob' => '1985-03-20'],
            ['name' => 'Dewi Lestari',    'email' => 'dewi.lestari@demo-hotel.com',    'phone' => '081234567004', 'id_type' => 'ktp',      'id_number' => '3201010492950004', 'city' => 'Yogyakarta', 'country' => 'ID', 'nationality' => 'Indonesian',   'dob' => '1995-04-10'],
            ['name' => 'Rudi Hermawan',   'email' => 'rudi.hermawan@demo-hotel.com',   'phone' => '081234567005', 'id_type' => 'ktp',      'id_number' => '3201010578880005', 'city' => 'Semarang',   'country' => 'ID', 'nationality' => 'Indonesian',   'dob' => '1988-05-25'],
            ['name' => 'Rina Kusumawati', 'email' => 'rina.kusumawati@demo-hotel.com', 'phone' => '081234567006', 'id_type' => 'ktp',      'id_number' => '3201010692920006', 'city' => 'Medan',      'country' => 'ID', 'nationality' => 'Indonesian',   'dob' => '1992-06-30'],
            ['name' => 'Hendra Wijaya',   'email' => 'hendra.wijaya@demo-hotel.com',   'phone' => '081234567007', 'id_type' => 'passport', 'id_number' => 'A1234567',         'city' => 'Singapore',  'country' => 'SG', 'nationality' => 'Singaporean',  'dob' => '1983-07-14'],
            ['name' => 'Maya Indrawati',  'email' => 'maya.indrawati@demo-hotel.com',  'phone' => '081234567008', 'id_type' => 'ktp',      'id_number' => '3201010887970008', 'city' => 'Makassar',   'country' => 'ID', 'nationality' => 'Indonesian',   'dob' => '1997-08-08'],
            ['name' => 'Agus Setiawan',   'email' => 'agus.setiawan@demo-hotel.com',   'phone' => '081234567009', 'id_type' => 'ktp',      'id_number' => '3201010975860009', 'city' => 'Palembang',  'country' => 'ID', 'nationality' => 'Indonesian',   'dob' => '1986-09-22'],
            ['name' => 'Fitri Handayani', 'email' => 'fitri.handayani@demo-hotel.com', 'phone' => '081234567010', 'id_type' => 'ktp',      'id_number' => '3201011091930010', 'city' => 'Denpasar',   'country' => 'ID', 'nationality' => 'Indonesian',   'dob' => '1993-10-05'],
        ];

        $ids = [];

        foreach ($guests as $i => $g) {
            $guestCode = 'GST-HTL-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);

            $existing = DB::table('guests')
                ->where('tenant_id', $tenantId)
                ->where('email', $g['email'])
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                $ids[] = (int) $existing->id;

                continue;
            }

            $id = DB::table('guests')->insertGetId([
                'tenant_id' => $tenantId,
                'guest_code' => $guestCode,
                'name' => $g['name'],
                'email' => $g['email'],
                'phone' => $g['phone'],
                'id_type' => $g['id_type'],
                'id_number' => $g['id_number'],
                'address' => 'Jl. Demo No. '.($i + 1),
                'city' => $g['city'],
                'country' => $g['country'],
                'nationality' => $g['nationality'],
                'date_of_birth' => $g['dob'],
                'preferred_language' => 'id',
                'communication_preference' => 'email',
                'vip_level' => ($i < 2) ? 'gold' : 'none',
                'loyalty_points' => ($i < 2) ? rand(500, 2000) : 0,
                'total_stays' => rand(1, 10),
                'membership_since' => Carbon::now()->subMonths(rand(3, 24))->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $ids[] = (int) $id;
        }

        return $ids;
    }

    // ─────────────────────────────────────────────────────────────
    //  Reservations — 10: 4 confirmed, 3 checked_in, 3 checked_out
    // ─────────────────────────────────────────────────────────────

    private function seedReservations(int $tenantId, array $guestIds, array $roomTypeIds, array $roomIds): int
    {
        if (empty($guestIds) || empty($roomTypeIds)) {
            $this->logWarning('HotelGenerator: missing guests or room types, skipping reservations', [
                'tenant_id' => $tenantId,
            ]);

            return 0;
        }

        $roomTypeIdList = array_values($roomTypeIds);
        $roomIdList = array_values($roomIds);

        $reservations = [
            // 4 confirmed (future check-in)
            ['status' => 'confirmed',    'check_in_offset' => 3,   'nights' => 2, 'adults' => 2, 'children' => 0],
            ['status' => 'confirmed',    'check_in_offset' => 5,   'nights' => 3, 'adults' => 2, 'children' => 1],
            ['status' => 'confirmed',    'check_in_offset' => 7,   'nights' => 1, 'adults' => 1, 'children' => 0],
            ['status' => 'confirmed',    'check_in_offset' => 10,  'nights' => 4, 'adults' => 2, 'children' => 2],
            // 3 checked_in (currently staying)
            ['status' => 'checked_in',   'check_in_offset' => -1,  'nights' => 3, 'adults' => 2, 'children' => 0],
            ['status' => 'checked_in',   'check_in_offset' => -2,  'nights' => 4, 'adults' => 1, 'children' => 0],
            ['status' => 'checked_in',   'check_in_offset' => 0,   'nights' => 2, 'adults' => 2, 'children' => 1],
            // 3 checked_out (past stays)
            ['status' => 'checked_out',  'check_in_offset' => -10, 'nights' => 2, 'adults' => 2, 'children' => 0],
            ['status' => 'checked_out',  'check_in_offset' => -15, 'nights' => 3, 'adults' => 1, 'children' => 0],
            ['status' => 'checked_out',  'check_in_offset' => -7,  'nights' => 1, 'adults' => 2, 'children' => 1],
        ];

        $rates = [350000, 550000, 850000];
        $count = 0;

        foreach ($reservations as $i => $res) {
            $resNumber = 'RES-HTL-'.$tenantId.'-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);

            $exists = DB::table('reservations')
                ->where('tenant_id', $tenantId)
                ->where('reservation_number', $resNumber)
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                continue;
            }

            $guestId = $guestIds[$i % count($guestIds)];
            $typeIndex = $i % count($roomTypeIdList);
            $roomTypeId = $roomTypeIdList[$typeIndex];
            $roomId = ! empty($roomIdList) ? $roomIdList[$i % count($roomIdList)] : null;
            $rate = $rates[$typeIndex] ?? 350000;

            $checkInDate = Carbon::today()->addDays($res['check_in_offset']);
            $checkOutDate = $checkInDate->copy()->addDays($res['nights']);
            $totalAmount = $rate * $res['nights'];
            $tax = round($totalAmount * 0.10, 2);
            $grandTotal = $totalAmount + $tax;

            $cancelledAt = null;
            $cancelReason = null;

            if ($res['status'] === 'cancelled') {
                $cancelledAt = $checkInDate->copy()->subDays(1)->toDateTimeString();
                $cancelReason = 'Pasien tidak dapat hadir';
            }

            DB::table('reservations')->insert([
                'tenant_id' => $tenantId,
                'guest_id' => $guestId,
                'room_type_id' => $roomTypeId,
                'room_id' => $roomId,
                'reservation_number' => $resNumber,
                'status' => $res['status'],
                'check_in_date' => $checkInDate->format('Y-m-d'),
                'check_out_date' => $checkOutDate->format('Y-m-d'),
                'adults' => $res['adults'],
                'children' => $res['children'],
                'nights' => $res['nights'],
                'rate_per_night' => $rate,
                'total_amount' => $totalAmount,
                'discount' => 0,
                'tax' => $tax,
                'grand_total' => $grandTotal,
                'source' => 'direct',
                'cancelled_at' => $cancelledAt,
                'cancel_reason' => $cancelReason,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $count++;
        }

        return $count;
    }

    // ─────────────────────────────────────────────────────────────
    //  Housekeeping Tasks — one task per room
    // ─────────────────────────────────────────────────────────────

    private function seedHousekeepingTasks(int $tenantId, array $roomIds): int
    {
        if (empty($roomIds)) {
            $this->logWarning('HotelGenerator: no room IDs available, skipping housekeeping tasks', [
                'tenant_id' => $tenantId,
            ]);

            return 0;
        }

        $taskDefs = [
            ['type' => 'regular_cleaning', 'priority' => 'normal', 'duration' => 30, 'status' => 'pending'],
            ['type' => 'turndown_service', 'priority' => 'normal', 'duration' => 20, 'status' => 'completed'],
            ['type' => 'deep_cleaning',    'priority' => 'high',   'duration' => 90, 'status' => 'pending'],
            ['type' => 'regular_cleaning', 'priority' => 'urgent', 'duration' => 30, 'status' => 'in_progress'],
            ['type' => 'inspection',       'priority' => 'normal', 'duration' => 15, 'status' => 'completed'],
        ];

        $rows = [];

        foreach ($roomIds as $idx => $roomId) {
            $taskDef = $taskDefs[$idx % count($taskDefs)];

            $exists = DB::table('housekeeping_tasks')
                ->where('tenant_id', $tenantId)
                ->where('room_id', $roomId)
                ->where('type', $taskDef['type'])
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                continue;
            }

            $scheduledAt = Carbon::today()->addHours(8 + ($idx % 8));
            $startedAt = null;
            $completedAt = null;

            if ($taskDef['status'] === 'in_progress') {
                $startedAt = $scheduledAt->copy()->addMinutes(5);
            } elseif ($taskDef['status'] === 'completed') {
                $startedAt = $scheduledAt->copy()->addMinutes(5);
                $completedAt = $startedAt->copy()->addMinutes($taskDef['duration']);
            }

            $rows[] = [
                'tenant_id' => $tenantId,
                'room_id' => $roomId,
                'assigned_to' => null,
                'type' => $taskDef['type'],
                'status' => $taskDef['status'],
                'priority' => $taskDef['priority'],
                'estimated_duration' => $taskDef['duration'],
                'scheduled_at' => $scheduledAt->format('Y-m-d H:i:s'),
                'started_at' => $startedAt?->format('Y-m-d H:i:s'),
                'completed_at' => $completedAt?->format('Y-m-d H:i:s'),
                'actual_duration' => $completedAt ? $taskDef['duration'] : null,
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (! empty($rows)) {
            $this->bulkInsert('housekeeping_tasks', $rows);
        }

        return count($rows);
    }
}
