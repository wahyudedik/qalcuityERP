# Payment System Deployment Guide

## 📋 Overview

Panduan lengkap untuk deploy Payment System (QRIS, Cash, Card, Bank Transfer) ke production environment.

---

## ✅ Pre-Deployment Checklist

### 1. Database Migrations

```bash
# Run all pending migrations
php artisan migrate

# Verify tables created
php artisan tinker
>>> Schema::hasTable('tenant_payment_gateways')
>>> Schema::hasTable('payment_transactions')
>>> Schema::hasTable('payment_callbacks')
>>> Schema::hasTable('print_jobs')
>>> Schema::hasTable('printer_settings')
```

**Expected Tables:**
- ✅ `tenant_payment_gateways` - Payment gateway configs
- ✅ `payment_transactions` - Transaction records
- ✅ `payment_callbacks` - Webhook logs
- ✅ `print_jobs` - Print queue
- ✅ `printer_settings` - Printer configs

---

### 2. Environment Configuration

Tambahkan ke file `.env`:

```env
# ==========================================
# PAYMENT GATEWAY CONFIGURATION
# ==========================================

# Midtrans (Optional - tenants can configure their own)
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false

# Xendit (Optional - tenants can configure their own)
XENDIT_API_KEY=
XENDIT_PUBLIC_KEY=
XENDIT_IS_PRODUCTION=false

# ==========================================
# PRINTER CONFIGURATION
# ==========================================

POS_PRINTER_TYPE=usb
POS_PRINTER_DESTINATION=POS-58
POS_PAPER_WIDTH=80
POS_PRINTER_AUTO_CONNECT=false

RECEIPT_COMPANY_NAME="Your Company Name"
RECEIPT_ADDRESS="Company Address"
RECEIPT_PHONE=021-12345678
RECEIPT_FOOTER_TEXT="Thank you for your purchase!"
RECEIPT_TAX_RATE=10
RECEIPT_SERVICE_CHARGE_RATE=5

KITCHEN_PRINTER_ENABLED=false
KITCHEN_PRINTER_TYPE=network
KITCHEN_PRINTER_DESTINATION=192.168.1.101

BARCODE_PRINTER_ENABLED=false
BARCODE_PRINTER_TYPE=usb
BARCODE_PRINTER_DESTINATION=LABEL-PRINTER

PRINT_QUEUE_ENABLED=true
PRINT_QUEUE_DRIVER=database
PRINT_QUEUE_RETRY=3
PRINT_QUEUE_RETRY_DELAY=5

LOG_PRINT_JOBS=true
PRINT_LOG_LEVEL=info

# ==========================================
# BRAND CUSTOMIZATION
# ==========================================

BRAND_COLOR_PRIMARY=#3B82F6
BRAND_COLOR_SECONDARY=#8B5CF6
BRAND_GRADIENT_PAYMENT=from-purple-600 to-blue-600
BRAND_LOGO_URL=/logo.png
PAYMENT_UI_TITLE="Select Payment Method"

# ==========================================
# QUEUE WORKER (Required for print jobs)
# ==========================================

QUEUE_CONNECTION=database
```

---

### 3. Queue Worker Setup

Payment system menggunakan Laravel Queue untuk:
- Print job processing
- Webhook handling
- Background tasks

#### **Option A: Local Development**

```bash
# Start queue worker
php artisan queue:work --queue=default --tries=3

# Keep it running with supervisor (recommended for production)
```

#### **Option B: Production with Supervisor**

Install supervisor:
```bash
# Ubuntu/Debian
sudo apt-get install supervisor

# CentOS/RHEL
sudo yum install supervisor
```

Create supervisor config `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/qalcuityERP/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/qalcuityERP/storage/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

Monitor workers:
```bash
sudo supervisorctl status
```

---

### 4. Webhook Configuration

Payment gateways perlu mengirim webhook ke server Anda.

#### **Midtrans Webhook Setup:**

1. Login ke Midtrans Dashboard
2. Go to **Settings > Configuration**
3. Set **Notification URL**:
   ```
   https://yourdomain.com/api/payment/webhook/midtrans
   ```
4. Enable notifications for:
   - ✅ Transaction status updates
   - ✅ Settlement notifications
   - ✅ Expiry notifications

#### **Xendit Webhook Setup:**

1. Login ke Xendit Dashboard
2. Go to **Developers > Webhooks**
3. Add webhook URL:
   ```
   https://yourdomain.com/api/payment/webhook/xendit
   ```
4. Select events:
   - ✅ `invoice.paid`
   - ✅ `invoice.expired`
   - ✅ `invoice.failed`

#### **Webhook Security:**

Set webhook secret di tenant settings untuk signature verification:

```javascript
// In tenant payment gateway settings
{
  "webhook_secret": "your-random-secret-key-here"
}
```

---

### 5. SSL/HTTPS Setup

Webhooks memerlukan HTTPS. Setup SSL certificate:

#### **Option A: Let's Encrypt (Free)**

```bash
# Install Certbot
sudo apt-get install certbot python3-certbot-nginx

