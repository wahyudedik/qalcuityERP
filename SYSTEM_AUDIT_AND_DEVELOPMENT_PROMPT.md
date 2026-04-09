# 📋 QALCUITY ERP - COMPREHENSIVE SYSTEM AUDIT & DEVELOPMENT PROMPT

**Generated:** 2026-04-10  
**System:** QalcuityERP - Multi-Tenant Enterprise Resource Planning  
**Tech Stack:** Laravel 11, PHP 8.2+, MySQL, TailwindCSS, Alpine.js, Vite  

---

## 🎯 PROMPT UTAMA UNTUK AI DEVELOPER

```
Anda adalah Senior Full-Stack Developer dan System Architect yang berpengalaman dalam Laravel ERP System. 
Tugas Anda adalah melakukan audit menyeluruh, memperbaiki bug, dan mengembangkan fitur yang masih belum lengkap 
dalam sistem QalcuityERP berdasarkan daftar berikut ini.

Prioritas:
1. P0 - CRITICAL: Bug yang menyebabkan error/crash, data corruption, security vulnerability
2. P1 - HIGH: Fitur penting yang belum lengkap atau tidak berfungsi dengan baik
3. P2 - MEDIUM: Enhancement, optimization, missing features
4. P3 - LOW: UI/UX improvement, documentation, code cleanup
```

---

## 📊 RINGKASAN SISTEM

### Modul Utama Yang Sudah Ada:
1. ✅ **Core ERP**: Accounting, Inventory, Sales, Purchasing, HRM, Finance
2. ✅ **Healthcare**: EMR, Patients, Appointments, Lab, Radiology, Pharmacy, Inpatient, Outpatient, ER, Telemedicine, BPJS
3. ✅ **Hotel & Hospitality**: Front Office, Housekeeping, Revenue Management, F&B, Spa, Banquet
4. ✅ **Industry-Specific**: Manufacturing, Construction, Agriculture, Fisheries, Livestock, Cosmetics, Telecom, Tour & Travel
5. ✅ **Advanced Features**: AI Integration, Multi-Company, Marketplace, E-commerce, Offline Mode, GDPR Compliance

### Statistik Codebase:
- **Migrations**: 256 files
- **Models**: 471+ files
- **Controllers**: 150+ files
- **Services**: 257+ files
- **Views**: 100+ directories dengan 500+ blade files
- **Routes**: API (200+ endpoints), Web (1000+ routes), Healthcare (50+ routes)

---

## 🐛 BUG DAN ISSUES YANG DITEMUKAN

### 🔴 P0 - CRITICAL BUGS

#### 1. Database Migration Issues
**Priority**: P0  
**Category**: Database/Backend  

**Masalah:**
- Multiple migrations menggunakan `Schema::dropIfExists()` yang berpotensi menghapus data production
- Foreign key constraints tidak konsisten antar migration
- Beberapa migration tidak memiliki proper `down()` method
- Tabel yang di-drop di satu migration tapi di-recreate di migration lain tanpa data migration

**Files Affected:**
- `database/migrations/2026_04_08_1800001_create_lab_radiology_billing_tables.php`
- `database/migrations/2026_04_08_1900001_create_telemedicine_resource_inventory_tables.php`
- `database/migrations/2026_04_08_200001_create_emr_tables.php`
- `database/migrations/2026_04_09_100000_create_quality_control_tables.php`
- `database/migrations/2026_04_09_200000_create_gdpr_compliance_tables.php`

**Prompt AI untuk Fix:**
```
Perbaiki migration files yang menggunakan Schema::dropIfExists() dengan pendekatan yang aman:
1. Ganti dengan Schema::hasTable() check sebelum create
2. Pastikan semua migration memiliki proper down() method yang reversible
3. Tambahkan data migration script jika ada tabel yang di-restructure
4. Gunakan DB::statement('SET FOREIGN_KEY_CHECKS=0') hanya jika benar-benar necessary
5. Tambahkan try-catch untuk backward compatibility

Contoh pattern yang benar:
if (!Schema::hasTable('table_name')) {
    Schema::create('table_name', function (Blueprint $table) { ... });
}
```

---

