# Implementation Plan: ERP AI Agent

## Overview

Implementasi ERP AI Agent dilakukan secara inkremental di atas infrastruktur yang sudah ada (GeminiService, ToolRegistry, ChatController, AiMemoryService). Setiap task membangun di atas task sebelumnya, dimulai dari fondasi data layer, kemudian core agent components, lalu integrasi dan fitur lanjutan.

## Tasks

- [x] 1. Buat migrations dan models untuk data layer baru
  - Buat migration `create_agent_audit_logs_table` dengan kolom: `tenant_id`, `user_id`, `session_id`, `action_name`, `action_type`, `parameters` (json), `result` (json), `status`, `error_message`, `is_undoable`, `undoable_until`
  - Buat migration `create_proactive_insights_table` dengan kolom: `tenant_id`, `condition_type`, `urgency`, `title`, `description`, `business_impact`, `recommendations` (json), `condition_data` (json), `condition_hash`, `suppressed_until`
  - Buat migration `create_insight_reads_table` (pivot) dengan kolom: `insight_id`, `user_id`, `status`, `read_at`
  - Buat migration `add_agent_columns_to_chat_sessions_table` untuk menambah: `session_type`, `active_plan` (json), `execution_status`, `erp_context_snapshot` (json), `is_cancelled`
  - Buat Eloquent model `AgentAuditLog` dengan fillable, casts, dan relasi ke User dan ChatSession
  - Buat Eloquent model `ProactiveInsight` dengan fillable, casts, relasi ke InsightRead
  - Buat Eloquent model `InsightRead` sebagai pivot model dengan relasi ke ProactiveInsight dan User
  - _Requirements: 6.3, 4.2, 4.5, 9.4_

- [x] 2. Buat DTOs untuk Agent Core
  - Buat `app/DTOs/Agent/AgentStep.php` dengan constructor properties: `order`, `name`, `toolName`, `args`, `isWriteOp`, `dependsOnStep`
  - Buat `app/DTOs/Agent/AgentPlan.php` dengan constructor properties: `goal`, `steps` (AgentStep[]), `summary`, `hasWriteOps`, `language`
  - Buat `app/DTOs/Agent/ErpContext.php` dengan constructor properties: `tenantId`, `kpiSummary`, `activeModules`, `accountingPeriod`, `industrySkills`, `builtAt`; tambahkan method `toSystemPrompt()` dan `isStale()`
  - Buat `app/DTOs/Agent/StepResult.php` dengan properties: `stepOrder`, `status`, `output`, `errorMessage`
  - Buat `app/DTOs/Agent/ExecutionContext.php` untuk menyimpan accumulated step outputs dengan method `get(stepOrder)` dan `set(stepOrder, output)`
  - Buat `app/DTOs/Agent/UndoResult.php` dengan properties: `success`, `message`, `restoredData`
  - _Requirements: 1.1, 1.3, 2.1_

- [x] 3. Implementasi AgentContextBuilder
  - Buat `app/Services/Agent/AgentContextBuilder.php`
  - Implementasi method `build(int $tenantId, array $activeModules): ErpContext` menggunakan parallel queries (DB::transaction atau concurrent queries) untuk KPI summary: revenue bulan ini, stok kritis, piutang jatuh tempo, jumlah karyawan aktif
  - Implementasi method `refresh(ErpContext $context, string $module): ErpContext` untuk update incremental per modul
  - Pastikan build selesai dalam < 3 detik dengan timeout handling; jika query timeout, gunakan partial context dan tandai field yang tidak tersedia
  - Scope semua query ke `tenant_id` yang diberikan
  - _Requirements: 2.1, 2.2, 2.4, 2.5_

  - [x] 3.1 Tulis property test untuk AgentContextBuilder — Property 5: ERP Context Completeness
    - **Property 5: ERP Context Completeness** — untuk kombinasi modul aktif apapun, `build()` selalu menghasilkan ErpContext dengan field `tenantId`, `kpiSummary`, `activeModules`, `builtAt` yang non-null
    - **Validates: Requirements 2.1**

  - [x] 3.2 Tulis property test untuk AgentContextBuilder — Property 6: Tenant Context Isolation
    - **Property 6: Tenant Context Isolation** — untuk dua tenant berbeda, ErpContext masing-masing tidak mengandung data dari tenant lain
    - **Validates: Requirements 2.5, 9.1**

  - [x] 3.3 Tulis integration test untuk build time AgentContextBuilder
    - Verifikasi `build()` selesai dalam < 3 detik dengan data tenant nyata
    - _Requirements: 2.2_

