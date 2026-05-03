<?php

namespace App\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;

/**
 * Analyzes third-party integrations for security and reliability:
 * - Payment gateway webhook signature validation and idempotency
 * - E-commerce sync conflict handling and rate limiting
 * - Messaging retry logic and credential storage
 * - AI rate limiting, quota management, caching, model auto-switching
 * - HTTP timeout and circuit breaker patterns in service classes
 *
 * Uses file-based static analysis (file_get_contents + regex)
 * rather than booting the full Laravel app.
 *
 * Validates: Requirements 8.1, 8.2, 8.3, 8.4, 8.6
 */
class IntegrationAnalyzer implements AnalyzerInterface
{
    private string $servicePath;
    private string $middlewarePath;
    private string $basePath;

    /**
     * Payment gateway service files to audit.
     *
     * @var string[]
     */
    private array $paymentServiceFiles;

    /**
     * E-commerce / marketplace service files to audit.
     *
     * @var string[]
     */
    private array $ecommerceServiceFiles;

    /**
     * Messaging service files to audit.
     *
     * @var string[]
     */
    private array $messagingServiceFiles;

    /**
     * AI service files to audit.
     *
     * @var string[]
     */
    private array $aiServiceFiles;

    /**
     * Patterns indicating webhook signature validation.
     */
    private const SIGNATURE_VALIDATION_PATTERNS = [
        '/signature/i',
        '/verify.*sign/i',
        '/hash_hmac\s*\(/i',
        '/openssl_verify\s*\(/i',
        '/Signature::verify/i',
        '/validateSignature/i',
        '/checkSignature/i',
        '/server_key/i',
        '/secret_key.*hmac/i',
    ];

    /**
     * Patterns indicating idempotency handling.
     */
    private const IDEMPOTENCY_PATTERNS = [
        '/idempoten/i',
        '/order_id.*unique/i',
        '/transaction_id.*exists/i',
        '/duplicate.*check/i',
        '/already.*processed/i',
        '/firstOrCreate/i',
        '/updateOrCreate/i',
        '/WebhookIdempotency/i',
    ];

    /**
     * Patterns indicating rate limiting.
     */
    private const RATE_LIMIT_PATTERNS = [
        '/rate.?limit/i',
        '/throttle/i',
        '/RateLimiter/i',
        '/too.?many.?requests/i',
        '/429/i',
        '/sleep\s*\(/i',
        '/retry.*after/i',
        '/RateLimit/i',
    ];

    /**
     * Patterns indicating conflict handling in sync operations.
     */
    private const CONFLICT_HANDLING_PATTERNS = [
        '/conflict/i',
        '/merge.*strategy/i',
        '/last.*write.*win/i',
        '/version.*check/i',
        '/optimistic.*lock/i',
        '/updated_at.*compare/i',
        '/ConflictResolution/i',
        '/SyncDataLossPrevention/i',
    ];

    /**
     * Patterns indicating retry logic.
     */
    private const RETRY_PATTERNS = [
        '/retry\s*\(/i',
        '/retries/i',
        '/max.*attempt/i',
        '/backoff/i',
        '/exponential/i',
        '/->retry\s*\(/i',
        '/withRetry/i',
    ];

    /**
     * Patterns indicating secure credential storage.
     */
    private const SECURE_CREDENTIAL_PATTERNS = [
        '/config\s*\(\s*[\'"]/',
        '/env\s*\(\s*[\'"]/',
        '/decrypt\s*\(/',
        '/Crypt::decrypt/',
        '/\$this->.*(?:key|token|secret|credential)/i',
    ];

    /**
     * Patterns indicating HTTP timeout configuration.
     */
    private const TIMEOUT_PATTERNS = [
        '/timeout\s*\(/i',
        '/->timeout\s*\(/i',
        '/connectTimeout/i',
        '/CURLOPT_TIMEOUT/i',
        '/CURLOPT_CONNECTTIMEOUT/i',
        '/[\'"]timeout[\'"]\s*=>/i',
        '/withOptions\s*\(\s*\[.*timeout/is',
    ];

