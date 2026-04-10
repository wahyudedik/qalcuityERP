# Telecom Entities

<cite>
**Referenced Files in This Document**
- [TelecomSubscription.php](file://app/Models/TelecomSubscription.php)
- [HotspotUser.php](file://app/Models/HotspotUser.php)
- [VoucherCode.php](file://app/Models/VoucherCode.php)
- [UsageTracking.php](file://app/Models/UsageTracking.php)
- [BandwidthAllocation.php](file://app/Models/BandwidthAllocation.php)
- [InternetPackage.php](file://app/Models/InternetPackage.php)
- [NetworkDevice.php](file://app/Models/NetworkDevice.php)
- [NetworkAlert.php](file://app/Models/NetworkAlert.php)
- [Contract.php](file://app/Models/Contract.php)
- [ContractSlaLog.php](file://app/Models/ContractSlaLog.php)
- [TelecomBillingIntegrationService.php](file://app/Services/Telecom/TelecomBillingIntegrationService.php)
- [GenerateTelecomInvoicesJob.php](file://app/Jobs/GenerateTelecomInvoicesJob.php)
- [2026_04_04_000002_create_internet_packages_table.php](file://database/migrations/2026_04_04_000002_create_internet_packages_table.php)
- [2026_04_04_000003_create_telecom_subscriptions_table.php](file://database/migrations/2026_04_04_000003_create_telecom_subscriptions_table.php)
- [UsageController.php](file://app/Http/Controllers/Api/Telecom/UsageController.php)
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
This document describes the telecom data models used by Qalcuity ERP for service provisioning, wireless access management, network monitoring, and billing. It focuses on:
- TelecomSubscription for provisioning and billing lifecycle
- HotspotUser and VoucherCode for guest Wi‑Fi access
- UsageTracking and BandwidthAllocation for monitoring and capacity planning
- Telecom billing patterns, usage-based pricing, and service level agreements

## Project Structure
The telecom domain spans models, services, jobs, and migrations that define entities, relationships, and workflows for provisioning, access control, monitoring, and invoicing.

```mermaid
graph TB
subgraph "Models"
A["TelecomSubscription"]
B["HotspotUser"]
C["VoucherCode"]
D["UsageTracking"]
E["BandwidthAllocation"]
F["InternetPackage"]
G["NetworkDevice"]
H["NetworkAlert"]
I["Contract"]
J["ContractSlaLog"]
end
subgraph "Services"
S1["TelecomBillingIntegrationService"]
end
subgraph "Jobs"
J1["GenerateTelecomInvoicesJob"]
end
subgraph "Migrations"
M1["internet_packages"]
M2["telecom_subscriptions"]
end
A --> F
A --> G
A --> B
A --> D
A --> E
A --> H
B --> G
B --> E
C --> F
D --> A
D --> G
E --> A
E --> B
E --> G
H --> A
H --> G
I --> A
I --> J
S1 --> A
S1 --> F
J1 --> S1
```

**Diagram sources**
- [TelecomSubscription.php:12-130](file://app/Models/TelecomSubscription.php#L12-L130)
- [HotspotUser.php:12-101](file://app/Models/HotspotUser.php#L12-L101)
- [VoucherCode.php:10-90](file://app/Models/VoucherCode.php#L10-L90)
- [UsageTracking.php:10-75](file://app/Models/UsageTracking.php#L10-L75)
- [BandwidthAllocation.php:10-81](file://app/Models/BandwidthAllocation.php#L10-L81)
- [InternetPackage.php:12-81](file://app/Models/InternetPackage.php#L12-L81)
- [NetworkDevice.php:13-97](file://app/Models/NetworkDevice.php#L13-L97)
- [NetworkAlert.php:10-82](file://app/Models/NetworkAlert.php#L10-L82)
- [Contract.php:11-45](file://app/Models/Contract.php#L11-L45)
- [ContractSlaLog.php:10-43](file://app/Models/ContractSlaLog.php#L10-L43)
- [TelecomBillingIntegrationService.php:13-93](file://app/Services/Telecom/TelecomBillingIntegrationService.php#L13-L93)
- [GenerateTelecomInvoicesJob.php:13-46](file://app/Jobs/GenerateTelecomInvoicesJob.php#L13-L46)
- [2026_04_04_000002_create_internet_packages_table.php:13-54](file://database/migrations/2026_04_04_000002_create_internet_packages_table.php#L13-L54)
- [2026_04_04_000003_create_telecom_subscriptions_table.php:13-33](file://database/migrations/2026_04_04_000003_create_telecom_subscriptions_table.php#L13-L33)

**Section sources**
- [TelecomSubscription.php:12-130](file://app/Models/TelecomSubscription.php#L12-L130)
- [InternetPackage.php:12-81](file://app/Models/InternetPackage.php#L12-L81)
- [HotspotUser.php:12-101](file://app/Models/HotspotUser.php#L12-L101)
- [VoucherCode.php:10-90](file://app/Models/VoucherCode.php#L10-L90)
- [UsageTracking.php:10-75](file://app/Models/UsageTracking.php#L10-L75)
- [BandwidthAllocation.php:10-81](file://app/Models/BandwidthAllocation.php#L10-L81)
- [NetworkDevice.php:13-97](file://app/Models/NetworkDevice.php#L13-L97)
- [NetworkAlert.php:10-82](file://app/Models/NetworkAlert.php#L10-L82)
- [Contract.php:11-45](file://app/Models/Contract.php#L11-L45)
- [ContractSlaLog.php:10-43](file://app/Models/ContractSlaLog.php#L10-L43)
- [TelecomBillingIntegrationService.php:13-93](file://app/Services/Telecom/TelecomBillingIntegrationService.php#L13-L93)
- [GenerateTelecomInvoicesJob.php:13-46](file://app/Jobs/GenerateTelecomInvoicesJob.php#L13-L46)
- [2026_04_04_000002_create_internet_packages_table.php:13-54](file://database/migrations/2026_04_04_000002_create_internet_packages_table.php#L13-L54)
- [2026_04_04_000003_create_telecom_subscriptions_table.php:13-33](file://database/migrations/2026_04_04_000003_create_telecom_subscriptions_table.php#L13-L33)

## Core Components
- TelecomSubscription: central entity for provisioning, billing cycle, quotas, and access credentials (hotspot and PPPoE).
- HotspotUser: per-subscription Wi‑Fi user accounts with rate limits, burst controls, quotas, and session metrics.
- VoucherCode: promotional or retail vouchers linked to packages, validity windows, and usage caps.
- UsageTracking: per-period traffic metrics and peak usage timestamps for monitoring and reporting.
- BandwidthAllocation: QoS policies for subscriptions and users, including max/guaranteed rates, priorities, and time-based rules.
- Supporting models: InternetPackage (service plans), NetworkDevice (routers/APs), NetworkAlert (alarms), Contract/ContractSlaLog (SLA).

**Section sources**
- [TelecomSubscription.php:16-65](file://app/Models/TelecomSubscription.php#L16-L65)
- [HotspotUser.php:16-69](file://app/Models/HotspotUser.php#L16-L69)
- [VoucherCode.php:13-50](file://app/Models/VoucherCode.php#L13-L50)
- [UsageTracking.php:13-51](file://app/Models/UsageTracking.php#L13-L51)
- [BandwidthAllocation.php:13-49](file://app/Models/BandwidthAllocation.php#L13-L49)
- [InternetPackage.php:16-57](file://app/Models/InternetPackage.php#L16-L57)
- [NetworkDevice.php:17-49](file://app/Models/NetworkDevice.php#L17-L49)
- [NetworkAlert.php:13-42](file://app/Models/NetworkAlert.php#L13-L42)
- [Contract.php:14-36](file://app/Models/Contract.php#L14-L36)
- [ContractSlaLog.php:13-27](file://app/Models/ContractSlaLog.php#L13-L27)

## Architecture Overview
The telecom subsystem integrates provisioning, access, monitoring, and billing via explicit model relationships and service/job orchestration.

```mermaid
classDiagram
class InternetPackage {
+int download_speed_mbps
+int upload_speed_mbps
+bigint quota_bytes
+enum quota_period
+decimal price
+decimal overage_price_per_gb
}
class TelecomSubscription {
+string subscription_number
+enum status
+date next_billing_date
+date last_billing_date
+datetime activated_at
+datetime expires_at
+int quota_used_bytes
+int quota_reset_bytes
+datetime quota_period_start
+datetime quota_period_end
+bool quota_exceeded
+string hotspot_username
+encrypted hotspot_password_encrypted
+string pppoe_username
+encrypted pppoe_password_encrypted
+string static_ip_address
+string mac_address_registered
+int priority_level
+decimal current_price
}
class HotspotUser {
+string username
+encrypted password_encrypted
+string mac_address
+enum auth_type
+bool is_active
+datetime activated_at
+datetime expires_at
+int rate_limit_download_kbps
+int rate_limit_upload_kbps
+int burst_limit_download_kbps
+int burst_limit_upload_kbps
+int burst_threshold_kbps
+int burst_time_seconds
+bigint quota_bytes
+bigint quota_used_bytes
+datetime quota_reset_at
+bool is_online
+string current_ip_address
+datetime last_login_at
+datetime last_logout_at
+int total_sessions
+int total_uptime_seconds
+array router_user_profile
}
class VoucherCode {
+string code
+string batch_number
+enum status
+datetime valid_from
+datetime valid_until
+int validity_hours
+datetime first_used_at
+datetime last_used_at
+int usage_count
+int max_usage
+int download_speed_mbps
+int upload_speed_mbps
+bigint quota_bytes
+decimal sale_price
+datetime sold_at
}
class UsageTracking {
+bigint bytes_in
+bigint bytes_out
+bigint bytes_total
+int packets_in
+int packets_out
+int sessions_count
+int session_duration_seconds
+datetime first_seen_at
+datetime last_seen_at
+string period_type
+datetime period_start
+datetime period_end
+int peak_bandwidth_kbps
+datetime peak_usage_time
+string ip_address
+string mac_address
+array additional_data
}
class BandwidthAllocation {
+string allocation_name
+string allocation_type
+int max_download_kbps
+int max_upload_kbps
+int guaranteed_download_kbps
+int guaranteed_upload_kbps
+int priority
+string queue_type
+array queue_parameters
+array time_rules
+bool is_active
+datetime active_from
+datetime active_until
+bigint current_usage_bytes
+datetime last_updated_at
}
class NetworkDevice {
+string name
+string device_type
+string brand
+string model
+string ip_address
+int port
+encrypted password_encrypted
+string api_token
+string mac_address
+string serial_number
+string firmware_version
+string status
+datetime last_seen_at
+array capabilities
+array configuration
}
class NetworkAlert {
+string alert_type
+string severity
+string title
+string message
+string status
+array threshold_data
+array current_metrics
+bool notification_sent
+datetime notification_sent_at
+array notified_users
+datetime acknowledged_at
+datetime resolved_at
+string resolution_notes
}
class Contract {
+string contract_number
+string title
+int sla_response_hours
+int sla_resolution_hours
+decimal sla_uptime_pct
+string sla_terms
+string terms
}
class ContractSlaLog {
+string incident_type
+string description
+datetime reported_at
+datetime responded_at
+datetime resolved_at
+bool sla_met
+string notes
}
InternetPackage "1" --> "*" TelecomSubscription : "package_id"
NetworkDevice "1" --> "*" TelecomSubscription : "device_id"
NetworkDevice "1" --> "*" HotspotUser : "device_id"
NetworkDevice "1" --> "*" BandwidthAllocation : "device_id"
TelecomSubscription "1" --> "*" HotspotUser : "subscription_id"
TelecomSubscription "1" --> "*" UsageTracking : "subscription_id"
TelecomSubscription "1" --> "*" BandwidthAllocation : "subscription_id"
TelecomSubscription "1" --> "*" NetworkAlert : "subscription_id"
HotspotUser "1" --> "*" BandwidthAllocation : "hotspot_user_id"
InternetPackage "1" --> "*" VoucherCode : "package_id"
Contract "1" --> "*" ContractSlaLog : "contract_id"
```

**Diagram sources**
- [InternetPackage.php:12-81](file://app/Models/InternetPackage.php#L12-L81)
- [TelecomSubscription.php:12-130](file://app/Models/TelecomSubscription.php#L12-L130)
- [HotspotUser.php:12-101](file://app/Models/HotspotUser.php#L12-L101)
- [VoucherCode.php:10-90](file://app/Models/VoucherCode.php#L10-L90)
- [UsageTracking.php:10-75](file://app/Models/UsageTracking.php#L10-L75)
- [BandwidthAllocation.php:10-81](file://app/Models/BandwidthAllocation.php#L10-L81)
- [NetworkDevice.php:13-97](file://app/Models/NetworkDevice.php#L13-L97)
- [NetworkAlert.php:10-82](file://app/Models/NetworkAlert.php#L10-L82)
- [Contract.php:11-45](file://app/Models/Contract.php#L11-L45)
- [ContractSlaLog.php:10-43](file://app/Models/ContractSlaLog.php#L10-L43)

## Detailed Component Analysis

### TelecomSubscription
Responsibilities:
- Provisioning lifecycle: pending, active, suspended, cancelled, expired
- Billing cycle management: monthly/quarterly/semi-annual/annual
- Quota tracking: used, reset, period boundaries
- Access credentials: encrypted hotspot and PPPoE secrets
- Relationships: belongs to Tenant, Customer, InternetPackage, NetworkDevice; has many HotspotUser, UsageTracking, BandwidthAllocation, NetworkAlert

Key behaviors:
- Status transitions: activate, suspend, cancel
- Quota reset and calculation of next reset based on package quota period
- Scopes for active/expired/quota-exceeded/expiring-soon
- Encrypted attributes for sensitive credentials

```mermaid
flowchart TD
Start(["Subscription Lifecycle"]) --> Pending["Pending"]
Pending --> Active["Active"]
Active --> Suspense["Suspended"]
Active --> Cancel["Cancelled"]
Active --> Expire["Expired (expires_at)"]
Suspense --> Active
Cancel --> End(["Closed"])
Expire --> End
```

**Diagram sources**
- [TelecomSubscription.php:134-203](file://app/Models/TelecomSubscription.php#L134-L203)

**Section sources**
- [TelecomSubscription.php:16-65](file://app/Models/TelecomSubscription.php#L16-L65)
- [TelecomSubscription.php:134-203](file://app/Models/TelecomSubscription.php#L134-L203)
- [TelecomSubscription.php:273-302](file://app/Models/TelecomSubscription.php#L273-L302)
- [2026_04_04_000003_create_telecom_subscriptions_table.php:20-33](file://database/migrations/2026_04_04_000003_create_telecom_subscriptions_table.php#L20-L33)

### HotspotUser
Responsibilities:
- Per-subscription Wi‑Fi user account management
- Rate limiting and burst controls (download/upload kbps)
- Quota enforcement and remaining quota computation
- Online/offline session tracking and uptime aggregation
- Relationships: belongs to Tenant, TelecomSubscription, NetworkDevice; has many BandwidthAllocation

Key behaviors:
- Password encryption/decryption
- Quota exceeded checks and remaining quota formatting
- Session counters and IP address tracking
- Scopes for active/online/expired

```mermaid
flowchart TD
UStart(["HotspotUser Session"]) --> Login["Login with credentials"]
Login --> Online["Mark As Online<br/>+ last_login_at + total_sessions++"]
Online --> Usage["Traffic updates<br/>+ quota_used_bytes"]
Usage --> CheckQuota{"Quota exceeded?"}
CheckQuota --> |Yes| Limit["Apply rate limits/burst rules"]
CheckQuota --> |No| Continue["Continue session"]
Continue --> Logout["Logout"]
Logout --> Offline["Mark As Offline<br/>+ total_uptime_seconds += duration"]
Offline --> UEnd(["Session End"])
```

**Diagram sources**
- [HotspotUser.php:184-204](file://app/Models/HotspotUser.php#L184-L204)
- [HotspotUser.php:137-145](file://app/Models/HotspotUser.php#L137-L145)

**Section sources**
- [HotspotUser.php:16-69](file://app/Models/HotspotUser.php#L16-L69)
- [HotspotUser.php:105-121](file://app/Models/HotspotUser.php#L105-L121)
- [HotspotUser.php:137-145](file://app/Models/HotspotUser.php#L137-L145)
- [HotspotUser.php:184-204](file://app/Models/HotspotUser.php#L184-L204)
- [HotspotUser.php:228-248](file://app/Models/HotspotUser.php#L228-L248)

### VoucherCode
Responsibilities:
- Retail/voucherized access provisioning aligned with InternetPackage
- Validity windows, usage counts, and redemption tracking
- Speed and quota caps inherited from package
- Relationships: belongs to Tenant, InternetPackage, User (generated_by), Customer (used_by/sold_to)

Key behaviors:
- Status lifecycle: unused → used → expired/revoked
- Validity checks and “can be used” evaluation
- Scopes for unused/valid/batch filtering

```mermaid
flowchart TD
VStart(["Voucher Lifecycle"]) --> Unused["Unused"]
Unused --> Used["Used<br/>+ usage_count++ + last_used_at"]
Unused --> Expire["Expired (valid_until)"]
Unused --> Revoked["Revoked"]
Used --> VEnd(["Closed"])
Expire --> VEnd
Revoked --> VEnd
```

**Diagram sources**
- [VoucherCode.php:137-158](file://app/Models/VoucherCode.php#L137-L158)
- [VoucherCode.php:110-122](file://app/Models/VoucherCode.php#L110-L122)

**Section sources**
- [VoucherCode.php:13-50](file://app/Models/VoucherCode.php#L13-L50)
- [VoucherCode.php:110-122](file://app/Models/VoucherCode.php#L110-L122)
- [VoucherCode.php:137-158](file://app/Models/VoucherCode.php#L137-L158)
- [VoucherCode.php:198-222](file://app/Models/VoucherCode.php#L198-L222)

### UsageTracking
Responsibilities:
- Capture per-period traffic metrics (bytes in/out/total), sessions, durations, and peak bandwidth
- Associate records to TelecomSubscription and NetworkDevice
- Human-readable formatting helpers for bytes and durations

Key behaviors:
- Scopes for period type, date range, and high usage ranking
- Formatting utilities for display

```mermaid
sequenceDiagram
participant Device as "NetworkDevice"
participant Tracking as "UsageTracking"
participant Sub as "TelecomSubscription"
Device->>Tracking : "Create record<br/>period_start/period_end, bytes_total"
Tracking->>Sub : "belongsTo subscription"
Note over Tracking,Sub : "Aggregated for reporting and SLA compliance"
```

**Diagram sources**
- [UsageTracking.php:10-75](file://app/Models/UsageTracking.php#L10-L75)
- [UsageTracking.php:139-158](file://app/Models/UsageTracking.php#L139-L158)

**Section sources**
- [UsageTracking.php:13-51](file://app/Models/UsageTracking.php#L13-L51)
- [UsageTracking.php:139-158](file://app/Models/UsageTracking.php#L139-L158)

### BandwidthAllocation
Responsibilities:
- Define QoS policies for subscriptions and users
- Enforce max and guaranteed rates, priority queues, and time-based rules
- Track current usage and activity windows

Key behaviors:
- Time-based activation checks against configured time_rules
- Attribute helpers to convert kbps to Mbps and format current usage
- Scopes for active, type, and priority ordering

```mermaid
flowchart TD
BAStart(["BandwidthAllocation"]) --> CheckActive{"is_active?"}
CheckActive --> |No| Inactive["Inactive"]
CheckActive --> |Yes| CheckWindow{"Within active_from/until?"}
CheckWindow --> |No| Inactive
CheckWindow --> |Yes| CheckRules{"time_rules match today?"}
CheckRules --> |No| Inactive
CheckRules --> |Yes| Active["Active Policy"]
```

**Diagram sources**
- [BandwidthAllocation.php:86-106](file://app/Models/BandwidthAllocation.php#L86-L106)
- [BandwidthAllocation.php:111-129](file://app/Models/BandwidthAllocation.php#L111-L129)

**Section sources**
- [BandwidthAllocation.php:13-49](file://app/Models/BandwidthAllocation.php#L13-L49)
- [BandwidthAllocation.php:86-106](file://app/Models/BandwidthAllocation.php#L86-L106)
- [BandwidthAllocation.php:134-145](file://app/Models/BandwidthAllocation.php#L134-L145)
- [BandwidthAllocation.php:167-186](file://app/Models/BandwidthAllocation.php#L167-L186)

### InternetPackage
Responsibilities:
- Service plan definition: speeds, burst, quota, rollover, pricing, features
- Link to TelecomSubscription and VoucherCode
- Overage calculation for usage-based billing

Key behaviors:
- Unlimited quota detection and GB conversion
- Formatted price display
- Scopes for active/public/ordering

**Section sources**
- [InternetPackage.php:16-57](file://app/Models/InternetPackage.php#L16-L57)
- [InternetPackage.php:138-146](file://app/Models/InternetPackage.php#L138-L146)
- [2026_04_04_000002_create_internet_packages_table.php:13-54](file://database/migrations/2026_04_04_000002_create_internet_packages_table.php#L13-L54)

### NetworkDevice and NetworkAlert
Responsibilities:
- NetworkDevice: router/AP inventory, connectivity status, and associations to subscriptions/users/allocations/alerts
- NetworkAlert: severity/status tracking, acknowledgments, resolutions, and notifications

**Section sources**
- [NetworkDevice.php:17-49](file://app/Models/NetworkDevice.php#L17-L49)
- [NetworkDevice.php:118-143](file://app/Models/NetworkDevice.php#L118-L143)
- [NetworkAlert.php:13-42](file://app/Models/NetworkAlert.php#L13-L42)
- [NetworkAlert.php:111-131](file://app/Models/NetworkAlert.php#L111-L131)

### Contract and ContractSlaLog (SLA)
Responsibilities:
- Contract captures SLA targets (response/resolution hours, uptime %) and terms
- ContractSlaLog tracks incidents and SLA compliance metrics

**Section sources**
- [Contract.php:14-36](file://app/Models/Contract.php#L14-L36)
- [Contract.php:64-69](file://app/Models/Contract.php#L64-L69)
- [ContractSlaLog.php:19-43](file://app/Models/ContractSlaLog.php#L19-L43)

## Dependency Analysis
Telecom models are tightly coupled around provisioning and monitoring:
- TelecomSubscription depends on InternetPackage and NetworkDevice
- HotspotUser depends on TelecomSubscription and NetworkDevice
- BandwidthAllocation depends on TelecomSubscription, HotspotUser, and NetworkDevice
- UsageTracking depends on TelecomSubscription and NetworkDevice
- VoucherCode depends on InternetPackage
- NetworkAlert depends on TelecomSubscription and NetworkDevice
- Billing integration orchestrates TelecomSubscription and Invoice creation

```mermaid
graph LR
IP["InternetPackage"] --> TS["TelecomSubscription"]
ND["NetworkDevice"] --> TS
TS --> HU["HotspotUser"]
TS --> UT["UsageTracking"]
TS --> BA["BandwidthAllocation"]
ND --> HU
ND --> BA
ND --> UT
TS --> NA["NetworkAlert"]
VC["VoucherCode"] --> IP
```

**Diagram sources**
- [TelecomSubscription.php:86-96](file://app/Models/TelecomSubscription.php#L86-L96)
- [HotspotUser.php:82-92](file://app/Models/HotspotUser.php#L82-L92)
- [UsageTracking.php:64-74](file://app/Models/UsageTracking.php#L64-L74)
- [BandwidthAllocation.php:69-80](file://app/Models/BandwidthAllocation.php#L69-L80)
- [NetworkDevice.php:78-97](file://app/Models/NetworkDevice.php#L78-L97)
- [NetworkAlert.php:63-65](file://app/Models/NetworkAlert.php#L63-L65)
- [VoucherCode.php:63-65](file://app/Models/VoucherCode.php#L63-L65)

**Section sources**
- [TelecomSubscription.php:86-130](file://app/Models/TelecomSubscription.php#L86-L130)
- [HotspotUser.php:82-101](file://app/Models/HotspotUser.php#L82-L101)
- [UsageTracking.php:64-75](file://app/Models/UsageTracking.php#L64-L75)
- [BandwidthAllocation.php:69-81](file://app/Models/BandwidthAllocation.php#L69-L81)
- [NetworkDevice.php:78-97](file://app/Models/NetworkDevice.php#L78-L97)
- [NetworkAlert.php:63-66](file://app/Models/NetworkAlert.php#L63-L66)
- [VoucherCode.php:63-65](file://app/Models/VoucherCode.php#L63-L65)

## Performance Considerations
- Indexing: ensure foreign keys (tenant_id, customer_id, package_id, device_id, subscription_id) are indexed in migrations for fast joins and scopes.
- Scopes and queries: leverage scopes (active/expired/quota exceeded, online/active, valid/unused) to minimize ad-hoc filtering.
- Encryption overhead: encrypted fields incur CPU cost; cache decrypted values only when necessary and avoid frequent re-encryption.
- BandwidthAllocation time_rules: evaluate time windows efficiently; precompute daily windows if needed.
- UsageTracking aggregation: summarize at period boundaries to reduce row counts for reporting.

[No sources needed since this section provides general guidance]

## Troubleshooting Guide
Common issues and diagnostics:
- Subscription status anomalies: verify activate/suspend/cancel transitions and expiry checks.
- Quota not resetting: confirm next reset calculation and resetQuota invocation.
- HotspotUser quota exceeded: validate quota_bytes vs quota_used_bytes and remaining_quota formatting.
- BandwidthAllocation inactive: check is_active flag, active_from/until, and time_rules matching current day/time.
- Voucher validity: ensure status reflects unused/used/expired and usage_count/max_usage thresholds.
- Alerts not resolving: track acknowledgment/resolution flows and notification_sent flags.

**Section sources**
- [TelecomSubscription.php:162-203](file://app/Models/TelecomSubscription.php#L162-L203)
- [HotspotUser.php:137-157](file://app/Models/HotspotUser.php#L137-L157)
- [BandwidthAllocation.php:86-106](file://app/Models/BandwidthAllocation.php#L86-L106)
- [VoucherCode.php:110-122](file://app/Models/VoucherCode.php#L110-L122)
- [NetworkAlert.php:111-131](file://app/Models/NetworkAlert.php#L111-L131)

## Conclusion
Qalcuity ERP’s telecom module provides a cohesive model set for provisioning, access control, monitoring, and billing. TelecomSubscription anchors the lifecycle and quotas, HotspotUser manages guest access, VoucherCode enables flexible sales, UsageTracking and BandwidthAllocation support capacity planning and QoS, and Contract/ContractSlaLog formalizes SLA expectations. The TelecomBillingIntegrationService and scheduled job automate recurring invoicing aligned with package billing cycles and usage.