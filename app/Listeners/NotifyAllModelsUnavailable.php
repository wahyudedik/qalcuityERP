<?php

namespace App\Listeners;

use App\Events\AllModelsUnavailable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Handles the AllModelsUnavailable event by sending alerts
 * via Slack webhook and/or email based on configuration.
 *
 * Requirements: 10.1, 10.2
 */
class NotifyAllModelsUnavailable
{
    /**
     * Handle the event.
     */
    public function handle(AllModelsUnavailable $event): void
    {
        $slackWebhookUrl = config('services.slack.error_webhook_url', env('SLACK_ERROR_WEBHOOK_URL'));
        $emailRecipients = config('services.alert.email_recipients', env('ERROR_ALERT_EMAIL_RECIPIENTS'));

        if (empty($slackWebhookUrl) && empty($emailRecipients)) {
            Log::warning('AllModelsUnavailable: no alert channels configured (SLACK_ERROR_WEBHOOK_URL and ERROR_ALERT_EMAIL_RECIPIENTS are both empty).');
            return;
        }

        if (!empty($slackWebhookUrl)) {
            $this->sendSlackAlert($slackWebhookUrl, $event);
        }

        if (!empty($emailRecipients)) {
            $this->sendEmailAlert($emailRecipients, $event);
        }
    }

    /**
     * Send an alert to the configured Slack webhook.
     */
    protected function sendSlackAlert(string $webhookUrl, AllModelsUnavailable $event): void
    {
        try {
            $modelList = implode(', ', $event->unavailableModels);
            $tenantInfo = $event->triggeredByTenantId
                ? " (triggered by tenant #{$event->triggeredByTenantId})"
                : '';

            $payload = [
                'text' => ":rotating_light: *Gemini AI — All Models Unavailable*{$tenantInfo}",
                'attachments' => [
                    [
                        'color' => 'danger',
                        'fields' => [
                            [
                                'title' => 'Unavailable Models',
                                'value' => $modelList ?: 'N/A',
                                'short' => false,
                            ],
                            [
                                'title' => 'Time',
                                'value' => now()->toDateTimeString(),
                                'short' => true,
                            ],
                            [
                                'title' => 'Environment',
                                'value' => app()->environment(),
                                'short' => true,
                            ],
                        ],
                        'footer' => 'Gemini Auto-Switching',
                    ],
                ],
            ];

            $response = Http::timeout(5)->post($webhookUrl, $payload);

            if (!$response->successful()) {
                Log::error('NotifyAllModelsUnavailable: Slack webhook returned non-success status.', [
                    'status' => $response->status(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('NotifyAllModelsUnavailable: failed to send Slack alert.', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send an alert email to the configured recipients.
     */
    protected function sendEmailAlert(string $recipients, AllModelsUnavailable $event): void
    {
        try {
            $addresses = array_filter(array_map('trim', explode(',', $recipients)));

            if (empty($addresses)) {
                return;
            }

            $modelList = implode(', ', $event->unavailableModels);
            $tenantInfo = $event->triggeredByTenantId
                ? " (triggered by tenant #{$event->triggeredByTenantId})"
                : '';
            $environment = app()->environment();
            $time = now()->toDateTimeString();

            Mail::raw(
                "ALERT: All Gemini AI models are currently unavailable{$tenantInfo}.\n\n"
                . "Unavailable models: {$modelList}\n"
                . "Time: {$time}\n"
                . "Environment: {$environment}\n\n"
                . "Please check the AI model monitoring dashboard and take action if necessary.",
                function ($message) use ($addresses, $environment) {
                    $message
                        ->to($addresses)
                        ->subject("[{$environment}] ALERT: All Gemini AI Models Unavailable");
                }
            );
        } catch (\Throwable $e) {
            Log::error('NotifyAllModelsUnavailable: failed to send email alert.', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
