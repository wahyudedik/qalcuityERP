# Troubleshooting: Halaman Notifikasi

## Masalah yang Dilaporkan

### 1. Sidebar Terpotong / Kosong
**Gejala:** Sidebar hanya menampilkan label section (HOTEL PMS, AKUN & NOTIFIKASI, PENGATURAN PERUSAHAAN, KONFIGURASI) tanpa menu items di bawahnya.

**Penyebab Potensial:**
- CSS overflow atau height constraint yang memotong konten
- Alpine.js `filteredNavItems` mengembalikan array kosong
- JavaScript error yang mencegah rendering menu items

**Solusi yang Sudah Diterapkan:**
✅ Menu "Notifikasi" dipindahkan keluar dari blok `@if ($user?->isAdmin())` sehingga semua user bisa mengaksesnya

**Langkah Debugging:**
1. Buka browser DevTools (F12)
2. Periksa Console untuk error JavaScript
3. Periksa elemen `<nav>` di sidebar dengan Inspector
4. Cek apakah `NAV_GROUPS.settings.items` memiliki data
5. Jalankan di Console:
   ```javascript
   Alpine.store('navSystem').filteredNavItems
   ```

### 2. Tombol Launcher (Waffle/9 Titik) Tidak Berfungsi
**Gejala:** Tombol waffle di kiri atas tidak membuka overlay launcher yang menampilkan grid modul.

**Penyebab Potensial:**
- Alpine.js store `navSystem` belum ter-init
- Event handler `@click` tidak terpasang
- Overlay launcher tidak ter-render karena `x-teleport` issue

**Komponen Terkait:**
- **Tombol:** `resources/views/layouts/app.blade.php` (baris ~230)
- **Overlay:** `resources/views/layouts/_nav_launcher.blade.php`
- **Store:** `resources/views/layouts/app.blade.php` (baris ~1622)

**Langkah Debugging:**
1. Buka browser DevTools Console
2. Cek apakah Alpine sudah loaded:
   ```javascript
   typeof Alpine
   ```
3. Cek store navSystem:
   ```javascript
   Alpine.store('navSystem')
   ```
4. Test toggle launcher secara manual:
   ```javascript
   Alpine.store('navSystem').toggleLauncher()
   ```
5. Periksa apakah overlay launcher ada di DOM:
   ```javascript
   document.getElementById('launcher-overlay')
   ```

## Cara Test Setelah Fix

### Test 1: Sidebar Menu
1. Login sebagai user biasa (bukan Admin)
2. Navigasi ke `/notifications`
3. Sidebar harus menampilkan:
   - Section "AKUN & NOTIFIKASI"
   - Menu "Notifikasi" (aktif/highlighted)
   - Section "Langganan" (jika applicable)

### Test 2: Launcher
1. Klik tombol waffle (9 titik) di kiri atas
2. Overlay launcher harus muncul dengan:
   - Search bar di atas
   - Grid modul (Dashboard, AI Chat, Transaksi, Inventori, Operasional, Keuangan, Pengaturan)
   - Section "Terakhir Dibuka" (jika ada history)
3. Klik salah satu modul → sidebar harus berubah sesuai modul yang dipilih
4. Klik backdrop atau tombol close → launcher harus tertutup

### Test 3: Notifikasi Interaktif
1. Di halaman notifikasi, klik pada item notifikasi
2. Detail notifikasi harus expand dengan animasi smooth
3. Jika ada URL, tombol "Lihat Detail" harus muncul
4. Klik "Tandai dibaca" → notifikasi harus ter-update tanpa expand/collapse

## File yang Dimodifikasi

1. **resources/views/notifications/index.blade.php**
   - Menambahkan Alpine.js `x-data`, `x-show`, `x-collapse` untuk expand/collapse
   - Menambahkan display data notifikasi dan link "Lihat Detail"

2. **resources/views/layouts/app.blade.php** (baris ~1404-1550)
   - Memindahkan menu "Notifikasi" keluar dari blok `@if ($user?->isAdmin())`
   - Menambahkan section "Akun & Notifikasi" yang accessible untuk semua user

## Checklist Verifikasi

- [x] `php artisan view:clear` berhasil tanpa error
- [x] `npm run build` berhasil tanpa error
- [ ] Browser Console tidak ada error JavaScript
- [ ] Alpine.js store `navSystem` ter-init dengan benar
- [ ] Tombol launcher membuka overlay
- [ ] Sidebar menampilkan menu items (tidak terpotong)
- [ ] Menu "Notifikasi" muncul untuk semua user
- [ ] Item notifikasi bisa di-expand untuk melihat detail

## Kontak Support

Jika masalah masih berlanjut setelah fix ini:
1. Screenshot browser Console (F12 → Console tab)
2. Screenshot Network tab untuk melihat failed requests
3. Screenshot elemen sidebar di Inspector
4. Informasi browser dan versi (Chrome/Firefox/Edge)
