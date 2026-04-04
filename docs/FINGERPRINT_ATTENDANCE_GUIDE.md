# Panduan Penggunaan Fitur Fingerprint untuk Absensi

## 📋 Overview

Fitur ini memungkinkan sistem absensi ERP terhubung dengan perangkat fingerprint biometric. Setiap tenant dapat mengkonfigurasi dan mengelola perangkat fingerprint mereka sendiri secara mandiri.

### Fitur Utama

✅ **Multi-Tenant Isolation** - Setiap tenant memiliki konfigurasi perangkat sendiri  
✅ **Multi-Device Support** - Mendukung multiple perangkat fingerprint per tenant  
✅ **Vendor Support** - ZKTeco, Suprema, dan Generic devices  
✅ **Real-time Sync** - Sinkronisasi data absensi dari perangkat ke sistem  
✅ **Employee Registration** - Registrasi fingerprint karyawan melalui UI  
✅ **Webhook Integration** - API endpoint untuk menerima data dari perangkat  
✅ **Attendance Processing** - Otomatis memproses check-in/check-out  

---

## 🏗️ Arsitektur Sistem

```
┌─────────────────┐
│ Fingerprint     │
│ Device          │
│ (ZKTeco/etc)    │
└────────┬────────┘
         │ HTTP POST / Webhook
         ▼
┌─────────────────────────┐
│ API Endpoint            │
│ /api/webhooks/fingerprint/attendance │
└────────┬────────────────┘
         │
         ▼
┌─────────────────────────┐
│ FingerprintWebhook      │
│ Controller              │
└────────┬────────────────┘
         │
         ▼
┌─────────────────────────┐
│ FingerprintDevice       │
│ Service                 │
└────────┬────────────────┘
         │
         ├─► FingerprintAttendanceLog (raw data)
         │
         └─► Attendance (processed)
```

---

## 🚀 Setup & Konfigurasi

### 1. Menambahkan Perangkat Fingerprint

1. Login sebagai admin/manager tenant
2. Navigasi ke **HRM → Fingerprint → Devices**
3. Klik tombol **"Tambah Perangkat"**
4. Isi form konfigurasi:
   - **Nama Perangkat**: Nama deskriptif (contoh: "Fingerprint Lobby Utama")
   - **Device ID**: ID unik untuk identifikasi (contoh: "FP001")
   - **Vendor**: Pilih vendor perangkat (ZKTeco, Suprema, atau Generic)
   - **Model**: Model perangkat (opsional)
   - **Protokol**: TCP/IP, UDP, HTTP, atau HTTPS
   - **IP Address**: Alamat IP perangkat dalam jaringan
   - **Port**: Port komunikasi (default 4370 untuk ZKTeco)
   - **API Key/Secret Key**: Untuk autentikasi (opsional)
5. Klik **"Simpan Perangkat"**

### 2. Testing Koneksi

Setelah menambahkan perangkat:
1. Buka halaman detail perangkat
2. Klik tombol **"Test Koneksi"**
3. Sistem akan mencoba terhubung ke perangkat
4. Status koneksi akan ditampilkan

### 3. Mendaftarkan Fingerprint Karyawan

1. Navigasi ke **HRM → Fingerprint → Employees**
2. Cari karyawan yang ingin didaftarkan
3. Klik **"Daftarkan"** pada kolom Aksi
4. Pilih perangkat fingerprint yang akan digunakan
5. Mintakan karyawan menempelkan jari pada perangkat
6. Masukkan UID yang muncul dari perangkat
7. Klik **"Daftarkan Fingerprint"**

---

## 📡 Integrasi dengan Perangkat Fingerprint

### Metode 1: Webhook Push (Recommended)

Perangkat fingerprint mengirimkan data absensi ke sistem secara real-time via HTTP POST.

**Endpoint:** `POST /api/webhooks/fingerprint/attendance`

**Request Body:**
```json
{
  "device_id": "FP001",
  "secret_key": "your-secret-key",
  "records": [
    {
      "uid": "EMP001",
      "timestamp": "2026-04-04 08:00:00",
      "type": "check_in"
    },
    {
      "uid": "EMP002",
      "timestamp": "2026-04-04 08:05:00",
      "type": "check_in"
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Data berhasil diproses: 2 record, 0 error",
  "processed": 2,
  "errors": 0
}
```

### Metode 2: Polling/Sync Manual

Admin dapat melakukan sinkronisasi manual dari UI:
1. Buka halaman detail perangkat
2. Klik tombol **"Sinkronisasi Absensi"**
3. Sistem akan menarik data dari perangkat

### Heartbeat Monitoring

Perangkat dapat mengirim heartbeat untuk monitoring status koneksi.

**Endpoint:** `POST /api/webhooks/fingerprint/heartbeat`

**Request Body:**
```json
{
  "device_id": "FP001"
}
```

---

## 🔧 Konfigurasi Perangkat by Vendor

### ZKTeco Devices

ZKTeco adalah vendor fingerprint paling populer. Konfigurasi:

