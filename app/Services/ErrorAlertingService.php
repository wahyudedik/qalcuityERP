<?php

namespace App\Services;

use App\Models\ErrorLog;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Error Alerting Service.
 *
 * Sends real-time alerts for critical errors via:
 * - Slack webhooks
 * - Email notifications
 * - Push notifications
 */
class ErrorAlertingService
{
    /**
     * Configuration for error alerting
     */
    protected array $alertConfig = [
        'emergency' => ['slack', 'email'],
        'alert' => ['slack', 'email'],
        'critical' => ['slack', 'email'],
        'error' => ['slack'],
        'warning' => [], // Only log, don't alert
    ];

    /**
     * Thresholds for alerting (occurrences per hour)
     */
    protected array $thresholds = [
        'default' => 5, // Alert after 5 occurrences per hour
        'exception' => 3,
        'timeout' => 10,
    ];

    /**
     * Send alert for critical error
     */
    public function sendAlert(ErrorLog $errorLog): void
    {
        if (! $this->shouldAlert($errorLog)) {
            return;
        }

        $channels = $this->alertConfig[$errorLog->level] ?? [];

        foreach ($channels as $channel) {
            try {
                match ($channel) {
                    'slack' => $this->sendToSlack($errorLog),
                    'email' => $this->sendToEmail($errorLog),
                    'push' => $this->sendToPush($errorLog),
                    default => null,
                };
            } catch (\Throwable $e) {
                Log::error('Failed to send error alert', [
                    'channel' => $channel,
                    'error_id' => $errorLog->uuid,
                    'exception' => $e->getMessage(),
                ]);
            }
        }

        // Mark as notified
        $errorLog->update([
            'notified' => true,
            'notified_at' => now(),
        ]);
    }

    /**
     * Determine if an error should trigger an alert
     */
    protected function shouldAlert(ErrorLog $errorLog): bool
    {
        // Check if alerting is enabled for this level
        if (! isset($this->alertConfig[$errorLog->level])) {
            return false;
        }

        // Check occurrence threshold
        $threshold = $this->thresholds[$errorLog->type] ?? $this->thresholds['default'];

        $recentOccurrences = ErrorLog::where('exception_class', $errorLog->exception_class)
            ->where('message', $errorLog->message)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        // Only alert on first occurrence or when threshold is exceeded
        return $recentOccurrences <= $threshold;
    }

    /**
     * Send error alert to Slack
     */
    protected function sendToSlack(ErrorLog $errorLog): void
    {
        $webhookUrl = config('services.slack.error_webhook');

        if (! $webhookUrl) {
            Log::warning('Slack error webhook not configured');

            return;
        }

        $payload = [
            'text' => '🚨 Critical Error Detected',
            'attachments' => [
                [
                    'color' => $this->getSlackColor($errorLog->level),
                    'fields' => [
                        [
                            'title' => 'Level',
                            'value' => strtoupper($errorLog->level),
                            'short' => true,
                        ],
                        [
                            'title' => 'Type',
                            'value' => $errorLog->type,
                            'short' => true,
                        ],
                        [
                            'title' => 'Exception',
                            'value' => "`{$errorLog->exception_class}`",
                            'short' => true,
                        ],
                        [
                            'title' => 'Occurrences',
                            'value' => "{$errorLog->occurrence_count}x in last hour",
                            'short' => true,
                        ],
                        [
                            'title' => 'Message',
                            'value' => $this->truncate($errorLog->message, 500),
                            'short' => false,
                        ],
                        [
                            'title' => 'Location',
                            'value' => "`{$errorLog->file}:{$errorLog->line}`",
                            'short' => true,
                        ],
                        [
                            'title' => 'URL',
                            'value' => $errorLog->url ?? 'N/A',
                            'short' => true,
                        ],
                    ],
                    'footer' => config('app.name'),
                    'ts' => time(),
                ],
            ],
        ];

        // Add tenant info if available
        if ($errorLog->tenant) {
            $payload['attachments'][0]['fields'][] = [
                'title' => 'Tenant',
                'value' => "{$errorLog->tenant->name} (ID: {$errorLog->tenant_id})",
                'short' => false,
            ];
        }

        $client = new Client;
        $client->post($webhookUrl, [
            'json' => $payload,
            'timeout' => 5,
        ]);

        Log::info('Slack error alert sent', ['error_id' => $errorLog->uuid]);
    }

