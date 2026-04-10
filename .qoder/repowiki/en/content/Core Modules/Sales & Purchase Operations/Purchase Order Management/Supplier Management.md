# Supplier Management

<cite>
**Referenced Files in This Document**
- [Supplier.php](file://app/Models/Supplier.php)
- [SupplierScorecard.php](file://app/Models/SupplierScorecard.php)
- [SupplierDocument.php](file://app/Models/SupplierDocument.php)
- [SupplierIncident.php](file://app/Models/SupplierIncident.php)
- [SupplierPortalUser.php](file://app/Models/SupplierPortalUser.php)
- [SupplierRfqResponse.php](file://app/Models/SupplierRfqResponse.php)
- [SupplierController.php](file://app/Http/Controllers/SupplierController.php)
- [SupplierScorecardController.php](file://app/Http/Controllers/Suppliers/SupplierScorecardController.php)
- [SupplierScorecardService.php](file://app/Services/SupplierScorecardService.php)
- [StrategicSourcingService.php](file://app/Services/StrategicSourcingService.php)
- [rfq-analysis.blade.php](file://resources/views/suppliers/rfq-analysis.blade.php)
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
10. [Appendices](#appendices)

## Introduction
This document describes the supplier management capabilities implemented in the system, focusing on supplier registration and profile management, supplier categorization and evaluation via scorecards, performance metrics, strategic sourcing and RFQ evaluation, supplier onboarding workflows, due diligence and compliance verification, supplier relationship management, communication tools, contract and portal integration, risk assessment, and collaboration features. It also outlines supplier scoring algorithms, rating systems, and improvement initiatives.

## Project Structure
Supplier management spans models, controllers, services, and views:
- Models define supplier data, documents, incidents, scorecards, RFQ responses, and portal users.
- Controllers handle supplier CRUD, scorecard dashboards, sourcing dashboards, and RFQ analysis.
- Services encapsulate business logic for scorecard generation, sourcing analytics, and supplier comparisons.
- Views present dashboards, scorecard details, and RFQ evaluation results.

```mermaid
graph TB
subgraph "Models"
M_Supplier["Supplier"]
M_Scorecard["SupplierScorecard"]
M_Doc["SupplierDocument"]
M_Incident["SupplierIncident"]
M_PortalUser["SupplierPortalUser"]
M_RfqResp["SupplierRfqResponse"]
end
subgraph "Controllers"
C_Supplier["SupplierController"]
C_Scorecard["SupplierScorecardController"]
end
subgraph "Services"
S_Scorecard["SupplierScorecardService"]
S_Sourcing["StrategicSourcingService"]
end
subgraph "Views"
V_Rfq["rfq-analysis.blade.php"]
end
C_Supplier --> M_Supplier
C_Scorecard --> S_Scorecard
C_Scorecard --> S_Sourcing
S_Scorecard --> M_Supplier
S_Scorecard --> M_Scorecard
S_Scorecard --> M_Incident
S_Sourcing --> M_Supplier
S_Sourcing --> M_RfqResp
S_Sourcing --> M_Scorecard
V_Rfq --> S_Sourcing
```

**Diagram sources**
- [Supplier.php:13-51](file://app/Models/Supplier.php#L13-L51)
- [SupplierScorecard.php:12-114](file://app/Models/SupplierScorecard.php#L12-L114)
- [SupplierDocument.php:11-64](file://app/Models/SupplierDocument.php#L11-L64)
- [SupplierIncident.php:11-70](file://app/Models/SupplierIncident.php#L11-L70)
- [SupplierPortalUser.php:11-45](file://app/Models/SupplierPortalUser.php#L11-L45)
- [SupplierRfqResponse.php:11-60](file://app/Models/SupplierRfqResponse.php#L11-L60)
- [SupplierController.php:9-128](file://app/Http/Controllers/SupplierController.php#L9-L128)
- [SupplierScorecardController.php:11-206](file://app/Http/Controllers/Suppliers/SupplierScorecardController.php#L11-L206)
- [SupplierScorecardService.php:12-321](file://app/Services/SupplierScorecardService.php#L12-L321)
- [StrategicSourcingService.php:11-388](file://app/Services/StrategicSourcingService.php#L11-L388)
- [rfq-analysis.blade.php:219-266](file://resources/views/suppliers/rfq-analysis.blade.php#L219-L266)

**Section sources**
- [Supplier.php:13-51](file://app/Models/Supplier.php#L13-L51)
- [SupplierScorecard.php:12-114](file://app/Models/SupplierScorecard.php#L12-L114)
- [SupplierController.php:9-128](file://app/Http/Controllers/SupplierController.php#L9-L128)
- [SupplierScorecardController.php:11-206](file://app/Http/Controllers/Suppliers/SupplierScorecardController.php#L11-L206)
- [SupplierScorecardService.php:12-321](file://app/Services/SupplierScorecardService.php#L12-L321)
- [StrategicSourcingService.php:11-388](file://app/Services/StrategicSourcingService.php#L11-L388)
- [rfq-analysis.blade.php:219-266](file://resources/views/suppliers/rfq-analysis.blade.php#L219-L266)

## Core Components
- Supplier model: Stores supplier profile, contact info, bank details, and active status. Includes relationships to purchase orders and scorecards.
- SupplierScorecard model: Captures quality, delivery, cost, and service metrics with computed overall score, rating, and status.
- SupplierDocument model: Manages supplier documents with verification lifecycle and expiry tracking.
- SupplierIncident model: Tracks supplier-related incidents with severity, impact, and resolution metadata.
- SupplierPortalUser model: Enables supplier portal access with role and activity tracking.
- SupplierRfqResponse model: Captures RFQ submissions with pricing, lead time, validity, and acceptance tracking.
- SupplierController: Handles supplier creation, updates, activation toggling, and deletion with tenant scoping and activity logging.
- SupplierScorecardController: Provides dashboards, scorecard generation, sourcing analytics, RFQ analysis, supplier comparison, and exports.
- SupplierScorecardService: Generates scorecards from purchase orders and incidents, computes weighted scores, and produces performance reports.
- StrategicSourcingService: Identifies sourcing opportunities, manages sourcing lifecycle, evaluates RFQ responses with multi-criteria scoring, compares suppliers, and tracks completion metrics.

**Section sources**
- [Supplier.php:13-51](file://app/Models/Supplier.php#L13-L51)
- [SupplierScorecard.php:12-114](file://app/Models/SupplierScorecard.php#L12-L114)
- [SupplierDocument.php:11-64](file://app/Models/SupplierDocument.php#L11-L64)
- [SupplierIncident.php:11-70](file://app/Models/SupplierIncident.php#L11-L70)
- [SupplierPortalUser.php:11-45](file://app/Models/SupplierPortalUser.php#L11-L45)
- [SupplierRfqResponse.php:11-60](file://app/Models/SupplierRfqResponse.php#L11-L60)
- [SupplierController.php:9-128](file://app/Http/Controllers/SupplierController.php#L9-L128)
- [SupplierScorecardController.php:11-206](file://app/Http/Controllers/Suppliers/SupplierScorecardController.php#L11-L206)
- [SupplierScorecardService.php:12-321](file://app/Services/SupplierScorecardService.php#L12-L321)
- [StrategicSourcingService.php:11-388](file://app/Services/StrategicSourcingService.php#L11-L388)

## Architecture Overview
The supplier management architecture follows layered MVC with service-driven business logic:
- Controllers orchestrate requests and delegate to services.
- Services encapsulate domain logic for scorecards, sourcing, and reporting.
- Models define persistence and relationships.
- Views render dashboards and analysis pages.

```mermaid
graph TB
UI["UI: Scorecard Dashboard<br/>RFQ Analysis"] --> Ctrl["SupplierScorecardController"]
UI --> SupCtrl["SupplierController"]
Ctrl --> SvcSc["SupplierScorecardService"]
Ctrl --> SvcSrc["StrategicSourcingService"]
SvcSc --> ModelSc["SupplierScorecard"]
SvcSc --> ModelSup["Supplier"]
SvcSc --> ModelInc["SupplierIncident"]
SvcSrc --> ModelSup
SvcSrc --> ModelRfq["SupplierRfqResponse"]
SvcSrc --> ModelSc
SupCtrl --> ModelSup
```

**Diagram sources**
- [SupplierScorecardController.php:11-206](file://app/Http/Controllers/Suppliers/SupplierScorecardController.php#L11-L206)
- [SupplierController.php:9-128](file://app/Http/Controllers/SupplierController.php#L9-L128)
- [SupplierScorecardService.php:12-321](file://app/Services/SupplierScorecardService.php#L12-L321)
- [StrategicSourcingService.php:11-388](file://app/Services/StrategicSourcingService.php#L11-L388)
- [SupplierScorecard.php:12-114](file://app/Models/SupplierScorecard.php#L12-L114)
- [Supplier.php:13-51](file://app/Models/Supplier.php#L13-L51)
- [SupplierIncident.php:11-70](file://app/Models/SupplierIncident.php#L11-L70)
- [SupplierRfqResponse.php:11-60](file://app/Models/SupplierRfqResponse.php#L11-L60)

## Detailed Component Analysis

### Supplier Registration and Profile Management
- Registration: Validates and creates suppliers under the current tenant, sets initial active status, and logs activity.
- Profile updates: Updates contact, company, banking, and active status with tenant scoping and change logging.
- Activation/deactivation: Toggles active flag and logs changes; prevents deletion if purchase orders exist by deactivating instead.
- Search and filtering: Index supports search across name/company/email/phone and status filters.

```mermaid
sequenceDiagram
participant U as "User"
participant C as "SupplierController"
participant M as "Supplier Model"
participant L as "ActivityLog"
U->>C : "POST /suppliers"
C->>C : "Validate input"
C->>M : "Check duplicate by name"
alt "Duplicate exists"
C-->>U : "Error : Duplicate name"
else "Unique"
C->>M : "Create supplier (tenant_id, is_active=true)"
M-->>C : "Supplier created"
C->>L : "Record activity log"
C-->>U : "Success message"
end
```

**Diagram sources**
- [SupplierController.php:46-74](file://app/Http/Controllers/SupplierController.php#L46-L74)
- [Supplier.php:13-51](file://app/Models/Supplier.php#L13-L51)

**Section sources**
- [SupplierController.php:16-127](file://app/Http/Controllers/SupplierController.php#L16-L127)
- [Supplier.php:13-51](file://app/Models/Supplier.php#L13-L51)

### Supplier Categorization and Evaluation
- Categorization: Supplier records support category-based grouping for performance segmentation.
- Scorecard generation: Computes weighted scores across quality, delivery, cost, and service metrics; derives rating and status.
- Performance trends: Tracks monthly scorecard history to detect improvements or declines.

```mermaid
flowchart TD
Start(["Generate Scorecard"]) --> FetchPO["Fetch Purchase Orders<br/>within period"]
FetchPO --> Quality["Compute Quality Metrics<br/>(defect rate, quality score)"]
FetchPO --> Delivery["Compute Delivery Metrics<br/>(on-time %, avg lead time)"]
FetchPO --> Cost["Compute Cost Metrics<br/>(total spend, cost savings)"]
FetchPO --> Service["Compute Service Metrics<br/>(incident resolution rate)"]
Quality --> Weight["Apply Weights<br/>(Q:35%, D:30%, C:20%, S:15%)"]
Delivery --> Weight
Cost --> Weight
Service --> Weight
Weight --> Overall["Calculate Overall Score"]
Overall --> Rating["Derive Rating & Status"]
Rating --> Save["Persist Scorecard"]
Save --> End(["Done"])
```

**Diagram sources**
- [SupplierScorecardService.php:17-54](file://app/Services/SupplierScorecardService.php#L17-L54)
- [SupplierScorecardService.php:59-177](file://app/Services/SupplierScorecardService.php#L59-L177)
- [SupplierScorecard.php:74-101](file://app/Models/SupplierScorecard.php#L74-L101)

**Section sources**
- [SupplierScorecardService.php:17-321](file://app/Services/SupplierScorecardService.php#L17-L321)
- [SupplierScorecard.php:12-114](file://app/Models/SupplierScorecard.php#L12-L114)

### Supplier Evaluation Processes, Scorecard Systems, and Performance Metrics
- Multi-criteria RFQ evaluation: Scores suppliers by price, lead time, supplier rating, delivery performance, and payment terms with defined weights.
- Evaluation methodology: Price (40%), lead time (25%), supplier rating (15%), delivery performance (10%), payment terms (10%).
- Supplier comparison: Compares multiple suppliers by on-time delivery rate and order value trends.

```mermaid
sequenceDiagram
participant U as "User"
participant C as "SupplierScorecardController"
participant S as "StrategicSourcingService"
participant M as "SupplierRfqResponse"
participant Sc as "SupplierScorecard"
U->>C : "Analyze RFQ Responses"
C->>S : "analyzeRfqResponses(rfqId)"
S->>M : "Load responses with supplier.scorecards"
S->>S : "Compute price/lead time/supplier rating/delivery/payment scores"
S->>Sc : "Use latest scorecard for rating/performance"
S-->>C : "Ranked responses with evaluation criteria"
C-->>U : "Render RFQ Analysis view"
```

**Diagram sources**
- [SupplierScorecardController.php:89-98](file://app/Http/Controllers/Suppliers/SupplierScorecardController.php#L89-L98)
- [StrategicSourcingService.php:163-250](file://app/Services/StrategicSourcingService.php#L163-L250)
- [SupplierRfqResponse.php:11-60](file://app/Models/SupplierRfqResponse.php#L11-L60)
- [SupplierScorecard.php:12-114](file://app/Models/SupplierScorecard.php#L12-L114)

**Section sources**
- [StrategicSourcingService.php:163-326](file://app/Services/StrategicSourcingService.php#L163-L326)
- [SupplierScorecardController.php:89-98](file://app/Http/Controllers/Suppliers/SupplierScorecardController.php#L89-L98)
- [rfq-analysis.blade.php:219-266](file://resources/views/suppliers/rfq-analysis.blade.php#L219-L266)

### Supplier Onboarding Workflows and Due Diligence
- Supplier onboarding: Creation via SupplierController with initial active status and activity logging.
- Due diligence and compliance: SupplierDocument model supports document types, verification, expiry dates, and verification lifecycle.
- Risk monitoring: SupplierIncident model captures severity, financial impact, resolution, and preventive actions.

```mermaid
classDiagram
class Supplier {
+int tenant_id
+string name
+string email
+string phone
+string company
+string address
+string npwp
+string bank_name
+string bank_account
+string bank_holder
+bool is_active
+purchaseOrders()
+scorecards()
}
class SupplierDocument {
+int tenant_id
+int supplier_id
+string document_type
+string document_name
+string file_path
+date issue_date
+date expiry_date
+bool is_verified
+verified_by
+verified_at
+isExpiringSoon(days)
+isExpired()
}
class SupplierIncident {
+int tenant_id
+int supplier_id
+int purchase_order_id
+string incident_type
+string severity
+string status
+decimal financial_impact
+datetime reported_at
+datetime resolved_at
+getSeverityColorAttribute()
}
Supplier "1" --> "*" SupplierDocument : "has_many"
Supplier "1" --> "*" SupplierIncident : "has_many"
```

**Diagram sources**
- [Supplier.php:13-51](file://app/Models/Supplier.php#L13-L51)
- [SupplierDocument.php:11-64](file://app/Models/SupplierDocument.php#L11-L64)
- [SupplierIncident.php:11-70](file://app/Models/SupplierIncident.php#L11-L70)

**Section sources**
- [SupplierController.php:46-127](file://app/Http/Controllers/SupplierController.php#L46-L127)
- [SupplierDocument.php:11-64](file://app/Models/SupplierDocument.php#L11-L64)
- [SupplierIncident.php:11-70](file://app/Models/SupplierIncident.php#L11-L70)

### Supplier Relationship Management
- Portal integration: SupplierPortalUser enables supplier-side access with roles and activity tracking.
- Communication: SupplierIncident captures reported/resolved timestamps and resolution notes for SLA tracking.
- Collaboration: StrategicSourcingService supports sourcing opportunities, RFQ tracking, and supplier participation metrics.

```mermaid
classDiagram
class SupplierPortalUser {
+int tenant_id
+int supplier_id
+string name
+string email
+string role
+string position
+bool is_active
+datetime last_login_at
}
class SupplierRfqResponse {
+int tenant_id
+int rfq_id
+int supplier_id
+decimal quoted_price
+int lead_time_days
+date valid_until
+string status
+datetime submitted_at
+isExpired()
}
SupplierPortalUser --> Supplier : "belongs_to"
SupplierRfqResponse --> Supplier : "belongs_to"
```

**Diagram sources**
- [SupplierPortalUser.php:11-45](file://app/Models/SupplierPortalUser.php#L11-L45)
- [SupplierRfqResponse.php:11-60](file://app/Models/SupplierRfqResponse.php#L11-L60)

**Section sources**
- [SupplierPortalUser.php:11-45](file://app/Models/SupplierPortalUser.php#L11-L45)
- [SupplierRfqResponse.php:11-60](file://app/Models/SupplierRfqResponse.php#L11-L60)
- [StrategicSourcingService.php:80-157](file://app/Services/StrategicSourcingService.php#L80-L157)

### Supplier Communication Tools, Contract Management, and Portal Integration
- Communication: SupplierIncident severity color coding and resolution tracking support SLA visibility.
- Contracts: SupplierDocument supports contract/certificates with verification and expiry checks.
- Portal: SupplierPortalUser model integrates supplier self-service and activity monitoring.

```mermaid
flowchart TD
Comm["Incident Reported"] --> Severity["Severity Level"]
Severity --> SLA["SLA Tracking<br/>Resolution Time"]
SLA --> Resolution["Resolution Notes & Actions"]
Resolution --> Portal["Portal Visibility<br/>SupplierPortalUser"]
Portal --> Contract["Contract Verification<br/>SupplierDocument"]
```

**Diagram sources**
- [SupplierIncident.php:11-70](file://app/Models/SupplierIncident.php#L11-L70)
- [SupplierPortalUser.php:11-45](file://app/Models/SupplierPortalUser.php#L11-L45)
- [SupplierDocument.php:11-64](file://app/Models/SupplierDocument.php#L11-L64)

**Section sources**
- [SupplierIncident.php:11-70](file://app/Models/SupplierIncident.php#L11-L70)
- [SupplierPortalUser.php:11-45](file://app/Models/SupplierPortalUser.php#L11-L45)
- [SupplierDocument.php:11-64](file://app/Models/SupplierDocument.php#L11-L64)

### Supplier Risk Assessment and Compliance Verification
- Risk indicators: Defect rates, late deliveries, incident counts, and financial impact inform risk profiles.
- Compliance verification: SupplierDocument tracks verification status and expiry dates to prevent non-compliant engagement.

```mermaid
flowchart TD
Data["Purchase Orders & Incidents"] --> Metrics["Compute Metrics"]
Metrics --> Risk["Risk Score<br/>(Defects, Delays, Incidents)"]
Risk --> Verify["Verify Documents<br/>Expiry & Verification"]
Verify --> Decision["Compliance Status"]
```

**Diagram sources**
- [SupplierScorecardService.php:59-177](file://app/Services/SupplierScorecardService.php#L59-L177)
- [SupplierDocument.php:53-63](file://app/Models/SupplierDocument.php#L53-L63)

**Section sources**
- [SupplierScorecardService.php:59-177](file://app/Services/SupplierScorecardService.php#L59-L177)
- [SupplierDocument.php:53-63](file://app/Models/SupplierDocument.php#L53-L63)

### Supplier Collaboration Features
- Sourcing opportunities: StrategicSourcingService identifies consolidation and diversification opportunities and tracks progress.
- RFQ lifecycle: SupplierRfqResponse captures submission, acceptance, and validity; evaluation dashboard ranks suppliers.
- Comparison: Supplier comparison by on-time delivery and order value helps select optimal partners.

```mermaid
sequenceDiagram
participant U as "User"
participant S as "StrategicSourcingService"
participant O as "SourcingOpportunity"
participant R as "SupplierRfqResponse"
U->>S : "Identify Opportunities"
S->>O : "Create/Track Opportunities"
U->>S : "Submit/Analyze RFQ Responses"
S->>R : "Load responses"
S-->>U : "Ranked recommendations"
U->>S : "Compare Suppliers"
S-->>U : "Comparison metrics"
```

**Diagram sources**
- [StrategicSourcingService.php:16-67](file://app/Services/StrategicSourcingService.php#L16-L67)
- [StrategicSourcingService.php:163-388](file://app/Services/StrategicSourcingService.php#L163-L388)
- [SupplierRfqResponse.php:11-60](file://app/Models/SupplierRfqResponse.php#L11-L60)

**Section sources**
- [StrategicSourcingService.php:16-67](file://app/Services/StrategicSourcingService.php#L16-L67)
- [SupplierRfqResponse.php:11-60](file://app/Models/SupplierRfqResponse.php#L11-L60)

## Dependency Analysis
Supplier management components depend on:
- Models: Supplier, SupplierScorecard, SupplierDocument, SupplierIncident, SupplierPortalUser, SupplierRfqResponse.
- Controllers: SupplierController, SupplierScorecardController.
- Services: SupplierScorecardService, StrategicSourcingService.
- Views: RFQ analysis view displays evaluation methodology and weights.

```mermaid
graph LR
C1["SupplierController"] --> M1["Supplier"]
C2["SupplierScorecardController"] --> S1["SupplierScorecardService"]
C2 --> S2["StrategicSourcingService"]
S1 --> M1
S1 --> M2["SupplierScorecard"]
S1 --> M3["SupplierIncident"]
S2 --> M1
S2 --> M4["SupplierRfqResponse"]
S2 --> M2
V1["rfq-analysis.blade.php"] --> S2
```

**Diagram sources**
- [SupplierController.php:9-128](file://app/Http/Controllers/SupplierController.php#L9-L128)
- [SupplierScorecardController.php:11-206](file://app/Http/Controllers/Suppliers/SupplierScorecardController.php#L11-L206)
- [SupplierScorecardService.php:12-321](file://app/Services/SupplierScorecardService.php#L12-L321)
- [StrategicSourcingService.php:11-388](file://app/Services/StrategicSourcingService.php#L11-L388)
- [Supplier.php:13-51](file://app/Models/Supplier.php#L13-L51)
- [SupplierScorecard.php:12-114](file://app/Models/SupplierScorecard.php#L12-L114)
- [SupplierIncident.php:11-70](file://app/Models/SupplierIncident.php#L11-L70)
- [SupplierRfqResponse.php:11-60](file://app/Models/SupplierRfqResponse.php#L11-L60)
- [rfq-analysis.blade.php:219-266](file://resources/views/suppliers/rfq-analysis.blade.php#L219-L266)

**Section sources**
- [SupplierController.php:9-128](file://app/Http/Controllers/SupplierController.php#L9-L128)
- [SupplierScorecardController.php:11-206](file://app/Http/Controllers/Suppliers/SupplierScorecardController.php#L11-L206)
- [SupplierScorecardService.php:12-321](file://app/Services/SupplierScorecardService.php#L12-L321)
- [StrategicSourcingService.php:11-388](file://app/Services/StrategicSourcingService.php#L11-L388)

## Performance Considerations
- Indexing: Ensure tenant_id, supplier_id, and date range fields are indexed for efficient queries on purchase orders, scorecards, incidents, and RFQ responses.
- Aggregation: Use database-level aggregations to compute defect rates, on-time percentages, and averages to minimize PHP loops.
- Background jobs: Offload bulk scorecard generation and exports to queued jobs for large datasets.
- Pagination: Controllers already paginate lists; maintain pagination for RFQ analysis and supplier comparisons.

## Troubleshooting Guide
- Duplicate supplier names: Validation prevents duplicates during creation; adjust input or check existing records.
- Deletion blocked by purchase orders: Deactivate supplier instead of deleting to preserve history.
- Expired or expiring documents: Use SupplierDocument helpers to detect expiry and trigger reminders.
- RFQ evaluation errors: Ensure RFQ has responses; otherwise, the analysis returns an error message.
- Scorecard generation failures: Wrap bulk generation in try-catch and review logs for per-supplier exceptions.

**Section sources**
- [SupplierController.php:62-64](file://app/Http/Controllers/SupplierController.php#L62-L64)
- [SupplierController.php:117-121](file://app/Http/Controllers/SupplierController.php#L117-L121)
- [SupplierDocument.php:53-63](file://app/Models/SupplierDocument.php#L53-L63)
- [SupplierScorecardController.php:93-95](file://app/Http/Controllers/Suppliers/SupplierScorecardController.php#L93-L95)
- [SupplierScorecardService.php:314-316](file://app/Services/SupplierScorecardService.php#L314-L316)

## Conclusion
The supplier management module provides a robust foundation for supplier lifecycle operations, from onboarding and profile maintenance to evaluation, risk monitoring, and strategic collaboration. The scorecard system and sourcing analytics enable data-driven decisions, while portal and document management streamline supplier engagement and compliance.

## Appendices

### Supplier Scoring Algorithms and Rating Systems
- Scorecard weights: Quality (35%), Delivery (30%), Cost (20%), Service (15%); overall score determines rating (A–F) and status (active/warning/critical).
- RFQ evaluation weights: Price (40%), Lead time (25%), Supplier rating (15%), Delivery performance (10%), Payment terms (10%).
- Improvement initiatives: Use scorecard trends and incident reports to identify areas for improvement and set action items.

**Section sources**
- [SupplierScorecardService.php:29-34](file://app/Services/SupplierScorecardService.php#L29-L34)
- [SupplierScorecard.php:74-101](file://app/Models/SupplierScorecard.php#L74-L101)
- [StrategicSourcingService.php:199-205](file://app/Services/StrategicSourcingService.php#L199-L205)
- [rfq-analysis.blade.php:219-266](file://resources/views/suppliers/rfq-analysis.blade.php#L219-L266)