<?php

namespace App\Services;

use App\Models\CosmeticFormula;
use App\Models\IngredientRestriction;
use App\Models\ProductRegistration;
use App\Models\RegistrationDocument;
use App\Models\SafetyDataSheet;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * BPOM Registration Service
 *
 * @note Linter may show false positives for auth()->id() - this is standard Laravel
 */
class BpomRegistrationService
{
    /**
     * TASK-2.34: Create BPOM registration
     */
    public function createRegistration(int $tenantId, int $formulaId, array $data): ProductRegistration
    {
        $formula = CosmeticFormula::where('tenant_id', $tenantId)
            ->findOrFail($formulaId);

        // Validate ingredients against restrictions
        $validationResult = $this->validateIngredientsForBpom($formula);

        if (! $validationResult['compliant']) {
            throw new \InvalidArgumentException(
                'Formula contains restricted ingredients: '.implode(', ', $validationResult['violations'])
            );
        }

        return DB::transaction(function () use ($tenantId, $formulaId, $formula, $data) {
            $registration = new ProductRegistration;
            $registration->tenant_id = $tenantId;
            $registration->formula_id = $formulaId;
            $registration->registration_number = $data['registration_number'] ?? $this->generateRegistrationNumber();
            $registration->product_name = $data['product_name'] ?? $formula->formula_name;
            $registration->product_category = $data['product_category'] ?? $formula->product_type;
            $registration->registration_type = $data['registration_type'] ?? 'notification';
            $registration->status = 'pending';
            $registration->submission_date = $data['submission_date'] ?? now();
            $registration->expiry_date = $data['expiry_date'] ?? now()->addYears(5);
            $registration->notes = $data['notes'] ?? null;
            $registration->submitted_by = Auth::check() ? Auth::id() : null;
            $registration->save();

            Log::info('BPOM registration created', [
                'registration_id' => $registration->id,
                'registration_number' => $registration->registration_number,
                'formula_id' => $formulaId,
            ]);

            return $registration;
        });
    }

    /**
     * Submit registration to BPOM
     */
    public function submitRegistration(ProductRegistration $registration): ProductRegistration
    {
        if ($registration->status !== 'pending') {
            throw new \InvalidArgumentException('Registration must be in pending status');
        }

        // Check required documents
        $requiredDocs = ['formula', 'label', 'test_report'];
        $existingDocs = $registration->documents->pluck('document_type')->toArray();
        $missingDocs = array_diff($requiredDocs, $existingDocs);

        if (! empty($missingDocs)) {
            throw new \InvalidArgumentException(
                'Missing required documents: '.implode(', ', $missingDocs)
            );
        }

        $registration->status = 'submitted';
        $registration->submission_date = now()->format('Y-m-d');
        $registration->save();

        Log::info('BPOM registration submitted', [
            'registration_id' => $registration->id,
        ]);

        return $registration;
    }

    /**
     * Approve registration
     */
    public function approveRegistration(ProductRegistration $registration, string $notifiedBy = ''): ProductRegistration
    {
        $registration->status = 'approved';
        $registration->approval_date = now()->format('Y-m-d');
        $registration->notified_by = $notifiedBy;
        $registration->save();

        Log::info('BPOM registration approved', [
            'registration_id' => $registration->id,
            'notified_by' => $notifiedBy,
        ]);

        return $registration;
    }

    /**
     * Reject registration
     */
    public function rejectRegistration(ProductRegistration $registration, string $reason): ProductRegistration
    {
        $registration->status = 'rejected';
        $registration->notes = ($registration->notes ? $registration->notes."\n\n" : '').
            'Rejected: '.$reason;
        $registration->save();

        Log::warning('BPOM registration rejected', [
            'registration_id' => $registration->id,
            'reason' => $reason,
        ]);

        return $registration;
    }