# Get certificate
sudo certbot --nginx -d yourdomain.com

# Auto-renewal
sudo certbot renew --dry-run
```

#### **Option B: Commercial SSL**

Purchase SSL dari provider (Comodo, DigiCert, dll) dan install di web server.

---

### 6. Firewall Configuration

Ensure ports are open:

```bash
# HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Queue worker (if using Redis)
sudo ufw allow 6379/tcp

# Printer network (if using network printers)
sudo ufw allow 9100/tcp
```

---

## 🚀 Deployment Steps

### Step 1: Pull Latest Code

```bash
cd /path/to/qalcuityERP
git pull origin main
```

### Step 2: Install Dependencies

```bash
# PHP dependencies
composer install --optimize-autoloader --no-dev

# JavaScript dependencies
npm install
npm run build
```

### Step 3: Run Migrations

```bash
php artisan migrate --force
```

### Step 4: Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 5: Restart Services

```bash
# Restart queue workers
sudo supervisorctl restart laravel-worker:*

# Restart web server
sudo systemctl restart nginx
# or
sudo systemctl restart apache2
```

### Step 6: Verify Deployment

```bash
# Check routes
php artisan route:list | grep payment

# Test webhook endpoint
curl -X POST https://yourdomain.com/api/payment/webhook/midtrans \
  -H "Content-Type: application/json" \
  -d '{"test": true}'

# Check queue workers
sudo supervisorctl status
```

---

## 🧪 Testing Checklist

### 1. Payment Gateway Configuration

```bash
# Test Midtrans credentials
curl -X POST https://yourdomain.com/api/payment/gateways/test \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"provider": "midtrans"}'

# Expected: {"success": true}
```

### 2. Generate QRIS Payment

```bash
curl -X POST https://yourdomain.com/api/payment/qris/{order_id} \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "transaction_number": "PAY-20260404-001",
  "qr_string": "000201010211...",
  "qr_image_url": "https://...",
  "expiry_time": 1712234567,
  "amount": 150000
}
```

### 3. Test Printer Connection

```bash
curl -X POST https://yourdomain.com/api/pos/print/test \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "printer_type": "usb",
    "printer_destination": "POS-58"
  }'
```

### 4. End-to-End Payment Flow

1. ✅ Create order via POS
2. ✅ Select payment method (QRIS)
3. ✅ Generate QR code
4. ✅ Display QR to customer
5. ✅ Customer scans & pays
6. ✅ Webhook received
7. ✅ Order status updated
8. ✅ Receipt printed
9. ✅ Payment recorded in history

---

## 📊 Monitoring Setup

### 1. Log Monitoring

Check payment-related logs:

```bash
# Real-time log monitoring
tail -f storage/logs/laravel.log | grep -i payment

# Webhook logs
tail -f storage/logs/laravel.log | grep -i webhook

# Print job logs
tail -f storage/logs/laravel.log | grep -i print
```

### 2. Database Monitoring

```sql
-- Check pending payments
SELECT COUNT(*) FROM payment_transactions 
WHERE status = 'pending' 
AND created_at < NOW() - INTERVAL 15 MINUTE;

-- Check failed webhooks
SELECT COUNT(*) FROM payment_callbacks 
WHERE processed = 0 
AND created_at > NOW() - INTERVAL 1 HOUR;

-- Check failed print jobs
SELECT COUNT(*) FROM print_jobs 
WHERE status = 'failed' 
AND retry_count >= 3;

