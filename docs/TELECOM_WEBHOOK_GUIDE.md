# Webhook Configuration Guide

## Overview

The Telecom Module uses webhooks to notify external systems about important events such as quota exceeded, device offline, payment received, and subscription changes.

---

## 📋 Available Webhook Events

### Telecom Events

| Event | Trigger | Payload Example |
|-------|---------|-----------------|
| `telecom.subscription.created` | New subscription activated | Subscription details |
| `telecom.subscription.renewed` | Subscription renewed | Renewal info |
| `telecom.subscription.suspended` | Subscription suspended | Suspension reason |
| `telecom.subscription.cancelled` | Subscription cancelled | Cancellation details |
| `telecom.quota_exceeded` | Customer exceeds quota | Usage data |
| `telecom.quota_warning` | 80% quota reached | Warning threshold |
| `telecom.device_offline` | Device unreachable | Device status |
| `telecom.device_online` | Device back online | Recovery info |
| `telecom.payment_received` | Payment processed | Invoice details |
| `telecom.voucher.redeemed` | Voucher used | Redemption data |

---

## 🔧 Configuring Webhooks

### Step 1: Access Webhook Settings

Navigate to: **Settings → Integrations → Webhooks**

Or via API:
```bash
GET /api/webhooks
```

### Step 2: Create New Webhook Endpoint

**Via Admin Panel:**

1. Click **Create Webhook**
2. Fill in details:
   - **Name**: "Quota Alert System"
   - **URL**: `https://your-app.com/webhooks/telecom/quota`
   - **Events**: Select `telecom.quota_exceeded`, `telecom.quota_warning`
   - **Secret**: Auto-generated or custom
   - **Active**: ✅ Enabled
3. Click **Save**

**Via API:**
```bash
POST /api/webhooks
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN

{
  "name": "Quota Alert System",
  "url": "https://your-app.com/webhooks/telecom/quota",
  "events": [
    "telecom.quota_exceeded",
    "telecom.quota_warning"
  ],
  "secret": "your_webhook_secret_key",
  "active": true
}
```

---

## 🔐 Webhook Security

### Signature Verification

All webhook requests include an `X-Webhook-Signature` header containing HMAC-SHA256 signature.

**Verify Signature:**

```php
// PHP Example
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'];
$secret = 'your_webhook_secret_key';

$expectedSignature = hash_hmac('sha256', $payload, $secret);

if (!hash_equals($expectedSignature, $signature)) {
    http_response_code(401);
    exit('Invalid signature');
}
```

```javascript
// Node.js Example
const crypto = require('crypto');

const payload = req.body;
const signature = req.headers['x-webhook-signature'];
const secret = 'your_webhook_secret_key';

const expectedSignature = crypto
  .createHmac('sha256', secret)
  .update(JSON.stringify(payload))
  .digest('hex');

if (signature !== expectedSignature) {
  return res.status(401).send('Invalid signature');
}
```

```python
# Python Example
import hmac
import hashlib

payload = request.get_data()
signature = request.headers.get('X-Webhook-Signature')
secret = b'your_webhook_secret_key'

expected_signature = hmac.new(
    secret,
    payload,
    hashlib.sha256
).hexdigest()

if not hmac.compare_digest(signature, expected_signature):
    return 'Invalid signature', 401
```

---

## 📨 Webhook Payload Examples

### Quota Exceeded Event

**Event:** `telecom.quota_exceeded`

```json
{
  "event": "telecom.quota_exceeded",
  "timestamp": "2024-01-15T14:30:00Z",
  "tenant_id": 1,
  "data": {
    "subscription_id": 789,
    "customer_id": 123,
    "customer_name": "John Doe",
    "package_name": "Premium 50Mbps",
    "quota_bytes": 10737418240,
    "used_bytes": 11811160064,
    "usage_percentage": 110.0,
    "exceeded_at": "2024-01-15T14:25:00Z"
  }
}
```

---

### Device Offline Event

**Event:** `telecom.device_offline`