#### 2. Missing Foreign Key Constraints
**Priority**: P0  
**Category**: Database Integrity  

**Masalah:**
- Beberapa tabel menggunakan `unsignedBigInteger()` untuk foreign key tanpa proper constraint
- Missing `->constrained()` atau `->onDelete()` action
- Potensi orphaned records saat parent deleted

**Files Affected:**
- `database/migrations/2026_04_08_1100001_create_hospital_resource_tables.php` (operating_room_id, surgery_schedule_id)
- `database/migrations/2026_04_08_1900001_create_telemedicine_resource_inventory_tables.php`
- `database/migrations/2026_04_08_700001_create_laboratory_tables.php` (lab_order_id)

**Prompt AI untuk Fix:**
```
Audit semua migration files dan perbaiki foreign key constraints:
1. Pastikan semua unsignedBigInteger() yang merupakan FK memiliki ->constrained('table_name')
2. Tentukan onDelete action: cascade, restrict, set null, atau no action
3. Tambahkan index untuk semua foreign key columns untuk performance
4. Buat migration baru untuk menambahkan FK yang missing di existing tables
5. Validasi dengan: php artisan db:show dan check integrity

Prioritas tabel yang harus dicek:
- operating_room_id, surgery_schedule_id, lab_order_id
- patient_visit_id, ward_id, bed_id
- equipment_id, maintenance_log_id
```

---

#### 3. Settings Cache Invalidation Gap
**Priority**: P0  
**Category**: Backend/Configuration  

**Masalah:**
- Settings tidak di-invalidate saat di-update导致 stale configuration
- User melihat data lama setelah update settings
- Memory dari memory overview: "Settings cache invalidation gap"

**Files Affected:**
- `app/Services/CacheService.php`
- `app/Http/Controllers/ModuleSettingsController.php`
- `app/Http/Controllers/ApiSettingsController.php`

**Prompt AI untuk Fix:**
```
Implement proper cache invalidation untuk settings:
1. Buat SettingsCacheService yang handle cache tag invalidation
2. Setiap update settings harus trigger Cache::tags(['settings'])->flush()
3. Tambahkan event listener: SettingsUpdated event -> ClearSettingsCache listener
4. Implement cache versioning untuk avoid stale data
5. Tambahkan artisan command: php artisan cache:clear:settings
6. Test dengan: update settings -> verify cache cleared -> verify new value loaded
```

---

#### 4. Healthcare Module - Incomplete Backend APIs
**Priority**: P0  
**Category**: Backend/API  

**Masalah:**
- Banyak route healthcare didefinisikan tapi controller methods belum lengkap
- Missing API endpoints untuk: lab results, radiology reports, surgery schedules
- HealthcareApiController hanya memiliki basic CRUD, missing specialized endpoints

**Files Affected:**
- `routes/healthcare.php` (440+ routes defined)
- `app/Http/Controllers/Api/HealthcareApiController.php`
- `app/Http/Controllers/Healthcare/*` (50+ controllers)

**Prompt AI untuk Fix:**
```
Lengkapi semua Healthcare API endpoints:

1. Laboratory Module:
   - GET /api/healthcare/lab-orders/{id}/results
   - POST /api/healthcare/lab-results/{id}/approve
   - GET /api/healthcare/lab-equipment/calibration-due
   - POST /api/healthcare/lab-samples/{id}/process

2. Radiology Module:
   - GET /api/healthcare/radiology-exams/{id}/images
   - POST /api/healthcare/radiology-reports/{id}/finalize
   - GET /api/healthcare/pacs/studies?patient_id={id}

3. Surgery Module:
   - POST /api/healthcare/surgery-schedules/{id}/assign-team
   - GET /api/healthcare/operating-rooms/availability
   - POST /api/healthcare/surgery-schedules/{id}/complete
   - GET /api/healthcare/surgery-schedules/stats

4. Pharmacy Module:
   - POST /api/healthcare/prescriptions/{id}/dispense
   - GET /api/healthcare/medications/expiring?days=30
   - POST /api/healthcare/pharmacy/stock-opname

5. Inpatient Module:
   - POST /api/healthcare/admissions/{id}/transfer-ward
   - GET /api/healthcare/beds/availability?ward_type=icu
   - POST /api/healthcare/admissions/{id}/discharge

Implementasi:
- Extend HealthcareApiController dengan methods di atas
- Pastikan semua endpoint menggunakan proper validation
- Tambahkan authorization checks
- Return standardized JSON response
- Write unit tests untuk setiap endpoint
```