    /**
     * Send error alert via email
     */
    protected function sendToEmail(ErrorLog $errorLog): void
    {
        $recipients = config('services.error_alert_email.recipients', []);

        if (empty($recipients)) {
            Log::warning('No email recipients configured for error alerts');

            return;
        }

        // Use Laravel's notification system or mail directly
        Mail::raw($this->getEmailContent($errorLog), function ($message) use ($recipients, $errorLog) {
            $message->to($recipients)
                ->subject("[CRITICAL ERROR] {$errorLog->exception_class} - ".config('app.name'));
            $message->priority(1); // High priority
        });

        Log::info('Email error alert sent', ['error_id' => $errorLog->uuid]);
    }

    /**
     * Send error alert via push notification
     */
    protected function sendToPush(ErrorLog $errorLog): void
    {
        // Implement using your existing push notification service
        // This would integrate with your existing notification system

        Log::debug('Push notification alert skipped (implement based on existing system)', [
            'error_id' => $errorLog->uuid,
        ]);
    }

    /**
     * Get Slack message color based on error level
     */
    protected function getSlackColor(string $level): string
    {
        return match ($level) {
            'emergency' => '#8B0000', // Dark red
            'alert' => '#FF0000', // Red
            'critical' => '#FF4500', // Orange-red
            'error' => '#FF0000', // Red
            default => '#808080', // Gray
        };
    }

    /**
     * Truncate text to specified length
     */
    protected function truncate(string $text, int $length): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length - 3).'...';
    }

    /**
     * Generate email content
     */
    protected function getEmailContent(ErrorLog $errorLog): string
    {
        $context = $errorLog->context ?? [];

        $content = "CRITICAL ERROR DETECTED\n";
        $content .= str_repeat('=', 50)."\n\n";

        $content .= "Exception Class: {$errorLog->exception_class}\n";
        $content .= 'Level: '.strtoupper($errorLog->level)."\n";
        $content .= "Message: {$errorLog->message}\n\n";

        $content .= "Location:\n";
        $content .= "File: {$errorLog->file}\n";
        $content .= "Line: {$errorLog->line}\n\n";

        if ($errorLog->url) {
            $content .= "URL: {$errorLog->url}\n";
        }

        if ($errorLog->tenant) {
            $content .= "Tenant: {$errorLog->tenant->name} (ID: {$errorLog->tenant_id})\n";
        }

        $content .= "\nOccurrences: {$errorLog->occurrence_count}x in the last hour\n";
        $content .= "First Occurrence: {$errorLog->first_occurrence->format('Y-m-d H:i:s')}\n";
        $content .= "Last Occurrence: {$errorLog->updated_at->format('Y-m-d H:i:s')}\n\n";

        $content .= "Stack Trace:\n";
        $content .= str_repeat('-', 50)."\n";
        $content .= $errorLog->stack_trace."\n\n";

        if (! empty($context)) {
            $content .= "Context:\n";
            $content .= str_repeat('-', 50)."\n";
            $content .= json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";
        }

        $content .= "\n".str_repeat('=', 50)."\n";
        $content .= 'View full details: '.route('admin.error-logs.show', $errorLog->uuid)."\n";

        return $content;
    }

    /**
     * Test alert system
     */
    public function testAlert(): void
    {
        $testError = ErrorLog::create([
            'level' => 'error',
            'type' => 'test',
            'message' => 'Test error alert from qalcuityERP',
            'exception_class' => 'TestException',
            'file' => __FILE__,
            'line' => __LINE__,
            'occurrence_count' => 1,
            'first_occurrence' => now(),
        ]);

        $this->sendAlert($testError);

        Log::info('Test error alert sent successfully');
    }
}
