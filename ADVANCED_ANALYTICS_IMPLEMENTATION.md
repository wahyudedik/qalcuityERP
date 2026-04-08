# Advanced Analytics Dashboard - Implementation Guide

## 📊 Overview

Advanced Analytics Dashboard telah berhasil diimplementasikan dengan fitur-fitur enterprise-grade untuk sistem AI ERP Qalcuity.

## ✅ What Was Implemented

### 1. **Controller** (654 lines)
- **File**: `app/Http/Controllers/Analytics/AdvancedAnalyticsDashboardController.php`
- **Features**:
  - Real-time KPI tracking (Revenue, Orders, Inventory, Customers)
  - Revenue trend analysis with daily breakdown
  - Top metrics (Products, Customers, Categories)
  - AI-powered predictive analytics
  - Sales forecasting with linear regression
  - Inventory demand prediction
  - Customer churn prediction
  - Custom report builder
  - Scheduled report generation
  - Export to PDF/Excel/CSV

### 2. **Routes** (6 new endpoints)
- **File**: `routes/web.php`
- **Endpoints**:
  - `GET /analytics/advanced` - Main advanced dashboard
  - `GET /analytics/predictive` - AI predictive analytics
  - `GET /analytics/report-builder` - Custom report builder UI
  - `POST /analytics/generate-report` - Generate custom report
  - `GET /analytics/scheduled-reports` - View scheduled reports
  - `POST /analytics/scheduled-reports` - Create scheduled report

### 3. **Model**: ScheduledReport
- **File**: `app/Models/ScheduledReport.php`
- **Features**:
  - Tenant isolation
  - JSON fields for metrics, recipients, filters
  - Scopes: `active()`, `due()`
  - Auto-calculate next run date
  - Track execution status

### 4. **Migration**
- **File**: `database/migrations/2026_04_08_000001_create_scheduled_reports_table.php`
- **Table**: `scheduled_reports`
- **Indexes**: tenant_id + is_active, next_run + is_active

### 5. **Console Command**
- **File**: `app/Console/Commands/ProcessScheduledReports.php`
- **Command**: `php artisan reports:process-scheduled`
- **Schedule**: Every hour (configurable)
- **Features**:
  - Auto-generate reports based on schedule
  - Send via email to recipients
  - Track success/failure
  - Error logging

### 6. **Scheduler Integration**
- **File**: `routes/console.php`
- **Schedule**: Hourly execution
- **Protection**: withoutOverlapping(), onOneServer()

---

## 🎯 Key Features Explained

### 1. Real-time KPI Tracking

#### Revenue Metrics
```php
$daily = Invoice::whereBetween('invoice_date', [$start, $end])->sum('total_amount');
$weekly = Invoice::whereBetween('invoice_date', [now()->subDays(7), now()])->sum('total_amount');
$monthly = Invoice::whereBetween('invoice_date', [now()->subDays(30), now()])->sum('total_amount');
$growth = (($current - $previous) / $previous) * 100;
```

#### Order Metrics
```php
$total_orders = SalesOrder::whereBetween('order_date', [$start, $end])->count();
$completed = SalesOrder::where('status', 'completed')->count();
$conversion_rate = ($completed / $total_orders) * 100;
$avg_value = SalesOrder::avg('total_amount');
```

#### Inventory Metrics
```php
$turnover_rate = COGS / AverageInventory;
$low_stock = ProductStock::where('quantity', '<=', DB::raw('reorder_level'))->count();
$out_of_stock = ProductStock::where('quantity', '<=', 0)->count();
```

#### Customer Metrics
```php
$retention_rate = (CustomersThisPeriod / CustomersLastPeriod) * 100;
$new_customers = Customer::whereMonth('created_at', now()->month)->count();
$active_customers = Customer::whereHas('orders', last_30_days)->count();
```

### 2. AI Predictive Analytics

#### Sales Forecasting (Linear Regression)
```php
// Calculate slope and intercept
$slope = ($n * ΣXY - ΣX * ΣY) / ($n * ΣX² - (ΣX)²);
$intercept = (ΣY - slope * ΣX) / n;

// Forecast future values
$predicted = slope * (n + i) + intercept;
```

