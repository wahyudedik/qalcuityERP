# Qalcuity ERP

Qalcuity ERP adalah sistem manajemen bisnis berbasis web multi-tenant yang dibangun dengan Laravel. Dirancang untuk mendukung operasional berbagai jenis bisnis dalam satu platform terpadu, dilengkapi asisten AI berbasis Google Gemini.

> Stack: Laravel 13 · PHP 8.3+ · MySQL · Vite

---

## Quick Start (Local Development)

### Prasyarat
- PHP 8.3+
- Composer
- MySQL 8.0+
- Node.js 18+ & npm
- Redis (optional untuk development, **wajib** untuk production)

### Setup

```bash
# Clone repository
git clone https://github.com/your-org/qalcuity-erp.git
cd qalcuity-erp

# Install dependencies & setup
composer run setup

# Atau manual:
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build

# Jalankan development server
composer run dev
# Atau manual:
# Terminal 1: php artisan serve
# Terminal 2: php artisan queue:listen
# Terminal 3: php artisan pail
# Terminal 4: npm run dev
```

Akses aplikasi di `http://localhost:8000`

**Default Super Admin:**
- Email: `superadmin@qalcuity.com`
- Password: sesuai `SUPER_ADMIN_PASSWORD` di `.env`

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

#### Minimum Requirements
- VPS dengan OS Linux (Ubuntu 20.04+ atau CentOS 7+)
- 2 CPU cores
- 4GB RAM (minimum 2GB)
- 40GB SSD storage
- Domain yang sudah pointing ke IP VPS
- SSH access ke VPS

#### Recommended Requirements (untuk 50+ concurrent users)
- 4 CPU cores
- 8GB RAM
- 80GB SSD storage
- Backup storage (S3 atau external)

#### Software Requirements
- PHP 8.3+ dengan extensions: redis, curl, gd, mbstring, pdo_mysql, zip, bcmath, xml, intl, opcache
- MySQL 8.0+ atau MariaDB 10.5+
- Redis 6.0+
- Nginx 1.18+
- Node.js 18+
- Supervisor (untuk queue worker)
- Certbot (untuk SSL)

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

**⚠️ PENTING:** Aplikasi ini **WAJIB menggunakan Redis** untuk production. Redis digunakan untuk:
- Cache (dashboard, settings, AI responses)
- Session management
- Queue jobs (AI processing, email, exports)

Tanpa Redis, aplikasi akan mengalami performance issue dan beberapa fitur tidak akan berfungsi optimal.

### 2. Setup Environment di aaPanel

#### 2.1 Install PHP & Extensions
1. Buka aaPanel → **App Store** → **PHP**
2. Install **PHP 8.3** (atau versi terbaru yang didukung)
3. Setelah instalasi, klik **Settings** pada PHP 8.3:
   - Aktifkan extensions: `redis`, `curl`, `gd`, `mbstring`, `pdo`, `pdo_mysql`, `zip`, `bcmath`, `json`, `xml`, `fileinfo`, `exif`, `intl`, `opcache`
   - Set `memory_limit = 512M`
   - Set `max_execution_time = 300`
   - Set `upload_max_filesize = 100M`
   - Set `post_max_size = 100M`
   - Enable OPcache untuk performance

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

#### 2.4 Install Redis
1. **App Store** → **Database** → **Redis** (latest stable)
2. Start Redis service
3. Verifikasi: `redis-cli ping` (harus return `PONG`)

#### 2.5 Install Node.js & npm
1. **App Store** → **Development** → **Node.js** (v18 atau v20)
2. Verifikasi: `node -v && npm -v`

#### 2.6 Install PHP Redis Extension
```bash
# SSH ke VPS
ssh root@your-vps-ip

# Install phpredis extension
pecl install redis

# Enable extension di php.ini
echo "extension=redis.so" >> /www/server/php/83/etc/php.ini

# Restart PHP-FPM
systemctl restart php-fpm-83

# Verifikasi
php -m | grep redis
```

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
APP_KEY=  # akan di-generate dengan php artisan key:generate

# Super Admin credentials
SUPER_ADMIN_PASSWORD=your_strong_password_here

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qalcuity_production
DB_USERNAME=qalcuity
DB_PASSWORD=strong_password_here

# Session & Cache (WAJIB Redis untuk production)
SESSION_DRIVER=redis
SESSION_LIFETIME=720
SESSION_SECURE_COOKIE=true
CACHE_STORE=redis
QUEUE_CONNECTION=redis

