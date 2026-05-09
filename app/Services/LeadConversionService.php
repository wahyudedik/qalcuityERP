<?php

namespace App\Services;

use App\Models\CrmLead;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

/**
 * LeadConversionService - Prevent duplicate customer creation during lead conversion
 *
 * BUG-CRM-001 FIX: Comprehensive duplicate detection before lead conversion
 *
 * Problems Fixed:
 * 1. Incomplete duplicate check (only email OR name+company)
 * 2. No phone number duplicate detection
 * 3. No fuzzy matching for slight variations
 * 4. No check if lead already converted
 * 5. No option to link to existing customer instead of creating new
 */
class LeadConversionService
{
    /**
     * BUG-CRM-001 FIX: Comprehensive duplicate detection
     *
     * @return array ['has_duplicates' => bool, 'duplicates' => array, 'suggestion' => string]
     */
    public function checkForDuplicates(CrmLead $lead): array
    {
        $duplicates = [];
        $tid = $lead->tenant_id;

        // Check 1: Exact email match (highest priority)
        if ($lead->email) {
            $emailMatch = Customer::where('tenant_id', $tid)
                ->where('email', $lead->email)
                ->first();

            if ($emailMatch) {
                $duplicates[] = [
                    'type' => 'exact_email',
                    'confidence' => 100,
                    'customer_id' => $emailMatch->id,
                    'customer_name' => $emailMatch->name,
                    'customer_email' => $emailMatch->email,
                    'customer_phone' => $emailMatch->phone,
                    'customer_company' => $emailMatch->company,
                    'match_field' => 'email',
                    'match_value' => $lead->email,
                    'suggestion' => 'Gunakan customer yang sudah ada',
                ];
            }
        }

        // Check 2: Exact phone match
        if ($lead->phone) {
            $originalPhone = $lead->phone;
            $normalizedPhone = $this->normalizePhone($lead->phone);

            $phoneMatch = Customer::where('tenant_id', $tid)
                ->where(function ($q) use ($originalPhone, $normalizedPhone) {
                    $q->where('phone', $originalPhone)
                        ->orWhere('phone', $normalizedPhone);
                })
                ->first();

            if ($phoneMatch) {
                $duplicates[] = [
                    'type' => 'exact_phone',
                    'confidence' => 95,
                    'customer_id' => $phoneMatch->id,
                    'customer_name' => $phoneMatch->name,
                    'customer_email' => $phoneMatch->email,
                    'customer_phone' => $phoneMatch->phone,
                    'customer_company' => $phoneMatch->company,
                    'match_field' => 'phone',
                    'match_value' => $lead->phone,
                    'suggestion' => 'Gunakan customer yang sudah ada',
                ];
            }
        }

        // Check 3: Name + Company match
        if ($lead->name && $lead->company) {
            $nameCompanyMatch = Customer::where('tenant_id', $tid)
                ->where('name', $lead->name)
                ->where('company', $lead->company)
                ->first();

            if ($nameCompanyMatch) {
                $duplicates[] = [
                    'type' => 'exact_name_company',
                    'confidence' => 90,
                    'customer_id' => $nameCompanyMatch->id,
                    'customer_name' => $nameCompanyMatch->name,
                    'customer_email' => $nameCompanyMatch->email,
                    'customer_phone' => $nameCompanyMatch->phone,
                    'customer_company' => $nameCompanyMatch->company,
                    'match_field' => 'name + company',
                    'match_value' => "{$lead->name} @ {$lead->company}",
                    'suggestion' => 'Gunakan customer yang sudah ada',
                ];
            }
        }

        // Check 4: Fuzzy name match (similar names)
        if ($lead->name) {
            $similarCustomers = Customer::where('tenant_id', $tid)
                ->where(function ($q) use ($lead) {
                    $q->where('name', 'LIKE', '%'.$lead->name.'%')
                        ->orWhere('name', 'LIKE', '%'.str_replace(' ', '%', $lead->name).'%');
                })
                ->limit(5)
                ->get();

            foreach ($similarCustomers as $similar) {
                // Skip if already found in exact matches
                $alreadyFound = collect($duplicates)->contains('customer_id', $similar->id);

                if (! $alreadyFound) {
                    $similarity = $this->calculateSimilarity($lead->name, $similar->name);

                    if ($similarity >= 80) {
                        $duplicates[] = [
                            'type' => 'fuzzy_name',
                            'confidence' => $similarity,
                            'customer_id' => $similar->id,
                            'customer_name' => $similar->name,
                            'customer_email' => $similar->email,
                            'customer_phone' => $similar->phone,
                            'customer_company' => $similar->company,
                            'match_field' => 'name (similarity)',
                            'match_value' => $similar->name,
                            'similarity' => $similarity,
                            'suggestion' => 'Periksa apakah ini customer yang sama',
                        ];
                    }
                }
            }
        }

        // Check 5: Check if lead already converted
        if ($lead->converted_to_customer_id) {
            $existingCustomer = Customer::find($lead->converted_to_customer_id);

            if ($existingCustomer) {
                return [
                    'has_duplicates' => true,
                    'already_converted' => true,
                    'duplicates' => [
                        [
                            'type' => 'already_converted',
                            'confidence' => 100,
                            'customer_id' => $existingCustomer->id,
                            'customer_name' => $existingCustomer->name,
                            'customer_email' => $existingCustomer->email,
                            'customer_phone' => $existingCustomer->phone,
                            'customer_company' => $existingCustomer->company,
                            'match_field' => 'lead.converted_to_customer_id',
                            'match_value' => 'Lead sudah dikonversi ke customer ini',
                            'suggestion' => 'Lead ini sudah pernah dikonversi sebelumnya',
                        ],
                    ],
                    'suggestion' => 'Lead ini sudah dikonversi ke customer #'.$existingCustomer->id,
                ];
            }
        }

        // Sort by confidence (highest first)
        usort($duplicates, function ($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        });

        return [
            'has_duplicates' => ! empty($duplicates),
            'already_converted' => false,
            'duplicates' => $duplicates,
            'suggestion' => ! empty($duplicates)
                ? 'Ditemukan '.count($duplicates).' potential duplicate(s). Review sebelum convert.'
                : 'Tidak ada duplicate yang terdeteksi. Aman untuk convert.',
        ];
    }

