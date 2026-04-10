<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Healthcare\PatientController;
use App\Http\Controllers\Healthcare\DoctorController;
use App\Http\Controllers\Healthcare\AppointmentController;
use App\Http\Controllers\Healthcare\EMRController;
use App\Http\Controllers\Healthcare\AdmissionController;
use App\Http\Controllers\Healthcare\BedManagementController;
use App\Http\Controllers\Healthcare\QueueController;
// use App\Http\Controllers\Healthcare\OutpatientController; // Not yet created
use App\Http\Controllers\Healthcare\TriageController;
use App\Http\Controllers\Healthcare\EmergencyController;
use App\Http\Controllers\Healthcare\PharmacyController;
use App\Http\Controllers\Healthcare\LaboratoryController;
use App\Http\Controllers\Healthcare\RadiologyController;
use App\Http\Controllers\Healthcare\BillingController;
use App\Http\Controllers\Healthcare\TelemedicineController;
use App\Http\Controllers\Healthcare\TelemedicineSettingsController;
use App\Http\Controllers\Healthcare\SurgeryController;
use App\Http\Controllers\Healthcare\ResourceController;
use App\Http\Controllers\Healthcare\InventoryController;
use App\Http\Controllers\Healthcare\AnalyticsController;
use App\Http\Controllers\Healthcare\ComplianceController;
use App\Http\Controllers\Healthcare\IntegrationController;
use App\Http\Controllers\Healthcare\PatientPortalController;

// Task 8: Additional Controllers
use App\Http\Controllers\Healthcare\WardController;
use App\Http\Controllers\Healthcare\BedController;
use App\Http\Controllers\Healthcare\TriageAssessmentController;
use App\Http\Controllers\Healthcare\QueueManagementController;
use App\Http\Controllers\Healthcare\LabTestCatalogController;
use App\Http\Controllers\Healthcare\LabResultController;
use App\Http\Controllers\Healthcare\RadiologyExamController;
use App\Http\Controllers\Healthcare\InsuranceClaimController;
use App\Http\Controllers\Healthcare\TeleconsultationController;
use App\Http\Controllers\Healthcare\SurgeryScheduleController;
use App\Http\Controllers\Healthcare\MedicalEquipmentController;
use App\Http\Controllers\Healthcare\MedicalSupplyController;
use App\Http\Controllers\Healthcare\SterilizationController;
use App\Http\Controllers\Healthcare\MedicalWasteController;
use App\Http\Controllers\Healthcare\AuditTrailController;
use App\Http\Controllers\Healthcare\ComplianceReportController;
use App\Http\Controllers\Healthcare\BackupController;
use App\Http\Controllers\Healthcare\HL7Controller;
use App\Http\Controllers\Healthcare\BPJSClaimController;
use App\Http\Controllers\Healthcare\LabEquipmentController;
use App\Http\Controllers\Healthcare\NotificationController;
use App\Http\Controllers\Healthcare\MedicalCertificateController;
use App\Http\Controllers\Healthcare\HealthEducationController;
use App\Http\Controllers\Healthcare\PatientMessageController;
use App\Http\Controllers\Healthcare\AnalyticsDashboardController;
use App\Http\Controllers\Healthcare\FinancialReportController;
use App\Http\Controllers\Healthcare\ClinicalQualityController;
use App\Http\Controllers\Healthcare\MinistryReportController;
use App\Http\Controllers\Healthcare\TrendAnalysisController;
use App\Http\Controllers\Healthcare\PatientSatisfactionController;

/*
|--------------------------------------------------------------------------
| Healthcare Module Routes
|--------------------------------------------------------------------------
|
| All routes for the Healthcare/ERP Medical module
| Organized by functional area
|
*/

