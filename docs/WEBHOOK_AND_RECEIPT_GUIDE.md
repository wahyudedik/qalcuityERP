# Webhook Handlers & Receipt Templates Guide

## 📋 Overview

Panduan lengkap untuk webhook handlers dengan signature verification dan receipt templates yang dioptimalkan untuk thermal printer.

---

## 🔔 Webhook Handlers

### Architecture

```
Payment Gateway (Midtrans/Xendit)
         ↓
   Webhook POST
         ↓
PaymentController::webhook()
         ↓
WebhookHandlerService
         ↓
├─ Signature Verification
├─ Payload Validation
├─ Transaction Update
├─ Order Completion
├─ Stock Deduction
└─ Callback Logging
```

### Features

✅ **Signature Verification** - HMAC SHA256 / SHA512  
✅ **Idempotency** - Duplicate webhooks handled safely  
✅ **Error Logging** - All callbacks logged to database  
✅ **Automatic Retry** - Failed callbacks can be retried  
✅ **Stock Management** - Automatic stock deduction on payment  
✅ **Multi-Tenant** - Isolated per tenant  

---

## 🛠️ Webhook Handler Service

### File: `app/Services/WebhookHandlerService.php`

**Key Methods:**

```php
// Handle Midtrans webhook
handleMidtrans(array $payload, ?string $signature): array

// Handle Xendit webhook
handleXendit(array $payload, ?string $signature): array

// Verify signatures
verifyMidtransSignature(array $payload, ?string $signature, string $secret): bool
verifyXenditSignature(array $payload, ?string $signature, string $secret): bool

// Status mapping
mapMidtransStatus(string $transactionStatus, ?string $fraudStatus): string
mapXenditStatus(string $status): string

// Retry failed callbacks
retryFailedCallbacks(int $limit = 10): array
```

### Midtrans Webhook Flow

```php
1. Receive webhook payload
2. Log callback to database
3. Verify SHA512 signature
4. Extract order_id and status
5. Find payment transaction
6. Update transaction status
7. If success → Update sales order
8. If success → Deduct stock
9. Mark callback as processed
```

**Sample Midtrans Payload:**
```json
{
  "transaction_time": "2026-04-04T10:30:00+07:00",
  "transaction_status": "settlement",
  "transaction_id": "abc123-def456",
  "status_message": "Success, transaction is found",
  "status_code": "200",
  "signature_key": "sha512_hash_here",
  "payment_type": "gopay",
  "order_id": "PAY-20260404-001",
  "merchant_id": "G123456789",
  "gross_amount": "150000.00",
  "fraud_status": "accept",
  "currency": "IDR"
}
```

**Signature Generation:**
```php
$hashInput = $order_id . $status_code . $gross_amount . $server_key;
$signature = hash('sha512', $hashInput);
```

### Xendit Webhook Flow

```php
1. Receive webhook payload
2. Log callback to database
3. Verify HMAC SHA256 signature
4. Extract external_id and status
5. Find payment transaction
6. Update transaction status
7. If success → Update sales order
8. If success → Deduct stock
9. Mark callback as processed
```

**Sample Xendit Payload:**
```json
{
  "id": "inv_abc123",
  "external_id": "PAY-20260404-001",
  "user_id": "user_xyz",
  "is_high": false,
  "payment_method": "QRIS",
  "status": "PAID",
  "paid_amount": 150000,
  "paid_at": "2026-04-04T10:30:00+07:00",
  "created": "2026-04-04T10:25:00+07:00",
  "updated": "2026-04-04T10:30:00+07:00",
  "currency": "IDR"
}
```

**Signature Generation:**
```php
$signature = hash_hmac('sha256', json_encode($payload), $webhook_secret);
```

---

## 🧪 Testing Webhooks

### Test Endpoints

All test endpoints require authentication via Sanctum token.

#### 1. Test Midtrans Webhook

```bash
curl -X POST https://yourdomain.com/api/payment/webhook-test/midtrans \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "order_id": "PAY-TEST-20260404",
    "amount": 150000,
    "webhook_secret": "your-secret-key"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Test webhook sent",
  "payload": { ... },
  "result": {
    "success": true,
    "message": "Webhook processed successfully"
  }
}
```

#### 2. Test Xendit Webhook

