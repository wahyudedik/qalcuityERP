<?php

namespace Tests\Feature\BugExploration;

use App\Models\NightAuditBatch;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Tenant;
use App\Models\User;
use App\Services\NightAuditService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Bug 1.20 — Night Audit Tanpa Room Rate Validation
 *
 * Membuktikan bahwa NightAuditService tidak memvalidasi bahwa semua
 * reservasi aktif memiliki room rate yang valid sebelum posting.
 *
 * EXPECTED: Test ini HARUS GAGAL pada kode unfixed.
 *
 * CATATAN: Berdasarkan kode aktual, NightAuditService.postRoomCharges()
 * sudah ada tapi tidak melempar exception untuk rate = 0, hanya skip posting.
 */
class HotelNightAuditTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant();
        $this->user = $this->createAdminUser($this->tenant);

        $this->actingAs($this->user);
    }

    /**
     * @test
     * Bug 1.20: Night audit harus melempar DomainException untuk reservasi tanpa room rate
     *
     * AKAN GAGAL karena NightAuditService tidak memvalidasi room rate sebelum posting
     *
     * Validates: Requirements 1.20
     */
    public function test_night_audit_throws_exception_for_reservation_without_room_rate(): void
    {
        // Arrange: Buat room type tanpa base rate
        $roomType = RoomType::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Standard Room',
            'code' => 'STD-'.uniqid(),
            'base_rate' => 0, // Tidak ada rate!
        ]);

        $room = Room::create([
            'tenant_id' => $this->tenant->id,
            'room_type_id' => $roomType->id,
            'number' => '101',
            'status' => 'occupied',
        ]);

        // Buat reservasi checked_in tanpa room rate
        $reservation = Reservation::create([
            'tenant_id' => $this->tenant->id,
            'room_type_id' => $roomType->id,
            'status' => 'checked_in',
            'check_in_date' => today()->subDay(),
            'check_out_date' => today()->addDay(),
            'rate_per_night' => null, // Tidak ada rate!
            'reservation_number' => 'RES-TEST-001',
            'nights' => 2,
            'adults' => 1,
        ]);

        // Attach room ke reservation
        $reservation->rooms()->attach($room->id);

        // Buat audit batch
        $batch = NightAuditBatch::create([
            'tenant_id' => $this->tenant->id,
            'batch_number' => 'AUDIT-TEST-001',
            'audit_date' => today(),
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        // Act: Jalankan night audit
        $service = app(NightAuditService::class);

        $threwException = false;
        $exceptionMessage = '';

        try {
            $result = $service->postRoomCharges($batch);

            // Jika tidak ada exception, cek apakah ada warning/error di result
            // Test ini AKAN GAGAL karena service tidak melempar exception
            // dan hanya skip posting untuk rate = 0
        } catch (\DomainException $e) {
            $threwException = true;
            $exceptionMessage = $e->getMessage();
        } catch (\Exception $e) {
            $threwException = true;
            $exceptionMessage = $e->getMessage();
        }

        // Assert: Harus ada exception untuk reservasi tanpa room rate
        // Test ini AKAN GAGAL karena NightAuditService tidak melempar exception
        $this->assertTrue(
            $threwException,
            'Bug 1.20: NightAuditService tidak melempar exception untuk reservasi '.
            'tanpa room rate yang valid. Service seharusnya memvalidasi semua reservasi '.
            'sebelum memulai posting dan melempar DomainException dengan daftar error.'
        );

        // Assert: Exception message harus menyebutkan reservasi bermasalah
        if ($threwException) {
            $this->assertStringContainsString(
                $reservation->id,
                $exceptionMessage,
                'Bug 1.20: Exception message tidak menyebutkan reservasi bermasalah.'
            );
        }
    }

    /**
     * @test
     * Bug 1.20: NightAuditService harus memiliki pre-validation sebelum posting
     *
     * AKAN GAGAL jika tidak ada pre-validation method
     */
    public function test_night_audit_service_has_pre_validation(): void
    {
        $nightAuditFile = 'app/Services/NightAuditService.php';

        if (! file_exists($nightAuditFile)) {
            $this->markTestSkipped('NightAuditService tidak ditemukan');
        }

        $content = file_get_contents($nightAuditFile);

        // Cari pre-validation logic: validasi rate sebelum posting
        $hasPreValidation = (
            str_contains($content, 'validateRates') ||
            str_contains($content, 'preValidate') ||
            str_contains($content, 'rateValidated') ||
            (str_contains($content, 'rate_per_night') && str_contains($content, 'DomainException')) ||
            (str_contains($content, 'rateAmount') && str_contains($content, 'errors'))
        );

        // Test ini AKAN GAGAL karena tidak ada pre-validation yang melempar DomainException
        $this->assertTrue(
            $hasPreValidation,
            'Bug 1.20: NightAuditService tidak memiliki pre-validation yang memvalidasi '.
            'room rate sebelum posting dan melempar DomainException. '.
            'Service saat ini hanya skip posting untuk rate = 0 tanpa memberikan error.'
        );
    }
}
