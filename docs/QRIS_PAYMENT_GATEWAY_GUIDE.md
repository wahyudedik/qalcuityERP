# QRIS Payment Gateway Integration Guide

## 📋 Overview

Sistem POS ERP mendukung **QRIS payment** melalui multiple payment gateway providers. Setiap tenant dapat **mengkonfigurasi sendiri** payment gateway mereka sesuai kebutuhan.

### Supported Providers:
✅ **Midtrans** - Most popular, reliable  
✅ **Xendit** - Developer-friendly  
🔜 **Duitku** - Coming soon  
🔜 **Tripay** - Coming soon  

---

## 🚀 Quick Start untuk Tenant

### Step 1: Daftar ke Payment Gateway Provider

#### **Option A: Midtrans** (Recommended)

1. **Daftar Account**:
   - Kunjungi: https://midtrans.com
   - Klik "Sign Up"
   - Isi form registrasi dengan data bisnis Anda

2. **Verifikasi Account**:
   - Verifikasi email
   - Lengkapi KYC (Know Your Customer)
   - Upload dokumen bisnis (NPWP, SIUP, dll)

3. **Dapatkan Credentials**:
   - Login ke Midtrans Dashboard
   - Go to **Settings > Access Keys**
   - Copy credentials berikut:
     - **Server Key** (untuk production)
     - **Client Key** (untuk frontend)
     - **Sandbox Server Key** (untuk testing)

4. **Setup Webhook**:
   - Go to **Settings > Configuration**
   - Set Notification URL: `https://yourdomain.com/api/payment/webhook/midtrans`
   - Enable transaction notifications

---

#### **Option B: Xendit**

1. **Daftar Account**:
   - Kunjungi: https://xendit.co
   - Klik "Get Started"
   - Register dengan email bisnis

2. **Verifikasi Account**:
   - Email verification
   - Complete business verification
   - Submit required documents

3. **Dapatkan API Key**:
   - Login ke Xendit Dashboard
   - Go to **Developers > API Keys**
   - Copy **Secret Key** (keep this secure!)
   - Copy **Public Key** (for frontend)

4. **Setup Webhook**:
   - Go to **Developers > Webhooks**
   - Add webhook URL: `https://yourdomain.com/api/payment/webhook/xendit`
   - Select events: `invoice.paid`, `invoice.expired`

---

### Step 2: Konfigurasi di Sistem ERP

Setelah mendapatkan credentials dari provider, konfigurasi di sistem:

#### **Via API:**

```javascript
// Save Midtrans configuration
const response = await fetch('/api/payment/gateways', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer YOUR_API_TOKEN',
    },
    body: JSON.stringify({
        provider: 'midtrans',
        environment: 'sandbox', // or 'production'
        credentials: {
            server_key: 'SB-Mid-server-xxxxxxxxxxxxx',
            client_key: 'SB-Mid-client-xxxxxxxxxxxxx'
        },
        is_active: true,
        is_default: true,
        webhook_secret: 'your-webhook-secret' // optional
    }),
});

const result = await response.json();
console.log(result.message); // "Payment gateway settings saved"
```

```javascript
// Save Xendit configuration
await fetch('/api/payment/gateways', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer YOUR_API_TOKEN',
    },
    body: JSON.stringify({
        provider: 'xendit',
        environment: 'sandbox',
        credentials: {
            api_key: 'xnd_development_xxxxxxxxxxxxx',
            public_key: 'xnd_public_development_xxxxxxxxxxxxx'
        },
        is_active: true,
        is_default: false
    }),
});
```

#### **Test Credentials:**

```javascript
// Verify credentials are valid
const testResult = await fetch('/api/payment/gateways/test', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer YOUR_API_TOKEN',
    },
    body: JSON.stringify({
        provider: 'midtrans' // or 'xendit'
    }),
});

const result = await testResult.json();
if (result.success) {
    console.log('Credentials verified successfully!');
} else {
    console.error('Verification failed:', result.error);
}
```

---

### Step 3: Generate QRIS Payment

Setelah gateway terkonfigurasi, Anda bisa mulai menerima pembayaran QRIS:

