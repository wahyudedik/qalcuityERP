<?php

namespace App\Services\DemoData\Generators;

use App\Services\DemoData\BaseIndustryGenerator;
use App\Services\DemoData\CoreDataContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HealthcareGenerator extends BaseIndustryGenerator
{
    public function getIndustryName(): string
    {
        return 'healthcare';
    }

    public function generate(CoreDataContext $ctx): array
    {
        $tenantId       = $ctx->tenantId;
        $recordsCreated = 0;
        $generatedData  = [];

        // 1. Doctors (3 with different specializations)
        $doctorIds = [];
        try {
            $doctorIds = $this->seedDoctors($tenantId);
            $recordsCreated += count($doctorIds);
            $generatedData['doctors'] = count($doctorIds);
        } catch (\Throwable $e) {
            $this->logWarning('HealthcareGenerator: failed to seed doctors', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['doctors'] = 0;
        }

        // 2. Patients (10 with demographic information)
        $patientIds = [];
        try {
            $patientIds = $this->seedPatients($tenantId);
            $recordsCreated += count($patientIds);
            $generatedData['patients'] = count($patientIds);
        } catch (\Throwable $e) {
            $this->logWarning('HealthcareGenerator: failed to seed patients', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['patients'] = 0;
        }

        // 3. Appointments (10: 4 scheduled, 4 completed, 2 cancelled)
        try {
            $appointmentCount = $this->seedAppointments($tenantId, $patientIds, $doctorIds);
            $recordsCreated += $appointmentCount;
            $generatedData['appointments'] = $appointmentCount;
        } catch (\Throwable $e) {
            $this->logWarning('HealthcareGenerator: failed to seed appointments', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['appointments'] = 0;
        }

        // 4. Medical Records (5 linked to patients and doctors)
        try {
            $medicalRecordCount = $this->seedMedicalRecords($tenantId, $patientIds, $doctorIds);
            $recordsCreated += $medicalRecordCount;
            $generatedData['medical_records'] = $medicalRecordCount;
        } catch (\Throwable $e) {
            $this->logWarning('HealthcareGenerator: failed to seed medical records', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['medical_records'] = 0;
        }

        // 5. Medicine Inventory (products with category 'medicine' + stock)
        try {
            $medicineCount = $this->seedMedicineInventory($tenantId, $ctx->warehouseId);
            $recordsCreated += $medicineCount;
            $generatedData['medicine_inventory'] = $medicineCount;
        } catch (\Throwable $e) {
            $this->logWarning('HealthcareGenerator: failed to seed medicine inventory', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['medicine_inventory'] = 0;
        }

        return [
            'records_created' => $recordsCreated,
            'generated_data'  => $generatedData,
        ];
    }

    private function seedDoctors(int $tenantId): array
    {
        $doctors = [
            ['doctor_number' => 'DR-HC-001', 'license_number' => 'SIP-001-DEMO', 'sip_number' => 'SIP-001-DEMO', 'specialization' => 'Umum', 'consultation_fee' => 150000, 'practice_days' => json_encode(['monday','tuesday','wednesday','thursday','friday']), 'practice_start_time' => '08:00:00', 'practice_end_time' => '16:00:00', 'years_of_experience' => 10],
            ['doctor_number' => 'DR-HC-002', 'license_number' => 'SIP-002-DEMO', 'sip_number' => 'SIP-002-DEMO', 'specialization' => 'Spesialis Anak', 'consultation_fee' => 250000, 'practice_days' => json_encode(['monday','wednesday','friday']), 'practice_start_time' => '09:00:00', 'practice_end_time' => '15:00:00', 'years_of_experience' => 8],
            ['doctor_number' => 'DR-HC-003', 'license_number' => 'SIP-003-DEMO', 'sip_number' => 'SIP-003-DEMO', 'specialization' => 'Spesialis Penyakit Dalam', 'consultation_fee' => 300000, 'practice_days' => json_encode(['tuesday','thursday','saturday']), 'practice_start_time' => '10:00:00', 'practice_end_time' => '17:00:00', 'years_of_experience' => 15],
        ];

        $ids = [];
        foreach ($doctors as $doc) {
            $existing = DB::table('doctors')
                ->where('tenant_id', $tenantId)
                ->where('doctor_number', $doc['doctor_number'])
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                $ids[] = (int) $existing->id;
                continue;
            }

            $id = DB::table('doctors')->insertGetId(array_merge($doc, [
                'tenant_id'                  => $tenantId,
                'status'                     => 'active',
                'accepting_patients'         => true,
                'available_for_telemedicine' => false,
                'available_for_home_visit'   => false,
                'available_for_emergency'    => false,
                'total_consultations'        => 0,
                'total_patients'             => 0,
                'average_rating'             => 0,
                'total_reviews'              => 0,
                'created_at'                 => now(),
                'updated_at'                 => now(),
            ]));

            $ids[] = (int) $id;
        }

        return $ids;
    }

    private function seedPatients(int $tenantId): array
    {
        $patients = [
            ['mrn' => 'MR-HC-0001', 'nik' => '3201010101800001', 'full_name' => 'Budi Santoso',    'gender' => 'male',   'birth_date' => '1980-01-15', 'blood_type' => 'A',  'phone' => '081234560001', 'city' => 'Jakarta'],
            ['mrn' => 'MR-HC-0002', 'nik' => '3201010285900002', 'full_name' => 'Siti Rahayu',     'gender' => 'female', 'birth_date' => '1990-02-20', 'blood_type' => 'B',  'phone' => '081234560002', 'city' => 'Bandung'],
            ['mrn' => 'MR-HC-0003', 'nik' => '3201010375850003', 'full_name' => 'Ahmad Fauzi',     'gender' => 'male',   'birth_date' => '1985-03-10', 'blood_type' => 'O',  'phone' => '081234560003', 'city' => 'Surabaya'],
            ['mrn' => 'MR-HC-0004', 'nik' => '3201010492950004', 'full_name' => 'Dewi Lestari',    'gender' => 'female', 'birth_date' => '1995-04-05', 'blood_type' => 'AB', 'phone' => '081234560004', 'city' => 'Yogyakarta'],
            ['mrn' => 'MR-HC-0005', 'nik' => '3201010578880005', 'full_name' => 'Rudi Hermawan',   'gender' => 'male',   'birth_date' => '1988-05-22', 'blood_type' => 'A',  'phone' => '081234560005', 'city' => 'Semarang'],
            ['mrn' => 'MR-HC-0006', 'nik' => '3201010692920006', 'full_name' => 'Rina Kusumawati', 'gender' => 'female', 'birth_date' => '1992-06-18', 'blood_type' => 'B',  'phone' => '081234560006', 'city' => 'Medan'],
            ['mrn' => 'MR-HC-0007', 'nik' => '3201010783870007', 'full_name' => 'Hendra Wijaya',   'gender' => 'male',   'birth_date' => '1987-07-30', 'blood_type' => 'O',  'phone' => '081234560007', 'city' => 'Makassar'],
            ['mrn' => 'MR-HC-0008', 'nik' => '3201010887970008', 'full_name' => 'Maya Indrawati',  'gender' => 'female', 'birth_date' => '1997-08-12', 'blood_type' => 'A',  'phone' => '081234560008', 'city' => 'Palembang'],
            ['mrn' => 'MR-HC-0009', 'nik' => '3201010975860009', 'full_name' => 'Agus Setiawan',   'gender' => 'male',   'birth_date' => '1986-09-25', 'blood_type' => 'B',  'phone' => '081234560009', 'city' => 'Denpasar'],
            ['mrn' => 'MR-HC-0010', 'nik' => '3201011091930010', 'full_name' => 'Fitri Handayani', 'gender' => 'female', 'birth_date' => '1993-10-08', 'blood_type' => 'O',  'phone' => '081234560010', 'city' => 'Balikpapan'],
        ];

        $ids = [];
        foreach ($patients as $i => $p) {
            $existing = DB::table('patients')
                ->where('tenant_id', $tenantId)
                ->where('medical_record_number', $p['mrn'])
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                $ids[] = (int) $existing->id;
                continue;
            }

            $firstName = explode(' ', $p['full_name'])[0];
            $id = DB::table('patients')->insertGetId([
                'tenant_id'                  => $tenantId,
                'medical_record_number'      => $p['mrn'],
                'nik'                        => $p['nik'],
                'full_name'                  => $p['full_name'],
                'short_name'                 => $firstName,
                'birth_date'                 => $p['birth_date'],
                'birth_place'                => $p['city'],
                'gender'                     => $p['gender'],
                'blood_type'                 => $p['blood_type'],
                'religion'                   => 'Islam',
                'marital_status'             => $i < 5 ? 'married' : 'single',
                'nationality'                => 'WNI',
                'phone_primary'              => $p['phone'],
                'email'                      => strtolower(str_replace(' ', '.', $p['full_name'])) . '@demo-health.com',
                'address_street'             => 'Jl. Demo Kesehatan No. ' . ($i + 1),
                'address_city'               => $p['city'],
                'address_province'           => 'Jawa',
                'emergency_contact_name'     => 'Keluarga ' . $firstName,
                'emergency_contact_phone'    => '0812345' . str_pad((string) ($i + 100), 5, '0', STR_PAD_LEFT),
                'emergency_contact_relation' => 'Keluarga',
                'status'                     => 'active',
                'is_blacklisted'             => false,
                'total_visits'               => 0,
                'total_admissions'           => 0,
                'qr_code'                    => 'QR-HC-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'created_at'                 => now(),
                'updated_at'                 => now(),
            ]);

            $ids[] = (int) $id;
        }

        return $ids;
    }

    private function seedAppointments(int $tenantId, array $patientIds, array $doctorIds): int
    {
        if (empty($patientIds) || empty($doctorIds)) {
            $this->logWarning('HealthcareGenerator: missing patients or doctors, skipping appointments', [
                'tenant_id' => $tenantId,
            ]);
            return 0;
        }

        // 4 scheduled (future), 4 completed (past), 2 cancelled
        $appointments = [
            ['number' => 'APT-HC-0001', 'status' => 'scheduled',  'day_offset' => 3,   'time' => '09:00', 'type' => 'consultation', 'reason' => 'Pemeriksaan rutin'],
            ['number' => 'APT-HC-0002', 'status' => 'scheduled',  'day_offset' => 5,   'time' => '10:00', 'type' => 'consultation', 'reason' => 'Kontrol tekanan darah'],
            ['number' => 'APT-HC-0003', 'status' => 'scheduled',  'day_offset' => 7,   'time' => '11:00', 'type' => 'follow_up',    'reason' => 'Kontrol pasca operasi'],
            ['number' => 'APT-HC-0004', 'status' => 'scheduled',  'day_offset' => 10,  'time' => '14:00', 'type' => 'check_up',     'reason' => 'Medical check-up tahunan'],
            ['number' => 'APT-HC-0005', 'status' => 'completed',  'day_offset' => -7,  'time' => '09:00', 'type' => 'consultation', 'reason' => 'Demam dan batuk'],
            ['number' => 'APT-HC-0006', 'status' => 'completed',  'day_offset' => -10, 'time' => '10:30', 'type' => 'consultation', 'reason' => 'Sakit kepala berulang'],
            ['number' => 'APT-HC-0007', 'status' => 'completed',  'day_offset' => -14, 'time' => '13:00', 'type' => 'follow_up',    'reason' => 'Kontrol diabetes'],
            ['number' => 'APT-HC-0008', 'status' => 'completed',  'day_offset' => -3,  'time' => '15:00', 'type' => 'consultation', 'reason' => 'Nyeri sendi'],
            ['number' => 'APT-HC-0009', 'status' => 'cancelled',  'day_offset' => -5,  'time' => '09:30', 'type' => 'consultation', 'reason' => 'Pemeriksaan umum'],
            ['number' => 'APT-HC-0010', 'status' => 'cancelled',  'day_offset' => -2,  'time' => '11:00', 'type' => 'check_up',     'reason' => 'Cek kolesterol'],
        ];

        $count = 0;
        foreach ($appointments as $i => $apt) {
            $exists = DB::table('appointments')
                ->where('tenant_id', $tenantId)
                ->where('appointment_number', $apt['number'])
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                continue;
            }

            $patientId = $patientIds[$i % count($patientIds)];
            $doctorId  = $doctorIds[$i % count($doctorIds)];
            $aptDate   = Carbon::today()->addDays($apt['day_offset']);

            $checkedInAt          = null;
            $consultationStarted  = null;
            $consultationEnded    = null;
            $cancelledAt          = null;
            $cancellationReason   = null;

            if ($apt['status'] === 'completed') {
                $checkedInAt         = $aptDate->copy()->setTimeFromTimeString($apt['time'])->subMinutes(15);
                $consultationStarted = $aptDate->copy()->setTimeFromTimeString($apt['time']);
                $consultationEnded   = $consultationStarted->copy()->addMinutes(30);
            } elseif ($apt['status'] === 'cancelled') {
                $cancelledAt        = $aptDate->copy()->subDays(1);
                $cancellationReason = 'Pasien tidak dapat hadir';
            }

            DB::table('appointments')->insert([
                'tenant_id'               => $tenantId,
                'patient_id'              => $patientId,
                'doctor_id'               => $doctorId,
                'appointment_number'      => $apt['number'],
                'appointment_date'        => $aptDate->format('Y-m-d'),
                'appointment_time'        => $apt['time'] . ':00',
                'estimated_duration'      => 30,
                'appointment_type'        => $apt['type'],
                'visit_type'              => 'outpatient',
                'status'                  => $apt['status'],
                'reason_for_visit'        => $apt['reason'],
                'is_urgent'               => false,
                'reminder_sent_24h'       => false,
                'reminder_sent_1h'        => false,
                'checked_in_at'           => $checkedInAt?->format('Y-m-d H:i:s'),
                'consultation_started_at' => $consultationStarted?->format('Y-m-d H:i:s'),
                'consultation_ended_at'   => $consultationEnded?->format('Y-m-d H:i:s'),
                'cancelled_at'            => $cancelledAt?->format('Y-m-d H:i:s'),
                'cancellation_reason'     => $cancellationReason,
                'created_at'              => now(),
                'updated_at'              => now(),
            ]);

            $count++;
        }

        return $count;
    }

    private function seedMedicalRecords(int $tenantId, array $patientIds, array $doctorIds): int
    {
        if (empty($patientIds) || empty($doctorIds)) {
            $this->logWarning('HealthcareGenerator: missing patients or doctors, skipping medical records', [
                'tenant_id' => $tenantId,
            ]);
            return 0;
        }

        $records = [
            [
                'record_type'     => 'outpatient',
                'chief_complaint' => 'Demam tinggi selama 3 hari',
                'diagnosis'       => 'Infeksi Saluran Pernapasan Atas (ISPA)',
                'treatment_plan'  => 'Antibiotik amoxicillin 500mg 3x1, istirahat cukup, minum air putih',
                'vital_signs'     => json_encode(['temperature' => 38.5, 'bp_systolic' => 120, 'bp_diastolic' => 80, 'heart_rate' => 88, 'spo2' => 98, 'weight' => 65, 'height' => 170]),
                'status'          => 'completed',
                'requires_follow_up' => true,
                'follow_up_date'  => Carbon::today()->addDays(7)->format('Y-m-d'),
            ],
            [
                'record_type'     => 'outpatient',
                'chief_complaint' => 'Nyeri dada dan sesak napas',
                'diagnosis'       => 'Hipertensi Grade I',
                'treatment_plan'  => 'Amlodipine 5mg 1x1, diet rendah garam, olahraga teratur',
                'vital_signs'     => json_encode(['temperature' => 36.8, 'bp_systolic' => 150, 'bp_diastolic' => 95, 'heart_rate' => 92, 'spo2' => 97, 'weight' => 78, 'height' => 168]),
                'status'          => 'completed',
                'requires_follow_up' => true,
                'follow_up_date'  => Carbon::today()->addDays(14)->format('Y-m-d'),
            ],
            [
                'record_type'     => 'outpatient',
                'chief_complaint' => 'Batuk berdahak lebih dari 2 minggu',
                'diagnosis'       => 'Bronkitis Akut',
                'treatment_plan'  => 'Ambroxol 30mg 3x1, Salbutamol inhaler, hindari asap rokok',
                'vital_signs'     => json_encode(['temperature' => 37.2, 'bp_systolic' => 118, 'bp_diastolic' => 76, 'heart_rate' => 82, 'spo2' => 96, 'weight' => 60, 'height' => 162]),
                'status'          => 'completed',
                'requires_follow_up' => false,
                'follow_up_date'  => null,
            ],
            [
                'record_type'     => 'outpatient',
                'chief_complaint' => 'Gula darah tidak terkontrol',
                'diagnosis'       => 'Diabetes Mellitus Tipe 2',
                'treatment_plan'  => 'Metformin 500mg 2x1, diet DM, cek gula darah rutin',
                'vital_signs'     => json_encode(['temperature' => 36.6, 'bp_systolic' => 130, 'bp_diastolic' => 85, 'heart_rate' => 78, 'spo2' => 99, 'weight' => 82, 'height' => 172]),
                'status'          => 'completed',
                'requires_follow_up' => true,
                'follow_up_date'  => Carbon::today()->addDays(30)->format('Y-m-d'),
            ],
            [
                'record_type'     => 'outpatient',
                'chief_complaint' => 'Sakit kepala berulang dan pusing',
                'diagnosis'       => 'Migrain',
                'treatment_plan'  => 'Paracetamol 500mg bila nyeri, hindari pemicu migrain, istirahat cukup',
                'vital_signs'     => json_encode(['temperature' => 36.5, 'bp_systolic' => 115, 'bp_diastolic' => 75, 'heart_rate' => 72, 'spo2' => 99, 'weight' => 55, 'height' => 158]),
                'status'          => 'completed',
                'requires_follow_up' => false,
                'follow_up_date'  => null,
            ],
        ];

        $rows = [];
        foreach ($records as $i => $rec) {
            $patientId = $patientIds[$i % count($patientIds)];
            $doctorId  = $doctorIds[$i % count($doctorIds)];

            $exists = DB::table('patient_medical_records')
                ->where('patient_id', $patientId)
                ->where('doctor_id', $doctorId)
                ->where('chief_complaint', $rec['chief_complaint'])
                ->exists();

            if ($exists) {
                continue;
            }

            $rows[] = [
                'patient_id'                  => $patientId,
                'doctor_id'                   => $doctorId,
                'record_type'                 => $rec['record_type'],
                'chief_complaint'             => $rec['chief_complaint'],
                'diagnosis'                   => $rec['diagnosis'],
                'treatment_plan'              => $rec['treatment_plan'],
                'vital_signs'                 => $rec['vital_signs'],
                'status'                      => $rec['status'],
                'is_emergency'                => false,
                'requires_follow_up'          => $rec['requires_follow_up'],
                'follow_up_date'              => $rec['follow_up_date'],
                'created_at'                  => Carbon::now()->subDays(($i + 1) * 3)->format('Y-m-d H:i:s'),
                'updated_at'                  => now(),
            ];
        }

        if (!empty($rows)) {
            $this->bulkInsert('patient_medical_records', $rows);
        }

        return count($rows);
    }

    private function seedMedicineInventory(int $tenantId, int $warehouseId): int
    {
        $medicines = [
            ['sku' => 'MED-HC-001', 'name' => 'Paracetamol 500mg',       'unit' => 'strip', 'price_buy' => 5000,   'price_sell' => 8000,   'stock_min' => 50,  'qty' => 200],
            ['sku' => 'MED-HC-002', 'name' => 'Amoxicillin 500mg',       'unit' => 'strip', 'price_buy' => 15000,  'price_sell' => 22000,  'stock_min' => 30,  'qty' => 150],
            ['sku' => 'MED-HC-003', 'name' => 'Amlodipine 5mg',          'unit' => 'strip', 'price_buy' => 12000,  'price_sell' => 18000,  'stock_min' => 20,  'qty' => 100],
            ['sku' => 'MED-HC-004', 'name' => 'Metformin 500mg',         'unit' => 'strip', 'price_buy' => 8000,   'price_sell' => 12000,  'stock_min' => 30,  'qty' => 120],
            ['sku' => 'MED-HC-005', 'name' => 'Ambroxol 30mg',           'unit' => 'strip', 'price_buy' => 6000,   'price_sell' => 9000,   'stock_min' => 25,  'qty' => 100],
            ['sku' => 'MED-HC-006', 'name' => 'Omeprazole 20mg',         'unit' => 'strip', 'price_buy' => 10000,  'price_sell' => 15000,  'stock_min' => 20,  'qty' => 80],
            ['sku' => 'MED-HC-007', 'name' => 'Cetirizine 10mg',         'unit' => 'strip', 'price_buy' => 7000,   'price_sell' => 11000,  'stock_min' => 20,  'qty' => 90],
            ['sku' => 'MED-HC-008', 'name' => 'Salbutamol Inhaler',      'unit' => 'pcs',   'price_buy' => 35000,  'price_sell' => 55000,  'stock_min' => 10,  'qty' => 40],
            ['sku' => 'MED-HC-009', 'name' => 'Tensimeter Digital',      'unit' => 'unit',  'price_buy' => 250000, 'price_sell' => 350000, 'stock_min' => 2,   'qty' => 5],
            ['sku' => 'MED-HC-010', 'name' => 'Stetoskop Littmann',      'unit' => 'unit',  'price_buy' => 800000, 'price_sell' => 1100000,'stock_min' => 2,   'qty' => 4],
            ['sku' => 'MED-HC-011', 'name' => 'Glukometer Accu-Check',   'unit' => 'unit',  'price_buy' => 350000, 'price_sell' => 500000, 'stock_min' => 3,   'qty' => 8],
            ['sku' => 'MED-HC-012', 'name' => 'Strip Gula Darah',        'unit' => 'box',   'price_buy' => 80000,  'price_sell' => 120000, 'stock_min' => 10,  'qty' => 50],
        ];

        $productIds = [];
        $stockRows  = [];

        foreach ($medicines as $m) {
            $existing = DB::table('products')
                ->where('tenant_id', $tenantId)
                ->where('sku', $m['sku'])
                ->first();

            if ($existing) {
                $productIds[] = (int) $existing->id;
                continue;
            }

            $id = DB::table('products')->insertGetId([
                'tenant_id'  => $tenantId,
                'name'       => $m['name'],
                'sku'        => $m['sku'],
                'category'   => 'medicine',
                'unit'       => $m['unit'],
                'price_buy'  => $m['price_buy'],
                'price_sell' => $m['price_sell'],
                'stock_min'  => $m['stock_min'],
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $productIds[] = (int) $id;

            if ($warehouseId > 0) {
                $stockRows[] = [
                    'product_id'   => $id,
                    'warehouse_id' => $warehouseId,
                    'quantity'     => $m['qty'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }
        }

        if (!empty($stockRows)) {
            DB::table('product_stocks')->insertOrIgnore($stockRows);
        }

        return count($productIds);
    }
}
