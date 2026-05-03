<?php

namespace App\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;

class SecurityAnalyzer implements AnalyzerInterface
{
    private string $basePath;
    private string $middlewarePath;
    private string $modelPath;
    private string $servicePath;
    private string $controllerPath;

    /**
     * @var string[]
     */
    private const REQUIRED_SECURITY_HEADERS = [
        'X-Frame-Options' => 'DENY',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Content-Security-Policy' => '__NON_EMPTY__',
    ];

    /**
     * @var string[]
     */
    private const CRITICAL_AUDIT_MODELS = [
        'User',
        'Invoice',
        'SalesOrder',
        'PurchaseOrder',
        'Payment',
        'JournalEntry',
        'Asset',
        'Teleconsultation',
        'TourBooking',
        'Workflow',
    ];

    public function __construct(
        ?string $basePath = null,
        ?string $middlewarePath = null,
        ?string $modelPath = null,
        ?string $servicePath = null,
        ?string $controllerPath = null,
    ) {
        if ($basePath !== null) {
            $this->basePath = $basePath;
        } else {
            try {
                $this->basePath = base_path();
            } catch (\Throwable) {
                $this->basePath = getcwd();
            }
        }

        $this->middlewarePath = $middlewarePath ?? ($this->basePath . '/app/Http/Middleware');
        $this->modelPath = $modelPath ?? ($this->basePath . '/app/Models');
        $this->servicePath = $servicePath ?? ($this->basePath . '/app/Services');
        $this->controllerPath = $controllerPath ?? ($this->basePath . '/app/Http/Controllers');
    }

    /**
     * @return AuditFinding[]
     */
    public function analyze(): array
    {
        $findings = [];

        array_push($findings, ...$this->checkAuthenticationFlow());
        array_push($findings, ...$this->checkFileUploadSecurity());
        array_push($findings, ...$this->checkSecurityHeaders());
        array_push($findings, ...$this->checkAuditTrailCoverage());
        array_push($findings, ...$this->checkGdprCompliance());

        return $findings;
    }

    public function category(): string
    {
        return 'security';
    }

    /**
     * @return AuditFinding[]
     */
    public function checkAuthenticationFlow(): array
    {
        $findings = [];
        $allSource = $this->collectPhpSources([
            $this->controllerPath . '/Auth',
            $this->servicePath,
            $this->basePath . '/routes',
            $this->basePath . '/config',
        ]);

        if (!$this->matchesAny($allSource, ['/throttle/i', '/RateLimiter/i', '/TooManyRequests/i'])) {
            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::High,
                title: 'Authentication flow missing brute-force protection signal',
                description: 'No throttle or rate-limiter pattern was found around authentication routes and services.',
                file: 'routes/web.php',
                line: null,
                recommendation: 'Apply throttle middleware or explicit RateLimiter rules to login and OTP endpoints.',
                metadata: ['check' => 'authentication_bruteforce'],
            );
        }

