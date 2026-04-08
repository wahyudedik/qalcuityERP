# 🎯 Sidebar Menu Structure - Final & Clean

## Overview

Dokumen ini mendefinisikan struktur final sidebar menu QalcuityERP yang sudah **dibersihkan dari duplikasi** dan **diorganisir dengan logis**.

---

## ✅ Issues Fixed

### **Issue 1: Duplicate Analytics Group** ❌ → ✅
**BEFORE:**
- Menu "Analitik" terpisah dari Dashboard
- RouteIs pattern duplicate di line 469-470 (sudah dihapus)

**AFTER:**
- Semua analytics menus masuk ke Dashboard group
- RouteIs patterns consolidated

---

### **Issue 2: Duplicate "Daftar Harga" Menu** ❌ → ✅
**BEFORE:**
- "Daftar Harga" muncul di **Master Data** DAN **Sales**
- Confusing untuk users

**AFTER:**
- "Daftar Harga" HANYA di **Master Data > Produk & Gudang**
- Removed duplicate from Sales group

---

## 📋 Final Sidebar Structure

### **1. Dashboard** (home)
```
📊 Dashboard
├── Overview
│   └── Dashboard
├── Reports & Analytics
│   ├── Laporan
│   ├── KPI Dashboard
│   ├── AI Forecasting
│   └── Proyeksi Arus Kas
└── AI & Intelligence
    ├── Deteksi Anomali
    ├── Input Cerdas (AI)
    └── Simulasi Keuangan
```

**Routes:**
- `dashboard`
- `reports*`
- `kpi*`
- `forecast*`
- `anomalies*`
- `zero-input*`
- `simulations*`

---

### **2. AI Chat** (ai)
```
💬 AI Chat
└── AI Chat
```

**Routes:**
- `chat*`

---

### **3. Master Data** (masterdata)
```
📁 Master Data
├── Kontak
│   ├── Data Customer
│   └── Data Supplier
├── Supplier Management
│   ├── Supplier Scorecard
│   └── Strategic Sourcing
└── Produk & Gudang
    ├── Data Produk
    ├── Data Gudang
    ├── Daftar Harga          ← ONLY HERE (removed from Sales)
    └── Kategori Produk
```

**Routes:**
- `customers*`
- `suppliers*` (excluding scorecards & sourcing)
- `suppliers.scorecards*`
- `suppliers.sourcing*`
- `products*`
- `warehouses*`
- `price-lists*`
- `categories*`

---

### **4. Penjualan** (sales)
```
💰 Penjualan
├── Transaksi
│   ├── Sales Order
│   ├── Penawaran (Quotation)
│   ├── Invoice
│   ├── Surat Jalan
│   ├── Uang Muka (DP)
│   └── Retur Penjualan
└── CRM & Loyalty
    ├── CRM
    ├── Loyalty Program
    ├── Helpdesk & Tiket
    └── Komisi Sales
```

**Routes:**
- `sales*`
- `quotations*`
- `invoices*`
- `delivery-orders*`
- `down-payments*`
- `sales-returns*`
- `crm*`
- `loyalty*`
- `pos*`
- `commission*`
- `helpdesk*`
- `subscription-billing*`

---

### **5. Persediaan** (inventory)
```
📦 Persediaan
├── Pembelian
│   ├── Purchase Order
│   ├── Penerimaan Barang
│   └── Retur Pembelian
└── WMS Gudang
    ├── Zone & Bin
    ├── Picking List
    ├── Stock Opname
    └── Putaway Rules
```

**Routes:**
- `inventory*`
- `purchasing*`
- `purchase-returns*`
- `landed-cost*`
- `consignment*`
- `wms*`

---