---

#### 5. Offline Mode - Conflict Resolution Incomplete
**Priority**: P0  
**Category**: Frontend/Backend Sync  

**Masalah:**
- Offline sync conflict detection ada tapi auto-resolution belum optimal
- Missing UI untuk manual conflict resolution
- BUG-OFF-001 dan BUG-OFF-002 sudah di-fix tapi belum fully tested

**Files Affected:**
- `app/Http/Controllers/OfflineSyncController.php`
- `app/Services/OfflineConflictResolutionService.php`
- `resources/js/offline-sync.js`

**Prompt AI untuk Fix:**
```
Lengkapi offline mode conflict resolution:

Backend:
1. Implement smart conflict detection dengan timestamp + user priority
2. Tambahkan auto-resolve strategies: last-write-wins, user-role-priority, manual-review
3. Buat conflict audit trail untuk tracking
4. Endpoint: POST /api/offline/conflicts/{id}/resolve dengan merge strategy

Frontend:
1. Buat conflict resolution UI component (modal dengan side-by-side diff)
2. Show conflict count in notification badge
3. Implement retry queue dengan exponential backoff
4. Add "Sync Now" button dengan progress indicator
5. Offline indicator yang jelas di topbar

Testing:
1. Simulate offline mode dengan Chrome DevTools
2. Create conflicts dan test resolution
3. Verify data consistency after sync
4. Test dengan multiple users editing same record
```

---

### 🟠 P1 - HIGH PRIORITY ISSUES

#### 6. Missing Controller Methods untuk Defined Routes
**Priority**: P1  
**Category**: Backend  

**Masalah:**
- Routes didefinisikan di `routes/web.php` dan `routes/healthcare.php` tapi methods belum ada di controllers
- Akan menyebabkan "Method not found" error saat diakses

**Prompt AI untuk Fix:**
```
Audit semua routes vs controller methods:

1. Extract semua routes dari:
   - routes/web.php
   - routes/healthcare.php  
   - routes/api.php

2. Verify setiap route has corresponding controller method

3. Untuk missing methods, implement dengan:
   - Proper request validation
   - Authorization checks (policies/middleware)
   - Business logic implementation
   - Response formatting (JSON/Blade)
   - Error handling

4. Prioritas modul yang harus dicek:
   - Healthcare (50+ routes)
   - Hotel (Revenue Management, Banquet)
   - Manufacturing (Quality Control, Work Orders)
   - Telemedicine (Video Consultation integration)

5. Buat test untuk setiap endpoint
```

---

#### 7. Hotel Module - Incomplete Features
**Priority**: P1  
**Category**: Frontend/Backend  

**Masalah:**
- Front Office check-in/check-out dashboard ada tapi belum fully functional
- Room upgrade/downgrade logic belum complete
- Group booking features partially implemented
- Guest preference system needs enhancement

**Files Affected:**
- `app/Http/Controllers/Hotel/FrontOfficeController.php`
- `app/Services/CheckInOutService.php`
- `app/Services/GroupBookingService.php`
- `resources/views/hotel/front-office/`

**Prompt AI untuk Fix:**
```
Lengkapi Hotel Front Office features:

1. Check-in Dashboard:
   - Show today's arrivals dengan status badges
   - Quick check-in action dengan ID scan integration
   - Room assignment dengan drag-drop
   - Pre-arrival form (guest preferences, special requests)

2. Check-out Process:
   - Auto-generate final bill dengan semua charges
   - Mini-bar integration
   - Payment processing (multiple payment methods)
   - Receipt generation (PDF)
   - Post-checkout survey

3. Room Changes:
   - Upgrade/downgrade dengan rate difference calculation
   - Visual room status map
   - Housekeeping notification on room change

4. Group Bookings:
   - Master booking dengan room block
   - Individual check-in/out within group
   - Group billing (master account vs individual)
   - Group reporting

5. Guest Preferences:
   - Preference tracking (room type, floor, amenities)
   - Auto-suggest based on history
   - Loyalty integration
   - Special occasions tracking

Frontend:
- Implement interactive room status grid
- Drag-drop room assignment
- Real-time status updates (Alpine.js/Livewire)
- Mobile-responsive check-in form
```

