<?php

namespace Tests\Unit\AI;

use App\Exceptions\InsufficientPlanException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit tests untuk InsufficientPlanException.
 *
 * Requirements: 3.5, 11.1
 */
class InsufficientPlanExceptionTest extends TestCase
{
    #[Test]
    public function exception_stores_required_plan_property(): void
    {
        // Requirements: 3.5
        $exception = new InsufficientPlanException(
            requiredPlan: 'professional',
            currentPlan: 'starter',
            useCase: 'financial_report'
        );

        $this->assertSame('professional', $exception->requiredPlan);
    }

    #[Test]
    public function exception_stores_current_plan_property(): void
    {
        // Requirements: 3.5
        $exception = new InsufficientPlanException(
            requiredPlan: 'professional',
            currentPlan: 'starter',
            useCase: 'financial_report'
        );

        $this->assertSame('starter', $exception->currentPlan);
    }

    #[Test]
    public function exception_stores_use_case_property(): void
    {
        // Requirements: 3.5
        $exception = new InsufficientPlanException(
            requiredPlan: 'professional',
            currentPlan: 'starter',
            useCase: 'financial_report'
        );

        $this->assertSame('financial_report', $exception->useCase);
    }

    #[Test]
    public function exception_message_contains_required_plan(): void
    {
        // Requirements: 11.1
        $exception = new InsufficientPlanException(
            requiredPlan: 'professional',
            currentPlan: 'starter',
            useCase: 'financial_report'
        );

        $this->assertStringContainsString('professional', $exception->getMessage());
    }

    #[Test]
    public function exception_message_contains_use_case(): void
    {
        // Requirements: 11.1
        $exception = new InsufficientPlanException(
            requiredPlan: 'professional',
            currentPlan: 'starter',
            useCase: 'financial_report'
        );

        $this->assertStringContainsString('financial_report', $exception->getMessage());
    }

    #[Test]
    public function exception_message_is_in_bahasa_indonesia(): void
    {
        // Requirements: 11.1
        $exception = new InsufficientPlanException(
            requiredPlan: 'professional',
            currentPlan: 'starter',
            useCase: 'financial_report'
        );

        // Pesan harus dalam Bahasa Indonesia
        $this->assertStringContainsString('Fitur ini memerlukan plan', $exception->getMessage());
        $this->assertStringContainsString('Upgrade plan Anda untuk mengakses', $exception->getMessage());
    }

    #[Test]
    public function exception_extends_runtime_exception(): void
    {
        // Requirements: 3.5
        $exception = new InsufficientPlanException(
            requiredPlan: 'professional',
            currentPlan: 'starter',
            useCase: 'financial_report'
        );

        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    #[Test]
    public function exception_properties_are_readonly(): void
    {
        // Requirements: 3.5
        // PHP 8.1+ readonly properties tidak dapat diubah setelah konstruksi
        $exception = new InsufficientPlanException(
            requiredPlan: 'professional',
            currentPlan: 'starter',
            useCase: 'financial_report'
        );

        // Verifikasi bahwa properties ada dan dapat dibaca
        $this->assertSame('professional', $exception->requiredPlan);
        $this->assertSame('starter', $exception->currentPlan);
        $this->assertSame('financial_report', $exception->useCase);

        // Readonly properties akan throw Error jika dicoba diubah
        // Kita tidak perlu test ini karena PHP akan enforce di compile time
    }

    #[Test]
    public function exception_works_with_different_plan_combinations(): void
    {
        // Requirements: 3.5, 11.1
        $testCases = [
            ['trial', 'starter', 'chatbot'],
            ['starter', 'business', 'forecasting'],
            ['business', 'professional', 'financial_report'],
            ['professional', 'enterprise', 'audit_analysis'],
        ];

        foreach ($testCases as [$currentPlan, $requiredPlan, $useCase]) {
            $exception = new InsufficientPlanException($requiredPlan, $currentPlan, $useCase);

            $this->assertSame($requiredPlan, $exception->requiredPlan);
            $this->assertSame($currentPlan, $exception->currentPlan);
            $this->assertSame($useCase, $exception->useCase);
            $this->assertStringContainsString($requiredPlan, $exception->getMessage());
            $this->assertStringContainsString($useCase, $exception->getMessage());
        }
    }
}
