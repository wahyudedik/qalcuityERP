<?php

namespace App\Services;

use App\Models\CrmLead;
use App\Models\Customer;

/**
 * CrmService — Layanan utama modul CRM.
 *
 * Bug 1.16 Fix: Konversi lead ke customer dengan dedup check.
 * Sebelum membuat customer baru, cek apakah customer dengan email
 * atau nomor telepon yang sama sudah ada di tenant yang sama.
 */
class CrmService
{
    /**
     * Konversi lead menjadi customer dengan dedup check.
     *
     * Bug_Condition: module = 'crm' AND NOT duplicateChecked(input)
     * Expected_Behavior: customer existing digunakan, tidak ada duplikat
     * Preservation: konversi lead dengan email baru tetap membuat customer baru
     */
    public function convertLeadToCustomer(CrmLead $lead): Customer
    {
        // Cek duplikat berdasarkan email atau telepon dalam scope tenant
        $existing = Customer::where('tenant_id', $lead->tenant_id)
            ->where(function ($q) use ($lead) {
                if ($lead->email) {
                    $q->where('email', $lead->email);
                }
                if ($lead->phone) {
                    $q->orWhere('phone', $lead->phone);
                }
            })->first();

        if ($existing) {
            // Gunakan customer existing, update lead dengan referensi ke customer tersebut
            $lead->update([
                'converted_to_customer_id' => $existing->id,
                'stage' => CrmLead::STAGE_CONVERTED,
            ]);
            return $existing;
        }

        // Tidak ada duplikat — buat customer baru
        $customer = Customer::create([
            'tenant_id' => $lead->tenant_id,
            'name'      => $lead->name,
            'email'     => $lead->email,
            'phone'     => $lead->phone,
            'company'   => $lead->company,
            'address'   => $lead->address ?? null,
            'is_active' => true,
        ]);

        $lead->update([
            'converted_to_customer_id' => $customer->id,
            'stage' => CrmLead::STAGE_CONVERTED,
        ]);

        return $customer;
    }
}
