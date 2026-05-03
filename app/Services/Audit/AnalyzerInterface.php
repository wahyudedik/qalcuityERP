<?php

namespace App\Services\Audit;

use App\DTOs\Audit\AuditFinding;

/**
 * Contract for all ERP audit analyzer classes.
 *
 * Each analyzer inspects a specific domain of the codebase
 * (controllers, models, routes, tenancy, etc.) and returns
 * structured findings for aggregation into an AuditReport.
 */
interface AnalyzerInterface
{
    /**
     * Run the analysis and return findings.
     *
     * @return AuditFinding[]
     */
    public function analyze(): array;

    /**
     * Get the analyzer category name.
     *
     * Used to group findings in the report (e.g., 'controller',
     * 'model', 'route', 'tenancy', 'permissions', 'crud', etc.).
     */
    public function category(): string;
}
