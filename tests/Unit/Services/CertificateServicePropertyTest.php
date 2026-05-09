<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Services\CertificateService;
use App\Services\ProductQrService;
use Carbon\Carbon;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

/**
 * Property-Based Tests for CertificateService.
 *
 * Feature: product-qr-certificate
 */
class CertificateServicePropertyTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait;

    private CertificateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $mockQrService = Mockery::mock(ProductQrService::class);
        $this->service = new CertificateService($mockQrService);
    }

    /**
     * Property 5: HMAC Hash Determinism
     *
     * For any combination of (product_id, tenant_id, sku, certificate_number, issued_at),
     * computeHash() must always return the same value when called multiple times
     * with the same inputs.
     *
     * **Validates: Requirements 2.2, 2.3**
     *
     * // Feature: product-qr-certificate, Property 5: HMAC Hash Determinism
     */
    #[ErisRepeat(repeat: 100)]
    public function test_hmac_hash_determinism(): void
    {
        $this
            ->forAll(
                Generators::choose(1, 999999),                                                          // product_id
                Generators::choose(1, 999999),                                                          // tenant_id
                Generators::elements(['SKU-A', 'SKU-B', 'SKU-C', 'PROD-001', 'ITEM-XYZ']),             // sku
                Generators::elements(['CERT-1-20240101-0001', 'CERT-2-20240202-0002', 'CERT-3-20240303-0003', 'CERT-999-20991231-9999', 'CERT-42-20250615-0007']) // certificate_number
            )
            ->then(function (int $productId, int $tenantId, string $sku, string $certificateNumber) {
                // Create a mock Product with the generated attributes
                $product = Mockery::mock(Product::class)->makePartial();
                $product->setRawAttributes([
                    'id' => $productId,
                    'tenant_id' => $tenantId,
                    'sku' => $sku,
                ]);

                // Use a fixed timestamp within this single call (deterministic)
                $issuedAt = Carbon::now();

                // Compute hash twice with identical inputs
                $hashFirst = $this->service->computeHash($product, $certificateNumber, $issuedAt);
                $hashSecond = $this->service->computeHash($product, $certificateNumber, $issuedAt);

                $this->assertIsString(
                    $hashFirst,
                    'computeHash() must return a string'
                );

                $this->assertSame(
                    $hashFirst,
                    $hashSecond,
                    'computeHash() must be deterministic: same inputs must always produce the same hash. '.
                    "product_id={$productId}, tenant_id={$tenantId}, sku={$sku}, ".
                    "certificate_number={$certificateNumber}, issued_at={$issuedAt->toIso8601String()}"
                );

                Mockery::close();
            });
    }

    /**
     * Property 4: Certificate Number Uniqueness
     *
     * For any number of certificates issued (for different products/tenants),
     * no two certificates should share the same certificate_number across the database.
     *
     * **Validates: Requirements 2.1**
     *
     * // Feature: product-qr-certificate, Property 4: Certificate Number Uniqueness
     */
    #[ErisRepeat(repeat: 100)]
    public function test_certificate_number_uniqueness(): void
    {
        $this
            ->forAll(
                Generators::choose(2, 10) // N: number of certificates to generate
            )
            ->then(function (int $n) {
                // Generate N unique tenant IDs so each gets sequence 1 (no prior DB records)
                $tenantIds = range(1, $n);

                $certificateNumbers = [];
                foreach ($tenantIds as $tenantId) {
                    $certificateNumbers[] = $this->service->generateCertificateNumber($tenantId);
                }

                $uniqueNumbers = array_unique($certificateNumbers);

                $this->assertCount(
                    count($certificateNumbers),
                    $uniqueNumbers,
                    "All {$n} generated certificate numbers must be unique. ".
                    'Got duplicates: '.implode(', ', array_diff_assoc($certificateNumbers, $uniqueNumbers))
                );
            });
    }
}