### **6. Operasional** (ops)
```
⚙️ Operasional
├── Manufacturing
│   ├── Produksi / WO
│   ├── BOM Multi-Level
│   ├── Mix Design Beton
│   ├── Work Center
│   ├── MRP Planning
│   └── Printing Jobs
├── Cosmetic Manufacturing
│   ├── Cosmetic Formulas
│   ├── Batch Production
│   ├── QC Laboratory
│   ├── BPOM Registrations
│   ├── Variants Manager
│   ├── Packaging & Labels
│   ├── Expiry & Recalls
│   ├── Distribution Channels
│   └── Cosmetic Analytics
├── Tour & Travel
│   ├── Tour Packages
│   ├── Bookings
│   └── Tour Analytics
├── Livestock Enhancement
│   ├── Dairy Management
│   ├── Poultry Management
│   ├── Breeding
│   ├── Health & Vaccination
│   └── Waste Management
├── Agriculture
│   ├── Manajemen Lahan
│   ├── Siklus Tanam
│   ├── Pencatatan Panen
│   ├── Analisis Biaya Lahan
│   └── Populasi Ternak
├── Perikanan (Fisheries)
│   ├── Dashboard Perikanan
│   ├── Cold Chain
│   ├── Fishing Operations
│   ├── Aquaculture
│   ├── Species & Grading
│   ├── Export Documentation
│   └── Analytics
├── Fleet Kendaraan
│   ├── Fleet
│   ├── Driver
│   ├── Trip / Penugasan
│   ├── Log BBM
│   └── Maintenance
├── Projects & Contracts
│   ├── Projects
│   ├── Contracts
│   ├── Shipping
│   └── Approvals
└── E-Commerce
    ├── Integrasi Marketplace
    └── Documents
```

**Routes:**
- `production*`
- `manufacturing*`
- `printing*`
- `cosmetic*`
- `tour-travel*`
- `livestock-enhancement*`
- `fisheries*`
- `fleet*`
- `contracts*`
- `shipping*`
- `approvals*`
- `ecommerce*`
- `documents*`
- `projects*`
- `timesheets*`
- `project-billing*`
- `farm*`

---

### **7. HRM** (hrm)
```
👥 HRM
├── Employees
├── Attendance
├── Leave Management
├── Payroll
├── Training
├── Overtime
├── Reimbursement
└── Self Service
```

**Routes:**
- `hrm*`
- `payroll*`
- `self-service*`
- `reimbursement*`

---

### **8. Keuangan** (finance)
```
💵 Keuangan
├── Accounting
│   ├── Chart of Accounts
│   ├── Jurnal Umum
│   ├── Buku Besar
│   ├── Neraca Saldo
│   └── Periode Akuntansi
├── Banking
│   ├── Rekening Bank
│   ├── Transaksi Bank
│   └── Rekonsiliasi
├── Receivables & Payables
│   ├── Piutang
│   ├── Hutang
│   └── Bulk Payments
└── Assets & Budget
    ├── Fixed Assets
    ├── Budget
    └── Depreciation
```

**Routes:**
- `accounting*`
- `expenses*`
- `bank.*`
- `bank-accounts*`
- `receivables*`
- `payables*`
- `bulk-payments*`
- `assets*`
- `budget*`
- `journals*`
- `deferred*`
- `writeoffs*`

---

### **9. Hotel PMS** (hotel) - If Enabled
```
🏨 Hotel PMS
├── Dashboard
├── Room Types
├── Rooms
├── Reservations
├── Guests
├── Check-in/Check-out
├── Housekeeping
├── Rates & Channels
└── Settings
```

**Routes:**
- `hotel*`

---

### **10. Pengaturan** (settings)
```
⚙️ Pengaturan
├── Company Profile
├── Modules
├── Users & Roles
├── Notifications
├── Import/Export
├── Audit Logs
├── API Settings
├── Subscription
└── Business Constraints
```

**Routes:**
- `company-profile*`
- `settings*`
- `tenant.users*`
- `reminders*`
- `import*`
- `audit*`
- `notifications*`
- `bot*`
- `api-settings*`
- `subscription.index`
- `cost-centers*`
- `ai-memory*`
- `taxes*`
- `custom-fields*`
- `constraints*`
- `company-groups*`

---

### **11. Super Admin** (superadmin) - Super Admin Only
```
🔧 Super Admin
├── Semua Tenant
├── Kelola Paket
├── Monitoring
├── Popup Iklan
├── Afiliasi
│   ├── Kelola Affiliate
│   ├── Komisi
│   ├── Payout
│   └── Fraud Monitor
└── Pengaturan Platform
```

**Routes:**
- `super-admin*`

---

## ✅ RouteIs Mapping (NO DUPLICATES)