# Redis Configuration
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_QUEUE_DB=2

# Security
BCRYPT_ROUNDS=12
LOG_LEVEL=error

# AI Provider
AI_DEFAULT_PROVIDER=gemini
GEMINI_API_KEY=your_gemini_api_key
GEMINI_MODEL=gemini-2.5-flash

# Mail (konfigurasi via SuperAdmin panel untuk production)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# File Storage (optional)
FILESYSTEM_DISK=local
# Untuk S3:
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

#### 3.6 Test Redis Connection
```bash
# Test Redis connection
php artisan tinker
>>> Illuminate\Support\Facades\Redis::connection()->ping();
# Harus return "PONG"
>>> exit
```

#### 3.7 Cache Configuration
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

Laravel queue digunakan untuk background jobs (AI processing, email, exports, dll). Setup di aaPanel:

#### 5.1 Buat Supervisor Config
```bash
# SSH ke VPS
ssh root@your-vps-ip

# Install supervisor jika belum ada
apt-get install supervisor  # Ubuntu/Debian
# atau
yum install supervisor      # CentOS

# Buat file supervisor config
sudo nano /etc/supervisor/conf.d/qalcuity-worker.conf
```

Isi dengan:
```ini
[program:qalcuity-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /www/wwwroot/qalcuity/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --timeout=90
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

**Catatan:** Gunakan `queue:work redis` karena kita menggunakan Redis untuk queue connection.

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
| 500 Internal Server Error | Cek `storage/logs/laravel.log` dan jalankan `php artisan config:clear` |
| Permission Denied | `chown -R www:www /www/wwwroot/qalcuity && chmod -R 755 storage bootstrap/cache` |
| Queue jobs tidak berjalan | Verifikasi supervisor: `sudo supervisorctl status` dan cek Redis connection |
| Redis connection failed | Verifikasi Redis running: `systemctl status redis` dan cek phpredis extension: `php -m \| grep redis` |
| Session tidak persist | Pastikan `SESSION_DRIVER=redis` dan Redis berjalan normal |
| Cache tidak bekerja | Jalankan `php artisan cache:clear` dan verifikasi Redis connection |
| Slow performance | 1. Enable OPcache di PHP settings<br>2. Optimize Redis memory<br>3. Index database tables<br>4. Enable Nginx gzip |
| SSL certificate error | Renew certificate di aaPanel SSL panel atau manual: `certbot renew` |
| Out of memory | Increase PHP `memory_limit` dan Redis `maxmemory`, atau upgrade VPS RAM |
| Queue worker died | Restart supervisor: `sudo supervisorctl restart qalcuity-worker:*` |

### 10. Performance Optimization

#### 10.1 Redis Memory Management
```bash
# Edit redis.conf
sudo nano /etc/redis/redis.conf

# Set max memory (sesuaikan dengan RAM VPS)
maxmemory 256mb
maxmemory-policy allkeys-lru

# Restart Redis
sudo systemctl restart redis
```

#### 10.2 PHP-FPM Tuning
Di aaPanel → PHP 8.3 → Settings → Performance:
```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

#### 10.3 MySQL Optimization
```sql
-- Enable query cache
SET GLOBAL query_cache_size = 67108864;
SET GLOBAL query_cache_type = 1;

-- Optimize tables regularly
OPTIMIZE TABLE cache, sessions, jobs, failed_jobs;
```

### 11. Monitoring & Health Check

#### 11.1 Setup Health Check Endpoint
Aplikasi sudah memiliki health check di `/health`. Monitor dengan:
```bash
# Cron job untuk monitoring
*/5 * * * * curl -f https://your-domain.com/health || echo "Site down!" | mail -s "Alert" admin@your-domain.com
```

#### 11.2 Monitor Queue
```bash
# Check queue status
php artisan queue:monitor redis --max=100

# Check failed jobs
php artisan queue:failed
```

#### 11.3 Monitor Redis
```bash
# Redis stats
redis-cli info stats
redis-cli info memory

# Monitor real-time
redis-cli monitor
```

### 12. Security Checklist

