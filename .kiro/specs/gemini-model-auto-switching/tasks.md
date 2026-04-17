# Implementation Plan: Gemini AI Model Auto-Switching

## Overview

Implementasi fitur ini dilakukan secara incremental: mulai dari fondasi data (migration, model), lalu core service (`ModelSwitcher`), kemudian integrasi ke `GeminiService`, diikuti async logging, alerting, command, dan UI monitoring. Setiap tahap di-wire ke tahap sebelumnya sehingga tidak ada kode yang tergantung (orphaned).

## Tasks

- [x] 1. Database migration dan model AiModelSwitchLog
  - [x] 1.1 Buat migration `create_ai_model_switch_logs_table`
    - Kolom: `id`, `from_model`, `to_model`, `reason` (enum), `error_message`, `request_context`, `triggered_by_tenant_id`, `switched_at`, `created_at`, `updated_at`
    - Index pada `switched_at`, `reason`, `triggered_by_tenant_id`
    - _Requirements: 5.1, 5.2_
  - [x] 1.2 Buat `App\Models\AiModelSwitchLog`
    - `$fillable`, cast `switched_at` ke Carbon, scope `recent($days)` dan `byReason($reason)`
    - _Requirements: 5.1, 5.4, 5.5_
  - [x] 1.3 Tulis unit test untuk scope `AiModelSwitchLog::recent()` dan `byReason()`
    - Pastikan scope memfilter berdasarkan tanggal dan reason dengan benar
    - _Requirements: 5.1_

- [x] 2. Exception, Event, dan config
  - [x] 2.1 Buat `App\Exceptions\AllModelsUnavailableException`
    - Constructor set message Bahasa Indonesia dan code 503
    - _Requirements: 2.5, 6.4_
  - [x] 2.2 Buat `App\Events\AllModelsUnavailable`
    - Property: `$unavailableModels` (array), `$triggeredByTenantId` (nullable int)
    - _Requirements: 10.1_
  - [x] 2.3 Update `config/gemini.php`
    - Tambah keys: `fallback_models` (array), `rate_limit_cooldown` (60), `quota_cooldown` (3600), `recovery_check_interval` (300)
    - _Requirements: 3.1, 3.2, 4.5, 8.1_

- [x] 3. Buat `App\Services\AI\ModelSwitcher`
  - [x] 3.1 Implementasi constructor dan konstanta cache key
    - Inject `Illuminate\Contracts\Cache\Repository`, define konstanta `CACHE_PREFIX`, `ACTIVE_MODEL_KEY`, `UNAVAILABLE_PREFIX`, `SWITCH_COUNT_KEY`
    - In-memory fallback array `$inMemoryState` untuk saat cache down
    - _Requirements: 1.1, 1.4_
  - [x] 3.2 Implementasi `getFallbackChain(): array`
    - Baca dari `SystemSetting::get('gemini_fallback_models')`, fallback ke `config('gemini.fallback_models')`
    - _Requirements: 8.1, 8.3_
  - [x] 3.3 Implementasi `getActiveModel(): string`
    - Baca dari cache; jika primary model sudah lewat cooldown, kembalikan primary untuk dicoba kembali
    - Default ke `config('gemini.model')` jika belum pernah di-set
    - _Requirements: 1.1, 1.2, 1.5, 4.1_
  - [x] 3.4 Implementasi `markUnavailable(string $model, string $reason): void`
    - Simpan `{reason, marked_at, expires_at}` ke cache dengan TTL sesuai reason (rate_limit=60s, quota_exceeded=3600s)
    - Fallback ke `$inMemoryState` jika cache throw exception
    - Increment switch count key (TTL 3600s), log warning jika >= 10 dalam 1 jam
    - _Requirements: 3.1, 3.2, 3.3, 10.3_
  - [x] 3.5 Implementasi `nextAvailableModel(string $failedModel): string`
    - Iterasi fallback chain, skip model yang ada di unavailable cache
    - Throw `AllModelsUnavailableException` + dispatch `AllModelsUnavailable` event jika semua exhausted
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.7_
  - [x] 3.6 Implementasi `setActiveModel(string $model): void`
    - Simpan model ke `ACTIVE_MODEL_KEY` tanpa TTL (persisten)
    - _Requirements: 1.2, 1.3_
  - [x] 3.7 Implementasi `getModelAvailability(): array`
    - Return array `[['model', 'available', 'reason', 'recovers_at'], ...]` untuk semua model di chain
    - _Requirements: 3.5, 3.6, 7.2, 7.5_
  - [x] 3.8 Implementasi `resetAll(): void`
    - Hapus semua cache key terkait unavailability dan active model
    - _Requirements: 7.6_