```json
{
  "event": "telecom.device_offline",
  "timestamp": "2024-01-15T14:30:00Z",
  "tenant_id": 1,
  "data": {
    "device_id": 1,
    "device_name": "Main Office Router",
    "ip_address": "192.168.88.1",
    "brand": "mikrotik",
    "last_seen_at": "2024-01-15T14:20:00Z",
    "offline_duration_minutes": 10,
    "affected_subscriptions": 42,
    "severity": "critical"
  }
}
```

---

### Payment Received Event

**Event:** `telecom.payment_received`

```json
{
  "event": "telecom.payment_received",
  "timestamp": "2024-01-15T14:30:00Z",
  "tenant_id": 1,
  "data": {
    "invoice_id": 456,
    "invoice_number": "QALC-TEL-20240115-0001",
    "customer_id": 123,
    "customer_name": "John Doe",
    "amount": 150000,
    "currency": "IDR",
    "payment_method": "bank_transfer",
    "paid_at": "2024-01-15T14:28:00Z",
    "subscription_id": 789,
    "subscription_status": "active"
  }
}
```

---

### Subscription Created Event

**Event:** `telecom.subscription.created`

```json
{
  "event": "telecom.subscription.created",
  "timestamp": "2024-01-15T14:30:00Z",
  "tenant_id": 1,
  "data": {
    "subscription_id": 789,
    "customer_id": 123,
    "customer_name": "John Doe",
    "package_id": 5,
    "package_name": "Premium 50Mbps",
    "started_at": "2024-01-15T14:30:00Z",
    "next_billing_date": "2024-02-15T00:00:00Z",
    "monthly_fee": 150000,
    "device_id": 1
  }
}
```

---

## 🔄 Retry Mechanism

### Automatic Retries

If your endpoint returns a non-2xx status code, the system will retry:

| Attempt | Delay | Max Attempts |
|---------|-------|--------------|
| 1 | Immediate | - |
| 2 | 5 minutes | - |
| 3 | 15 minutes | - |
| 4 | 1 hour | - |
| 5 | 6 hours | Final attempt |

### Retry Headers

Webhook responses include retry information:

```
X-Webhook-Attempt: 2
X-Webhook-Max-Attempts: 5
X-Webhook-Next-Retry: 2024-01-15T14:35:00Z
```

---

## 🛠️ Testing Webhooks

### Test Mode

Enable test mode to send test payloads without triggering real events.

**Via Admin Panel:**
1. Edit webhook endpoint
2. Toggle **Test Mode** ON
3. Click **Send Test Payload**
4. Check your endpoint logs

**Via API:**
```bash
POST /api/webhooks/{id}/test
Content-Type: application/json

{
  "event": "telecom.quota_exceeded"
}
```

### Local Development with ngrok

For local testing, use ngrok to expose your local server:

```bash
# Install ngrok
npm install -g ngrok

# Start ngrok tunnel
ngrok http 8000

# Use the provided URL as webhook endpoint
# Example: https://abc123.ngrok.io/webhooks/telecom
```

---

## 📊 Monitoring Webhooks

### View Delivery History

Navigate to: **Settings → Integrations → Webhooks → [Endpoint] → History**

Or via API:
```bash
GET /api/webhooks/{id}/deliveries?per_page=50
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1001,
      "event": "telecom.quota_exceeded",
      "status": "delivered",
      "response_code": 200,
      "attempt": 1,
      "delivered_at": "2024-01-15T14:30:05Z",
      "response_time_ms": 145
    },
    {
      "id": 1002,
      "event": "telecom.device_offline",
      "status": "failed",
      "response_code": 500,
      "attempt": 3,
      "next_retry_at": "2024-01-15T15:30:00Z",
      "error": "Internal Server Error"
    }
  ]
}
```

### Webhook Logs

Check detailed logs for debugging:

```bash
GET /api/webhooks/{id}/deliveries/{deliveryId}/log
```

---

## 🚨 Troubleshooting

### Issue: Webhooks Not Being Delivered

**Checklist:**
- [ ] Webhook endpoint is active
- [ ] URL is correct and accessible
- [ ] SSL certificate is valid (HTTPS required)
- [ ] Firewall allows incoming connections
- [ ] Endpoint returns 2xx status code

