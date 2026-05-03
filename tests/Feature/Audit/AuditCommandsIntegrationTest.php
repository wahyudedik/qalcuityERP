<?php

namespace Tests\Feature\Audit;

use App\Services\Audit\ControllerAnalyzer;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;
use Tests\TestCase;

class AuditCommandsIntegrationTest extends TestCase
{
    public function test_audit_all_runs_and_outputs_json_structure(): void
    {
        $code = Artisan::call('audit:all', ['--format' => 'json']);

        $this->assertSame(0, $code);
        $output = Artisan::output();
        $payload = json_decode($output, true);

        $this->assertIsArray($payload);
        $this->assertArrayHasKey('summary', $payload);
        $this->assertArrayHasKey('findings', $payload);
    }

    public function test_audit_all_handles_analyzer_failure_as_finding(): void
    {
        $this->app->bind(ControllerAnalyzer::class, static function (): ControllerAnalyzer {
            throw new RuntimeException('forced analyzer failure for test');
        });

        $code = Artisan::call('audit:all', ['--format' => 'json']);

        $this->assertSame(0, $code);
        $output = Artisan::output();
        $this->assertStringContainsString('Analyzer execution failed', $output);
        $this->assertStringContainsString('forced analyzer failure for test', $output);
    }

    public function test_audit_all_severity_filter_limits_output(): void
    {
        $code = Artisan::call('audit:all', ['--format' => 'json', '--severity' => 'critical']);

        $this->assertSame(0, $code);
        $output = Artisan::output();
        $payload = json_decode($output, true);
        $this->assertIsArray($payload);

        foreach ($payload['findings'] as $finding) {
            $this->assertSame('critical', $finding['severity']);
        }
    }

    public function test_individual_audit_commands_run_successfully(): void
    {
        $commands = [
            ['audit:core', ['--format' => 'json']],
            ['audit:tenancy', ['--format' => 'json']],
            ['audit:permissions', ['--format' => 'json']],
            ['audit:crud', ['--format' => 'json']],
            ['audit:database', ['--format' => 'json', '--dry-run' => true]],
        ];

        foreach ($commands as [$name, $options]) {
            $code = Artisan::call($name, $options);
            $this->assertSame(0, $code, "Command {$name} should exit successfully.");
        }
    }
}
