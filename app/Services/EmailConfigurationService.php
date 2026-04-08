<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Email Configuration Service
 * 
 * Provides fallback mechanism for email configuration:
 * 1. Database settings (tenant-specific)
 * 2. Environment variables (.env)
 * 3. Default fallback (log driver)
 * 
 * BUG-014: Email Configuration Fallback Tidak Jelas
 */
class EmailConfigurationService
{
    /**
     * Get email configuration with fallback
     * 
     * Priority:
     * 1. Tenant database settings
     * 2. Environment variables (.env)
     * 3. Default log driver (failsafe)
     * 
     * @param int|null $tenantId
     * @return array
     */
    public function getEmailConfig(?int $tenantId = null): array
    {
        // Try tenant-specific config first
        if ($tenantId) {
            $tenantConfig = $this->getTenantEmailConfig($tenantId);
            if ($tenantConfig && $this->isValidConfig($tenantConfig)) {
                return $tenantConfig;
            }
        }

        // Fallback to environment config
        $envConfig = $this->getEnvEmailConfig();
        if ($this->isValidConfig($envConfig)) {
            return $envConfig;
        }

        // Final fallback: log driver (safe default)
        return $this->getSafeFallbackConfig();
    }

    /**
     * Get tenant-specific email configuration from database
     */
    protected function getTenantEmailConfig(int $tenantId): ?array
    {
        try {
            $tenant = \App\Models\Tenant::find($tenantId);
            if (!$tenant || !$tenant->email_settings) {
                return null;
            }

            $settings = $tenant->email_settings;

            return [
                'driver' => $settings['driver'] ?? 'smtp',
                'host' => $settings['host'] ?? null,
                'port' => $settings['port'] ?? 587,
                'username' => $settings['username'] ?? null,
                'password' => $settings['password'] ?? null,
                'encryption' => $settings['encryption'] ?? 'tls',
                'from_address' => $settings['from_address'] ?? null,
                'from_name' => $settings['from_name'] ?? $tenant->name ?? 'ERP System',
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to get tenant email config', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get email configuration from environment variables
     */
    protected function getEnvEmailConfig(): array
    {
        return [
            'driver' => config('mail.default', env('MAIL_MAILER', 'log')),
            'host' => env('MAIL_HOST'),
            'port' => env('MAIL_PORT', 587),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'from_address' => env('MAIL_FROM_ADDRESS'),
            'from_name' => env('MAIL_FROM_NAME', config('app.name', 'ERP System')),
        ];
    }

    /**
     * Safe fallback configuration (log driver)
     * This ensures emails are logged instead of failing silently
     */
    protected function getSafeFallbackConfig(): array
    {
        Log::warning('Using safe fallback email config (log driver). Email will not be sent, only logged.', [
            'reason' => 'No valid email configuration found in database or .env',
            'recommendation' => 'Configure MAIL_MAILER in .env or set tenant email settings',
        ]);

        return [
            'driver' => 'log',
            'host' => null,
            'port' => null,
            'username' => null,
            'password' => null,
            'encryption' => null,
            'from_address' => 'noreply@localhost',
            'from_name' => 'ERP System (Fallback)',
        ];
    }

    /**
     * Validate email configuration
     */
    protected function isValidConfig(array $config): bool
    {
        $driver = $config['driver'] ?? '';

        // Log and array drivers are always valid (for testing/development)
        if (in_array($driver, ['log', 'array'])) {
            return true;
        }

        // SMTP and other drivers require host
        if (empty($config['host'])) {
            return false;
        }

        return true;
    }

    /**
     * Apply email configuration dynamically
     * 
     * @param array $config
     * @param string $mailerName
     * @return void
     */
    public function applyConfig(array $config, string $mailerName = 'dynamic'): void
    {
        Config::set("mail.mailers.{$mailerName}", [
            'transport' => $config['driver'],
            'host' => $config['host'],
            'port' => $config['port'],
            'encryption' => $config['encryption'],
            'username' => $config['username'],
            'password' => $config['password'],
        ]);

        Config::set('mail.from.address', $config['from_address']);
        Config::set('mail.from.name', $config['from_name']);
        Config::set('mail.default', $mailerName);
    }

    /**
     * Test email configuration
     * 
     * @param array $config
     * @return array
     */
    public function testConfig(array $config): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'details' => [],
        ];

        try {
            // Validate config structure
            if (!$this->isValidConfig($config)) {
                $result['message'] = 'Invalid configuration';
                $result['details'] = [
                    'driver' => $config['driver'] ?? 'missing',
                    'host' => $config['host'] ?? 'missing',
                ];
                return $result;
            }

            // For log/array drivers, always success
            if (in_array($config['driver'], ['log', 'array'])) {
                $result['success'] = true;
                $result['message'] = 'Configuration valid (log/array driver)';
                return $result;
            }

            // For SMTP, try to connect
            if ($config['driver'] === 'smtp') {
                $connection = @fsockopen($config['host'], $config['port'], $errno, $errstr, 5);

                if ($connection) {
                    fclose($connection);
                    $result['success'] = true;
                    $result['message'] = 'Successfully connected to SMTP server';
                } else {
                    $result['message'] = "Failed to connect: {$errstr} ({$errno})";
                }
            }

        } catch (\Exception $e) {
            $result['message'] = 'Test failed: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Get current email configuration status
     */
    public function getStatus(): array
    {
        $currentDriver = config('mail.default', env('MAIL_MAILER', 'log'));

        return [
            'current_driver' => $currentDriver,
            'has_env_config' => !empty(env('MAIL_HOST')),
            'has_tenant_config' => false, // Will be set if tenant context exists
            'is_fallback' => $currentDriver === 'log' && empty(env('MAIL_HOST')),
            'recommendation' => $this->getRecommendation($currentDriver),
        ];
    }

    /**
     * Get recommendation based on current config
     */
    protected function getRecommendation(string $driver): string
    {
        return match ($driver) {
            'log' => 'Emails are being logged only. Configure SMTP for production.',
            'array' => 'Emails are being discarded. Configure SMTP for production.',
            'smtp' => 'SMTP configured. Ensure credentials are valid.',
            default => 'Unknown driver. Check configuration.',
        };
    }
}
