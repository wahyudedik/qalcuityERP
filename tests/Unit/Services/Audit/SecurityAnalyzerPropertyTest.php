<?php

namespace Tests\Unit\Services\Audit;

use App\Services\Audit\SecurityAnalyzer;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

class SecurityAnalyzerPropertyTest extends TestCase
{
    use TestTrait;

    /**
     * Property 17: Security Headers Presence.
     */
    #[ErisRepeat(repeat: 100)]
    public function test_property_17_security_headers_presence(): void
    {
        $required = [
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Content-Security-Policy' => "default-src 'self'",
        ];

        $this->forAll(
            Generators::elements(...array_keys($required)),
            Generators::bool()
        )->then(function (string $mutatedHeader, bool $removeHeader) use ($required) {
            $headers = $required;
            if ($removeHeader) {
                unset($headers[$mutatedHeader]);
            } else {
                $headers[$mutatedHeader] = $mutatedHeader === 'Content-Security-Policy'
                    ? ''
                    : 'invalid-value';
            }

            $analyzer = new SecurityAnalyzer(basePath: getcwd());
            $issues = $analyzer->validateSecurityHeaders($headers);

            $this->assertNotEmpty($issues, 'Missing or invalid required header must produce issues.');
            $issueHeaders = array_column($issues, 'header');
            $this->assertContains($mutatedHeader, $issueHeaders);
        });
    }

    /**
     * Property 18: Audit Trail Completeness.
     */
    #[ErisRepeat(repeat: 100)]
    public function test_property_18_audit_trail_completeness(): void
    {
        $this->forAll(
            Generators::bool(),
            Generators::bool(),
            Generators::bool(),
            Generators::bool(),
            Generators::bool()
        )->then(function (
            bool $hasUserId,
            bool $hasTimestamp,
            bool $hasAction,
            bool $hasOldValues,
            bool $hasNewValues
        ) {
            $entry = [];
            if ($hasUserId) {
                $entry['user_id'] = 123;
            }
            if ($hasTimestamp) {
                $entry['timestamp'] = '2026-05-01T20:00:00Z';
            }
            if ($hasAction) {
                $entry['action'] = 'updated';
            }
            if ($hasOldValues) {
                $entry['old_values'] = ['status' => 'draft'];
            }
            if ($hasNewValues) {
                $entry['new_values'] = ['status' => 'approved'];
            }

            $analyzer = new SecurityAnalyzer(basePath: getcwd());
            $missing = $analyzer->checkAuditLogEntryCompleteness($entry);

            $allPresent = $hasUserId && $hasTimestamp && $hasAction && $hasOldValues && $hasNewValues;
            if ($allPresent) {
                $this->assertSame([], $missing, 'Complete audit trail must have no missing fields.');
            } else {
                $this->assertNotEmpty($missing, 'Incomplete audit trail must report missing fields.');
            }
        });
    }
}