- [x] 4. Implementasi AgentPlanner
  - Buat `app/Services/Agent/AgentPlanner.php`
  - Implementasi method `plan(string $instruction, ErpContext $context, array $availableTools, string $language): AgentPlan` yang memanggil GeminiService dengan planning prompt khusus; parse response menjadi array AgentStep dengan validasi struktur
  - Implementasi method `requiresPlanning(string $instruction): bool` untuk deteksi apakah instruksi perlu multi-step atau bisa single-turn
  - Pastikan plan menghasilkan maksimal 10 langkah; jika Gemini gagal, retry 1x lalu fallback ke single-turn response
  - Dukung instruksi dalam Bahasa Indonesia dan Bahasa Inggris
  - _Requirements: 1.1, 1.2, 1.6_

  - [x] 4.1 Tulis property test untuk AgentPlanner — Property 1: Plan Step Count Invariant
    - **Property 1: Plan Step Count Invariant** — untuk instruksi apapun yang memerlukan planning, `plan()` menghasilkan AgentPlan dengan 1–10 langkah, setiap langkah memiliki `name`, `toolName`, dan `args` yang valid
    - **Validates: Requirements 1.1**

  - [x] 4.2 Tulis unit test untuk AgentPlanner
    - Test happy path: instruksi multi-step menghasilkan plan terurut
    - Test edge case: instruksi kosong, plan dengan 1 langkah, plan dengan tepat 10 langkah
    - Test fallback: Gemini gagal → retry → fallback single-turn
    - _Requirements: 1.1, 1.6_

- [x] 5. Implementasi AgentExecutor
  - Buat `app/Services/Agent/AgentExecutor.php`
  - Implementasi method `executeStep(AgentStep $step, ExecutionContext $context, ToolRegistry $registry): StepResult` yang memanggil tool via ToolRegistry, menangani timeout > 10 detik sebagai failure, dan mencatat hasil ke AgentAuditLog
  - Implementasi method `resolveArgs(array $args, ExecutionContext $context): array` untuk mengganti placeholder `{{step_N.field}}` dengan nilai aktual dari ExecutionContext
  - Implementasi method `canUndo(AgentAuditLog $log): bool` — true jika `is_undoable = true` dan `undoable_until` belum lewat
  - Implementasi method `undo(AgentAuditLog $log, ToolRegistry $registry): UndoResult` untuk reverse aksi write dalam window 5 menit
  - Validasi permission user sebelum eksekusi; tolak dengan pesan permission yang diperlukan jika tidak berwenang
  - Tolak operasi destruktif (bulk delete, modifikasi data historis terkunci) sebelum eksekusi
  - _Requirements: 1.3, 1.4, 6.1, 6.2, 6.3, 6.5, 6.6, 9.3_

  - [x] 5.1 Tulis property test untuk AgentExecutor — Property 2: Step Output Propagation
    - **Property 2: Step Output Propagation** — output langkah ke-i selalu tersedia di ExecutionContext untuk langkah ke-(i+1) hingga ke-N
    - **Validates: Requirements 1.3**

  - [x] 5.2 Tulis property test untuk AgentExecutor — Property 3: Fail-Fast Execution
    - **Property 3: Fail-Fast Execution** — jika langkah ke-k gagal, eksekusi berhenti dan langkah ke-(k+1) hingga ke-N tidak dieksekusi; kegagalan dicatat di AgentAuditLog
    - **Validates: Requirements 1.4**

  - [x] 5.3 Tulis property test untuk AgentExecutor — Property 4: Write Operation Approval Gate
    - **Property 4: Write Operation Approval Gate** — untuk plan yang mengandung `isWriteOp = true`, langkah write tidak dieksekusi tanpa konfirmasi eksplisit
    - **Validates: Requirements 1.5**

  - [x] 5.4 Tulis property test untuk AgentExecutor — Property 13: Audit Log Completeness
    - **Property 13: Audit Log Completeness** — setiap aksi write yang berhasil menghasilkan AgentAuditLog dengan semua field non-null: `user_id`, `tenant_id`, `action_name`, `parameters`, `result`, `status`, `created_at`
    - **Validates: Requirements 6.3**

  - [x] 5.5 Tulis property test untuk AgentExecutor — Property 14: Permission Enforcement
    - **Property 14: Permission Enforcement** — untuk kombinasi (user, aksi) di mana user tidak punya permission, eksekusi selalu ditolak dengan pesan error yang menyebutkan permission yang diperlukan
    - **Validates: Requirements 6.5**

  - [x] 5.6 Tulis property test untuk AgentExecutor — Property 15: Destructive Action Rejection
    - **Property 15: Destructive Action Rejection** — instruksi yang mengandung operasi destruktif selalu ditolak dengan penjelasan alasan, tidak pernah dieksekusi
    - **Validates: Requirements 9.3**

  - [x] 5.7 Tulis unit test untuk AgentExecutor
    - Test resolveArgs dengan placeholder valid dan tidak valid
    - Test canUndo dan undo dalam/di luar window 5 menit
    - Test timeout handling > 10 detik
    - _Requirements: 1.3, 6.6_