Route::middleware(['auth', 'verified'])->prefix('healthcare')->name('healthcare.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Patient Management Routes (15 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('/', [PatientController::class, 'index'])->name('index');
        Route::get('/create', [PatientController::class, 'create'])->name('create');
        Route::post('/', [PatientController::class, 'store'])->name('store');
        Route::get('/{id}', [PatientController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PatientController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PatientController::class, 'update'])->name('update');
        Route::delete('/{id}', [PatientController::class, 'destroy'])->name('destroy');
        Route::get('/search', [PatientController::class, 'search'])->name('search');
        Route::get('/{id}/medical-records', [PatientController::class, 'medicalRecords'])->name('medical-records');
        Route::get('/{id}/visits', [PatientController::class, 'visits'])->name('visits');
        Route::get('/{id}/appointments', [PatientController::class, 'appointments'])->name('appointments');
        Route::get('/{id}/prescriptions', [PatientController::class, 'prescriptions'])->name('prescriptions');
        Route::get('/{id}/lab-results', [PatientController::class, 'labResults'])->name('lab-results');
        Route::get('/{id}/timeline', [PatientController::class, 'timeline'])->name('timeline');
        Route::get('/qr/{qr_code}', [PatientController::class, 'scanQR'])->name('qr.scan');
    });

    /*
    |--------------------------------------------------------------------------
    | Doctor & Scheduling Routes (12 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('doctors')->name('doctors.')->group(function () {
        Route::get('/', [DoctorController::class, 'index'])->name('index');
        Route::get('/{id}', [DoctorController::class, 'show'])->name('show');
        Route::get('/{id}/schedule', [DoctorController::class, 'schedule'])->name('schedule');
        Route::post('/{id}/schedule', [DoctorController::class, 'storeSchedule'])->name('schedule.store');
        Route::get('/{id}/appointments', [DoctorController::class, 'appointments'])->name('appointments');
    });

    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::get('/', [AppointmentController::class, 'index'])->name('index');
        Route::post('/', [AppointmentController::class, 'store'])->name('store');
        Route::get('/{id}', [AppointmentController::class, 'show'])->name('show');
        Route::put('/{id}', [AppointmentController::class, 'update'])->name('update');
        Route::delete('/{id}', [AppointmentController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/check-in', [AppointmentController::class, 'checkIn'])->name('check-in');
        Route::post('/{id}/cancel', [AppointmentController::class, 'cancel'])->name('cancel');
    });

    /*
    |--------------------------------------------------------------------------
    | EMR (Electronic Medical Records) Routes (10 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('emr')->name('emr.')->group(function () {
        Route::get('/', [EMRController::class, 'index'])->name('index');
        Route::get('/dashboard/{patient_id}', [EMRController::class, 'dashboard'])->name('dashboard');
        Route::get('/{patient_id}/vital-signs-chart', [EMRController::class, 'getVitalSignsChart'])->name('vital-signs-chart');
        Route::get('/{patient_id}', [EMRController::class, 'show'])->name('show');
        Route::post('/', [EMRController::class, 'store'])->name('store');
        Route::put('/{id}', [EMRController::class, 'update'])->name('update');
        Route::get('/{id}/history', [EMRController::class, 'history'])->name('history');
        Route::post('/{id}/diagnosis', [EMRController::class, 'addDiagnosis'])->name('diagnosis');
        Route::post('/{id}/prescription', [EMRController::class, 'addPrescription'])->name('prescription');
        Route::post('/{id}/lab-order', [EMRController::class, 'orderLab'])->name('lab-order');
        Route::get('/{patient_id}/timeline', [EMRController::class, 'getTimeline'])->name('timeline');
        Route::get('/{patient_id}/export', [EMRController::class, 'export'])->name('export');
        Route::get('/visit/{visit_id}/soap-note', [EMRController::class, 'createSOAPNote'])->name('soap-note');
        Route::post('/visit/{visit_id}/soap-note', [EMRController::class, 'createSOAPNote'])->name('soap-note.store');
        Route::get('/search-icd10', [EMRController::class, 'searchICD10'])->name('search-icd10');
        Route::post('/check-drug-interactions', [EMRController::class, 'checkDrugInteractions'])->name('check-drug-interactions');
        Route::get('/prescription/{prescription_id}/print', [EMRController::class, 'printPrescription'])->name('prescription.print');
    });

    /*
    |--------------------------------------------------------------------------
    | Inpatient Management Routes (15 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('inpatient')->name('inpatient.')->group(function () {
        Route::get('/wards', [BedManagementController::class, 'wards'])->name('wards');
        Route::get('/wards/{id}/beds', [BedManagementController::class, 'wardBeds'])->name('wards.beds');

        Route::prefix('admissions')->name('admissions.')->group(function () {
            Route::post('/', [AdmissionController::class, 'store'])->name('store');
            Route::get('/', [AdmissionController::class, 'index'])->name('index');
            Route::get('/{id}', [AdmissionController::class, 'show'])->name('show');
            Route::put('/{id}', [AdmissionController::class, 'update'])->name('update');
            Route::post('/{id}/discharge', [AdmissionController::class, 'discharge'])->name('discharge');
            Route::post('/{id}/transfer', [AdmissionController::class, 'transfer'])->name('transfer');
        });

        Route::get('/bed-management', [BedManagementController::class, 'index'])->name('bed-management');
        Route::post('/bed-management/assign', [BedManagementController::class, 'assignBed'])->name('bed-management.assign');
        Route::post('/bed-management/release', [BedManagementController::class, 'releaseBed'])->name('bed-management.release');

        Route::get('/rounds', [AdmissionController::class, 'rounds'])->name('rounds');
        Route::post('/rounds', [AdmissionController::class, 'recordRounds'])->name('rounds.store');
        Route::get('/occupancy', [BedManagementController::class, 'occupancy'])->name('occupancy');
        Route::get('/dashboard', [AdmissionController::class, 'dashboard'])->name('dashboard');
    });

    /*
    |--------------------------------------------------------------------------
    | Outpatient & Queue Routes (10 routes)
    |--------------------------------------------------------------------------
    */
    // Outpatient - Commented out until controller is created
    // Route::prefix('outpatient')->name('outpatient.')->group(function () {
    //     Route::get('/visits', [OutpatientController::class, 'index'])->name('visits');
    //     Route::post('/visits', [OutpatientController::class, 'store'])->name('visits.store');
    //     Route::get('/dashboard', [OutpatientController::class, 'dashboard'])->name('dashboard');
    // });

    Route::prefix('queue')->name('queue.')->group(function () {
        Route::get('/', [QueueController::class, 'index'])->name('index');
        Route::post('/assign-number', [QueueController::class, 'assignNumber'])->name('assign-number');
        Route::get('/display', [QueueController::class, 'display'])->name('display');
        Route::get('/current', [QueueController::class, 'current'])->name('current');
        Route::post('/call-next', [QueueController::class, 'callNext'])->name('call-next');
        Route::post('/skip', [QueueController::class, 'skip'])->name('skip');
        Route::get('/analytics', [QueueController::class, 'analytics'])->name('analytics');
    });

    /*
    |--------------------------------------------------------------------------
    | ER Management Routes (10 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('er')->name('er.')->group(function () {
        Route::get('/', [EmergencyController::class, 'index'])->name('index');
        Route::post('/triage', [TriageController::class, 'store'])->name('triage');
        Route::get('/triage/{id}', [TriageController::class, 'show'])->name('triage.show');
        Route::post('/triage/{id}/update', [TriageController::class, 'update'])->name('triage.update');
        Route::get('/patients', [EmergencyController::class, 'patients'])->name('patients');
        Route::get('/dashboard', [EmergencyController::class, 'dashboard'])->name('dashboard');
        Route::post('/critical-alerts', [EmergencyController::class, 'createAlert'])->name('critical-alerts');
        Route::get('/critical-alerts', [EmergencyController::class, 'alerts'])->name('alerts');
        Route::get('/throughput', [EmergencyController::class, 'throughput'])->name('throughput');
        Route::post('/admit', [EmergencyController::class, 'admit'])->name('admit');
    });

    /*
    |--------------------------------------------------------------------------
    | Pharmacy Routes (12 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('pharmacy')->name('pharmacy.')->group(function () {
        Route::get('/', [PharmacyController::class, 'index'])->name('index');
        Route::get('/inventory', [PharmacyController::class, 'inventory'])->name('inventory');
        Route::post('/inventory', [PharmacyController::class, 'storeInventory'])->name('inventory.store');
        Route::get('/prescriptions', [PharmacyController::class, 'prescriptions'])->name('prescriptions');
        Route::get('/prescriptions/{id}', [PharmacyController::class, 'showPrescription'])->name('prescriptions.show');
        Route::post('/prescriptions/{id}/dispense', [PharmacyController::class, 'dispense'])->name('prescriptions.dispense');
        Route::post('/prescriptions/{id}/verify', [PharmacyController::class, 'verify'])->name('prescriptions.verify');
        Route::get('/stock-alerts', [PharmacyController::class, 'stockAlerts'])->name('stock-alerts');
        Route::get('/expiring-soon', [PharmacyController::class, 'expiringSoon'])->name('expiring-soon');
        Route::post('/stock-opname', [PharmacyController::class, 'stockOpname'])->name('stock-opname');
        Route::get('/dashboard', [PharmacyController::class, 'dashboard'])->name('dashboard');
        Route::get('/reports', [PharmacyController::class, 'reports'])->name('reports');
    });

    /*
    |--------------------------------------------------------------------------
    | Laboratory Routes (12 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('laboratory')->name('laboratory.')->group(function () {
        Route::get('/', [LaboratoryController::class, 'index'])->name('index');
        Route::get('/tests', [LaboratoryController::class, 'tests'])->name('tests');
        Route::post('/orders', [LaboratoryController::class, 'storeOrder'])->name('orders.store');
        Route::get('/orders', [LaboratoryController::class, 'orders'])->name('orders');
        Route::get('/orders/{id}', [LaboratoryController::class, 'showOrder'])->name('orders.show');
        Route::post('/orders/{id}/collect-sample', [LaboratoryController::class, 'collectSample'])->name('orders.collect-sample');
        Route::post('/orders/{id}/enter-results', [LaboratoryController::class, 'enterResults'])->name('orders.enter-results');
        Route::post('/orders/{id}/validate', [LaboratoryController::class, 'validateResults'])->name('orders.validate');
        Route::get('/results', [LaboratoryController::class, 'results'])->name('results');
        Route::get('/results/{id}', [LaboratoryController::class, 'showResult'])->name('results.show');
        Route::get('/dashboard', [LaboratoryController::class, 'dashboard'])->name('dashboard');
        Route::get('/equipment', [LaboratoryController::class, 'equipment'])->name('equipment');
    });

    /*
    |--------------------------------------------------------------------------
    | Radiology Routes (10 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('radiology')->name('radiology.')->group(function () {
        Route::get('/', [RadiologyController::class, 'index'])->name('index');
        Route::post('/exams', [RadiologyController::class, 'storeExam'])->name('exams.store');
        Route::get('/exams', [RadiologyController::class, 'exams'])->name('exams');
        Route::get('/exams/{id}', [RadiologyController::class, 'showExam'])->name('exams.show');
        Route::post('/exams/{id}/upload-images', [RadiologyController::class, 'uploadImages'])->name('exams.upload-images');
        Route::get('/pacs/{study_id}', [RadiologyController::class, 'pacsViewer'])->name('pacs');
        Route::post('/exams/{id}/report', [RadiologyController::class, 'addReport'])->name('exams.report');
        Route::get('/dashboard', [RadiologyController::class, 'dashboard'])->name('dashboard');
        Route::get('/schedule', [RadiologyController::class, 'schedule'])->name('schedule');
        Route::get('/reports', [RadiologyController::class, 'reports'])->name('reports');
    });

    /*
    |--------------------------------------------------------------------------
    | Medical Billing Routes (12 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/', [BillingController::class, 'index'])->name('index');
        Route::get('/invoices', [BillingController::class, 'invoices'])->name('invoices');
        Route::post('/invoices', [BillingController::class, 'storeInvoice'])->name('invoices.store');
        Route::get('/invoices/{id}', [BillingController::class, 'showInvoice'])->name('invoices.show');
        Route::post('/invoices/{id}/pay', [BillingController::class, 'payInvoice'])->name('invoices.pay');
        Route::get('/insurance-claims', [BillingController::class, 'insuranceClaims'])->name('insurance-claims');
        Route::post('/insurance-claims', [BillingController::class, 'storeClaim'])->name('insurance-claims.store');
        Route::get('/insurance-claims/{id}', [BillingController::class, 'showClaim'])->name('insurance-claims.show');
        Route::post('/insurance-claims/{id}/submit', [BillingController::class, 'submitClaim'])->name('insurance-claims.submit');
        Route::get('/payment-plans', [BillingController::class, 'paymentPlans'])->name('payment-plans');
        Route::get('/dashboard', [BillingController::class, 'dashboard'])->name('dashboard');
        Route::get('/reports', [BillingController::class, 'reports'])->name('reports');
    });

    /*
    |--------------------------------------------------------------------------
    | Telemedicine Routes (10 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('telemedicine')->name('telemedicine.')->group(function () {
        Route::get('/', [TelemedicineController::class, 'index'])->name('index');
        Route::post('/book', [TelemedicineController::class, 'book'])->name('book');
        Route::get('/consultations', [TelemedicineController::class, 'consultations'])->name('consultations');
        Route::get('/consultations/{id}', [TelemedicineController::class, 'showConsultation'])->name('consultations.show');
        Route::get('/consultations/{id}/join', [TelemedicineController::class, 'join'])->name('consultations.join');
        Route::post('/consultations/{id}/start', [TelemedicineController::class, 'start'])->name('consultations.start');
        Route::post('/consultations/{id}/end', [TelemedicineController::class, 'end'])->name('consultations.end');
        Route::post('/consultations/{id}/prescription', [TelemedicineController::class, 'addPrescription'])->name('consultations.prescription');
        Route::post('/consultations/{id}/feedback', [TelemedicineController::class, 'addFeedback'])->name('consultations.feedback');
        Route::get('/dashboard', [TelemedicineController::class, 'dashboard'])->name('dashboard');

        // Payment Routes
        Route::get('/consultations/{id}/payment', [TelemedicineController::class, 'showPayment'])->name('payment.show');
        Route::post('/consultations/{id}/payment', [TelemedicineController::class, 'processPayment'])->name('payment.process');
        Route::post('/consultations/{id}/refund', [TelemedicineController::class, 'processRefund'])->name('payment.refund');
        Route::get('/payment/finish', [TelemedicineController::class, 'paymentFinish'])->name('payment.finish');
        Route::get('/payment/pending', [TelemedicineController::class, 'paymentPending'])->name('payment.pending');
        Route::get('/payment/error', [TelemedicineController::class, 'paymentError'])->name('payment.error');
        Route::post('/payment/callback/{provider}', [TelemedicineController::class, 'paymentCallback'])->name('payment.callback');

        // Video Integration Routes (Jitsi Meet)
        Route::get('/consultations/{id}/video-room', [TelemedicineController::class, 'videoRoom'])->name('video-room');
        Route::post('/consultations/{id}/generate-token', [TelemedicineController::class, 'generateToken'])->name('generate-token');
        Route::post('/consultations/{id}/start-recording', [TelemedicineController::class, 'startRecording'])->name('start-recording');
        Route::post('/consultations/{id}/stop-recording', [TelemedicineController::class, 'stopRecording'])->name('stop-recording');

        // Feedback Routes
        Route::get('/consultations/{id}/feedback', [TelemedicineController::class, 'showFeedback'])->name('feedback.show');
        Route::post('/consultations/{id}/feedback', [TelemedicineController::class, 'submitFeedback'])->name('feedback.store');
        Route::get('/consultations/{id}/feedback/data', [TelemedicineController::class, 'getFeedback'])->name('feedback.data');

        // Settings Routes
        Route::get('/settings', [TelemedicineSettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [TelemedicineSettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/test-connection', [TelemedicineSettingsController::class, 'testConnection'])->name('settings.test-connection');
        Route::post('/settings/reset', [TelemedicineSettingsController::class, 'resetToDefault'])->name('settings.reset');
    });

    /*
    |--------------------------------------------------------------------------
    | Hospital Resource Routes (10 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('resources')->name('resources.')->group(function () {
        Route::get('/or', [ResourceController::class, 'operatingRooms'])->name('or');
        Route::post('/or/schedule', [ResourceController::class, 'scheduleOR'])->name('or.schedule');
        Route::get('/or/schedule', [ResourceController::class, 'orSchedule'])->name('or.schedule.index');
        Route::post('/surgeries', [ResourceController::class, 'storeSurgery'])->name('surgeries.store');
        Route::get('/surgeries', [ResourceController::class, 'surgeries'])->name('surgeries');
        Route::post('/surgeries/{id}/start', [ResourceController::class, 'startSurgery'])->name('surgeries.start');
        Route::post('/surgeries/{id}/complete', [ResourceController::class, 'completeSurgery'])->name('surgeries.complete');
        Route::get('/equipment', [ResourceController::class, 'equipment'])->name('equipment');
        Route::get('/analytics', [ResourceController::class, 'analytics'])->name('analytics');
        Route::get('/dashboard', [ResourceController::class, 'dashboard'])->name('dashboard');
    });

    /*
    |--------------------------------------------------------------------------
    | Medical Inventory Routes (10 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/supplies', [InventoryController::class, 'supplies'])->name('supplies');
        Route::post('/supplies', [InventoryController::class, 'storeSupply'])->name('supplies.store');
        Route::post('/supplies/{id}/receive', [InventoryController::class, 'receive'])->name('supplies.receive');
        Route::post('/supplies/{id}/issue', [InventoryController::class, 'issue'])->name('supplies.issue');
        Route::get('/expiring-soon', [InventoryController::class, 'expiringSoon'])->name('expiring-soon');
        Route::get('/low-stock', [InventoryController::class, 'lowStock'])->name('low-stock');
        Route::post('/requests', [InventoryController::class, 'storeRequest'])->name('requests.store');
        Route::post('/sterilization', [InventoryController::class, 'recordSterilization'])->name('sterilization');
        Route::get('/dashboard', [InventoryController::class, 'dashboard'])->name('dashboard');
    });

    /*
    |--------------------------------------------------------------------------
    | Reporting & Analytics Routes (10 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::get('/kpi', [AnalyticsController::class, 'kpi'])->name('kpi');
        Route::get('/bor', [AnalyticsController::class, 'bor'])->name('bor');
        Route::get('/alos', [AnalyticsController::class, 'alos'])->name('alos');
        Route::get('/mortality', [AnalyticsController::class, 'mortality'])->name('mortality');
        Route::get('/infection', [AnalyticsController::class, 'infection'])->name('infection');
        Route::get('/financial', [AnalyticsController::class, 'financial'])->name('financial');
        Route::get('/satisfaction', [AnalyticsController::class, 'satisfaction'])->name('satisfaction');
        Route::post('/reports/ministry', [AnalyticsController::class, 'generateMinistryReport'])->name('reports.ministry');
        Route::get('/dashboard', [AnalyticsController::class, 'dashboard'])->name('dashboard');
    });

    /*
    |--------------------------------------------------------------------------
    | Regulatory Compliance Routes (8 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('compliance')->name('compliance.')->group(function () {
        Route::get('/', [ComplianceController::class, 'index'])->name('index');
        Route::get('/audit-trail', [ComplianceController::class, 'auditTrail'])->name('audit-trail');
        Route::post('/backup', [ComplianceController::class, 'createBackup'])->name('backup');
        Route::get('/reports', [ComplianceController::class, 'reports'])->name('reports');
        Route::post('/reports', [ComplianceController::class, 'generateReport'])->name('reports.generate');
        Route::get('/anonymization', [ComplianceController::class, 'anonymization'])->name('anonymization');
        Route::post('/anonymization', [ComplianceController::class, 'anonymizeData'])->name('anonymization.process');
        Route::get('/dashboard', [ComplianceController::class, 'dashboard'])->name('dashboard');
    });

    /*
    |--------------------------------------------------------------------------
    | Integration Routes (8 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('integration')->name('integration.')->group(function () {
        Route::get('/', [IntegrationController::class, 'index'])->name('index');
        Route::post('/hl7', [IntegrationController::class, 'receiveHL7'])->name('hl7');
        Route::post('/bpjs/claims', [IntegrationController::class, 'submitBPJSClaim'])->name('bpjs.claims.submit');
        Route::get('/bpjs/claims', [IntegrationController::class, 'bpjsClaims'])->name('bpjs.claims');
        Route::get('/lab-equipment', [IntegrationController::class, 'labEquipment'])->name('lab-equipment');
        Route::post('/notifications', [IntegrationController::class, 'sendNotification'])->name('notifications.send');
        Route::get('/notifications', [IntegrationController::class, 'notifications'])->name('notifications');
        Route::get('/dashboard', [IntegrationController::class, 'dashboard'])->name('dashboard');
    });

    /*
    |--------------------------------------------------------------------------
    | Task 8: Additional Specialized Controllers Routes
    |--------------------------------------------------------------------------
    */

    // Ward & Bed Management
    Route::resource('wards', WardController::class);
    Route::resource('beds', BedController::class);
    Route::post('/beds/{bed}/assign-patient', [BedController::class, 'assignPatient'])->name('beds.assign-patient');
    Route::post('/beds/{bed}/release', [BedController::class, 'releasePatient'])->name('beds.release');
    Route::get('/beds/availability', [BedController::class, 'checkAvailability'])->name('beds.availability');

    // Triage & Queue Management
    Route::resource('triage', TriageAssessmentController::class);
    Route::get('/triage/queue', [TriageAssessmentController::class, 'queue'])->name('triage.queue');
    Route::resource('queue-management', QueueManagementController::class);
    Route::post('/queue-management/next', [QueueManagementController::class, 'callNext'])->name('queue-management.next');
    Route::get('/queue-management/display', [QueueManagementController::class, 'displayBoard'])->name('queue-management.display');

    // Laboratory
    Route::resource('lab-test-catalog', LabTestCatalogController::class);
    Route::resource('lab-results', LabResultController::class);
    Route::post('/lab-results/{result}/verify', [LabResultController::class, 'verify'])->name('lab-results.verify');
    Route::post('/lab-results/{result}/flag-critical', [LabResultController::class, 'flagCritical'])->name('lab-results.flag-critical');

    // Radiology
    Route::resource('radiology-exams', RadiologyExamController::class);
    Route::post('/radiology-exams/{exam}/complete', [RadiologyExamController::class, 'complete'])->name('radiology-exams.complete');

    // Insurance & BPJS
    Route::resource('insurance-claims', InsuranceClaimController::class);
    Route::post('/insurance-claims/{claim}/adjudicate', [InsuranceClaimController::class, 'adjudicate'])->name('insurance-claims.adjudicate');
    Route::post('/insurance-claims/{claim}/resubmit', [InsuranceClaimController::class, 'resubmit'])->name('insurance-claims.resubmit');

    Route::resource('bpjs-claims', BPJSClaimController::class);
    Route::post('/bpjs-claims/{claim}/submit', [BPJSClaimController::class, 'submit'])->name('bpjs-claims.submit');

    // Telemedicine & Surgery
    Route::resource('teleconsultations', TeleconsultationController::class);
    Route::get('/teleconsultations/{consultation}/join', [TeleconsultationController::class, 'joinRoom'])->name('teleconsultations.join');

    Route::resource('surgery-schedules', SurgeryScheduleController::class);
    Route::post('/surgery-schedules/{schedule}/start', [SurgeryScheduleController::class, 'start'])->name('surgery-schedules.start');
    Route::post('/surgery-schedules/{schedule}/complete', [SurgeryScheduleController::class, 'complete'])->name('surgery-schedules.complete');

    // Medical Equipment & Supplies
    Route::resource('medical-equipment', MedicalEquipmentController::class);
    Route::post('/medical-equipment/{equipment}/maintenance-log', [MedicalEquipmentController::class, 'logMaintenance'])->name('medical-equipment.log-maintenance');

    Route::resource('medical-supplies', MedicalSupplyController::class);
    Route::post('/medical-supplies/{supply}/adjust-stock', [MedicalSupplyController::class, 'adjustStock'])->name('medical-supplies.adjust-stock');

    // Sterilization & Waste
    Route::resource('sterilization', SterilizationController::class);
    Route::post('/sterilization/{cycle}/quality-check', [SterilizationController::class, 'qualityCheck'])->name('sterilization.quality-check');

    Route::resource('medical-waste', MedicalWasteController::class);
    Route::post('/medical-waste/{waste}/dispose', [MedicalWasteController::class, 'dispose'])->name('medical-waste.dispose');

    // Audit & Compliance
    Route::get('audit-trail', [AuditTrailController::class, 'index'])->name('audit-trail.index');
    Route::get('audit-trail/export', [AuditTrailController::class, 'export'])->name('audit-trail.export');

    Route::resource('compliance-reports', ComplianceReportController::class);
    Route::post('/compliance-reports/{report}/review', [ComplianceReportController::class, 'review'])->name('compliance-reports.review');
    Route::post('/compliance-reports/{report}/approve', [ComplianceReportController::class, 'approve'])->name('compliance-reports.approve');

    // Backup
    Route::resource('backups', BackupController::class);
    Route::post('/backups/{backup}/restore', [BackupController::class, 'restore'])->name('backups.restore');
    Route::get('/backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');

    // HL7 & Integration
    Route::get('hl7-messages', [HL7Controller::class, 'index'])->name('hl7.index');
    Route::post('/hl7-messages/{message}/retry', [HL7Controller::class, 'retry'])->name('hl7.retry');

    // Lab Equipment
    Route::resource('lab-equipment', LabEquipmentController::class);
    Route::post('/lab-equipment/{equipment}/test-connection', [LabEquipmentController::class, 'testConnection'])->name('lab-equipment.test-connection');

    // Notifications
    Route::resource('notification-rules', NotificationController::class);

    // Medical Certificates
    Route::resource('medical-certificates', MedicalCertificateController::class);
    Route::get('/medical-certificates/{certificate}/print', [MedicalCertificateController::class, 'print'])->name('medical-certificates.print');

    // Health Education & Patient Communication
    Route::resource('health-education', HealthEducationController::class);

    Route::resource('patient-messages', PatientMessageController::class);
    Route::get('/patient-messages/inbox', [PatientMessageController::class, 'inbox'])->name('patient-messages.inbox');
    Route::get('/patient-messages/sent', [PatientMessageController::class, 'sent'])->name('patient-messages.sent');
    Route::post('/patient-messages/{message}/reply', [PatientMessageController::class, 'reply'])->name('patient-messages.reply');

    // Analytics & Reports
    Route::get('analytics-dashboard', [AnalyticsDashboardController::class, 'index'])->name('analytics-dashboard.index');
    Route::get('/analytics/patient-demographics', [AnalyticsDashboardController::class, 'patientDemographics'])->name('analytics.patient-demographics');
    Route::get('/analytics/visit-trends', [AnalyticsDashboardController::class, 'visitTrends'])->name('analytics.visit-trends');

    Route::get('financial-reports', [FinancialReportController::class, 'index'])->name('financial-reports.index');
    Route::get('/financial-reports/aging', [FinancialReportController::class, 'agingReport'])->name('financial-reports.aging');
    Route::post('/financial-reports/export', [FinancialReportController::class, 'export'])->name('financial-reports.export');

    Route::get('clinical-quality', [ClinicalQualityController::class, 'index'])->name('clinical-quality.index');
    Route::get('/clinical-quality/readmission-rate', [ClinicalQualityController::class, 'readmissionRate'])->name('clinical-quality.readmission-rate');
    Route::get('/clinical-quality/average-length-of-stay', [ClinicalQualityController::class, 'averageLengthOfStay'])->name('clinical-quality.alos');

    Route::resource('ministry-reports', MinistryReportController::class);
    Route::post('/ministry-reports/{report}/submit', [MinistryReportController::class, 'submit'])->name('ministry-reports.submit');

    Route::get('trend-analysis', [TrendAnalysisController::class, 'index'])->name('trend-analysis.index');
    Route::get('/trend-analysis/visits', [TrendAnalysisController::class, 'visitTrends'])->name('trend-analysis.visits');
    Route::get('/trend-analysis/revenue', [TrendAnalysisController::class, 'revenueTrends'])->name('trend-analysis.revenue');

    Route::resource('patient-satisfaction', PatientSatisfactionController::class);
    Route::get('/patient-satisfaction/statistics', [PatientSatisfactionController::class, 'statistics'])->name('patient-satisfaction.statistics');

});