- [x] 4. Property-based tests untuk ModelSwitcher
  - [x] 4.1 Tulis property test P1: Active model persistence
    - **Property 1: Active model persistence**
    - Generate random model name, set active, assert `getActiveModel()` mengembalikan model itu
    - **Validates: Requirements 1.1, 1.2**
  - [x] 4.2 Tulis property test P2: Fallback chain skips unavailable
    - **Property 2: Fallback chain respects order and skips cooldowns**
    - Generate random subset unavailable models, assert `nextAvailableModel()` selalu return model available pertama sesuai urutan chain
    - **Validates: Requirements 2.1, 2.2, 2.3, 2.4, 2.5, 2.7**
  - [x] 4.3 Tulis property test P3: Cooldown duration invariant
    - **Property 3: Cooldown duration invariant**
    - Mock waktu (Carbon::setTestNow), mark unavailable, assert model unavailable selama TTL, available setelah TTL
    - **Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5**
  - [x] 4.4 Tulis property test P4: Recovery round-trip ke primary
    - **Property 4: Recovery round-trip to primary model**
    - Mark primary unavailable, advance time melewati cooldown, assert `getActiveModel()` kembali primary
    - **Validates: Requirements 4.1, 4.2**
  - [x] 4.5 Tulis property test P8: Switch count threshold warning
    - **Property 8: Switch frequency warning threshold**
    - Generate N > 10 switch events dalam 1 jam, assert Laravel warning di-log
    - **Validates: Requirements 10.3**
  - [x] 4.6 Tulis property test P9: SystemSetting chain precedence dan cache invalidation
    - **Property 9: SystemSetting precedence and cache invalidation**
    - Set `gemini_fallback_models` via SystemSetting, assert `getFallbackChain()` mengembalikan nilai baru dan cache stale di-invalidate
    - **Validates: Requirements 8.1, 8.3, 8.5**

- [x] 5. Checkpoint — pastikan semua tests di task 1–4 pass
  - Jalankan `php artisan test --filter=AiModelSwitchLog` dan semua test ModelSwitcher
  - Pastikan tidak ada error; tanyakan kepada user jika ada pertanyaan.