**Debug Steps:**
```bash
# Test endpoint accessibility
curl -X POST https://your-endpoint.com/webhook \
  -H "Content-Type: application/json" \
  -d '{"test": true}'

# Check SSL certificate
openssl s_client -connect your-endpoint.com:443

# Review delivery history
GET /api/webhooks/{id}/deliveries
```

---

### Issue: Invalid Signature Errors

**Common Causes:**
- Secret key mismatch
- Payload modified before verification
- Encoding issues

**Solution:**
```php
// Ensure you're using the raw payload
$payload = file_get_contents('php://input'); // NOT $_POST

// Verify secret matches
$secret = config('services.webhook.secret');

// Use hash_equals for timing-safe comparison
if (!hash_equals($expectedSignature, $signature)) {
    // Invalid
}
```

---

### Issue: High Failure Rate

**Optimization Tips:**
1. Respond quickly (< 2 seconds)
2. Process asynchronously (queue jobs)
3. Implement idempotency
4. Add proper error handling
5. Monitor endpoint health

**Example Async Processing:**
```php
// Laravel Example
Route::post('/webhooks/telecom', function (Request $request) {
    // Verify signature first
    if (!verifySignature($request)) {
        return response()->json(['error' => 'Invalid signature'], 401);
    }
    
    // Queue processing
    ProcessWebhookJob::dispatch($request->all());
    
    // Return immediately
    return response()->json(['received' => true], 200);
});
```

---

## 📝 Best Practices

### 1. Idempotency

Handle duplicate webhook deliveries gracefully:

```php
public function handle(array $payload)
{
    $eventId = $payload['id'] ?? null;
    
    // Check if already processed
    if (ProcessedWebhook::where('event_id', $eventId)->exists()) {
        return; // Already processed
    }
    
    // Process event
    $this->processEvent($payload);
    
    // Mark as processed
    ProcessedWebhook::create(['event_id' => $eventId]);
}
```

### 2. Timeout Handling

Set appropriate timeout for webhook processing:

```nginx
# Nginx configuration
location /webhooks/telecom {
    proxy_read_timeout 30s;
    proxy_connect_timeout 10s;
}
```

### 3. Rate Limiting

Implement rate limiting on your endpoint:

```php
// Laravel middleware
Route::post('/webhooks/telecom', function () {
    // Process webhook
})->middleware('throttle:60,1'); // 60 requests per minute
```

### 4. Logging

Log all webhook deliveries for auditing:

```php
Log::info('Webhook received', [
    'event' => $payload['event'],
    'timestamp' => $payload['timestamp'],
    'subscription_id' => $payload['data']['subscription_id'] ?? null,
]);
```

---

## 🔗 Integration Examples

### Slack Notification

```php
// Send quota alert to Slack
if ($event === 'telecom.quota_exceeded') {
    Http::post('https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK', [
        'text' => "⚠️ Quota Exceeded: {$data['customer_name']} has exceeded their quota!",
        'attachments' => [[
            'color' => 'danger',
            'fields' => [
                ['title' => 'Customer', 'value' => $data['customer_name'], 'short' => true],
                ['title' => 'Usage', 'value' => "{$data['usage_percentage']}%", 'short' => true],
            ]
        ]]
    ]);
}
```

### SMS Alert

```php
// Send SMS to admin
if ($event === 'telecom.device_offline' && $data['severity'] === 'critical') {
    SmsService::send('+628123456789', 
        "CRITICAL: Router {$data['device_name']} is offline! {$data['affected_subscriptions']} customers affected."
    );
}
```

### Database Update

```php
// Update external CRM
if ($event === 'telecom.subscription.created') {
    DB::connection('crm')->table('customers')->where('id', $data['customer_id'])
        ->update([
            'internet_package' => $data['package_name'],
            'subscription_start' => $data['started_at'],
        ]);
}
```

---

## 📞 Support

For webhook-related issues:
- Email: support@qalcuity.com
- Documentation: https://docs.qalcuity.com/webhooks
- Status Page: https://status.qalcuity.com

---

**Last Updated:** April 4, 2026  
**Webhook Version:** 1.0.0  
**Guide Status:** Complete ✅