```javascript
// Generate QRIS for an order
const orderResponse = await fetch(`/api/payment/qris/${orderId}`, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer YOUR_API_TOKEN',
    },
    body: JSON.stringify({
        provider: 'midtrans' // optional, uses default if not specified
    }),
});

const paymentData = await orderResponse.json();

if (paymentData.success) {
    // Display QR code to customer
    document.getElementById('qr-code').src = paymentData.qr_image_url;
    
    // Show expiry countdown
    const expiryTime = paymentData.expiry_time; // Unix timestamp
    startCountdown(expiryTime);
    
    // Start polling for payment status
    startPolling(paymentData.transaction_number);
}
```

---

### Step 4: Monitor Payment Status

#### **Auto-polling (Frontend):**

```javascript
function startPolling(transactionNumber) {
    const pollInterval = setInterval(async () => {
        const response = await fetch(
            `/api/payment/status?transaction_number=${transactionNumber}`
        );
        
        const status = await response.json();
        
        if (status.status === 'success') {
            clearInterval(pollInterval);
            handlePaymentSuccess(status);
        } else if (['failed', 'expired', 'cancelled'].includes(status.status)) {
            clearInterval(pollInterval);
            handlePaymentFailure(status);
        }
    }, 5000); // Check every 5 seconds
    
    // Stop polling after 15 minutes (QRIS expiry)
    setTimeout(() => clearInterval(pollInterval), 15 * 60 * 1000);
}
```

#### **Webhook (Backend - Automatic):**

Sistem otomatis menerima webhook dari payment gateway dan update status pembayaran. Tidak perlu setup tambahan!

---

## 🔐 Security Best Practices

### 1. **Credential Storage**
- ✅ Credentials dienkripsi otomatis di database
- ✅ Jangan share credentials di code repository
- ✅ Gunakan environment variables untuk sensitive data
- ✅ Rotate keys secara berkala

### 2. **Webhook Verification**
Sistem otomatis verify webhook signature. Pastikan:
- Webhook secret dikonfigurasi dengan benar
- Endpoint webhook accessible dari internet
- SSL/HTTPS enabled

### 3. **Environment Separation**
- Gunakan **sandbox** untuk development/testing
- Switch ke **production** hanya setelah testing selesai
- Test semua flow sebelum go-live

---

## 💰 Pricing & Fees

### **Midtrans:**
- **QRIS Fee**: ~0.7% per transaction
- **Minimum**: Rp 2,500 per transaction
- **Settlement**: T+1 (next day)
- **No monthly fee**

### **Xendit:**
- **QRIS Fee**: ~0.7% per transaction
- **Minimum**: Rp 2,000 per transaction
- **Settlement**: T+1 atau T+2
- **No monthly fee**

*Fees dapat berubah, cek website provider untuk pricing terbaru.*

---

## 🎯 Payment Flow Diagram

```
┌──────────┐      ┌──────────┐      ┌──────────────┐
│ Customer │      │   POS    │      │ Payment      │
│          │      │ System   │      │ Gateway      │
└────┬─────┘      └────┬─────┘      └──────┬───────┘
     │                  │                   │
     │ 1. Place Order   │                   │
     ├─────────────────>│                   │
     │                  │                   │
     │ 2. Select QRIS   │                   │
     ├─────────────────>│                   │
     │                  │                   │
     │                  │ 3. Generate QR    │
     │                  ├──────────────────>│
     │                  │                   │
     │                  │ 4. Return QR Code │
     │                  │<──────────────────┤
     │                  │                   │
     │ 5. Display QR    │                   │
     │<─────────────────┤                   │
     │                  │                   │
     │ 6. Scan & Pay    │                   │
     ├──────────────────────────────────────>│
     │                  │                   │
     │                  │ 7. Webhook        │
     │                  │<──────────────────┤
     │                  │                   │
     │ 8. Payment OK    │                   │
     │<─────────────────┤                   │
     │                  │                   │
     │ 9. Print Receipt │                   │
     │<─────────────────┤                   │
     └                  └                   └
```

---

## 📊 API Endpoints Reference

### **Payment Gateway Configuration**

#### Get All Gateways
```http
GET /api/payment/gateways
Authorization: Bearer {token}
```