- [x] 6. Extend `GeminiService` dengan ModelSwitcher
  - [x] 6.1 Inject `ModelSwitcher` ke constructor `GeminiService`
    - Tambah parameter `ModelSwitcher $switcher` di constructor; bind di service provider jika diperlukan
    - _Requirements: 1.5_
  - [x] 6.2 Implementasi `classifyError(\Throwable $e): ?string`
    - Return `'rate_limit'` untuk 429, `'quota_exceeded'` untuk pesan mengandung "quota"/"RESOURCE_EXHAUSTED", `'service_unavailable'` untuk 503, `null` untuk error lain
    - _Requirements: 3.1, 3.2, 2.3_
  - [x] 6.3 Implementasi `callWithFallback(callable $apiCall): array`
    - Loop: `getActiveModel()` → api call → jika error classified, `markUnavailable()` + dispatch `LogModelSwitchJob` → `nextAvailableModel()` → retry
    - Jika sukses setelah switch, panggil `setActiveModel()`, tambah flag `switched_model` di response
    - Catch `AllModelsUnavailableException`, return `['text' => '...pesan Indonesia...', 'error' => true]`
    - _Requirements: 2.1, 2.2, 2.3, 2.6, 6.1, 6.2, 6.3, 6.4_
  - [x] 6.4 Refactor `chat()` dan `chatWithTools()` untuk gunakan `callWithFallback()`
    - Bungkus existing API call logic dalam closure, pass ke `callWithFallback()`; pastikan tidak ada perubahan signature public method
    - _Requirements: 6.1, 6.2, 6.3_
  - [x] 6.5 Tulis unit test untuk `classifyError()`
    - Test masing-masing klasifikasi (429, quota message, 503, generic error)
    - _Requirements: 3.1, 3.2, 2.3_
  - [x] 6.6 Tulis property test P6: Response format invariance after fallback
    - **Property 6: Response format invariance after fallback**
    - Mock client 429 pada model pertama, sukses pada model kedua; assert response array mengandung semua required keys plus `model`
    - **Validates: Requirements 6.1, 6.2, 6.3**
  - [x] 6.7 Tulis property test P7: AllModelsUnavailable dispatch dan user message
    - **Property 7: AllModelsUnavailable event and user-friendly error**
    - Mock semua model return 429; assert event di-dispatch dan response text = pesan Indonesia
    - **Validates: Requirements 2.5, 6.4, 10.1**

- [x] 7. LogModelSwitchJob (async logging)
  - [x] 7.1 Buat `App\Jobs\LogModelSwitchJob implements ShouldQueue`
    - Constructor dengan `readonly` properties: `fromModel`, `toModel`, `reason`, `errorMessage`, `requestContext`, `triggeredByTenantId`
    - `handle()`: insert `AiModelSwitchLog::create([...])`
    - _Requirements: 5.1, 5.3_
  - [x] 7.2 Tulis property test P5: Switch log completeness
    - **Property 5: Switch log completeness**
    - Dispatch `LogModelSwitchJob`, jalankan queue sync, assert record di database dengan field yang benar
    - **Validates: Requirements 5.1, 5.3**
  - [x] 7.3 Tulis property test P10: Tenant ID recorded in switch log
    - **Property 10: Tenant ID recorded in switch log**
    - Pass berbagai `triggeredByTenantId` (int dan null), assert nilai yang tersimpan di DB sesuai
    - **Validates: Requirements 9.3_**

- [x] 8. AllModelsUnavailable event listener (notifikasi)
  - [x] 8.1 Buat `App\Listeners\NotifyAllModelsUnavailable`
    - Handle `AllModelsUnavailable` event; kirim notifikasi via Slack webhook (`SLACK_ERROR_WEBHOOK_URL`) dan/atau email (`ERROR_ALERT_EMAIL_RECIPIENTS`) sesuai konfigurasi yang ada
    - _Requirements: 10.1, 10.2_
  - [x] 8.2 Daftarkan listener di `EventServiceProvider` (atau `AppServiceProvider` jika pakai automatic discovery)
    - Map `AllModelsUnavailable::class => [NotifyAllModelsUnavailable::class]`
    - _Requirements: 10.1_
  - [x] 8.3 Tulis unit test untuk `NotifyAllModelsUnavailable` listener
    - Mock notification channel; assert notifikasi dikirim saat event di-dispatch
    - _Requirements: 10.2_

- [x] 9. PruneModelSwitchLogs artisan command
  - [x] 9.1 Buat `App\Console\Commands\PruneModelSwitchLogs`
    - Signature: `ai:prune-switch-logs`
    - `handle()`: delete records lebih tua dari `SystemSetting::get('gemini_log_retention_days', 30)` hari
    - _Requirements: 5.4_
  - [x] 9.2 Daftarkan schedule di `routes/console.php` (atau `Kernel.php`)
    - Jalankan `ai:prune-switch-logs` harian
    - _Requirements: 5.4_
  - [x] 9.3 Tulis unit test untuk `PruneModelSwitchLogs`
    - Seed records dengan berbagai umur, jalankan command, assert hanya records lama yang dihapus
    - _Requirements: 5.4_