**Accuracy**: MAPE (Mean Absolute Percentage Error)
```php
$accuracy = (1 - Σ|actual - predicted| / actual / n) * 100;
```

**AI Enhancement** (Optional with Gemini):
```php
$geminiService = app(GeminiService::class);
$insights = $geminiService->generateText(
    "Analyze sales data and provide 3 key insights",
    temperature: 0.7
);
```

#### Inventory Demand Prediction
```php
$avg_daily_demand = $total_demand_90d / 90;
$predicted_30d = $avg_daily_demand * 30;
$reorder_needed = $predicted_30d > $current_stock;
$recommended_order = max(0, $predicted_30d - $current_stock);
```

#### Customer Churn Prediction
**Risk Scoring Model**:
```php
$risk_score = 0;

// Recency factor
if ($days_since_last_order > 60) $risk_score += 40;
elseif ($days_since_last_order > 30) $risk_score += 20;

// Frequency factor
if ($order_count_90d == 0) $risk_score += 30;
elseif ($order_count_90d < 3) $risk_score += 15;

// Monetary factor
if ($total_spent_90d < 1,000,000) $risk_score += 20;

$risk_level = $risk_score >= 70 ? 'high' : ($risk_score >= 40 ? 'medium' : 'low');
```

### 3. Custom Report Builder

**Metrics Available**:
- Revenue (daily, weekly, monthly, growth)
- Orders (total, completed, conversion, avg value)
- Customers (total, new, active, retention)
- Inventory (turnover, stock levels, stockouts)

**Filters**:
- Date range (custom, 7d, 30d, 90d, YTD)
- Module (sales, inventory, finance, etc.)
- Category
- Status

**Export Formats**:
- PDF (via Laravel DomPDF)
- Excel (via Laravel Excel)
- CSV (native PHP)

### 4. Scheduled Reports

**Create Scheduled Report**:
```php
ScheduledReport::create([
    'tenant_id' => $tenantId,
    'name' => 'Monthly Revenue Report',
    'metrics' => ['revenue', 'orders', 'customers'],
    'frequency' => 'monthly', // daily, weekly, monthly
    'recipients' => ['owner@company.com', 'manager@company.com'],
    'format' => 'pdf',
    'filters' => [
        'date_range' => 'last_30_days',
        'module' => 'sales'
    ],
    'is_active' => true,
    'next_run' => now()->addMonth()->startOfDay(),
]);
```

**Auto-Execution**:
```bash
# Runs every hour via scheduler
php artisan reports:process-scheduled

# Finds all due reports and:
# 1. Generates report data
# 2. Exports to specified format
# 3. Sends email to recipients
# 4. Updates last_run_at and next_run
```

---

## 🚀 How to Use

### Step 1: Run Migration
```bash
php artisan migrate
```

### Step 2: Access Dashboard
Navigate to: `/analytics/advanced`

### Step 3: View Predictive Analytics
Navigate to: `/analytics/predictive?type=sales&horizon=30`

**Available Types**:
- `sales` - Sales forecasting
- `inventory` - Inventory demand prediction
- `churn` - Customer churn prediction

### Step 4: Build Custom Report
Navigate to: `/analytics/report-builder`

**Select**:
- Metrics to include
- Date range
- Filters
- Export format

### Step 5: Schedule Reports
Navigate to: `/analytics/scheduled-reports`

**Create New**:
```
Name: Weekly Sales Summary
Metrics: [revenue, orders, customers]
Frequency: weekly
Recipients: [email1, email2]
Format: pdf
```

---

## 📈 Performance Optimizations

### Caching Strategy
```php
// KPIs cached for 5 minutes
Cache::remember($cacheKey, now()->addMinutes(5), fn() => {...});

// Revenue trend cached for 10 minutes
Cache::remember($cacheKey, now()->addMinutes(10), fn() => {...});

// Top metrics cached for 15 minutes
Cache::remember($cacheKey, now()->addMinutes(15), fn() => {...});

// Predictions cached for 6-12 hours
Cache::remember($cacheKey, now()->addHours(6), fn() => {...});
```

