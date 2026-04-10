# Export Documentation & Compliance

<cite>
**Referenced Files in This Document**
- [create_export_jobs_table.php](file://database/migrations/2026_04_08_001834_create_export_jobs_table.php)
- [ExportService.php](file://app/Services/ExportService.php)
- [ExportJob.php](file://app/Models/ExportJob.php)
- [create_fisheries_tables.php](file://database/migrations/2026_04_06_140000_create_fisheries_tables.php)
- [export.blade.php](file://resources/views/fisheries/export.blade.php)
- [create_security_compliance_tables.php](file://database/migrations/2026_04_06_110000_create_security_compliance_tables.php)
- [create_regulatory_compliance_tables.php](file://database/migrations/2026_04_08_1400001_create_regulatory_compliance_tables.php)
- [MedicalCertificateController.php](file://app/Http/Controllers/Healthcare/MedicalCertificateController.php)
- [MedicalCertificate.php](file://app/Models/MedicalCertificate.php)
- [CoaCertificate.php](file://app/Models/CoaCertificate.php)
- [ConsignmentShipment.php](file://app/Models/ConsignmentShipment.php)
- [ConsignmentShipmentItem.php](file://app/Models/ConsignmentShipmentItem.php)
- [ExportShipment.php](file://app/Models/ExportShipment.php)
- [Shipment.php](file://app/Models/Shipment.php)
</cite>

## Table of Contents
1. [Introduction](#introduction)
2. [Project Structure](#project-structure)
3. [Core Components](#core-components)
4. [Architecture Overview](#architecture-overview)
5. [Detailed Component Analysis](#detailed-component-analysis)
6. [Dependency Analysis](#dependency-analysis)
7. [Performance Considerations](#performance-considerations)
8. [Troubleshooting Guide](#troubleshooting-guide)
9. [Conclusion](#conclusion)

## Introduction
This document describes the Export Documentation & Compliance System, focusing on:
- Export permit applications and tracking
- Health certificates issuance
- Customs declarations and submission
- Shipment tracking and status updates
- Regulatory compliance workflows
- International trade regulations and phytosanitary requirements
- Documentation formatting standards
- Integration with customs automation systems

The system integrates fisheries-specific export workflows with broader compliance and export infrastructure, including secure export job processing, audit trails, and regulatory reporting.

## Project Structure
The system spans database migrations, models, services, controllers, and views tailored for fisheries export operations and compliance.

```mermaid
graph TB
subgraph "Database Migrations"
M1["create_export_jobs_table.php"]
M2["create_fisheries_tables.php"]
M3["create_security_compliance_tables.php"]
M4["create_regulatory_compliance_tables.php"]
end
subgraph "Models"
EJ["ExportJob.php"]
ES["ExportShipment.php"]
CS["ConsignmentShipment.php"]
CSI["ConsignmentShipmentItem.php"]
SH["Shipment.php"]
MC["MedicalCertificate.php"]
CC["CoaCertificate.php"]
end
subgraph "Services"
S1["ExportService.php"]
end
subgraph "Controllers"
HC["MedicalCertificateController.php"]
end
subgraph "Views"
V1["export.blade.php"]
end
M1 --> EJ
M2 --> ES
M2 --> CS
M2 --> CSI
M2 --> SH
M2 --> MC
M2 --> CC
S1 --> EJ
HC --> MC
V1 --> ES
V1 --> CS
V1 --> SH
```

**Diagram sources**
- [create_export_jobs_table.php:1-50](file://database/migrations/2026_04_08_001834_create_export_jobs_table.php#L1-L50)
- [create_fisheries_tables.php:403-426](file://database/migrations/2026_04_06_140000_create_fisheries_tables.php#L403-L426)
- [create_security_compliance_tables.php:1-242](file://database/migrations/2026_04_06_110000_create_security_compliance_tables.php#L1-L242)
- [create_regulatory_compliance_tables.php:1-327](file://database/migrations/2026_04_08_1400001_create_regulatory_compliance_tables.php#L1-L327)
- [ExportService.php:1-244](file://app/Services/ExportService.php#L1-L244)
- [ExportJob.php:1-11](file://app/Models/ExportJob.php#L1-L11)
- [ExportShipment.php](file://app/Models/ExportShipment.php)
- [ConsignmentShipment.php](file://app/Models/ConsignmentShipment.php)
- [ConsignmentShipmentItem.php](file://app/Models/ConsignmentShipmentItem.php)
- [Shipment.php](file://app/Models/Shipment.php)
- [MedicalCertificateController.php](file://app/Http/Controllers/Healthcare/MedicalCertificateController.php)
- [MedicalCertificate.php](file://app/Models/MedicalCertificate.php)
- [CoaCertificate.php](file://app/Models/CoaCertificate.php)
- [export.blade.php:42-491](file://resources/views/fisheries/export.blade.php#L42-L491)

**Section sources**
- [create_export_jobs_table.php:1-50](file://database/migrations/2026_04_08_001834_create_export_jobs_table.php#L1-L50)
- [create_fisheries_tables.php:403-426](file://database/migrations/2026_04_06_140000_create_fisheries_tables.php#L403-L426)
- [create_security_compliance_tables.php:1-242](file://database/migrations/2026_04_06_110000_create_security_compliance_tables.php#L1-L242)
- [create_regulatory_compliance_tables.php:1-327](file://database/migrations/2026_04_08_1400001_create_regulatory_compliance_tables.php#L1-L327)
- [ExportService.php:1-244](file://app/Services/ExportService.php#L1-L244)
- [export.blade.php:42-491](file://resources/views/fisheries/export.blade.php#L42-L491)

## Core Components
- Export job tracking and progress monitoring via queued exports
- Fisheries export permits, health certificates, customs declarations, and shipment tracking
- Regulatory compliance records, audit trails, and disaster recovery logs
- Medical certificate issuance integrated with healthcare workflows
- Data anonymization and consent management for privacy-compliant operations

Key capabilities:
- Queue large exports with progress tracking and download URLs
- Manage permit lifecycle, certificate issuance, and customs documentation
- Track shipments and update statuses for regulatory submissions
- Maintain HIPAA-compliant audit logs and compliance reports
- Support anonymization requests and data subject requests

**Section sources**
- [ExportService.php:1-244](file://app/Services/ExportService.php#L1-L244)
- [create_export_jobs_table.php:1-50](file://database/migrations/2026_04_08_001834_create_export_jobs_table.php#L1-L50)
- [create_fisheries_tables.php:403-426](file://database/migrations/2026_04_06_140000_create_fisheries_tables.php#L403-L426)
- [create_regulatory_compliance_tables.php:21-311](file://database/migrations/2026_04_08_1400001_create_regulatory_compliance_tables.php#L21-L311)
- [export.blade.php:42-491](file://resources/views/fisheries/export.blade.php#L42-L491)

## Architecture Overview
The system follows a layered architecture:
- Presentation layer: Blade views for fisheries export dashboard
- Application layer: Controllers and services orchestrating workflows
- Domain layer: Models representing permits, certificates, customs declarations, and shipments
- Infrastructure layer: Queued export processing, storage, and compliance logging

```mermaid
graph TB
UI["Fisheries Export UI<br/>export.blade.php"] --> SVC["ExportService.php"]
SVC --> JOB["ExportJob.php"]
SVC --> QUEUE["Queued Export Jobs"]
UI --> CTRL["MedicalCertificateController.php"]
CTRL --> CERT["MedicalCertificate.php"]
UI --> MODELS["Permit/Certificate/Shipment Models"]
MODELS --> DB["Database Migrations"]
subgraph "Compliance Layer"
SEC["Security Compliance Tables"]
REG["Regulatory Compliance Tables"]
end
DB --> SEC
DB --> REG
```

**Diagram sources**
- [export.blade.php:42-491](file://resources/views/fisheries/export.blade.php#L42-L491)
- [ExportService.php:1-244](file://app/Services/ExportService.php#L1-L244)
- [ExportJob.php:1-11](file://app/Models/ExportJob.php#L1-L11)
- [create_fisheries_tables.php:403-426](file://database/migrations/2026_04_06_140000_create_fisheries_tables.php#L403-L426)
- [create_security_compliance_tables.php:1-242](file://database/migrations/2026_04_06_110000_create_security_compliance_tables.php#L1-L242)
- [create_regulatory_compliance_tables.php:1-327](file://database/migrations/2026_04_08_1400001_create_regulatory_compliance_tables.php#L1-L327)
- [MedicalCertificateController.php](file://app/Http/Controllers/Healthcare/MedicalCertificateController.php)
- [MedicalCertificate.php](file://app/Models/MedicalCertificate.php)

## Detailed Component Analysis

### Export Job Processing
The export job system enables scalable, queued exports with progress tracking and completion notifications.

```mermaid
classDiagram
class ExportService {
+queueExport(exportClass, constructorArgs, filename, disk) string
+getProgress(jobId) array
+updateProgress(jobId, status, totalRows, processedRows, message, extra) void
+shouldQueue(estimatedRows) bool
+estimateRowCount(query) int
+downloadExport(jobId) BinaryFileResponse|null
+cleanupOldExports(daysOld) int
}
class ExportJob {
+job_id
+user_id
+tenant_id
+export_type
+filename
+disk
+file_path
+status
+total_rows
+processed_rows
+file_size
+download_url
+error_message
+started_at
+completed_at
+failed_at
}
ExportService --> ExportJob : "creates/updates"
```

**Diagram sources**
- [ExportService.php:1-244](file://app/Services/ExportService.php#L1-L244)
- [ExportJob.php:1-11](file://app/Models/ExportJob.php#L1-L11)
- [create_export_jobs_table.php:15-39](file://database/migrations/2026_04_08_001834_create_export_jobs_table.php#L15-L39)

```mermaid
sequenceDiagram
participant Client as "Client"
participant Controller as "Controller"
participant Service as "ExportService"
participant Job as "ExportJob"
participant Queue as "Queue"
participant Storage as "Storage"
Client->>Controller : "Initiate Export"
Controller->>Service : "queueExport(...)"
Service->>Job : "Create job record"
Service->>Queue : "Dispatch queued export"
Queue-->>Service : "Progress updates"
Service->>Job : "updateProgress(...)"
Note over Service,Job : "Cache progress and persist status"
Client->>Service : "Get Progress"
Service-->>Client : "Status, %, download_url"
Client->>Service : "Download Export"
Service->>Storage : "Serve file"
Storage-->>Client : "File download"
```

**Diagram sources**
- [ExportService.php:28-107](file://app/Services/ExportService.php#L28-L107)
- [create_export_jobs_table.php:15-39](file://database/migrations/2026_04_08_001834_create_export_jobs_table.php#L15-L39)

**Section sources**
- [ExportService.php:1-244](file://app/Services/ExportService.php#L1-L244)
- [create_export_jobs_table.php:1-50](file://database/migrations/2026_04_08_001834_create_export_jobs_table.php#L1-L50)

### Fisheries Export Permits, Certificates, and Shipments
The fisheries export module supports permit applications, health certificates, customs declarations, and shipment tracking.

```mermaid
erDiagram
EXPORT_PERMITS {
bigint id PK
string permit_number
string permit_type
string destination_country
string commodity
date issue_date
date expiry_date
string issuing_authority
text notes
string status
string document_path
timestamp created_at
timestamp updated_at
}
CUSTOMS_DECLARATIONS {
bigint id PK
string declaration_number
bigint shipment_id FK
bigint export_permit_id FK
string hs_code
string country_of_origin
string destination_country
decimal declared_value
string currency
decimal total_weight
int package_count
string package_type
text contents_description
timestamp filing_date
string status
string document_path
timestamp created_at
timestamp updated_at
}
HEALTH_CERTIFICATES {
bigint id PK
string certificate_number
string certificate_type
string issuer
string issuing_authority
date issue_date
date expiry_date
string status
string document_path
timestamp created_at
timestamp updated_at
}
SHIPMENTS {
bigint id PK
string shipment_number
string carrier
string vessel_name
string loading_port
string discharge_port
date estimated_departure
date estimated_arrival
decimal total_weight_kg
decimal declared_value_usd
string status
timestamp created_at
timestamp updated_at
}
EXPORT_PERMITS ||--o{ CUSTOMS_DECLARATIONS : "references"
SHIPMENTS ||--o{ CUSTOMS_DECLARATIONS : "references"
```

**Diagram sources**
- [create_fisheries_tables.php:403-426](file://database/migrations/2026_04_06_140000_create_fisheries_tables.php#L403-L426)

```mermaid
sequenceDiagram
participant User as "User"
participant UI as "export.blade.php"
participant Controller as "Controller"
participant Service as "ExportDocumentationService"
participant DB as "Database"
User->>UI : "Open Export Dashboard"
UI->>Controller : "GET /fisheries/export"
Controller->>Service : "Load permits/certificates/shipments"
Service->>DB : "Query permits/customs/shipments"
DB-->>Service : "Results"
Service-->>Controller : "Aggregated data"
Controller-->>UI : "Render dashboard"
User->>UI : "Submit Permit Application"
UI->>Controller : "POST Permit Form"
Controller->>Service : "Create Permit"
Service->>DB : "Insert permit record"
DB-->>Service : "Success"
Service-->>Controller : "Redirect with success"
Controller-->>UI : "Show success message"
```

**Diagram sources**
- [export.blade.php:42-491](file://resources/views/fisheries/export.blade.php#L42-L491)
- [create_fisheries_tables.php:403-426](file://database/migrations/2026_04_06_140000_create_fisheries_tables.php#L403-L426)

**Section sources**
- [export.blade.php:42-491](file://resources/views/fisheries/export.blade.php#L42-L491)
- [create_fisheries_tables.php:403-426](file://database/migrations/2026_04_06_140000_create_fisheries_tables.php#L403-L426)

### Regulatory Compliance and Audit Trails
The system maintains HIPAA-compliant audit trails, access violation logs, anonymization requests, compliance reports, backup logs, and disaster recovery logs.

```mermaid
erDiagram
AUDIT_TRAILS {
bigint id PK
string audit_number
bigint user_id
string user_name
string user_role
string ip_address
text user_agent
string action
string action_category
string model_type
bigint model_id
string record_identifier
json old_values
json new_values
json changed_fields
string access_reason
string department
bigint patient_id
boolean is_hipaa_relevant
boolean contains_phi
string data_classification
boolean is_suspicious
string risk_level
text notes
timestamp created_at
}
ACCESS_VIOLATIONS {
bigint id PK
bigint user_id
bigint audit_id
string violation_number
enum violation_type
string violation_description
enum severity
string ip_address
text user_agent
datetime violation_time
enum status
text investigation_notes
bigint investigated_by
datetime investigated_at
text resolution
datetime resolved_at
boolean user_notified
boolean access_revoked
boolean reported_to_authority
text corrective_actions
timestamp created_at
}
DATA_ANONYMIZATION_LOGS {
bigint id PK
bigint requested_by
bigint approved_by
string anonymization_number
string purpose
text description
date request_date
datetime approved_at
datetime completed_at
json data_types
int total_records
int anonymized_records
json anonymization_methods
json fields_anonymized
boolean is_reversible
enum status
text rejection_reason
string output_file_path
string output_format
text data_usage_agreement
boolean ethics_approval
string ethics_approval_number
text compliance_notes
timestamp created_at
}
COMPLIANCE_REPORTS {
bigint id PK
bigint generated_by
string report_number
string report_type
string report_name
date period_start
date period_end
datetime generated_at
json compliance_frameworks
json requirements_checked
json compliance_status
int total_checks
int passed_checks
int failed_checks
int warning_checks
decimal compliance_score
json findings
json recommendations
json corrective_actions
enum status
text executive_summary
text notes
string report_file_path
string evidence_folder
timestamp created_at
}
BACKUP_LOGS {
bigint id PK
bigint initiated_by
string backup_number
datetime backup_start
datetime backup_end
enum backup_type
enum backup_method
json tables_included
int total_records
decimal backup_size_mb
string storage_location
string storage_path
string storage_provider
boolean is_encrypted
string encryption_algorithm
enum status
text error_message
boolean verification_passed
datetime verified_at
date retention_until
boolean is_deleted
datetime deleted_at
boolean hipaa_compliant
text compliance_notes
timestamp created_at
}
DISASTER_RECOVERY_LOGS {
bigint id PK
bigint initiated_by
bigint approved_by
string dr_number
datetime incident_start
datetime incident_end
enum incident_type
string incident_description
enum severity
json affected_systems
int affected_records
datetime downtime_start
datetime downtime_end
int downtime_minutes
string backup_used
datetime recovery_start
datetime recovery_end
int records_recovered
int records_lost
enum status
text recovery_notes
text lessons_learned
text preventive_measures
boolean reported_to_authority
text regulatory_notifications
timestamp created_at
}
```

**Diagram sources**
- [create_regulatory_compliance_tables.php:21-311](file://database/migrations/2026_04_08_1400001_create_regulatory_compliance_tables.php#L21-L311)

**Section sources**
- [create_regulatory_compliance_tables.php:1-327](file://database/migrations/2026_04_08_1400001_create_regulatory_compliance_tables.php#L1-L327)

### Medical Certificate Issuance
The healthcare module supports medical certificate creation and issuance, aligned with compliance requirements.

```mermaid
classDiagram
class MedicalCertificateController {
+store(request)
+show(id)
+generate(id)
}
class MedicalCertificate {
+certificate_number
+certificate_type
+issuer
+issuing_authority
+issue_date
+expiry_date
+status
+document_path
}
MedicalCertificateController --> MedicalCertificate : "manages"
```

**Diagram sources**
- [MedicalCertificateController.php](file://app/Http/Controllers/Healthcare/MedicalCertificateController.php)
- [MedicalCertificate.php](file://app/Models/MedicalCertificate.php)

**Section sources**
- [MedicalCertificateController.php](file://app/Http/Controllers/Healthcare/MedicalCertificateController.php)
- [MedicalCertificate.php](file://app/Models/MedicalCertificate.php)

### Shipment Tracking and Status Updates
Shipment tracking integrates with customs declarations and export permits for end-to-end visibility.

```mermaid
flowchart TD
Start(["Shipment Created"]) --> UpdateStatus["Update Shipment Status"]
UpdateStatus --> Validate["Validate Against Permit/Certificate"]
Validate --> PermitValid{"Permit Valid?"}
PermitValid --> |Yes| Proceed["Proceed to Customs Declaration"]
PermitValid --> |No| Block["Block Shipment<br/>Require Permit Renewal"]
Proceed --> Declare["Create/Update Customs Declaration"]
Declare --> Submit["Submit to Customs Automation"]
Submit --> Track["Track Status via API"]
Track --> Complete["Complete/Release"]
Block --> Review["Review and Resolve Issues"]
Review --> UpdateStatus
```

[No sources needed since this diagram shows conceptual workflow, not actual code structure]

## Dependency Analysis
The system exhibits clear separation of concerns:
- ExportService depends on ExportJob and storage for queued exports
- Fisheries modules depend on dedicated migration tables for permits, certificates, customs declarations, and shipments
- Regulatory compliance tables support audit, access violations, anonymization, reports, backups, and disaster recovery
- Medical certificate workflows integrate with healthcare controllers and models

```mermaid
graph LR
ES["ExportService.php"] --> EJ["ExportJob.php"]
ES --> EX["Excel Facade"]
UI["export.blade.php"] --> SVC["ExportService.php"]
UI --> PERM["Export Permits"]
UI --> CERT["Health Certificates"]
UI --> SHIP["Shipments"]
PERM --> DB["Fisheries Tables"]
CERT --> DB
SHIP --> DB
DB --> REG["Regulatory Compliance Tables"]
DB --> SEC["Security Compliance Tables"]
```

**Diagram sources**
- [ExportService.php:1-244](file://app/Services/ExportService.php#L1-L244)
- [ExportJob.php:1-11](file://app/Models/ExportJob.php#L1-L11)
- [export.blade.php:42-491](file://resources/views/fisheries/export.blade.php#L42-L491)
- [create_fisheries_tables.php:403-426](file://database/migrations/2026_04_06_140000_create_fisheries_tables.php#L403-L426)
- [create_regulatory_compliance_tables.php:1-327](file://database/migrations/2026_04_08_1400001_create_regulatory_compliance_tables.php#L1-L327)
- [create_security_compliance_tables.php:1-242](file://database/migrations/2026_04_06_110000_create_security_compliance_tables.php#L1-L242)

**Section sources**
- [ExportService.php:1-244](file://app/Services/ExportService.php#L1-L244)
- [export.blade.php:42-491](file://resources/views/fisheries/export.blade.php#L42-L491)
- [create_fisheries_tables.php:403-426](file://database/migrations/2026_04_06_140000_create_fisheries_tables.php#L403-L426)
- [create_regulatory_compliance_tables.php:1-327](file://database/migrations/2026_04_08_1400001_create_regulatory_compliance_tables.php#L1-L327)
- [create_security_compliance_tables.php:1-242](file://database/migrations/2026_04_06_110000_create_security_compliance_tables.php#L1-L242)

## Performance Considerations
- Use queued exports for large datasets to prevent timeouts and improve scalability
- Monitor export progress via cache and database fallback for resilience
- Optimize database queries with appropriate indexing on frequently filtered columns
- Implement cleanup routines for old export jobs and files to manage storage growth
- Ensure storage disks are configured for high throughput and durability

[No sources needed since this section provides general guidance]

## Troubleshooting Guide
Common issues and resolutions:
- Export job not found: Verify job ID and check database records; confirm queue worker is running
- Progress stuck at pending: Confirm queue worker is processing jobs and cache is writable
- Download fails: Ensure file exists on the configured disk and job status is completed
- Compliance report generation errors: Validate framework configurations and required fields
- Audit trail anomalies: Review access violation logs and investigate flagged activities

**Section sources**
- [ExportService.php:77-107](file://app/Services/ExportService.php#L77-L107)
- [create_regulatory_compliance_tables.php:74-121](file://database/migrations/2026_04_08_1400001_create_regulatory_compliance_tables.php#L74-L121)

## Conclusion
The Export Documentation & Compliance System provides a robust foundation for managing export permits, health certificates, customs declarations, and shipment tracking while maintaining strong regulatory compliance and auditability. The modular design supports scalability, security, and integration with customs automation systems, ensuring adherence to international trade regulations and phytosanitary requirements.