---

#### 8. Healthcare EMR - UI/UX Issues
**Priority**: P1  
**Category**: Frontend  

**Masalah:**
- EMR view terlalu complex dan overwhelming untuk doctors
- Missing quick actions untuk common workflows
- Patient timeline visualization belum optimal

**Files Affected:**
- `resources/views/emr/`
- `resources/views/healthcare/patient-visits/`
- `resources/views/healthcare/appointments/`

**Prompt AI untuk Fix:**
```
Redesign EMR UI/UX untuk better usability:

1. Patient Dashboard:
   - Vital signs chart (last 30 days trend)
   - Active medications list with alerts
   - Upcoming appointments
   - Recent lab results highlight
   - Quick action buttons (New Visit, Prescribe, Order Lab)

2. Visit Notes:
   - SOAP format template (Subjective, Objective, Assessment, Plan)
   - Voice-to-text integration untuk dictation
   - ICD-10 search dengan autocomplete
   - Previous notes sidebar untuk reference
   - Auto-save draft setiap 30 detik

3. Timeline View:
   - Interactive timeline semua patient interactions
   - Filter by type (visits, labs, medications, procedures)
   - Click to expand details
   - Color-coded by category

4. Prescriptions:
   - Drug interaction checker
   - Dosage calculator (weight/age-based)
   - Print prescription with clinic letterhead
   - E-prescription to pharmacy

5. Performance:
   - Lazy load patient history
   - Cache frequently accessed data
   - Optimize database queries (N+1 problem)
   - Implement infinite scroll untuk long lists
```

---

#### 9. Manufacturing Module - Quality Control Gaps
**Priority**: P1  
**Category**: Backend/Business Logic  

**Masalah:**
- Quality check workflow belum terintegrasi dengan production
- Defect tracking ada tapi root cause analysis missing
- QC standards belum enforce di work orders

**Files Affected:**
- `database/migrations/2026_04_09_100000_create_quality_control_tables.php`
- `app/Services/QualityControlService.php` (belum ada)
- `app/Http/Controllers/ManufacturingController.php`

**Prompt AI untuk Fix:**
```
Implement comprehensive Quality Control system:

1. QC Workflow Integration:
   - Pre-production QC (raw materials inspection)
   - In-process QC (during manufacturing checkpoints)
   - Post-production QC (finished goods inspection)
   - Auto-hold batch if QC failed

2. Quality Checks:
   - Define QC standards per product
   - Sampling plan (AQL-based)
   - Measurement recording dengan tolerances
   - Pass/Fail/Conditional decision
   - Photo attachment untuk evidence

3. Defect Management:
   - Defect categorization (critical, major, minor)
   - Root cause analysis (5-Why, Fishbone template)
   - CAPA (Corrective and Preventive Action) tracking
   - Defect trend analysis
   - Supplier defect rate tracking

4. Certificates of Analysis (CoA):
   - Auto-generate CoA untuk passed batches
   - CoA template per customer requirement
   - Digital signature
   - PDF export

5. Reporting:
   - First Pass Yield (FPY)
   - Defect rate by product/line/operator
   - Cost of Quality (COQ)
   - Trend charts (SPC - Statistical Process Control)
```

---

#### 10. Telemedicine - Video Integration Missing
**Priority**: P1  
**Category**: Frontend/Third-party Integration  

**Masalah:**
- Teleconsultation model ada tapi video call integration belum ada
- Missing WebRTC atau third-party video SDK integration
- Recording feature defined tapi not functional

**Files Affected:**
- `app/Models/Teleconsultation.php`
- `resources/views/telemedicine/`
- `database/migrations/2026_04_08_1000001_create_telemedicine_tables.php`

