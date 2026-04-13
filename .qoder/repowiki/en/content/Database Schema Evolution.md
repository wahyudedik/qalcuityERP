# Database Schema Evolution

<cite>
**Referenced Files in This Document**
- [database.php](file://config/database.php)
- [migration.php](file://config/migration.php)
- [0001_01_01_000000_create_users_table.php](file://database/migrations/0001_01_01_000000_create_users_table.php)
- [User.php](file://app/Models/User.php)
- [Tenant.php](file://app/Models/Tenant.php)
- [TenantDataMigrationService.php](file://app/Services/TenantDataMigrationService.php)
- [2026_04_06_090000_create_error_handling_tables.php](file://database/migrations/2026_04_06_090000_create_error_handling_tables.php)
- [2026_04_10_050000_add_missing_foreign_key_constraints.php](file://database/migrations/2026_04_10_050000_add_missing_foreign_key_constraints.php)
- [2026_01_01_000024_create_advanced_document_management_tables.php](file://database/migrations/2026_01_01_000024_create_advanced_document_management_tables.php)
- [2026_04_08_1000001_create_telemedicine_tables.php](file://database/migrations/2026_04_08_1000001_create_telemedicine_tables.php)
- [2026_04_10_000001_create_telemedicine_settings.php](file://database/migrations/2026_04_10_000001_create_telemedicine_settings.php)
- [2026_04_08_1900001_create_telemedicine_resource_inventory_tables.php](file://database/migrations/2026_04_08_1900001_create_telemedicine_resource_inventory_tables.php)
- [2026_04_06_120000_create_multi_company_tables.php](file://database/migrations/2026_04_06_120000_create_multi_company_tables.php)
- [TelemedicineSetting.php](file://app/Models/TelemedicineSetting.php)
- [NotificationPreference.php](file://app/Models/NotificationPreference.php)
- [Department.php](file://app/Models/Department.php)
- [CompanyGroup.php](file://app/Models/CompanyGroup.php)
- [MIGRATION_AUDIT_REPORT.md](file://MIGRATION_AUDIT_REPORT.md)
- [TASK_LIST_DETAILED.md](file://TASK_LIST_DETAILED.md)
</cite>

## Update Summary
**Changes Made**
- Added comprehensive document management system with versioning, approval workflows, and cloud storage integration
- Integrated advanced telemedicine infrastructure including consultation management, recording systems, prescription workflows, and comprehensive resource inventory tracking
- Implemented multi-company consolidation and inter-entity transaction management
- Enhanced notification infrastructure with comprehensive preference management and channel support
- Added organizational structure tables for hierarchical department management
- Expanded error handling and recovery mechanisms with automated backup and conflict resolution systems

## Table of Contents
1. [Introduction](#introduction)
2. [Project Structure](#project-structure)
3. [Core Components](#core-components)
4. [Architecture Overview](#architecture-overview)
5. [Detailed Component Analysis](#detailed-component-analysis)
6. [Advanced Schema Extensions](#advanced-schema-extensions)
7. [Dependency Analysis](#dependency-analysis)
8. [Performance Considerations](#performance-considerations)
9. [Troubleshooting Guide](#troubleshooting-guide)
10. [Conclusion](#conclusion)

## Introduction
This document explains how the qalcuityERP codebase manages database schema evolution through Laravel migrations, tenant isolation, and robust error handling mechanisms. The system has been significantly extended to support advanced document management, telemedicine operations, multi-company consolidation, and comprehensive notification infrastructure. It covers the migration configuration, schema definition patterns, tenant-aware models, and operational safeguards for safe schema changes across multiple tenants.

## Project Structure
The schema evolution system centers around four pillars:
- Configuration: Database connections and migration optimization settings
- Migrations: Versioned schema definitions and corrective adjustments
- Models: Tenant-aware Eloquent models that enforce multi-tenancy constraints
- Extended Services: Advanced document management, telemedicine, and multi-company operations

```mermaid
graph TB
ConfigDB["config/database.php<br/>Database connections"]
ConfigMigration["config/migration.php<br/>Migration optimization"]
Migrations["database/migrations/*.php<br/>Schema definitions"]
Models["app/Models/*.php<br/>Tenant-aware models"]
Services["app/Services/*.php<br/>Migration and error handling"]
AdvancedSchemas["Extended Schema Components<br/>Document Management<br/>Telemedicine<br/>Multi-Company<br/>Notification Infrastructure<br/>Resource Inventory<br/>Error Handling"]
ConfigDB --> Migrations
ConfigMigration --> Migrations
Migrations --> Models
Models --> Services
Services --> AdvancedSchemas
```

**Diagram sources**
- [database.php:1-185](file://config/database.php#L1-L185)
- [migration.php:1-24](file://config/migration.php#L1-L24)
- [0001_01_01_000000_create_users_table.php:1-52](file://database/migrations/0001_01_01_000000_create_users_table.php#L1-L52)
- [User.php:15-280](file://app/Models/User.php#L15-L280)
- [Tenant.php:10-223](file://app/Models/Tenant.php#L10-L223)
- [TenantDataMigrationService.php:301-350](file://app/Services/TenantDataMigrationService.php#L301-L350)
- [2026_01_01_000024_create_advanced_document_management_tables.php:1-187](file://database/migrations/2026_01_01_000024_create_advanced_document_management_tables.php#L1-L187)
- [2026_04_08_1000001_create_telemedicine_tables.php:1-266](file://database/migrations/2026_04_08_1000001_create_telemedicine_tables.php#L1-L266)

**Section sources**
- [database.php:1-185](file://config/database.php#L1-L185)
- [migration.php:1-24](file://config/migration.php#L1-L24)

## Core Components
- Database configuration supports SQLite, MySQL/MariaDB, PostgreSQL, and SQL Server drivers with dedicated connection settings and Redis options.
- Migration optimization settings enable foreign key toggles, single-transaction execution, and batch sizing to improve development performance.
- Initial migrations define foundational tables including users, password reset tokens, sessions, and tenant relationships.
- Tenant-aware models encapsulate multi-tenancy constraints and relationship patterns.

**Section sources**
- [database.php:33-117](file://config/database.php#L33-L117)
- [migration.php:14-22](file://config/migration.php#L14-L22)
- [0001_01_01_000000_create_users_table.php:11-50](file://database/migrations/0001_01_01_000000_create_users_table.php#L11-L50)
- [User.php:19-57](file://app/Models/User.php#L19-L57)
- [Tenant.php:13-57](file://app/Models/Tenant.php#L13-L57)

## Architecture Overview
The schema evolution architecture integrates configuration-driven database connectivity, versioned migrations, and tenant-aware models. The system now includes comprehensive operational safeguards including error handling tables, restore points, conflict resolution, foreign key constraint enforcement, and extensive schema extensions for document management, telemedicine, multi-company operations, and notification infrastructure.

```mermaid
graph TB
subgraph "Configuration Layer"
DBConn["Database Connections"]
MigOpt["Migration Optimization"]
end
subgraph "Schema Layer"
Users["users table"]
PasswordReset["password_reset_tokens table"]
Sessions["sessions table"]
ErrorHandling["Error handling tables"]
RestorePoints["Restore points"]
EditConflicts["Edit conflicts"]
DocumentMgmt["Document Management Tables"]
Telemedicine["Telemedicine Tables"]
TeleResource["Telemedicine Resource Inventory"]
MultiCompany["Multi-Company Tables"]
NotificationInfra["Notification Infrastructure"]
end
subgraph "Domain Layer"
TenantModel["Tenant model"]
UserModel["User model"]
MigrationService["TenantDataMigrationService"]
DocumentModel["Document models"]
TelemedicineModel["Telemedicine models"]
TeleResourceModel["Telemedicine resource models"]
MultiCompanyModel["Multi-company models"]
NotificationModel["Notification models"]
end
DBConn --> Users
MigOpt --> Users
Users --> UserModel
TenantModel --> UserModel
ErrorHandling --> MigrationService
RestorePoints --> MigrationService
EditConflicts --> MigrationService
DocumentMgmt --> DocumentModel
Telemedicine --> TelemedicineModel
TeleResource --> TeleResourceModel
MultiCompany --> MultiCompanyModel
NotificationInfra --> NotificationModel
```

**Diagram sources**
- [database.php:33-117](file://config/database.php#L33-L117)
- [migration.php:14-22](file://config/migration.php#L14-L22)
- [0001_01_01_000000_create_users_table.php:13-39](file://database/migrations/0001_01_01_000000_create_users_table.php#L13-L39)
- [2026_04_06_090000_create_error_handling_tables.php:52-80](file://database/migrations/2026_04_06_090000_create_error_handling_tables.php#L52-L80)
- [2026_01_01_000024_create_advanced_document_management_tables.php:43-147](file://database/migrations/2026_01_01_000024_create_advanced_document_management_tables.php#L43-L147)
- [2026_04_08_1000001_create_telemedicine_tables.php:20-251](file://database/migrations/2026_04_08_1000001_create_telemedicine_tables.php#L20-251)
- [2026_04_08_1900001_create_telemedicine_resource_inventory_tables.php:60-148](file://database/migrations/2026_04_08_1900001_create_telemedicine_resource_inventory_tables.php#L60-L148)
- [2026_04_06_120000_create_multi_company_tables.php:13-234](file://database/migrations/2026_04_06_120000_create_multi_company_tables.php#L13-234)
- [Tenant.php:77-90](file://app/Models/Tenant.php#L77-L90)
- [User.php:61-64](file://app/Models/User.php#L61-L64)
- [TenantDataMigrationService.php:301-350](file://app/Services/TenantDataMigrationService.php#L301-L350)

## Detailed Component Analysis

### Database Configuration
- Supports multiple drivers with environment-based overrides for host, port, database name, credentials, charset, collation, and SSL options.
- Defines migration repository table and update behavior for published migrations.
- Includes Redis client configuration for caching and queues.

**Section sources**
- [database.php:20-117](file://config/database.php#L20-L117)
- [database.php:130-133](file://config/database.php#L130-L133)
- [database.php:146-182](file://config/database.php#L146-L182)

### Migration Optimization Settings
- Disables foreign key checks during migrations for faster execution when enabled.
- Allows single-transaction mode for all migrations when feasible.
- Configurable batch size for controlled execution during development.

**Section sources**
- [migration.php:14-22](file://config/migration.php#L14-L22)

### Initial Schema Definitions
- Creates users table with tenant foreign key, role enumeration, and activity flag.
- Adds password reset tokens and sessions tables for authentication lifecycle.
- Establishes tenant relationship via tenant_id on users.

```mermaid
erDiagram
TENANT {
int id PK
string name
string slug
boolean is_active
datetime trial_ends_at
datetime plan_expires_at
}
USER {
int id PK
int tenant_id FK
string name
string email UK
string password
enum role
boolean is_active
timestamp email_verified_at
}
TENANT ||--o{ USER : "has many"
```

**Diagram sources**
- [0001_01_01_000000_create_users_table.php:13-24](file://database/migrations/0001_01_01_000000_create_users_table.php#L13-L24)
- [Tenant.php:13-47](file://app/Models/Tenant.php#L13-L47)
- [User.php:19-40](file://app/Models/User.php#L19-L40)

**Section sources**
- [0001_01_01_000000_create_users_table.php:11-50](file://database/migrations/0001_01_01_000000_create_users_table.php#L11-L50)
- [User.php:61-64](file://app/Models/User.php#L61-L64)
- [Tenant.php:77-80](file://app/Models/Tenant.php#L77-L80)

### Tenant-Aware Models
- User model defines fillable attributes, hidden fields, casting rules, and tenant relationship.
- Tenant model manages module visibility, subscription status, and business context helpers.

**Section sources**
- [User.php:19-57](file://app/Models/User.php#L19-L57)
- [User.php:61-64](file://app/Models/User.php#L61-L64)
- [Tenant.php:13-57](file://app/Models/Tenant.php#L13-L57)
- [Tenant.php:64-75](file://app/Models/Tenant.php#L64-L75)

### Error Handling and Restore Points
- Error handling tables capture migration failures, backup types, statuses, and timestamps with tenant scoping.
- Restore points table stores snapshots and triggers for safe rollbacks before major changes.
- Edit conflicts table tracks multi-user editing conflicts requiring resolution.
- Enhanced error logs provide actionable solutions and recovery queue for failed operations.

```mermaid
erDiagram
ACTION_LOGS {
int id PK
int tenant_id FK
int user_id FK
string action_type
string model_type
int model_id
json before_state
json after_state
json metadata
boolean can_undo
boolean undone
timestamp undone_at
int undone_by_user_id FK
timestamp expires_at
}
AUTOMATED_BACKUPS {
int id PK
int tenant_id FK
string backup_type
string status
string file_path
string file_size_mb
json tables_included
int records_count
text error_message
timestamp started_at
timestamp completed_at
timestamp expires_at
}
RESTORE_POINTS {
int id PK
int tenant_id FK
int user_id FK
string name
text description
string trigger_event
json affected_models
json snapshot_data
boolean is_active
boolean used
timestamp used_at
timestamp expires_at
}
EDIT_CONFLICTS {
int id PK
int tenant_id FK
string model_type
int model_id
int original_user_id FK
int conflicting_user_id FK
json original_data
json first_user_changes
json second_user_changes
string resolution_strategy
string status
json resolved_data
int resolved_by_user_id FK
text resolution_notes
timestamp detected_at
timestamp resolved_at
}
ERROR_LOGS_ENHANCED {
int id PK
int tenant_id FK
int user_id FK
string error_type
string error_code
text error_message
text stack_trace
string file
int line
json context
json suggested_solutions
string severity
boolean resolved
text resolution_notes
timestamp resolved_at
}
RECOVERY_QUEUE {
int id PK
int tenant_id FK
int user_id FK
string operation_type
json operation_data
string failure_reason
int retry_count
int max_retries
string status
json last_error
timestamp next_retry_at
timestamp completed_at
}
TENANT ||--o{ ACTION_LOGS : "has many"
TENANT ||--o{ AUTOMATED_BACKUPS : "has many"
TENANT ||--o{ RESTORE_POINTS : "has many"
TENANT ||--o{ EDIT_CONFLICTS : "has many"
TENANT ||--o{ ERROR_LOGS_ENHANCED : "has many"
TENANT ||--o{ RECOVERY_QUEUE : "has many"
```

**Diagram sources**
- [2026_04_06_090000_create_error_handling_tables.php:14-144](file://database/migrations/2026_04_06_090000_create_error_handling_tables.php#L14-L144)

**Section sources**
- [2026_04_06_090000_create_error_handling_tables.php:14-160](file://database/migrations/2026_04_06_090000_create_error_handling_tables.php#L14-L160)

### Foreign Key Constraint Enforcement
- Corrective migrations add missing foreign key constraints with defensive error handling.
- Scans existing foreign keys and conditionally adds constraints to prevent referential integrity issues.

```mermaid
flowchart TD
Start(["Migration Execution"]) --> ScanTables["Scan target tables for missing FKs"]
ScanTables --> CheckFKs{"Existing FKs present?"}
CheckFKs --> |Yes| NextTable["Move to next table"]
CheckFKs --> |No| AttemptAdd["Attempt to add FK constraint"]
AttemptAdd --> AddSuccess{"Addition successful?"}
AddSuccess --> |Yes| NextTable
AddSuccess --> |No| LogWarning["Log warning and continue"]
LogWarning --> NextTable
NextTable --> MoreTables{"More tables to process?"}
MoreTables --> |Yes| ScanTables
MoreTables --> |No| End(["Migration Complete"])
```

**Diagram sources**
- [2026_04_10_050000_add_missing_foreign_key_constraints.php:78-108](file://database/migrations/2026_04_10_050000_add_missing_foreign_key_constraints.php#L78-L108)

**Section sources**
- [2026_04_10_050000_add_missing_foreign_key_constraints.php:78-108](file://database/migrations/2026_04_10_050000_add_missing_foreign_key_constraints.php#L78-L108)

### Tenant Data Migration Service
- Provides utilities for moving, merging, and deleting tenant data while reassigning foreign key references.
- Includes placeholder logic for discovering references and reassigning them across tables.

```mermaid
sequenceDiagram
participant Caller as "Caller"
participant Service as "TenantDataMigrationService"
participant DB as "Database"
Caller->>Service : splitTenant(sourceTenantId, splitConfig)
Service->>Service : moveRecords()
Service->>Service : mergeRecords()
Service->>Service : deleteRecords()
Service->>DB : reassignReferences(modelClass, oldId, newId)
DB-->>Service : Affected rows count
Service-->>Caller : Migration summary
```

**Diagram sources**
- [TenantDataMigrationService.php:301-350](file://app/Services/TenantDataMigrationService.php#L301-L350)

**Section sources**
- [TenantDataMigrationService.php:301-350](file://app/Services/TenantDataMigrationService.php#L301-L350)

### Migration Audit and Testing
- Migration audit report documents fixes, indexes, foreign keys, and testing procedures.
- Task list confirms deliverables including fixed migrations, performance indexes, foreign key constraints, and migration test documentation.

**Section sources**
- [MIGRATION_AUDIT_REPORT.md:1-200](file://MIGRATION_AUDIT_REPORT.md#L1-L200)
- [TASK_LIST_DETAILED.md:76-87](file://TASK_LIST_DETAILED.md#L76-L87)

## Advanced Schema Extensions

### Document Management System
The system now includes comprehensive document management capabilities with versioning, approval workflows, and cloud storage integration.

```mermaid
erDiagram
DOCUMENTS {
int id PK
int tenant_id FK
string title
text content
string tags
int version
int parent_id FK
string status
int approved_by FK
timestamp approved_at
text approval_notes
timestamp expires_at
timestamp archived_at
string storage_provider
string storage_bucket
text ocr_text
boolean has_ocr
string digital_signature
boolean is_signed
timestamp signed_at
}
DOCUMENT_VERSIONS {
int id PK
int document_id FK
int version
string file_name
string file_path
bigint file_size
int changed_by FK
text change_summary
timestamp created_at
timestamp updated_at
}
DOCUMENT_APPROVAL_WORKFLOWS {
int id PK
int tenant_id FK
string name
text description
string document_type
json approval_steps
boolean is_active
timestamp created_at
timestamp updated_at
}
DOCUMENT_APPROVAL_REQUESTS {
int id PK
int document_id FK
int workflow_id FK
int step_number
int approver_id FK
string approver_role
string status
text comments
timestamp actioned_at
timestamp created_at
timestamp updated_at
}
TENANT_STORAGE_CONFIGS {
int id PK
int tenant_id FK
string provider
string bucket_name
string region
string access_key
string secret_key
string endpoint
json additional_config
boolean is_active
boolean is_default
timestamp created_at
timestamp updated_at
}
DOCUMENT_SIGNATURES {
int id PK
int document_id FK
int signer_id FK
string signature_type
string signature_hash
string certificate_serial
text signature_metadata
string ip_address
string user_agent
timestamp signed_at
timestamp created_at
timestamp updated_at
}
DOCUMENTS ||--o{ DOCUMENT_VERSIONS : "has many"
DOCUMENTS ||--o{ DOCUMENT_APPROVAL_REQUESTS : "has many"
DOCUMENTS ||--o{ DOCUMENT_SIGNATURES : "has many"
DOCUMENT_APPROVAL_WORKFLOWS ||--o{ DOCUMENT_APPROVAL_REQUESTS : "has many"
```

**Diagram sources**
- [2026_01_01_000024_create_advanced_document_management_tables.php:13-147](file://database/migrations/2026_01_01_000024_create_advanced_document_management_tables.php#L13-L147)

**Section sources**
- [2026_01_01_000024_create_advanced_document_management_tables.php:11-187](file://database/migrations/2026_01_01_000024_create_advanced_document_management_tables.php#L11-L187)

### Telemedicine Infrastructure
Advanced telemedicine capabilities include consultation management, recording systems, prescription workflows, comprehensive resource inventory tracking, and detailed operational analytics.

```mermaid
erDiagram
TELEMEDICINE_SETTINGS {
int id PK
int tenant_id FK
string jitsi_server_url
string jitsi_app_id
string jitsi_secret
boolean enable_recording
string recording_storage_path
boolean enable_waiting_room
boolean enable_chat
boolean enable_screen_share
boolean reminder_enabled
int reminder_minutes_before
boolean send_email_reminder
boolean send_sms_reminder
boolean enable_feedback
boolean require_feedback
int consultation_timeout_minutes
int max_participants
boolean allow_group_consultation
string custom_logo_url
string welcome_message
}
TELECONSULTATIONS {
int id PK
int patient_id FK
int doctor_id
int visit_id FK
string consultation_number UK
date consultation_date
datetime scheduled_time
datetime actual_start_time
datetime actual_end_time
int scheduled_duration
int actual_duration
enum platform
enum consultation_type
string meeting_id
string meeting_url
string meeting_password
text meeting_details
enum status
text chief_complaint
text medical_history
text diagnosis
string icd10_code
text treatment_plan
text doctor_notes
decimal consultation_fee
decimal discount
decimal total_amount
enum payment_status
datetime paid_at
text cancellation_reason
int cancelled_by FK
datetime cancelled_at
int rescheduled_to FK
text reschedule_reason
text notes
timestamp created_at
timestamp updated_at
}
TELECONSULTATION_RECORDINGS {
int id PK
int teleconsultation_id FK
int patient_id FK
int doctor_id
string recording_url
string file_path
bigint file_size
int duration_seconds
string recording_format
timestamp recording_started_at
timestamp recording_ended_at
boolean is_encrypted
int retention_days
timestamp expires_at
timestamp created_at
timestamp updated_at
}
TELECONSULTATION_PAYMENTS {
int id PK
int teleconsultation_id FK
int patient_id FK
string payment_number UK
decimal consultation_fee
decimal platform_fee
decimal discount_amount
decimal total_amount
decimal amount_paid
decimal balance_due
enum payment_method
string payment_gateway
string transaction_id
enum payment_status
timestamp paid_at
text payment_notes
timestamp created_at
timestamp updated_at
}
TELECONSULTATION_FEEDBACKS {
int id PK
int teleconsultation_id FK
int patient_id FK
int doctor_id
int overall_rating
int doctor_rating
int video_quality_rating
int audio_quality_rating
int app_ease_rating
text comments
text suggestions
boolean would_recommend
enum consultation_outcome
timestamp created_at
timestamp updated_at
}
TELECONSULTATION_FEEDBACKS ||--o| TELEMEDICINE_SETTINGS : "configures"
TELECONSULTATIONS ||--o{ TELECONSULTATION_RECORDINGS : "has many"
TELECONSULTATIONS ||--o{ TELECONSULTATION_PAYMENTS : "has many"
TELECONSULTATIONS ||--o| TELECONSULTATION_FEEDBACKS : "has one"
```

**Diagram sources**
- [2026_04_10_000001_create_telemedicine_settings.php:13-49](file://database/migrations/2026_04_10_000001_create_telemedicine_settings.php#L13-L49)
- [2026_04_08_1000001_create_telemedicine_tables.php:20-251](file://database/migrations/2026_04_08_1000001_create_telemedicine_tables.php#L20-251)

**Section sources**
- [2026_04_10_000001_create_telemedicine_settings.php:11-61](file://database/migrations/2026_04_10_000001_create_telemedicine_settings.php#L11-L61)
- [2026_04_08_1000001_create_telemedicine_tables.php:11-266](file://database/migrations/2026_04_08_1000001_create_telemedicine_tables.php#L11-L266)

### Telemedicine Resource Inventory
Comprehensive telemedicine resource inventory tracking includes equipment management, supply tracking, maintenance scheduling, and utilization analytics.

```mermaid
erDiagram
MEDICAL_EQUIPMENT {
int id PK
string equipment_code UK
string equipment_name
enum equipment_type
string category
string manufacturer
string model_number
string serial_number
date purchase_date
decimal purchase_cost
date warranty_expiry
enum status
string location
int department_id FK
date next_maintenance_date
date last_maintenance_date
date calibration_date
date next_calibration_date
boolean requires_calibration
int maintenance_interval_days
text specifications
text notes
timestamp created_at
timestamp updated_at
}
EQUIPMENT_MAINTENANCE_LOGS {
int id PK
int equipment_id FK
enum maintenance_type
date maintenance_date
time start_time
time end_time
int technician_id FK
text work_performed
text parts_replaced
decimal parts_cost
decimal labor_cost
decimal total_cost
enum status
text findings
text recommendations
date next_maintenance_date
timestamp created_at
timestamp updated_at
}
SURGERY_SCHEDULES {
int id PK
string schedule_number UK
int patient_id FK
int admission_id FK
int primary_surgeon_id FK
int assistant_surgeon_id FK
int anesthesiologist_id FK
int operating_room_id FK
date surgery_date
time scheduled_start_time
time scheduled_end_time
time actual_start_time
time actual_end_time
int estimated_duration_minutes
int actual_duration_minutes
string surgery_name
text surgery_description
string icd9_code
string icd10_code
enum urgency
enum status
text pre_op_diagnosis
text post_op_diagnosis
text surgery_notes
text complications
timestamp created_at
timestamp updated_at
}
SURGERY_TEAMS {
int id PK
int surgery_schedule_id FK
int doctor_id FK
enum role
text responsibilities
timestamp check_in_time
timestamp check_out_time
timestamp created_at
timestamp updated_at
}
MEDICAL_SUPPLY_TRANSACTIONS {
int id PK
string transaction_number UK
int supply_id FK
int created_by FK
date transaction_date
enum transaction_type
int quantity
int previous_quantity
int new_quantity
string reference_number
int from_department_id FK
int to_department_id FK
string batch_number
date expiry_date
decimal unit_cost
decimal total_cost
text notes
timestamp created_at
timestamp updated_at
}
MEDICAL_SUPPLY_REQUESTS {
int id PK
string request_number UK
int requested_by FK
int department_id FK
enum urgency
enum status
date request_date
date required_by_date
int approved_by FK
timestamp approved_at
text approval_notes
int fulfilled_by FK
timestamp fulfilled_at
text notes
timestamp created_at
timestamp updated_at
}
STERILIZATION_LOGS {
int id PK
string sterilization_number UK
int equipment_id FK
string equipment_name
enum sterilization_method
date sterilization_date
time start_time
time end_time
int duration_minutes
decimal temperature
decimal pressure
string cycle_number
string batch_number
string load_description
int items_count
int performed_by FK
int validated_by FK
enum status
text biological_indicator_result
text chemical_indicator_result
boolean passed_validation
text notes
timestamp created_at
timestamp updated_at
}
MEDICAL_WASTE_LOGS {
int id PK
string waste_number UK
enum waste_type
string waste_description
decimal weight_kg
int container_count
string container_type
string color_code
int generated_by_department FK
int recorded_by FK
date generation_date
string storage_location
date disposal_date
string disposal_method
string disposal_contractor
string manifest_number
boolean is_compliant
text notes
timestamp created_at
timestamp updated_at
}
MEDICAL_EQUIPMENT ||--o{ EQUIPMENT_MAINTENANCE_LOGS : "has many"
SURGERY_SCHEDULES ||--o{ SURGERY_TEAMS : "has many"
MEDICAL_SUPPLY_TRANSACTIONS ||--o{ MEDICAL_SUPPLY_REQUESTS : "triggers"
```

**Diagram sources**
- [2026_04_08_1900001_create_telemedicine_resource_inventory_tables.php:198-391](file://database/migrations/2026_04_08_1900001_create_telemedicine_resource_inventory_tables.php#L198-L391)

**Section sources**
- [2026_04_08_1900001_create_telemedicine_resource_inventory_tables.php:1-445](file://database/migrations/2026_04_08_1900001_create_telemedicine_resource_inventory_tables.php#L1-L445)

### Multi-Company Consolidation
Comprehensive multi-company infrastructure supports inter-entity transactions, consolidated reporting, shared services, and inventory transfers.

```mermaid
erDiagram
COMPANY_GROUPS {
int id PK
string name
string code UK
text description
int parent_tenant_id FK
boolean is_active
json settings
timestamp created_at
timestamp updated_at
}
TENANT_GROUP_MEMBERS {
int id PK
int company_group_id FK
int tenant_id FK
string ownership_percentage
date joined_date
date exited_date
boolean is_active
string role
timestamp created_at
timestamp updated_at
}
INTER_COMPANY_TRANSACTIONS {
int id PK
int company_group_id FK
int from_tenant_id FK
int to_tenant_id FK
string transaction_type
string reference_type
int reference_id
decimal amount
string currency
decimal exchange_rate
date transaction_date
date due_date
string status
text description
json line_items
int created_by_user_id FK
int approved_by_user_id FK
timestamp approved_at
text rejection_reason
timestamp created_at
timestamp updated_at
}
INTER_COMPANY_ACCOUNTS {
int id PK
int company_group_id FK
int tenant_id FK
int counterparty_tenant_id FK
string account_type
decimal balance
string currency
date last_reconciliation_date
timestamp created_at
timestamp updated_at
}
CONSOLIDATED_REPORTS {
int id PK
int company_group_id FK
string report_type
date period_start
date period_end
string currency
json report_data
json elimination_entries
json subsidiary_contributions
string status
int prepared_by_user_id FK
int approved_by_user_id FK
timestamp approved_at
text notes
timestamp created_at
timestamp updated_at
}
SHARED_SERVICES {
int id PK
int company_group_id FK
int provider_tenant_id FK
string service_name
text description
string billing_method
decimal fixed_fee
json allocation_rules
boolean is_active
timestamp created_at
timestamp updated_at
}
SHARED_SERVICE_SUBSCRIPTIONS {
int id PK
int shared_service_id FK
int subscriber_tenant_id FK
decimal allocation_percentage
date start_date
date end_date
boolean is_active
timestamp created_at
timestamp updated_at
}
SHARED_SERVICE_BILLINGS {
int id PK
int shared_service_id FK
int subscriber_tenant_id FK
date billing_period_start
date billing_period_end
decimal amount
string currency
string status
int invoice_id
text calculation_details
timestamp created_at
timestamp updated_at
}
INVENTORY_TRANSFERS {
int id PK
int company_group_id FK
int from_tenant_id FK
int to_tenant_id FK
string transfer_number UK
date transfer_date
date expected_arrival_date
date actual_arrival_date
string status
string shipping_method
string tracking_number
decimal shipping_cost
text notes
int created_by_user_id FK
int received_by_user_id FK
timestamp created_at
timestamp updated_at
}
INVENTORY_TRANSFER_ITEMS {
int id PK
int inventory_transfer_id FK
int product_id FK
int quantity_requested
int quantity_sent
int quantity_received
decimal unit_cost
string batch_number
date expiry_date
text notes
timestamp created_at
timestamp updated_at
}
ELIMINATION_ENTRIES {
int id PK
int consolidated_report_id FK
string entry_type
int from_tenant_id FK
int to_tenant_id FK
decimal amount
text description
json original_transactions
timestamp created_at
timestamp updated_at
}
COMPANY_GROUPS ||--o{ TENANT_GROUP_MEMBERS : "has many"
COMPANY_GROUPS ||--o{ INTER_COMPANY_TRANSACTIONS : "has many"
COMPANY_GROUPS ||--o{ INTER_COMPANY_ACCOUNTS : "has many"
COMPANY_GROUPS ||--o{ CONSOLIDATED_REPORTS : "has many"
COMPANY_GROUPS ||--o{ SHARED_SERVICES : "has many"
SHARED_SERVICES ||--o{ SHARED_SERVICE_SUBSCRIPTIONS : "has many"
SHARED_SERVICES ||--o{ SHARED_SERVICE_BILLINGS : "has many"
COMPANY_GROUPS ||--o{ INVENTORY_TRANSFERS : "has many"
INVENTORY_TRANSFERS ||--o{ INVENTORY_TRANSFER_ITEMS : "has many"
CONSOLIDATED_REPORTS ||--o{ ELIMINATION_ENTRIES : "has many"
```

**Diagram sources**
- [2026_04_06_120000_create_multi_company_tables.php:13-234](file://database/migrations/2026_04_06_120000_create_multi_company_tables.php#L13-234)

**Section sources**
- [2026_04_06_120000_create_multi_company_tables.php:11-255](file://database/migrations/2026_04_06_120000_create_multi_company_tables.php#L11-L255)

### Notification Infrastructure
Enhanced notification system with comprehensive preference management, multi-channel support, and module-specific configurations.

```mermaid
erDiagram
NOTIFICATION_PREFERENCES {
int id PK
int user_id FK
string notification_type
boolean in_app
boolean email
boolean push
boolean whatsapp
string digest_frequency
time quiet_hours_start
time quiet_hours_end
boolean is_dnd
json module_preferences
timestamp created_at
timestamp updated_at
}
USERS {
int id PK
int tenant_id FK
string name
string email
string password
enum role
boolean is_active
timestamp email_verified_at
}
NOTIFICATION_PREFERENCES ||--o{ USERS : "belongs to"
NOTIFICATION_TYPES {
string module
string type
string localized_name
}
PREFERENCES ||--o{ NOTIFICATION_TYPES : "configures"
```

**Diagram sources**
- [NotificationPreference.php:10-147](file://app/Models/NotificationPreference.php#L10-L147)

**Section sources**
- [NotificationPreference.php:8-148](file://app/Models/NotificationPreference.php#L8-L148)

### Organizational Structure
Hierarchical department management with parent-child relationships, department heads, and multi-type department support.

```mermaid
erDiagram
DEPARTMENTS {
int id PK
int tenant_id FK
string name
string code
text description
string type
int parent_id FK
int head_id FK
string location
string phone
string email
boolean is_active
int sort_order
timestamp created_at
timestamp updated_at
}
USERS {
int id PK
int tenant_id FK
string name
string email
string password
enum role
boolean is_active
timestamp email_verified_at
}
DEPARTMENTS ||--o{ DEPARTMENTS : "has many children"
DEPARTMENTS ||--o{ USERS : "has many doctors"
DEPARTMENTS ||--o{ USERS : "has head"
```

**Diagram sources**
- [Department.php:13-124](file://app/Models/Department.php#L13-L124)

**Section sources**
- [Department.php:9-126](file://app/Models/Department.php#L9-L126)

## Dependency Analysis
The schema evolution system exhibits strong cohesion within configuration, migrations, and models, with clear separation of concerns and extensive new dependencies:
- Configuration depends on environment variables and defines defaults for all supported databases.
- Migrations depend on configuration and apply schema changes consistently across environments.
- Models depend on migrations and enforce tenant isolation and data integrity.
- Services depend on models and migrations to perform safe tenant data operations.
- Extended components depend on core models and introduce new domain-specific relationships.

```mermaid
graph TB
Config["config/database.php"]
MigOpt["config/migration.php"]
MigUsers["migrations/*_create_users_table.php"]
MigFix["migrations/*_add_missing_foreign_key_constraints.php"]
MigErr["migrations/*_create_error_handling_tables.php"]
MigDoc["migrations/*_create_advanced_document_management_tables.php"]
MigTele["migrations/*_create_telemedicine_tables.php"]
MigTeleSettings["migrations/*_create_telemedicine_settings.php"]
MigTeleRes["migrations/*_create_telemedicine_resource_inventory_tables.php"]
MigMulti["migrations/*_create_multi_company_tables.php"]
UserModel["app/Models/User.php"]
TenantModel["app/Models/Tenant.php"]
DocModel["app/Models/* (Document related)"]
TeleModel["app/Models/* (Telemedicine related)"]
TeleResModel["app/Models/* (Telemedicine resource related)"]
MultiModel["app/Models/* (Multi-company related)"]
NotifModel["app/Models/NotificationPreference.php"]
DeptModel["app/Models/Department.php"]
CompanyGroupModel["app/Models/CompanyGroup.php"]
MigService["app/Services/TenantDataMigrationService.php"]
Config --> MigUsers
MigOpt --> MigUsers
MigUsers --> UserModel
MigFix --> UserModel
MigErr --> MigService
TenantModel --> UserModel
MigDoc --> DocModel
MigTele --> TeleModel
MigTeleSettings --> TeleModel
MigTeleRes --> TeleResModel
MigMulti --> MultiModel
NotifModel --> UserModel
DeptModel --> UserModel
CompanyGroupModel --> UserModel
MigService --> UserModel
```

**Diagram sources**
- [database.php:1-185](file://config/database.php#L1-L185)
- [migration.php:1-24](file://config/migration.php#L1-L24)
- [0001_01_01_000000_create_users_table.php:1-52](file://database/migrations/0001_01_01_000000_create_users_table.php#L1-L52)
- [2026_04_10_050000_add_missing_foreign_key_constraints.php:78-108](file://database/migrations/2026_04_10_050000_add_missing_foreign_key_constraints.php#L78-L108)
- [2026_04_06_090000_create_error_handling_tables.php:52-80](file://database/migrations/2026_04_06_090000_create_error_handling_tables.php#L52-L80)
- [2026_01_01_000024_create_advanced_document_management_tables.php:1-187](file://database/migrations/2026_01_01_000024_create_advanced_document_management_tables.php#L1-L187)
- [2026_04_08_1000001_create_telemedicine_tables.php:1-266](file://database/migrations/2026_04_08_1000001_create_telemedicine_tables.php#L1-L266)
- [2026_04_08_1900001_create_telemedicine_resource_inventory_tables.php:1-445](file://database/migrations/2026_04_08_1900001_create_telemedicine_resource_inventory_tables.php#L1-L445)
- [2026_04_06_120000_create_multi_company_tables.php:1-255](file://database/migrations/2026_04_06_120000_create_multi_company_tables.php#L1-L255)
- [User.php:15-280](file://app/Models/User.php#L15-L280)
- [Tenant.php:10-223](file://app/Models/Tenant.php#L10-L223)
- [TelemedicineSetting.php:1-110](file://app/Models/TelemedicineSetting.php#L1-L110)
- [NotificationPreference.php:1-148](file://app/Models/NotificationPreference.php#L1-L148)
- [Department.php:1-126](file://app/Models/Department.php#L1-L126)
- [CompanyGroup.php:1-47](file://app/Models/CompanyGroup.php#L1-L47)
- [TenantDataMigrationService.php:301-350](file://app/Services/TenantDataMigrationService.php#L301-L350)

**Section sources**
- [database.php:1-185](file://config/database.php#L1-L185)
- [migration.php:1-24](file://config/migration.php#L1-L24)
- [0001_01_01_000000_create_users_table.php:1-52](file://database/migrations/0001_01_01_000000_create_users_table.php#L1-L52)
- [User.php:15-280](file://app/Models/User.php#L15-L280)
- [Tenant.php:10-223](file://app/Models/Tenant.php#L10-L223)
- [TenantDataMigrationService.php:301-350](file://app/Services/TenantDataMigrationService.php#L301-L350)

## Performance Considerations
- Enable foreign key disabling during migrations for faster development cycles when appropriate.
- Use single transaction mode to reduce overhead when migrating small batches.
- Set batch size to control memory usage and execution time during large migrations.
- Index tenant_id and frequently queried columns to maintain query performance under multi-tenancy.
- Implement proper indexing strategies for new document management, telemedicine, and multi-company tables.
- Consider partitioning strategies for large historical datasets in telemedicine and document management systems.
- Optimize cloud storage integration with appropriate caching and CDN configurations.
- Implement soft deletes for resource-intensive entities like telemedicine recordings and equipment.
- Use unique constraints judiciously to prevent duplicate entries in high-volume scenarios.

## Troubleshooting Guide
Common issues and resolutions:
- Foreign key constraint failures: Use corrective migrations to add missing constraints with defensive error handling.
- Migration rollback challenges: Utilize restore points to snapshot critical data before major changes.
- Multi-user edit conflicts: Track and resolve conflicts using the edit conflicts table.
- Audit and reporting: Review migration audit reports and task lists to confirm fixes and testing coverage.
- Document version conflicts: Monitor document approval workflows and version histories for resolution.
- Telemedicine integration issues: Verify Jitsi server connectivity and configuration settings.
- Multi-company data synchronization: Ensure proper inter-company transaction processing and elimination entries.
- Notification delivery failures: Check notification preferences and channel configurations.
- Resource inventory conflicts: Monitor equipment maintenance schedules and supply tracking for resolution.
- Backup and recovery: Utilize automated backup system and recovery queue for failed operations.

**Section sources**
- [2026_04_10_050000_add_missing_foreign_key_constraints.php:78-108](file://database/migrations/2026_04_10_050000_add_missing_foreign_key_constraints.php#L78-L108)
- [2026_04_06_090000_create_error_handling_tables.php:52-80](file://database/migrations/2026_04_06_090000_create_error_handling_tables.php#L52-L80)
- [MIGRATION_AUDIT_REPORT.md:1-200](file://MIGRATION_AUDIT_REPORT.md#L1-L200)
- [TASK_LIST_DETAILED.md:76-87](file://TASK_LIST_DETAILED.md#L76-L87)

## Conclusion
The qalcuityERP schema evolution system combines configurable database connectivity, optimized migration execution, tenant-aware models, and robust operational safeguards. The recent extensive extensions now support comprehensive document management with versioning and approval workflows, advanced telemedicine operations with consultation tracking, recording systems, and comprehensive resource inventory management, multi-company consolidation with inter-entity transaction management, and sophisticated notification infrastructure with comprehensive preference management. The expanded error handling system includes automated backup, restore points, conflict resolution, and recovery queue mechanisms. By leveraging corrective migrations, enhanced error handling tables, restore points, foreign key enforcement, and these new advanced schema components, the platform ensures reliable schema changes across multiple tenants while maintaining performance and data integrity.