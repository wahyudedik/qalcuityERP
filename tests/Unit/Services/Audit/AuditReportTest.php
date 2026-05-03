<?php

namespace Tests\Unit\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;
use App\Services\Audit\AuditReport;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for AuditFinding DTO and AuditReport service.
 *
 * Validates: Requirements 12.1
 */
class AuditReportTest extends TestCase
{
    // ── AuditFinding Construction ─────────────────────────────────

    public function test_audit_finding_can_be_constructed_with_all_fields(): void
    {
        $finding = new AuditFinding(
            category: 'tenancy',
            severity: Severity::Critical,
            title: 'Missing tenant isolation',
            description: 'Model lacks BelongsToTenant trait',
            file: 'app/Models/Invoice.php',
            line: 42,
            recommendation: 'Add BelongsToTenant trait',
            metadata: ['model' => 'Invoice'],
        );

        $this->assertSame('tenancy', $finding->category);
        $this->assertSame(Severity::Critical, $finding->severity);
        $this->assertSame('Missing tenant isolation', $finding->title);
        $this->assertSame('Model lacks BelongsToTenant trait', $finding->description);
        $this->assertSame('app/Models/Invoice.php', $finding->file);
        $this->assertSame(42, $finding->line);
        $this->assertSame('Add BelongsToTenant trait', $finding->recommendation);
        $this->assertSame(['model' => 'Invoice'], $finding->metadata);
    }

    public function test_audit_finding_can_be_constructed_with_nullable_fields_as_null(): void
    {
        $finding = new AuditFinding(
            category: 'security',
            severity: Severity::High,
            title: 'Missing CSRF protection',
            description: 'Route lacks CSRF middleware',
            file: null,
            line: null,
            recommendation: null,
        );

        $this->assertNull($finding->file);
        $this->assertNull($finding->line);
        $this->assertNull($finding->recommendation);
    }

    public function test_audit_finding_metadata_defaults_to_empty_array(): void
    {
        $finding = new AuditFinding(
            category: 'core',
            severity: Severity::Low,
            title: 'Test finding',
            description: 'Description',
            file: null,
            line: null,
            recommendation: null,
        );

        $this->assertSame([], $finding->metadata);
    }

