# Crop Production Management

<cite>
**Referenced Files in This Document**
- [CropCycle.php](file://app/Models/CropCycle.php)
- [FarmPlot.php](file://app/Models/FarmPlot.php)
- [HarvestLog.php](file://app/Models/HarvestLog.php)
- [IrrigationLog.php](file://app/Models/IrrigationLog.php)
- [IrrigationSchedule.php](file://app/Models/IrrigationSchedule.php)
- [PestDetection.php](file://app/Models/PestDetection.php)
- [WeatherData.php](file://app/Models/WeatherData.php)
- [FarmAnalyticsService.php](file://app/Services/FarmAnalyticsService.php)
- [IrrigationAutomationService.php](file://app/Services/IrrigationAutomationService.php)
- [PestDetectionService.php](file://app/Services/PestDetectionService.php)
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
This document describes the Crop Production Management capabilities present in the codebase. It focuses on crop cycle planning, farm plot management, harvest operations, irrigation scheduling, pest and disease monitoring, weather-driven insights, analytics, and operational efficiency. The system models core entities such as Crop Cycle, Farm Plot, Harvest Log, Irrigation Schedule/Log, Pest Detection, and Weather Data, and exposes analytical services for cost, yield, and performance benchmarking. It also outlines automation-ready patterns for irrigation and pest detection.

## Project Structure
The relevant domain is organized around Eloquent models and service classes:
- Models define data structures, relationships, computed attributes, and helper methods for planning, monitoring, and reporting.
- Services encapsulate analytics and automation logic to support decision-making and operational workflows.

```mermaid
graph TB
subgraph "Models"
CC["CropCycle"]
FP["FarmPlot"]
HL["HarvestLog"]
IS["IrrigationSchedule"]
IL["IrrigationLog"]
PD["PestDetection"]
WD["WeatherData"]
end
subgraph "Services"
FAS["FarmAnalyticsService"]
IAS["IrrigationAutomationService"]
PDS["PestDetectionService"]
end
FP --> CC
CC --> IS
IS --> IL
CC --> PD
FP --> HL
WD --> FAS
WD --> IAS
WD --> PDS
```

**Diagram sources**
- [FarmPlot.php:11-104](file://app/Models/FarmPlot.php#L11-L104)
- [CropCycle.php:11-96](file://app/Models/CropCycle.php#L11-L96)
- [HarvestLog.php:11-80](file://app/Models/HarvestLog.php#L11-L80)
- [IrrigationSchedule.php:11-94](file://app/Models/IrrigationSchedule.php#L11-L94)
- [IrrigationLog.php:10-39](file://app/Models/IrrigationLog.php#L10-L39)
- [PestDetection.php:10-74](file://app/Models/PestDetection.php#L10-L74)
- [WeatherData.php:16-194](file://app/Models/WeatherData.php#L16-L194)
- [FarmAnalyticsService.php:11-160](file://app/Services/FarmAnalyticsService.php#L11-L160)
- [IrrigationAutomationService.php](file://app/Services/IrrigationAutomationService.php)
- [PestDetectionService.php](file://app/Services/PestDetectionService.php)

**Section sources**
- [FarmPlot.php:11-104](file://app/Models/FarmPlot.php#L11-L104)
- [CropCycle.php:11-96](file://app/Models/CropCycle.php#L11-L96)
- [HarvestLog.php:11-80](file://app/Models/HarvestLog.php#L11-L80)
- [IrrigationSchedule.php:11-94](file://app/Models/IrrigationSchedule.php#L11-L94)
- [IrrigationLog.php:10-39](file://app/Models/IrrigationLog.php#L10-L39)
- [PestDetection.php:10-74](file://app/Models/PestDetection.php#L10-L74)
- [WeatherData.php:16-194](file://app/Models/WeatherData.php#L16-L194)
- [FarmAnalyticsService.php:11-160](file://app/Services/FarmAnalyticsService.php#L11-L160)

## Core Components
- Crop Cycle: Tracks planting, growth stages, expected/actual harvest dates, and progress metrics.
- Farm Plot: Manages plot metadata, status lifecycle, and aggregated costs/yields.
- Harvest Log: Captures harvest quantities, rejects, moisture, labor/transport costs, and generates revenue estimates.
- Irrigation Schedule/Log: Defines scheduled irrigation events, frequency, and records actual water usage and timing.
- Pest Detection: Stores AI-driven pest/disease observations, severity, and treatment status.
- Weather Data: Provides current and forecast weather metrics and farming suitability/recommendations.
- Analytics Service: Computes cost breakdowns, cost-per-hectare, HPP per kg, yield per hectare, comparisons, and trends.
- Automation Services: Offer hooks for irrigation scheduling and pest detection workflows.

**Section sources**
- [CropCycle.php:15-96](file://app/Models/CropCycle.php#L15-L96)
- [FarmPlot.php:14-103](file://app/Models/FarmPlot.php#L14-L103)
- [HarvestLog.php:14-79](file://app/Models/HarvestLog.php#L14-L79)
- [IrrigationSchedule.php:15-94](file://app/Models/IrrigationSchedule.php#L15-L94)
- [IrrigationLog.php:14-38](file://app/Models/IrrigationLog.php#L14-L38)
- [PestDetection.php:14-74](file://app/Models/PestDetection.php#L14-L74)
- [WeatherData.php:20-194](file://app/Models/WeatherData.php#L20-L194)
- [FarmAnalyticsService.php:16-158](file://app/Services/FarmAnalyticsService.php#L16-L158)

## Architecture Overview
The system follows a layered pattern:
- Domain models encapsulate business entities and derived computations.
- Services orchestrate analytics and automation logic.
- Weather data integrates with scheduling and pest detection to inform decisions.

```mermaid
classDiagram
class FarmPlot {
+int id
+string code
+string name
+decimal area_size
+string area_unit
+string status
+date planted_at
+date expected_harvest
+bool is_active
+totalCost() float
+totalHarvest() float
+costPerUnit() float?
+daysSincePlanted() int?
+daysUntilHarvest() int?
+isHarvestOverdue() bool
}
class CropCycle {
+int id
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
+getProgressPercentageAttribute() float
}
class IrrigationSchedule {
+int id
+int crop_cycle_id
+string zone_name
+string schedule_type
+time irrigation_time
+int duration_minutes
+string frequency
+array custom_days
+float water_volume_liters
+string irrigation_method
+bool is_active
+bool weather_adjusted
+datetime last_irrigated_at
+datetime next_irrigation_at
+int total_irrigations
+float total_water_used_liters
+shouldIrrigateToday() bool
+recordIrrigation(duration, waterUsed) void
}
class IrrigationLog {
+int id
+int irrigation_schedule_id
+datetime irrigated_at
+int actual_duration_minutes
+float actual_water_used_liters
+string status
}
class PestDetection {
+int id
+int crop_cycle_id
+string image_path
+string pest_name
+string disease_name
+float confidence_score
+string severity
+bool pest_detected
+bool disease_detected
+markAsTreated() void
+markAsResolved() void
}
class HarvestLog {
+int id
+int farm_plot_id
+int crop_cycle_id
+date harvest_date
+string crop_name
+decimal total_qty
+string unit
+decimal reject_qty
+decimal moisture_pct
+decimal labor_cost
+decimal transport_cost
+netQty() float
+rejectPercent() float
+totalCost() float
+costPerUnit() float?
+estimatedRevenue() float
}
class WeatherData {
+int id
+string location_name
+float latitude
+float longitude
+float temperature
+float humidity
+float rainfall
+string weather_condition
+datetime forecast_date
+string forecast_type
+isSuitableForFarming() bool
+getFarmingRecommendations() array
+predictHarvestReadiness(cropType, daysPlanted) array
}
class FarmAnalyticsService {
+plotCostBreakdown(plotId, cycleId) array
+costPerHectare(plot, cycleId) float
+hppPerKg(plot, cycleId) float?
+yieldPerHectare(plot, cycleId) float
+comparePlots(tenantId) array
+monthlyCostTrend(plotId, months) array
}
FarmPlot "1" --> "*" CropCycle : "has many"
CropCycle "1" --> "*" IrrigationSchedule : "has many"
IrrigationSchedule "1" --> "*" IrrigationLog : "has many"
CropCycle "1" --> "*" PestDetection : "has many"
FarmPlot "1" --> "*" HarvestLog : "has many"
WeatherData "1" --> "*" IrrigationSchedule : "drives scheduling"
WeatherData "1" --> "*" PestDetection : "drives alerts"
WeatherData "1" --> "*" FarmAnalyticsService : "feeds insights"
```

**Diagram sources**
- [FarmPlot.php:11-104](file://app/Models/FarmPlot.php#L11-L104)
- [CropCycle.php:11-96](file://app/Models/CropCycle.php#L11-L96)
- [IrrigationSchedule.php:11-94](file://app/Models/IrrigationSchedule.php#L11-L94)
- [IrrigationLog.php:10-39](file://app/Models/IrrigationLog.php#L10-L39)
- [PestDetection.php:10-74](file://app/Models/PestDetection.php#L10-L74)
- [HarvestLog.php:11-80](file://app/Models/HarvestLog.php#L11-L80)
- [WeatherData.php:16-194](file://app/Models/WeatherData.php#L16-L194)
- [FarmAnalyticsService.php:11-160](file://app/Services/FarmAnalyticsService.php#L11-L160)

## Detailed Component Analysis

### Crop Cycle Planning
- Fields capture planting and harvest timelines, growth stage transitions, and yield estimates.
- Computed attributes track elapsed time since planting, remaining days to harvest, and progress percentage.
- Growth stage updates based on days since planting guide planning milestones.

```mermaid
flowchart TD
Start(["Planting Date Set"]) --> Elapsed["Compute days_since_planted"]
Elapsed --> Progress["Compute progress percentage"]
Progress --> Stage["Update growth_stage"]
Stage --> |<7 days| Planted["planted"]
Stage --> |<30 days| Vegetative["vegetative"]
Stage --> |<60 days| Flowering["flowering"]
Stage --> |<80 days| Fruiting["fruiting"]
Stage --> |else| Ready["ready_to_harvest"]
Ready --> Expected["Expected harvest date"]
Expected --> Actual["Actual harvest date"]
```

**Diagram sources**
- [CropCycle.php:56-94](file://app/Models/CropCycle.php#L56-L94)

**Section sources**
- [CropCycle.php:15-41](file://app/Models/CropCycle.php#L15-L41)
- [CropCycle.php:56-94](file://app/Models/CropCycle.php#L56-L94)

### Farm Plot Management
- Stores plot metadata, ownership, and status lifecycle with localized labels and colors.
- Aggregates activity costs and harvest quantities to compute cost per unit and days indicators.
- Supports active cycle lookup and status helpers for planning.

```mermaid
flowchart TD
A["Plot Created"] --> B["Set status to preparing/prepared"]
B --> C["Assign current_crop and planted_at"]
C --> D["Track activities and costs"]
D --> E["Compute totalCost(), totalHarvest()"]
E --> F{"Harvest due soon?"}
F --> |Yes| G["Mark ready_harvest or overdue"]
F --> |No| H["Continue growing"]
G --> I["Harvesting sessions tracked via HarvestLog"]
H --> I
```

**Diagram sources**
- [FarmPlot.php:32-102](file://app/Models/FarmPlot.php#L32-L102)

**Section sources**
- [FarmPlot.php:14-30](file://app/Models/FarmPlot.php#L14-L30)
- [FarmPlot.php:32-50](file://app/Models/FarmPlot.php#L32-L50)
- [FarmPlot.php:55-83](file://app/Models/FarmPlot.php#L55-L83)
- [FarmPlot.php:85-102](file://app/Models/FarmPlot.php#L85-L102)

### Harvest Operations
- Captures harvest batch number generation, quantities, rejects, moisture, and logistics costs.
- Computes net quantity, reject percentage, total cost, and cost per unit.
- Estimates revenue from grade allocations.

```mermaid
sequenceDiagram
participant Field as "Field Crew"
participant Log as "HarvestLog"
participant Grades as "HarvestLogGrade"
participant Workers as "HarvestLogWorker"
Field->>Log : Create harvest record (total_qty, reject_qty, labor_cost, transport_cost)
Log->>Log : netQty() = total - reject
Log->>Log : rejectPercent()
Log->>Log : totalCost() = labor + transport
Log->>Log : costPerUnit() = totalCost / netQty
Log->>Grades : Link grade allocations (quantity * price_per_unit)
Log->>Workers : Track worker hours and productivity
```

**Diagram sources**
- [HarvestLog.php:41-79](file://app/Models/HarvestLog.php#L41-L79)

**Section sources**
- [HarvestLog.php:14-32](file://app/Models/HarvestLog.php#L14-L32)
- [HarvestLog.php:41-79](file://app/Models/HarvestLog.php#L41-L79)

### Irrigation Scheduling and Automation
- Irrigation Schedule defines zones, methods, frequency, and weather-adjusted flags.
- Determines whether irrigation should occur today based on frequency and custom days.
- Records actual durations and water volumes, updating counters and next irrigation time.

```mermaid
sequenceDiagram
participant Scheduler as "IrrigationSchedule"
participant Logger as "IrrigationLog"
participant Auto as "IrrigationAutomationService"
Auto->>Scheduler : shouldIrrigateToday()
alt Today is scheduled
Auto->>Scheduler : recordIrrigation(duration, waterUsed)
Scheduler->>Logger : Create log entry (irrigated_at, actual_duration, actual_water_used)
Scheduler->>Scheduler : Increment total_irrigations and total_water_used_liters
Scheduler->>Scheduler : Update last_irrigated_at and next_irrigation_at
else Not scheduled
Auto-->>Auto : Skip
end
```

**Diagram sources**
- [IrrigationSchedule.php:61-92](file://app/Models/IrrigationSchedule.php#L61-L92)
- [IrrigationLog.php:14-38](file://app/Models/IrrigationLog.php#L14-L38)
- [IrrigationAutomationService.php](file://app/Services/IrrigationAutomationService.php)

**Section sources**
- [IrrigationSchedule.php:15-46](file://app/Models/IrrigationSchedule.php#L15-L46)
- [IrrigationSchedule.php:61-92](file://app/Models/IrrigationSchedule.php#L61-L92)
- [IrrigationLog.php:14-38](file://app/Models/IrrigationLog.php#L14-L38)

### Pest and Disease Management
- Pest Detection stores AI-derived observations, confidence, severity, and treatment status.
- Provides severity color mapping and status transitions (treated/resolved).

```mermaid
flowchart TD
Observe["AI/Field Observation"] --> PDetect["PestDetection.create"]
PDetect --> Severity{"Severity"}
Severity --> |Low| Green["green"]
Severity --> |Medium| Yellow["yellow"]
Severity --> |High| Orange["orange"]
Severity --> |Critical| Red["red"]
PDetect --> Treat["markAsTreated()"]
Treat --> Resolve["markAsResolved()"]
```

**Diagram sources**
- [PestDetection.php:50-73](file://app/Models/PestDetection.php#L50-L73)

**Section sources**
- [PestDetection.php:14-39](file://app/Models/PestDetection.php#L14-L39)
- [PestDetection.php:50-73](file://app/Models/PestDetection.php#L50-L73)

### Weather-Driven Insights and Recommendations
- WeatherData captures current and forecast metrics and evaluates suitability for farming.
- Generates actionable recommendations (spray postponement, fungal checks, irrigation adjustments).
- Predicts harvest readiness and quality risks based on rainfall and crop type.

```mermaid
flowchart TD
WData["WeatherData.create/update"] --> Suit["isSuitableForFarming()"]
Suit --> |No| Warn["Flag risky conditions"]
Suit --> |Yes| Rec["getFarmingRecommendations()"]
Rec --> Actions["Recommendations array"]
WData --> Predict["predictHarvestReadiness(cropType, daysPlanted)"]
Predict --> Readiness["Readiness %, days to harvest, risks"]
```

**Diagram sources**
- [WeatherData.php:85-146](file://app/Models/WeatherData.php#L85-L146)
- [WeatherData.php:151-192](file://app/Models/WeatherData.php#L151-L192)

**Section sources**
- [WeatherData.php:20-53](file://app/Models/WeatherData.php#L20-L53)
- [WeatherData.php:85-146](file://app/Models/WeatherData.php#L85-L146)
- [WeatherData.php:151-192](file://app/Models/WeatherData.php#L151-L192)

### Analytics and Performance Benchmarking
- Computes cost breakdowns by activity type, cost per hectare, HPP per kg, and yield per hectare.
- Compares plots across key metrics and shows monthly cost trends.

```mermaid
flowchart TD
A["Select Plot(s)"] --> B["Aggregate activities and harvest logs"]
B --> C["Cost breakdown by activity type"]
B --> D["Total cost / area = cost per hectare"]
B --> E["Total cost / total harvested = HPP per kg"]
B --> F["Total harvested / area = yield per hectare"]
B --> G["Compare plots and trends"]
```

**Diagram sources**
- [FarmAnalyticsService.php:16-158](file://app/Services/FarmAnalyticsService.php#L16-L158)

**Section sources**
- [FarmAnalyticsService.php:16-35](file://app/Services/FarmAnalyticsService.php#L16-L35)
- [FarmAnalyticsService.php:40-78](file://app/Services/FarmAnalyticsService.php#L40-L78)
- [FarmAnalyticsService.php:83-96](file://app/Services/FarmAnalyticsService.php#L83-L96)
- [FarmAnalyticsService.php:101-137](file://app/Services/FarmAnalyticsService.php#L101-L137)
- [FarmAnalyticsService.php:142-158](file://app/Services/FarmAnalyticsService.php#L142-L158)

## Dependency Analysis
- FarmPlot depends on CropCycle and FarmPlotActivity for lifecycle and cost aggregation.
- CropCycle links to IrrigationSchedule and PestDetection for planning and health monitoring.
- IrrigationSchedule produces IrrigationLog entries upon execution.
- WeatherData informs scheduling and pest detection decisions.
- FarmAnalyticsService consumes multiple models to produce insights.

```mermaid
graph LR
FP["FarmPlot"] --> CC["CropCycle"]
CC --> IS["IrrigationSchedule"]
IS --> IL["IrrigationLog"]
CC --> PD["PestDetection"]
FP --> HL["HarvestLog"]
WD["WeatherData"] --> IS
WD --> PD
WD --> FAS["FarmAnalyticsService"]
```

**Diagram sources**
- [FarmPlot.php:53-58](file://app/Models/FarmPlot.php#L53-L58)
- [CropCycle.php:47-54](file://app/Models/CropCycle.php#L47-L54)
- [IrrigationSchedule.php:52-59](file://app/Models/IrrigationSchedule.php#L52-L59)
- [IrrigationLog.php:30-37](file://app/Models/IrrigationLog.php#L30-L37)
- [PestDetection.php:41-48](file://app/Models/PestDetection.php#L41-L48)
- [HarvestLog.php:34-39](file://app/Models/HarvestLog.php#L34-L39)
- [WeatherData.php:58-61](file://app/Models/WeatherData.php#L58-L61)
- [FarmAnalyticsService.php:11-160](file://app/Services/FarmAnalyticsService.php#L11-L160)

**Section sources**
- [FarmPlot.php:53-58](file://app/Models/FarmPlot.php#L53-L58)
- [CropCycle.php:47-54](file://app/Models/CropCycle.php#L47-L54)
- [IrrigationSchedule.php:52-59](file://app/Models/IrrigationSchedule.php#L52-L59)
- [IrrigationLog.php:30-37](file://app/Models/IrrigationLog.php#L30-L37)
- [PestDetection.php:41-48](file://app/Models/PestDetection.php#L41-L48)
- [HarvestLog.php:34-39](file://app/Models/HarvestLog.php#L34-L39)
- [WeatherData.php:58-61](file://app/Models/WeatherData.php#L58-L61)
- [FarmAnalyticsService.php:11-160](file://app/Services/FarmAnalyticsService.php#L11-L160)

## Performance Considerations
- Prefer indexed foreign keys on tenant_id, crop_cycle_id, farm_plot_id for efficient joins.
- Use aggregated queries in analytics to minimize N+1 selects and reduce DB load.
- Cache frequently accessed weather forecasts and recommendations to avoid repeated API calls.
- Batch irrigation log updates and limit recomputation of next irrigation timestamps to necessary intervals.

## Troubleshooting Guide
- Irrigation not recorded: Verify schedule is active and today matches frequency/custom days; confirm automation invoked recordIrrigation with valid duration and water volume.
- Pest detection not treated: Ensure markAsTreated/markAsResolved is called after intervention; check severity thresholds and status transitions.
- Weather recommendations not applied: Confirm forecast_type and location filters; validate isSuitableForFarming and recommendation generation logic.
- Analytics discrepancies: Cross-check activity vs. harvest log totals; ensure correct cycle scoping and unit conversions.

**Section sources**
- [IrrigationSchedule.php:61-92](file://app/Models/IrrigationSchedule.php#L61-L92)
- [IrrigationLog.php:14-38](file://app/Models/IrrigationLog.php#L14-L38)
- [PestDetection.php:61-73](file://app/Models/PestDetection.php#L61-L73)
- [WeatherData.php:85-146](file://app/Models/WeatherData.php#L85-L146)
- [FarmAnalyticsService.php:16-158](file://app/Services/FarmAnalyticsService.php#L16-L158)

## Conclusion
The system provides a robust foundation for crop cycle planning, plot lifecycle management, harvest tracking, irrigation automation, pest monitoring, and weather-informed decision support. Analytics services enable cost, yield, and comparative performance insights. The modular design supports extension for precision agriculture sensors, IoT devices, and advanced forecasting models.

## Appendices
- Precision Agriculture Implementation Examples
  - Sensor integration: Extend WeatherData to ingest IoT sensor readings (soil moisture, EC, temperature) and derive actionable thresholds for irrigation and fertilization triggers.
  - Drone/imagery pipeline: Store PestDetection image_path and integrate AI classification APIs; surface severity and treatment recommendations.
  - Variable rate application: Map application logs to FarmPlotActivity with input_quantity and cost to refine HPP calculations.
- Sustainable Farming Practices
  - Organic compliance: Add organic_certification and inputs_category to activities; flag restricted inputs in recommendations.
  - Water conservation: Enforce minimum water_volume_liters per hectare targets; track drought tolerance crops via metadata.
- Supply Chain Integration
  - Grade modeling: Enhance HarvestLogGrade with customer-specific categories and pricing tiers; export estimated revenue for sales planning.
  - Traceability: Link HarvestLog to product variants and batches for downstream traceability workflows.