    /**
     * Patterns indicating circuit breaker implementation.
     */
    private const CIRCUIT_BREAKER_PATTERNS = [
        '/circuit.?breaker/i',
        '/CircuitBreaker/i',
        '/half.?open/i',
        '/failure.?count/i',
        '/failure.?threshold/i',
        '/isOpen\s*\(/i',
    ];

    public function __construct(
        ?string $servicePath = null,
        ?string $middlewarePath = null,
        ?string $basePath = null,
        ?array $paymentServiceFiles = null,
        ?array $ecommerceServiceFiles = null,
        ?array $messagingServiceFiles = null,
        ?array $aiServiceFiles = null,
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

        $this->servicePath = $servicePath ?? ($this->basePath . '/app/Services');
        $this->middlewarePath = $middlewarePath ?? ($this->basePath . '/app/Http/Middleware');

        $this->paymentServiceFiles = $paymentServiceFiles ?? [
            $this->servicePath . '/PaymentGatewayService.php',
        ];

        $this->ecommerceServiceFiles = $ecommerceServiceFiles ?? [
            $this->servicePath . '/MarketplaceSyncService.php',
            $this->servicePath . '/EcommerceService.php',
        ];

        $this->messagingServiceFiles = $messagingServiceFiles ?? [
            $this->servicePath . '/WhatsAppService.php',
            $this->servicePath . '/BotService.php',
        ];

        $this->aiServiceFiles = $aiServiceFiles ?? [
            $this->servicePath . '/GeminiService.php',
            $this->servicePath . '/AiResponseCacheService.php',
            $this->servicePath . '/AiQuotaService.php',
        ];
    }

    /**
     * Run the full integration analysis.
     *
     * @return AuditFinding[]
     */
    public function analyze(): array
    {
        $findings = [];

        array_push($findings, ...$this->checkPaymentGateways());
        array_push($findings, ...$this->checkEcommerceIntegrations());
        array_push($findings, ...$this->checkMessagingServices());
        array_push($findings, ...$this->checkAiIntegration());

        // Scan all service files for HTTP patterns
        $serviceFiles = $this->discoverPhpFiles($this->servicePath);
        foreach ($serviceFiles as $filePath) {
            array_push($findings, ...$this->checkHttpPatterns($filePath));
        }

        return $findings;
    }

    /**
     * Get the analyzer category name.
     */
    public function category(): string
    {
        return 'integration';
    }

