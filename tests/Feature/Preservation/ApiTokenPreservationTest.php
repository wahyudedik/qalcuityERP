<?php

namespace Tests\Feature\Preservation;

use App\Models\ApiToken;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Preservation Test — API Token Validation and Rate Limiting
 *
 * Memverifikasi bahwa validasi API token yang SUDAH BENAR tidak berubah
 * setelah fix diterapkan. Test ini harus LULUS pada kode unfixed (baseline).
 *
 * Validates: Requirements 3.11
 */
class ApiTokenPreservationTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;
    private User $user;
    private string $testRoute = '/api/test-token-auth';

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant();
        $this->user   = $this->createAdminUser($this->tenant);

        // Daftarkan route test yang menggunakan ApiTokenAuth middleware
        Route::middleware(\App\Http\Middleware\ApiTokenAuth::class . ':read')
            ->get($this->testRoute, fn() => response()->json(['ok' => true]));
    }

    // ── Requirement 3.11: Valid token grants access ───────────────────────────

    /**
     * @test
     * Preservation 3.11: Token valid memberikan akses ke endpoint
     *
     * Validates: Requirements 3.11
     */
    public function test_valid_api_token_grants_access(): void
    {
        $token = ApiToken::generate(
            tenantId:  $this->tenant->id,
            name:      'Test Token',
            abilities: ['read'],
        );

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->token,
        ])->get($this->testRoute);

        $response->assertStatus(200);
        $response->assertJson(['ok' => true]);
    }

    /**
     * @test
     * Preservation 3.11: Token valid dengan ability wildcard memberikan akses
     *
     * Validates: Requirements 3.11
     */
    public function test_valid_token_with_wildcard_ability_grants_access(): void
    {
        $token = ApiToken::generate(
            tenantId:  $this->tenant->id,
            name:      'Wildcard Token',
            abilities: ['*'],
        );

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->token,
        ])->get($this->testRoute);

        $response->assertStatus(200);
    }

    // ── Requirement 3.11: Expired token returns 401 ───────────────────────────

    /**
     * @test
     * Preservation 3.11: Token kadaluarsa mengembalikan 401
     *
     * Validates: Requirements 3.11
     */
    public function test_expired_api_token_returns_401(): void
    {
        $token = ApiToken::generate(
            tenantId:  $this->tenant->id,
            name:      'Expired Token',
            abilities: ['read'],
            expiresAt: now()->subDay(), // Sudah kadaluarsa kemarin
        );

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->token,
        ])->get($this->testRoute);

        $response->assertStatus(401);
    }

    // ── Requirement 3.11: Inactive token returns 401 ─────────────────────────

    /**
     * @test
     * Preservation 3.11: Token tidak aktif mengembalikan 401
     *
     * Validates: Requirements 3.11
     */
    public function test_inactive_api_token_returns_401(): void
    {
        $token = ApiToken::generate(
            tenantId:  $this->tenant->id,
            name:      'Inactive Token',
            abilities: ['read'],
        );

        // Nonaktifkan token
        $token->update(['is_active' => false]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->token,
        ])->get($this->testRoute);

        $response->assertStatus(401);
    }

    /**
     * @test
     * Preservation 3.11: Request tanpa token mengembalikan 401
     *
     * Validates: Requirements 3.11
     */
    public function test_request_without_token_returns_401(): void
    {
        $response = $this->get($this->testRoute);

        $response->assertStatus(401);
    }

    // ── Requirement 3.11: Token without required ability returns 403 ──────────

    /**
     * @test
     * Preservation 3.11: Token tanpa ability yang diperlukan mengembalikan 403
     *
     * Validates: Requirements 3.11
     */
    public function test_token_without_required_ability_returns_403(): void
    {
        // Token hanya punya ability 'write', tapi endpoint butuh 'read'
        $token = ApiToken::generate(
            tenantId:  $this->tenant->id,
            name:      'Write-Only Token',
            abilities: ['write'],
        );

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->token,
        ])->get($this->testRoute); // Route butuh 'read'

        $response->assertStatus(403);
    }

    // ── Requirement 3.11: ApiToken model methods ─────────────────────────────

    /**
     * @test
     * Preservation 3.11: ApiToken.isValid() mengembalikan true untuk token aktif dan belum kadaluarsa
     *
     * Validates: Requirements 3.11
     */
    public function test_api_token_is_valid_returns_true_for_active_non_expired_token(): void
    {
        $token = ApiToken::generate(
            tenantId:  $this->tenant->id,
            name:      'Valid Token',
            abilities: ['read'],
        );

        $this->assertTrue($token->isValid(), "Token aktif dan belum kadaluarsa harus valid");
    }

    /**
     * @test
     * Preservation 3.11: ApiToken.isValid() mengembalikan false untuk token kadaluarsa
     *
     * Validates: Requirements 3.11
     */
    public function test_api_token_is_valid_returns_false_for_expired_token(): void
    {
        $token = ApiToken::generate(
            tenantId:  $this->tenant->id,
            name:      'Expired Token',
            abilities: ['read'],
            expiresAt: now()->subHour(),
        );

        $this->assertFalse($token->isValid(), "Token kadaluarsa harus tidak valid");
    }

    /**
     * @test
     * Preservation 3.11: ApiToken.isValid() mengembalikan false untuk token tidak aktif
     *
     * Validates: Requirements 3.11
     */
    public function test_api_token_is_valid_returns_false_for_inactive_token(): void
    {
        $token = ApiToken::generate(
            tenantId:  $this->tenant->id,
            name:      'Inactive Token',
            abilities: ['read'],
        );
        $token->update(['is_active' => false]);
        $token->refresh();

        $this->assertFalse($token->isValid(), "Token tidak aktif harus tidak valid");
    }

    /**
     * @test
     * Preservation 3.11: ApiToken.can() mengembalikan true untuk ability yang dimiliki
     *
     * Validates: Requirements 3.11
     */
    public function test_api_token_can_returns_true_for_owned_ability(): void
    {
        $token = ApiToken::generate(
            tenantId:  $this->tenant->id,
            name:      'Read Token',
            abilities: ['read', 'export'],
        );

        $this->assertTrue($token->can('read'), "Token harus bisa 'read'");
        $this->assertTrue($token->can('export'), "Token harus bisa 'export'");
        $this->assertFalse($token->can('write'), "Token tidak boleh bisa 'write'");
    }

    /**
     * @test
     * Preservation 3.11: ApiToken.can() mengembalikan true untuk wildcard ability
     *
     * Validates: Requirements 3.11
     */
    public function test_api_token_can_returns_true_for_wildcard_ability(): void
    {
        $token = ApiToken::generate(
            tenantId:  $this->tenant->id,
            name:      'Admin Token',
            abilities: ['*'],
        );

        $this->assertTrue($token->can('read'), "Wildcard token harus bisa 'read'");
        $this->assertTrue($token->can('write'), "Wildcard token harus bisa 'write'");
        $this->assertTrue($token->can('delete'), "Wildcard token harus bisa 'delete'");
    }

    /**
     * @test
     * Preservation 3.11: Token default memiliki expiry 90 hari
     *
     * Validates: Requirements 3.11
     */
    public function test_api_token_default_expiry_is_90_days(): void
    {
        $token = ApiToken::generate(
            tenantId:  $this->tenant->id,
            name:      'Default Expiry Token',
            abilities: ['read'],
        );

        $this->assertNotNull($token->expires_at, "Token harus memiliki expiry date");

        $expectedExpiry = now()->addDays(90);
        $this->assertEqualsWithDelta(
            $expectedExpiry->timestamp,
            $token->expires_at->timestamp,
            60, // toleransi 60 detik
            "Token default harus kadaluarsa dalam 90 hari"
        );
    }
}
