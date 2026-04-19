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

---

## Setup VPS dengan aaPanel untuk Production

### Prasyarat
- VPS dengan OS Linux (Ubuntu 20.04+ atau CentOS 7+)
- Minimal 2GB RAM, 20GB storage
- Domain yang sudah pointing ke IP VPS
- SSH access ke VPS

### 1. Install aaPanel

```bash
# Untuk Ubuntu/Debian
wget -O install.sh http://www.aapanel.com/script/install_6.0_en.sh && sudo bash install.sh aapanel

# Untuk CentOS
wget -O install.sh http://www.aapanel.com/script/install_6.0_en.sh && sudo bash install.sh aapanel
```

Setelah instalasi selesai, akses aaPanel melalui browser:
- URL: `http://your-vps-ip:7800`
- Username & password akan ditampilkan di terminal

### 2. Setup Environment di aaPanel

#### 2.1 Install PHP & Extensions
1. Buka aaPanel → **App Store** → **PHP**
2. Install **PHP 8.3** (atau versi terbaru yang didukung)
3. Setelah instalasi, klik **Settings** pada PHP 8.3:
   - Aktifkan extensions: `curl`, `gd`, `mbstring`, `pdo`, `pdo_mysql`, `zip`, `bcmath`, `json`, `xml`, `fileinfo`, `exif`
   - Set `memory_limit = 512M`
   - Set `max_execution_time = 300`
   - Set `upload_max_filesize = 100M`
   - Set `post_max_size = 100M`

#### 2.2 Install MySQL
1. **App Store** → **Database** → **MySQL 8.0** (atau MariaDB 10.5+)
2. Catat username & password yang digenerate
3. Buka MySQL console dan buat database:
   ```sql
   CREATE DATABASE qalcuity_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'qalcuity'@'localhost' IDENTIFIED BY 'strong_password_here';
   GRANT ALL PRIVILEGES ON qalcuity_production.* TO 'qalcuity'@'localhost';
   FLUSH PRIVILEGES;
   ```

#### 2.3 Install Nginx
1. **App Store** → **Web Server** → **Nginx** (latest stable)
2. Konfigurasi akan otomatis

#### 2.4 Install Node.js & npm
1. **App Store** → **Development** → **Node.js** (v18 atau v20)
2. Verifikasi: `node -v && npm -v`

### 3. Deploy Aplikasi

#### 3.1 Clone Repository
```bash
# SSH ke VPS
ssh root@your-vps-ip

# Navigate ke web root
cd /www/wwwroot

# Clone repository
git clone https://github.com/your-org/qalcuity-erp.git qalcuity
cd qalcuity
```

#### 3.2 Setup Laravel
```bash
# Install dependencies
composer install --no-dev --optimize-autoloader

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www:www storage bootstrap/cache public
```

#### 3.3 Konfigurasi .env untuk Production
Edit `.env` dengan nilai production:
```env
APP_NAME="Qalcuity ERP"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qalcuity_production
DB_USERNAME=qalcuity
DB_PASSWORD=strong_password_here

CACHE_DRIVER=redis
QUEUE_CONNECTION=database
SESSION_DRIVER=cookie

# Gemini AI
GEMINI_API_KEY=your_gemini_api_key

# Mail (gunakan SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@your-domain.com

# AWS S3 (optional, untuk file storage)
FILESYSTEM_DISK=local
# atau
# FILESYSTEM_DISK=s3
# AWS_ACCESS_KEY_ID=your_key
# AWS_SECRET_ACCESS_KEY=your_secret
# AWS_DEFAULT_REGION=ap-southeast-1
# AWS_BUCKET=your-bucket
```

#### 3.4 Database Migration & Seeding
```bash
# Run migrations
php artisan migrate --force

# Seed database (optional, untuk demo data)
php artisan db:seed --class=SuperAdminSeeder
```

#### 3.5 Build Frontend Assets
```bash
# Install npm dependencies
npm install

# Build production assets
npm run build
```

#### 3.6 Cache Configuration
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. Konfigurasi Nginx di aaPanel

#### 4.1 Buat Website Baru
1. Buka aaPanel → **Website** → **Add Site**
2. Isi:
   - **Domain**: your-domain.com (dan www.your-domain.com)
   - **Root Path**: `/www/wwwroot/qalcuity/public`
   - **PHP Version**: 8.3
   - **Database**: qalcuity_production
3. Klik **Submit**