### Query Optimizations
- Eager loading: `->with(['product', 'customer'])`
- Select only needed columns: `->selectRaw('DATE(), SUM()')`
- Group by for aggregation: `->groupBy('date')`
- Database indexes on: `tenant_id`, `is_active`, `next_run`

### AI Enhancement (Optional)
- Gemini API called only if API key is configured
- Cached for 6 hours to minimize API calls
- Graceful fallback if API fails

---

## 🔧 Configuration

### Enable AI Features
```env
GEMINI_API_KEY=your_api_key_here
```

### Email Configuration
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
```

### Cache Driver (Recommended: Redis)
```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## 📊 Sample Output

### Sales Forecast Response
```json
{
  "historical": [
    {"date": "2026-03-01", "revenue": 15000000},
    {"date": "2026-03-02", "revenue": 18000000},
    ...
  ],
  "forecast": [
    {"date": "2026-04-08", "predicted_revenue": 16500000},
    {"date": "2026-04-09", "predicted_revenue": 17200000},
    ...
  ],
  "confidence_interval": {
    "lower": 14500000,
    "upper": 18500000,
    "mean": 16500000,
    "std_dev": 2000000
  },
  "ai_insights": "Sales trending upward with 12% growth...",
  "accuracy": 87.5
}
```

### Inventory Demand Response
```json
{
  "predictions": [
    {
      "product_id": 1,
      "product_name": "Product A",
      "avg_daily_demand": 15.5,
      "predicted_demand_30d": 465,
      "current_stock": 200,
      "reorder_needed": true,
      "recommended_order_qty": 265
    }
  ],
  "total_products_analyzed": 20,
  "products_needing_reorder": 8
}
```

### Churn Prediction Response
```json
{
  "customers": [
    {
      "customer": {...},
      "risk_score": 85,
      "risk_level": "high",
      "days_since_last_order": 75,
      "order_count_90d": 0,
      "total_spent_90d": 0
    }
  ],
  "high_risk_count": 12,
  "medium_risk_count": 25,
  "low_risk_count": 163
}
```

---

## 🎨 Frontend Implementation (TODO)

### Required Views
1. `resources/views/analytics/advanced-dashboard.blade.php`
2. `resources/views/analytics/predictive.blade.php`
3. `resources/views/analytics/report-builder.blade.php`
4. `resources/views/analytics/scheduled-reports.blade.php`
5. `resources/views/analytics/exports/pdf-report.blade.php`

### Charts Library Recommendation
- **ApexCharts** (modern, interactive)
- **Chart.js** (simple, lightweight)

### WebSocket Integration (Real-time)
```javascript
// Listen for real-time updates
Echo.channel(`analytics.${tenantId}`)
    .listen('KpiUpdated', (e) => {
        updateChart(e.kpis);
    });
```

---

## 🧪 Testing

### Test KPI Endpoint
```bash
curl -X GET "http://localhost/analytics/advanced" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

### Test Predictive Analytics
```bash
curl -X GET "http://localhost/analytics/predictive?type=sales&horizon=30" \
  -H "Authorization: Bearer {token}"
```

### Test Scheduled Report Creation
```bash
curl -X POST "http://localhost/analytics/scheduled-reports" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Daily Sales Report",
    "metrics": ["revenue", "orders"],
    "frequency": "daily",
    "recipients": ["test@example.com"],
    "format": "pdf"
  }'
