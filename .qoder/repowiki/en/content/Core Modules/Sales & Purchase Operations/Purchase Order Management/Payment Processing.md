
# Payment Processing

<cite>
**Referenced Files in This Document**
- [InvoiceController.php](file://app/Http/Controllers/InvoiceController.php)
- [InvoicePaymentService.php](file://app/Services/InvoicePaymentService.php)
- [PaymentGatewayService.php](file://app/Services/PaymentGatewayService.php)
- [CashFlowProjectionService.php](file://app/Services/CashFlowProjectionService.php)
- [TransactionLinkService.php](file://app/Services/TransactionLinkService.php)
- [Invoice.php](file://app/Models/Invoice.php)
- [InvoiceInstallment.php](file://app/Models/InvoiceInstallment.php)
- [Payment.php](file://app/Models/Payment.php)
- [Payable.php](file://app/Models/Payable.php)
- [PaymentTransaction.php](file://app/Models/PaymentTransaction.php)
- [PaymentCallback.php](file://app/Models/PaymentCallback.php)
- [TenantPaymentGateway.php](file://app/Models/TenantPaymentGateway.php)
- [BankAccount.php](file://app/Models/BankAccount.php)
- [BankStatement.php](file://app/Models/BankStatement.php)
- [BankTransaction.php](file://app/Models/BankTransaction.php)
- [2026_03_23_000032_create_invoice_installments_table.php](file://database/migrations/2026_03_23_000032_create_invoice_installments_table.php)
- [2026_04_04_900000_create_payment_gateway_tables.php](file://database/migrations/2026_04_04_900000_create_payment_gateway_tables.php)
- [2026_01_01_000027_create_advanced_features_tables.php](file://database/migrations/2026_01_01_000027_create_advanced_features_tables.php)
- [TransactionConsistencyTest.php](file://tests/Feature/TransactionConsistencyTest.php)
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
This document explains the end-to-end payment processing workflows in the system, covering payable creation, invoice validation, payment authorization, scheduling and installment planning, cash flow management, multiple payment methods, gateway integration, reconciliation, automated processing, tracking, and financial reporting. It also outlines integration touchpoints with accounting systems and bank feeds.

## Project Structure
Payment processing spans controllers, services, models, migrations, and supporting utilities:
- Controllers orchestrate user actions (e.g., recording payments).
- Services encapsulate business logic (e.g., invoice payment, gateway operations).
- Models represent domain entities (e.g., invoices, payments, installments, bank accounts).
- Migrations define schema for payment, gateway, and bank reconciliation tables.
- Utilities support linking transactions and cash flow projections.

```mermaid
graph TB
  subgraph "HTTP Layer"
    IC["InvoiceController"]
  end

  subgraph "Services"
    IPS["InvoicePaymentService"]
    PG["PaymentGatewayService"]
    TLS["TransactionLinkService"]
    CFP["CashFlowProjectionService"]
  end

  subgraph "Domain Models"
    INV["Invoice"]
    PAY["Payment"]
    INST["InvoiceInstallment"]
    PT["PaymentTransaction"]
    PC["PaymentCallback"]
    TP["TenantPaymentGateway"]
    BA["BankAccount"]
    BST["BankStatement"]
    BT["BankTransaction"]
  end

  IC --> IPS
  IC --> PG
  IPS --> INV
  IPS --> PAY
  IPS --> TLS
  PG --> PT
  PG --> PC
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP......
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP......
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP
  PG --> TP......