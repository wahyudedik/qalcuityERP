# Telecom Module - API Documentation

## Base URL

```
Production: https://your-domain.com/api/telecom
Staging: https://staging.your-domain.com/api/telecom
Local: http://localhost:8000/api/telecom
```

## Authentication

All API endpoints require authentication using Laravel Sanctum tokens.

### Get API Token

```bash
POST /login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password"
}

Response:
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

### Use Token in Requests

```bash
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

---

## 📡 Device Management

### Register New Device

**Endpoint:** `POST /api/telecom/devices`

**Request:**
```json
{
  "name": "Main Router",
  "brand": "mikrotik",
  "device_type": "router",
  "ip_address": "192.168.88.1",
  "port": 8728,
  "username": "admin",
  "password": "password123",
  "location": "Server Room A",
  "notes": "Primary internet gateway"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Device registered successfully",
  "data": {
    "id": 1,
    "name": "Main Router",
    "brand": "mikrotik",
    "device_type": "router",
    "ip_address": "192.168.88.1",
    "port": 8728,
    "status": "pending",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

**Validation Errors (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "ip_address": ["The ip address field must be a valid IP address."],
    "brand": ["The selected brand is invalid."]
  }
}
```

---

### List All Devices

**Endpoint:** `GET /api/telecom/devices`

**Query Parameters:**
- `status` (optional): Filter by status (online/offline/pending)
- `brand` (optional): Filter by brand (mikrotik/ubiquiti/openwrt)
- `per_page` (optional): Items per page (default: 15)

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Main Router",
      "brand": "mikrotik",
      "status": "online",
      "last_seen_at": "2024-01-15T14:30:00Z"
    },
    {
      "id": 2,
      "name": "Backup Router",
      "brand": "mikrotik",
      "status": "offline",
      "last_seen_at": "2024-01-14T08:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 2,
    "per_page": 15
  }
}
```

---

### Get Device Status

**Endpoint:** `GET /api/telecom/devices/{id}/status`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "device_id": 1,
    "name": "Main Router",
    "status": "online",
    "uptime": "15 days, 3 hours",
    "cpu_usage": 25.5,
    "memory_usage": 45.2,
    "active_users": 42,
    "bandwidth": {
      "download_mbps": 85.3,
      "upload_mbps": 42.1
    },
    "last_seen_at": "2024-01-15T14:30:00Z"
  }
}
```

**Response (404 Not Found):**
```json
{
  "success": false,
  "message": "Device not found"
}
```

---

### Update Device

**Endpoint:** `PUT /api/telecom/devices/{id}`

**Request:**
```json
{
  "name": "Updated Router Name",
  "location": "New Location",
  "notes": "Updated notes"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Device updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Router Name",
    ...
  }
}
```

---

### Delete Device

**Endpoint:** `DELETE /api/telecom/devices/{id}`

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Device deleted successfully"
}
```

---

## 👥 Hotspot User Management

### Create Hotspot User

**Endpoint:** `POST /api/telecom/hotspot/users`

**Request:**
```json
{
  "device_id": 1,
  "customer_id": 123,
  "username": "customer001",
  "password": "securepass123",
  "profile": "premium_50mbps",
  "comment": "Customer subscription - John Doe"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Hotspot user created successfully",
  "data": {
    "id": 456,
    "username": "customer001",
    "device_id": 1,
    "customer_id": 123,
    "profile": "premium_50mbps",
    "status": "active",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

---

### Remove Hotspot User

**Endpoint:** `DELETE /api/telecom/hotspot/users/{username}`

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Hotspot user removed successfully"
}
```

---

### List Active Users

**Endpoint:** `GET /api/telecom/hotspot/users`

**Query Parameters:**
- `device_id` (optional): Filter by device
- `status` (optional): active/inactive

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 456,
      "username": "customer001",
      "profile": "premium_50mbps",
      "status": "active",
      "bytes_in": 1073741824,
      "bytes_out": 536870912,
      "session_time": 86400
    }
  ]
}
```

---

## 📊 Usage Tracking

### Get Customer Usage

**Endpoint:** `GET /api/telecom/usage/{customerId}`

**Query Parameters:**
- `period` (optional): current/month/year/custom
- `start_date` (optional): YYYY-MM-DD (for custom period)
- `end_date` (optional): YYYY-MM-DD (for custom period)

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "customer_id": 123,
    "subscription_id": 789,
    "package_name": "Premium 50Mbps",
    "current_period": {
      "start": "2024-01-01T00:00:00Z",
      "end": "2024-01-31T23:59:59Z"
    },
    "usage": {
      "download_bytes": 5368709120,
      "upload_bytes": 2684354560,
      "total_bytes": 8053063680,
      "quota_bytes": 10737418240,
      "usage_percentage": 75.0
    },
    "status": "active",
    "quota_exceeded": false
  }
}
```

---

## 🎫 Voucher Management

### Generate Vouchers

**Endpoint:** `POST /api/telecom/vouchers/generate`

**Request:**
```json
{
  "package_id": 5,
  "quantity": 10,
  "code_length": 8,
  "validity_hours": 24,
  "batch_number": "BATCH-2024-001"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "10 vouchers generated successfully",
  "data": [
    {
      "id": 1001,
      "code": "A7K9M2P4",
      "package_id": 5,
      "package_name": "Daily 10Mbps",
      "valid_from": "2024-01-15T10:30:00Z",
      "valid_until": "2024-01-16T10:30:00Z",
      "status": "unused"
    },
    ...
  ]
}
```

---

### List Vouchers

**Endpoint:** `GET /api/telecom/vouchers`

**Query Parameters:**
- `status` (optional): unused/used/expired/revoked
- `package_id` (optional): Filter by package
- `batch_number` (optional): Filter by batch

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1001,
      "code": "A7K9M2P4",
      "package_name": "Daily 10Mbps",
      "status": "unused",
      "valid_until": "2024-01-16T10:30:00Z",
      "redeemed_at": null,
      "redeemed_by": null
    }
  ],
  "meta": {
    "total": 50,
    "unused": 35,
    "used": 10,
    "expired": 5
  }
}
```

