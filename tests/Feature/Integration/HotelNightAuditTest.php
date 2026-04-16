<?php

namespace Tests\Feature\Integration;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\HotelNightAuditService;
use Tests\TestCase;

/**
 * Integration Test 14.2 — Hotel Night Audit End-to-End
 *
 * Verifikasi alur lengkap:
 * 1. Buat reservasi dengan rate valid dan invalid
 * 2. Jalankan night audit
 * 3. Verifikasi DomainException ditampilkan untuk reservasi invalid (Bug 1.20 fix)
 * 4. Verifikasi audit berhasil jika semua rate valid (preservation)
 *
 * Validates: Requirements 2.20
 */
class HotelNightAuditTest extends TestCase
{

    private $tenant;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant();
        $this->user   = $this->createAdminUser($this->tenant);
        $this->actingAs($this->user);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function createGuest(): Guest
    {
        return Guest::create([
            'tenant_id'  => $this->tenant->id,
            'guest_code' => 'GST-' . uniqid(),
            'name'       => 'Tamu Test',
            'email'      => 'tamu-' . uniqid() . '@test.com',
        ]);
    }

    private function createRoomType(float $baseRate = 500000): RoomType
    {
        return RoomType::create([
            'tenant_id'       => $this->tenant->id,
            'name'            => 'Standard Room',
            'code'            => 'STD-' . uniqid(),
            'base_rate'       => $baseRate,
            'base_occupancy'  => 2,
            'max_occupancy'   => 3,
            'is_active'       => true,
        ]);
    }

    private function createRoom(RoomType $roomType, string $number = '101'): Room
    {
        return Room::create([
            'tenant_id'    => $this->tenant->id,
            'room_type_id' => $roomType->id,
            'number'       => $number . '-' . uniqid(),
            'floor'        => '1',
            'status'       => 'occupied',
            'is_active'    => true,
        ]);
    }

