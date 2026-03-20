# Qalcuity ERP — Panduan Deployment ke VPS dengan aaPanel

> Stack: Laravel 13 · PHP 8.3+ · MySQL · Node.js · Queue: database · Cache: database

---

## Daftar Isi

1. [Persiapan VPS & aaPanel](#1-persiapan-vps--aapanel)
2. [Setup PHP, MySQL & Ekstensi](#2-setup-php-mysql--ekstensi)
3. [Upload Kode ke VPS](#3-upload-kode-ke-vps)
4. [Buat Website di aaPanel](#4-buat-website-di-aapanel)
5. [Konfigurasi .env Production](#5-konfigurasi-env-production)
6. [Install Dependencies & Build](#6-install-dependencies--build)
7. [Migrasi Database & Seeder](#7-migrasi-database--seeder)
8. [Permission & Storage Link](#8-permission--storage-link)
9. [Konfigurasi Nginx](#9-konfigurasi-nginx)
10. [Setup Queue Worker (Supervisor)](#10-setup-queue-worker-supervisor)
11. [Setup Scheduler (Cron)](#11-setup-scheduler-cron)
12. [Optimasi Production](#12-optimasi-production)
13. [Setup SSL (HTTPS)](#13-setup-ssl-https)
14. [Troubleshooting](#14-troubleshooting)

---

## 1. Persiapan VPS & aaPanel

### Install aaPanel
Jika aaPanel belum terinstall di VPS:
```bash
# Ubuntu/Debian
wget -O install.sh http://www.aapanel.com/script/install-ubuntu_6.0_en.sh && bash install.sh aapanel

# CentOS
yum install -y wget && wget -O install.sh http://www.aapanel.com/script/install_6.0_en.sh && bash install.sh aapanel
```

Setelah install, akses aaPanel di `http://IP_VPS:8888` dengan kredensial yang ditampilkan.

### Install Stack di aaPanel
Masuk ke **App Store** → install:
- **Nginx** (rekomendasi: 1.24+)
- **MySQL** (rekomendasi: 8.0)
- **PHP** (pilih **8.3**)
- **phpMyAdmin** (opsional, untuk manajemen DB via UI)

---

## 2. Setup PHP, MySQL & Ekstensi

### Install Ekstensi PHP yang Dibutuhkan
Di aaPanel → **App Store** → **PHP 8.3** → **Settings** → **Install extensions**:

Install ekstensi berikut (jika belum ada):
- `fileinfo`
- `gd` atau `imagick`
- `zip`
- `bcmath`
- `pdo_mysql`
- `mbstring`
- `openssl`
- `tokenizer`
- `xml`
- `ctype`
- `json`
- `curl`

### Konfigurasi PHP
Di aaPanel → **PHP 8.3** → **Settings** → **Configuration**:
```ini
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 120
memory_limit = 256M
```
Klik **Save** lalu **Restart PHP**.

### Buat Database MySQL
Di aaPanel → **Database** → **Add Database**:
- Database Name: `qalcuity_erp`
- Username: `qalcuity_user`
- Password: (buat password kuat)
- Klik **Submit**

Catat kredensial ini untuk diisi di `.env`.

---

## 3. Upload Kode ke VPS

### Opsi A: Via Git (Rekomendasi)
```bash
# SSH ke VPS
ssh root@IP_VPS

# Masuk ke direktori web
cd /www/wwwroot

# Clone repository
git clone https://github.com/username/qalcuity-erp.git qalcuityerp.com
```

### Opsi B: Via SFTP (FileZilla / WinSCP)
1. Buka FileZilla → masukkan Host, Username, Password VPS, Port 22
2. Upload seluruh folder proyek ke `/www/wwwroot/qalcuityerp.com/`
3. Pastikan file `.env.example` ikut terupload

### Opsi C: Via aaPanel File Manager
Di aaPanel → **Files** → navigasi ke `/www/wwwroot/` → upload ZIP → extract.

---

## 4. Buat Website di aaPanel

1. Di aaPanel → **Website** → **Add site**
2. Isi:
   - **Domain**: `qalcuityerp.com` (atau subdomain/IP)
   - **Root directory**: `/www/wwwroot/qalcuityerp.com/public`
   - **PHP version**: `PHP-83`
   - **Database**: pilih database yang sudah dibuat
3. Klik **Submit**

> **Penting:** Root directory harus mengarah ke folder `public`, bukan root proyek.

---

## 5. Konfigurasi .env Production

SSH ke VPS, masuk ke direktori proyek:
```bash
cd /www/wwwroot/qalcuityerp.com
cp .env.example .env
nano .env
```

Isi `.env` untuk production:
```dotenv
APP_NAME="Qalcuity ERP"
APP_ENV=production
APP_KEY=                          # akan di-generate di langkah berikutnya
APP_DEBUG=false
APP_URL=https://qalcuityerp.com

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_TIMEZONE=Asia/Jakarta

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qalcuity_erp
DB_USERNAME=qalcuity_user
DB_PASSWORD=PASSWORD_DATABASE_ANDA

SESSION_DRIVER=database
SESSION_LIFETIME=120

QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com          # ganti sesuai provider email Anda
MAIL_PORT=587
MAIL_USERNAME=email@domain.com
MAIL_PASSWORD=app_password
MAIL_FROM_ADDRESS="noreply@qalcuityerp.com"
MAIL_FROM_NAME="${APP_NAME}"

GEMINI_API_KEY=ISI_API_KEY_GEMINI_ANDA
GEMINI_MODEL=gemini-2.5-flash
```

Simpan file (`Ctrl+X` → `Y` → `Enter`).

---

## 6. Install Dependencies & Build

```bash
cd /www/wwwroot/qalcuityerp.com

# Install Composer dependencies (production, tanpa dev packages)
composer install --optimize-autoloader --no-dev

# Generate APP_KEY
php artisan key:generate

# Install Node.js dependencies & build assets
npm install
npm run build
```

> Jika Node.js belum terinstall di VPS:
> ```bash
> curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
> apt-get install -y nodejs
> ```

---

## 7. Migrasi Database & Seeder

```bash
cd /www/wwwroot/qalcuityerp.com

# Jalankan migrasi
php artisan migrate --force

# Buat tabel untuk queue, cache, session (jika belum ada)
php artisan queue:table
php artisan cache:table
php artisan session:table
php artisan migrate --force

# Jalankan seeder untuk data awal (super admin, subscription plans, dll)
php artisan db:seed --force
```

---

## 8. Permission & Storage Link

```bash
cd /www/wwwroot/qalcuityerp.com

# Set permission folder
chmod -R 755 storage bootstrap/cache
chown -R www:www storage bootstrap/cache

# Buat symlink storage
php artisan storage:link
```

---

## 9. Konfigurasi Nginx

Di aaPanel → **Website** → klik nama domain → **Config** → tab **Nginx Config**.

Ganti isi konfigurasi dengan:
```nginx
server {
    listen 80;
    server_name qalcuityerp.com www.qalcuityerp.com;
    root /www/wwwroot/qalcuityerp.com/public;

    index index.php index.html;
    charset utf-8;

    # Gzip compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/tmp/php-cgi-83.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 120;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Klik **Save** → **Reload Nginx**.

---

## 10. Setup Queue Worker (Supervisor)

Queue worker dibutuhkan untuk memproses jobs seperti notifikasi ERP, laporan bulanan, dan chat AI.

### Install Supervisor
```bash
apt-get install -y supervisor
```

### Buat Konfigurasi Supervisor

Di aaPanel → **App Store** → cari **Supervisor** → Install. Atau buat manual:

```bash
nano /etc/supervisor/conf.d/qalcuity-worker.conf
```

Isi dengan:
```ini
[program:qalcuity-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /www/wwwroot/qalcuityerp.com/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=120
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www
numprocs=2
redirect_stderr=true
stdout_logfile=/www/wwwroot/qalcuityerp.com/storage/logs/worker.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
stopwaitsecs=3600
```

### Aktifkan Worker
```bash
supervisorctl reread
supervisorctl update
supervisorctl start qalcuity-worker:*

# Cek status
supervisorctl status
```

### Via aaPanel Supervisor UI
Jika menggunakan aaPanel Supervisor plugin:
1. aaPanel → **App Store** → **Supervisor** → **Settings** → **Add daemon**
2. Isi:
   - **Name**: `qalcuity-worker`
   - **Run user**: `www`
   - **Run dir**: `/www/wwwroot/qalcuityerp.com`
   - **Command**: `php artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=120`
   - **Processes**: `2`
3. Klik **Confirm**

---

## 11. Setup Scheduler (Cron)

Laravel Scheduler menjalankan task terjadwal berikut:
- `erp:check-notifications` — setiap hari jam 08:00 & 13:00 (cek stok menipis)
- `CheckTrialExpiry` — setiap hari jam 07:00 (cek trial/plan expired)
- `GenerateTenantReport` — tanggal 1 setiap bulan jam 01:00 (laporan bulanan)
- Cleanup chat sessions lama — setiap Minggu jam 02:00
- Cleanup failed jobs — setiap hari jam 03:00

### Tambah Cron di aaPanel
Di aaPanel → **Cron** → **Add Task**:

| Field | Value |
|-------|-------|
| Task type | Shell Script |
| Task name | Qalcuity Scheduler |
| Execute cycle | N minutes → **1 minute** |
| Script content | `cd /www/wwwroot/qalcuityerp.com && php artisan schedule:run >> /dev/null 2>&1` |

Klik **Add Task**.

### Atau via SSH (crontab manual)
```bash
crontab -e -u www
```
Tambahkan baris:
```
* * * * * cd /www/wwwroot/qalcuityerp.com && php artisan schedule:run >> /dev/null 2>&1
```

---

## 12. Optimasi Production

Jalankan perintah ini setelah deploy (dan setiap kali ada update kode):

```bash
cd /www/wwwroot/qalcuityerp.com

# Cache konfigurasi, route, dan view
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize autoloader
composer dump-autoload --optimize
```

### Script Deploy Otomatis
Buat file `deploy.sh` di root proyek untuk mempermudah update:

```bash
#!/bin/bash
set -e

cd /www/wwwroot/qalcuityerp.com

echo "==> Pull latest code..."
git pull origin main

echo "==> Install dependencies..."
composer install --optimize-autoloader --no-dev

echo "==> Build assets..."
npm ci
npm run build

echo "==> Run migrations..."
php artisan migrate --force

echo "==> Clear & rebuild cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "==> Restart queue workers..."
php artisan queue:restart

echo "==> Fix permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www:www storage bootstrap/cache

echo "==> Deploy selesai!"
```

```bash
chmod +x deploy.sh
./deploy.sh
```

---

## 13. Setup SSL (HTTPS)

Di aaPanel → **Website** → klik nama domain → **SSL**:
1. Pilih tab **Let's Encrypt**
2. Centang domain `qalcuityerp.com` dan `www.qalcuityerp.com`
3. Klik **Apply**
4. Aktifkan **Force HTTPS** (toggle)

Setelah SSL aktif, pastikan `APP_URL` di `.env` sudah menggunakan `https://`.

---

## 14. Troubleshooting

### Error 500 setelah deploy
```bash
# Cek log Laravel
tail -f /www/wwwroot/qalcuityerp.com/storage/logs/laravel.log

# Pastikan APP_KEY sudah di-generate
php artisan key:generate

# Clear semua cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Queue tidak berjalan
```bash
# Cek status supervisor
supervisorctl status

# Restart worker
supervisorctl restart qalcuity-worker:*

# Cek log worker
tail -f /www/wwwroot/qalcuityerp.com/storage/logs/worker.log

# Cek failed jobs
php artisan queue:failed
```

### Permission denied
```bash
chmod -R 755 /www/wwwroot/qalcuityerp.com/storage
chmod -R 755 /www/wwwroot/qalcuityerp.com/bootstrap/cache
chown -R www:www /www/wwwroot/qalcuityerp.com
```

### Composer memory limit
```bash
COMPOSER_MEMORY_LIMIT=-1 composer install --optimize-autoloader --no-dev
```

### Nginx 404 untuk semua route
Pastikan konfigurasi Nginx sudah ada `try_files $uri $uri/ /index.php?$query_string;` dan root mengarah ke folder `public`.

### Cek apakah scheduler berjalan
```bash
php artisan schedule:list
php artisan schedule:run --verbose
```

---

## Checklist Final Sebelum Go Live

- [ ] `APP_ENV=production` dan `APP_DEBUG=false`
- [ ] `APP_URL` menggunakan `https://`
- [ ] `APP_KEY` sudah di-generate
- [ ] Database terkoneksi dan migrasi sudah dijalankan
- [ ] `php artisan storage:link` sudah dijalankan
- [ ] Supervisor queue worker berjalan (`supervisorctl status`)
- [ ] Cron scheduler sudah ditambahkan di aaPanel
- [ ] SSL aktif dan Force HTTPS diaktifkan
- [ ] `php artisan config:cache` sudah dijalankan
- [ ] `GEMINI_API_KEY` sudah diisi dengan API key production
- [ ] Permission `storage/` dan `bootstrap/cache/` sudah `755` dengan owner `www`
