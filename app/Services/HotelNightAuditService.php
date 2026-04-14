<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

/**
 * HotelNightAuditService
 *
 * Orchestrates the hotel night audit process with pre-validation.
 *
 * BUG-HOTEL-001 FIX (Bug 1.20): runNightAudit() now validates that every
 * checked-in reservation has a valid room rate (> 0) BEFORE any posting
 * begins. If any reservation is missing a rate, a DomainException is thrown
 * with the full list of problematic reservations so staff can fix them first.
 */
class HotelNightAuditService
{
    public function __construct(
        private readonly NightAuditService $nightAuditService
    ) {}

    /**
     * Run the full night audit for the given tenant and date.
     *
     * Bug Condition: module = 'hotel' AND NOT rateValidated(input) — posting dengan nilai 0
     * Expected Behavior: DomainException with list of problematic reservations before posting starts.
     *
     * @param  int                    $tenantId
     * @param  \DateTimeInterface     $auditDate
     * @return array                  Audit result summary
     *
     * @throws \DomainException       when any checked-in reservation has an invalid/zero room rate
     */
    public function runNightAudit(int $tenantId, \DateTimeInterface $auditDate): array
    {
        $auditCarbon = \Carbon\Carbon::instance($auditDate);

        // --- Pre-validation phase ---
        // Collect ALL errors before touching any data (fail-fast, atomic validation)
        $activeReservations = Reservation::where('tenant_id', $tenantId)
            ->where('check_in_date', '<=', $auditCarbon)
            ->where('check_out_date', '>', $auditCarbon)
            ->where('status', 'checked_in')
            ->with(['rooms.roomType'])
            ->get();

        $errors = [];

        foreach ($activeReservations as $reservation) {
            foreach ($reservation->rooms as $room) {
                $rate = $reservation->rate_per_night
                    ?? $room->roomType?->base_rate
                    ?? 0;

                if (!$rate || $rate <= 0) {
                    $errors[] = sprintf(
                        'Reservasi #%d (Kamar %s): room rate tidak valid atau 0.',
                        $reservation->id,
                        $room->number ?? $room->id
                    );
                }
            }
        }

        if (!empty($errors)) {
            throw new \DomainException(
                "Night Audit dibatalkan. Perbaiki item berikut sebelum melanjutkan:\n" .
                implode("\n", $errors)
            );
        }

        // --- Posting phase (only reached when validation passes) ---
        $batch = $this->nightAuditService->startAudit($tenantId, $auditCarbon);

        $roomResult     = $this->nightAuditService->postRoomCharges($batch);
        $fbResult       = $this->nightAuditService->postFBRevenue($batch);
        $minibarResult  = $this->nightAuditService->postMinibarCharges($batch);

        $this->nightAuditService->calculateOccupancyStats($batch);
        $this->nightAuditService->completeAudit($batch);

        return [
            'batch_id'       => $batch->id,
            'batch_number'   => $batch->batch_number,
            'audit_date'     => $auditCarbon->toDateString(),
            'room_charges'   => $roomResult,
            'fb_revenue'     => $fbResult,
            'minibar_charges' => $minibarResult,
        ];
    }

    /**
     * Validate reservations without running the audit.
     * Useful for a "dry-run" check from the UI before committing.
     *
     * @return array{valid: bool, errors: string[]}
     */
    public function validateReservations(int $tenantId, \DateTimeInterface $auditDate): array
    {
        $auditCarbon = \Carbon\Carbon::instance($auditDate);

        $activeReservations = Reservation::where('tenant_id', $tenantId)
            ->where('check_in_date', '<=', $auditCarbon)
            ->where('check_out_date', '>', $auditCarbon)
            ->where('status', 'checked_in')
            ->with(['rooms.roomType'])
            ->get();

        $errors = [];

        foreach ($activeReservations as $reservation) {
            foreach ($reservation->rooms as $room) {
                $rate = $reservation->rate_per_night
                    ?? $room->roomType?->base_rate
                    ?? 0;

                if (!$rate || $rate <= 0) {
                    $errors[] = sprintf(
                        'Reservasi #%d (Kamar %s): room rate tidak valid atau 0.',
                        $reservation->id,
                        $room->number ?? $room->id
                    );
                }
            }
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
        ];
    }
}
