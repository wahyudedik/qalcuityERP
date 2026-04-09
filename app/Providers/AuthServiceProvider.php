<?php

namespace App\Providers;

use App\Models\Patient;
use App\Models\Emr;
use App\Models\Diagnosis;
use App\Models\Prescription;
use App\Models\LabResult;
use App\Policies\MedicalRecordPolicy;
use App\Policies\PatientDataPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
            // Patient Data Policy
        Patient::class => PatientDataPolicy::class,

            // Medical Record Policy (applies to multiple models)
        Emr::class => MedicalRecordPolicy::class,
        Diagnosis::class => MedicalRecordPolicy::class,
        Prescription::class => MedicalRecordPolicy::class,
        LabResult::class => MedicalRecordPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Additional policy registrations can be added here
        // if needed for dynamic model-policy mapping
    }
}