    /**
     * TASK-2.35: Get expiring registrations
     */
    public function getExpiringRegistrations(int $tenantId, int $days = 90): array
    {
        $expiring = ProductRegistration::where('tenant_id', $tenantId)
            ->where('status', 'approved')
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays($days)])
            ->orderBy('expiry_date')
            ->get();

        $expired = ProductRegistration::where('tenant_id', $tenantId)
            ->where('status', 'approved')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->orderByDesc('expiry_date')
            ->get();

        return [
            'expiring_soon' => $expiring,
            'expired' => $expired,
            'expiring_count' => $expiring->count(),
            'expired_count' => $expired->count(),
        ];
    }

    /**
     * Get registration statistics
     */
    public function getRegistrationStats(int $tenantId): array
    {
        $total = ProductRegistration::where('tenant_id', $tenantId)->count();
        $pending = ProductRegistration::where('tenant_id', $tenantId)->where('status', 'pending')->count();
        $submitted = ProductRegistration::where('tenant_id', $tenantId)->where('status', 'submitted')->count();
        $approved = ProductRegistration::where('tenant_id', $tenantId)->where('status', 'approved')->count();
        $rejected = ProductRegistration::where('tenant_id', $tenantId)->where('status', 'rejected')->count();
        $expired = ProductRegistration::where('tenant_id', $tenantId)
            ->where('status', 'approved')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->count();

        $approvalRate = $total > 0 ? round(($approved / $total) * 100, 2) : 0;

        return [
            'total' => $total,
            'pending' => $pending,
            'submitted' => $submitted,
            'approved' => $approved,
            'rejected' => $rejected,
            'expired' => $expired,
            'approval_rate' => $approvalRate,
            'expiring_soon' => $this->getExpiringRegistrations($tenantId, 90)['expiring_count'],
        ];
    }

    /**
     * TASK-2.34: Validate ingredients for BPOM compliance
     */
    public function validateIngredientsForBpom(CosmeticFormula $formula): array
    {
        $ingredients = $formula->ingredients;
        $violations = [];
        $warnings = [];

        foreach ($ingredients as $ingredient) {
            $restrictions = IngredientRestriction::where('tenant_id', $formula->tenant_id)
                ->where(function ($query) use ($ingredient) {
                    $query->where('ingredient_name', $ingredient->inci_name)
                        ->orWhere('cas_number', $ingredient->cas_number);
                })
                ->get();

            foreach ($restrictions as $restriction) {
                if ($restriction->restriction_type === 'banned') {
                    $violations[] = "{$ingredient->inci_name} is banned";
                } elseif ($restriction->restriction_type === 'limited' && $ingredient->percentage) {
                    if ($ingredient->percentage > $restriction->max_limit) {
                        $violations[] = "{$ingredient->inci_name} exceeds limit ({$restriction->max_limit}%)";
                    } elseif ($ingredient->percentage > $restriction->max_limit * 0.8) {
                        $warnings[] = "{$ingredient->inci_name} approaching limit ({$restriction->max_limit}%)";
                    }
                }
            }
        }

        return [
            'compliant' => empty($violations),
            'violations' => $violations,
            'warnings' => $warnings,
        ];
    }

    /**
     * Upload registration document
     */
    public function uploadDocument(ProductRegistration $registration, UploadedFile $file, array $data): RegistrationDocument
    {
        $path = $file->store('bpom-documents', 'public');

        $document = new RegistrationDocument;
        $document->tenant_id = $registration->tenant_id;
        $document->registration_id = $registration->id;
        $document->document_name = $data['document_name'] ?? $file->getClientOriginalName();
        $document->document_type = $data['document_type'] ?? 'other';
        $document->file_path = $path;
        $document->file_name = $file->getClientOriginalName();
        $document->file_size = $file->getSize() / 1024; // KB
        $document->description = $data['description'] ?? null;
        $document->save();

        Log::info('BPOM document uploaded', [
            'document_id' => $document->id,
            'registration_id' => $registration->id,
        ]);

        return $document;
    }

    /**
     * TASK-2.37: Generate Certificate of Analysis
     */
    public function generateCertificateOfAnalysis($batch): string
    {
        // This integrates with BatchPdfExportService
        $pdfService = app(BatchPdfExportService::class);

        return $pdfService->generateCertificateOfAnalysis($batch);
    }

    /**
     * TASK-2.38: Build compliance checklist
     */
    public function getComplianceChecklist(CosmeticFormula $formula): array
    {
        $ingredientValidation = $this->validateIngredientsForBpom($formula);
        $registration = ProductRegistration::where('formula_id', $formula->id)
            ->where('status', 'approved')
            ->first();

        $hasSDS = SafetyDataSheet::where('formula_id', $formula->id)
            ->where('status', 'active')
            ->exists();

        $checks = [
            'ingredient_compliance' => [
                'label' => 'Ingredient Compliance',
                'passed' => $ingredientValidation['compliant'],
                'details' => $ingredientValidation['compliant'] ? 'All ingredients compliant' : implode(', ', $ingredientValidation['violations']),
            ],
            'bpom_registration' => [
                'label' => 'BPOM Registration',
                'passed' => $registration !== null,
                'details' => $registration ? "Approved ({$registration->registration_number})" : 'Not registered',
            ],
            'safety_data_sheet' => [
                'label' => 'Safety Data Sheet',
                'passed' => $hasSDS,
                'details' => $hasSDS ? 'SDS available' : 'SDS not created',
            ],
            'formula_documented' => [
                'label' => 'Formula Documented',
                'passed' => $formula->ingredients->count() > 0,
                'details' => "{$formula->ingredients->count()} ingredients documented",
            ],
            'stability_tested' => [
                'label' => 'Stability Tested',
                'passed' => $formula->stabilityTests->where('overall_result', 'Pass')->count() > 0,
                'details' => $formula->stabilityTests->count().' tests conducted',
            ],
        ];

        $passedCount = collect($checks)->where('passed', true)->count();
        $totalChecks = count($checks);

        return [
            'checks' => $checks,
            'passed' => $passedCount,
            'total' => $totalChecks,
            'percentage' => round(($passedCount / $totalChecks) * 100, 2),
            'fully_compliant' => $passedCount === $totalChecks,
        ];
    }

    /**
     * Generate registration number
     */
    protected function generateRegistrationNumber(): string
    {
        $year = now()->format('Y');
        $count = ProductRegistration::whereYear('created_at', $year)->count() + 1;

        return 'BPOM-'.$year.'-'.str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Mark expired registrations
     */
    public function markExpiredRegistrations(int $tenantId): int
    {
        $count = ProductRegistration::where('tenant_id', $tenantId)
            ->where('status', 'approved')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->update(['status' => 'expired']);

        if ($count > 0) {
            Log::warning("Marked {$count} BPOM registrations as expired", [
                'tenant_id' => $tenantId,
            ]);
        }

        return $count;
    }
}
