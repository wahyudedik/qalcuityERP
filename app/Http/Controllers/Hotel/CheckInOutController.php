<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CheckoutReceipt;
use App\Models\MinibarCharge;
use App\Models\PreArrivalForm;
use App\Models\Reservation;
use App\Services\CheckInOutService;
use App\Services\RoomAvailabilityService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read Reservation $reservation
 */
class CheckInOutController extends Controller
{
    private CheckInOutService $checkInOutService;

    private RoomAvailabilityService $availabilityService;

    public function __construct(
        CheckInOutService $checkInOutService,
        RoomAvailabilityService $availabilityService
    ) {
        $this->checkInOutService = $checkInOutService;
        $this->availabilityService = $availabilityService;
    }

    // tenantId() inherited from parent Controller

    /**
     * Get authenticated user ID safely
     */
    private function getUserId(): int
    {
        return Auth::id() ?? abort(401, 'Unauthenticated.');
    }

    /**
     * Display Check-in/Check-out Dashboard
     */
    public function index(Request $request)
    {
        $tenantId = $this->tenantId();
        $today = now()->toDateString();

        // Get reservations for check-in today
        $checkIns = Reservation::where('tenant_id', $tenantId)
            ->whereDate('check_in_date', $today)
            ->whereIn('status', ['confirmed', 'pending'])
            ->with(['guest', 'roomType', 'room', 'preArrivalForm'])
            ->orderBy('check_in_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Get reservations for check-out today
        $checkOuts = Reservation::where('tenant_id', $tenantId)
            ->whereDate('check_out_date', $today)
            ->where('status', 'checked_in')
            ->with(['guest', 'roomType', 'room'])
            ->orderBy('check_out_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Get early check-ins (before today)
        $earlyCheckIns = Reservation::where('tenant_id', $tenantId)
            ->whereDate('check_in_date', '<', $today)
            ->whereIn('status', ['confirmed', 'pending'])
            ->with(['guest', 'roomType', 'room'])
            ->orderBy('check_in_date', 'asc')
            ->take(10)
            ->get();

        // Get late check-outs (after today)
        $lateCheckOuts = Reservation::where('tenant_id', $tenantId)
            ->whereDate('check_out_date', '>', $today)
            ->where('status', 'checked_in')
            ->with(['guest', 'roomType', 'room'])
            ->orderBy('check_out_date', 'asc')
            ->take(10)
            ->get();

        return view('hotel.check-in-out.index', compact(
            'checkIns',
            'checkOuts',
            'earlyCheckIns',
            'lateCheckOuts'
        ));
    }

    public function checkInForm(Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        // Get available rooms for the reservation's room type
        $availableRooms = $this->availabilityService->getAvailableRooms(
            $reservation->tenant_id,
            $reservation->check_in_date->toDateString(),
            $reservation->check_out_date->toDateString(),
            $reservation->room_type_id
        );

        return view('hotel.check-in.form', compact('reservation', 'availableRooms'));
    }

    public function processCheckIn(Request $request, Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'key_card_number' => 'nullable|string|max:50',
            'deposit_amount' => 'nullable|numeric|min:0',
            'deposit_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        try {
            $checkInOut = $this->checkInOutService->processCheckIn($reservation->id, [
                'room_id' => $data['room_id'],
                'key_card_number' => $data['key_card_number'] ?? null,
                'deposit_amount' => $data['deposit_amount'] ?? 0,
                'deposit_method' => $data['deposit_method'] ?? null,
                'notes' => $data['notes'] ?? null,
                'processed_by' => $this->getUserId(),
            ]);

            ActivityLog::record('check_in_processed', "Check-in processed: {$reservation->reservation_number}", $reservation);

            return redirect()->route('hotel.reservations.show', $reservation)->with('success', "Check-in completed for reservation {$reservation->reservation_number}.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function checkOutForm(Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        // Calculate charges
        $charges = $this->checkInOutService->calculateCharges($reservation->id);

        return view('hotel.check-out.form', compact('reservation', 'charges'));
    }

    public function processCheckOut(Request $request, Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'notes' => 'nullable|string',
            'payment_method' => 'required|in:cash,credit_card,debit_card,transfer,qris',
            'amount_paid' => 'required|numeric|min:0',
            'transaction_reference' => 'nullable|string|max:255',
        ]);

        try {
            // Calculate final charges
            $charges = $this->checkInOutService->calculateCharges($reservation->id);

            // Process check-out
            $checkInOut = $this->checkInOutService->processCheckOut($reservation->id, [
                'notes' => $data['notes'] ?? null,
                'processed_by' => $this->getUserId(),
            ]);

            // Create receipt
            $amountPaid = (float) $data['amount_paid'];
            $grandTotal = (float) $charges['grand_total'];
            $changeAmount = max(0, $amountPaid - $grandTotal);

            $receipt = CheckoutReceipt::create([
                'tenant_id' => $reservation->tenant_id,
                'reservation_id' => $reservation->id,
                'receipt_number' => CheckoutReceipt::generateReceiptNumber(),
                'grand_total' => $grandTotal,
                'amount_paid' => $amountPaid,
                'change_amount' => $changeAmount,
                'payment_method' => $data['payment_method'],
                'payment_status' => $amountPaid >= $grandTotal ? 'paid' : 'partially_paid',
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'payment_details' => $charges,
                'notes' => $data['notes'] ?? null,
                'paid_at' => now(),
                'processed_by' => $this->getUserId(),
            ]);

            // Mark minibar charges as charged
            MinibarCharge::where('reservation_id', $reservation->id)
                ->where('status', 'pending')
                ->update(['status' => 'charged']);

            ActivityLog::record('check_out_processed', "Check-out processed: {$reservation->reservation_number}", $reservation);

            // Generate PDF receipt
            $pdf = Pdf::loadView('hotel.check-out.receipt', compact('reservation', 'charges', 'receipt'));
            $pdfPath = "receipts/{$reservation->tenant_id}/{$receipt->receipt_number}.pdf";
            Storage::disk('public')->put($pdfPath, $pdf->output());
            $receipt->update(['pdf_path' => $pdfPath]);

            return redirect()->route('hotel.reservations.show', $reservation)
                ->with('success', "Check-out completed. Receipt: {$receipt->receipt_number}")
                ->with('receipt_url', Storage::url($pdfPath));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Show pre-arrival form
     */
    public function preArrivalForm(Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        // Check if form already exists
        $form = PreArrivalForm::where('reservation_id', $reservation->id)->first();

        return view('hotel.check-in.pre-arrival', compact('reservation', 'form'));
    }

    /**
     * Submit pre-arrival form
     */
    public function submitPreArrival(Request $request, Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            // ID Information
            'id_number' => 'nullable|string|max:50',
            'id_type' => 'nullable|string|in:passport,ktp,sim',
            'id_expiry' => 'nullable|date|after:today',
            'nationality' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',

            // Emergency Contact
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'emergency_contact_relationship' => 'nullable|string|max:100',

            // Preferences
            'room_preference' => 'nullable|string|max:255',
            'bed_preference' => 'nullable|string|in:twin,king,queen',
            'special_requests' => 'nullable|array',
            'dietary_requirements' => 'nullable|string',
            'amenities_requested' => 'nullable|array',

            // Arrival Details
            'estimated_arrival_time' => 'nullable|date_format:H:i',
            'transportation_method' => 'nullable|string|max:100',
            'flight_number' => 'nullable|string|max:50',
            'airport_pickup_required' => 'nullable|boolean',

            // Consents
            'terms_accepted' => 'required|accepted',
            'marketing_consent' => 'nullable|boolean',
            'data_processing_consent' => 'required|accepted',
        ]);

        try {
            $form = PreArrivalForm::updateOrCreate(
                ['reservation_id' => $reservation->id],
                array_merge($data, [
                    'tenant_id' => $reservation->tenant_id,
                    'guest_id' => $reservation->guest_id,
                    'airport_pickup_required' => $data['airport_pickup_required'] ?? false,
                    'terms_accepted' => true,
                    'marketing_consent' => $data['marketing_consent'] ?? false,
                    'data_processing_consent' => true,
                    'status' => 'completed',
                    'submitted_at' => now(),
                ])
            );

            ActivityLog::record('pre_arrival_submitted', "Pre-arrival form submitted: {$reservation->reservation_number}", $reservation);

            return redirect()->route('hotel.checkin-out.index')
                ->with('success', 'Pre-arrival form submitted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Quick check-in from dashboard (one-click)
     */
    public function quickCheckIn(Request $request, Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'room_id' => 'required|exists:rooms,id',
        ]);

        try {
            $checkInOut = $this->checkInOutService->processCheckIn($reservation->id, [
                'room_id' => $data['room_id'],
                'key_card_number' => null,
                'deposit_amount' => 0,
                'deposit_method' => null,
                'notes' => 'Quick check-in from dashboard',
                'processed_by' => $this->getUserId(),
            ]);

            ActivityLog::record('quick_check_in', "Quick check-in: {$reservation->reservation_number}", $reservation);

            return back()->with('success', "Guest checked in successfully to Room {$checkInOut->room->number}");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Verify pre-arrival form
     */
    public function verifyPreArrival(PreArrivalForm $form)
    {
        abort_unless($form->tenant_id === $this->tenantId(), 403);

        $form->markAsVerified($this->getUserId());

        ActivityLog::record('pre_arrival_verified', "Pre-arrival form verified: {$form->reservation->reservation_number}", $form->reservation);

        return back()->with('success', 'Pre-arrival form verified successfully!');
    }
}
