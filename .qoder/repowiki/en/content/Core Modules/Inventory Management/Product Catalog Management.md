# Product Catalog Management

<cite>
**Referenced Files in This Document**
- [Product.php](file://app/Models/Product.php)
- [ProductCategory.php](file://app/Models/ProductCategory.php)
- [ProductVariant.php](file://app/Models/ProductVariant.php)
- [ProductBatch.php](file://app/Models/ProductBatch.php)
- [ProductStock.php](file://app/Models/ProductStock.php)
- [ProductController.php](file://app/Http/Controllers/ProductController.php)
- [ProductCategoryController.php](file://app/Http/Controllers/ProductCategoryController.php)
- [ProductImport.php](file://app/Imports/ProductImport.php)
- [BarcodeService.php](file://app/Services/BarcodeService.php)
- [ProductPriceHistory.php](file://app/Models/ProductPriceHistory.php)
- [VariantInventory.php](file://app/Models/VariantInventory.php)
- [CosmeticFormula.php](file://app/Models/CosmeticFormula.php)
- [Warehouse.php](file://app/Models/Warehouse.php)
- [SalesOrderItem.php](file://app/Models/SalesOrderItem.php)
- [ActivityLog.php](file://app/Models/ActivityLog.php)
- [ProductObserver.php](file://app/Observers/ProductObserver.php)
- [ProductStockObserver.php](file://app/Observers/ProductStockObserver.php)
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
This document provides comprehensive documentation for Product Catalog Management within the ERP system. It covers product creation, categorization, variant management, and batch tracking for expiry-controlled products. The documentation explains the product lifecycle from creation to deactivation, SKU generation strategies, image management, and product attributes. It also details batch/lot tracking mechanisms, variant configurations for multi-attribute products, product import/export capabilities, barcode generation, and integration points with sales and purchase modules.

## Project Structure
The product catalog functionality spans models, controllers, importers, services, observers, and supporting domain models. The structure follows Laravel conventions with clear separation of concerns:
- Models define the data schema and relationships for products, categories, variants, batches, and stock.
- Controllers handle HTTP requests for CRUD operations, bulk actions, and lifecycle management.
- Importers process product data from spreadsheets with validation and error handling.
- Services encapsulate reusable business logic such as barcode generation.
- Observers monitor model events for audit trails and automated updates.

```mermaid
graph TB
subgraph "Controllers"
PC["ProductController"]
PCC["ProductCategoryController"]
end
subgraph "Models"
PM["Product"]
PCM["ProductCategory"]
PV["ProductVariant"]
PB["ProductBatch"]
PS["ProductStock"]
PW["Warehouse"]
PF["CosmeticFormula"]
PVIN["VariantInventory"]
PPH["ProductPriceHistory"]
SOI["SalesOrderItem"]
AL["ActivityLog"]
end
subgraph "Services"
BS["BarcodeService"]
end
subgraph "Import"
PI["ProductImport"]
end
PC --> PM
PC --> PS
PC --> PB
PC --> PW
PC --> AL
PCC --> PCM
PV --> PF
PV --> PVIN
PM --> PCM
PM --> PS
PM --> PB
PS --> PW
BS --> PM
PI --> PM
SOI --> PM
```

**Diagram sources**
- [ProductController.php:1-305](file://app/Http/Controllers/ProductController.php#L1-L305)
- [ProductCategoryController.php:1-69](file://app/Http/Controllers/ProductCategoryController.php#L1-L69)
- [Product.php:1-71](file://app/Models/Product.php#L1-L71)
- [ProductCategory.php:1-48](file://app/Models/ProductCategory.php#L1-L48)
- [ProductVariant.php:1-175](file://app/Models/ProductVariant.php#L1-L175)
- [ProductBatch.php:1-59](file://app/Models/ProductBatch.php#L1-L59)
- [ProductStock.php:1-15](file://app/Models/ProductStock.php#L1-L15)
- [Warehouse.php](file://app/Models/Warehouse.php)
- [CosmeticFormula.php](file://app/Models/CosmeticFormula.php)
- [VariantInventory.php](file://app/Models/VariantInventory.php)
- [ProductPriceHistory.php](file://app/Models/ProductPriceHistory.php)
- [SalesOrderItem.php](file://app/Models/SalesOrderItem.php)
- [ActivityLog.php](file://app/Models/ActivityLog.php)
- [BarcodeService.php](file://app/Services/BarcodeService.php)
- [ProductImport.php:1-171](file://app/Imports/ProductImport.php#L1-L171)

**Section sources**
- [ProductController.php:1-305](file://app/Http/Controllers/ProductController.php#L1-L305)
- [ProductCategoryController.php:1-69](file://app/Http/Controllers/ProductCategoryController.php#L1-L69)
- [Product.php:1-71](file://app/Models/Product.php#L1-L71)
- [ProductCategory.php:1-48](file://app/Models/ProductCategory.php#L1-L48)
- [ProductVariant.php:1-175](file://app/Models/ProductVariant.php#L1-L175)
- [ProductBatch.php:1-59](file://app/Models/ProductBatch.php#L1-L59)
- [ProductStock.php:1-15](file://app/Models/ProductStock.php#L1-L15)
- [ProductImport.php:1-171](file://app/Imports/ProductImport.php#L1-L171)
- [BarcodeService.php](file://app/Services/BarcodeService.php)

## Core Components
This section outlines the primary components involved in product catalog management and their responsibilities.

- Product: Central entity representing items with attributes such as name, SKU, pricing, stock thresholds, images, and expiry controls. Provides relationships to stock, batches, and movements.
- ProductCategory: Hierarchical classification system with parent-child relationships and creator attribution.
- ProductVariant: Variant management for multi-attribute products with automatic SKU generation, stock transactions, and status management.
- ProductBatch: Lot/batch tracking for expiry-controlled products with manufacturing/expiry dates and status tracking.
- ProductStock: Warehouse-level stock quantities linked to products.
- ProductImport: Excel-based importer with validation, deduplication by SKU, and error reporting.
- BarcodeService: Generates barcodes for products and variants.
- ProductPriceHistory: Historical pricing records for audit and analytics.
- VariantInventory: Transaction log for variant stock movements.
- Observers: Automatic activity logging and state synchronization for product and stock models.

**Section sources**
- [Product.php:1-71](file://app/Models/Product.php#L1-L71)
- [ProductCategory.php:1-48](file://app/Models/ProductCategory.php#L1-L48)
- [ProductVariant.php:1-175](file://app/Models/ProductVariant.php#L1-L175)
- [ProductBatch.php:1-59](file://app/Models/ProductBatch.php#L1-L59)
- [ProductStock.php:1-15](file://app/Models/ProductStock.php#L1-L15)
- [ProductImport.php:1-171](file://app/Imports/ProductImport.php#L1-L171)
- [BarcodeService.php](file://app/Services/BarcodeService.php)
- [ProductPriceHistory.php](file://app/Models/ProductPriceHistory.php)
- [VariantInventory.php](file://app/Models/VariantInventory.php)
- [ProductObserver.php](file://app/Observers/ProductObserver.php)
- [ProductStockObserver.php](file://app/Observers/ProductStockObserver.php)

## Architecture Overview
The product catalog architecture integrates controllers, models, services, and observers to support a complete lifecycle from creation to deactivation. Controllers orchestrate user interactions, models enforce data integrity and relationships, services encapsulate cross-cutting concerns like barcode generation, and observers maintain audit trails and derived state.

```mermaid
classDiagram
class Product {
+int tenant_id
+string name
+string sku
+string barcode
+string category
+string unit
+float price_buy
+float price_sell
+int stock_min
+string description
+string image
+bool is_active
+bool has_expiry
+int expiry_alert_days
+totalStock() int
+stockInWarehouse(warehouseId) int
}
class ProductCategory {
+int tenant_id
+string name
+string description
+int parent_id
+int created_by
+children() HasMany
+products() HasMany
+creator() BelongsTo
}
class ProductVariant {
+int tenant_id
+int formula_id
+string variant_name
+string sku
+string barcode
+array variant_attributes
+float price
+float cost_price
+int stock_quantity
+int reorder_level
+string status
+generateSKU(formulaCode, attributes) string
+addStock(quantity, type, notes) void
+removeStock(quantity, type, notes) void
+isLowStock() bool
+isOutOfStock() bool
}
class ProductBatch {
+int tenant_id
+int product_id
+int warehouse_id
+string batch_number
+int quantity
+float cost_price
+int quantity_remaining
+date manufacture_date
+date expiry_date
+string status
+daysUntilExpiry() int
+isExpired() bool
}
class ProductStock {
+int product_id
+int warehouse_id
+int quantity
}
class Warehouse {
+int id
+string name
+bool is_active
}
class CosmeticFormula {
+int id
+string formula_code
}
class VariantInventory {
+int variant_id
+datetime transaction_date
+string transaction_type
+int quantity
+int balance
+string notes
}
class ProductImport {
+collection(Collection) void
+rules() array
+customValidationMessages() array
+getStatistics() array
}
class BarcodeService {
+generate(product) string
+generateVariant(variant) string
}
class SalesOrderItem {
+int product_id
}
class ActivityLog {
+record(action, message, subject, old?, new?) void
}
ProductCategory "1" --> "*" Product : "has many"
Product "1" --> "*" ProductStock : "has many"
Product "1" --> "*" ProductBatch : "has many"
ProductStock "1" --> "1" Warehouse : "belongs to"
ProductVariant "1" --> "1" CosmeticFormula : "belongs to"
ProductVariant "1" --> "*" VariantInventory : "has many"
Product "1" --> "*" SalesOrderItem : "referenced by"
ProductController --> Product : "manages"
ProductController --> ProductStock : "manages"
ProductController --> ProductBatch : "manages"
ProductImport --> Product : "creates/updates"
BarcodeService --> Product : "generates barcode"
```

**Diagram sources**
- [Product.php:1-71](file://app/Models/Product.php#L1-L71)
- [ProductCategory.php:1-48](file://app/Models/ProductCategory.php#L1-L48)
- [ProductVariant.php:1-175](file://app/Models/ProductVariant.php#L1-L175)
- [ProductBatch.php:1-59](file://app/Models/ProductBatch.php#L1-L59)
- [ProductStock.php:1-15](file://app/Models/ProductStock.php#L1-L15)
- [Warehouse.php](file://app/Models/Warehouse.php)
- [CosmeticFormula.php](file://app/Models/CosmeticFormula.php)
- [VariantInventory.php](file://app/Models/VariantInventory.php)
- [ProductImport.php:1-171](file://app/Imports/ProductImport.php#L1-L171)
- [BarcodeService.php](file://app/Services/BarcodeService.php)
- [SalesOrderItem.php](file://app/Models/SalesOrderItem.php)
- [ActivityLog.php](file://app/Models/ActivityLog.php)

## Detailed Component Analysis

### Product Lifecycle Management
The product lifecycle spans creation, updates, activation/deactivation, and deletion with safeguards against disrupting existing sales.

```mermaid
sequenceDiagram
participant U as "User"
participant C as "ProductController"
participant M as "Product Model"
participant S as "ProductStock"
participant B as "ProductBatch"
participant W as "Warehouse"
participant L as "ActivityLog"
U->>C : "POST /products (create)"
C->>M : "validate and create Product"
alt "Initial stock provided"
C->>S : "create ProductStock"
C->>L : "log product_created"
opt "Expiry control enabled"
C->>B : "create ProductBatch"
end
end
C-->>U : "Success response"
U->>C : "PATCH /products/{id} (update)"
C->>M : "validate and update Product"
C->>L : "log product_updated"
C-->>U : "Success response"
U->>C : "DELETE /products/{id} (destroy)"
C->>M : "check sales history"
alt "Has sales"
C->>M : "set is_active=false"
C->>L : "log product_deactivated"
else "No sales"
C->>S : "delete related stock"
C->>M : "delete Product"
C->>L : "log product_deleted"
end
C-->>U : "Success response"
```

**Diagram sources**
- [ProductController.php:157-303](file://app/Http/Controllers/ProductController.php#L157-L303)
- [Product.php:1-71](file://app/Models/Product.php#L1-L71)
- [ProductStock.php:1-15](file://app/Models/ProductStock.php#L1-L15)
- [ProductBatch.php:1-59](file://app/Models/ProductBatch.php#L1-L59)
- [Warehouse.php](file://app/Models/Warehouse.php)
- [ActivityLog.php](file://app/Models/ActivityLog.php)
- [SalesOrderItem.php](file://app/Models/SalesOrderItem.php)

Key lifecycle behaviors:
- Creation validates uniqueness by name, generates SKU if not provided, stores images, initializes stock, and optionally creates batches for expiry-controlled items.
- Updates allow image replacement and metadata changes while preserving derived state.
- Deletion checks prior sales; if any sales exist, deactivation is preferred to preserve historical integrity; otherwise, soft deletion occurs with cleanup of related stock.

**Section sources**
- [ProductController.php:157-303](file://app/Http/Controllers/ProductController.php#L157-L303)
- [Product.php:1-71](file://app/Models/Product.php#L1-L71)
- [ProductStock.php:1-15](file://app/Models/ProductStock.php#L1-L15)
- [ProductBatch.php:1-59](file://app/Models/ProductBatch.php#L1-L59)
- [ActivityLog.php](file://app/Models/ActivityLog.php)
- [SalesOrderItem.php](file://app/Models/SalesOrderItem.php)

### SKU Generation Strategies
SKU generation varies by entity:
- Products: Auto-generated from the product name with a random suffix when not provided during creation.
- Variants: Generated programmatically from a formula code and variant attribute values, ensuring uniqueness and readability.

```mermaid
flowchart TD
Start(["SKU Generation"]) --> Entity{"Entity Type"}
Entity --> |Product| PGen["Generate from product name<br/>+ random suffix"]
Entity --> |Variant| VGen["Combine formula code<br/>+ attribute codes<br/>+ random suffix"]
PGen --> Output["Return SKU"]
VGen --> Output
```

**Diagram sources**
- [ProductController.php:184-184](file://app/Http/Controllers/ProductController.php#L184-L184)
- [ProductVariant.php:63-74](file://app/Models/ProductVariant.php#L63-L74)

**Section sources**
- [ProductController.php:184-184](file://app/Http/Controllers/ProductController.php#L184-L184)
- [ProductVariant.php:63-74](file://app/Models/ProductVariant.php#L63-L74)

### Batch/Lot Tracking for Expiry-Controlled Products
Batch tracking supports manufacturing date, expiry date, remaining quantity, and status. It includes helpers to compute days until expiry and detect expiration.

```mermaid
flowchart TD
Start(["Batch Entry"]) --> Create["Create ProductBatch<br/>with product_id, warehouse_id,<br/>batch_number, quantity, dates"]
Create --> Active{"Quantity > 0<br/>and status='active'?"}
Active --> |Yes| Track["Track daysUntilExpiry()<br/>and isExpired()"]
Active --> |No| Inactive["Mark as inactive"]
Track --> Alert{"Expiry within alert threshold?"}
Alert --> |Yes| Notify["Trigger expiry alerts"]
Alert --> |No| Monitor["Continue monitoring"]
Inactive --> End(["End"])
Notify --> End
Monitor --> End
```

**Diagram sources**
- [ProductBatch.php:31-57](file://app/Models/ProductBatch.php#L31-L57)

**Section sources**
- [ProductBatch.php:1-59](file://app/Models/ProductBatch.php#L1-L59)

### Variant Management and Multi-Attribute Configurations
Variants enable multi-attribute product configurations with automatic SKU generation, stock transactions, and status management. Attributes are stored as arrays and formatted for display.

```mermaid
classDiagram
class ProductVariant {
+int tenant_id
+int formula_id
+string variant_name
+string sku
+string barcode
+array variant_attributes
+float price
+float cost_price
+int stock_quantity
+int reorder_level
+string status
+generateSKU(formulaCode, attributes) string
+addStock(quantity, type, notes) void
+removeStock(quantity, type, notes) void
+isLowStock() bool
+isOutOfStock() bool
+getMarginAttribute() float
+getFormattedAttributesAttribute() string
}
class VariantInventory {
+int variant_id
+datetime transaction_date
+string transaction_type
+int quantity
+int balance
+string notes
}
class CosmeticFormula {
+int id
+string formula_code
}
ProductVariant --> VariantInventory : "has many"
ProductVariant --> CosmeticFormula : "belongs to"
```

**Diagram sources**
- [ProductVariant.php:1-175](file://app/Models/ProductVariant.php#L1-L175)
- [VariantInventory.php](file://app/Models/VariantInventory.php)
- [CosmeticFormula.php](file://app/Models/CosmeticFormula.php)

**Section sources**
- [ProductVariant.php:1-175](file://app/Models/ProductVariant.php#L1-L175)
- [VariantInventory.php](file://app/Models/VariantInventory.php)
- [CosmeticFormula.php](file://app/Models/CosmeticFormula.php)

### Product Import/Export Functionality
Product import reads spreadsheet rows, validates fields, resolves categories by name, and either creates or updates products by SKU. Export capabilities leverage the broader export framework for inventory and financial reports.

```mermaid
sequenceDiagram
participant U as "User"
participant C as "ProductImport"
participant M as "Product Model"
participant Cat as "Category"
U->>C : "Upload CSV/Excel"
C->>C : "Iterate rows and validate"
C->>Cat : "Resolve category by name"
alt "SKU exists"
C->>M : "Update existing product"
else "New SKU"
C->>M : "Create new product"
end
C-->>U : "Import statistics and errors"
```

**Diagram sources**
- [ProductImport.php:35-81](file://app/Imports/ProductImport.php#L35-L81)

**Section sources**
- [ProductImport.php:1-171](file://app/Imports/ProductImport.php#L1-L171)

### Barcode Generation
Barcodes are generated via a dedicated service integrated with product and variant entities. This enables standardized labeling and scanning across sales and inventory modules.

```mermaid
sequenceDiagram
participant U as "User"
participant C as "ProductController"
participant S as "BarcodeService"
participant M as "Product Model"
U->>C : "Request barcode for product"
C->>S : "generate(product)"
S->>M : "retrieve product details"
S-->>C : "return barcode data"
C-->>U : "display or download barcode"
```

**Diagram sources**
- [ProductController.php:157-244](file://app/Http/Controllers/ProductController.php#L157-L244)
- [BarcodeService.php](file://app/Services/BarcodeService.php)

**Section sources**
- [ProductController.php:157-244](file://app/Http/Controllers/ProductController.php#L157-L244)
- [BarcodeService.php](file://app/Services/BarcodeService.php)

### Integration with Sales/Purchase Modules
Products integrate with sales and purchase workflows:
- SalesOrderItem references products to prevent deletion if sales exist.
- ProductStock tracks warehouse-level quantities for inventory management.
- ActivityLog records product lifecycle events for auditability.

```mermaid
graph LR
SOI["SalesOrderItem"] --> PM["Product"]
PS["ProductStock"] --> PM
PM --> AL["ActivityLog"]
```

**Diagram sources**
- [SalesOrderItem.php](file://app/Models/SalesOrderItem.php)
- [ProductStock.php:1-15](file://app/Models/ProductStock.php#L1-L15)
- [Product.php:1-71](file://app/Models/Product.php#L1-L71)
- [ActivityLog.php](file://app/Models/ActivityLog.php)

**Section sources**
- [SalesOrderItem.php](file://app/Models/SalesOrderItem.php)
- [ProductStock.php:1-15](file://app/Models/ProductStock.php#L1-L15)
- [Product.php:1-71](file://app/Models/Product.php#L1-L71)
- [ActivityLog.php](file://app/Models/ActivityLog.php)

## Dependency Analysis
The product catalog module exhibits cohesive internal dependencies and controlled external integrations:
- Controllers depend on models and services for business operations.
- Models define relationships and scopes for querying and filtering.
- Importers depend on models for persistence and validation.
- Observers depend on models and external systems for audit and notifications.
- Services encapsulate cross-cutting concerns like barcode generation.

```mermaid
graph TB
PC["ProductController"] --> PM["Product"]
PC --> PS["ProductStock"]
PC --> PB["ProductBatch"]
PC --> PW["Warehouse"]
PC --> AL["ActivityLog"]
PCC["ProductCategoryController"] --> PCM["ProductCategory"]
PI["ProductImport"] --> PM
PI --> Cat["Category"]
BS["BarcodeService"] --> PM
BS --> PV["ProductVariant"]
PO["ProductObserver"] --> PM
PSO["ProductStockObserver"] --> PS
```

**Diagram sources**
- [ProductController.php:1-305](file://app/Http/Controllers/ProductController.php#L1-L305)
- [ProductCategoryController.php:1-69](file://app/Http/Controllers/ProductCategoryController.php#L1-L69)
- [Product.php:1-71](file://app/Models/Product.php#L1-L71)
- [ProductStock.php:1-15](file://app/Models/ProductStock.php#L1-L15)
- [ProductBatch.php:1-59](file://app/Models/ProductBatch.php#L1-L59)
- [Warehouse.php](file://app/Models/Warehouse.php)
- [ActivityLog.php](file://app/Models/ActivityLog.php)
- [ProductImport.php:1-171](file://app/Imports/ProductImport.php#L1-L171)
- [ProductCategory.php:1-48](file://app/Models/ProductCategory.php#L1-L48)
- [BarcodeService.php](file://app/Services/BarcodeService.php)
- [ProductObserver.php](file://app/Observers/ProductObserver.php)
- [ProductStockObserver.php](file://app/Observers/ProductStockObserver.php)

**Section sources**
- [ProductController.php:1-305](file://app/Http/Controllers/ProductController.php#L1-L305)
- [ProductCategoryController.php:1-69](file://app/Http/Controllers/ProductCategoryController.php#L1-L69)
- [Product.php:1-71](file://app/Models/Product.php#L1-L71)
- [ProductStock.php:1-15](file://app/Models/ProductStock.php#L1-L15)
- [ProductBatch.php:1-59](file://app/Models/ProductBatch.php#L1-L59)
- [Warehouse.php](file://app/Models/Warehouse.php)
- [ActivityLog.php](file://app/Models/ActivityLog.php)
- [ProductImport.php:1-171](file://app/Imports/ProductImport.php#L1-L171)
- [ProductCategory.php:1-48](file://app/Models/ProductCategory.php#L1-L48)
- [BarcodeService.php](file://app/Services/BarcodeService.php)
- [ProductObserver.php](file://app/Observers/ProductObserver.php)
- [ProductStockObserver.php](file://app/Observers/ProductStockObserver.php)

## Performance Considerations
- Indexing: Ensure tenant_id, SKU, and foreign keys (product_id, warehouse_id) are indexed to optimize queries for filtering, joins, and lookups.
- Pagination: Controllers already paginate results; maintain reasonable page sizes to balance responsiveness and memory usage.
- Image Storage: Store images efficiently and consider CDN integration for reduced latency.
- Batch Operations: Use bulk actions judiciously; validate tenant ownership before processing to avoid unnecessary overhead.
- Observers: Keep observer logic lightweight to minimize impact on write operations.

## Troubleshooting Guide
Common issues and resolutions:
- Duplicate product names: Creation prevents duplicates by name within a tenant; adjust naming or use SKUs for differentiation.
- Insufficient stock for variant removal: Variant removal enforces stock availability; reconcile inventory before adjustments.
- Deletion blocked by sales: If a product has sales history, deactivate instead of deleting to preserve audit trails.
- Import errors: Review import statistics and error logs to identify malformed rows or invalid categories; correct data and re-import.
- Expiry alerts: Configure expiry alert days appropriately; use batch scopes to identify expiring items proactively.

**Section sources**
- [ProductController.php:180-182](file://app/Http/Controllers/ProductController.php#L180-L182)
- [ProductVariant.php:94-112](file://app/Models/ProductVariant.php#L94-L112)
- [ProductController.php:291-296](file://app/Http/Controllers/ProductController.php#L291-L296)
- [ProductImport.php:161-169](file://app/Imports/ProductImport.php#L161-L169)
- [ProductBatch.php:47-57](file://app/Models/ProductBatch.php#L47-L57)

## Conclusion
The Product Catalog Management system provides a robust foundation for managing products, categories, variants, and batches with strong lifecycle controls, SKU generation, image handling, and integration with sales and purchase workflows. The modular design, supported by controllers, models, importers, services, and observers, ensures scalability, maintainability, and auditability across tenants and warehouses.