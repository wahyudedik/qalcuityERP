# Farm Plot Management

<cite>
**Referenced Files in This Document**
- [FarmPlot.php](file://app/Models/FarmPlot.php)
- [FarmPlotActivity.php](file://app/Models/FarmPlotActivity.php)
- [FarmPlotController.php](file://app/Http/Controllers/FarmPlotController.php)
- [FarmAnalyticsService.php](file://app/Services/FarmAnalyticsService.php)
- [FarmTools.php](file://app/Services/ERP/FarmTools.php)
- [CropCycle.php](file://app/Models/CropCycle.php)
- [HarvestLog.php](file://app/Models/HarvestLog.php)
- [HarvestLogGrade.php](file://app/Models/HarvestLogGrade.php)
- [HarvestLogWorker.php](file://app/Models/HarvestLogWorker.php)
- [IrrigationSchedule.php](file://app/Models/IrrigationSchedule.php)
- [PestDetection.php](file://app/Models/PestDetection.php)
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
This document describes the farm plot management system, focusing on plot registration, classification, spatial organization, status tracking across production phases, activity logging, analytics, and operational integrations such as crop cycles, harvest logging, irrigation scheduling, and pest detection. It synthesizes the model and service layers to present a practical guide for managing agricultural operations within the platform.

## Project Structure
The farm module centers around domain models for plots, activities, cycles, and harvests, supported by a controller for CRUD and analytics, and a service layer that orchestrates farm operations and exposes a natural-language API via a Farm Tools executor.

```mermaid
graph TB
subgraph "HTTP Layer"
Ctl["FarmPlotController"]
end
subgraph "Domain Models"
FP["FarmPlot"]
FPA["FarmPlotActivity"]
CC["CropCycle"]
HL["HarvestLog"]
HLG["HarvestLogGrade"]
HLL["HarvestLogWorker"]
IS["IrrigationSchedule"]
PD["PestDetection"]
end
subgraph "Services"
FAS["FarmAnalyticsService"]
FT["FarmTools"]
end
Ctl --> FP
Ctl --> FPA
Ctl --> FAS
Ctl --> FT
FP --> FPA
FP --> CC
CC --> HL
HL --> HLG
HL --> HLL
CC --> IS
CC --> PD
FT --> FP
FT --> FPA
FT --> CC
FT --> HL
FT --> FAS
```

**Diagram sources**
- [FarmPlotController.php:10-198](file://app/Http/Controllers/FarmPlotController.php#L10-L198)
- [FarmPlot.php:11-104](file://app/Models/FarmPlot.php#L11-L104)
- [FarmPlotActivity.php:10-48](file://app/Models/FarmPlotActivity.php#L10-L48)
- [FarmAnalyticsService.php:11-160](file://app/Services/FarmAnalyticsService.php#L11-L160)
- [FarmTools.php:9-1127](file://app/Services/ERP/FarmTools.php#L9-L1127)
- [CropCycle.php:11-96](file://app/Models/CropCycle.php#L11-L96)
- [HarvestLog.php:11-80](file://app/Models/HarvestLog.php#L11-L80)
- [HarvestLogGrade.php:8-21](file://app/Models/HarvestLogGrade.php#L8-L21)
- [HarvestLogWorker.php:8-20](file://app/Models/HarvestLogWorker.php#L8-L20)
- [IrrigationSchedule.php:11-94](file://app/Models/IrrigationSchedule.php#L11-L94)
- [PestDetection.php:10-74](file://app/Models/PestDetection.php#L10-L74)

**Section sources**
- [FarmPlotController.php:10-198](file://app/Http/Controllers/FarmPlotController.php#L10-L198)
- [FarmPlot.php:11-104](file://app/Models/FarmPlot.php#L11-L104)
- [FarmPlotActivity.php:10-48](file://app/Models/FarmPlotActivity.php#L10-L48)
- [FarmAnalyticsService.php:11-160](file://app/Services/FarmAnalyticsService.php#L11-L160)
- [FarmTools.php:9-1127](file://app/Services/ERP/FarmTools.php#L9-L1127)
- [CropCycle.php:11-96](file://app/Models/CropCycle.php#L11-L96)
- [HarvestLog.php:11-80](file://app/Models/HarvestLog.php#L11-L80)
- [HarvestLogGrade.php:8-21](file://app/Models/HarvestLogGrade.php#L8-L21)
- [HarvestLogWorker.php:8-20](file://app/Models/HarvestLogWorker.php#L8-L20)
- [IrrigationSchedule.php:11-94](file://app/Models/IrrigationSchedule.php#L11-L94)
- [PestDetection.php:10-74](file://app/Models/PestDetection.php#L10-L74)

## Core Components
- FarmPlot: Central entity representing a plot with attributes for identification, area, location, ownership, current crop, status, and timestamps. Provides helpers for status labels/colors, days calculations, and cost/productivity metrics aggregation.
- FarmPlotActivity: Records daily farming operations with typed activity categories, input usage, costs, and harvest quantities/grades.
- CropCycle: Tracks planned and actual phases of a cropping season, enabling progress calculation and synchronization with plot status.
- HarvestLog: Captures detailed harvest events including grades, workers, labor/transport costs, and derived metrics.
- Analytics Service: Aggregates costs, yields, and productivity metrics across plots and cycles, including HPP per kg, yield per hectare, and cost per hectare.
- Farm Tools: Natural language interface for plot creation, status updates, activity recording, cycle management, and analytics retrieval.

**Section sources**
- [FarmPlot.php:11-104](file://app/Models/FarmPlot.php#L11-L104)
- [FarmPlotActivity.php:10-48](file://app/Models/FarmPlotActivity.php#L10-L48)
- [CropCycle.php:11-96](file://app/Models/CropCycle.php#L11-L96)
- [HarvestLog.php:11-80](file://app/Models/HarvestLog.php#L11-L80)
- [FarmAnalyticsService.php:11-160](file://app/Services/FarmAnalyticsService.php#L11-L160)
- [FarmTools.php:9-1127](file://app/Services/ERP/FarmTools.php#L9-L1127)

## Architecture Overview
The system follows a layered architecture:
- HTTP layer: Controller handles requests, validations, and orchestrates model updates and analytics.
- Domain models: Encapsulate business rules, relationships, and computed metrics.
- Services: Provide cross-cutting operations such as analytics and natural language farm command execution.

```mermaid
classDiagram
class FarmPlot {
+string code
+string name
+float area_size
+string area_unit
+string location
+string soil_type
+string irrigation_type
+string ownership
+float rent_cost
+string current_crop
+string status
+date planted_at
+date expected_harvest
+bool is_active
+string notes
+statusLabel() string
+statusColor() string
+daysSincePlanted() int?
+daysUntilHarvest() int?
+isHarvestOverdue() bool
+totalCost() float
+totalHarvest() float
+costPerUnit() float?
}
class FarmPlotActivity {
+string activity_type
+date date
+string description
+string input_product
+float input_quantity
+string input_unit
+float cost
+float harvest_qty
+string harvest_unit
+string harvest_grade
+string notes
+activityLabel() string
}
class CropCycle {
+string crop_name
+string variety
+float area_hectares
+date planting_date
+date expected_harvest_date
+date actual_harvest_date
+string growth_stage
+int days_since_planted
+int days_to_harvest
+float estimated_yield_tons
+float actual_yield_tons
+string status
+updateGrowthStage() void
}
class HarvestLog {
+date harvest_date
+string crop_name
+float total_qty
+string unit
+float reject_qty
+float moisture_pct
+string storage_location
+float labor_cost
+float transport_cost
+netQty() float
+rejectPercent() float
+totalCost() float
+costPerUnit() float?
+estimatedRevenue() float
}
class FarmAnalyticsService {
+plotCostBreakdown(plotId, cycleId?) array
+costPerHectare(plot, cycleId?) float
+hppPerKg(plot, cycleId?) float?
+yieldPerHectare(plot, cycleId?) float
+comparePlots(tenantId) array
+monthlyCostTrend(plotId, months) array
}
class FarmTools {
+definitions() array
+createFarmPlot(args) array
+getFarmPlots(args) array
+updatePlotStatus(args) array
+recordFarmActivity(args) array
+startCropCycle(args) array
+getCropCycles(args) array
+advanceCropPhase(args) array
+logHarvest(args) array
+getFarmCostAnalysis(args) array
}
FarmPlot "1" --> "*" FarmPlotActivity : "has many"
FarmPlot "1" --> "*" CropCycle : "has many"
CropCycle "1" --> "*" HarvestLog : "produces"
HarvestLog "1" --> "*" HarvestLogGrade : "has many"
HarvestLog "1" --> "*" HarvestLogWorker : "has many"
FarmTools --> FarmPlot
FarmTools --> FarmPlotActivity
FarmTools --> CropCycle
FarmTools --> HarvestLog
FarmTools --> FarmAnalyticsService
```

**Diagram sources**
- [FarmPlot.php:11-104](file://app/Models/FarmPlot.php#L11-L104)
- [FarmPlotActivity.php:10-48](file://app/Models/FarmPlotActivity.php#L10-L48)
- [CropCycle.php:11-96](file://app/Models/CropCycle.php#L11-L96)
- [HarvestLog.php:11-80](file://app/Models/HarvestLog.php#L11-L80)
- [FarmAnalyticsService.php:11-160](file://app/Services/FarmAnalyticsService.php#L11-L160)
- [FarmTools.php:9-1127](file://app/Services/ERP/FarmTools.php#L9-L1127)

## Detailed Component Analysis

### Plot Registration and Classification
- Registration: Plots are created with unique codes, names, area, units, location, soil type, irrigation type, ownership, and optional notes. Default status is idle and activation flag is set to true.
- Classification: Status values include idle, preparing, planted, growing, ready_harvest, harvesting, and post_harvest. Each status has localized label and color metadata for UI rendering.
- Spatial organization: Area size and unit are stored, enabling productivity metrics. Ownership and rent cost are captured for financial modeling.

```mermaid
flowchart TD
Start(["Create Plot"]) --> Validate["Validate inputs<br/>code, name, area_size, area_unit"]
Validate --> UniqueCode{"Unique code?"}
UniqueCode --> |No| Error["Return error: code exists"]
UniqueCode --> |Yes| Persist["Persist FarmPlot<br/>status= idle, is_active=true"]
Persist --> Log["Record activity log"]
Log --> Done(["Success"])
```

**Diagram sources**
- [FarmPlotController.php:52-81](file://app/Http/Controllers/FarmPlotController.php#L52-L81)
- [FarmPlot.php:14-30](file://app/Models/FarmPlot.php#L14-L30)

**Section sources**
- [FarmPlotController.php:52-81](file://app/Http/Controllers/FarmPlotController.php#L52-L81)
- [FarmPlot.php:14-50](file://app/Models/FarmPlot.php#L14-L50)

### Status Tracking Across Production Phases
- Status lifecycle: The system tracks idle → preparing → planted → growing → ready_harvest → harvesting → post_harvest, with helpers to compute days since planted and until harvest, and to detect overdue harvests.
- Automatic transitions: Activities such as soil preparation, planting, and harvesting can trigger status updates. Crop cycles also drive phase transitions and plot status synchronization.

```mermaid
stateDiagram-v2
[*] --> Idle
Idle --> Preparing : "soil_prep activity"
Preparing --> Planted : "planting activity"
Planted --> Growing : "phase : vegetative/generative"
Growing --> ReadyHarvest : "phase : pre-harvest"
ReadyHarvest --> Harvesting : "harvesting activity"
Harvesting --> PostHarvest : "phase : post_harvest"
PostHarvest --> Idle : "phase : completed"
```

**Diagram sources**
- [FarmPlot.php:32-50](file://app/Models/FarmPlot.php#L32-L50)
- [FarmPlotController.php:180-192](file://app/Http/Controllers/FarmPlotController.php#L180-L192)
- [FarmTools.php:557-563](file://app/Services/ERP/FarmTools.php#L557-L563)

**Section sources**
- [FarmPlot.php:32-83](file://app/Models/FarmPlot.php#L32-L83)
- [FarmPlotController.php:180-192](file://app/Http/Controllers/FarmPlotController.php#L180-L192)
- [FarmTools.php:557-563](file://app/Services/ERP/FarmTools.php#L557-L563)

### Plot Activity Logging
- Activity types: Planting, fertilizing, spraying, watering, weeding, pruning, harvesting, soil preparation, and other activities. Each activity stores date, description, input product/quantity/unit, cost, and harvest quantity/grade.
- Auto-status updates: Certain activities automatically adjust plot status and planting date.
- Aggregation: Plots expose total cost and total harvest quantities, and derived cost-per-unit metric.

```mermaid
sequenceDiagram
participant U as "User"
participant C as "FarmPlotController"
participant A as "FarmPlotActivity"
participant P as "FarmPlot"
U->>C : "storeActivity(plot, payload)"
C->>A : "create(activity)"
alt "activity_type in {soil_prep, planting, harvesting}"
C->>P : "update(status, planted_at if applicable)"
end
C-->>U : "success message"
```

**Diagram sources**
- [FarmPlotController.php:155-196](file://app/Http/Controllers/FarmPlotController.php#L155-L196)
- [FarmPlotActivity.php:13-46](file://app/Models/FarmPlotActivity.php#L13-L46)
- [FarmPlot.php:85-102](file://app/Models/FarmPlot.php#L85-L102)

**Section sources**
- [FarmPlotActivity.php:13-46](file://app/Models/FarmPlotActivity.php#L13-L46)
- [FarmPlotController.php:155-196](file://app/Http/Controllers/FarmPlotController.php#L155-L196)
- [FarmPlot.php:85-102](file://app/Models/FarmPlot.php#L85-L102)

### Soil Analysis Integration, Crop Rotation Planning, and Field Preparation Protocols
- Soil and field data: Plots capture soil type and irrigation type, enabling tailored field preparation and water management.
- Crop rotation: Planned via CropCycle entries with crop name, season, and target yield. The system supports multiple cycles per plot and tracks actual vs. planned dates.
- Field preparation: Logged as dedicated activity type and can trigger status transitions.

```mermaid
flowchart TD
Plan["Start CropCycle<br/>crop_name, season, plan_*"] --> Prep["Soil prep activity"]
Prep --> Plant["Planting activity"]
Plant --> Grow["Growing phases"]
Grow --> Harvest["Harvest logging"]
Harvest --> Post["Post-harvest phase"]
Post --> Complete["Mark cycle completed"]
```

**Diagram sources**
- [FarmTools.php:458-496](file://app/Services/ERP/FarmTools.php#L458-L496)
- [FarmTools.php:532-575](file://app/Services/ERP/FarmTools.php#L532-L575)
- [FarmPlotActivity.php:29-39](file://app/Models/FarmPlotActivity.php#L29-L39)

**Section sources**
- [FarmTools.php:458-496](file://app/Services/ERP/FarmTools.php#L458-L496)
- [FarmTools.php:532-575](file://app/Services/ERP/FarmTools.php#L532-L575)
- [FarmPlotActivity.php:29-39](file://app/Models/FarmPlotActivity.php#L29-L39)

### Infrastructure Tracking and Environmental Monitoring
- Irrigation scheduling: Crop cycles maintain schedules with frequency, timing, duration, and water volume, supporting automated reminders and usage tracking.
- Pest detection: Crops can be monitored for pests/diseases with severity levels, treatment recommendations, and status tracking.

```mermaid
classDiagram
class IrrigationSchedule {
+string schedule_type
+time irrigation_time
+int duration_minutes
+string frequency
+array custom_days
+float water_volume_liters
+bool is_active
+shouldIrrigateToday() bool
+recordIrrigation(duration, waterUsed) void
}
class PestDetection {
+string pest_name
+string disease_name
+float confidence_score
+string severity
+bool pest_detected
+bool disease_detected
+markAsTreated() void
+markAsResolved() void
}
CropCycle "1" --> "*" IrrigationSchedule : "has many"
CropCycle "1" --> "*" PestDetection : "has many"
```

**Diagram sources**
- [IrrigationSchedule.php:11-94](file://app/Models/IrrigationSchedule.php#L11-L94)
- [PestDetection.php:10-74](file://app/Models/PestDetection.php#L10-L74)

**Section sources**
- [IrrigationSchedule.php:61-83](file://app/Models/IrrigationSchedule.php#L61-L83)
- [PestDetection.php:50-73](file://app/Models/PestDetection.php#L50-L73)

### Analytics and Productivity Metrics
- Cost breakdown: By activity type, including total cost and percentage contribution.
- Productivity metrics: Cost per hectare, yield per hectare, and HPP per kg (cost per kilogram harvested), with fallbacks to harvest logs or activity records.
- Comparative analysis: Side-by-side comparison across plots with rankings by HPP.

```mermaid
flowchart TD
Input["Plot ID (+ optional cycle)"] --> Breakdown["plotCostBreakdown"]
Input --> CostHa["costPerHectare"]
Input --> HPP["hppPerKg"]
Input --> YieldHa["yieldPerHectare"]
Compare["comparePlots(tenantId)"] --> Rank["Sort by HPP"]
Rank --> Summary["Totals & averages"]
```

**Diagram sources**
- [FarmAnalyticsService.php:16-158](file://app/Services/FarmAnalyticsService.php#L16-L158)

**Section sources**
- [FarmAnalyticsService.php:16-158](file://app/Services/FarmAnalyticsService.php#L16-L158)

### Natural Language Farm Operations (FarmTools)
- Definitions: JSON schema exposing commands for plot creation, status updates, activity recording, cycle management, and analytics.
- Executors: Implement the commands, validate inputs, persist data, and return formatted messages with contextual summaries.

```mermaid
sequenceDiagram
participant User as "User"
participant FT as "FarmTools"
participant FP as "FarmPlot"
participant FPA as "FarmPlotActivity"
participant CC as "CropCycle"
participant HL as "HarvestLog"
User->>FT : "record_farm_activity(args)"
FT->>FP : "lookup by tenant + code"
FT->>FPA : "create(activity)"
FT->>FP : "auto-update status if needed"
FT-->>User : "formatted success message"
```

**Diagram sources**
- [FarmTools.php:67-86](file://app/Services/ERP/FarmTools.php#L67-L86)
- [FarmTools.php:409-454](file://app/Services/ERP/FarmTools.php#L409-L454)

**Section sources**
- [FarmTools.php:13-305](file://app/Services/ERP/FarmTools.php#L13-L305)
- [FarmTools.php:409-454](file://app/Services/ERP/FarmTools.php#L409-L454)

## Dependency Analysis
- Controller depends on models and services for persistence, validation, and analytics.
- Models encapsulate relationships and computed metrics, reducing duplication across layers.
- FarmTools acts as a facade for orchestration, delegating to models and services while providing a unified command surface.

```mermaid
graph LR
Ctl["FarmPlotController"] --> FP["FarmPlot"]
Ctl --> FPA["FarmPlotActivity"]
Ctl --> FAS["FarmAnalyticsService"]
Ctl --> FT["FarmTools"]
FT --> FP
FT --> FPA
FT --> CC["CropCycle"]
FT --> HL["HarvestLog"]
FT --> FAS
```

**Diagram sources**
- [FarmPlotController.php:10-198](file://app/Http/Controllers/FarmPlotController.php#L10-L198)
- [FarmTools.php:9-1127](file://app/Services/ERP/FarmTools.php#L9-L1127)

**Section sources**
- [FarmPlotController.php:10-198](file://app/Http/Controllers/FarmPlotController.php#L10-L198)
- [FarmTools.php:9-1127](file://app/Services/ERP/FarmTools.php#L9-L1127)

## Performance Considerations
- Aggregation queries: Use database-level aggregations (sums, counts, groupings) to avoid loading unnecessary rows.
- Indexing: Ensure tenant_id, code, and foreign keys are indexed for fast lookups.
- Pagination: Controllers already paginate lists; keep page sizes reasonable for large datasets.
- Computed metrics: Prefer storing derived metrics (e.g., total cost, total harvest) at the plot level to reduce repeated joins.

## Troubleshooting Guide
- Duplicate plot code: Creation fails if the code is not unique for the tenant. Resolve by changing the code.
- Not found errors: Updating status or recording activities requires an existing plot; verify plot code and tenant association.
- Overdue harvests: Use the plot helper to detect overdue status and take corrective actions.
- Inconsistent status: Activities and crop cycle phases are designed to synchronize plot status; check recent transitions and cycle phases.

**Section sources**
- [FarmPlotController.php:69-71](file://app/Http/Controllers/FarmPlotController.php#L69-L71)
- [FarmPlot.php:77-83](file://app/Models/FarmPlot.php#L77-L83)
- [FarmTools.php:389-391](file://app/Services/ERP/FarmTools.php#L389-L391)

## Conclusion
The farm plot management system integrates plot registration, status tracking, activity logging, analytics, and operational workflows. It supports structured crop cycle management, detailed harvest accounting, and operational insights through natural language commands. The modular design enables scalability and maintainability while keeping the user-facing operations straightforward.