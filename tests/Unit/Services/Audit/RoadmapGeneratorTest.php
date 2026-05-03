<?php

namespace Tests\Unit\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;
use App\Services\Audit\RoadmapGenerator;
use PHPUnit\Framework\TestCase;

class RoadmapGeneratorTest extends TestCase
{
    public function test_severity_based_prioritization_ordering(): void
    {
        $generator = new RoadmapGenerator();
        $result = $generator->generate([
            $this->finding('security', Severity::Low, 'Low issue'),
            $this->finding('security', Severity::Critical, 'Critical issue'),
            $this->finding('security', Severity::High, 'High issue'),
        ]);

        $titles = array_column($result['prioritized_findings'], 'title');
        $this->assertSame(['Critical issue', 'High issue', 'Low issue'], $titles);
    }

    public function test_timeframe_categorization_logic(): void
    {
        $generator = new RoadmapGenerator();

        $this->assertSame('short-term', $generator->categorizeByTimeframe($this->finding('security', Severity::Critical, 'a')));
        $this->assertSame('short-term', $generator->categorizeByTimeframe($this->finding('security', Severity::High, 'b')));
        $this->assertSame('medium-term', $generator->categorizeByTimeframe($this->finding('security', Severity::Medium, 'c')));
        $this->assertSame('long-term', $generator->categorizeByTimeframe($this->finding('security', Severity::Low, 'd')));
    }

    public function test_permission_matrix_output_structure(): void
    {
        $generator = new RoadmapGenerator();
        $matrix = $generator->generatePermissionMatrix([
            $this->finding('permission', Severity::High, 'Missing role check'),
        ]);

        $this->assertNotEmpty($matrix);
        $this->assertArrayHasKey('area', $matrix[0]);
        $this->assertArrayHasKey('issue', $matrix[0]);
        $this->assertArrayHasKey('severity', $matrix[0]);
        $this->assertArrayHasKey('action', $matrix[0]);
    }

    public function test_crud_matrix_output_structure(): void
    {
        $generator = new RoadmapGenerator();
        $finding = new AuditFinding(
            category: 'crud',
            severity: Severity::High,
            title: 'Entity missing destroy action',
            description: 'destroy not implemented',
            file: null,
            line: null,
            recommendation: 'Add destroy endpoint',
            metadata: ['model' => 'Order'],
        );

        $matrix = $generator->generateCrudMatrix([$finding]);

        $this->assertNotEmpty($matrix);
        $this->assertArrayHasKey('entity', $matrix[0]);
        $this->assertArrayHasKey('status', $matrix[0]);
        $this->assertArrayHasKey('issue', $matrix[0]);
    }

    private function finding(string $category, Severity $severity, string $title): AuditFinding
    {
        return new AuditFinding(
            category: $category,
            severity: $severity,
            title: $title,
            description: 'desc',
            file: null,
            line: null,
            recommendation: 'fix',
        );
    }
}
