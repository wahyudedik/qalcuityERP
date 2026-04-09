<?php

namespace App\Http\Controllers\Api;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\Doctor;
use App\Models\Emr;
use App\Models\LabEquipment;
use App\Models\LabOrder;
use App\Models\LabResult;
use App\Models\LabSample;
use App\Models\Medicine;
use App\Models\OperatingRoom;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\PharmacyInventory;
use App\Models\Prescription;
use App\Models\RadiologyExam;
use App\Models\RadiologyResult;
use App\Models\SurgerySchedule;
use App\Models\SurgeryTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HealthcareApiController extends ApiBaseController
{
    /**
     * Get patients list
     */
    public function patients(Request $request)
    {
        $query = Patient::where('tenant_id', $this->getTenantId());

        if ($request->filled('search')) {
            $query->where('full_name', 'like', "%{$request->search}%")
                ->orWhere('mrn', 'like', "%{$request->search}%");
        }

        $patients = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success($patients);
    }

    /**
     * Get patient detail
     */
    public function patient($id)
    {
        $patient = Patient::where('tenant_id', $this->getTenantId())
            ->with(['visits', 'appointments', 'labResults'])
            ->findOrFail($id);

        return $this->success($patient);
    }

    /**
     * Create patient
     */
    public function createPatient(Request $request)
    {
        $validated = $request->validate([
            'mrn' => 'required|string|unique:patients,mrn',
            'full_name' => 'required|string',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
        ]);

        $patient = Patient::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
        ]));

        return $this->success($patient, 'Patient created successfully', 201);
    }

    /**
     * Update patient
     */
    public function updatePatient(Request $request, $id)
    {
        $patient = Patient::where('tenant_id', $this->getTenantId())->findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'sometimes|string',
            'date_of_birth' => 'sometimes|date',
            'gender' => 'sometimes|in:male,female,other',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
        ]);

        $patient->update($validated);

        return $this->success($patient, 'Patient updated successfully');
    }

    /**
     * Delete patient
     */
    public function deletePatient($id)
    {
        $patient = Patient::where('tenant_id', $this->getTenantId())->findOrFail($id);
        $patient->delete();

        return $this->success(null, 'Patient deleted successfully');
    }

    /**
     * Get doctors list
     */
    public function doctors(Request $request)
    {
        $query = Doctor::where('tenant_id', $this->getTenantId())
            ->where('is_active', true);

        if ($request->filled('specialty')) {
            $query->where('specialty', $request->specialty);
        }

        $doctors = $query->paginate($request->get('per_page', 20));

        return $this->success($doctors);
    }

    /**
     * Get doctor detail
     */
    public function doctor($id)
    {
        $doctor = Doctor::where('tenant_id', $this->getTenantId())
            ->with(['appointments', 'patients'])
            ->findOrFail($id);

        return $this->success($doctor);
    }

    /**
     * Get appointments
     */
    public function appointments(Request $request)
    {
        $query = Appointment::where('tenant_id', $this->getTenantId())
            ->with(['patient', 'doctor']);

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $appointments = $query->latest('appointment_date')->paginate($request->get('per_page', 20));

        return $this->success($appointments);
    }

    /**
     * Get appointment detail
     */
    public function appointment($id)
    {
        $appointment = Appointment::where('tenant_id', $this->getTenantId())
            ->with(['patient', 'doctor', 'emr'])
            ->findOrFail($id);

        return $this->success($appointment);
    }

    /**
     * Create appointment
     */
    public function createAppointment(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'reason' => 'required|string',
            'visit_type' => 'nullable|in:new,follow_up,emergency',
        ]);

        $appointment = Appointment::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'status' => 'scheduled',
        ]));

        return $this->success($appointment, 'Appointment created successfully', 201);
    }

    /**
     * Update appointment status
     */
    public function updateAppointmentStatus(Request $request, $id)
    {
        $appointment = Appointment::where('tenant_id', $this->getTenantId())->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,cancelled,no_show',
        ]);

        $appointment->update($validated);

        return $this->success($appointment, 'Appointment status updated successfully');
    }

    /**
     * Get lab results
     */
    public function labResults(Request $request)
    {
        $query = LabResult::where('tenant_id', $this->getTenantId())
            ->with(['labOrder.patient', 'labOrder.labTest']);

        if ($request->filled('patient_id')) {
            $query->whereHas('labOrder', function ($q) use ($request) {
                $q->where('patient_id', $request->patient_id);
            });
        }

        if ($request->filled('is_critical')) {
            $query->where('is_critical', $request->boolean('is_critical'));
        }

        $results = $query->latest('result_date')->paginate($request->get('per_page', 20));

        return $this->success($results);
    }

    /**
     * Get lab result detail
     */
    public function labResult($id)
    {
        $result = LabResult::where('tenant_id', $this->getTenantId())
            ->with(['labOrder.patient', 'labOrder.labTest'])
            ->findOrFail($id);

        return $this->success($result);
    }

    /**
     * Create lab result
     */
    public function createLabResult(Request $request)
    {
        $validated = $request->validate([
            'lab_order_id' => 'required|exists:lab_orders,id',
            'result_value' => 'required|string',
            'unit' => 'nullable|string',
            'reference_range' => 'nullable|string',
            'is_critical' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $result = LabResult::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'result_date' => now(),
        ]));

        return $this->success($result, 'Lab result created successfully', 201);
    }

    /**
     * Get prescriptions
     */
    public function prescriptions(Request $request)
    {
        $query = Prescription::where('tenant_id', $this->getTenantId())
            ->with(['patient', 'doctor']);

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        $prescriptions = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success($prescriptions);
    }

    /**
     * Get prescription detail
     */
    public function prescription($id)
    {
        $prescription = Prescription::where('tenant_id', $this->getTenantId())
            ->with(['patient', 'doctor'])
            ->findOrFail($id);

        return $this->success($prescription);
    }

    /**
     * Create prescription
     */
    public function createPrescription(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'medications' => 'required|array',
            'diagnosis' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $prescription = Prescription::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'prescription_date' => now(),
        ]));

        return $this->success($prescription, 'Prescription created successfully', 201);
    }

    /**
     * Get EMR for patient
     */
    public function getEmr($patientId)
    {
        $emr = Emr::where('tenant_id', $this->getTenantId())
            ->where('patient_id', $patientId)
            ->with(['patient', 'doctor', 'appointment'])
            ->latest()
            ->get();

        return $this->success($emr);
    }

    /**
     * Create EMR entry
     */
    public function createEmr(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'chief_complaint' => 'required|string',
            'diagnosis' => 'nullable|string',
            'treatment' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $emr = Emr::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'visit_date' => now(),
        ]));

        return $this->success($emr, 'EMR entry created successfully', 201);
    }

    /**
     * Get admissions
     */
    public function admissions(Request $request)
    {
        $query = Admission::where('tenant_id', $this->getTenantId())
            ->with(['patient', 'doctor', 'bed']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $admissions = $query->latest('admission_date')->paginate($request->get('per_page', 20));

        return $this->success($admissions);
    }

    /**
     * Create admission
     */
    public function createAdmission(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'bed_id' => 'required|exists:beds,id',
            'diagnosis' => 'required|string',
            'admission_type' => 'required|in:emergency,elective,maternal',
        ]);

        $admission = Admission::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'admission_date' => now(),
            'status' => 'admitted',
        ]));

        return $this->success($admission, 'Patient admitted successfully', 201);
    }

    /**
     * Discharge patient
     */
    public function dischargePatient(Request $request, $id)
    {
        $admission = Admission::where('tenant_id', $this->getTenantId())->findOrFail($id);

        $validated = $request->validate([
            'discharge_notes' => 'nullable|string',
            'discharge_diagnosis' => 'nullable|string',
        ]);

        $admission->update(array_merge($validated, [
            'discharge_date' => now(),
            'status' => 'discharged',
        ]));

        return $this->success($admission, 'Patient discharged successfully');
    }

    /**
     * Get bed availability
     */
    public function bedAvailability(Request $request)
    {
        $query = Bed::where('tenant_id', $this->getTenantId())
            ->with(['ward']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('ward_id')) {
            $query->where('ward_id', $request->ward_id);
        }

        $beds = $query->get();

        return $this->success([
            'total' => $beds->count(),
            'available' => $beds->where('status', 'available')->count(),
            'occupied' => $beds->where('status', 'occupied')->count(),
            'maintenance' => $beds->where('status', 'maintenance')->count(),
            'beds' => $beds,
        ]);
    }

    /**
     * Get bed detail
     */
    public function bedDetail($id)
    {
        $bed = Bed::where('tenant_id', $this->getTenantId())
            ->with(['ward', 'currentAdmission'])
            ->findOrFail($id);

        return $this->success($bed);
    }

    // ═══════════════════════════════════════════════════════════
    // LABORATORY APIs (4 endpoints)
    // ═══════════════════════════════════════════════════════════

    /**
     * GET /api/healthcare/lab-orders/{id}/results
     * Get all lab results for a specific lab order
     */
    public function getLabOrderResults($orderId)
    {
        $labOrder = LabOrder::where('tenant_id', $this->getTenantId())
            ->with(['patient', 'doctor', 'exam'])
            ->findOrFail($orderId);

        $results = LabResult::where('lab_order_id', $orderId)
            ->with(['test', 'verifiedBy', 'sample'])
            ->get();

        return $this->success([
            'order' => $labOrder,
            'results' => $results,
            'total_results' => $results->count(),
            'verified_count' => $results->where('is_verified', true)->count(),
            'pending_count' => $results->where('is_verified', false)->count(),
            'critical_count' => $results->where('is_critical', true)->count(),
        ]);
    }

    /**
     * POST /api/healthcare/lab-results/{id}/approve
     * Approve/verify a lab result
     */
    public function approveLabResult(Request $request, $id)
    {
        $result = LabResult::where('tenant_id', $this->getTenantId())
            ->with(['order.patient'])
            ->findOrFail($id);

        if ($result->is_verified) {
            throw ValidationException::withMessages([
                'message' => 'Lab result already verified',
            ]);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $result->update([
            'is_verified' => true,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'notes' => $validated['notes'] ?? $result->notes,
        ]);

        // Notify patient and ordering doctor
        if ($result->order && $result->order->patient) {
            // TODO: Send notification
        }

        return $this->success($result, 'Lab result approved successfully');
    }

    /**
     * GET /api/healthcare/lab-equipment/calibration-due
     * Get lab equipment due for calibration
     */
    public function getLabEquipmentCalibrationDue(Request $request)
    {
        $daysThreshold = $request->get('days', 30);
        $dueDate = now()->addDays($daysThreshold);

        $equipment = LabEquipment::where('tenant_id', $this->getTenantId())
            ->where('is_active', true)
            ->where(function ($query) use ($dueDate) {
                $query->whereNull('last_calibration_date')
                    ->orWhere('next_calibration_date', '<=', $dueDate);
            })
            ->with(['department'])
            ->orderBy('next_calibration_date', 'asc')
            ->get();

        return $this->success([
            'equipment' => $equipment,
            'total_due' => $equipment->count(),
            'overdue' => $equipment->where('next_calibration_date', '<', now())->count(),
            'due_within_30_days' => $equipment->whereBetween('next_calibration_date', [now(), $dueDate])->count(),
        ]);
    }

    /**
     * POST /api/healthcare/lab-samples/{id}/process
     * Process a lab sample (update status)
     */
    public function processLabSample(Request $request, $id)
    {
        $sample = LabSample::where('tenant_id', $this->getTenantId())
            ->with(['labOrder.patient', 'labOrder'])
            ->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:collected,in_transit,received,processing,completed,rejected',
            'processed_by' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:1000',
            'rejection_reason' => 'nullable|required_if:status,rejected|string|max:500',
        ]);

        $updateData = [
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $sample->notes,
        ];

        if ($validated['status'] === 'received') {
            $updateData['received_at'] = now();
            $updateData['received_by'] = auth()->id();
        }

        if ($validated['status'] === 'processing') {
            $updateData['processing_started_at'] = now();
            $updateData['processed_by'] = $validated['processed_by'] ?? auth()->id();
        }

        if ($validated['status'] === 'completed') {
            $updateData['processing_completed_at'] = now();
        }

        if ($validated['status'] === 'rejected') {
            $updateData['rejection_reason'] = $validated['rejection_reason'];
            $updateData['rejected_at'] = now();
            $updateData['rejected_by'] = auth()->id();
        }

        $sample->update($updateData);

        return $this->success($sample, 'Lab sample processed successfully');
    }

    // ═══════════════════════════════════════════════════════════
    // RADIOLOGY APIs (3 endpoints)
    // ═══════════════════════════════════════════════════════════

    /**
     * GET /api/healthcare/radiology-exams/{id}/images
     * Get images for a radiology exam
     */
    public function getRadiologyExamImages($examId)
    {
        $exam = RadiologyExam::where('tenant_id', $this->getTenantId())
            ->findOrFail($examId);

        $images = RadiologyImage::where('radiology_exam_id', $examId)
            ->orderBy('sequence', 'asc')
            ->get();

        return $this->success([
            'exam' => $exam,
            'images' => $images,
            'total_images' => $images->count(),
        ]);
    }

    /**
     * POST /api/healthcare/radiology-reports/{id}/finalize
     * Finalize a radiology report
     */
    public function finalizeRadiologyReport(Request $request, $id)
    {
        $report = RadiologyResult::where('tenant_id', $this->getTenantId())
            ->with(['order.patient', 'order.exam'])
            ->findOrFail($id);

        if ($report->status === 'finalized') {
            throw ValidationException::withMessages([
                'message' => 'Report already finalized',
            ]);
        }

        $validated = $request->validate([
            'findings' => 'required|string',
            'impression' => 'required|string',
            'recommendations' => 'nullable|string',
        ]);

        DB::transaction(function () use ($report, $validated) {
            $report->update([
                'findings' => $validated['findings'],
                'impression' => $validated['impression'],
                'recommendations' => $validated['recommendations'] ?? null,
                'status' => 'finalized',
                'finalized_by' => auth()->id(),
                'finalized_at' => now(),
            ]);

            // Update order status
            if ($report->order) {
                $report->order->update(['status' => 'completed']);
            }
        });

        return $this->success($report, 'Radiology report finalized successfully');
    }

    /**
     * GET /api/healthcare/pacs/studies
     * Get PACS studies with filters
     */
    public function getPacsStudies(Request $request)
    {
        $query = \App\Models\PacsStudy::where('tenant_id', $this->getTenantId())
            ->with(['patient', 'radiologyExam', 'radiologist']);

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('modality')) {
            $query->where('modality', $request->modality);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('study_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('study_date', '<=', $request->date_to);
        }

        $studies = $query->latest('study_date')
            ->paginate($request->get('per_page', 20));

        return $this->success($studies);
    }

    // ═══════════════════════════════════════════════════════════
    // SURGERY APIs (3 endpoints)
    // ═══════════════════════════════════════════════════════════

    /**
     * POST /api/healthcare/surgery-schedules/{id}/assign-team
     * Assign surgical team to a surgery schedule
     */
    public function assignSurgeryTeam(Request $request, $id)
    {
        $schedule = SurgerySchedule::where('tenant_id', $this->getTenantId())
            ->with(['patient', 'surgeon', 'operatingRoom'])
            ->findOrFail($id);

        if ($schedule->status !== 'scheduled') {
            throw ValidationException::withMessages([
                'message' => 'Can only assign team to scheduled surgeries',
            ]);
        }

        $validated = $request->validate([
            'team_members' => 'required|array',
            'team_members.*.user_id' => 'required|exists:users,id',
            'team_members.*.role' => 'required|in:surgeon,assistant,anesthesiologist,nurse,technician',
        ]);

        DB::transaction(function () use ($schedule, $validated) {
            // Remove existing team
            SurgeryTeam::where('surgery_schedule_id', $schedule->id)->delete();

            // Add new team members
            foreach ($validated['team_members'] as $member) {
                SurgeryTeam::create([
                    'surgery_schedule_id' => $schedule->id,
                    'user_id' => $member['user_id'],
                    'role' => $member['role'],
                    'tenant_id' => $this->getTenantId(),
                ]);
            }
        });

        $schedule->load('team.user');

        return $this->success($schedule, 'Surgical team assigned successfully');
    }

    /**
     * GET /api/healthcare/operating-rooms/availability
     * Get operating room availability
     */
    public function getOperatingRoomsAvailability(Request $request)
    {
        $query = OperatingRoom::where('tenant_id', $this->getTenantId())
            ->where('is_active', true);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $rooms = $query->with(['currentSurgery', 'upcomingSurgery'])->get();

        $availability = $rooms->map(function ($room) {
            return [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'room_name' => $room->room_name,
                'status' => $room->status,
                'is_available' => $room->status === 'available',
                'current_surgery' => $room->currentSurgery ? [
                    'id' => $room->currentSurgery->id,
                    'patient' => $room->currentSurgery->patient->full_name ?? 'Unknown',
                    'started_at' => $room->currentSurgery->actual_start_time,
                ] : null,
                'next_surgery' => $room->upcomingSurgery->first() ? [
                    'id' => $room->upcomingSurgery->first()->id,
                    'patient' => $room->upcomingSurgery->first()->patient->full_name ?? 'Unknown',
                    'scheduled_at' => $room->upcomingSurgery->first()->scheduled_start_time,
                ] : null,
            ];
        });

        return $this->success([
            'rooms' => $availability,
            'total' => $rooms->count(),
            'available' => $rooms->where('status', 'available')->count(),
            'occupied' => $rooms->where('status', 'occupied')->count(),
            'maintenance' => $rooms->where('status', 'maintenance')->count(),
        ]);
    }

    /**
     * POST /api/healthcare/surgery-schedules/{id}/complete
     * Complete a surgery schedule
     */
    public function completeSurgery(Request $request, $id)
    {
        $schedule = SurgerySchedule::where('tenant_id', $this->getTenantId())
            ->with(['patient', 'surgeon', 'operatingRoom'])
            ->findOrFail($id);

        if (!in_array($schedule->status, ['in_progress', 'scheduled'])) {
            throw ValidationException::withMessages([
                'message' => 'Can only complete in-progress or scheduled surgeries',
            ]);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:2000',
            'complications' => 'nullable|string|max:2000',
            'estimated_blood_loss' => 'nullable|numeric|min:0',
            'specimens_collected' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($schedule, $validated) {
            $schedule->update([
                'status' => 'completed',
                'actual_end_time' => now(),
                'surgery_notes' => $validated['notes'] ?? $schedule->surgery_notes,
                'complications' => $validated['complications'] ?? null,
                'estimated_blood_loss' => $validated['estimated_blood_loss'] ?? null,
                'specimens_collected' => $validated['specimens_collected'] ?? false,
            ]);

            // Free up operating room
            if ($schedule->operatingRoom) {
                $schedule->operatingRoom->update(['status' => 'available']);
            }

            // Free up team members
            $schedule->team()->delete();
        });

        return $this->success($schedule, 'Surgery completed successfully');
    }

    // ═══════════════════════════════════════════════════════════
    // PHARMACY APIs (3 endpoints)
    // ═══════════════════════════════════════════════════════════

    /**
     * POST /api/healthcare/prescriptions/{id}/dispense
     * Dispense a prescription
     */
    public function dispensePrescription(Request $request, $id)
    {
        $prescription = Prescription::where('tenant_id', $this->getTenantId())
            ->with(['patient', 'doctor', 'items'])
            ->findOrFail($id);

        if ($prescription->status === 'dispensed') {
            throw ValidationException::withMessages([
                'message' => 'Prescription already dispensed',
            ]);
        }

        if ($prescription->status === 'cancelled') {
            throw ValidationException::withMessages([
                'message' => 'Cannot dispense cancelled prescription',
            ]);
        }

        $validated = $request->validate([
            'dispensed_by' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:1000',
            'partial_dispense' => 'nullable|boolean',
            'items_dispensed' => 'nullable|array',
            'items_dispensed.*.item_id' => 'required_with:items_dispensed|exists:prescription_items,id',
            'items_dispensed.*.quantity' => 'required_with:items_dispensed|integer|min:1',
        ]);

        DB::transaction(function () use ($prescription, $validated) {
            $updateData = [
                'status' => $validated['partial_dispense'] ?? false ? 'partially_dispensed' : 'dispensed',
                'dispensed_at' => now(),
                'dispensed_by' => $validated['dispensed_by'] ?? auth()->id(),
                'dispensing_notes' => $validated['notes'] ?? null,
            ];

            $prescription->update($updateData);

            // Update prescription items and inventory
            if (!empty($validated['items_dispensed'])) {
                foreach ($validated['items_dispensed'] as $itemData) {
                    $item = $prescription->items()->findOrFail($itemData['item_id']);

                    $item->update([
                        'quantity_dispensed' => $itemData['quantity'],
                        'dispensed_at' => now(),
                    ]);

                    // Deduct from pharmacy inventory
                    if ($item->medicine) {
                        PharmacyInventory::where('medicine_id', $item->medicine_id)
                            ->decrement('quantity', $itemData['quantity']);
                    }
                }
            } else {
                // Mark all items as dispensed
                $prescription->items()->update([
                    'quantity_dispensed' => DB::raw('quantity_prescribed'),
                    'dispensed_at' => now(),
                ]);
            }
        });

        return $this->success($prescription, 'Prescription dispensed successfully');
    }

    /**
     * GET /api/healthcare/medications/expiring
     * Get medications expiring soon
     */
    public function getExpiringMedications(Request $request)
    {
        $daysThreshold = $request->get('days', 90);
        $expiryDate = now()->addDays($daysThreshold);

        $medications = Medicine::where('tenant_id', $this->getTenantId())
            ->where('is_active', true)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', $expiryDate)
            ->with(['category', 'supplier'])
            ->orderBy('expiry_date', 'asc')
            ->get();

        $inventory = PharmacyInventory::whereIn('medicine_id', $medications->pluck('id'))
            ->where('quantity', '>', 0)
            ->with(['medicine', 'warehouse'])
            ->get();

        return $this->success([
            'medications' => $medications,
            'inventory' => $inventory,
            'total_expiring' => $medications->count(),
            'expired' => $medications->where('expiry_date', '<', now())->count(),
            'expiring_within_30_days' => $medications->whereBetween('expiry_date', [now(), now()->addDays(30)])->count(),
            'expiring_within_90_days' => $medications->whereBetween('expiry_date', [now()->addDays(30), $expiryDate])->count(),
        ]);
    }

    /**
     * POST /api/healthcare/pharmacy/stock-opname
     * Create pharmacy stock opname (stock count)
     */
    public function createPharmacyStockOpname(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.warehouse_id' => 'nullable|exists:warehouses,id',
            'items.*.physical_quantity' => 'required|integer|min:0',
            'items.*.notes' => 'nullable|string|max:500',
            'opname_date' => 'nullable|date',
            'performed_by' => 'nullable|exists:users,id',
        ]);

        DB::transaction(function () use ($validated) {
            $results = [];

            foreach ($validated['items'] as $itemData) {
                $inventory = PharmacyInventory::where('medicine_id', $itemData['medicine_id'])
                    ->where('warehouse_id', $itemData['warehouse_id'] ?? null)
                    ->where('tenant_id', $this->getTenantId())
                    ->first();

                if (!$inventory) {
                    continue;
                }

                $systemQuantity = $inventory->quantity;
                $physicalQuantity = $itemData['physical_quantity'];
                $difference = $physicalQuantity - $systemQuantity;

                // Update inventory
                $inventory->update([
                    'quantity' => $physicalQuantity,
                    'last_stock_check' => $validated['opname_date'] ?? now(),
                    'stock_check_by' => $validated['performed_by'] ?? auth()->id(),
                ]);

                // Log discrepancy if any
                if ($difference !== 0) {
                    \App\Models\StockOpnameSession::create([
                        'medicine_id' => $itemData['medicine_id'],
                        'warehouse_id' => $itemData['warehouse_id'] ?? null,
                        'system_quantity' => $systemQuantity,
                        'physical_quantity' => $physicalQuantity,
                        'difference' => $difference,
                        'notes' => $itemData['notes'] ?? null,
                        'opname_date' => $validated['opname_date'] ?? now(),
                        'performed_by' => $validated['performed_by'] ?? auth()->id(),
                        'tenant_id' => $this->getTenantId(),
                    ]);
                }

                $results[] = [
                    'medicine_id' => $itemData['medicine_id'],
                    'system_quantity' => $systemQuantity,
                    'physical_quantity' => $physicalQuantity,
                    'difference' => $difference,
                    'updated' => true,
                ];
            }

            return $results;
        });

        return $this->success(null, 'Pharmacy stock opname completed successfully');
    }

    // ═══════════════════════════════════════════════════════════
    // INPATIENT APIs (3 endpoints)
    // ═══════════════════════════════════════════════════════════

    /**
     * POST /api/healthcare/admissions/{id}/transfer-ward
     * Transfer patient to different ward/bed
     */
    public function transferWard(Request $request, $id)
    {
        $admission = Admission::where('tenant_id', $this->getTenantId())
            ->with(['patient', 'bed.ward'])
            ->findOrFail($id);

        if ($admission->status !== 'admitted') {
            throw ValidationException::withMessages([
                'message' => 'Can only transfer admitted patients',
            ]);
        }

        $validated = $request->validate([
            'new_ward_id' => 'required|exists:wards,id',
            'new_bed_id' => 'required|exists:beds,id',
            'transfer_reason' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:2000',
        ]);

        // Check if new bed is available
        $newBed = Bed::where('id', $validated['new_bed_id'])
            ->where('status', 'available')
            ->first();

        if (!$newBed) {
            throw ValidationException::withMessages([
                'message' => 'Requested bed is not available',
            ]);
        }

        DB::transaction(function () use ($admission, $validated, $newBed) {
            $oldBed = $admission->bed;

            // Update admission
            $admission->update([
                'ward_id' => $validated['new_ward_id'],
                'bed_id' => $validated['new_bed_id'],
                'transfer_reason' => $validated['transfer_reason'],
                'admission_notes' => ($admission->admission_notes . "\n[Transfer] " . $validated['notes']) ?? null,
            ]);

            // Free old bed
            if ($oldBed) {
                $oldBed->update(['status' => 'available']);
            }

            // Occupy new bed
            $newBed->update(['status' => 'occupied']);
        });

        $admission->load(['bed.ward', 'patient']);

        return $this->success($admission, 'Patient transferred successfully');
    }

    /**
     * GET /api/healthcare/beds/availability
     * Get bed availability with detailed info
     */
    public function getBedAvailability(Request $request)
    {
        $query = Bed::where('tenant_id', $this->getTenantId())
            ->with(['ward', 'currentAdmission.patient']);

        if ($request->filled('ward_id')) {
            $query->where('ward_id', $request->ward_id);
        }

        if ($request->filled('bed_type')) {
            $query->where('bed_type', $request->bed_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('gender')) {
            $query->where('gender_specific', '!=', 'both')
                ->where(function ($q) use ($request) {
                    $q->where('gender_specific', $request->gender)
                        ->orWhere('gender_specific', 'both');
                });
        }

        $beds = $query->get();

        $grouped = $beds->groupBy('ward_id')->map(function ($wardBeds) {
            return [
                'total' => $wardBeds->count(),
                'available' => $wardBeds->where('status', 'available')->count(),
                'occupied' => $wardBeds->where('status', 'occupied')->count(),
                'maintenance' => $wardBeds->where('status', 'maintenance')->count(),
                'beds' => $wardBeds,
            ];
        });

        return $this->success([
            'summary' => [
                'total' => $beds->count(),
                'available' => $beds->where('status', 'available')->count(),
                'occupied' => $beds->where('status', 'occupied')->count(),
                'maintenance' => $beds->where('status', 'maintenance')->count(),
                'occupancy_rate' => $beds->count() > 0
                    ? round(($beds->where('status', 'occupied')->count() / $beds->count()) * 100, 2)
                    : 0,
            ],
            'by_ward' => $grouped,
            'beds' => $beds,
        ]);
    }

    /**
     * POST /api/healthcare/admissions/{id}/discharge
     * Discharge a patient (enhanced version)
     */
    public function dischargeAdmission(Request $request, $id)
    {
        $admission = Admission::where('tenant_id', $this->getTenantId())
            ->with(['patient', 'bed', 'doctor'])
            ->findOrFail($id);

        if ($admission->status !== 'admitted') {
            throw ValidationException::withMessages([
                'message' => 'Can only discharge admitted patients',
            ]);
        }

        $validated = $request->validate([
            'discharge_diagnosis' => 'required|string',
            'discharge_notes' => 'nullable|string|max:3000',
            'discharge_type' => 'required|in:regular,ama,referral,transfer,deceased',
            'discharge_condition' => 'nullable|in:improved,stable,worse,deceased',
            'follow_up_required' => 'nullable|boolean',
            'follow_up_date' => 'nullable|date|after:today',
            'medications_on_discharge' => 'nullable|string',
            'restrictions' => 'nullable|string',
        ]);

        DB::transaction(function () use ($admission, $validated) {
            $admission->update([
                'status' => 'discharged',
                'actual_discharge_date' => now(),
                'discharge_diagnosis' => $validated['discharge_diagnosis'],
                'discharge_summary' => $validated['discharge_notes'] ?? null,
                'discharge_type' => $validated['discharge_type'],
                'discharge_status' => $validated['discharge_condition'] ?? 'stable',
                'requires_follow_up' => $validated['follow_up_required'] ?? false,
                'follow_up_date' => $validated['follow_up_date'] ?? null,
                'discharge_medications' => $validated['medications_on_discharge'] ?? null,
                'discharge_restrictions' => $validated['restrictions'] ?? null,
            ]);

            // Free up bed
            if ($admission->bed) {
                // Mark bed for cleaning
                $admission->bed->update(['status' => 'maintenance']);
            }
        });

        $admission->load(['patient', 'bed.ward']);

        return $this->success($admission, 'Patient discharged successfully');
    }
}