**Prompt AI untuk Fix:**
```
Implement telemedicine video consultation:

Options (choose one):
A. WebRTC (self-hosted, more control, more complex)
B. Agora.io SDK (recommended, easy integration, good quality)
C. Twilio Video (reliable, pay-per-use)
D. Jitsi Meet (open source, self-hosted option)

Implementation (using Agora as example):

Backend:
1. Install agora token generator: composer require agora/access-token
2. Create endpoint: POST /api/healthcare/teleconsultations/{id}/generate-token
3. Generate RTC token with expiry
4. Store recording config
5. Webhook handler untuk recording ready notification

Frontend:
1. Install Agora SDK: npm install agora-rtc-sdk-ng
2. Create VideoCall component:
   - Join room dengan token
   - Enable/disable camera & microphone
   - Screen sharing
   - Chat sidebar
   - Participant list
3. Recording indicator
4. Connection quality indicator
5. Fallback to audio-only if bandwidth low

Features:
- Virtual waiting room
- Appointment reminder (push notification + email)
- Auto-start recording (with consent)
- Prescription sharing during call
- Consultation notes template
- Post-consultation feedback
```

---

### 🟡 P2 - MEDIUM PRIORITY ENHANCEMENTS

#### 11. Missing Features - List untuk Dikembangkan

**Priority**: P2  
**Category**: Multiple Modules  

```
Implement fitur-fitur berikut yang masih belum ada:

A. REPORTING & ANALYTICS:
1. Executive Dashboard dengan KPI cards
2. Custom report builder (drag-drop fields)
3. Scheduled report delivery (email/PDF)
4. Real-time analytics dengan WebSocket
5. Predictive analytics (sales forecast, inventory demand)
6. Comparative analysis (YoY, MoM)
7. Export to multiple formats (PDF, Excel, CSV)
8. Report sharing dengan external stakeholders

B. NOTIFICATIONS:
1. Push notifications (Web Push API)
2. WhatsApp integration (Twilio/Wablas)
3. SMS notifications
4. In-app notification center dengan read/unread
5. Notification preferences per user
6. Escalation rules (if not acknowledged in X hours)
7. Digest emails (daily/weekly summary)

C. DOCUMENT MANAGEMENT:
1. Document versioning
2. Document approval workflow
3. OCR untuk scanned documents
4. Digital signature integration
5. Document templates library
6. Bulk document generation
7. Document expiry tracking
8. Integration dengan Google Drive/Dropbox

D. MOBILE APP:
1. React Native app untuk iOS/Android
2. Offline-first architecture
3. Barcode scanning dengan camera
4. Push notifications
5. Biometric login
6. Photo capture untuk evidence
7. GPS location tracking (untuk field staff)

E. AI ENHANCEMENTS:
1. AI-powered data entry (receipt scanning, invoice OCR)
2. Anomaly detection dengan ML
3. Predictive maintenance (IoT sensor data)
4. Natural language query ("show me sales last month")
5. Auto-categorization transactions
6. Smart recommendations (reorder points, pricing)
7. Chatbot untuk customer support
8. Sentiment analysis untuk customer feedback

F. INTEGRATIONS:
1. Accounting software (Xero, QuickBooks)
2. Payment gateways (Stripe, PayPal)
3. Shipping providers (JNE, TIKI, SiCepat)
4. E-commerce (Shopify, WooCommerce)
5. CRM (Salesforce, HubSpot)
6. Email marketing (Mailchimp, SendGrid)
7. BI tools (Power BI, Tableau)
8. IoT devices (sensors, smart scales)

G. USER EXPERIENCE:
1. Dark mode toggle
2. Customizable dashboard widgets
3. Keyboard shortcuts
4. Bulk actions dengan confirmation
5. Advanced filters dengan save
6. Column customization (show/hide, reorder)
7. Quick search (Cmd+K / Ctrl+K)
8. Onboarding wizard untuk new users
9. Contextual help tooltips
10. Accessibility compliance (WCAG 2.1)
```

---

#### 12. Performance Optimization Needed
**Priority**: P2  
**Category**: Backend/Database  