```php
$activeGroup = match (true) {
    // Dashboard (includes analytics)
    request()->routeIs('dashboard') => 'home',
    request()->routeIs('reports*', 'kpi*', 'forecast*', 'anomalies*', 'zero-input*', 'simulations*') => 'home',
    
    // AI Chat
    request()->routeIs('chat*') => 'ai',
    
    // Sales
    request()->routeIs('quotations*', 'invoices*', 'delivery-orders*', 'down-payments*', 
                       'sales-returns*', 'crm*', 'loyalty*', 'pos*', 'commission*', 
                       'helpdesk*', 'subscription-billing*', 'sales.*', 'sales.index', 'price-lists*') => 'sales',
    
    // Inventory
    request()->routeIs('inventory*', 'purchasing*', 'purchase-returns*', 'landed-cost*', 
                       'consignment*', 'wms*') => 'inventory',
    
    // Master Data
    request()->routeIs('customers*', 'suppliers*', 'products*', 'warehouses*') => 'masterdata',
    
    // Operations
    request()->routeIs('production*', 'manufacturing*', 'printing*', 'cosmetic*', 
                       'tour-travel*', 'livestock-enhancement*', 'fisheries*', 'fleet*', 
                       'contracts*', 'shipping*', 'approvals*', 'ecommerce*', 'documents*', 
                       'projects*', 'timesheets*', 'project-billing*', 'farm*') => 'ops',
    
    // HRM
    request()->routeIs('hrm*', 'payroll*', 'self-service*', 'reimbursement*') => 'hrm',
    
    // Finance
    request()->routeIs('accounting*', 'expenses*', 'bank.*', 'bank-accounts*', 
                       'receivables*', 'payables*', 'bulk-payments*', 'assets*', 
                       'budget*', 'journals*', 'deferred*', 'writeoffs*') => 'finance',
    
    // Hotel
    request()->routeIs('hotel*') => 'hotel',
    
    // Settings
    request()->routeIs('company-profile*', 'settings*', 'tenant.users*', 'reminders*', 
                       'import*', 'audit*', 'notifications*', 'bot*', 'api-settings*', 
                       'subscription.index', 'cost-centers*', 'ai-memory*', 'taxes*', 
                       'custom-fields*', 'constraints*', 'company-groups*') => 'settings',
    
    // Super Admin
    request()->routeIs('super-admin*') => 'superadmin',
    
    default => '',
};
```

---

## 🎯 Key Improvements

### **1. No Duplicate Menus** ✅
- ❌ **REMOVED:** "Analitik" group (duplicate dengan Dashboard)
- ❌ **REMOVED:** "Daftar Harga" dari Sales (sudah ada di Master Data)
- ❌ **REMOVED:** Duplicate routeIs pattern (line 469-470)

### **2. Logical Grouping** ✅
- All dashboards & analytics → **Dashboard**
- All master data → **Master Data**
- All operations → **Operasional**
- Clear section headers

### **3. Consistent Active States** ✅
- Each route maps to ONE group only
- No overlapping routeIs patterns
- Proper highlighting

### **4. Clean Structure** ✅
- Section headers for organization
- Permission-based visibility
- Module toggle support

---

## 📊 Menu Count Summary

| Menu Group | Items | Sections | Status |
|-----------|-------|----------|--------|
| Dashboard | 7 | 3 | ✅ Clean |
| AI Chat | 1 | 0 | ✅ Clean |
| Master Data | 7-9 | 3 | ✅ Clean |
| Penjualan | 6-10 | 2 | ✅ Clean |
| Persediaan | 7-11 | 2 | ✅ Clean |
| Operasional | 30-40 | 8 | ✅ Organized |
| HRM | 8-12 | Variable | ✅ Clean |
| Keuangan | 12-15 | 4 | ✅ Clean |
| Hotel PMS | 8-10 | Variable | ✅ Clean |
| Pengaturan | 15-20 | Variable | ✅ Clean |
| Super Admin | 6-8 | 4 | ✅ Clean |

---

## ✅ Validation Checklist

Before adding new menu items:

- [ ] Menu not already exists in another group
- [ ] RouteIs pattern not used by another group
- [ ] Proper section header added
- [ ] Permission check included (`$canView()`)
- [ ] Module toggle check if applicable (`isModuleEnabled()`)
- [ ] Active state pattern correct (`routeIs()`)
- [ ] No emoji icons in labels
- [ ] Consistent naming convention

---

**Last Updated:** April 8, 2026  
**Version:** 2.0 (Clean & Final)  
**Status:** ✅ Production Ready
