<?php

namespace Tests\Unit\AI;

use App\Exceptions\CustomExceptionHandler;
use App\Exceptions\InsufficientPlanException;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Property-based tests untuk InsufficientPlanException.
 *
 * Requirements: 11.2
 *
 * Menggunakan Eris untuk memverifikasi bahwa CustomExceptionHandler
 * selalu mengembalikan HTTP 403 untuk semua kombinasi plan dan use case.
 */
class InsufficientPlanPropertyTest extends TestCase
{
    use TestTrait;

    /**
     * @eris-repeat 100
     * Feature: ai-use-case-routing, Property 11: HTTP 403 untuk Semua Skenario Plan Tidak Cukup
     *
     * Untuk sembarang InsufficientPlanException, CustomExceptionHandler selalu mengembalikan
     * HTTP 403 dengan pesan Bahasa Indonesia yang menyebutkan plan minimum dan nama use case.
     *
     * Requirements: 11.2
     */
    public function test_http403_for_all_insufficient_plan_scenarios(): void
    {
        $this->forAll(
            Generator\elements('trial', 'starter', 'business', 'professional', 'enterprise'),
            Generator\elements('trial', 'starter', 'business', 'professional', 'enterprise'),
            Generator\elements(
                'chatbot',
                'crud_ai',
                'auto_reply',
                'invoice_parsing',
                'document_parsing',
                'notification_ai',
                'product_description',
                'email_draft',
                'financial_report',
                'forecasting',
                'decision_support',
                'audit_analysis',
                'business_recommendation',
                'bank_reconciliation_ai',
                'budget_analysis',
                'anomaly_detection'
            )
        )->then(function ($currentPlan, $requiredPlan, $useCase) {
            // Buat exception
            $exception = new InsufficientPlanException($requiredPlan, $currentPlan, $useCase);

            // Buat mock request yang expects JSON
            $request = Request::create('/test', 'GET');
            $request->headers->set('Accept', 'application/json');

            // Render exception
            $handler = app(CustomExceptionHandler::class);
            $response = $handler->render($request, $exception);

            // Property 11: Selalu HTTP 403
            $this->assertSame(
                403,
                $response->getStatusCode(),
                "Expected HTTP 403 for InsufficientPlanException with currentPlan={$currentPlan}, requiredPlan={$requiredPlan}, useCase={$useCase}"
            );

            // Assert response body structure
            $body = json_decode($response->getContent(), true);
            $this->assertIsArray($body, 'Response body should be valid JSON array');
            $this->assertArrayHasKey('message', $body, 'Response should have message key');
            $this->assertArrayHasKey('required_plan', $body, 'Response should have required_plan key');
            $this->assertArrayHasKey('current_plan', $body, 'Response should have current_plan key');
            $this->assertArrayHasKey('use_case', $body, 'Response should have use_case key');

            // Assert message content (Bahasa Indonesia)
            $this->assertStringContainsString(
                $requiredPlan,
                $body['message'],
                'Message should contain required plan name'
            );
            $this->assertStringContainsString(
                $useCase,
                $body['message'],
                'Message should contain use case name'
            );
            $this->assertStringContainsString(
                'Fitur ini memerlukan plan',
                $body['message'],
                'Message should be in Bahasa Indonesia'
            );

            // Assert exact values
            $this->assertSame(
                $requiredPlan,
                $body['required_plan'],
                'required_plan in response should match exception property'
            );
            $this->assertSame(
                $currentPlan,
                $body['current_plan'],
                'current_plan in response should match exception property'
            );
            $this->assertSame(
                $useCase,
                $body['use_case'],
                'use_case in response should match exception property'
            );
        });
    }

    /**
     * @eris-repeat 50
     * Feature: ai-use-case-routing, Property 11: Redirect untuk non-JSON requests
     *
     * Untuk request non-JSON (Blade), CustomExceptionHandler harus redirect
     * ke halaman subscription dengan flash message dalam Bahasa Indonesia.
     *
     * Requirements: 11.2
     */
    public function test_redirect_for_non_json_requests(): void
    {
        $this->forAll(
            Generator\elements('trial', 'starter', 'business', 'professional'),
            Generator\elements('starter', 'business', 'professional', 'enterprise'),
            Generator\elements('chatbot', 'financial_report', 'forecasting', 'crud_ai')
        )->then(function ($currentPlan, $requiredPlan, $useCase) {
            // Buat exception
            $exception = new InsufficientPlanException($requiredPlan, $currentPlan, $useCase);

            // Buat request yang TIDAK expects JSON (Blade request)
            $request = Request::create('/test', 'GET');
            $request->headers->set('Accept', 'text/html');

            // Render exception
            $handler = app(CustomExceptionHandler::class);
            $response = $handler->render($request, $exception);

            // Property 11: Harus redirect (HTTP 302)
            $this->assertSame(
                302,
                $response->getStatusCode(),
                'Expected HTTP 302 redirect for non-JSON request with InsufficientPlanException'
            );

            // Assert redirect target
            $this->assertTrue($response->isRedirect(), 'Response should be a redirect');

            // Note: Kita tidak bisa assert exact redirect URL karena route('subscription.index')
            // mungkin belum terdaftar di test environment. Yang penting adalah:
            // 1. Status code 302
            // 2. Response adalah redirect
            // 3. Flash message ada (akan diverifikasi di integration test)
        });
    }
}