```bash
curl -X POST https://yourdomain.com/api/payment/webhook-test/xendit \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "order_id": "PAY-TEST-20260404",
    "amount": 150000,
    "webhook_secret": "your-secret-key"
  }'
```

#### 3. Get Webhook History

```bash
curl -X GET "https://yourdomain.com/api/payment/webhook-test/history?limit=50&provider=midtrans&processed=false" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### 4. Retry Failed Webhooks

```bash
curl -X POST https://yourdomain.com/api/payment/webhook-test/retry-failed \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"limit": 10}'
```

#### 5. Get Webhook Statistics

```bash
curl -X GET https://yourdomain.com/api/payment/webhook-test/stats \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_callbacks": 150,
    "processed": 145,
    "pending": 3,
    "failed": 2,
    "by_provider": [
      {
        "provider": "midtrans",
        "count": 100,
        "processed_count": 98
      },
      {
        "provider": "xendit",
        "count": 50,
        "processed_count": 47
      }
    ],
    "recent_failures": [ ... ]
  }
}
```

---

## 🖨️ Receipt Templates

### Architecture

```
SalesOrder Completed
         ↓
ReceiptTemplateService
         ↓
├─ Print Header (Logo, Company Info)
├─ Print Order Info (Number, Date, Customer)
├─ Print Items (Name, Qty, Price)
├─ Print Totals (Subtotal, Tax, Discount, Total)
├─ Print Payment Info (Method, Paid, Change)
├─ Print Footer (Thank you, Website)
└─ Cut Paper
```

### File: `app/Services/ReceiptTemplateService.php`

**Key Methods:**

```php
// Print sales receipt
printSalesReceipt(SalesOrder $order): array

// Print kitchen ticket (F&B)
printKitchenTicket(array $ticketData): array

// Print barcode label
printBarcodeLabel(string $barcode, string $productName, float $price): array

// Print QR code
printQrCode(string $qrData): void

// Create printer instance
static createPrinter(string $type, string $destination, int $paperWidth = 80): ?self
```

### Supported Paper Sizes

| Size | Characters/Line | Use Case |
|------|----------------|----------|
| 58mm | 32 chars | Compact receipts, mobile printers |
| 80mm | 48 chars | Standard POS receipts |

### Column Widths

**58mm Paper:**
- Item Name: 16 chars
- Quantity: 4 chars (right-aligned)
- Price: 10 chars (right-aligned)

**80mm Paper:**
- Item Name: 24 chars
- Quantity: 6 chars (right-aligned)
- Price: 16 chars (right-aligned)

---

## 🎨 Receipt Customization

### Configuration Options

Edit `config/pos_printer.php`:

```php
'receipt' => [
    // Company information
    'company_name' => env('RECEIPT_COMPANY_NAME', 'Your Store'),
    'address' => env('RECEIPT_ADDRESS', '123 Main Street'),
    'phone' => env('RECEIPT_PHONE', '021-12345678'),
    
    // Logo
    'show_logo' => env('RECEIPT_SHOW_LOGO', false),
    'logo_path' => env('RECEIPT_LOGO_PATH', '/images/logo.png'),
    
    // Footer
    'footer_text' => env('RECEIPT_FOOTER_TEXT', 'Thank you for your purchase!'),
    'show_website' => env('RECEIPT_SHOW_WEBSITE', true),
    
    // QR Code
    'show_qr_code' => env('RECEIPT_SHOW_QR_CODE', false),
    'qr_data' => 'website_url_or_feedback_form',
    
    // Paper settings
    'paper_width' => env('RECEIPT_PAPER_WIDTH', 80), // 58 or 80
    'auto_cut' => env('RECEIPT_AUTO_CUT', true),
],
```

### Brand Integration

Edit `config/brand.php`:

```php
'receipt' => [
    'show_logo' => env('RECEIPT_SHOW_LOGO', false),
    'footer_message' => env('RECEIPT_FOOTER_MESSAGE', 'Thank you!'),
    'show_qr_code' => env('RECEIPT_SHOW_QR_CODE', true),
    'paper_width' => env('RECEIPT_PAPER_WIDTH', 80),
],
```

---

## 📝 Sample Receipt Output

### 80mm Receipt Example

```
========================================
           QALCUITY ERP
       123 Main Street, Jakarta
          Tel: 021-12345678