- [ ] Set `APP_ENV=production` dan `APP_DEBUG=false`
- [ ] Gunakan strong password untuk database, super admin, dan Redis
- [ ] Enable SSL/HTTPS dengan Let's Encrypt
- [ ] Set `SESSION_SECURE_COOKIE=true`
- [ ] Setup firewall rules (hanya allow port 80, 443, 22)
- [ ] Disable Redis remote access (bind ke 127.0.0.1)
- [ ] Regular backup database & files (daily)
- [ ] Update PHP, MySQL, Nginx, Redis secara berkala
- [ ] Setup monitoring & alerting
- [ ] Disable unnecessary services
- [ ] Setup rate limiting untuk API
- [ ] Enable 2FA untuk aaPanel admin account
- [ ] Set proper file permissions (755 untuk directories, 644 untuk files)
- [ ] Enable fail2ban untuk SSH protection
- [ ] Regular security audit dengan `php artisan security:check`

---

## Alternative: Manual Setup (Tanpa aaPanel)

Jika Anda lebih suka setup manual tanpa control panel:

### 1. Install Dependencies

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.3 & Extensions
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-mysql php8.3-redis \
    php8.3-curl php8.3-gd php8.3-mbstring php8.3-xml php8.3-zip \
    php8.3-bcmath php8.3-intl php8.3-opcache

# Install MySQL
sudo apt install -y mysql-server
sudo mysql_secure_installation

# Install Redis
sudo apt install -y redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Install Nginx
sudo apt install -y nginx
sudo systemctl enable nginx
sudo systemctl start nginx

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js & npm
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Supervisor
sudo apt install -y supervisor
sudo systemctl enable supervisor
sudo systemctl start supervisor
```

### 2. Setup Database

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE qalcuity_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'qalcuity'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON qalcuity_production.* TO 'qalcuity'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Deploy Application

```bash
# Clone repository
cd /var/www
sudo git clone https://github.com/your-org/qalcuity-erp.git qalcuity
cd qalcuity

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Setup environment
cp .env.example .env
nano .env  # Edit dengan konfigurasi production

# Generate key & migrate
php artisan key:generate
php artisan migrate --force