    private function createCheckedInReservation(
        Room $room,
        float $ratePerNight = 0.0,
        string $checkIn = '-1 day',
        string $checkOut = '+1 day'
    ): Reservation {
        $guest = $this->createGuest();

        $reservation = Reservation::create([
            'tenant_id'          => $this->tenant->id,
            'guest_id'           => $guest->id,
            'room_type_id'       => $room->room_type_id,
            'room_id'            => $room->id,
            'reservation_number' => 'RES-' . uniqid(),
            'status'             => 'checked_in',
            'check_in_date'      => now()->modify($checkIn)->toDateString(),
            'check_out_date'     => now()->modify($checkOut)->toDateString(),
            'adults'             => 1,
            'nights'             => 1,
            'rate_per_night'     => $ratePerNight,
            'total_amount'       => $ratePerNight,
            'grand_total'        => $ratePerNight,
        ]);

        // Attach room via many-to-many
        $reservation->rooms()->attach($room->id, [
            'check_in_date'  => $reservation->check_in_date,
            'check_out_date' => $reservation->check_out_date,
            'rate_per_night' => $ratePerNight,
            'status'         => 'checked_in',
        ]);

        return $reservation;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Tests
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Integration 14.2 — Hotel Night Audit: DomainException untuk reservasi tanpa room rate.
     * Bug 1.20 fix: pre-validation sebelum posting.
     * Validates: Requirements 2.20
     */
    public function test_night_audit_throws_domain_exception_for_invalid_rate(): void
    {
        // Reservasi dengan rate invalid (0 dan room type base_rate = 0)
        $roomTypeInvalid = $this->createRoomType(0); // base_rate = 0
        $roomInvalid     = $this->createRoom($roomTypeInvalid, '101');
        $reservationBad  = $this->createCheckedInReservation($roomInvalid, 0.0); // rate_per_night = 0

        $service = app(HotelNightAuditService::class);

        $this->expectException(\DomainException::class);

        $service->runNightAudit($this->tenant->id, now());
    }

    /**
     * @test
     * Integration 14.2 — Hotel Night Audit: exception message menyebutkan reservasi bermasalah.
     * Validates: Requirements 2.20
     */
    public function test_night_audit_exception_message_lists_problematic_reservations(): void
    {
        $roomTypeInvalid = $this->createRoomType(0);
        $roomInvalid     = $this->createRoom($roomTypeInvalid, '201');
        $reservationBad  = $this->createCheckedInReservation($roomInvalid, 0.0);

        $service = app(HotelNightAuditService::class);

        try {
            $service->runNightAudit($this->tenant->id, now());
            $this->fail('DomainException harus dilempar untuk reservasi tanpa room rate.');
        } catch (\DomainException $e) {
            $message = $e->getMessage();

            // Pesan harus menyebutkan reservasi bermasalah
            $this->assertStringContainsString(
                (string) $reservationBad->id,
                $message,
                'Exception message harus menyebutkan ID reservasi bermasalah.'
            );

            // Pesan harus menyebutkan "Night Audit dibatalkan" atau serupa
            $this->assertStringContainsString(
                'Night Audit',
                $message,
                'Exception message harus menyebutkan Night Audit.'
            );
        }
    }

    /**
     * @test
     * Integration 14.2 — Hotel Night Audit: mixed reservasi valid dan invalid → exception.
     * Semua error dikumpulkan sebelum melempar exception (fail-fast, atomic validation).
     * Validates: Requirements 2.20
     */
    public function test_night_audit_collects_all_errors_before_throwing(): void
    {
        // Reservasi valid
        $roomTypeValid = $this->createRoomType(500000);
        $roomValid     = $this->createRoom($roomTypeValid, '301');
        $this->createCheckedInReservation($roomValid, 500000.0);

        // Dua reservasi invalid (rate = 0, room type base_rate = 0)
        $roomTypeInvalid = $this->createRoomType(0);
        $roomInvalid1    = $this->createRoom($roomTypeInvalid, '302');
        $roomInvalid2    = $this->createRoom($roomTypeInvalid, '303');
        $resBad1         = $this->createCheckedInReservation($roomInvalid1, 0.0);
        $resBad2         = $this->createCheckedInReservation($roomInvalid2, 0.0);

        $service = app(HotelNightAuditService::class);

        try {
            $service->runNightAudit($this->tenant->id, now());
            $this->fail('DomainException harus dilempar.');
        } catch (\DomainException $e) {
            $message = $e->getMessage();

            // Kedua reservasi bermasalah harus disebutkan
            $this->assertStringContainsString(
                (string) $resBad1->id,
                $message,
                'Exception harus menyebutkan reservasi bermasalah pertama.'
            );
            $this->assertStringContainsString(
                (string) $resBad2->id,
                $message,
                'Exception harus menyebutkan reservasi bermasalah kedua.'
            );
        }
    }

    /**
     * @test
     * Integration 14.2 — Hotel Night Audit: validateReservations() mengembalikan errors untuk rate invalid.
     * Validates: Requirements 2.20
     */
    public function test_validate_reservations_returns_errors_for_invalid_rate(): void
    {
        $roomTypeInvalid = $this->createRoomType(0);
        $roomInvalid     = $this->createRoom($roomTypeInvalid, '401');
        $reservationBad  = $this->createCheckedInReservation($roomInvalid, 0.0);

        $service = app(HotelNightAuditService::class);
        $result  = $service->validateReservations($this->tenant->id, now());

        $this->assertFalse($result['valid'],
            'validateReservations harus mengembalikan valid=false untuk reservasi tanpa rate.');
        $this->assertNotEmpty($result['errors'],
            'validateReservations harus mengembalikan daftar errors.');
        $this->assertStringContainsString(
            (string) $reservationBad->id,
            implode(' ', $result['errors']),
            'Errors harus menyebutkan ID reservasi bermasalah.'
        );
    }

    /**
     * @test
     * Integration 14.2 — Hotel Night Audit: validateReservations() valid jika semua rate valid.
     * Preservation: audit tidak diblokir jika semua reservasi memiliki rate valid.
     * Validates: Requirements 2.20
     */
    public function test_validate_reservations_returns_valid_when_all_rates_are_set(): void
    {
        $roomTypeValid = $this->createRoomType(500000);
        $roomValid     = $this->createRoom($roomTypeValid, '501');
        $this->createCheckedInReservation($roomValid, 500000.0);

        $service = app(HotelNightAuditService::class);
        $result  = $service->validateReservations($this->tenant->id, now());

        $this->assertTrue($result['valid'],
            'validateReservations harus mengembalikan valid=true jika semua rate valid.');
        $this->assertEmpty($result['errors'],
            'Tidak boleh ada errors jika semua rate valid.');
    }

    /**
     * @test
     * Integration 14.2 — Hotel Night Audit: reservasi dengan rate_per_night valid melewati validasi.
     * Validates: Requirements 2.20
     */
    public function test_reservation_with_valid_rate_per_night_passes_validation(): void
    {
        $roomType = $this->createRoomType(0); // base_rate = 0, tapi rate_per_night diisi
        $room     = $this->createRoom($roomType, '601');
        $this->createCheckedInReservation($room, 750000.0); // rate_per_night valid

        $service = app(HotelNightAuditService::class);
        $result  = $service->validateReservations($this->tenant->id, now());

        $this->assertTrue($result['valid'],
            'Reservasi dengan rate_per_night valid harus melewati validasi.');
    }

    /**
     * @test
     * Integration 14.2 — Hotel Night Audit: tidak ada reservasi checked_in → audit valid.
     * Edge case: tidak ada reservasi aktif.
     * Validates: Requirements 2.20
     */
    public function test_night_audit_with_no_checked_in_reservations_is_valid(): void
    {
        $service = app(HotelNightAuditService::class);
        $result  = $service->validateReservations($this->tenant->id, now());

        $this->assertTrue($result['valid'],
            'Tidak ada reservasi checked_in → validasi harus valid.');
        $this->assertEmpty($result['errors']);
    }
}