#### Save/Update Gateway
```http
POST /api/payment/gateways
Authorization: Bearer {token}
Content-Type: application/json

{
    "provider": "midtrans",
    "environment": "sandbox",
    "credentials": {
        "server_key": "SB-Mid-server-xxx",
        "client_key": "SB-Mid-client-xxx"
    },
    "is_active": true,
    "is_default": true,
    "webhook_secret": "optional-secret"
}
```

#### Test Gateway Credentials
```http
POST /api/payment/gateways/test
Authorization: Bearer {token}
Content-Type: application/json

{
    "provider": "midtrans"
}
```

#### Toggle Gateway Active/Inactive
```http
POST /api/payment/gateways/{gateway_id}/toggle
Authorization: Bearer {token}
```

#### Delete Gateway
```http
DELETE /api/payment/gateways/{gateway_id}
Authorization: Bearer {token}
```

---

### **Payment Transactions**

#### Generate QRIS Payment
```http
POST /api/payment/qris/{order_id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "provider": "midtrans" // optional
}
```

**Response:**
```json
{
    "success": true,
    "transaction_number": "PAY-20260404-001",
    "qr_string": "00020101021126...",
    "qr_image_url": "https://api.midtrans.com/qr/xxx.png",
    "expiry_time": 1712234567,
    "amount": 150000
}
```

#### Check Payment Status
```http
GET /api/payment/status?transaction_number=PAY-20260404-001
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "status": "success",
    "paid_at": "2026-04-04T14:35:00.000000Z",
    "transaction_number": "PAY-20260404-001"
}
```

#### Get Transaction Details
```http
GET /api/payment/transaction/{transaction_number}
Authorization: Bearer {token}
```

#### Get Payment History
```http
GET /api/payment/history?status=success&limit=50
Authorization: Bearer {token}
```

---

### **Webhook Endpoints** (No Auth Required)

#### Midtrans Webhook
```http
POST /api/payment/webhook/midtrans
Content-Type: application/json

{
    "order_id": "PAY-20260404-001",
    "transaction_status": "settlement",
    "fraud_status": "accept",
    "gross_amount": "150000.00",
    ...
}
```

#### Xendit Webhook
```http
POST /api/payment/webhook/xendit
Content-Type: application/json

{
    "external_id": "PAY-20260404-001",
    "status": "COMPLETED",
    "amount": 150000,
    ...
}
```

---

## 🔧 Troubleshooting

### **Problem: QR Code tidak muncul**

**Solution:**
1. Check gateway configuration aktif
2. Verify credentials valid
3. Check API response error message
4. Ensure order has grand_total > 0

```javascript
// Debug: Check gateway status
const gateways = await fetch('/api/payment/gateways');
const data = await gateways.json();
console.log('Active gateways:', data.data.filter(g => g.is_active));
```

---

### **Problem: Payment status tidak update**

**Solution:**
1. Check webhook endpoint accessible
2. Verify webhook secret configured
3. Check payment_callbacks table untuk logs
4. Manual check via status endpoint

```sql
-- Check webhook logs
SELECT * FROM payment_callbacks 
WHERE tenant_id = YOUR_TENANT_ID 
ORDER BY created_at DESC 
LIMIT 10;
```

---

### **Problem: Credentials verification failed**

**Solution:**
1. Double-check credentials (no extra spaces)
2. Ensure correct environment (sandbox vs production)
3. Check account status di provider dashboard
4. Try regenerating API keys

---

### **Problem: Webhook tidak diterima**

**Solution:**
1. Ensure server publicly accessible (not localhost)
2. Check firewall allows incoming POST requests
3. Verify webhook URL correct di provider dashboard
4. Check SSL certificate valid (HTTPS required)

```bash
# Test webhook endpoint
curl -X POST https://yourdomain.com/api/payment/webhook/midtrans \
  -H "Content-Type: application/json" \
  -d '{"test": true}'
```

---

## 📝 Sample Implementation

### **Complete Checkout Flow with QRIS:**