# Set permissions
sudo chown -R www-data:www-data /var/www/qalcuity
sudo chmod -R 755 storage bootstrap/cache
```

### 4. Configure Nginx

```bash
sudo nano /etc/nginx/sites-available/qalcuity
```

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    root /var/www/qalcuity/public;
    index index.php;

    # SSL Configuration (use certbot for Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Gzip
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/qalcuity /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx

# Setup SSL with Let's Encrypt
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

### 5. Setup Queue Worker

```bash
sudo nano /etc/supervisor/conf.d/qalcuity-worker.conf
```

```ini
[program:qalcuity-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/qalcuity/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/qalcuity/storage/logs/worker.log
stopwaitsecs=3600
user=www-data
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start qalcuity-worker:*
```

### 6. Setup Cron

```bash
sudo crontab -e -u www-data
```

Add:
```
* * * * * cd /var/www/qalcuity && php artisan schedule:run >> /dev/null 2>&1
```

---

## Post-Deployment

### First Login

1. Akses `https://your-domain.com`
2. Login dengan Super Admin credentials:
   - Email: `superadmin@qalcuity.com`
   - Password: sesuai `SUPER_ADMIN_PASSWORD` di `.env`

### Initial Configuration

1. **SuperAdmin Panel** (`/super-admin/settings`):
   - Configure Gemini API key
   - Setup SMTP for production emails
   - Configure push notification VAPID keys
   - Setup error alerting (Slack/Email)

2. **Create First Tenant**:
   - Go to SuperAdmin → Tenants → Add New
   - Set subscription plan
   - Configure tenant modules

3. **Tenant Settings** (login as tenant admin):
   - Company profile
   - Fiscal year
   - Chart of accounts
   - Tax settings
   - Payment gateway integration
   - E-commerce marketplace integration

---

## Maintenance Commands

```bash
# Clear all caches
php artisan optimize:clear

# Rebuild caches
php artisan optimize

# View logs in real-time
php artisan pail

# Check queue status
php artisan queue:monitor redis

# Retry failed jobs
php artisan queue:retry all

# Prune old logs (keep last 30 days)
php artisan model:prune --model="App\Models\ActivityLog"

# Database backup
mysqldump -u qalcuity -p qalcuity_production > backup_$(date +%Y%m%d).sql

# Restore database
mysql -u qalcuity -p qalcuity_production < backup_20260503.sql
```

---

## Development vs Production

| Aspect | Development | Production |
|--------|-------------|------------|
| APP_ENV | `local` | `production` |
| APP_DEBUG | `true` | `false` |
| Cache | `database` | `redis` |
| Queue | `database` | `redis` |
| Session | `database` | `redis` |
| HTTPS | Optional | **Required** |
| Queue Worker | `php artisan queue:listen` | Supervisor |
| Assets | `npm run dev` | `npm run build` |
| Logs | All levels | `error` only |

---

## Best Practices

### 1. Database
- Gunakan connection pooling untuk high traffic
- Regular OPTIMIZE TABLE untuk performance
- Setup read replica untuk reporting queries
- Index foreign keys dan frequently queried columns

### 2. Redis
- Set `maxmemory-policy` ke `allkeys-lru`
- Monitor memory usage: `redis-cli info memory`
- Gunakan Redis persistence (RDB + AOF) untuk data penting
- Separate Redis instances untuk cache vs queue (optional)

### 3. Queue
- Gunakan multiple queue workers untuk parallel processing
- Set `--max-time=3600` untuk prevent memory leaks
- Monitor failed jobs: `php artisan queue:failed`
- Setup retry strategy untuk transient failures

### 4. Security
- Never commit `.env` file
- Rotate API keys regularly
- Use environment-specific credentials
- Enable database SSL connection untuk production
- Setup rate limiting: `php artisan route:list` untuk check throttle middleware

### 5. Monitoring
- Setup application monitoring (New Relic, Datadog, atau Laravel Pulse)
- Monitor disk space: `df -h`
- Monitor Redis memory: `redis-cli info memory`
- Monitor MySQL slow queries: enable slow query log
- Setup uptime monitoring (UptimeRobot, Pingdom)

### 6. Backup Strategy
- Database: Daily full backup + hourly incremental
- Files: Daily backup ke S3 atau external storage
- Test restore procedure regularly
- Keep backups for at least 30 days
- Store backups in different geographic location

---

## Common Issues & Solutions

### Issue: "Class 'Redis' not found"
**Cause:** PHP Redis extension tidak terinstall  
**Solution:**
```bash
pecl install redis
echo "extension=redis.so" >> /etc/php/8.3/cli/php.ini
echo "extension=redis.so" >> /etc/php/8.3/fpm/php.ini
systemctl restart php8.3-fpm
```

### Issue: Queue jobs tidak diproses
**Cause:** Queue worker tidak running  
**Solution:**
```bash
sudo supervisorctl status
sudo supervisorctl restart qalcuity-worker:*
```

### Issue: "Too many connections" MySQL error
**Cause:** Connection pool exhausted  
**Solution:**
```sql
SET GLOBAL max_connections = 500;
```
Dan tambahkan di `my.cnf`:
```ini
[mysqld]
max_connections = 500
```

### Issue: Redis memory penuh
**Cause:** Cache tidak di-evict  
**Solution:**
```bash
# Temporary fix
redis-cli FLUSHDB

# Permanent fix - edit redis.conf
maxmemory 512mb
maxmemory-policy allkeys-lru
```

### Issue: Slow dashboard loading
**Cause:** Cache tidak aktif atau N+1 queries  
**Solution:**
```bash
# Clear & rebuild cache
php artisan optimize:clear
php artisan optimize

# Check query count
php artisan debugbar:clear
```

### Issue: Session logout otomatis
**Cause:** Redis session expired atau SESSION_LIFETIME terlalu pendek  
**Solution:**
```env
SESSION_LIFETIME=720  # 12 hours
SESSION_DRIVER=redis
```

### Issue: AI responses lambat
**Cause:** Network latency ke Gemini API atau rate limiting  
**Solution:**
- Enable AI response caching: `AI_RESPONSE_CACHE_ENABLED=true`
- Increase timeout: `GEMINI_TIMEOUT=120`
- Check rate limits di Google AI Studio
- Consider using queue untuk AI processing

---

## Support & Documentation

- **Documentation:** [https://docs.qalcuity.com](https://docs.qalcuity.com)
- **API Reference:** [https://api.qalcuity.com/docs](https://api.qalcuity.com/docs)
- **Community Forum:** [https://forum.qalcuity.com](https://forum.qalcuity.com)
- **Issue Tracker:** [https://github.com/your-org/qalcuity-erp/issues](https://github.com/your-org/qalcuity-erp/issues)

---

## License

Proprietary - All rights reserved

---