        if (!$this->matchesAny($allSource, ['/Password::/i', '/min:\d{1,2}/i', '/password.*rules/i'])) {
            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Medium,
                title: 'Authentication flow missing password policy signal',
                description: 'No strong password policy pattern was detected in auth validation paths.',
                file: 'app/Http/Controllers/Auth',
                line: null,
                recommendation: 'Enforce password complexity and minimum length using Laravel validation rules or Password::defaults().',
                metadata: ['check' => 'authentication_password_policy'],
            );
        }

        if (!$this->matchesAny($allSource, ['/invalidate\(\)/i', '/regenerateToken\(\)/i', '/session\.lifetime/i'])) {
            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Medium,
                title: 'Authentication flow missing session management signal',
                description: 'Session invalidation/regeneration or configured session lifetime could not be detected.',
                file: 'config/session.php',
                line: null,
                recommendation: 'Ensure logout invalidates sessions, regenerates CSRF token, and session lifetime is explicitly configured.',
                metadata: ['check' => 'authentication_session_management'],
            );
        }

        return $findings;
    }

    /**
     * @return AuditFinding[]
     */
    public function checkFileUploadSecurity(): array
    {
        $findings = [];
        $middlewareFile = $this->middlewarePath . '/ValidateFileUpload.php';

        if (!file_exists($middlewareFile)) {
            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::High,
                title: 'ValidateFileUpload middleware not found',
                description: 'File upload validation middleware is missing.',
                file: 'app/Http/Middleware/ValidateFileUpload.php',
                line: null,
                recommendation: 'Create and apply ValidateFileUpload middleware on file upload routes.',
                metadata: ['check' => 'file_upload_middleware'],
            );
            return $findings;
        }

        $routesSource = $this->collectPhpSources([$this->basePath . '/routes']);
        if (!$this->matchesAny($routesSource, ['/ValidateFileUpload/i', '/validate\.upload/i'])) {
            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Medium,
                title: 'File upload middleware usage not detected in routes',
                description: 'ValidateFileUpload exists but route-level usage was not detected in route files.',
                file: 'routes',
                line: null,
                recommendation: 'Attach ValidateFileUpload middleware to every upload endpoint.',
                metadata: ['check' => 'file_upload_route_coverage'],
            );
        }

        return $findings;
    }

    /**
     * @return AuditFinding[]
     */
    public function checkSecurityHeaders(): array
    {
        $findings = [];
        $middlewareFile = $this->middlewarePath . '/AddSecurityHeaders.php';

        if (!file_exists($middlewareFile)) {
            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Critical,
                title: 'AddSecurityHeaders middleware not found',
                description: 'Security header middleware is missing, so baseline response headers may be absent.',
                file: 'app/Http/Middleware/AddSecurityHeaders.php',
                line: null,
                recommendation: 'Create AddSecurityHeaders middleware and register it globally.',
                metadata: ['check' => 'security_headers_middleware'],
            );
            return $findings;
        }

        $source = (string) @file_get_contents($middlewareFile);
        $headerMap = $this->extractSetHeaderMap($source);
        $issues = $this->validateSecurityHeaders($headerMap);

        foreach ($issues as $issue) {
            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::High,
                title: 'Missing or incorrect security header: ' . $issue['header'],
                description: $issue['message'],
                file: $this->relativePath($middlewareFile),
                line: null,
                recommendation: 'Ensure required header is set with secure default value in AddSecurityHeaders middleware.',
                metadata: ['check' => 'security_headers', 'header' => $issue['header']],
            );
        }

        return $findings;
    }

    /**
     * @return AuditFinding[]
     */
    public function checkAuditTrailCoverage(): array
    {
        $findings = [];

        foreach (self::CRITICAL_AUDIT_MODELS as $model) {
            $path = $this->modelPath . '/' . $model . '.php';
            if (!file_exists($path)) {
                continue;
            }

            $source = (string) @file_get_contents($path);
            if (!preg_match('/AuditsChanges/', $source)) {
                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::High,
                    title: "Missing AuditsChanges trait on {$model}",
                    description: "Critical model {$model} does not appear to use the AuditsChanges trait.",
                    file: $this->relativePath($path),
                    line: null,
                    recommendation: 'Use AuditsChanges trait to capture create/update/delete activity logs.',
                    metadata: ['check' => 'audit_trail_trait', 'model' => $model],
                );
            }
        }

        return $findings;
    }

    /**
     * @return AuditFinding[]
     */
    public function checkGdprCompliance(): array
    {
        $findings = [];
        $gdprSources = $this->collectPhpSources([
            $this->controllerPath,
            $this->servicePath,
            $this->modelPath,
        ]);

        $requiredPatterns = [
            'data_export' => '/(DataRequest|export.*data|download.*data)/i',
            'data_deletion' => '/(delete.*data|erase.*data|forget.*me)/i',
            'consent' => '/(consent|GdprConsent)/i',
        ];

        foreach ($requiredPatterns as $key => $pattern) {
            if (preg_match($pattern, $gdprSources)) {
                continue;
            }

            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Medium,
                title: "Missing GDPR capability signal: {$key}",
                description: "Could not detect implementation pattern for GDPR capability '{$key}'.",
                file: 'app',
                line: null,
                recommendation: 'Implement and document data export, deletion, and consent flows for GDPR compliance.',
                metadata: ['check' => 'gdpr_capability', 'capability' => $key],
            );
        }

        return $findings;
    }

    /**
     * Validate presence and correctness of required headers.
     *
     * @param array<string, string> $headers
     * @return array<int, array{header:string, message:string}>
     */
    public function validateSecurityHeaders(array $headers): array
    {
        $issues = [];

        foreach (self::REQUIRED_SECURITY_HEADERS as $header => $expectedValue) {
            if (!array_key_exists($header, $headers)) {
                $issues[] = [
                    'header' => $header,
                    'message' => "Header {$header} is not configured.",
                ];
                continue;
            }

            $actual = trim($headers[$header]);
            if ($expectedValue === '__NON_EMPTY__') {
                if ($actual === '') {
                    $issues[] = [
                        'header' => $header,
                        'message' => "Header {$header} is present but empty.",
                    ];
                }
                continue;
            }

            if (strcasecmp($actual, $expectedValue) !== 0) {
                $issues[] = [
                    'header' => $header,
                    'message' => "Header {$header} value '{$actual}' does not match expected '{$expectedValue}'.",
                ];
            }
        }

        return $issues;
    }

    /**
     * @param array<string, mixed> $entry
     * @return string[]
     */
    public function checkAuditLogEntryCompleteness(array $entry): array
    {
        $required = ['user_id', 'timestamp', 'action', 'old_values', 'new_values'];
        $missing = [];

        foreach ($required as $field) {
            if (!array_key_exists($field, $entry) || $entry[$field] === null) {
                $missing[] = $field;
            }
        }

        return $missing;
    }

    /**
     * @param string[] $directories
     */
    private function collectPhpSources(array $directories): string
    {
        $buffer = '';

        foreach ($directories as $directory) {
            foreach ($this->discoverPhpFiles($directory) as $file) {
                $source = @file_get_contents($file);
                if ($source !== false) {
                    $buffer .= "\n" . $source;
                }
            }
        }

        return $buffer;
    }

    /**
     * @return string[]
     */
    private function discoverPhpFiles(string $directory): array
    {
        $files = [];
        if (!is_dir($directory)) {
            return $files;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    /**
     * @param string[] $patterns
     */
    private function matchesAny(string $content, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    private function extractSetHeaderMap(string $source): array
    {
        $headers = [];
        preg_match_all(
            '/headers->set\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]*)[\'"]\s*\)/',
            $source,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $headers[$match[1]] = $match[2];
        }

        return $headers;
    }

    private function relativePath(string $absolutePath): string
    {
        $basePath = $this->basePath . '/';
        if (str_starts_with($absolutePath, $basePath)) {
            return substr($absolutePath, strlen($basePath));
        }

        return $absolutePath;
    }
}