========================================
Order #: POS-20260404-A1B2C
Date: 04/04/2026 14:30
Customer: John Doe
Cashier: Admin
----------------------------------------
Item                     Qty       Price
----------------------------------------
Nasi Goreng Spesial       2   Rp 50.000
Es Teh Manis              3   Rp 15.000
Ayam Bakar Madu           1   Rp 45.000
----------------------------------------
Subtotal:                Rp 110.000
Discount:                -Rp 10.000
Tax:                     Rp 10.000
========================================
TOTAL:                   Rp 110.000
========================================

Payment Method: Qris
Paid: Rp 110.000

----------------------------------------
    Thank you for your purchase!
  Please keep this receipt for your
          records.

      qalcuity-erp.com

========================================
```

### 58mm Receipt Example

```
================================
     QALCUITY ERP
  123 Main Street
   Tel: 021-12345678
================================
Order #: POS-20260404-A1B2C
Date: 04/04/2026 14:30
Customer: John Doe
--------------------------------
Item            Qty    Price
--------------------------------
Nasi Goreng      2  Rp 50.000
Es Teh           3  Rp 15.000
Ayam Bakar       1  Rp 45.000
--------------------------------
Subtotal:      Rp 110.000
Discount:      -Rp 10.000
Tax:           Rp 10.000
================================
TOTAL:         Rp 110.000
================================

Payment: Qris
Paid: Rp 110.000

--------------------------------
  Thank you for your purchase!
   Keep this receipt please.

    qalcuity-erp.com

================================
```

---

## 🔌 Usage Examples

### Print Receipt After Payment

```php
use App\Services\ReceiptTemplateService;
use App\Models\SalesOrder;

// After payment completed
$order = SalesOrder::find($orderId);

// Create printer (USB example)
$printer = ReceiptTemplateService::createPrinter(
    'usb',
    'POS-58',  // Printer name in Windows
    80         // Paper width
);

if ($printer) {
    $result = $printer->printSalesReceipt($order);
    
    if ($result['success']) {
        return response()->json(['message' => 'Receipt printed']);
    } else {
        return response()->json(['error' => $result['error']], 500);
    }
}
```

### Print Kitchen Ticket

```php
$ticketData = [
    'table_number' => 'T05',
    'order_number' => 'ORD-20260404-001',
    'items' => [
        [
            'name' => 'Nasi Goreng Spesial',
            'quantity' => 2,
            'notes' => 'Extra spicy',
            'modifiers' => ['No onion', 'Extra egg']
        ],
        [
            'name' => 'Es Teh Manis',
            'quantity' => 3,
            'notes' => '',
            'modifiers' => []
        ]
    ],
    'special_instructions' => 'URGENT - Customer waiting!'
];

$printer = ReceiptTemplateService::createPrinter('network', '192.168.1.101:9100', 80);
$result = $printer->printKitchenTicket($ticketData);
```

### Print Barcode Label

```php
$printer = ReceiptTemplateService::createPrinter('usb', 'LABEL-PRINTER', 58);
$result = $printer->printBarcodeLabel(
    'PROD-12345',
    'Premium Coffee Beans 250g',
    75000
);
```

---

## 🔐 Security Best Practices

### Webhook Security

1. **Always verify signatures:**
```php
if ($gateway && $gateway->webhook_secret) {
    if (!$this->verifyMidtransSignature($payload, $signature, $gateway->webhook_secret)) {
        return ['success' => false, 'error' => 'Invalid signature'];
    }
}
```

2. **Use HTTPS for webhook URLs:**
```
✅ https://yourdomain.com/api/payment/webhook/midtrans
❌ http://yourdomain.com/api/payment/webhook/midtrans
```

3. **Store webhook secrets securely:**
```php
// In TenantPaymentGateway model
protected function casts(): array
{
    return [
        'credentials' => 'encrypted:array',
        'webhook_secret' => 'encrypted',
    ];
}
```

4. **Log all webhook attempts:**
```php
PaymentCallback::create([
    'tenant_id' => $this->tenantId,
    'provider' => 'midtrans',
    'payload' => json_encode($payload),
    'signature' => $signature,
    'processed' => false,
]);
```

---

## 🐛 Troubleshooting

### Problem: Webhook not received

**Check:**
1. Webhook URL accessible from internet
2. SSL certificate valid
3. Firewall allows incoming POST
4. Payment gateway dashboard shows delivery status

**Debug:**
```bash
# Check logs
tail -f storage/logs/laravel.log | grep webhook