#### 4.2 Konfigurasi Nginx Config
1. Di aaPanel, klik **Website** → pilih domain → **Config**
2. Ganti konfigurasi dengan:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    root /www/wwwroot/qalcuity/public;
    index index.php index.html index.htm default.php default.html default.htm;

    # Redirect HTTP ke HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    root /www/wwwroot/qalcuity/public;
    index index.php index.html index.htm default.php default.html default.htm;

    # SSL Certificate (aaPanel akan auto-generate dengan Let's Encrypt)
    ssl_certificate /www/server/panel/vhost/cert/your-domain.com/fullchain.pem;
    ssl_certificate_key /www/server/panel/vhost/cert/your-domain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;

    # Laravel rewrite rules
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_param SCRIPT_NAME /index.php;
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ ~$ {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

3. Klik **Save**

#### 4.3 Setup SSL Certificate
1. Di aaPanel → **Website** → pilih domain → **SSL**
2. Klik **Let's Encrypt** → **Auto** untuk auto-renewal
3. Tunggu hingga certificate terinstall

### 5. Setup Queue Worker

Laravel queue digunakan untuk background jobs. Setup di aaPanel:

#### 5.1 Buat Supervisor Config
```bash
# SSH ke VPS
ssh root@your-vps-ip

# Buat file supervisor config
sudo nano /etc/supervisor/conf.d/qalcuity-worker.conf
```

Isi dengan:
```ini
[program:qalcuity-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /www/wwwroot/qalcuity/artisan queue:work database --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
numprocs=4
redirect_stderr=true
stdout_logfile=/www/wwwroot/qalcuity/storage/logs/worker.log
stopwaitsecs=3600
user=www
```

#### 5.2 Start Supervisor
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start qalcuity-worker:*
```

Verifikasi:
```bash
sudo supervisorctl status
```

### 6. Setup Cron Jobs

```bash
# Edit crontab
sudo crontab -e

# Tambahkan Laravel scheduler
* * * * * cd /www/wwwroot/qalcuity && php artisan schedule:run >> /dev/null 2>&1
```

### 7. Monitoring & Maintenance

#### 7.1 View Logs
```bash
# Application logs
tail -f /www/wwwroot/qalcuity/storage/logs/laravel.log

# Queue worker logs
tail -f /www/wwwroot/qalcuity/storage/logs/worker.log

# Nginx logs
tail -f /www/server/nginx/logs/error.log
```

#### 7.2 Database Backup
Di aaPanel:
1. **Database** → pilih database → **Backup**
2. Setup automatic backup (daily/weekly)

#### 7.3 File Backup
Di aaPanel:
1. **Website** → pilih domain → **Backup**
2. Setup automatic backup

#### 7.4 Monitor Performance
- Buka aaPanel Dashboard untuk melihat CPU, Memory, Disk usage
- Setup alerts untuk resource usage

### 8. Update & Deployment

#### 8.1 Pull Latest Code
```bash
cd /www/wwwroot/qalcuity
git pull origin main
```

#### 8.2 Install Dependencies & Migrate
```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 8.3 Restart Services
```bash
# Restart PHP-FPM
sudo systemctl restart php-fpm

# Restart Nginx
sudo systemctl restart nginx

# Restart Queue Workers
sudo supervisorctl restart qalcuity-worker:*
```

### 9. Troubleshooting

| Masalah | Solusi |
|---------|--------|
| 500 Internal Server Error | Cek `storage/logs/laravel.log` dan `php artisan config:clear` |
| Permission Denied | `chown -R www:www /www/wwwroot/qalcuity` |
| Queue jobs tidak berjalan | Verifikasi supervisor status: `sudo supervisorctl status` |
| Slow performance | Increase PHP memory limit, enable Redis caching, optimize database queries |
| SSL certificate error | Renew certificate di aaPanel atau gunakan `certbot renew` |

### 10. Security Checklist

- [ ] Set `APP_DEBUG=false` di production
- [ ] Gunakan strong database password
- [ ] Enable SSL/HTTPS
- [ ] Setup firewall rules (hanya allow port 80, 443, 22)
- [ ] Regular backup database & files
- [ ] Update PHP, MySQL, Nginx secara berkala
- [ ] Setup monitoring & alerting
- [ ] Disable unnecessary services
- [ ] Setup rate limiting untuk API
- [ ] Enable 2FA untuk aaPanel admin account