    /**
     * Audit payment gateway integrations for webhook signature validation and idempotency.
     *
     * Checks Midtrans/Xendit/Duitku service files for:
     * - Webhook signature validation (hash_hmac, openssl_verify, etc.)
     * - Idempotent handling of duplicate webhook notifications
     * - Payment event logging
     *
     * Validates: Requirement 8.1
     *
     * @return AuditFinding[]
     */
    public function checkPaymentGateways(): array
    {
        $findings = [];

        foreach ($this->paymentServiceFiles as $filePath) {
            if (!file_exists($filePath)) {
                continue;
            }

            $sourceCode = @file_get_contents($filePath);
            if ($sourceCode === false) {
                continue;
            }

            $className = $this->resolveClassName($sourceCode);
            if ($className === null) {
                continue;
            }

            $shortClass = $this->shortClassName($className);

            // Check for webhook signature validation
            if (!$this->matchesAnyPattern($sourceCode, self::SIGNATURE_VALIDATION_PATTERNS)) {
                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::Critical,
                    title: "Missing webhook signature validation in {$shortClass}",
                    description: "Payment service {$shortClass} does not appear to validate webhook signatures. "
                        . "Without signature validation, attackers could forge payment notifications "
                        . "to mark orders as paid without actual payment.",
                    file: $this->relativePath($filePath),
                    line: null,
                    recommendation: "Implement webhook signature validation using the payment gateway's "
                        . "server key and hash_hmac() or the gateway's SDK verification method.",
                    metadata: [
                        'service' => $className,
                        'check' => 'payment_signature',
                    ],
                );
            }

            // Check for idempotency handling
            if (!$this->matchesAnyPattern($sourceCode, self::IDEMPOTENCY_PATTERNS)) {
                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::High,
                    title: "Missing idempotency handling in {$shortClass}",
                    description: "Payment service {$shortClass} does not appear to handle duplicate webhook "
                        . "notifications idempotently. Payment gateways may send the same notification "
                        . "multiple times, which could result in double-processing of payments.",
                    file: $this->relativePath($filePath),
                    line: null,
                    recommendation: "Implement idempotency checks using transaction_id or order_id to detect "
                        . "and skip duplicate notifications. Consider using WebhookIdempotencyService.",
                    metadata: [
                        'service' => $className,
                        'check' => 'payment_idempotency',
                    ],
                );
            }
        }

        return $findings;
    }

    /**
     * Audit e-commerce integrations for sync conflict handling and rate limiting.
     *
     * Checks Shopee/Tokopedia/Lazada service files for:
     * - Conflict handling during order/stock sync
     * - API rate limit respect
     *
     * Validates: Requirement 8.2
     *
     * @return AuditFinding[]
     */
    public function checkEcommerceIntegrations(): array
    {
        $findings = [];

        foreach ($this->ecommerceServiceFiles as $filePath) {
            if (!file_exists($filePath)) {
                continue;
            }

            $sourceCode = @file_get_contents($filePath);
            if ($sourceCode === false) {
                continue;
            }

            $className = $this->resolveClassName($sourceCode);
            if ($className === null) {
                continue;
            }

            $shortClass = $this->shortClassName($className);

            // Check for conflict handling
            if (!$this->matchesAnyPattern($sourceCode, self::CONFLICT_HANDLING_PATTERNS)) {
                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::High,
                    title: "Missing sync conflict handling in {$shortClass}",
                    description: "E-commerce service {$shortClass} does not appear to handle sync conflicts. "
                        . "When syncing orders or stock between the ERP and marketplace platforms, "
                        . "conflicts can arise from concurrent updates, leading to data inconsistencies or overselling.",
                    file: $this->relativePath($filePath),
                    line: null,
                    recommendation: "Implement conflict resolution strategies (e.g., last-write-wins, version checking) "
                        . "and use ConflictResolutionService or SyncDataLossPreventionService for sync operations.",
                    metadata: [
                        'service' => $className,
                        'check' => 'ecommerce_conflict',
                    ],
                );
            }

            // Check for rate limiting
            if (!$this->matchesAnyPattern($sourceCode, self::RATE_LIMIT_PATTERNS)) {
                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::Medium,
                    title: "Missing rate limit handling in {$shortClass}",
                    description: "E-commerce service {$shortClass} does not appear to respect API rate limits. "
                        . "Marketplace APIs enforce rate limits, and exceeding them can result in "
                        . "temporary bans or failed sync operations.",
                    file: $this->relativePath($filePath),
                    line: null,
                    recommendation: "Implement rate limit handling with exponential backoff and respect "
                        . "HTTP 429 responses. Consider using a rate limiter or throttle mechanism.",
                    metadata: [
                        'service' => $className,
                        'check' => 'ecommerce_rate_limit',
                    ],
                );
            }
        }

        return $findings;
    }

    /**
     * Audit messaging service integrations for retry logic and credential storage.
     *
     * Checks WhatsApp/Telegram service files for:
     * - Message delivery retry logic
     * - Secure credential storage (not hardcoded)
     *
     * Validates: Requirement 8.3
     *
     * @return AuditFinding[]
     */
    public function checkMessagingServices(): array
    {
        $findings = [];

        foreach ($this->messagingServiceFiles as $filePath) {
            if (!file_exists($filePath)) {
                continue;
            }

            $sourceCode = @file_get_contents($filePath);
            if ($sourceCode === false) {
                continue;
            }

            $className = $this->resolveClassName($sourceCode);
            if ($className === null) {
                continue;
            }

            $shortClass = $this->shortClassName($className);

            // Check for retry logic
            if (!$this->matchesAnyPattern($sourceCode, self::RETRY_PATTERNS)) {
                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::Medium,
                    title: "Missing retry logic in {$shortClass}",
                    description: "Messaging service {$shortClass} does not appear to implement retry logic "
                        . "for failed message deliveries. Transient network failures or API rate limits "
                        . "can cause message delivery to fail without retries.",
                    file: $this->relativePath($filePath),
                    line: null,
                    recommendation: "Implement retry logic with exponential backoff for message delivery failures. "
                        . "Use Laravel's retry() helper or a dedicated retry mechanism.",
                    metadata: [
                        'service' => $className,
                        'check' => 'messaging_retry',
                    ],
                );
            }

            // Check for secure credential storage
            if (!$this->matchesAnyPattern($sourceCode, self::SECURE_CREDENTIAL_PATTERNS)) {
                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::High,
                    title: "Potentially insecure credential storage in {$shortClass}",
                    description: "Messaging service {$shortClass} does not appear to load API credentials "
                        . "from config() or env(). Credentials should never be hardcoded in service files.",
                    file: $this->relativePath($filePath),
                    line: null,
                    recommendation: "Store API credentials in .env and access them via config() or env(). "
                        . "For per-tenant credentials, use encrypted database storage with Crypt::decrypt().",
                    metadata: [
                        'service' => $className,
                        'check' => 'messaging_credentials',
                    ],
                );
            }
        }

        return $findings;
    }

    /**
     * Audit AI integration for rate limiting, quota management, caching, and model auto-switching.
     *
     * Checks GeminiService and related files for:
     * - Rate limiting (RateLimitAiRequests middleware)
     * - Quota management (CheckAiQuota middleware)
     * - Response caching (AiResponseCacheService)
     * - Model auto-switching (AiModelSwitchLog)
     *
     * Validates: Requirement 8.4
     *
     * @return AuditFinding[]
     */
    public function checkAiIntegration(): array
    {
        $findings = [];

        // Collect all AI service source code for cross-referencing
        $aiSources = [];
        foreach ($this->aiServiceFiles as $filePath) {
            if (!file_exists($filePath)) {
                continue;
            }

            $sourceCode = @file_get_contents($filePath);
            if ($sourceCode === false) {
                continue;
            }

            $className = $this->resolveClassName($sourceCode);
            if ($className === null) {
                continue;
            }

            $aiSources[] = [
                'path' => $filePath,
                'source' => $sourceCode,
                'class' => $className,
                'shortClass' => $this->shortClassName($className),
            ];
        }

        if (empty($aiSources)) {
            return $findings;
        }

        // Combine all AI source code for cross-referencing
        $combinedSource = implode("\n", array_column($aiSources, 'source'));

        // Also check middleware directory for AI-specific middleware
        $middlewareSource = '';
        $middlewareFiles = $this->discoverPhpFiles($this->middlewarePath);
        foreach ($middlewareFiles as $mwFile) {
            $mwContent = @file_get_contents($mwFile);
            if ($mwContent !== false) {
                $middlewareSource .= "\n" . $mwContent;
            }
        }

        $allSource = $combinedSource . "\n" . $middlewareSource;

        // Check for rate limiting
        $rateLimitPatterns = [
            '/RateLimitAiRequests/i',
            '/rate.?limit.*ai/i',
            '/ai.*rate.?limit/i',
            '/throttle.*ai/i',
        ];
        if (!$this->matchesAnyPattern($allSource, $rateLimitPatterns)) {
            $primaryFile = $aiSources[0];
            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::High,
                title: "Missing AI rate limiting",
                description: "No RateLimitAiRequests middleware or AI-specific rate limiting was found. "
                    . "Without rate limiting, a single tenant could exhaust the AI API quota, "
                    . "affecting all other tenants.",
                file: $this->relativePath($primaryFile['path']),
                line: null,
                recommendation: "Implement RateLimitAiRequests middleware to enforce per-tenant AI request limits.",
                metadata: [
                    'check' => 'ai_rate_limit',
                ],
            );
        }

        // Check for quota management
        $quotaPatterns = [
            '/CheckAiQuota/i',
            '/ai.*quota/i',
            '/quota.*check/i',
            '/usage.*limit/i',
            '/AiQuota/i',
        ];
        if (!$this->matchesAnyPattern($allSource, $quotaPatterns)) {
            $primaryFile = $aiSources[0];
            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Medium,
                title: "Missing AI quota management",
                description: "No CheckAiQuota middleware or quota management was found for AI services. "
                    . "Without quota management, tenants could exceed their plan's AI usage limits.",
                file: $this->relativePath($primaryFile['path']),
                line: null,
                recommendation: "Implement CheckAiQuota middleware to enforce per-tenant AI usage quotas "
                    . "based on subscription plan.",
                metadata: [
                    'check' => 'ai_quota',
                ],
            );
        }

        // Check for response caching
        $cachingPatterns = [
            '/AiResponseCache/i',
            '/cache.*ai.*response/i',
            '/ai.*response.*cache/i',
            '/Cache\s*::\s*remember/i',
            '/->remember\s*\(/i',
        ];
        if (!$this->matchesAnyPattern($combinedSource, $cachingPatterns)) {
            $primaryFile = $aiSources[0];
            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Low,
                title: "Missing AI response caching",
                description: "No response caching pattern was found in AI service files. "
                    . "Caching identical AI responses reduces API costs and improves response times.",
                file: $this->relativePath($primaryFile['path']),
                line: null,
                recommendation: "Implement response caching using AiResponseCacheService to cache "
                    . "frequently requested AI responses with appropriate TTL.",
                metadata: [
                    'check' => 'ai_caching',
                ],
            );
        }

        // Check for model auto-switching
        $switchingPatterns = [
            '/AiModelSwitch/i',
            '/model.*switch/i',
            '/fallback.*model/i',
            '/auto.*switch/i',
            '/switchModel/i',
            '/AllModelsUnavailable/i',
        ];
        if (!$this->matchesAnyPattern($combinedSource, $switchingPatterns)) {
            $primaryFile = $aiSources[0];
            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Medium,
                title: "Missing AI model auto-switching",
                description: "No model auto-switching pattern was found in AI service files. "
                    . "When the primary AI model is unavailable or rate-limited, the system should "
                    . "automatically switch to a fallback model to maintain availability.",
                file: $this->relativePath($primaryFile['path']),
                line: null,
                recommendation: "Implement model auto-switching logic that falls back to alternative "
                    . "AI models when the primary model is unavailable. Log switches via AiModelSwitchLog.",
                metadata: [
                    'check' => 'ai_model_switching',
                ],
            );
        }

        return $findings;
    }

    /**
     * Check a service class for missing HTTP timeout, retry logic, and circuit breaker patterns.
     *
     * Scans for HTTP client usage (Http::, Guzzle, curl) and verifies
     * that timeout configuration, retry logic, and circuit breaker patterns are present.
     *
     * Validates: Requirement 8.6
     *
     * @param string $serviceClass Absolute path to the service file
     * @return AuditFinding[]
     */
    public function checkHttpPatterns(string $serviceClass): array
    {
        $findings = [];

        if (!file_exists($serviceClass)) {
            return $findings;
        }

        $sourceCode = @file_get_contents($serviceClass);
        if ($sourceCode === false) {
            return $findings;
        }

        // Only check files that make HTTP requests
        $httpPatterns = [
            '/Http\s*::\s*(get|post|put|patch|delete|head|send|withHeaders|withToken|withBody)\s*\(/i',
            '/new\s+Client\s*\(/i',
            '/GuzzleHttp/i',
            '/curl_init\s*\(/i',
            '/curl_exec\s*\(/i',
        ];

        if (!$this->matchesAnyPattern($sourceCode, $httpPatterns)) {
            return $findings;
        }

        $className = $this->resolveClassName($sourceCode);
        if ($className === null) {
            return $findings;
        }

        if ($this->isAbstractOrInterfaceOrTrait($sourceCode)) {
            return $findings;
        }

        $shortClass = $this->shortClassName($className);

        // Check for timeout configuration
        if (!$this->matchesAnyPattern($sourceCode, self::TIMEOUT_PATTERNS)) {
            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::High,
                title: "Missing HTTP timeout in {$shortClass}",
                description: "Service {$shortClass} makes HTTP requests but does not configure timeouts. "
                    . "Without timeouts, requests to unresponsive external services can hang indefinitely, "
                    . "consuming server resources and blocking user requests.",
                file: $this->relativePath($serviceClass),
                line: null,
                recommendation: "Add timeout configuration to HTTP requests, e.g., "
                    . "Http::timeout(30)->get(...) or set 'timeout' in Guzzle options.",
                metadata: [
                    'service' => $className,
                    'check' => 'http_timeout',
                ],
            );
        }

        // Check for retry logic
        if (!$this->matchesAnyPattern($sourceCode, self::RETRY_PATTERNS)) {
            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Medium,
                title: "Missing HTTP retry logic in {$shortClass}",
                description: "Service {$shortClass} makes HTTP requests but does not implement retry logic. "
                    . "Transient network failures or temporary service outages can cause requests to fail "
                    . "without retries, leading to unnecessary errors.",
                file: $this->relativePath($serviceClass),
                line: null,
                recommendation: "Implement retry logic with exponential backoff, e.g., "
                    . "Http::retry(3, 100)->get(...) or use Laravel's retry() helper.",
                metadata: [
                    'service' => $className,
                    'check' => 'http_retry',
                ],
            );
        }

        // Check for circuit breaker pattern
        if (!$this->matchesAnyPattern($sourceCode, self::CIRCUIT_BREAKER_PATTERNS)) {
            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Low,
                title: "Missing circuit breaker pattern in {$shortClass}",
                description: "Service {$shortClass} makes HTTP requests but does not implement a circuit breaker. "
                    . "Without a circuit breaker, repeated calls to a failing external service will continue "
                    . "to timeout, degrading overall system performance.",
                file: $this->relativePath($serviceClass),
                line: null,
                recommendation: "Implement a circuit breaker pattern that tracks failure counts and "
                    . "temporarily stops calling the external service after a threshold is reached.",
                metadata: [
                    'service' => $className,
                    'check' => 'http_circuit_breaker',
                ],
            );
        }

        return $findings;
    }

    // ── Private Helpers ──────────────────────────────────────────

    /**
     * Check if source code matches any of the given regex patterns.
     *
     * @param string $sourceCode Source code to search
     * @param string[] $patterns Array of regex patterns
     */
    private function matchesAnyPattern(string $sourceCode, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $sourceCode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Recursively discover all PHP files under a directory.
     *
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
     * Resolve a fully-qualified class name from PHP source code.
     */
    private function resolveClassName(string $sourceCode): ?string
    {
        $namespace = null;
        $className = null;

        if (preg_match('/^\s*namespace\s+([^;]+)\s*;/m', $sourceCode, $matches)) {
            $namespace = trim($matches[1]);
        }

        if (preg_match('/^\s*(?:final\s+)?class\s+(\w+)/m', $sourceCode, $matches)) {
            $className = $matches[1];
        }

        if ($className === null) {
            return null;
        }

        return $namespace ? "{$namespace}\\{$className}" : $className;
    }

    /**
     * Check if the source code defines an abstract class, interface, or trait.
     */
    private function isAbstractOrInterfaceOrTrait(string $sourceCode): bool
    {
        return (bool) preg_match('/^\s*(?:abstract\s+class|interface|trait)\s+/m', $sourceCode);
    }

    /**
     * Get the short class name (without namespace).
     */
    private function shortClassName(string $className): string
    {
        $parts = explode('\\', $className);
        return end($parts);
    }

    /**
     * Convert an absolute path to a relative path from the project root.
     */
    private function relativePath(string $absolutePath): string
    {
        $basePath = $this->basePath . '/';
        if (str_starts_with($absolutePath, $basePath)) {
            return substr($absolutePath, strlen($basePath));
        }

        return $absolutePath;
    }
}