# Check database
SELECT * FROM payment_callbacks ORDER BY created_at DESC LIMIT 10;
```

### Problem: Invalid signature

**Solution:**
1. Verify webhook secret matches in both systems
2. Check signature generation algorithm
3. Ensure payload hasn't been modified

**Test:**
```bash
curl -X POST https://yourdomain.com/api/payment/webhook-test/midtrans \
  -H "Authorization: Bearer TOKEN" \
  -d '{"webhook_secret": "your-secret"}'
```

### Problem: Receipt not printing

**Check:**
1. Printer connected and online
2. Correct printer type (usb/network/file)
3. Paper loaded correctly
4. Printer driver installed

**Debug:**
```php
// Test printer connection
$printer = ReceiptTemplateService::createPrinter('usb', 'POS-58', 80);
if (!$printer) {
    Log::error("Failed to connect to printer");
}
```

### Problem: Stock not deducted

**Check:**
1. Webhook processed successfully
2. Sales order exists
3. Product stock available
4. Check `stock_deducted_at` field

**Query:**
```sql
SELECT 
    so.number,
    so.status,
    so.stock_deducted_at,
    pt.transaction_number,
    pt.status as payment_status
FROM sales_orders so
JOIN payment_transactions pt ON so.id = pt.sales_order_id
WHERE so.stock_deducted_at IS NULL
AND pt.status = 'success';
```

---

## 📊 Monitoring

### Key Metrics

```sql
-- Webhook processing rate
SELECT 
    provider,
    COUNT(*) as total,
    SUM(CASE WHEN processed = 1 THEN 1 ELSE 0 END) as processed,
    ROUND(SUM(CASE WHEN processed = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as success_rate
FROM payment_callbacks
WHERE created_at > NOW() - INTERVAL 24 HOUR
GROUP BY provider;

-- Average processing time
SELECT 
    AVG(TIMESTAMPDIFF(SECOND, created_at, processed_at)) as avg_seconds
FROM payment_callbacks
WHERE processed = 1
AND created_at > NOW() - INTERVAL 24 HOUR;

-- Failed webhooks by error
SELECT 
    error_message,
    COUNT(*) as count
FROM payment_callbacks
WHERE processed = 0
AND error_message IS NOT NULL
GROUP BY error_message
ORDER BY count DESC;
```

### Alerts Setup

Monitor for:
- ❌ Webhook success rate < 95%
- ❌ Failed webhooks > 10/hour
- ❌ Processing time > 5 seconds
- ❌ Unprocessed callbacks older than 1 hour

---

## ✅ Checklist

### Webhook Setup
- [ ] Webhook URL configured in payment gateway
- [ ] Webhook secret generated and stored
- [ ] HTTPS enabled
- [ ] Signature verification working
- [ ] Test webhook successful
- [ ] Error logging configured
- [ ] Retry mechanism tested

### Receipt Printing
- [ ] Printer connected and tested
- [ ] Paper size configured (58mm or 80mm)
- [ ] Company info customized
- [ ] Logo uploaded (optional)
- [ ] Footer message set
- [ ] Test receipt printed successfully
- [ ] Auto-print after payment enabled

---

## 🚀 Deployment

1. **Configure environment variables:**
```env
RECEIPT_COMPANY_NAME="Your Store"
RECEIPT_ADDRESS="123 Main St"
RECEIPT_PHONE=021-12345678
RECEIPT_FOOTER_TEXT="Thank you!"
RECEIPT_PAPER_WIDTH=80
```

2. **Setup webhook URLs in payment gateways:**
- Midtrans: Settings > Configuration > Notification URL
- Xendit: Developers > Webhooks

3. **Test end-to-end flow:**
- Generate QRIS payment
- Complete payment (sandbox)
- Verify webhook received
- Check receipt printed
- Verify stock deducted

4. **Monitor first transactions:**
```bash
tail -f storage/logs/laravel.log | grep -E "webhook|receipt"
```

---

## 📞 Support

For issues with:
- **Webhooks**: Check `payment_callbacks` table
- **Receipts**: Check printer connection and logs
- **Signatures**: Verify webhook secret configuration
- **Stock**: Check `stock_deducted_at` field

---

**Implementation complete and production-ready!** 🎉🖨️🔔