- [x] 6. Checkpoint — Pastikan semua tests pass
  - Pastikan semua tests pass, tanyakan kepada user jika ada pertanyaan.

- [x] 7. Implementasi SkillRouter
  - Buat `app/Services/Agent/SkillRouter.php`
  - Implementasi method `detectSkills(string $message, array $activeModules): array` untuk deteksi domain bisnis dari intent pesan (Akuntansi, Inventory, HRM, Penjualan, Project)
  - Implementasi method `buildSkillPrompt(array $skills, ErpContext $context): string` yang menyusun system prompt tambahan per skill dengan terminologi domain yang tepat (akuntansi Indonesia, regulasi ketenagakerjaan, metode costing FIFO/Average)
  - Aktifkan skill industri khusus (Healthcare, Manufaktur, Telecom) jika modul tersebut aktif di tenant
  - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6_

  - [x] 7.1 Tulis unit test untuk SkillRouter
    - Test deteksi skill dari berbagai pesan dalam Bahasa Indonesia dan Inggris
    - Test buildSkillPrompt mengandung terminologi yang benar per domain
    - _Requirements: 8.2, 8.3_

- [x] 8. Implementasi AgentOrchestrator
  - Buat `app/Services/Agent/AgentOrchestrator.php`
  - Implementasi method `handle(string $message, User $user, AgentSession $session, bool $confirmed): \Generator` yang mengorkestrasi: build ErpContext → load memory → detect skills → plan (jika perlu) → tampilkan Approval Gate jika ada write ops → eksekusi langkah per langkah → update memory → kirim summary
  - Setiap langkah eksekusi menghasilkan SSE event: `plan_summary`, `step_started`, `step_completed`, `step_failed`, `task_summary`
  - Implementasi method `cancel(AgentSession $session): void` yang set flag `is_cancelled`; cek flag sebelum setiap langkah
  - Pastikan acknowledgment awal dikirim dalam < 2 detik
  - Integrasikan AgentPlanner, AgentExecutor, AgentContextBuilder, SkillRouter, dan AiMemoryService
  - _Requirements: 1.2, 1.5, 7.1, 7.2, 7.3, 7.4, 7.5, 7.6_

  - [x] 8.1 Tulis unit test untuk AgentOrchestrator (end-to-end dengan mock)
    - Test happy path multi-step dengan mock GeminiService dan ToolRegistry
    - Test Approval Gate: plan dengan write ops tidak dieksekusi tanpa konfirmasi
    - Test cancellation: langkah berikutnya tidak dieksekusi setelah cancel
    - _Requirements: 1.2, 1.5, 7.4, 7.5_

