# Requirements Document

## Introduction

Fitur Gemini AI Model Auto-Switching memungkinkan sistem ERP multi-tenant untuk secara otomatis berpindah ke model Gemini alternatif ketika model utama mengalami rate limit, quota habis, atau error availability. Tujuannya adalah menjaga ketersediaan fitur AI secara continuous tanpa mengganggu pengalaman pengguna, serta menyediakan mekanisme recovery otomatis kembali ke model utama setelah quota reset.

Sistem ini bekerja di atas `GeminiService` yang sudah ada, memperluas mekanisme fallback yang saat ini bersifat per-request menjadi fallback yang persisten dan berbasis state dengan kemampuan monitoring, logging, dan recovery terjadwal.

## Glossary

- **GeminiService**: Service PHP yang menangani semua komunikasi dengan Google Gemini API di aplikasi ERP
- **ModelSwitcher**: Komponen baru yang mengelola state perpindahan model, fallback chain, dan recovery
- **Primary_Model**: Model Gemini yang dikonfigurasi sebagai model utama (`gemini.model` di config)
- **Fallback_Chain**: Urutan model alternatif yang akan dicoba ketika model sebelumnya tidak tersedia
- **Active_Model**: Model Gemini yang sedang aktif digunakan pada saat tertentu
- **Rate_Limit_Error**: Error HTTP 429 dari Gemini API yang menandakan request terlalu banyak per menit/hari
- **Quota_Error**: Error yang menandakan token/request quota harian/bulanan telah habis
- **Cooldown_Period**: Durasi waktu sebuah model ditandai sebagai tidak tersedia sebelum dicoba kembali
- **Switch_Event**: Kejadian perpindahan Active_Model dari satu model ke model lain
- **Recovery**: Proses kembalinya Active_Model ke Primary_Model setelah Cooldown_Period berakhir
- **AiModelSwitchLog**: Model database baru untuk menyimpan riwayat Switch_Event
- **Tenant**: Instansi bisnis individual dalam sistem ERP multi-tenant
- **SystemSetting**: Model yang menyimpan konfigurasi sistem termasuk pengaturan AI

---

## Requirements

### Requirement 1: Persistent Model State Management

**User Story:** As a system administrator, I want the active Gemini model state to persist across requests, so that the system does not repeatedly retry a rate-limited model on every new request.

#### Acceptance Criteria

1. THE ModelSwitcher SHALL maintain the Active_Model state in application cache with a cache key scoped per application instance (non-tenant-specific, karena API key bersifat global).
2. WHEN a Switch_Event occurs, THE ModelSwitcher SHALL update the cached Active_Model state immediately so subsequent requests use the new model without re-attempting the failed model.
3. THE ModelSwitcher SHALL store the timestamp of each Switch_Event alongside the model name in cache.
4. WHEN the cache is unavailable, THE ModelSwitcher SHALL fall back to in-memory state for the duration of the current request cycle.
5. THE ModelSwitcher SHALL expose a `getActiveModel(): string` method that returns the current Active_Model from cache, defaulting to Primary_Model if no switch has occurred.

---

### Requirement 2: Automatic Fallback Chain Execution

**User Story:** As a business user, I want the AI to automatically try alternative models when the current model fails, so that I can continue using AI features without interruption.

#### Acceptance Criteria

1. WHEN a Rate_Limit_Error occurs on the Active_Model, THE ModelSwitcher SHALL attempt the next model in the Fallback_Chain without surfacing the error to the user.
2. WHEN a Quota_Error occurs on the Active_Model, THE ModelSwitcher SHALL attempt the next model in the Fallback_Chain without surfacing the error to the user.
3. WHEN an HTTP 503 (service unavailable) error occurs on the Active_Model, THE ModelSwitcher SHALL attempt the next model in the Fallback_Chain.
4. THE ModelSwitcher SHALL attempt each model in the Fallback_Chain in the order defined in `config('gemini.fallback_models')`.
5. WHEN all models in the Fallback_Chain have been exhausted, THE ModelSwitcher SHALL throw a `AllModelsUnavailableException` with a user-friendly message.
6. WHEN a model in the Fallback_Chain succeeds after a switch, THE GeminiService SHALL return the response with a `switched_model` flag in the response array indicating the fallback was used.
7. THE Fallback_Chain SHALL skip any model that is currently marked as unavailable in cache (still within its Cooldown_Period).