```
Optimasi performance untuk scalability:

DATABASE:
1. Add missing indexes (check slow query log)
2. Implement query caching untuk frequently accessed data
3. Use eager loading to prevent N+1 queries
4. Partition large tables (audit_logs, activity_logs)
5. Archive old data (completed orders, old invoices)
6. Database connection pooling
7. Read replicas untuk reporting queries

BACKEND:
1. Implement response caching (Cache::remember)
2. Queue heavy operations (report generation, email sending)
3. Use Laravel Octane untuk faster request handling
4. Optimize service layer (reduce duplicate queries)
5. Implement pagination untuk large datasets
6. Use chunk processing untuk bulk operations
7. Lazy loading untuk relationships

FRONTEND:
1. Lazy load images dan components
2. Implement virtual scrolling untuk long lists
3. Debounce search inputs
4. Minimize JavaScript bundle size
5. Use CDN untuk static assets
6. Implement service worker caching
7. Code splitting per route

MONITORING:
1. Laravel Telescope untuk debugging
2. Sentry untuk error tracking
3. New Relic/Datadog untuk APM
4. Custom metrics dashboard
5. Alert thresholds (response time > 2s, error rate > 1%)
```

---

#### 13. Security Enhancements
**Priority**: P2  
**Category**: Security  

```
Implement security best practices:

AUTHENTICATION:
1. Rate limiting untuk login attempts
2. Account lockout setelah 5 failed attempts
3. Password complexity requirements
4. Password history (prevent reuse)
5. Session management (concurrent sessions limit)
6. Auto-logout setelah inactivity (15 min)

AUTHORIZATION:
1. Role-based access control (RBAC) refinement
2. Field-level permissions
3. Data ownership validation
4. API scope-based access
5. Time-based access (business hours only)

DATA PROTECTION:
1. Encrypt sensitive fields (PII, financial data)
2. Implement data masking untuk display
3. Audit trail untuk data access
4. Data retention policies
5. Secure file upload (type/size validation)
6. SQL injection prevention (parameterized queries)
7. XSS protection (Blade auto-escaping)
8. CSRF token validation

COMPLIANCE:
1. GDPR compliance (data export, deletion)
2. PDP Indonesia compliance
3. HIPAA untuk healthcare data
4. PCI DSS untuk payment data
5. Regular security audit
6. Penetration testing
7. Vulnerability scanning
```

---

#### 14. Testing Coverage Gaps
**Priority**: P2  
**Category**: Testing/QA  

```
Implement comprehensive testing strategy:

UNIT TESTS:
1. Service layer tests (business logic)
2. Model tests (accessors, mutators, scopes)
3. Helper function tests
4. Validation rule tests

Target: 80% code coverage

FEATURE TESTS:
1. API endpoint tests (all CRUD operations)
2. Authentication & authorization tests
3. Workflow tests (multi-step processes)
4. Integration tests (third-party APIs)

Target: All critical paths tested

INTEGRATION TESTS:
1. Payment gateway integration
2. Marketplace sync
3. Email/SMS delivery
4. File upload/download

E2E TESTS:
1. User login flow
2. Order creation flow
3. Invoice payment flow
4. Report generation flow

Tools:
- PHPUnit untuk unit/feature tests
- Pest PHP untuk elegant testing syntax
- Laravel Dusk untuk browser tests
- Mockery untuk mocking dependencies

CI/CD:
- Run tests on every pull request
- Code coverage reporting
- Block merge if coverage < threshold
```

---

#### 15. Documentation Needs
**Priority**: P2  
**Category**: Documentation  

```
Create comprehensive documentation:

DEVELOPER DOCS:
1. Architecture overview
2. Module structure
3. Database schema diagrams
4. API documentation (OpenAPI/Swagger)
5. Deployment guide
6. Local development setup
7. Coding standards
8. Git workflow

USER MANUALS:
1. Getting started guide
2. Module-specific guides (per industry)
3. Video tutorials
4. FAQ database
5. Troubleshooting guide
6. Best practices

ADMIN DOCS:
1. System configuration
2. User management
3. Backup & restore procedures
4. Monitoring & alerting
5. Performance tuning
6. Security hardening

Tools:
- Swagger/OpenAPI untuk API docs
- MkDocs atau VuePress untuk documentation site
- Draw.io untuk diagrams
- Loom untuk video tutorials
```