    /**
     * BUG-CRM-001 FIX: Convert lead to customer with duplicate prevention
     *
     * @param  bool  $forceCreate  Force create even if duplicates found
     * @param  int|null  $linkToCustomerId  Link to existing customer instead of creating new
     */
    public function convertLead(CrmLead $lead, bool $forceCreate = false, ?int $linkToCustomerId = null): array
    {
        // Validate lead stage
        if ($lead->stage !== 'won') {
            return [
                'success' => false,
                'message' => 'Hanya lead dengan stage "Won" yang bisa dikonversi menjadi customer.',
            ];
        }

        // Check for duplicates
        $duplicateCheck = $this->checkForDuplicates($lead);

        // If already converted, return existing customer
        if ($duplicateCheck['already_converted']) {
            $existingCustomer = Customer::find($lead->converted_to_customer_id);

            return [
                'success' => false,
                'already_converted' => true,
                'message' => "Lead ini sudah dikonversi ke customer: {$existingCustomer->name} (#{$existingCustomer->id})",
                'customer' => $existingCustomer,
            ];
        }

        // If duplicates found and not forcing
        if ($duplicateCheck['has_duplicates'] && ! $forceCreate && ! $linkToCustomerId) {
            return [
                'success' => false,
                'has_duplicates' => true,
                'message' => 'Potential duplicate customer(s) detected. Review sebelum convert.',
                'duplicates' => $duplicateCheck['duplicates'],
                'suggestion' => 'Gunakan parameter link_to_customer_id untuk link ke customer yang sudah ada, atau force_create untuk buat baru.',
            ];
        }

        // Link to existing customer
        if ($linkToCustomerId) {
            $existingCustomer = Customer::where('tenant_id', $lead->tenant_id)
                ->find($linkToCustomerId);

            if (! $existingCustomer) {
                return [
                    'success' => false,
                    'message' => 'Customer tidak ditemukan.',
                ];
            }

            // Update lead with link to existing customer
            $lead->update([
                'converted_to_customer_id' => $existingCustomer->id,
                'stage' => 'converted',
            ]);

            Log::info('CRM: Lead linked to existing customer', [
                'lead_id' => $lead->id,
                'lead_name' => $lead->name,
                'customer_id' => $existingCustomer->id,
                'customer_name' => $existingCustomer->name,
            ]);

            return [
                'success' => true,
                'action' => 'linked',
                'message' => "Lead \"{$lead->name}\" dilink ke customer existing: {$existingCustomer->name} (#{$existingCustomer->id})",
                'customer' => $existingCustomer,
                'lead' => $lead,
            ];
        }

        // Create new customer (force or no duplicates)
        $customer = Customer::create([
            'tenant_id' => $lead->tenant_id,
            'name' => $lead->name,
            'company' => $lead->company,
            'phone' => $lead->phone,
            'email' => $lead->email,
            'address' => $lead->address ?? null,
            'notes' => "Converted from lead: {$lead->id}",
            'is_active' => true,
        ]);

        // Update lead
        $lead->update([
            'converted_to_customer_id' => $customer->id,
            'stage' => 'converted',
        ]);

        Log::info('CRM: Lead converted to new customer', [
            'lead_id' => $lead->id,
            'lead_name' => $lead->name,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
        ]);

        return [
            'success' => true,
            'action' => 'created',
            'message' => "Lead \"{$lead->name}\" berhasil dikonversi menjadi customer: {$customer->name} (#{$customer->id})",
            'customer' => $customer,
            'lead' => $lead,
        ];
    }

    /**
     * Normalize phone number for comparison
     */
    protected function normalizePhone(string $phone): string
    {
        // Remove all non-digit characters
        $normalized = preg_replace('/[^0-9]/', '', $phone);

        // Convert 08 to 628 (Indonesian format)
        if (substr($normalized, 0, 1) === '0' && substr($normalized, 1, 1) === '8') {
            $normalized = '62'.substr($normalized, 1);
        }

        return $normalized;
    }

    /**
     * Calculate similarity percentage between two strings
     * Uses simple string comparison
     */
    protected function calculateSimilarity(string $str1, string $str2): int
    {
        $str1 = strtolower(trim($str1));
        $str2 = strtolower(trim($str2));

        // Exact match
        if ($str1 === $str2) {
            return 100;
        }

        // Use similar_text for fuzzy matching
        similar_text($str1, $str2, $percent);

        return (int) round($percent);
    }
}
