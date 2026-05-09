<?php

namespace Tests\Unit\Listeners;

use App\Events\AllModelsUnavailable;
use App\Listeners\NotifyAllModelsUnavailable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit tests for NotifyAllModelsUnavailable listener.
 *
 * Feature: gemini-model-auto-switching
 * Requirements: 10.2
 */
class NotifyAllModelsUnavailableTest extends TestCase
{
    private NotifyAllModelsUnavailable $listener;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();

        $this->listener = new NotifyAllModelsUnavailable;
    }

    // ── Helper ────────────────────────────────────────────────────

    private function makeEvent(array $models = ['gemini-1.5-pro', 'gemini-1.5-flash'], ?int $tenantId = null): AllModelsUnavailable
    {
        return new AllModelsUnavailable($models, $tenantId);
    }

    // ── Slack webhook ─────────────────────────────────────────────

    #[Test]
    public function it_sends_slack_alert_when_webhook_url_is_configured(): void
    {
        config(['services.slack.error_webhook_url' => 'https://hooks.slack.com/test-webhook']);
        config(['services.alert.email_recipients' => null]);

        $this->listener->handle($this->makeEvent());

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'hooks.slack.com/test-webhook');
        });
    }

    #[Test]
    public function it_includes_unavailable_models_in_slack_payload(): void
    {
        config(['services.slack.error_webhook_url' => 'https://hooks.slack.com/test-webhook']);
        config(['services.alert.email_recipients' => null]);

        $models = ['gemini-1.5-pro', 'gemini-1.5-flash', 'gemini-1.0-pro'];
        $this->listener->handle($this->makeEvent($models));

        Http::assertSent(function ($request) {
            $body = $request->data();
            $attachments = $body['attachments'][0]['fields'] ?? [];
            $modelField = collect($attachments)->firstWhere('title', 'Unavailable Models');

            return $modelField && str_contains($modelField['value'], 'gemini-1.5-pro')
                && str_contains($modelField['value'], 'gemini-1.5-flash');
        });
    }

    #[Test]
    public function it_includes_tenant_id_in_slack_payload_when_provided(): void
    {
        config(['services.slack.error_webhook_url' => 'https://hooks.slack.com/test-webhook']);
        config(['services.alert.email_recipients' => null]);

        $this->listener->handle($this->makeEvent(['gemini-1.5-pro'], tenantId: 42));

        Http::assertSent(function ($request) {
            $body = $request->data();

            return str_contains($body['text'] ?? '', 'tenant #42');
        });
    }

    #[Test]
    public function it_does_not_include_tenant_info_in_slack_when_tenant_id_is_null(): void
    {
        config(['services.slack.error_webhook_url' => 'https://hooks.slack.com/test-webhook']);
        config(['services.alert.email_recipients' => null]);

        $this->listener->handle($this->makeEvent(['gemini-1.5-pro'], tenantId: null));

        Http::assertSent(function ($request) {
            $body = $request->data();

            return ! str_contains($body['text'] ?? '', 'tenant #');
        });
    }

    #[Test]
    public function it_does_not_send_slack_when_webhook_url_is_empty(): void
    {
        config(['services.slack.error_webhook_url' => null]);
        config(['services.alert.email_recipients' => null]);

        $this->listener->handle($this->makeEvent());

        Http::assertNothingSent();
    }

    // ── Email ─────────────────────────────────────────────────────

    #[Test]
    public function it_sends_email_when_recipients_are_configured(): void
    {
        config(['services.slack.error_webhook_url' => null]);
        config(['services.alert.email_recipients' => 'admin@example.com']);

        // Mail::raw() is a no-op in MailFake; use shouldReceive to assert it is called
        Mail::shouldReceive('raw')
            ->once()
            ->withArgs(function ($text, $callback) {
                return str_contains($text, 'Gemini AI') || str_contains($text, 'unavailable');
            });

        $this->listener->handle($this->makeEvent());
    }

    #[Test]
    public function it_sends_email_to_all_configured_recipients(): void
    {
        config(['services.slack.error_webhook_url' => null]);
        config(['services.alert.email_recipients' => 'admin@example.com, ops@example.com']);

        Mail::shouldReceive('raw')->once();

        $this->listener->handle($this->makeEvent());
    }

    #[Test]
    public function it_does_not_send_email_when_recipients_are_empty(): void
    {
        config(['services.slack.error_webhook_url' => null]);
        config(['services.alert.email_recipients' => null]);

        Mail::shouldReceive('raw')->never();

        $this->listener->handle($this->makeEvent());
    }
    // ── Both channels ─────────────────────────────────────────────

    #[Test]
    public function it_sends_both_slack_and_email_when_both_are_configured(): void
    {
        config(['services.slack.error_webhook_url' => 'https://hooks.slack.com/test-webhook']);
        config(['services.alert.email_recipients' => 'admin@example.com']);

        Mail::shouldReceive('raw')->once();

        $this->listener->handle($this->makeEvent());

        Http::assertSent(fn ($r) => str_contains($r->url(), 'hooks.slack.com'));
    }

    // ── No channels configured ────────────────────────────────────

    #[Test]
    public function it_logs_warning_when_no_channels_are_configured(): void
    {
        config(['services.slack.error_webhook_url' => null]);
        config(['services.alert.email_recipients' => null]);

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(fn ($msg) => str_contains($msg, 'AllModelsUnavailable'));

        Mail::shouldReceive('raw')->never();

        $this->listener->handle($this->makeEvent());

        Http::assertNothingSent();
    }

    // ── Various unavailableModels combinations ────────────────────

    #[Test]
    public function it_handles_single_unavailable_model(): void
    {
        config(['services.slack.error_webhook_url' => 'https://hooks.slack.com/test-webhook']);
        config(['services.alert.email_recipients' => null]);

        $this->listener->handle($this->makeEvent(['gemini-1.5-pro']));

        Http::assertSent(function ($request) {
            $body = $request->data();
            $attachments = $body['attachments'][0]['fields'] ?? [];
            $modelField = collect($attachments)->firstWhere('title', 'Unavailable Models');

            return $modelField && str_contains($modelField['value'], 'gemini-1.5-pro');
        });
    }

    #[Test]
    public function it_handles_empty_unavailable_models_array(): void
    {
        config(['services.slack.error_webhook_url' => 'https://hooks.slack.com/test-webhook']);
        config(['services.alert.email_recipients' => null]);

        // Should not throw — gracefully handles empty array
        $this->listener->handle($this->makeEvent([]));

        Http::assertSent(function ($request) {
            $body = $request->data();
            $attachments = $body['attachments'][0]['fields'] ?? [];
            $modelField = collect($attachments)->firstWhere('title', 'Unavailable Models');

            return $modelField && ($modelField['value'] === 'N/A' || $modelField['value'] === '');
        });
    }

    // ── Various triggeredByTenantId combinations ──────────────────

    #[Test]
    public function it_handles_various_tenant_ids_in_slack_payload(): void
    {
        config(['services.slack.error_webhook_url' => 'https://hooks.slack.com/test-webhook']);
        config(['services.alert.email_recipients' => null]);

        foreach ([1, 99, 1000] as $tenantId) {
            Http::fake();

            $this->listener->handle($this->makeEvent(['gemini-1.5-pro'], $tenantId));

            Http::assertSent(function ($request) use ($tenantId) {
                $body = $request->data();

                return str_contains($body['text'] ?? '', "tenant #{$tenantId}");
            });
        }
    }

    #[Test]
    public function it_handles_various_tenant_ids_in_email_body(): void
    {
        config(['services.slack.error_webhook_url' => null]);
        config(['services.alert.email_recipients' => 'admin@example.com']);

        Mail::shouldReceive('raw')
            ->once()
            ->withArgs(function ($text) {
                return str_contains($text, 'tenant #7');
            });

        $this->listener->handle($this->makeEvent(['gemini-1.5-pro'], 7));
    }
}