---

### Requirement 3: Model Cooldown and Availability Tracking

**User Story:** As a system, I want to remember which models are currently rate-limited, so that I don't waste API calls retrying models that are known to be unavailable.

#### Acceptance Criteria

1. WHEN a Rate_Limit_Error occurs on a model, THE ModelSwitcher SHALL mark that model as unavailable in cache with a Cooldown_Period of 60 seconds.
2. WHEN a Quota_Error occurs on a model, THE ModelSwitcher SHALL mark that model as unavailable in cache with a Cooldown_Period of 3600 seconds (1 jam) to reflect daily quota reset cycles.
3. THE ModelSwitcher SHALL store the unavailability reason (rate_limit atau quota_exceeded) alongside the model entry in cache.
4. WHEN a model's Cooldown_Period expires, THE ModelSwitcher SHALL automatically consider that model as available again on the next request cycle.
5. THE ModelSwitcher SHALL expose a `getModelAvailability(): array` method yang mengembalikan status setiap model dalam Fallback_Chain beserta alasan unavailability dan estimasi waktu recovery.
6. WHEN checking availability, THE ModelSwitcher SHALL return availability status for all models defined in `config('gemini.fallback_models')`.

---

### Requirement 4: Automatic Recovery to Primary Model

**User Story:** As a system administrator, I want the system to automatically return to using the primary Gemini model once it becomes available again, so that we consistently use the best-configured model.

#### Acceptance Criteria

1. WHEN the Primary_Model's Cooldown_Period expires, THE ModelSwitcher SHALL attempt to use the Primary_Model on the next AI request.
2. WHEN the Primary_Model successfully responds after a Switch_Event, THE ModelSwitcher SHALL update the Active_Model back to Primary_Model and log a recovery Switch_Event.
3. THE ModelSwitcher SHALL NOT require manual intervention to recover back to the Primary_Model.
4. WHEN a scheduled recovery check runs, THE ModelSwitcher SHALL probe the Primary_Model availability using a minimal test prompt with low token usage.
5. WHERE the optional scheduled recovery is configured, THE ModelSwitcher SHALL run recovery probes at the interval defined in `config('gemini.recovery_check_interval', 300)` seconds.

---

### Requirement 5: Switch Event Logging

**User Story:** As a system administrator, I want to see a log of every time the AI model was switched, so that I can monitor API health and quota usage patterns.

#### Acceptance Criteria

1. WHEN a Switch_Event occurs, THE AiModelSwitchLog SHALL record: timestamp, from_model, to_model, reason (rate_limit/quota_exceeded/service_unavailable/recovery), dan error_message.
2. THE AiModelSwitchLog SHALL be stored in the database in a dedicated `ai_model_switch_logs` table.
3. WHEN logging a Switch_Event, THE GeminiService SHALL record the log asynchronously (via Laravel Queue) agar tidak menambah latency pada respons AI.
4. THE AiModelSwitchLog SHALL retain records for a configurable period, defaulting to 30 days, after which records are automatically purged.
5. THE AiModelSwitchLog SHALL include a `request_context` field menyimpan context minimal (module name atau route) tanpa menyimpan data sensitif pengguna.

---

### Requirement 6: Transparent User Experience

**User Story:** As a business user, I want to continue using AI features without noticing any interruption when the system switches models, so that my workflow is not disrupted.

#### Acceptance Criteria

1. WHEN a Switch_Event occurs mid-request, THE GeminiService SHALL complete the original request using the new model and return a valid response to the caller.
2. THE GeminiService SHALL NOT expose raw Gemini API error messages to end users during a Switch_Event.
3. WHEN a Switch_Event occurs, THE GeminiService SHALL return the response in the same format (array structure) as a normal response, with an additional optional `model` field indicating which model was used.
4. IF all models are exhausted, THEN THE GeminiService SHALL return a user-friendly error message in Bahasa Indonesia: "Layanan AI sedang mengalami gangguan. Silakan coba beberapa saat lagi."