/*
|--------------------------------------------------------------------------
| Patient Portal Routes (15 routes)
|--------------------------------------------------------------------------
| Separate from healthcare admin routes - for patient self-service
*/
Route::middleware(['auth', 'verified'])->prefix('patient-portal')->name('patient-portal.')->group(function () {
    Route::get('/', [PatientPortalController::class, 'index'])->name('index');
    Route::get('/dashboard', [PatientPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/medical-records', [PatientPortalController::class, 'medicalRecords'])->name('medical-records');
    Route::get('/appointments', [PatientPortalController::class, 'appointments'])->name('appointments');
    Route::post('/appointments/book', [PatientPortalController::class, 'bookAppointment'])->name('appointments.book');
    Route::get('/lab-results', [PatientPortalController::class, 'labResults'])->name('lab-results');
    Route::get('/lab-results/{id}', [PatientPortalController::class, 'showLabResult'])->name('lab-results.show');
    Route::get('/prescriptions', [PatientPortalController::class, 'prescriptions'])->name('prescriptions');
    Route::get('/billing', [PatientPortalController::class, 'billing'])->name('billing');
    Route::post('/billing/pay', [PatientPortalController::class, 'payBill'])->name('billing.pay');
    Route::get('/certificates', [PatientPortalController::class, 'certificates'])->name('certificates');
    Route::post('/certificates/request', [PatientPortalController::class, 'requestCertificate'])->name('certificates.request');
    Route::get('/messages', [PatientPortalController::class, 'messages'])->name('messages');
    Route::post('/messages', [PatientPortalController::class, 'sendMessage'])->name('messages.send');
    Route::get('/health-education', [PatientPortalController::class, 'healthEducation'])->name('health-education');
});
