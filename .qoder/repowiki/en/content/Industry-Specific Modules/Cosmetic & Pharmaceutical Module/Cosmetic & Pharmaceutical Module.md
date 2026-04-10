# Cosmetic & Pharmaceutical Module

<cite>
**Referenced Files in This Document**
- [CosmeticBatchRecord.php](file://app/Models/CosmeticBatchRecord.php)
- [CosmeticFormula.php](file://app/Models/CosmeticFormula.php)
- [FormulaIngredient.php](file://app/Models/FormulaIngredient.php)
- [FormulaVersion.php](file://app/Models/FormulaVersion.php)
- [BatchQualityCheck.php](file://app/Models/BatchQualityCheck.php)
- [BatchReworkLog.php](file://app/Models/BatchReworkLog.php)
- [BatchRecall.php](file://app/Models/BatchRecall.php)
- [BatchController.php](file://app/Http/Controllers/Cosmetic/BatchController.php)
- [FormulaController.php](file://app/Http/Controllers/Cosmetic/FormulaController.php)
- [QCController.php](file://app/Http/Controllers/Cosmetic/QCController.php)
- [RegistrationController.php](file://app/Http/Controllers/Cosmetic/RegistrationController.php)
- [RegulatoryComplianceService.php](file://app/Services/RegulatoryComplianceService.php)
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
This document describes the Cosmetic & Pharmaceutical Module within the qalcuityERP system. It focuses on product formulation management, batch production tracking, quality control testing, regulatory compliance, expiration date management, ingredient tracking, and Good Manufacturing Practice (GMP) compliance. It also covers product registration processes, safety data sheet management, stability testing, batch release procedures, and regulatory reporting requirements. Finally, it addresses cosmetic labeling compliance, pharmaceutical quality assurance, and supply chain traceability for both industries.

## Project Structure
The module is organized around Laravel Eloquent models representing domain entities and controller actions orchestrating workflows. Key areas include:
- Formulation lifecycle: creation, approval, versioning, stability testing
- Batch lifecycle: creation, production, QC checks, rework, release, recall
- Quality assurance: test templates, test results, certificates of analysis (CoA), out-of-specification investigations
- Regulatory and compliance: registration tracking, safety data sheets, compliance reports
- Expiration and traceability: batch expiry calculation, status scoping, and audit-ready attributes

```mermaid
graph TB
subgraph "Formulation Management"
CF["CosmeticFormula"]
FI["FormulaIngredient"]
FV["FormulaVersion"]
ST["StabilityTest"]
end
subgraph "Batch Production"
CBR["CosmeticBatchRecord"]
BQC["BatchQualityCheck"]
BRL["BatchReworkLog"]
BR["BatchRecall"]
end
subgraph "Quality Assurance"
QCT["QCTestTemplate"]
QCR["QCTestResult"]
COA["CoaCertificate"]
OOS["OosInvestigation"]
end
subgraph "Regulatory & Compliance"
PR["ProductRegistration"]
SDS["SafetyDataSheet"]
RCS["RegulatoryComplianceService"]
end
CF --> FI
CF --> FV
CF --> ST
CBR --> BQC
CBR --> BRL
CBR --> BR
QCT --> QCR
QCR --> COA
QCR --> OOS
PR --> SDS
PR --> CF
CF --> CBR
CBR --> QCR
PR --> RCS
```

**Diagram sources**
- [CosmeticFormula.php:12-239](file://app/Models/CosmeticFormula.php#L12-L239)
- [FormulaIngredient.php:10-199](file://app/Models/FormulaIngredient.php#L10-L199)
- [FormulaVersion.php:10-105](file://app/Models/FormulaVersion.php#L10-L105)
- [CosmeticBatchRecord.php:12-312](file://app/Models/CosmeticBatchRecord.php#L12-L312)
- [BatchQualityCheck.php:10-218](file://app/Models/BatchQualityCheck.php#L10-L218)
- [BatchReworkLog.php:10-227](file://app/Models/BatchReworkLog.php#L10-L227)
- [BatchRecall.php:12-129](file://app/Models/BatchRecall.php#L12-L129)
- [QCController.php:13-301](file://app/Http/Controllers/Cosmetic/QCController.php#L13-L301)
- [RegistrationController.php:13-251](file://app/Http/Controllers/Cosmetic/RegistrationController.php#L13-L251)
- [RegulatoryComplianceService.php:17-581](file://app/Services/RegulatoryComplianceService.php#L17-L581)

**Section sources**
- [BatchController.php:12-355](file://app/Http/Controllers/Cosmetic/BatchController.php#L12-L355)
- [FormulaController.php:13-308](file://app/Http/Controllers/Cosmetic/FormulaController.php#L13-L308)
- [QCController.php:13-301](file://app/Http/Controllers/Cosmetic/QCController.php#L13-L301)
- [RegistrationController.php:13-251](file://app/Http/Controllers/Cosmetic/RegistrationController.php#L13-L251)
- [RegulatoryComplianceService.php:17-581](file://app/Services/RegulatoryComplianceService.php#L17-L581)

## Core Components
- Formulation lifecycle: creation, approval, versioning, stability testing, and readiness checks
- Batch lifecycle: creation, production, QC checkpoints, rework tracking, release gating, and recall management
- Quality assurance: standardized test templates, test execution, certificate generation, and OOS investigations
- Regulatory and compliance: product registration tracking, ingredient restrictions, safety data sheets, and compliance reporting
- Expiration and traceability: batch expiry calculation, status scoping, and audit-ready attributes

**Section sources**
- [CosmeticFormula.php:12-239](file://app/Models/CosmeticFormula.php#L12-L239)
- [FormulaIngredient.php:10-199](file://app/Models/FormulaIngredient.php#L10-L199)
- [FormulaVersion.php:10-105](file://app/Models/FormulaVersion.php#L10-L105)
- [CosmeticBatchRecord.php:12-312](file://app/Models/CosmeticBatchRecord.php#L12-L312)
- [BatchQualityCheck.php:10-218](file://app/Models/BatchQualityCheck.php#L10-L218)
- [BatchReworkLog.php:10-227](file://app/Models/BatchReworkLog.php#L10-L227)
- [BatchRecall.php:12-129](file://app/Models/BatchRecall.php#L12-L129)
- [QCController.php:13-301](file://app/Http/Controllers/Cosmetic/QCController.php#L13-L301)
- [RegistrationController.php:13-251](file://app/Http/Controllers/Cosmetic/RegistrationController.php#L13-L251)
- [RegulatoryComplianceService.php:17-581](file://app/Services/RegulatoryComplianceService.php#L17-L581)

## Architecture Overview
The module follows a layered MVC pattern with strong separation of concerns:
- Controllers orchestrate user actions and delegate to model/business logic
- Models encapsulate domain entities, relationships, validations, and helper methods
- Services provide cross-cutting capabilities (e.g., regulatory compliance)
- Views render dashboards and forms for batch, formula, QC, and registration workflows

```mermaid
classDiagram
class CosmeticFormula {
+string formula_code
+string formula_name
+string product_type
+float target_ph
+int shelf_life_months
+float batch_size
+string batch_unit
+float total_cost
+float cost_per_unit
+string status
+calculateTotalCost() float
+isReadyForProduction() bool
}
class FormulaIngredient {
+int product_id
+string inci_name
+string common_name
+string cas_number
+float quantity
+string unit
+float percentage
+string function
+string phase
+int sort_order
+getCostAttribute() float
+isConcentrationSafe() bool
}
class FormulaVersion {
+string version_number
+string changes_summary
+string reason_for_change
+isNewerThan(other) bool
}
class CosmeticBatchRecord {
+string batch_number
+date production_date
+date expiry_date
+float planned_quantity
+float actual_quantity
+float yield_percentage
+string status
+calculateYield() float
+isExpired() bool
+canBeReleased() bool
+release(userId) void
+reject(userId, notes) void
}
class BatchQualityCheck {
+string check_point
+string parameter
+float target_value
+float actual_value
+float lower_limit
+float upper_limit
+string result
+isWithinLimits() bool
+pass(userId, observations) void
+fail(userId, observations) void
}
class BatchReworkLog {
+string rework_code
+string reason
+string rework_action
+float quantity_before
+float quantity_after
+float loss_quantity
+string status
+calculateLoss() float
+complete(userId, notes) void
+fail(userId, notes) void
}
class BatchRecall {
+string recall_number
+string recall_reason
+string severity
+string status
+float total_units
+float units_returned
+float units_destroyed
+complete(notes) void
+cancel(notes) void
}
class QCTestTemplate {
+string template_name
+string test_category
+array test_parameters
+array acceptance_criteria
}
class QCTestResult {
+string test_code
+string test_name
+string test_category
+string sample_id
+date test_date
+array parameters
+string result
+complete(result, parameters, observations) void
+approve(userId) void
}
class CoaCertificate {
+string coa_number
+string status
+date issue_date
+date expiry_date
+generateFromBatch(batchId, userId) CoaCertificate
+approve(userId) void
}
class OosInvestigation {
+string oos_number
+string oos_type
+string severity
+string status
+updateRootCause(rootCause) void
+addCorrectiveAction(action) void
+addPreventiveAction(action) void
+complete(userId) void
}
class ProductRegistration {
+string registration_number
+string product_name
+string product_category
+string registration_type
+date expiry_date
+checkIngredientCompliance() array
+submit(userId) void
+approve(notifiedBy, approvalNumber) void
}
class SafetyDataSheet {
+string sds_number
+string status
+date issue_date
+date review_date
+activate() void
+createNewVersion() SafetyDataSheet
}
class RegulatoryComplianceService {
+createComplianceReport(reportData) ComplianceReport
+createBackup(backupData) BackupLog
+logMedicalRecordAccess(accessData) AuditTrail
+checkAccessPermission(user, patientId, action, reason) array
}
CosmeticFormula "1" --> "*" FormulaIngredient : "has many"
CosmeticFormula "1" --> "*" FormulaVersion : "has many"
CosmeticFormula "1" --> "*" StabilityTest : "has many"
CosmeticBatchRecord "1" --> "*" BatchQualityCheck : "has many"
CosmeticBatchRecord "1" --> "*" BatchReworkLog : "has many"
CosmeticBatchRecord "1" --> "*" BatchRecall : "has many"
QCTestTemplate "1" --> "*" QCTestResult : "generates"
QCTestResult "1" --> "1" CoaCertificate : "generates"
QCTestResult "1" --> "1" OosInvestigation : "triggers"
ProductRegistration "1" --> "*" SafetyDataSheet : "references"
ProductRegistration "1" --> "1" CosmeticFormula : "references"
```

**Diagram sources**
- [CosmeticFormula.php:12-239](file://app/Models/CosmeticFormula.php#L12-L239)
- [FormulaIngredient.php:10-199](file://app/Models/FormulaIngredient.php#L10-L199)
- [FormulaVersion.php:10-105](file://app/Models/FormulaVersion.php#L10-L105)
- [CosmeticBatchRecord.php:12-312](file://app/Models/CosmeticBatchRecord.php#L12-L312)
- [BatchQualityCheck.php:10-218](file://app/Models/BatchQualityCheck.php#L10-L218)
- [BatchReworkLog.php:10-227](file://app/Models/BatchReworkLog.php#L10-L227)
- [BatchRecall.php:12-129](file://app/Models/BatchRecall.php#L12-L129)
- [QCController.php:13-301](file://app/Http/Controllers/Cosmetic/QCController.php#L13-L301)
- [RegistrationController.php:13-251](file://app/Http/Controllers/Cosmetic/RegistrationController.php#L13-L251)
- [RegulatoryComplianceService.php:17-581](file://app/Services/RegulatoryComplianceService.php#L17-L581)

## Detailed Component Analysis

### Formulation Management
- Creation and metadata: name, type, brand, target pH, shelf life, batch size/unit, notes
- Ingredients: INCI/common names, CAS numbers, quantities, units, percentages, function, phase, sort order
- Costing: automatic total cost and per-unit cost calculation from ingredients
- Approval and versioning: status transitions, initial version creation upon approval
- Stability testing: initiation and completion with acceptance criteria
- Readiness checks: approval, ingredients presence, measured pH, passing stability test

```mermaid
sequenceDiagram
participant U as "User"
participant FC as "FormulaController"
participant FM as "CosmeticFormula"
participant FI as "FormulaIngredient"
participant FV as "FormulaVersion"
U->>FC : "POST /cosmetic/formulas"
FC->>FM : "create formula with metadata"
loop for each ingredient
FC->>FI : "create ingredient"
end
FC->>FM : "calculateTotalCost()"
FM-->>FC : "updated totals"
FC-->>U : "redirect to formula show"
U->>FC : "POST /cosmetic/formulas/{id}/status"
FC->>FM : "set status=approved"
FC->>FV : "create v1.0 version"
FC-->>U : "success message"
```

**Diagram sources**
- [FormulaController.php:13-308](file://app/Http/Controllers/Cosmetic/FormulaController.php#L13-L308)
- [CosmeticFormula.php:12-239](file://app/Models/CosmeticFormula.php#L12-L239)
- [FormulaIngredient.php:10-199](file://app/Models/FormulaIngredient.php#L10-L199)
- [FormulaVersion.php:10-105](file://app/Models/FormulaVersion.php#L10-L105)

**Section sources**
- [FormulaController.php:13-308](file://app/Http/Controllers/Cosmetic/FormulaController.php#L13-L308)
- [CosmeticFormula.php:12-239](file://app/Models/CosmeticFormula.php#L12-L239)
- [FormulaIngredient.php:10-199](file://app/Models/FormulaIngredient.php#L10-L199)
- [FormulaVersion.php:10-105](file://app/Models/FormulaVersion.php#L10-L105)

### Batch Production Tracking
- Creation: batch number generation, production date, expiry date, planned quantity
- Status transitions: draft → in_progress → qc_pending → released/rejected/on_hold
- Yield calculation: actual vs planned quantity
- Expiration: expiry date checks and days-to-expiry attribute
- Release gating: requires actual quantity, all QC passed, no open rework

```mermaid
flowchart TD
Start(["Create Batch"]) --> SetDraft["Set Status: Draft"]
SetDraft --> StartProd["Set Status: In Progress<br/>Record Produced By"]
StartProd --> RunQC["Add QC Checks<br/>Auto-pass/fail by limits"]
RunQC --> AllPassed{"All QC Passed?"}
AllPassed --> |No| Hold["Set Status: On Hold / Rejected"]
AllPassed --> |Yes| OpenRework{"Open Rework Exists?"}
OpenRework --> |Yes| Hold
OpenRework --> |No| Release["Release Batch<br/>Record QC Completed At"]
Release --> End(["Done"])
Hold --> End
```

**Diagram sources**
- [BatchController.php:12-355](file://app/Http/Controllers/Cosmetic/BatchController.php#L12-L355)
- [CosmeticBatchRecord.php:12-312](file://app/Models/CosmeticBatchRecord.php#L12-L312)
- [BatchQualityCheck.php:10-218](file://app/Models/BatchQualityCheck.php#L10-L218)
- [BatchReworkLog.php:10-227](file://app/Models/BatchReworkLog.php#L10-L227)

**Section sources**
- [BatchController.php:12-355](file://app/Http/Controllers/Cosmetic/BatchController.php#L12-L355)
- [CosmeticBatchRecord.php:12-312](file://app/Models/CosmeticBatchRecord.php#L12-L312)

### Quality Control Testing
- Templates: reusable test categories, parameters, acceptance criteria
- Results: execution with parameters, pass/fail/inconclusive, auto-OOS creation on failure
- Certificates of Analysis: batch-derived CoA generation and approval
- OOS investigations: root cause, corrective/preventive actions, completion

```mermaid
sequenceDiagram
participant U as "User"
participant QC as "QCController"
participant QCT as "QCTestTemplate"
participant QCR as "QCTestResult"
participant COA as "CoaCertificate"
participant OOS as "OosInvestigation"
U->>QC : "Create Test Template"
QC->>QCT : "store template"
U->>QC : "Create Test Result"
QC->>QCR : "store draft"
U->>QC : "Complete Test"
QC->>QCR : "set result + parameters"
alt failed
QC->>OOS : "auto-create investigation"
end
U->>QC : "Approve Test"
QC->>QCR : "approve"
U->>QC : "Generate CoA"
QC->>COA : "generate from batch"
QC-->>U : "success"
```

**Diagram sources**
- [QCController.php:13-301](file://app/Http/Controllers/Cosmetic/QCController.php#L13-L301)
- [BatchQualityCheck.php:10-218](file://app/Models/BatchQualityCheck.php#L10-L218)

**Section sources**
- [QCController.php:13-301](file://app/Http/Controllers/Cosmetic/QCController.php#L13-L301)

### Regulatory Compliance and Product Registration
- Product registration: registration number, product name, category, type, expiry, submission/approval
- Ingredient restrictions: banned/restricted/limited lists with max limits and regulation references
- Safety data sheets: hazard statements, precautionary measures, first aid/fire fighting, handling/storage, activation and versioning
- Compliance reporting: HIPAA/Permenkes checks, audit trails, backups, disaster recovery logging

```mermaid
flowchart TD
RegStart["Create Registration"] --> AttachFormula["Attach Formula (optional)"]
AttachFormula --> AutoCheck["Auto-check Ingredient Compliance"]
AutoCheck --> Issues{"Compliant?"}
Issues --> |No| Warn["Warn on Issues"]
Issues --> |Yes| Submit["Submit for Review"]
Submit --> Approve["Approve Registration<br/>Issue Approval Number"]
Approve --> SDS["Create/Activate SDS"]
SDS --> Report["Generate Compliance Reports"]
Report --> End(["Done"])
Warn --> End
```

**Diagram sources**
- [RegistrationController.php:13-251](file://app/Http/Controllers/Cosmetic/RegistrationController.php#L13-L251)
- [RegulatoryComplianceService.php:17-581](file://app/Services/RegulatoryComplianceService.php#L17-L581)

**Section sources**
- [RegistrationController.php:13-251](file://app/Http/Controllers/Cosmetic/RegistrationController.php#L13-L251)
- [RegulatoryComplianceService.php:17-581](file://app/Services/RegulatoryComplianceService.php#L17-L581)

### Expiration Date Management and Traceability
- Batch expiry: expiry date field, isExpired(), days until expiry
- Status scoping: in_progress, qc_pending, released, expired, expiring soon
- Batch recall: severity levels, return/destroyed metrics, completion/cancellation
- Audit-ready attributes: timestamps, user references, status labels/colors

```mermaid
flowchart TD
Scan(["Scan Batch"]) --> CheckExp{"Expired?"}
CheckExp --> |Yes| Alert["Flag Expired Batch"]
CheckExp --> |No| Days["Show Days Until Expiry"]
Days --> Recall{"Recall Needed?"}
Recall --> |Yes| Initiate["Initiate Recall<br/>Severity + Regions"]
Recall --> |No| Track["Continue Monitoring"]
Initiate --> Complete["Complete/Cancel Recall"]
Track --> Complete
Complete --> End(["Done"])
```

**Diagram sources**
- [CosmeticBatchRecord.php:12-312](file://app/Models/CosmeticBatchRecord.php#L12-L312)
- [BatchRecall.php:12-129](file://app/Models/BatchRecall.php#L12-L129)

**Section sources**
- [CosmeticBatchRecord.php:12-312](file://app/Models/CosmeticBatchRecord.php#L12-L312)
- [BatchRecall.php:12-129](file://app/Models/BatchRecall.php#L12-L129)

### Ingredient Tracking and Safety
- Ingredient functions/phases: emollient, preservative, active, fragrance, emulsifier, thickener, humectant, surfactant, colorant, solvent, pH adjuster, antioxidant
- Concentration safety thresholds per function
- CAS number linking for traceability
- Cost attribution via linked products

**Section sources**
- [FormulaIngredient.php:10-199](file://app/Models/FormulaIngredient.php#L10-L199)

## Dependency Analysis
- Controllers depend on models for persistence and business logic
- Models encapsulate relationships, validations, and helper methods
- Services provide cross-domain compliance and reporting capabilities
- Tight coupling is minimized through clear method contracts and Eloquent relationships

```mermaid
graph LR
BC["BatchController"] --> CBR["CosmeticBatchRecord"]
BC --> BQC["BatchQualityCheck"]
BC --> BRL["BatchReworkLog"]
FC["FormulaController"] --> CF["CosmeticFormula"]
FC --> FI["FormulaIngredient"]
FC --> FV["FormulaVersion"]
QC["QCController"] --> QCT["QCTestTemplate"]
QC --> QCR["QCTestResult"]
QC --> COA["CoaCertificate"]
QC --> OOS["OosInvestigation"]
RC["RegistrationController"] --> PR["ProductRegistration"]
RC --> SDS["SafetyDataSheet"]
RCS["RegulatoryComplianceService"] --> PR
RCS --> QCR
```

**Diagram sources**
- [BatchController.php:12-355](file://app/Http/Controllers/Cosmetic/BatchController.php#L12-L355)
- [FormulaController.php:13-308](file://app/Http/Controllers/Cosmetic/FormulaController.php#L13-L308)
- [QCController.php:13-301](file://app/Http/Controllers/Cosmetic/QCController.php#L13-L301)
- [RegistrationController.php:13-251](file://app/Http/Controllers/Cosmetic/RegistrationController.php#L13-L251)
- [RegulatoryComplianceService.php:17-581](file://app/Services/RegulatoryComplianceService.php#L17-L581)

**Section sources**
- [BatchController.php:12-355](file://app/Http/Controllers/Cosmetic/BatchController.php#L12-L355)
- [FormulaController.php:13-308](file://app/Http/Controllers/Cosmetic/FormulaController.php#L13-L308)
- [QCController.php:13-301](file://app/Http/Controllers/Cosmetic/QCController.php#L13-L301)
- [RegistrationController.php:13-251](file://app/Http/Controllers/Cosmetic/RegistrationController.php#L13-L251)
- [RegulatoryComplianceService.php:17-581](file://app/Services/RegulatoryComplianceService.php#L17-L581)

## Performance Considerations
- Use pagination for large datasets (e.g., batches, tests, registrations)
- Leverage Eloquent relationships with eager loading to avoid N+1 queries
- Apply scopes for filtering and sorting to reduce controller logic
- Cache frequently accessed configuration data (e.g., ingredient restrictions)
- Index database columns used in filters (status, formula_id, batch_number, test_code)

## Troubleshooting Guide
Common issues and resolutions:
- Batch cannot be released: verify actual quantity is recorded, all QC checks passed, and no open rework
- QC result not calculated: ensure limits and actual values are set; pass/fail determined automatically when limits are present
- Formula cost mismatch: recalculate total cost after ingredient updates
- SDS not activating: ensure proper status transitions and versioning
- Compliance report errors: confirm required checks and data availability for selected framework

**Section sources**
- [CosmeticBatchRecord.php:234-257](file://app/Models/CosmeticBatchRecord.php#L234-L257)
- [BatchQualityCheck.php:114-123](file://app/Models/BatchQualityCheck.php#L114-L123)
- [FormulaController.php:138-140](file://app/Http/Controllers/Cosmetic/FormulaController.php#L138-L140)
- [RegistrationController.php:228-249](file://app/Http/Controllers/Cosmetic/RegistrationController.php#L228-L249)
- [RegulatoryComplianceService.php:284-297](file://app/Services/RegulatoryComplianceService.php#L284-L297)

## Conclusion
The Cosmetic & Pharmaceutical Module provides a robust foundation for managing formulations, batch production, quality control, regulatory compliance, and traceability. Its modular design supports scalability, maintainability, and adherence to industry standards, enabling efficient operations across both cosmetic and pharmaceutical domains.