---

## 📋 DATABASE TABLES AUDIT

### Tables Yang Perlu Diverifikasi:

```
Jalankan audit berikut untuk memastikan database consistency:

1. CHECK TABLE EXISTENCE:
php artisan tinker
Schema::hasTable('table_name')

Tables to verify (sample):
- lab_orders, lab_results, lab_samples
- radiology_exams, radiology_images, pacs_studies
- surgery_schedules, surgery_teams, operating_rooms
- teleconsultations, telemedicine_prescriptions
- admissions, wards, beds
- prescriptions, prescription_items
- diagnoses, icd10_codes
- medical_equipment, equipment_maintenance_logs

2. CHECK FOREIGN KEY INTEGRITY:
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE REFERENCED_TABLE_SCHEMA = 'your_database'
AND REFERENCED_TABLE_NAME IS NULL;

3. CHECK MISSING INDEXES:
EXPLAIN SELECT * FROM table_name WHERE condition;
Look for "type: ALL" (full table scan)

4. CHECK DATA CONSISTENCY:
- Orphaned records (FK pointing to non-existent parent)
- Duplicate entries (should be unique)
- Invalid enum values
- Null values in required fields
```

---

## 🎯 DEVELOPMENT ROADMAP

### Phase 1: Critical Fixes (Week 1-2)
```
Priority: P0 items
1. Fix migration issues
2. Add missing foreign keys
3. Fix cache invalidation
4. Complete healthcare APIs
5. Test offline mode thoroughly

Deliverables:
- All migrations run successfully
- Database integrity verified
- No critical errors in logs
- Healthcare API 100% functional
```

### Phase 2: Feature Completion (Week 3-6)
```
Priority: P1 items
1. Complete hotel front office
2. Redesign EMR UI/UX
3. Implement quality control
4. Add telemedicine video
5. Fill missing controller methods

Deliverables:
- All defined routes working
- UI/UX improvements deployed
- Business logic complete
- Integration tested
```

### Phase 3: Enhancements (Week 7-10)
```
Priority: P2 items
1. Add missing features (reporting, notifications, etc.)
2. Performance optimization
3. Security hardening
4. Testing coverage
5. Documentation

Deliverables:
- Feature complete per module
- Performance metrics met
- Security audit passed
- 80%+ test coverage
- Documentation published
```

### Phase 4: Polish & Launch (Week 11-12)
```
Priority: P3 items
1. UI/UX refinements
2. Bug fixes dari testing
3. User acceptance testing
4. Production deployment prep
5. Training materials

Deliverables:
- Production-ready system
- User manuals complete
- Training sessions done
- Go-live checklist complete
```

---

## 🧪 TESTING CHECKLIST

```
Before deploying any fix/feature, verify:

BACKEND:
[ ] Migration runs successfully (fresh + rollback)
[ ] Seeders populate test data correctly
[ ] All routes respond with correct status codes
[ ] Validation rules work (valid & invalid data)
[ ] Authorization checks prevent unauthorized access
[ ] Error handling returns proper responses
[ ] Database queries are optimized (no N+1)
[ ] Cache invalidation works correctly
[ ] Queue jobs process successfully
[ ] Event listeners trigger correctly

FRONTEND:
[ ] All components render correctly
[ ] Forms validate client-side
[ ] API calls handle errors gracefully
[ ] Loading states display properly
[ ] Empty states handled
[ ] Mobile responsive
[ ] Cross-browser compatibility
[ ] Accessibility (keyboard navigation, screen readers)
[ ] Performance (no layout shifts, fast load)

INTEGRATION:
[ ] Third-party APIs respond correctly
[ ] Webhooks received and processed
[ ] File uploads work (all types/sizes)
[ ] Email/SMS sent successfully
[ ] Payment processing complete flow
[ ] Offline mode sync works

DATA:
[ ] Data integrity maintained
[ ] No orphaned records
[ ] Audit trail complete
[ ] Backup/restore tested
[ ] Data export/import verified
```

---

## 📊 MONITORING & ALERTS