---

### Requirement 7: Monitoring Dashboard

**User Story:** As a system administrator, I want to view the current model status and switch history in the admin panel, so that I can proactively manage AI availability and capacity.

#### Acceptance Criteria

1. THE SuperAdmin panel SHALL display the current Active_Model and its status (available/rate_limited/quota_exceeded).
2. THE SuperAdmin panel SHALL display the availability status of all models in the Fallback_Chain.
3. THE SuperAdmin panel SHALL display a paginated log of recent Switch_Events from AiModelSwitchLog.
4. THE SuperAdmin panel SHALL display the timestamp of the last Switch_Event and the reason for the switch.
5. WHEN viewing the monitoring dashboard, THE SuperAdmin panel SHALL show the estimated recovery time for any models currently in Cooldown_Period.
6. THE SuperAdmin panel SHALL provide a "Force Reset" action yang memungkinkan administrator mereset semua model cooldown secara manual.

---

### Requirement 8: Configuration Management

**User Story:** As a system administrator, I want to configure the fallback chain and cooldown periods through the admin panel, so that I can tune the auto-switching behavior without code changes.

#### Acceptance Criteria

1. THE SystemSetting SHALL support storing the ordered Fallback_Chain as a configurable JSON value under the key `gemini_fallback_models`.
2. THE SystemSetting SHALL support storing Cooldown_Period durations for rate_limit and quota_exceeded errors under keys `gemini_rate_limit_cooldown` dan `gemini_quota_cooldown`.
3. WHEN `gemini_fallback_models` is set in SystemSetting, THE ModelSwitcher SHALL use that value instead of `config('gemini.fallback_models')`.
4. THE SuperAdmin settings panel SHALL include UI fields to manage the Fallback_Chain order (drag-and-drop atau ordered list) dan Cooldown_Period values.
5. WHEN a new Fallback_Chain is saved, THE ModelSwitcher SHALL invalidate the current model availability cache and rebuild the chain with the new configuration.

---

### Requirement 9: Multi-Tenant Isolation

**User Story:** As a system architect, I want model switching to operate at the application level (not per-tenant), so that the system correctly reflects the single shared Gemini API key's quota state.

#### Acceptance Criteria

1. THE ModelSwitcher SHALL manage model state at the application level (global cache key), bukan per-tenant, karena semua tenant menggunakan satu Gemini API key yang sama.
2. WHEN a Rate_Limit_Error is triggered by one tenant's request, THE ModelSwitcher SHALL switch the Active_Model for all subsequent requests dari semua tenant.
3. THE AiModelSwitchLog SHALL record which tenant's request triggered each Switch_Event untuk keperluan audit.
4. THE ModelSwitcher SHALL NOT allow individual tenants to override or reset the global model state.

---

### Requirement 10: Observability and Alerting

**User Story:** As a system administrator, I want to receive alerts when all Gemini models become unavailable, so that I can take immediate action to restore AI service.

#### Acceptance Criteria

1. WHEN all models in the Fallback_Chain are simultaneously in Cooldown_Period, THE ModelSwitcher SHALL dispatch an `AllModelsUnavailable` event.
2. WHEN an `AllModelsUnavailable` event is dispatched, THE NotificationService SHALL send an alert to the configured error alert channels (Slack dan/atau email sesuai konfigurasi `SLACK_ERROR_WEBHOOK_URL` dan `ERROR_ALERT_EMAIL_RECIPIENTS`).
3. THE ModelSwitcher SHALL track the frequency of Switch_Events per hour dan log a warning WHEN Switch_Events exceed 10 occurrences within a 60-minute window.
4. WHEN the Active_Model recovers back to Primary_Model, THE NotificationService SHALL send a recovery notification to the configured alert channels.