-- Payment success rate (last 24 hours)
SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful,
    ROUND(SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as success_rate
FROM payment_transactions
WHERE created_at > NOW() - INTERVAL 24 HOUR;
```

### 3. Queue Monitoring

```bash
# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Flush failed jobs
php artisan queue:flush
```

### 4. Alerting Setup

Setup alerts for:
- ❌ Payment success rate < 90%
- ❌ Failed webhooks > 10/hour
- ❌ Print queue backlog > 50 jobs
- ❌ Queue worker down

Example with Laravel Horizon (if installed):
```bash
composer require laravel/horizon
php artisan horizon:install
```

---

## 🔐 Security Checklist

- [ ] HTTPS enabled on all endpoints
- [ ] Webhook signature verification enabled
- [ ] API tokens rotated regularly
- [ ] Database credentials encrypted
- [ ] Payment gateway credentials encrypted at rest
- [ ] Rate limiting on payment endpoints
- [ ] CORS configured correctly
- [ ] Input validation on all endpoints
- [ ] SQL injection protection (using Eloquent)
- [ ] XSS protection (Blade auto-escaping)

---

## 📈 Performance Optimization

### 1. Database Indexes

Verify indexes exist:

```sql
SHOW INDEX FROM payment_transactions;
SHOW INDEX FROM payment_callbacks;
SHOW INDEX FROM print_jobs;
```

Add missing indexes if needed:

```sql
CREATE INDEX idx_payment_status ON payment_transactions(tenant_id, status);
CREATE INDEX idx_payment_created ON payment_transactions(tenant_id, created_at);
CREATE INDEX idx_callback_processed ON payment_callbacks(tenant_id, processed);
```

### 2. Caching

Cache gateway configurations:

```php
// In TenantPaymentGateway model
public static function booted()
{
    static::saved(fn($gateway) => cache()->forget("gateway.{$gateway->tenant_id}.{$gateway->provider}"));
}

public static function getCached(int $tenantId, string $provider): ?self
{
    return cache()->remember("gateway.{$tenantId}.{$provider}", 3600, function () use ($tenantId, $provider) {
        return static::where('tenant_id', $tenantId)
            ->where('provider', $provider)
            ->where('is_active', true)
            ->first();
    });
}
```

### 3. Queue Optimization

```env
QUEUE_CONNECTION=redis  # Faster than database
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## 🆘 Troubleshooting

### Problem: Webhooks not received

**Solution:**
1. Check webhook URL accessible from internet
2. Verify SSL certificate valid
3. Check firewall allows incoming POST requests
4. Test with webhook testing tool (webhook.site)
5. Check payment gateway dashboard for delivery status

### Problem: QR code not generating

**Solution:**
```bash
# Check gateway configuration
php artisan tinker
>>> App\Models\TenantPaymentGateway::where('tenant_id', 1)->get()

# Test API manually
curl -X POST https://api.sandbox.midtrans.com/v2/charge \
  -H "Authorization: Basic BASE64_ENCODED_SERVER_KEY" \
  -H "Content-Type: application/json" \
  -d '{...payload...}'
```

### Problem: Print jobs stuck in queue

**Solution:**
```bash
# Check queue worker status
sudo supervisorctl status

# Restart workers
sudo supervisorctl restart laravel-worker:*

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### Problem: Payment status not updating

**Solution:**
1. Check `payment_callbacks` table for webhook logs
2. Verify webhook signature matches
3. Check transaction number format
4. Manual status check via API

---

## 📝 Post-Deployment Tasks

1. **Monitor first 24 hours closely**
   - Check error logs every hour
   - Monitor payment success rate
   - Verify webhooks being received

2. **Train staff**
   - How to configure payment gateways
   - How to handle payment issues
   - How to reprint receipts

3. **Document custom configurations**
   - Printer IP addresses
   - Webhook URLs
   - Custom brand settings

4. **Setup backup**
   ```bash
   # Backup database daily
   0 2 * * * mysqldump -u user -p database > /backup/db_$(date +\%Y\%m\%d).sql
   
   # Backup uploaded files
   0 3 * * * rsync -av /path/to/storage/app /backup/storage/
   ```

5. **Plan for scale**
   - Monitor database growth
   - Plan for increased queue workers
   - Consider load balancing if needed

---

## 📞 Support Contacts

- **Midtrans Support**: support@midtrans.com | +62-21-2279-7070
- **Xendit Support**: help@xendit.co
- **System Admin**: [Your IT Team Contact]
- **Emergency**: [24/7 Support Number]

---

## ✅ Deployment Sign-Off

- [ ] All migrations run successfully
- [ ] Queue workers running
- [ ] Webhooks configured & tested
- [ ] SSL certificate installed
- [ ] Payment gateways configured
- [ ] Printers connected & tested
- [ ] Staff trained
- [ ] Monitoring setup
- [ ] Backup strategy in place
- [ ] Documentation updated

**Deployed by**: _________________  
**Date**: _________________  
**Approved by**: _________________

---

## 🎉 You're Ready to Go Live!

Payment system is now ready for production use. Monitor closely during the first week and gather user feedback for continuous improvement.