---

### Redeem Voucher

**Endpoint:** `POST /api/telecom/vouchers/{code}/redeem`

**Request:**
```json
{
  "customer_id": 123,
  "device_id": 1
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Voucher redeemed successfully",
  "data": {
    "voucher_code": "A7K9M2P4",
    "package_name": "Daily 10Mbps",
    "customer_id": 123,
    "redeemed_at": "2024-01-15T10:30:00Z",
    "valid_until": "2024-01-16T10:30:00Z"
  }
}
```

**Response (422 Unprocessable Entity):**
```json
{
  "success": false,
  "message": "Voucher has expired",
  "errors": {
    "voucher": ["This voucher is no longer valid"]
  }
}
```

---

## 🔔 Webhooks

### Router Usage Webhook

**Endpoint:** `POST /api/telecom/webhook/router-usage`

**Headers:**
```
X-Webhook-Signature: hmac-sha256-signature
Content-Type: application/json
```

**Request:**
```json
{
  "device_id": 1,
  "subscription_id": 789,
  "bytes_in": 104857600,
  "bytes_out": 52428800,
  "timestamp": "2024-01-15T14:30:00Z"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Usage data recorded"
}
```

---

### Device Alert Webhook

**Endpoint:** `POST /api/telecom/webhook/device-alert`

**Request:**
```json
{
  "device_id": 1,
  "alert_type": "offline",
  "severity": "high",
  "message": "Device unreachable for 5 minutes",
  "timestamp": "2024-01-15T14:30:00Z"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Alert processed"
}
```

---

## 📦 Internet Packages

### List Packages

**Endpoint:** `GET /api/telecom/packages`

**Query Parameters:**
- `is_active` (optional): true/false
- `billing_cycle` (optional): monthly/yearly

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "name": "Premium 50Mbps",
      "download_speed_mbps": 50,
      "upload_speed_mbps": 20,
      "quota_bytes": 10737418240,
      "price": 150000,
      "billing_cycle": "monthly",
      "is_active": true
    }
  ]
}
```

---

### Create Package

**Endpoint:** `POST /api/telecom/packages`

**Request:**
```json
{
  "name": "Business 100Mbps",
  "download_speed_mbps": 100,
  "upload_speed_mbps": 50,
  "quota_bytes": 21474836480,
  "price": 300000,
  "billing_cycle": "monthly",
  "description": "High-speed business package"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Package created successfully",
  "data": {
    "id": 6,
    "name": "Business 100Mbps",
    ...
  }
}
```

---

## 🔐 Error Responses

### Common Error Codes

| Code | Meaning | Example |
|------|---------|---------|
| 401 | Unauthorized | Missing or invalid token |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource doesn't exist |
| 422 | Validation Error | Invalid input data |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Server Error | Internal server error |

### Error Response Format

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Error message 1", "Error message 2"]
  }
}
```

---

## 📝 Rate Limiting

- **Authenticated requests:** 60 requests per minute
- **Unauthenticated requests:** 20 requests per minute
- **Webhook endpoints:** No rate limit (signature verified)

**Rate Limit Headers:**
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1705312200
```

---

## 🌍 Localization

API responses support multiple languages via `Accept-Language` header:

```
Accept-Language: id-ID  // Indonesian
Accept-Language: en-US  // English (default)
```

---

## 📚 Code Examples

### PHP (Laravel)

```php
use Illuminate\Support\Facades\Http;

$response = Http::withToken($apiToken)
    ->post('https://your-domain.com/api/telecom/devices', [
        'name' => 'My Router',
        'brand' => 'mikrotik',
        'ip_address' => '192.168.88.1',
    ]);

$device = $response->json()['data'];
```

### JavaScript (Fetch)

```javascript
const response = await fetch('/api/telecom/devices', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    name: 'My Router',
    brand: 'mikrotik',
    ip_address: '192.168.88.1',
  }),
});

const device = await response.json();
```

### cURL

```bash
curl -X POST https://your-domain.com/api/telecom/devices \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "My Router",
    "brand": "mikrotik",
    "ip_address": "192.168.88.1"
  }'
```

---

## 🔄 Versioning

Current API version: **v1**

Version is included in the base URL path (future-proofing):
```
/api/v1/telecom/devices
```

Currently simplified to `/api/telecom/devices` for v1.

---

**Last Updated:** April 4, 2026  
**API Version:** 1.0.0  
**Documentation Status:** Complete ✅