```

### Test Manual Report Processing
```bash
php artisan reports:process-scheduled
```

---

## 📋 Next Steps (Frontend Development)

### Priority 1: Basic Views
- [ ] Create `advanced-dashboard.blade.php` with KPI cards
- [ ] Add revenue trend chart (ApexCharts/Chart.js)
- [ ] Add top products/customers tables
- [ ] Add date range selector

### Priority 2: Predictive Views
- [ ] Create `predictive.blade.php`
- [ ] Add sales forecast chart with confidence interval
- [ ] Add inventory demand table with reorder alerts
- [ ] Add churn risk visualization (risk meter)

### Priority 3: Report Builder
- [ ] Create `report-builder.blade.php`
- [ ] Add drag-and-drop metric selector
- [ ] Add filter panel (date, module, category)
- [ ] Add export buttons (PDF/Excel/CSV)

### Priority 4: Scheduled Reports
- [ ] Create `scheduled-reports.blade.php`
- [ ] Add form to create new schedule
- [ ] Add table of existing schedules
- [ ] Add activate/deactivate toggle
- [ ] Add run history

### Priority 5: Advanced Features
- [ ] WebSocket real-time updates
- [ ] Dashboard customization (save layout)
- [ ] Report templates
- [ ] Compare periods
- [ ] Drill-down analytics

---

## 🎯 Success Metrics

### Performance Targets
- **KPI Load Time**: < 2 seconds (with caching)
- **Revenue Trend**: < 3 seconds
- **Predictions**: < 5 seconds (with AI), < 1 second (cached)
- **Report Generation**: < 10 seconds
- **Cache Hit Rate**: > 80%

### Business Impact
- **Decision Speed**: 50% faster with real-time KPIs
- **Forecast Accuracy**: > 85% (linear regression)
- **Churn Prediction**: Identify 70%+ at-risk customers
- **Inventory Optimization**: Reduce stockouts by 40%
- **Time Saved**: 10+ hours/week with automated reports

---

## 📚 Related Files

### Backend
- `app/Http/Controllers/Analytics/AdvancedAnalyticsDashboardController.php` (NEW)
- `app/Models/ScheduledReport.php` (NEW)
- `app/Console/Commands/ProcessScheduledReports.php` (NEW)
- `database/migrations/2026_04_08_000001_create_scheduled_reports_table.php` (NEW)
- `routes/web.php` (MODIFIED)
- `routes/console.php` (MODIFIED)

### Existing Analytics (Enhanced By This)
- `app/Services/AdvancedAnalyticsService.php`
- `app/Http/Controllers/Analytics/AnalyticsDashboardController.php`
- `resources/views/analytics/dashboard.blade.php`

---

## 💡 Tips & Best Practices

1. **Always use caching** for analytics queries
2. **Index tenant_id** on all analytics tables
3. **Use eager loading** to prevent N+1 queries
4. **Test with large datasets** (>100k records)
5. **Monitor cache hit rates** and adjust TTL
6. **Use Redis** for production (not database driver)
7. **Set up monitoring** for scheduled report failures
8. **Archive old reports** to save storage
9. **Implement rate limiting** on API endpoints
10. **Add request ID tracking** for debugging

---

## 🐛 Troubleshooting

### Issue: Slow KPI Loading
**Solution**: 
- Check if Redis is running
- Increase cache TTL
- Add database indexes

### Issue: Scheduled Reports Not Running
**Solution**:
```bash
# Check scheduler
php artisan schedule:list

# Run manually
php artisan reports:process-scheduled

# Check logs
tail -f storage/logs/laravel.log
```

### Issue: AI Predictions Failing
**Solution**:
- Verify GEMINI_API_KEY is set
- Check API quota
- Review error logs

### Issue: Email Not Sending
**Solution**:
- Test SMTP connection
- Check MAIL_* env variables
- Review mail queue: `php artisan queue:work`

---

## ✅ Checklist

- [x] Controller created (654 lines)
- [x] Routes added (6 endpoints)
- [x] ScheduledReport model created
- [x] Migration created
- [x] Console command created
- [x] Scheduler integration added
- [x] Caching implemented
- [x] Tenant isolation enforced
- [x] Error handling added
- [x] Documentation written
- [ ] Frontend views (TODO)
- [ ] WebSocket integration (TODO)
- [ ] Unit tests (TODO)
- [ ] Load testing (TODO)

---

**Implementation Date**: April 8, 2026  
**Status**: ✅ Backend Complete, 🔄 Frontend Needed  
**Next Action**: Create Blade views with charts