- **Protocol**: TCP
- **Port**: 4370 (default)
- **Library**: adrobinoga/zklib (untuk PHP)

**Contoh konfigurasi IP:**
```
IP Address: 192.168.1.201
Port: 4370
Protocol: TCP
```

### Suprema Devices

- **Protocol**: HTTP/HTTPS
- **Port**: 80 atau 443
- **Auth**: API Key + Secret Key

### Generic/Custom Devices

Untuk perangkat lain, gunakan webhook push method dengan format JSON yang sesuai.

---

## 📊 Monitoring & Reporting

### Status Dashboard

Pada halaman detail perangkat, Anda dapat melihat:
- **Status Aktivasi**: Aktif/Nonaktif
- **Status Koneksi**: Terhubung/Tidak Terhubung
- **Scan Hari Ini**: Jumlah scan hari ini
- **Karyawan Terdaftar**: Jumlah karyawan yang sudah registrasi
- **Last Sync**: Waktu sinkronisasi terakhir

### Attendance Logs

Semua scan fingerprint dicatat di tabel `fingerprint_attendance_logs`:
- Data mentah dari perangkat
- Status pemrosesan (processed/pending)
- Mapping ke employee
- Timestamp scan

### Processed Attendance

Data yang sudah diproses akan masuk ke tabel `attendances`:
- Check-in time
- Check-out time
- Status (present, late, absent)
- Work duration (dalam menit)

---

## 🔐 Security & Tenant Isolation

### Tenant Isolation

Setiap tenant hanya dapat:
- Melihat dan mengelola perangkat milik tenant sendiri
- Mendaftarkan karyawan tenant sendiri
- Mengakses data absensi tenant sendiri

Middleware `EnforceTenantIsolation` memastikan tidak ada kebocoran data antar tenant.

### API Authentication

Untuk webhook endpoints:
- Gunakan `secret_key` yang dikonfigurasi di perangkat
- Validasi signature jika diperlukan
- Rate limiting diterapkan via middleware

---

## ⚙️ Advanced Configuration

### Custom Device Configuration

Field `config` (JSON) dapat digunakan untuk menyimpan konfigurasi tambahan:

```json
{
  "timeout": 30,
  "retry_attempts": 3,
  "auto_sync_interval": 300,
  "custom_headers": {
    "X-Custom-Header": "value"
  }
}
```

### Auto-Sync Schedule

Anda dapat membuat scheduled job untuk auto-sync:

```php
// In app/Console/Kernel.php or routes/console.php
Schedule::call(function () {
    $devices = FingerprintDevice::where('is_active', true)->get();
    $service = app(FingerprintDeviceService::class);
    
    foreach ($devices as $device) {
        $service->syncAttendanceLogs($device);
    }
})->everyFiveMinutes();
```

---

## 🐛 Troubleshooting

### Perangkat Tidak Terhubung

1. Pastikan IP address dan port benar
2. Cek koneksi jaringan ke perangkat
3. Verifikasi firewall tidak memblokir port
4. Test koneksi via ping/telnet

### Data Tidak Masuk

1. Cek log di `storage/logs/laravel.log`
2. Verifikasi webhook endpoint dapat diakses
3. Pastikan device_id dan secret_key cocok
4. Cek apakah perangkat aktif di sistem

### Employee UID Tidak Dikenali

1. Pastikan karyawan sudah terdaftar di sistem
2. Verifikasi fingerprint_uid sudah diisi
3. Cek mapping UID di tabel employees
4. Re-register fingerprint jika perlu

### Duplicate Attendance

Sistem menggunakan unique constraint pada:
- `device_id + employee_uid + scan_time`

Duplicate akan otomatis ditolak.

---

## 📝 Database Schema

### fingerprint_devices
- id, tenant_id, name, device_id
- ip_address, port, protocol
- vendor, model
- api_key, secret_key
- is_active, is_connected
- last_sync_at, config, notes

### fingerprint_attendance_logs
- id, tenant_id, device_id
- employee_uid, employee_id
- scan_time, scan_type
- is_processed, processed_at
- raw_data, error_message

### employees (added fields)
- fingerprint_uid
- fingerprint_registered

---

## 🔄 Future Enhancements

Fitur yang dapat ditambahkan:
- [ ] Real-time WebSocket connection ke perangkat
- [ ] Face recognition support
- [ ] Multi-finger registration
- [ ] Shift-based attendance validation
- [ ] Overtime auto-calculation
- [ ] Geofencing untuk mobile check-in
- [ ] Offline mode dengan sync later
- [ ] Device health monitoring & alerts

---

## 📞 Support

Untuk pertanyaan atau masalah teknis:
1. Cek log aplikasi di `storage/logs/`
2. Verifikasi konfigurasi perangkat
3. Test koneksi via UI
4. Hubungi tim support dengan menyertakan:
   - Vendor dan model perangkat
   - Error message dari log
   - Screenshot konfigurasi

---

**Version:** 1.0.0  
**Last Updated:** April 4, 2026