- [x] 10. Checkpoint — pastikan semua tests di task 6–9 pass
  - Jalankan `php artisan test --filter=GeminiService` dan semua test terkait logging/events
  - Pastikan tidak ada error; tanyakan kepada user jika ada pertanyaan.

- [x] 11. SystemSetting support untuk konfigurasi AI
  - [x] 11.1 Pastikan `SystemSetting` mendukung keys: `gemini_fallback_models`, `gemini_rate_limit_cooldown`, `gemini_quota_cooldown`, `gemini_log_retention_days`, `gemini_recovery_check_interval`
    - Tambah default values jika `SystemSetting::get()` belum support fallback default, atau gunakan `config()` sebagai fallback di `ModelSwitcher`
    - _Requirements: 8.1, 8.2_

- [x] 12. SuperAdmin AI Model monitoring page
  - [x] 12.1 Buat `App\Http\Controllers\SuperAdmin\AiModelController`
    - Method `index()`: ambil data dari `ModelSwitcher::getModelAvailability()` dan `AiModelSwitchLog::recent()` paginated, return view
    - Method `reset()`: panggil `ModelSwitcher::resetAll()`, redirect back dengan flash message
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6_
  - [x] 12.2 Buat Blade view `resources/views/super-admin/ai-model/index.blade.php`
    - Tampilkan: Active_Model saat ini dan statusnya, tabel availability semua model dengan estimasi recovery time, paginated log switch events
    - Tombol "Force Reset" yang POST ke route `super-admin.ai-model.reset`
    - Ikuti layout dan styling yang sudah ada di views SuperAdmin lainnya
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6_
  - [x] 12.3 Daftarkan routes di `routes/web.php` dalam group `super-admin`
    - `GET /super-admin/ai-model` → `AiModelController@index` → name `super-admin.ai-model.index`
    - `POST /super-admin/ai-model/reset` → `AiModelController@reset` → name `super-admin.ai-model.reset`
    - _Requirements: 7.1_

- [x] 13. SuperAdmin settings UI untuk fallback chain management
  - [x] 13.1 Tambah fields konfigurasi Gemini di `SystemSettingsController::update()` yang sudah ada
    - Terima input: `gemini_fallback_models` (textarea JSON atau comma-separated), `gemini_rate_limit_cooldown`, `gemini_quota_cooldown`, `gemini_log_retention_days`
    - Setelah save, panggil `ModelSwitcher::resetAll()` untuk invalidate cache
    - _Requirements: 8.1, 8.2, 8.4, 8.5_
  - [x] 13.2 Tambah fields di Blade view settings SuperAdmin yang sudah ada (`resources/views/super-admin/settings/index.blade.php` atau sejenisnya)
    - Section baru "Konfigurasi AI / Gemini": input fallback models (ordered list), cooldown fields
    - _Requirements: 8.4_

- [x] 14. Checkpoint akhir — wire semua komponen dan pastikan full test suite pass
  - Jalankan `php artisan test` (full suite)
  - Verifikasi schedule terdaftar dengan `php artisan schedule:list`
  - Pastikan tidak ada error; tanyakan kepada user jika ada pertanyaan.

## Notes

- Tasks dengan `*` bersifat opsional dan bisa dilewati untuk MVP yang lebih cepat
- Setiap property test harus diberi tag komentar: `// Feature: gemini-model-auto-switching, Property {N}: {property_text}`
- Property tests menggunakan PestPHP dengan minimal 100 iterasi per test (gunakan `repeat()` atau Faker dalam loop)
- `ModelSwitcher` beroperasi di application level (bukan per-tenant) — jangan scope cache key ke tenant
- Semua caller existing (`ProcessChatMessage`, dll) tidak perlu diubah karena perubahan hanya di internal `GeminiService`
