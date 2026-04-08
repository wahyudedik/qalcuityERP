# Integration Marketplace - Complete Documentation

## 📋 Table of Contents
1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Installation & Setup](#installation--setup)
4. [Shopify Integration](#shopify-integration)
5. [WooCommerce Integration](#woocommerce-integration)
6. [Webhook System](#webhook-system)
7. [Sync Operations](#sync-operations)
8. [API Reference](#api-reference)
9. [Troubleshooting](#troubleshooting)
10. [Extending with New Connectors](#extending-with-new-connectors)

---

## Overview

The Integration Marketplace enables seamless bi-directional synchronization between QalcuityERP and external e-commerce platforms (Shopify, WooCommerce, and more).

### Key Features
- ✅ **Bi-directional Sync**: Products, Orders, Inventory
- ✅ **OAuth Authentication**: Secure token-based auth
- ✅ **Real-time Webhooks**: Instant event notifications
- ✅ **Automated Scheduling**: Configurable sync frequency
- ✅ **Error Handling**: Retry logic with exponential backoff
- ✅ **Monitoring**: Detailed sync logs and statistics
- ✅ **Multi-tenant**: Isolated data per tenant

### Supported Platforms
- ✅ **Shopify** (OAuth 2.0 + REST Admin API 2024-01)
- ✅ **WooCommerce** (OAuth 1.0a + REST API v3)
- 🚧 **Tokopedia** (Coming Soon)
- 🚧 **Shopee** (Coming Soon)
- 🚧 **Lazada** (Coming Soon)

---

## Architecture

### Components

```
┌─────────────────────────────────────────────────────┐
│                  Frontend (Blade)                    │
│  - Marketplace UI                                    │
│  - Setup Wizard                                      │
│  - Monitoring Dashboard                              │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│                 Controllers                          │
│  - IntegrationController (CRUD + Sync)              │
│  - OAuthController (Authentication)                 │
│  - WebhookController (Event Handling)               │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│              Connector Services                      │
│  - BaseConnector (Abstract)                         │
│    ├─ ShopifyConnector                              │
│    └─ WooCommerceConnector                          │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│               Queue Jobs                             │
│  - SyncProductsJob                                  │
│  - SyncOrdersJob                                    │
│  - SyncInventoryJob                                 │
│  - RetryWebhookDeliveriesJob                        │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│              Webhook System                          │
│  - WebhookDeliveryService                           │
│  - HMAC Signature Verification                      │
│  - Exponential Backoff Retry                        │
└─────────────────────────────────────────────────────┘
```

### Database Schema

```
integrations
├─ id, tenant_id, user_id
├─ name, slug, type, status
├─ config (JSON), oauth_tokens (JSON)
├─ sync_frequency, last_sync_at, next_sync_at
└─ metadata (JSON), created_at, updated_at

integration_configs
├─ id, tenant_id, integration_id
├─ key, value (encrypted), category
└─ is_encrypted, created_at, updated_at

integration_sync_logs
├─ id, tenant_id, integration_id
├─ sync_type, direction, status
├─ records_processed, records_failed
├─ error_message, duration_seconds
└─ details (JSON), created_at, updated_at

webhook_subscriptions
├─ id, tenant_id, integration_id
├─ endpoint_url, secret_key
├─ events (JSON), is_active
└─ last_triggered_at, created_at, updated_at

webhook_deliveries
├─ id, subscription_id
├─ event_type, payload (JSON)
├─ response_code, response_body
├─ attempt_count, max_attempts, status
├─ next_retry_at, delivered_at
└─ error_message, created_at, updated_at

ecommerce_product_mappings
├─ id, tenant_id, product_id, channel_id
├─ external_id, external_sku, external_variant_id
├─ is_active, metadata (JSON)
└─ last_synced_at, created_at, updated_at
```

---

## Installation & Setup

### Prerequisites
- Laravel 13+
- PHP 8.2+
- MySQL 8.0+
- Redis (for queue processing)
- Node.js 18+ (for frontend assets)

### Step 1: Run Migrations

```bash
php artisan migrate
```

### Step 2: Configure Queue Worker

```bash
# Start queue worker
php artisan queue:work --tries=3

# Or use supervisor for production
sudo supervisorctl restart all
```

### Step 3: Configure Scheduler

Add to crontab:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Step 4: Configure Environment

Add to `.env`:
```env
# Shopify
SHOPIFY_CLIENT_ID=your_shopify_client_id
SHOPIFY_CLIENT_SECRET=your_shopify_client_secret

# App URL (for webhooks)
APP_URL=https://your-domain.com

# Queue
QUEUE_CONNECTION=redis
```

---

## Shopify Integration

### Setup Steps

#### 1. Create Shopify Custom App
1. Go to **Shopify Admin** → **Settings** → **Apps and sales channels**
2. Click **Develop apps**
3. Click **Create an app**
4. Name it "QalcuityERP Integration"

#### 2. Configure API Permissions
Enable the following scopes:
- `read_products`, `write_products`
- `read_orders`, `write_orders`
- `read_inventory`, `write_inventory`

#### 3. Install & Authorize
1. Click **Install app**
2. Copy the **Admin API access token**
3. In QalcuityERP: Integrations → Shopify → Setup
4. Enter shop domain (e.g., `your-store.myshopify.com`)
5. Click **Save & Connect to Shopify**
6. Authorize the OAuth flow

### Sync Operations

#### Products (ERP → Shopify)
- Creates new products in Shopify
- Updates existing products
- Maps ERP product ID ↔ Shopify product ID
- Syncs variants and inventory

#### Orders (Shopify → ERP)
- Pulls new orders from Shopify
- Creates SalesOrder in ERP
- Maps customer information
- Syncs payment status

#### Inventory (Bi-directional)
- Updates stock levels in Shopify
- Prevents overselling
- Real-time sync via webhooks

---

## WooCommerce Integration

### Setup Steps

#### 1. Enable REST API
1. Go to **WooCommerce** → **Settings** → **Advanced** → **REST API**
2. Click **Add key**

#### 2. Generate API Keys
- **Description**: "QalcuityERP Integration"
- **User**: Select admin user
- **Permissions**: **Read/Write**
- Click **Generate API key**
- Copy **Consumer Key** and **Consumer Secret**

#### 3. Configure in QalcuityERP
1. Go to **Integrations** → **WooCommerce** → **Setup**
2. Enter:
   - Store URL (e.g., `https://your-store.com`)
   - Consumer Key
   - Consumer Secret
3. Click **Save & Test Connection**

#### 4. Create Webhooks (Optional but Recommended)
1. Go to **WooCommerce** → **Settings** → **Advanced** → **Webhooks**
2. Create webhooks for:
   - `Order created` → `https://your-domain.com/api/integrations/webhooks/woocommerce`
   - `Order updated` → same URL
   - `Product created` → same URL
   - `Product updated` → same URL

---

## Webhook System

### How It Works

1. **Event Occurs** on marketplace (e.g., new order)
2. **Marketplace sends webhook** to QalcuityERP
3. **WebhookController receives** and verifies HMAC signature
4. **WebhookDeliveryService creates** delivery record
5. **Delivery attempted** immediately
6. **If failed**, retry with exponential backoff:
   - Attempt 1: 1 minute
   - Attempt 2: 5 minutes
   - Attempt 3: 15 minutes
   - Attempt 4: 1 hour
   - Attempt 5: 4 hours

### Signature Verification

All webhooks are verified using HMAC-SHA256:

```php
// Shopify
$computedHmac = base64_encode(hash_hmac('sha256', $payload, $secret, true));
return hash_equals($computedHmac, $hmac);

// WooCommerce
$computedSignature = base64_encode(hash_hmac('sha256', $payload, $secret, true));
return hash_equals($computedSignature, $signature);
```

### Monitoring

View webhook delivery logs:
- **Route**: `/integrations/webhook-logs`
- **Shows**: Event type, status, attempts, response code, endpoint

---

## Sync Operations

### Manual Sync

Trigger sync from UI:
1. Go to **Integration Details** page
2. Click **Sync Products**, **Sync Orders**, or **Sync Inventory**
3. Or click **Sync All** to trigger all three

### Automated Sync

Scheduler runs based on frequency:
- **Real-time**: Every 5 minutes
- **Hourly**: Every hour
- **Daily**: 1:00 AM daily
- **Weekly**: Sunday 1:00 AM

### Sync Logs

View sync history:
- **Route**: `/integrations/{integration}/sync-history`
- **Shows**: Type, direction, status, records, duration, timestamp

### Success Rate

Calculate success rate:
```php
$successRate = ($successfulSyncs / $totalSyncs) * 100;
```

---

## API Reference

### Web Routes

```
GET    /integrations                          - List all integrations
GET    /integrations/create                   - Create integration form
POST   /integrations                          - Store new integration
GET    /integrations/{integration}            - Show integration details
GET    /integrations/{integration}/setup      - Setup wizard
PATCH  /integrations/{integration}/update     - Update configuration
DELETE /integrations/{integration}            - Delete integration

POST   /integrations/{integration}/test-connection  - Test API connection
POST   /integrations/{integration}/sync             - Trigger manual sync
POST   /integrations/{integration}/activate         - Activate integration
POST   /integrations/{integration}/deactivate       - Deactivate integration
POST   /integrations/{integration}/register-webhooks - Register webhooks

GET    /integrations/{integration}/sync-history - View sync logs
GET    /integrations/{integration}/sync-stats   - Get sync statistics

GET    /integrations/oauth/{provider}/start     - Start OAuth flow
GET    /integrations/oauth/{provider}/callback  - OAuth callback
POST   /integrations/oauth/{integration}/woocommerce/complete - Complete WooCommerce setup
POST   /integrations/oauth/{integration}/disconnect - Disconnect integration
POST   /integrations/oauth/{integration}/refresh-token - Refresh OAuth token
```

### Webhook Endpoints (Public)

```
POST   /api/integrations/webhooks/shopify         - Shopify webhooks
POST   /api/integrations/webhooks/woocommerce     - WooCommerce webhooks
POST   /api/integrations/webhooks/test            - Test endpoint
```

---

## Troubleshooting

### Issue: Authentication Failed

**Shopify**:
- Verify shop domain format: `your-store.myshopify.com`
- Check OAuth app has correct permissions
- Ensure app is installed and authorized

**WooCommerce**:
- Verify store URL includes `https://`
- Check Consumer Key/Secret are correct
- Ensure REST API is enabled in WooCommerce settings
- Verify user has admin permissions

### Issue: Webhooks Not Receiving Events

1. **Check webhook URL** is accessible from internet
2. **Verify signature** in logs
3. **Check firewall** allows POST requests
4. **Review webhook logs** for errors:
   ```bash
   tail -f storage/logs/laravel.log | grep webhook
   ```

### Issue: Sync Fails

1. **Check connection**:
   ```
   Integration Details → Test Connection
   ```

2. **Review sync logs**:
   ```
   Integration Details → Sync History
   ```

3. **Check queue worker**:
   ```bash
   php artisan queue:work --tries=3
   ```

4. **Check API rate limits**:
   - Shopify: 40 requests/minute
   - WooCommerce: No strict limit, but be reasonable

### Issue: Products Not Mapping

1. Check `ecommerce_product_mappings` table:
   ```sql
   SELECT * FROM ecommerce_product_mappings 
   WHERE product_id = 123 AND channel_id = 1;
   ```

2. Verify external_id is set after first sync

3. Check sync logs for errors

### Logs Location

```bash
# All logs
storage/logs/laravel.log

# Integration-specific logs
grep "Integration" storage/logs/laravel.log

# Webhook logs
grep "webhook" storage/logs/laravel.log

# Queue logs
grep "Job" storage/logs/laravel.log
```

---

## Extending with New Connectors

### Step 1: Create Connector Class

```php
namespace App\Services\Integrations;

use App\Models\Integration;

class TokopediaConnector extends BaseConnector
{
    public function authenticate(): bool
    {
        // Implement OAuth/API key auth
    }

    public function syncProducts(): array
    {
        // Implement product sync
    }

    public function syncOrders(): array
    {
        // Implement order sync
    }

    public function syncInventory(): array
    {
        // Implement inventory sync
    }

    public function registerWebhooks(): array
    {
        // Register webhooks on marketplace
    }

    public function handleWebhook(array $payload): void
    {
        // Process incoming webhook
    }
}
```

### Step 2: Add to Marketplace UI

In `IntegrationController@index`:
```php
$availableIntegrations['e-commerce'][] = [
    'slug' => 'tokopedia',
    'name' => 'Tokopedia',
    'description' => 'Integration with Tokopedia marketplace',
    'logo' => 'tokopedia.png',
];
```

### Step 3: Create Migration (if needed)

```bash
php artisan make:migration add_tokopedia_fields_to_integrations_table
```

### Step 4: Add Routes

In `routes/web.php`:
```php
Route::post('/tokopedia/callback', [OAuthController::class, 'handleTokopediaCallback']);
```

### Step 5: Test

1. Create integration in UI
2. Configure API credentials
3. Test connection
4. Run sync operations
5. Verify in logs

---

## Security Best Practices

### API Keys
- ✅ All sensitive configs encrypted using `encrypt()`
- ✅ Never log API keys or tokens
- ✅ Rotate tokens regularly

### Webhooks
- ✅ Always verify HMAC signatures
- ✅ Use HTTPS for webhook endpoints
- ✅ Implement idempotency checks

### Tenant Isolation
- ✅ All queries scoped to `tenant_id`
- ✅ Middleware enforces tenant context
- ✅ No cross-tenant data leakage

### Rate Limiting
- ✅ Built-in rate limiting in BaseConnector
- ✅ Configurable per connector
- ✅ Automatic backoff when limit reached

---

## Performance Optimization

### Queue Configuration
```env
QUEUE_CONNECTION=redis
QUEUE_WORKER_TIMEOUT=300
```

### Database Indexes
All tables include indexes on:
- `tenant_id`
- `integration_id`
- `status`
- `created_at`

### Caching
- Integration configs cached per request
- Product mappings cached when possible
- Sync statistics cached for 5 minutes

---

## Support & Resources

- **Documentation**: `/docs/INTEGRATION_MARKETPLACE.md`
- **API Docs**: `/api/integrations/webhooks/test`
- **Logs**: `storage/logs/laravel.log`
- **Queue Monitor**: `php artisan queue:monitor`

---

## Changelog

### v1.0.0 (April 2026)
- ✅ Initial release
- ✅ Shopify integration
- ✅ WooCommerce integration
- ✅ Webhook system with retry logic
- ✅ Automated sync scheduling
- ✅ Monitoring dashboard
- ✅ Product mapping system

---

## License

Proprietary - QalcuityERP © 2026
