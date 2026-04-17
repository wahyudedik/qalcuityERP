# Qalcuity ERP

Qalcuity ERP adalah sistem manajemen bisnis berbasis web multi-tenant yang dibangun dengan Laravel. Dirancang untuk mendukung operasional berbagai jenis bisnis dalam satu platform terpadu, dilengkapi asisten AI berbasis Google Gemini.

> Stack: Laravel 13 · PHP 8.3+ · MySQL · Vite

---

## Modul Utama

### 💰 Akuntansi & Keuangan
Jurnal umum, buku besar, neraca, laporan laba rugi, arus kas, rekonsiliasi bank, multi-currency, pajak (PPN/PPh), cost center, amortisasi, dan laporan konsolidasi multi-perusahaan.

### 📦 Inventori & Gudang
Manajemen produk multi-gudang, transfer stok, batch/lot tracking, landed cost, konsinyasi, costing (FIFO/Average), barcode & QR Code produk, sertifikat keaslian digital, dan WMS.

### 🛒 Penjualan & Pembelian
Quotation, sales order, invoice, purchase order, delivery order, retur, down payment, manajemen supplier, price list, diskon, dan komisi sales.

### 👥 HRM & Payroll
Data karyawan, absensi, shift, lembur, rekrutmen, pelatihan, penggajian otomatis, payslip, reimbursement, ESS (Employee Self Service), dan integrasi fingerprint.

### 🏪 Point of Sale
Kasir berbasis web dengan dukungan barcode scanner, manajemen sesi kasir, dan integrasi payment gateway.

### 📊 Laporan & Analitik
Dashboard dengan widget kustom, laporan keuangan/penjualan/inventori/HRM, analitik lanjutan (segmentasi pelanggan, prediksi arus kas, analisis churn), export Excel & PDF.

### 🤖 AI Assistant
Asisten berbasis Google Gemini yang memahami konteks bisnis — menjawab pertanyaan seputar laporan, stok, karyawan, dan memberikan rekomendasi berbasis data.

---

## Integrasi

| Kategori | Platform |
|----------|----------|
| E-Commerce | Shopee, Tokopedia, Lazada |
| Payment Gateway | Midtrans, Xendit, Duitku |
| Pengiriman | RajaOngkir, JNE, J&T, Sicepat |
| Komunikasi | WhatsApp (Fonnte / Business API), Telegram Bot |
| Akuntansi | Jurnal.id, Accurate Online |
| Auth | Google OAuth |

---

## Modul Industri Khusus

| Modul | Deskripsi |
|-------|-----------|
| **Telecom / ISP** | Manajemen pelanggan ISP, integrasi MikroTik RouterOS, billing otomatis, voucher, monitoring bandwidth real-time |
| **Healthcare** | Rekam medis, jadwal dokter, rawat inap, BPJS, apotek |
| **F&B** | Manajemen menu, resep, dapur, meja, reservasi |
| **Hotel** | Reservasi kamar, housekeeping, front desk |
| **Manufaktur** | Bill of Materials, work order, production planning |
| **Konstruksi** | RAB, progress proyek, mix design beton |
| **Pertanian** | Plot lahan, siklus tanam, log panen |
| **Peternakan** | Manajemen ternak, kesehatan hewan |
| **Perikanan** | Manajemen kolam, panen, kualitas air |
| **Kosmetik** | Formulasi produk, batch produksi, compliance |
| **Tour & Travel** | Paket wisata, booking, itinerary |
| **Percetakan** | Job order cetak, estimasi biaya, produksi |

---

## Fitur Platform

- **Multi-Tenant** — isolasi data penuh per tenant dengan manajemen subscription
- **Approval Workflow** — alur persetujuan multi-level yang dapat dikonfigurasi
- **Automation Builder** — workflow otomatis berbasis trigger & aksi
- **Custom Fields** — tambah field kustom pada entitas bisnis
- **Document Management** — template, versioning, tanda tangan digital
- **Audit Trail** — log perubahan data lengkap
- **Helpdesk** — tiket dukungan internal
- **Loyalty Program** — poin reward pelanggan
- **Gamifikasi** — poin, achievement, leaderboard karyawan
- **KPI Tracking** — pantau indikator kinerja utama
- **Push Notification** — notifikasi browser real-time
- **Affiliate Program** — sistem referral dengan komisi
- **GDPR Tools** — manajemen data pribadi & consent