- [x] 9. Implementasi AiMemoryService extensions
  - Tambahkan method `pruneStaleMemories(int $tenantId): void` pada `AiMemoryService` yang ada: turunkan `confidence_score` 50% untuk record dengan `last_seen_at` > 90 hari, hapus record dengan `confidence_score` hasil penurunan < 0.1
  - Tambahkan method `saveTaskPattern(int $tenantId, int $userId, AgentPlan $plan): void` untuk menyimpan pola task yang berhasil sebagai template
  - Pastikan semua query di-scope ke kombinasi `tenant_id` + `user_id`
  - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_

  - [x] 9.1 Tulis property test untuk AiMemoryService — Property 11: Memory Isolation Per Tenant-User
    - **Property 11: Memory Isolation Per Tenant-User** — `getPreferences(tenantA, userA)` tidak pernah mengembalikan data dari kombinasi (tenantB, userB) yang berbeda
    - **Validates: Requirements 5.4**

  - [x] 9.2 Tulis property test untuk AiMemoryService — Property 12: Memory Confidence Decay
    - **Property 12: Memory Confidence Decay** — record dengan `last_seen_at` > 90 hari mendapat penurunan confidence 50%; record dengan confidence hasil penurunan < 0.1 dihapus
    - **Validates: Requirements 5.5**

- [x] 10. Implementasi ProactiveInsightEngine
  - Buat `app/Services/Agent/ProactiveInsightEngine.php`
  - Implementasi method `analyze(int $tenantId): array` yang mengecek 5 kondisi: stok < reorder point, piutang jatuh tempo > 7 hari, anggaran terpakai > 90%, kontrak karyawan berakhir dalam 30 hari, invoice belum dibayar > threshold; hasilkan ProactiveInsight untuk setiap kondisi yang terpenuhi
  - Setiap ProactiveInsight harus mengandung: `title`, `description`, `business_impact`, `recommendations` (minimal 1 elemen)
  - Implementasi dedup via `condition_hash`: skip jika hash yang sama sudah ada dan `suppressed_until` belum lewat
  - Implementasi method `getPendingInsights(int $tenantId, int $userId): array` yang mengecek InsightRead pivot
  - Implementasi method `dismiss(ProactiveInsight $insight, string $reason): void` yang set `suppressed_until = now() + 24 jam` dan buat InsightRead record
  - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_

  - [x] 10.1 Tulis property test untuk ProactiveInsightEngine — Property 8: Proactive Insight Condition Trigger
    - **Property 8: Proactive Insight Condition Trigger** — untuk kondisi bisnis yang memenuhi salah satu trigger, `analyze()` menghasilkan minimal satu ProactiveInsight
    - **Validates: Requirements 4.2**

  - [x] 10.2 Tulis property test untuk ProactiveInsightEngine — Property 9: Proactive Insight Structure Completeness
    - **Property 9: Proactive Insight Structure Completeness** — setiap ProactiveInsight yang dihasilkan selalu mengandung `title`, `description`, `business_impact`, dan `recommendations` (array ≥ 1 elemen)
    - **Validates: Requirements 4.3**

  - [x] 10.3 Tulis property test untuk ProactiveInsightEngine — Property 10: Insight Suppression After Dismiss
    - **Property 10: Insight Suppression After Dismiss** — setelah dismiss, `getPendingInsights()` tidak mengembalikan insight dengan `condition_hash` yang sama dalam window 24 jam
    - **Validates: Requirements 4.5**

  - [x] 10.4 Tulis integration test untuk scheduled insight generation
    - Verifikasi job berjalan dan menghasilkan insights untuk kondisi yang terpenuhi
    - _Requirements: 4.1_

- [x] 11. Buat scheduled job untuk ProactiveInsightEngine
  - Buat `app/Jobs/GenerateProactiveInsightsJob.php` yang memanggil `ProactiveInsightEngine::analyze()` per tenant aktif
  - Daftarkan job di `app/Console/Kernel.php` dengan schedule setiap 6 jam
  - Tambahkan push notification untuk insight dengan urgency `high` atau `critical` jika fitur notifikasi push aktif untuk tenant
  - _Requirements: 4.1, 4.6_

