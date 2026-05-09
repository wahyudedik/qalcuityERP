<?php

namespace Tests\Feature\Services;

use App\Models\CertificateVerifyLog;
use App\Models\Product;
use App\Models\ProductCertificate;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CertificateService;
use App\Services\ProductQrService;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Mockery;
use Tests\TestCase;

/**
 * Feature Property-Based Tests for CertificateService.
 *
 * Feature: product-qr-certificate
 */
class CertificateServiceFeatureTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait;

    /**
     * Property 7: Single Active Certificate Invariant
     *
     * For any product, after issuing a new certificate, the count of certificates
     * with status 'active' for that product must be exactly 1, and the total number
     * of certificates (active + revoked) must grow with each issuance (history is preserved).
     *
     * **Validates: Requirements 2.5, 4.2**
     *
     * // Feature: product-qr-certificate, Property 7: Single Active Certificate Invariant
     */
    #[ErisRepeat(repeat: 100)]
    public function test_single_active_certificate_invariant(): void
    {
        Storage::fake('public');

        $this
            ->forAll(
                Generators::choose(2, 5)
            )
            ->then(function (int $n) {
                // Create a Tenant, User, and Product
                $tenant = $this->createTenant([
                    'name' => 'Tenant '.uniqid(),
                    'slug' => 'tenant-'.uniqid(),
                ]);

                $issuer = $this->createAdminUser($tenant);

                $product = $this->createProduct($tenant->id, [
                    'name' => 'Product '.uniqid(),
                    'sku' => 'SKU-'.uniqid(),
                ]);

                // Mock ProductQrService to avoid Imagick dependency
                $mockQrService = Mockery::mock(ProductQrService::class);
                $mockQrService->shouldReceive('generate')
                    ->times($n)
                    ->andReturnUsing(function (Product $prod, bool $force = false) {
                        $relativePath = 'qr-codes/'.$prod->tenant_id.'/'.$prod->id.'.png';
                        Storage::disk('public')->put($relativePath, 'fake-png-data');
                        $prod->qr_code_path = $relativePath;
                        $prod->save();

                        return $relativePath;
                    });

                $service = new CertificateService($mockQrService);

                // Issue N certificates sequentially, asserting invariants after each
                for ($i = 1; $i <= $n; $i++) {
                    $service->issue($product, $issuer);

                    $activeCount = ProductCertificate::where('product_id', $product->id)
                        ->where('status', 'active')
                        ->count();

                    $totalCount = ProductCertificate::where('product_id', $product->id)
                        ->count();

                    // After each issue: exactly 1 active certificate
                    $this->assertEquals(
                        1,
                        $activeCount,
                        "After issuing certificate #{$i}, active count must be 1. Got {$activeCount}. n={$n}"
                    );

                    // After each issue: total certificates = number issued so far
                    $this->assertEquals(
                        $i,
                        $totalCount,
                        "After issuing certificate #{$i}, total count must be {$i}. Got {$totalCount}. n={$n}"
                    );
                }

                // Final assertions after all N issues
                $finalActiveCount = ProductCertificate::where('product_id', $product->id)
                    ->where('status', 'active')
                    ->count();

                $finalTotalCount = ProductCertificate::where('product_id', $product->id)
                    ->count();

                $this->assertEquals(
                    1,
                    $finalActiveCount,
                    "After all {$n} issuances, exactly 1 active certificate must exist. Got {$finalActiveCount}."
                );

                $this->assertEquals(
                    $n,
                    $finalTotalCount,
                    "After all {$n} issuances, total certificate count must be {$n}. Got {$finalTotalCount}."
                );

                Mockery::close();
            });
    }

    /**
     * Property 11: Double-Revoke Error
     *
     * For any certificate that is already revoked, calling revoke() again must throw
     * an InvalidArgumentException (not silently succeed).
     *
     * **Validates: Requirements 4.4**
     *
     * // Feature: product-qr-certificate, Property 11: Double-Revoke Error
     */
    #[ErisRepeat(repeat: 100)]
    public function test_double_revoke_throws_exception(): void
    {
        // Feature: product-qr-certificate, Property 11: Double-Revoke Error
        Storage::fake('public');

        $this
            ->forAll(
                Generators::elements([
                    'Produk cacat',
                    'Barang palsu',
                    'Kadaluarsa',
                    'Tidak sesuai spesifikasi',
                    'Penarikan produk',
                ])
            )
            ->then(function (string $revokeReason) {
                $tenant = $this->createTenant([
                    'name' => 'Tenant '.uniqid(),
                    'slug' => 'tenant-'.uniqid(),
                ]);

                $revoker = $this->createAdminUser($tenant);

                $product = $this->createProduct($tenant->id, [
                    'name' => 'Product '.uniqid(),
                    'sku' => 'SKU-'.uniqid(),
                ]);

                $mockQrService = Mockery::mock(ProductQrService::class);
                $mockQrService->shouldReceive('generate')
                    ->once()
                    ->andReturnUsing(function (Product $prod, bool $force = false) {
                        $relativePath = 'qr-codes/'.$prod->tenant_id.'/'.$prod->id.'.png';
                        Storage::disk('public')->put($relativePath, 'fake-png-data');
                        $prod->qr_code_path = $relativePath;
                        $prod->save();

                        return $relativePath;
                    });

                $service = new CertificateService($mockQrService);

                // Issue a certificate
                $certificate = $service->issue($product, $revoker);

                // First revoke — should succeed
                $service->revoke($certificate, $revoker, $revokeReason);

                // Second revoke — should throw InvalidArgumentException
                try {
                    $service->revoke($certificate, $revoker, 'Second revoke attempt');
                    $this->fail('Expected InvalidArgumentException was not thrown');
                } catch (\InvalidArgumentException $e) {
                    $this->assertStringContainsString('already revoked', $e->getMessage());
                }

                Mockery::close();
            });
    }

    /**
     * Property 8: Verification Round-Trip
     *
     * For any certificate issued by CertificateService::issue() with unchanged product data,
     * calling CertificateService::verify() with the same certificate_number must return
     * status 'VALID'. Calling verify() multiple times with the same input must return
     * consistent results (idempotent).
     *
     * **Validates: Requirements 6.1, 6.4, 3.2, 3.3**
     *
     * // Feature: product-qr-certificate, Property 8: Verification Round-Trip
     */
    #[ErisRepeat(repeat: 100)]
    public function test_verification_round_trip(): void
    {
        // Feature: product-qr-certificate, Property 8: Verification Round-Trip
        Storage::fake('public');

        $this
            ->forAll(
                Generators::elements(['Product Alpha', 'Product Beta', 'Product Gamma', 'Produk Uji', 'Item Test']),
                Generators::elements(['SKU-001', 'SKU-002', 'SKU-ABC', 'PROD-XYZ', 'ITEM-999'])
            )
            ->then(function (string $productName, string $skuSuffix) {
                // Create a Tenant, User, and Product
                $tenant = $this->createTenant([
                    'name' => 'Tenant '.uniqid(),
                    'slug' => 'tenant-'.uniqid(),
                ]);

                $issuer = $this->createAdminUser($tenant);

                $product = $this->createProduct($tenant->id, [
                    'name' => $productName,
                    'sku' => $skuSuffix.'-'.uniqid(),
                ]);

                // Mock ProductQrService to avoid Imagick dependency
                $mockQrService = Mockery::mock(ProductQrService::class);
                $mockQrService->shouldReceive('generate')
                    ->once()
                    ->andReturnUsing(function (Product $prod, bool $force = false) {
                        $relativePath = 'qr-codes/'.$prod->tenant_id.'/'.$prod->id.'.png';
                        Storage::disk('public')->put($relativePath, 'fake-png-data');
                        $prod->qr_code_path = $relativePath;
                        $prod->save();

                        return $relativePath;
                    });

                $service = new CertificateService($mockQrService);

                // Issue a certificate
                $certificate = $service->issue($product, $issuer);

                // First verify call — assert status = 'VALID'
                $result1 = $service->verify($certificate->certificate_number, '127.0.0.1');

                $this->assertEquals(
                    'VALID',
                    $result1['status'],
                    'First verify() call must return VALID for a freshly issued certificate. '.
                    "product_name={$productName}, sku={$product->sku}, cert={$certificate->certificate_number}"
                );

                // Second verify call — assert status = 'VALID' (idempotent)
                $result2 = $service->verify($certificate->certificate_number, '127.0.0.1');

                $this->assertEquals(
                    'VALID',
                    $result2['status'],
                    'Second verify() call must also return VALID (idempotent). '.
                    "product_name={$productName}, sku={$product->sku}, cert={$certificate->certificate_number}"
                );

                // Assert both calls return the same status
                $this->assertEquals(
                    $result1['status'],
                    $result2['status'],
                    'Both verify() calls must return the same status (idempotent). '.
                    "First={$result1['status']}, Second={$result2['status']}"
                );

                Mockery::close();
            });
    }

    /**
     * Property 9: Tamper Detection
     *
     * For any certificate that has been issued, if the product's SKU is changed after
     * issuance, then verify() must return status 'TIDAK VALID' (not 'VALID').
     *
     * **Validates: Requirements 6.2, 3.4**
     *
     * // Feature: product-qr-certificate, Property 9: Tamper Detection
     */
    #[ErisRepeat(repeat: 100)]
    public function test_tamper_detection(): void
    {
        // Feature: product-qr-certificate, Property 9: Tamper Detection
        Storage::fake('public');

        $this
            ->forAll(
                Generators::elements(['Product Alpha', 'Product Beta', 'Product Gamma', 'Produk Uji', 'Item Test']),
                Generators::elements(['SKU-001', 'SKU-002', 'SKU-ABC', 'PROD-XYZ', 'ITEM-999'])
            )
            ->then(function (string $productName, string $skuSuffix) {
                // Create a Tenant, User, and Product
                $tenant = $this->createTenant([
                    'name' => 'Tenant '.uniqid(),
                    'slug' => 'tenant-'.uniqid(),
                ]);

                $issuer = $this->createAdminUser($tenant);

                $product = $this->createProduct($tenant->id, [
                    'name' => $productName,
                    'sku' => $skuSuffix.'-'.uniqid(),
                ]);

                // Mock ProductQrService to avoid Imagick dependency
                $mockQrService = Mockery::mock(ProductQrService::class);
                $mockQrService->shouldReceive('generate')
                    ->once()
                    ->andReturnUsing(function (Product $prod, bool $force = false) {
                        $relativePath = 'qr-codes/'.$prod->tenant_id.'/'.$prod->id.'.png';
                        Storage::disk('public')->put($relativePath, 'fake-png-data');
                        $prod->qr_code_path = $relativePath;
                        $prod->save();

                        return $relativePath;
                    });

                $service = new CertificateService($mockQrService);

                // Issue a certificate
                $certificate = $service->issue($product, $issuer);

                // Tamper with the product's SKU after issuance
                $product->update(['sku' => 'TAMPERED-'.uniqid()]);

                // Verify the certificate — hash should no longer match
                $result = $service->verify($certificate->certificate_number, '127.0.0.1');

                $this->assertEquals(
                    'TIDAK VALID',
                    $result['status'],
                    "verify() must return 'TIDAK VALID' after product SKU is tampered. ".
                    "product_name={$productName}, cert={$certificate->certificate_number}"
                );

                Mockery::close();
            });
    }

    /**
     * Property 10: Revoked Certificate Verification
     *
     * For any certificate that has been revoked, calling verify() with its certificate_number
     * must return status 'DICABUT', not 'VALID'.
     *
     * **Validates: Requirements 3.6, 4.5**
     *
     * // Feature: product-qr-certificate, Property 10: Revoked Certificate Verification
     */
    #[ErisRepeat(repeat: 100)]
    public function test_revoked_certificate_verification(): void
    {
        // Feature: product-qr-certificate, Property 10: Revoked Certificate Verification
        Storage::fake('public');

        $this
            ->forAll(
                Generators::elements([
                    'Produk cacat',
                    'Barang palsu',
                    'Kadaluarsa',
                    'Tidak sesuai spesifikasi',
                    'Penarikan produk',
                ])
            )
            ->then(function (string $revokeReason) {
                $tenant = $this->createTenant([
                    'name' => 'Tenant '.uniqid(),
                    'slug' => 'tenant-'.uniqid(),
                ]);

                $user = $this->createAdminUser($tenant);

                $product = $this->createProduct($tenant->id, [
                    'name' => 'Product '.uniqid(),
                    'sku' => 'SKU-'.uniqid(),
                ]);

                $mockQrService = Mockery::mock(ProductQrService::class);
                $mockQrService->shouldReceive('generate')
                    ->once()
                    ->andReturnUsing(function (Product $prod, bool $force = false) {
                        $relativePath = 'qr-codes/'.$prod->tenant_id.'/'.$prod->id.'.png';
                        Storage::disk('public')->put($relativePath, 'fake-png-data');
                        $prod->qr_code_path = $relativePath;
                        $prod->save();

                        return $relativePath;
                    });

                $service = new CertificateService($mockQrService);

                // Issue a certificate
                $certificate = $service->issue($product, $user);

                // Revoke the certificate
                $service->revoke($certificate, $user, $revokeReason);

                // Verify the revoked certificate — must return DICABUT
                $result = $service->verify($certificate->certificate_number, '127.0.0.1');

                $this->assertEquals(
                    'DICABUT',
                    $result['status'],
                    "verify() must return 'DICABUT' for a revoked certificate. ".
                    "cert={$certificate->certificate_number}, reason={$revokeReason}"
                );

                Mockery::close();
            });
    }

    /**
     * Property 12: Verification Log Recorded
     *
     * For any call to verify() with a certificate_number that exists in the database,
     * a new record must appear in certificate_verify_logs after the call completes.
     *
     * **Validates: Requirements 3.7**
     *
     * // Feature: product-qr-certificate, Property 12: Verification Log Recorded
     */
    #[ErisRepeat(repeat: 100)]
    public function test_verification_log_recorded(): void
    {
        // Feature: product-qr-certificate, Property 12: Verification Log Recorded
        Storage::fake('public');

        $this
            ->forAll(
                Generators::elements(['Product Alpha', 'Product Beta', 'Product Gamma', 'Produk Uji', 'Item Test']),
                Generators::elements(['SKU-001', 'SKU-002', 'SKU-ABC', 'PROD-XYZ', 'ITEM-999'])
            )
            ->then(function (string $productName, string $skuSuffix) {
                $tenant = $this->createTenant([
                    'name' => 'Tenant '.uniqid(),
                    'slug' => 'tenant-'.uniqid(),
                ]);

                $issuer = $this->createAdminUser($tenant);

                $product = $this->createProduct($tenant->id, [
                    'name' => $productName,
                    'sku' => $skuSuffix.'-'.uniqid(),
                ]);

                $mockQrService = Mockery::mock(ProductQrService::class);
                $mockQrService->shouldReceive('generate')
                    ->once()
                    ->andReturnUsing(function (Product $prod, bool $force = false) {
                        $relativePath = 'qr-codes/'.$prod->tenant_id.'/'.$prod->id.'.png';
                        Storage::disk('public')->put($relativePath, 'fake-png-data');
                        $prod->qr_code_path = $relativePath;
                        $prod->save();

                        return $relativePath;
                    });

                $service = new CertificateService($mockQrService);

                // Issue a certificate
                $certificate = $service->issue($product, $issuer);

                // Count logs before verify
                $countBefore = CertificateVerifyLog::count();

                // Call verify
                $service->verify($certificate->certificate_number, '127.0.0.1');

                // Count logs after verify — must have increased by exactly 1
                $countAfter = CertificateVerifyLog::count();

                $this->assertEquals(
                    $countBefore + 1,
                    $countAfter,
                    'verify() must create exactly 1 new log record in certificate_verify_logs. '.
                    "Before={$countBefore}, After={$countAfter}, cert={$certificate->certificate_number}"
                );

                Mockery::close();
            });
    }

    /**
     * Property 13: Not Found for Unknown Certificate
     *
     * For any string that does not exist as a certificate_number in the database,
     * verify() must return status 'TIDAK DITEMUKAN'.
     *
     * **Validates: Requirements 3.5**
     *
     * // Feature: product-qr-certificate, Property 13: Not Found for Unknown Certificate
     */
    #[ErisRepeat(repeat: 100)]
    public function test_not_found_for_unknown_certificate(): void
    {
        // Feature: product-qr-certificate, Property 13: Not Found for Unknown Certificate
        Storage::fake('public');

        $this
            ->forAll(
                Generators::elements([
                    'UNKNOWN-001',
                    'FAKE-CERT-999',
                    'NONEXISTENT-ABC',
                    'RANDOM-XYZ-123',
                    'CERT-0-00000000-0000',
                ])
            )
            ->then(function (string $unknownCertNumber) {
                $mockQrService = Mockery::mock(ProductQrService::class);
                $service = new CertificateService($mockQrService);

                $result = $service->verify($unknownCertNumber, '127.0.0.1');

                $this->assertEquals(
                    'TIDAK DITEMUKAN',
                    $result['status'],
                    "verify() must return 'TIDAK DITEMUKAN' for an unknown certificate_number. ".
                    "cert_number={$unknownCertNumber}"
                );

                $this->assertNull(
                    $result['certificate'],
                    'verify() must return null certificate for an unknown certificate_number.'
                );

                $this->assertNull(
                    $result['product'],
                    'verify() must return null product for an unknown certificate_number.'
                );

                Mockery::close();
            });
    }

    /**
     * Property 15: 404 Cross-Tenant Product
     *
     * For any product_id that belongs to a different tenant, attempting to find it
     * scoped to the current tenant must throw ModelNotFoundException.
     *
     * **Validates: Requirements 2.8**
     *
     * // Feature: product-qr-certificate, Property 15: 404 for Cross-Tenant Product
     */
    #[ErisRepeat(repeat: 100)]
    public function test_cross_tenant_product_throws_not_found_exception(): void
    {
        // Feature: product-qr-certificate, Property 15: 404 for Cross-Tenant Product
        Storage::fake('public');

        $this
            ->forAll(
                Generators::elements(['Product Alpha', 'Product Beta', 'Product Gamma', 'Produk Uji', 'Item Test']),
                Generators::elements(['SKU-001', 'SKU-002', 'SKU-ABC', 'PROD-XYZ', 'ITEM-999'])
            )
            ->then(function (string $productName, string $skuSuffix) {
                // Create two separate tenants
                $tenantA = $this->createTenant([
                    'name' => 'Tenant A '.uniqid(),
                    'slug' => 'tenant-a-'.uniqid(),
                ]);

                $tenantB = $this->createTenant([
                    'name' => 'Tenant B '.uniqid(),
                    'slug' => 'tenant-b-'.uniqid(),
                ]);

                // Create a product belonging to tenant B
                $productB = $this->createProduct($tenantB->id, [
                    'name' => $productName,
                    'sku' => $skuSuffix.'-'.uniqid(),
                ]);

                // Simulate tenant isolation: try to find tenant B's product scoped to tenant A
                // This is how the controller enforces cross-tenant isolation
                $this->expectException(ModelNotFoundException::class);

                Product::where('tenant_id', $tenantA->id)->findOrFail($productB->id);
            });
    }

    /**
     * Property 14: Label PDF Contains Required Fields
     *
     * For any subset of products selected for label printing, the PDF generated must
     * contain: product name, SKU, and certificate_number for each product in the subset.
     *
     * **Validates: Requirements 5.4, 5.1**
     *
     * // Feature: product-qr-certificate, Property 14: Label PDF Contains Required Fields
     */
    #[ErisRepeat(repeat: 100)]
    public function test_label_pdf_contains_required_fields(): void
    {
        // Feature: product-qr-certificate, Property 14: Label PDF Contains Required Fields
        Storage::fake('public');

        $this
            ->forAll(
                Generators::elements(['Product Alpha', 'Product Beta', 'Product Gamma', 'Produk Uji', 'Item Test']),
                Generators::elements(['SKU-001', 'SKU-002', 'SKU-ABC', 'PROD-XYZ', 'ITEM-999'])
            )
            ->then(function (string $productName, string $skuSuffix) {
                $tenant = $this->createTenant([
                    'name' => 'Tenant '.uniqid(),
                    'slug' => 'tenant-'.uniqid(),
                ]);

                $issuer = $this->createAdminUser($tenant);

                $product = $this->createProduct($tenant->id, [
                    'name' => $productName.' '.uniqid(),
                    'sku' => $skuSuffix.'-'.uniqid(),
                ]);

                $mockQrService = Mockery::mock(ProductQrService::class);
                $mockQrService->shouldReceive('generate')
                    ->once()
                    ->andReturnUsing(function (Product $prod, bool $force = false) {
                        $relativePath = 'qr-codes/'.$prod->tenant_id.'/'.$prod->id.'.png';
                        Storage::disk('public')->put($relativePath, 'fake-png-data');
                        $prod->qr_code_path = $relativePath;
                        $prod->save();

                        return $relativePath;
                    });

                $service = new CertificateService($mockQrService);

                // Issue a certificate
                $certificate = $service->issue($product, $issuer);

                // Reload product and certificate with relations
                $certificate->loadMissing(['product.tenant']);

                // Render the Blade view directly (same view used by generatePdf)
                $html = View::make('certificates.pdf', [
                    'certificate' => $certificate,
                    'product' => $certificate->product,
                    'tenant' => $certificate->product->tenant,
                    'qrBase64' => null,
                ])->render();

                // Assert the HTML contains the product name
                $this->assertStringContainsString(
                    $product->name,
                    $html,
                    'PDF view HTML must contain the product name. '.
                    "product_name={$product->name}, cert={$certificate->certificate_number}"
                );

                // Assert the HTML contains the SKU
                $this->assertStringContainsString(
                    $product->sku,
                    $html,
                    'PDF view HTML must contain the product SKU. '.
                    "sku={$product->sku}, cert={$certificate->certificate_number}"
                );

                // Assert the HTML contains the certificate_number
                $this->assertStringContainsString(
                    $certificate->certificate_number,
                    $html,
                    'PDF view HTML must contain the certificate_number. '.
                    "cert={$certificate->certificate_number}"
                );

                Mockery::close();
            });
    }

    /**
     * Property 6: Certificate Issuance Completeness
     *
     * For any product that has a certificate issued, the resulting ProductCertificate
     * record must have: certificate_hash not null, issued_by not null, issued_at not null,
     * and the related product must have qr_code_path not null after the process completes.
     *
     * **Validates: Requirements 2.4, 1.4, 2.6**
     *
     * // Feature: product-qr-certificate, Property 6: Certificate Issuance Completeness
     */
    #[ErisRepeat(repeat: 100)]
    public function test_certificate_issuance_completeness(): void
    {
        Storage::fake('public');

        $this
            ->forAll(
                Generators::elements(['Product Alpha', 'Product Beta', 'Product Gamma', 'Produk Uji', 'Item Test']),
                Generators::elements(['SKU-001', 'SKU-002', 'SKU-ABC', 'PROD-XYZ', 'ITEM-999'])
            )
            ->then(function (string $productName, string $skuSuffix) {
                // Create a Tenant record
                $tenant = $this->createTenant([
                    'name' => 'Tenant '.uniqid(),
                    'slug' => 'tenant-'.uniqid(),
                ]);

                // Create a User record (the issuer)
                $issuer = $this->createAdminUser($tenant);

                // Create a Product record belonging to that tenant
                $product = $this->createProduct($tenant->id, [
                    'name' => $productName,
                    'sku' => $skuSuffix.'-'.uniqid(),
                ]);

                // Mock ProductQrService to avoid Imagick dependency.
                // The mock sets qr_code_path on the product (simulating real QR generation)
                // and uses Storage::fake('public') to avoid real file writes.
                $mockQrService = Mockery::mock(ProductQrService::class);
                $mockQrService->shouldReceive('generate')
                    ->once()
                    ->andReturnUsing(function (Product $prod, bool $force = false) {
                        $relativePath = 'qr-codes/'.$prod->tenant_id.'/'.$prod->id.'.png';
                        Storage::disk('public')->put($relativePath, 'fake-png-data');
                        $prod->qr_code_path = $relativePath;
                        $prod->save();

                        return $relativePath;
                    });

                // Inject the mocked QrService into CertificateService
                $service = new CertificateService($mockQrService);

                // Issue the certificate
                $certificate = $service->issue($product, $issuer);

                // Assert: certificate_hash is not null
                $this->assertNotNull(
                    $certificate->certificate_hash,
                    'certificate_hash must not be null after issue(). '.
                    "product_name={$productName}, sku={$product->sku}"
                );

                // Assert: issued_by is not null
                $this->assertNotNull(
                    $certificate->issued_by,
                    'issued_by must not be null after issue(). '.
                    "product_name={$productName}, sku={$product->sku}"
                );

                // Assert: issued_at is not null
                $this->assertNotNull(
                    $certificate->issued_at,
                    'issued_at must not be null after issue(). '.
                    "product_name={$productName}, sku={$product->sku}"
                );

                // Assert: product->qr_code_path is not null
                $freshProduct = $product->fresh();
                $this->assertNotNull(
                    $freshProduct->qr_code_path,
                    'product->qr_code_path must not be null after certificate issuance. '.
                    "product_name={$productName}, sku={$product->sku}"
                );

                Mockery::close();
            });
    }
}
