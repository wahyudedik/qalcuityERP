<?php

namespace Tests\Feature\BugExploration;

use App\Http\Middleware\RateLimitAiRequests;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Bug 1.28 — AI Rate Limiting Tidak Per-Tenant
 *
 * Membuktikan bahwa RateLimitAiRequests middleware menggunakan
 * key per-user bukan per-tenant, sehingga satu tenant bisa
 * menghabiskan quota tenant lain.
 *
 * EXPECTED: Test ini HARUS GAGAL pada kode unfixed.
 *
 * CATATAN: Berdasarkan kode aktual, RateLimitAiRequests menggunakan
 * key "ai:user:{user_id}" bukan "ai:tenant:{tenant_id}".
 * Bug: rate limit per-user, bukan per-tenant.
 */
class PerformanceAiRateLimitTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;

    private User $user1;

    private User $user2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant(['plan' => 'starter']);

        // Dua user dari tenant yang sama
        $this->user1 = $this->createAdminUser($this->tenant);

        $this->user2 = $this->createAdminUser($this->tenant);
    }

    /**
     * @test
     * Bug 1.28: Rate limit harus per-tenant, bukan per-user
     *
     * Jika user1 sudah mencapai limit, user2 dari tenant yang sama
     * seharusnya juga terkena limit (karena per-tenant).
     *
     * AKAN GAGAL karena rate limit per-user, bukan per-tenant
     *
     * Validates: Requirements 1.28
     */
    public function test_ai_rate_limit_is_per_tenant_not_per_user(): void
    {
        $middleware = new RateLimitAiRequests;

        // Verifikasi bahwa resolveKey menggunakan tenant_id, bukan user_id
        $reflector = new \ReflectionClass($middleware);
        $method = $reflector->getMethod('resolveKey');
        $method->setAccessible(true);

        // Buat mock request untuk user1
        $request1 = Request::create('/chat', 'POST');
        $request1->setUserResolver(fn () => $this->user1);

        // Buat mock request untuk user2 (tenant yang sama)
        $request2 = Request::create('/chat', 'POST');
        $request2->setUserResolver(fn () => $this->user2);

        $key1 = $method->invoke($middleware, $request1);
        $key2 = $method->invoke($middleware, $request2);

        // Assert: Key untuk user1 dan user2 dari tenant yang sama harus SAMA
        // (karena rate limit per-tenant)
        // Test ini AKAN GAGAL karena key menggunakan user_id (berbeda untuk user1 dan user2)
        $this->assertEquals(
            $key1,
            $key2,
            "Bug 1.28: Rate limit key berbeda untuk user1 ({$key1}) dan user2 ({$key2}) ".
            'dari tenant yang sama. Rate limit seharusnya per-tenant, bukan per-user. '.
            'Dengan rate limit per-user, satu tenant bisa menghabiskan quota dengan '.
            'membuat banyak user dan mengirim request dari masing-masing user.'
        );
    }

    /**
     * @test
     * Bug 1.28: Rate limit key harus menggunakan tenant_id
     *
     * AKAN GAGAL karena key menggunakan user_id
     */
    public function test_rate_limit_key_uses_tenant_id(): void
    {
        $middlewareFile = 'app/Http/Middleware/RateLimitAiRequests.php';

        if (! file_exists($middlewareFile)) {
            $this->markTestSkipped('RateLimitAiRequests tidak ditemukan');
        }

        $content = file_get_contents($middlewareFile);

        // Cari resolveKey method
        $methodPattern = '/protected function resolveKey[^{]*\{[^}]*\}/s';
        preg_match($methodPattern, $content, $matches);
        $methodCode = $matches[0] ?? '';

        // Assert: Key harus menggunakan tenant_id
        $usesTenantId = str_contains($methodCode, 'tenant_id') ||
            str_contains($methodCode, 'tenant->id');

        // Test ini AKAN GAGAL karena key menggunakan user_id
        $this->assertTrue(
            $usesTenantId,
            'Bug 1.28: resolveKey() menggunakan user_id bukan tenant_id untuk rate limit key. '.
            'Kode yang ditemukan: '.substr($methodCode, 0, 300)."\n".
            'Seharusnya: return "ai:tenant:{tenant_id}" bukan "ai:user:{user_id}"'
        );
    }

    /**
     * @test
     * Bug 1.28: Request ke-61 dalam 1 menit harus mendapat HTTP 429
     *
     * Dengan plan starter (limit 30 req/menit), request ke-31 harus 429.
     * AKAN GAGAL jika rate limit tidak berfungsi per-tenant
     */
    public function test_61st_ai_request_gets_429_response(): void
    {
        // Clear rate limiter
        RateLimiter::clear("ai:tenant:{$this->tenant->id}");
        RateLimiter::clear("ai:user:{$this->user1->id}");

        // Setup route untuk test
        Route::middleware(['auth', 'ai.rate'])->post('/test-ai-chat', function () {
            return response()->json(['ok' => true]);
        });

        $this->actingAs($this->user1);

        // Kirim 30 request (limit untuk plan starter)
        $limit = 30; // Plan starter: 30 req/menit untuk chat
        for ($i = 0; $i < $limit; $i++) {
            $response = $this->postJson('/test-ai-chat', ['message' => "Request {$i}"]);
            // Beberapa request mungkin 404 karena route test, skip
        }

        // Request ke-31 seharusnya mendapat 429
        $response = $this->postJson('/test-ai-chat', ['message' => 'Request over limit']);

        // Test ini AKAN GAGAL jika rate limit tidak berfungsi per-tenant
        // atau jika limit tidak tercapai
        $this->assertEquals(
            429,
            $response->getStatusCode(),
            'Bug 1.28: Request ke-'.($limit + 1).' tidak mendapat HTTP 429. '.
            'Rate limit per-tenant tidak berfungsi dengan benar. '.
            'Status code yang diterima: '.$response->getStatusCode()
        );
    }
}