```
Setup monitoring untuk production:

ERROR TRACKING:
- Sentry/Rollbar untuk error monitoring
- Real-time alerting untuk critical errors
- Error grouping dan prioritization
- User impact analysis

PERFORMANCE:
- Response time tracking (p50, p95, p99)
- Database query performance
- Cache hit/miss ratio
- Queue processing time
- Memory usage

BUSINESS METRICS:
- Daily active users
- Transactions per day
- Revenue tracking
- User adoption per module
- Feature usage analytics

ALERTS:
- Error rate > 1%
- Response time > 2 seconds
- Database connections > 80%
- Disk space < 20%
- Queue backlog > 1000 jobs
```

---

## 🚀 DEPLOYMENT CHECKLIST

```
Pre-deployment:
[ ] All tests passing
[ ] Code review approved
[ ] Database migrations tested
[ ] Rollback plan prepared
[ ] Backup created
[ ] Environment variables configured
[ ] Cache cleared
[ ] Queue workers restarted
[ ] CDN purged
[ ] Monitoring alerts active

Post-deployment:
[ ] Smoke tests passed
[ ] Error logs checked (no new errors)
[ ] Performance metrics normal
[ ] User feedback monitored
[ ] Rollback if critical issues
[ ] Documentation updated
[ ] Team notified
```

---

## 💡 BEST PRACTICES REMINDER

```
CODING STANDARDS:
- PSR-12 untuk PHP
- Laravel best practices
- SOLID principles
- DRY (Don't Repeat Yourself)
- KISS (Keep It Simple, Stupid)
- YAGNI (You Ain't Gonna Need It)

GIT WORKFLOW:
- Feature branches (feature/xyz)
- Descriptive commit messages
- Pull requests dengan description
- Code review required
- Squash merge untuk clean history

SECURITY:
- Never commit .env
- Sanitize all inputs
- Use parameterized queries
- Validate file uploads
- Implement rate limiting
- Use HTTPS everywhere

PERFORMANCE:
- Optimize queries (indexes, eager loading)
- Cache aggressively
- Queue heavy tasks
- Paginate large datasets
- Minimize HTTP requests
- Compress assets
```

---

## 📞 SUPPORT & RESOURCES

```
Dokumentasi Laravel:
- https://laravel.com/docs/11.x
- https://laravel-news.com/

Packages yang digunakan:
- Livewire (real-time components)
- Alpine.js (lightweight JS)
- TailwindCSS (utility-first CSS)
- Vite (build tool)
- Laravel Sanctum (API auth)
- Laravel Queue (background jobs)
- Laravel Telescope (debugging)

External Services:
- Midtrans (payment gateway)
- Xendit (payment gateway)
- Agora (video calls)
- Sentry (error tracking)
- Twilio (SMS/WhatsApp)
```

---

## ✅ FINAL DELIVERABLES CHECKLIST

```
Setelah semua tasks selesai, pastikan:

CODE QUALITY:
[ ] No PHPStan/Psalm errors
[ ] No ESLint errors
[ ] Code coverage > 80%
[ ] No critical security issues
[ ] Performance benchmarks met

DOCUMENTATION:
[ ] API docs updated
[ ] User manuals complete
[ ] Deployment guide current
[ ] Architecture diagrams updated
[ ] Changelog maintained

TESTING:
[ ] All unit tests pass
[ ] All feature tests pass
[ ] All integration tests pass
[ ] E2E tests pass
[ ] Load testing done

DEPLOYMENT:
[ ] Staging environment tested
[ ] Production deployment successful
[ ] Monitoring active
[ ] Backup schedule configured
[ ] Rollback plan tested

USER ACCEPTANCE:
[ ] UAT completed
[ ] Sign-off dari stakeholders
[ ] Training sessions done
[ ] Support team briefed
[ ] Feedback mechanism in place
```

---

**END OF AUDIT DOCUMENT**

Gunakan prompt ini sebagai panduan untuk AI developer dalam melakukan audit, fixing, dan development sistem QalcuityERP secara komprehensif.

Setiap bug fix dan feature development harus:
1. Mengikuti coding standards
2. Include tests
3. Update documentation
4. Pass code review
5. Deploy dengan zero downtime

Good luck! 🚀