- [x] 12. Implementasi AgentController dan routes
  - Buat `app/Http/Controllers/AgentController.php` dengan method: `send`, `stream`, `confirm`, `cancel`, `undo`, `insights`, `dismissInsight`, `memory`, `clearMemory`
  - `stream()` mengembalikan `StreamedResponse` dengan SSE format; delegasikan ke `AgentOrchestrator::handle()` sebagai Generator
  - `send()` mengembalikan `JsonResponse` untuk non-streaming request
  - `confirm()` meneruskan konfirmasi user ke session yang aktif dan melanjutkan eksekusi
  - `cancel()` memanggil `AgentOrchestrator::cancel()`
  - `undo()` memanggil `AgentExecutor::undo()` untuk audit log terakhir dalam 5 menit
  - `insights()` memanggil `ProactiveInsightEngine::getPendingInsights()`
  - `memory()` dan `clearMemory()` mengekspos AiMemoryService untuk user
  - Tambahkan routes di `routes/api.php` atau `routes/web.php` dengan middleware auth dan tenant scope
  - _Requirements: 7.1, 7.2, 7.6, 5.6, 4.4, 6.6_

  - [x] 12.1 Tulis unit test untuk AgentController
    - Test setiap endpoint: request validation, response format, error handling
    - Test SSE streaming menghasilkan event yang benar
    - _Requirements: 7.1, 7.6_

- [x] 13. Implementasi Cross-Module Query support
  - Tambahkan WorkflowTools baru di ToolRegistry untuk kombinasi modul: Akuntansi+Inventory, Akuntansi+HRM, Penjualan+CRM+Inventory, HRM+Payroll+Absensi, Project+Keuangan
  - Setiap tool cross-module mengeksekusi query paralel ke modul terkait dan mengkorelasikan hasilnya
  - Jika modul tidak aktif, kembalikan hasil parsial dari modul yang aktif beserta daftar modul tidak tersedia (tidak pernah error total)
  - Pastikan eksekusi selesai dalam < 5 detik untuk query 3 modul
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

  - [x] 13.1 Tulis property test untuk Cross-Module Query — Property 7: Partial Cross-Module Results
    - **Property 7: Partial Cross-Module Results** — untuk kombinasi modul di mana sebagian tidak aktif, hasil selalu parsial (bukan error total) dengan daftar modul tidak tersedia
    - **Validates: Requirements 3.5**

  - [x] 13.2 Tulis integration test untuk cross-module query time
    - Verifikasi query 3 modul selesai dalam < 5 detik
    - _Requirements: 3.4_

- [x] 14. Integrasi dengan Automation Builder (Workflow Tools)
  - Tambahkan tool `trigger_workflow` di ToolRegistry yang memanggil Automation Builder dengan parameter yang sesuai
  - Tambahkan tool `list_workflows` untuk membaca daftar workflow aktif tenant
  - Tangani callback notifikasi hasil workflow dan sampaikan ke AgentSession yang aktif
  - Jika workflow dalam kondisi nonaktif, informasikan user dan tawarkan alternatif
  - Jika aksi memerlukan approval workflow, inisiasi proses approval dan informasikan user
  - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_

- [x] 15. Implementasi quota dan rate limiting per tenant
  - Tambahkan middleware atau service untuk mengecek kuota AI per tenant berdasarkan subscription plan
  - Tolak request yang melebihi kuota dengan pesan informatif
  - Deteksi pola penggunaan mencurigakan (banyak write ops dalam waktu singkat) dan batasi laju eksekusi; kirim notifikasi ke admin tenant
  - _Requirements: 9.2, 9.6_

- [x] 16. Checkpoint akhir — Pastikan semua tests pass
  - Pastikan semua tests pass, tanyakan kepada user jika ada pertanyaan.

## Notes

- Task bertanda `*` bersifat opsional dan dapat dilewati untuk MVP yang lebih cepat
- Setiap task mereferensikan requirements spesifik untuk traceability
- Property-based tests menggunakan library **eris/eris** (PHP PBT library)
- Semua query data wajib di-scope ke `tenant_id` yang terverifikasi (Requirements 9.1)
- Audit log tidak boleh memiliki softDeletes — data tidak dapat dihapus oleh user biasa (Requirements 9.4)
- Komponen baru ditempatkan di `app/Services/Agent/` dan `app/DTOs/Agent/`