    public function test_audit_finding_properties_are_readonly(): void
    {
        $finding = new AuditFinding(
            category: 'model',
            severity: Severity::Medium,
            title: 'Test',
            description: 'Desc',
            file: 'test.php',
            line: 1,
            recommendation: 'Fix it',
            metadata: ['key' => 'value'],
        );

        $reflection = new \ReflectionClass($finding);
        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isReadOnly(), "Property {$property->getName()} should be readonly");
        }
    }

    // ── AuditReport add() and addAll() ────────────────────────────

    public function test_add_stores_a_single_finding(): void
    {
        $report = new AuditReport();
        $finding = $this->makeFinding('core', Severity::High, 'Finding 1');

        $report->add($finding);

        $this->assertCount(1, $report->getFindings());
        $this->assertSame($finding, $report->getFindings()[0]);
    }

    public function test_add_all_stores_multiple_findings(): void
    {
        $report = new AuditReport();
        $findings = [
            $this->makeFinding('core', Severity::High, 'Finding 1'),
            $this->makeFinding('tenancy', Severity::Critical, 'Finding 2'),
            $this->makeFinding('ui', Severity::Low, 'Finding 3'),
        ];

        $report->addAll($findings);

        $this->assertCount(3, $report->getFindings());
    }

    // ── AuditReport getFindings() filtering ───────────────────────

    public function test_get_findings_with_no_filters_returns_all(): void
    {
        $report = $this->buildMixedReport();

        $all = $report->getFindings();

        $this->assertCount(5, $all);
    }

    public function test_get_findings_filtered_by_category_returns_only_matching(): void
    {
        $report = $this->buildMixedReport();

        $tenancyFindings = $report->getFindings(category: 'tenancy');

        $this->assertCount(2, $tenancyFindings);
        foreach ($tenancyFindings as $f) {
            $this->assertSame('tenancy', $f->category);
        }
    }

    public function test_get_findings_filtered_by_severity_returns_only_matching(): void
    {
        $report = $this->buildMixedReport();

        $criticalFindings = $report->getFindings(severity: Severity::Critical);

        $this->assertCount(2, $criticalFindings);
        foreach ($criticalFindings as $f) {
            $this->assertSame(Severity::Critical, $f->severity);
        }
    }

    public function test_get_findings_filtered_by_both_category_and_severity(): void
    {
        $report = $this->buildMixedReport();

        $results = $report->getFindings(category: 'tenancy', severity: Severity::Critical);

        $this->assertCount(1, $results);
        $this->assertSame('tenancy', $results[0]->category);
        $this->assertSame(Severity::Critical, $results[0]->severity);
    }

    public function test_get_findings_returns_empty_for_non_matching_filter(): void
    {
        $report = $this->buildMixedReport();

        $results = $report->getFindings(category: 'nonexistent');

        $this->assertCount(0, $results);
    }

    public function test_get_findings_returns_reindexed_array(): void
    {
        $report = $this->buildMixedReport();

        $results = $report->getFindings(category: 'security');

        // Should be 0-indexed even after filtering
        $this->assertArrayHasKey(0, $results);
        $this->assertCount(1, $results);
    }

    // ── AuditReport getSummary() ──────────────────────────────────

    public function test_get_summary_returns_correct_counts(): void
    {
        $report = $this->buildMixedReport();

        $summary = $report->getSummary();

        $this->assertSame(5, $summary['total_findings']);

        // by_severity
        $this->assertSame(2, $summary['by_severity']['critical']);
        $this->assertSame(1, $summary['by_severity']['high']);
        $this->assertSame(1, $summary['by_severity']['medium']);
        $this->assertSame(1, $summary['by_severity']['low']);

        // by_category
        $this->assertSame(2, $summary['by_category']['tenancy']);
        $this->assertSame(1, $summary['by_category']['core']);
        $this->assertSame(1, $summary['by_category']['security']);
        $this->assertSame(1, $summary['by_category']['ui']);
    }

    public function test_get_summary_includes_all_severity_keys_even_when_zero(): void
    {
        $report = new AuditReport();
        $report->add($this->makeFinding('core', Severity::Low, 'Only low'));

        $summary = $report->getSummary();

        $this->assertArrayHasKey('critical', $summary['by_severity']);
        $this->assertArrayHasKey('high', $summary['by_severity']);
        $this->assertArrayHasKey('medium', $summary['by_severity']);
        $this->assertArrayHasKey('low', $summary['by_severity']);
        $this->assertSame(0, $summary['by_severity']['critical']);
        $this->assertSame(0, $summary['by_severity']['high']);
        $this->assertSame(0, $summary['by_severity']['medium']);
        $this->assertSame(1, $summary['by_severity']['low']);
    }

    // ── AuditReport toJson() ──────────────────────────────────────

    public function test_to_json_produces_valid_json_with_required_keys(): void
    {
        $report = new AuditReport();
        $report->add($this->makeFinding('core', Severity::High, 'Test finding'));

        $json = $report->toJson();
        $decoded = json_decode($json, true);

        $this->assertNotNull($decoded, 'toJson() must produce valid JSON');
        $this->assertArrayHasKey('generated_at', $decoded);
        $this->assertArrayHasKey('summary', $decoded);
        $this->assertArrayHasKey('findings', $decoded);
    }

    public function test_to_json_summary_matches_design_spec_structure(): void
    {
        $report = new AuditReport();
        $report->add($this->makeFinding('core', Severity::High, 'F1'));
        $report->add($this->makeFinding('tenancy', Severity::Critical, 'F2'));

        $decoded = json_decode($report->toJson(), true);
        $summary = $decoded['summary'];

        $this->assertArrayHasKey('total_findings', $summary);
        $this->assertArrayHasKey('by_severity', $summary);
        $this->assertArrayHasKey('by_category', $summary);
        $this->assertSame(2, $summary['total_findings']);
        $this->assertSame(1, $summary['by_severity']['critical']);
        $this->assertSame(1, $summary['by_severity']['high']);
    }

    public function test_to_json_findings_contain_all_dto_fields(): void
    {
        $report = new AuditReport();
        $report->add(new AuditFinding(
            category: 'model',
            severity: Severity::Medium,
            title: 'Missing fillable',
            description: 'Model uses guarded = []',
            file: 'app/Models/User.php',
            line: 10,
            recommendation: 'Define $fillable explicitly',
            metadata: ['model' => 'User'],
        ));

        $decoded = json_decode($report->toJson(), true);
        $finding = $decoded['findings'][0];

        $this->assertSame('model', $finding['category']);
        $this->assertSame('medium', $finding['severity']);
        $this->assertSame('Missing fillable', $finding['title']);
        $this->assertSame('Model uses guarded = []', $finding['description']);
        $this->assertSame('app/Models/User.php', $finding['file']);
        $this->assertSame(10, $finding['line']);
        $this->assertSame('Define $fillable explicitly', $finding['recommendation']);
        $this->assertSame(['model' => 'User'], $finding['metadata']);
    }

    public function test_to_json_handles_null_fields_in_findings(): void
    {
        $report = new AuditReport();
        $report->add($this->makeFinding('core', Severity::Low, 'Null fields'));

        $decoded = json_decode($report->toJson(), true);
        $finding = $decoded['findings'][0];

        $this->assertNull($finding['file']);
        $this->assertNull($finding['line']);
        $this->assertNull($finding['recommendation']);
    }

    // ── AuditReport toMarkdown() ──────────────────────────────────

    public function test_to_markdown_contains_report_header(): void
    {
        $report = new AuditReport();
        $report->add($this->makeFinding('core', Severity::High, 'Test'));

        $md = $report->toMarkdown();

        $this->assertStringContainsString('# ERP Audit Report', $md);
        $this->assertStringContainsString('**Generated at:**', $md);
    }

    public function test_to_markdown_contains_summary_table(): void
    {
        $report = new AuditReport();
        $report->add($this->makeFinding('core', Severity::High, 'Test'));

        $md = $report->toMarkdown();

        $this->assertStringContainsString('## Summary', $md);
        $this->assertStringContainsString('Total Findings', $md);
        $this->assertStringContainsString('| 1 |', $md);
    }

    public function test_to_markdown_contains_severity_badges(): void
    {
        $report = new AuditReport();
        $report->add($this->makeFinding('core', Severity::Critical, 'Critical issue'));
        $report->add($this->makeFinding('core', Severity::High, 'High issue'));
        $report->add($this->makeFinding('core', Severity::Medium, 'Medium issue'));
        $report->add($this->makeFinding('core', Severity::Low, 'Low issue'));

        $md = $report->toMarkdown();

        $this->assertStringContainsString('🔴 Critical', $md);
        $this->assertStringContainsString('🟠 High', $md);
        $this->assertStringContainsString('🟡 Medium', $md);
        $this->assertStringContainsString('🟢 Low', $md);
    }

    public function test_to_markdown_groups_findings_by_category(): void
    {
        $report = new AuditReport();
        $report->add($this->makeFinding('tenancy', Severity::Critical, 'Tenant issue'));
        $report->add($this->makeFinding('security', Severity::High, 'Security issue'));

        $md = $report->toMarkdown();

        $this->assertStringContainsString('## Tenancy', $md);
        $this->assertStringContainsString('## Security', $md);
    }

    public function test_to_markdown_includes_file_location_when_present(): void
    {
        $report = new AuditReport();
        $report->add(new AuditFinding(
            category: 'core',
            severity: Severity::Medium,
            title: 'Issue with file',
            description: 'Description',
            file: 'app/Models/Invoice.php',
            line: 55,
            recommendation: null,
        ));

        $md = $report->toMarkdown();

        $this->assertStringContainsString('`app/Models/Invoice.php:55`', $md);
    }

    public function test_to_markdown_includes_recommendation_when_present(): void
    {
        $report = new AuditReport();
        $report->add(new AuditFinding(
            category: 'core',
            severity: Severity::Low,
            title: 'Suggestion',
            description: 'Description',
            file: null,
            line: null,
            recommendation: 'Add an index on tenant_id',
        ));

        $md = $report->toMarkdown();

        $this->assertStringContainsString('**Recommendation:** Add an index on tenant_id', $md);
    }

    // ── Empty AuditReport edge case ───────────────────────────────

    public function test_empty_report_get_summary_returns_zero_counts(): void
    {
        $report = new AuditReport();

        $summary = $report->getSummary();

        $this->assertSame(0, $summary['total_findings']);
        $this->assertSame(0, $summary['by_severity']['critical']);
        $this->assertSame(0, $summary['by_severity']['high']);
        $this->assertSame(0, $summary['by_severity']['medium']);
        $this->assertSame(0, $summary['by_severity']['low']);
        $this->assertSame([], $summary['by_category']);
    }

    public function test_empty_report_to_json_produces_valid_json(): void
    {
        $report = new AuditReport();

        $json = $report->toJson();
        $decoded = json_decode($json, true);

        $this->assertNotNull($decoded);
        $this->assertSame(0, $decoded['summary']['total_findings']);
        $this->assertSame([], $decoded['findings']);
    }

    public function test_empty_report_to_markdown_produces_valid_output(): void
    {
        $report = new AuditReport();

        $md = $report->toMarkdown();

        $this->assertStringContainsString('# ERP Audit Report', $md);
        $this->assertStringContainsString('## Summary', $md);
        $this->assertStringContainsString('| Total Findings | 0 |', $md);
    }

    public function test_empty_report_get_findings_returns_empty_array(): void
    {
        $report = new AuditReport();

        $this->assertSame([], $report->getFindings());
        $this->assertSame([], $report->getFindings(category: 'core'));
        $this->assertSame([], $report->getFindings(severity: Severity::Critical));
    }

    // ── Helpers ───────────────────────────────────────────────────

    /**
     * Build a report with a known mix of categories and severities.
     *
     * Contents:
     *  - tenancy / Critical
     *  - tenancy / High
     *  - core / Medium
     *  - security / Critical
     *  - ui / Low
     */
    private function buildMixedReport(): AuditReport
    {
        $report = new AuditReport();
        $report->addAll([
            $this->makeFinding('tenancy', Severity::Critical, 'Tenant critical'),
            $this->makeFinding('tenancy', Severity::High, 'Tenant high'),
            $this->makeFinding('core', Severity::Medium, 'Core medium'),
            $this->makeFinding('security', Severity::Critical, 'Security critical'),
            $this->makeFinding('ui', Severity::Low, 'UI low'),
        ]);

        return $report;
    }

    private function makeFinding(string $category, Severity $severity, string $title): AuditFinding
    {
        return new AuditFinding(
            category: $category,
            severity: $severity,
            title: $title,
            description: "Description for {$title}",
            file: null,
            line: null,
            recommendation: null,
        );
    }
}