```javascript
class QrisCheckout {
    constructor(orderId) {
        this.orderId = orderId;
        this.pollInterval = null;
    }
    
    async initiatePayment() {
        try {
            // 1. Generate QRIS
            const response = await fetch(`/api/payment/qris/${this.orderId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.getToken()}`,
                },
            });
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error);
            }
            
            // 2. Display QR Code
            this.displayQrCode(data);
            
            // 3. Start countdown
            this.startCountdown(data.expiry_time);
            
            // 4. Start polling
            this.startPolling(data.transaction_number);
            
            return data;
            
        } catch (error) {
            console.error('Payment initiation failed:', error);
            this.showError(error.message);
        }
    }
    
    displayQrCode(data) {
        const qrContainer = document.getElementById('qr-container');
        qrContainer.innerHTML = `
            <div class="text-center">
                <img src="${data.qr_image_url}" alt="QRIS Code" class="w-64 h-64"/>
                <p class="mt-4 text-lg font-bold">
                    Rp ${new Intl.NumberFormat('id-ID').format(data.amount)}
                </p>
                <p class="text-sm text-gray-600 mt-2">
                    Scan dengan e-wallet Anda
                </p>
                <div id="countdown" class="mt-2 text-red-600"></div>
            </div>
        `;
    }
    
    startCountdown(expiryTimestamp) {
        const countdownEl = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            const now = Math.floor(Date.now() / 1000);
            const remaining = expiryTimestamp - now;
            
            if (remaining <= 0) {
                clearInterval(timer);
                countdownEl.textContent = 'EXPIRED';
                this.stopPolling();
                this.showExpiredMessage();
            } else {
                const minutes = Math.floor(remaining / 60);
                const seconds = remaining % 60;
                countdownEl.textContent = 
                    `Expires in: ${minutes}:${seconds.toString().padStart(2, '0')}`;
            }
        }, 1000);
    }
    
    startPolling(transactionNumber) {
        this.pollInterval = setInterval(async () => {
            try {
                const response = await fetch(
                    `/api/payment/status?transaction_number=${transactionNumber}`,
                    {
                        headers: {
                            'Authorization': `Bearer ${this.getToken()}`,
                        },
                    }
                );
                
                const status = await response.json();
                
                if (status.status === 'success') {
                    this.stopPolling();
                    this.handlePaymentSuccess(status);
                } else if (['failed', 'expired', 'cancelled'].includes(status.status)) {
                    this.stopPolling();
                    this.handlePaymentFailure(status);
                }
                
            } catch (error) {
                console.error('Status check failed:', error);
            }
        }, 5000); // Check every 5 seconds
    }
    
    stopPolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
    }
    
    handlePaymentSuccess(status) {
        alert('Payment successful! Printing receipt...');
        this.printReceipt();
        this.redirectToThankYouPage();
    }
    
    handlePaymentFailure(status) {
        alert(`Payment ${status.status}. Please try again.`);
        this.resetCheckout();
    }
    
    getToken() {
        return localStorage.getItem('api_token');
    }
}

// Usage:
const checkout = new QrisCheckout(orderId);
checkout.initiatePayment();
```

---

## ✅ Pre-Launch Checklist

Sebelum go-live dengan QRIS payment:

- [ ] Daftar account di payment gateway provider
- [ ] Complete KYC verification
- [ ] Dapatkan production credentials
- [ ] Konfigurasi gateway di sistem (production mode)
- [ ] Test credentials verification
- [ ] Test complete payment flow (sandbox)
- [ ] Verify webhook endpoint working
- [ ] Test dengan berbagai e-wallet (GoPay, OVO, Dana, LinkAja)
- [ ] Setup monitoring untuk failed transactions
- [ ] Train staff cara handle payment issues
- [ ] Prepare customer support untuk payment questions
- [ ] Document refund/cancellation process

---

## 📞 Support

### **Midtrans Support:**
- Email: support@midtrans.com
- Phone: +62-21-2279-7070
- Docs: https://docs.midtrans.com

### **Xendit Support:**
- Email: help@xendit.co
- Chat: Available in dashboard
- Docs: https://developers.xendit.co

### **ERP System Support:**
- Check logs: `storage/logs/laravel.log`
- Payment callbacks: `payment_callbacks` table
- Contact system administrator untuk technical issues

---

## 🎉 Ready to Accept QRIS Payments!

Dengan setup ini, tenant Anda sekarang bisa:
- ✅ Menerima pembayaran QRIS dari semua e-wallet
- ✅ Auto-update payment status via webhook
- ✅ Track semua transaksi payment
- ✅ Multi-gateway support (Midtrans, Xendit, dll)
- ✅ Sandbox testing sebelum production
- ✅ Secure credential management

Selamat berjualan! 💰
