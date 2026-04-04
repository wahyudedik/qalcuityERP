# Telecom Module - Admin User Guide

## Table of Contents

1. [Getting Started](#getting-started)
2. [Device Management](#device-management)
3. [Package Management](#package-management)
4. [Subscription Management](#subscription-management)
5. [Customer Portal](#customer-portal)
6. [Voucher Management](#voucher-management)
7. [Monitoring Dashboard](#monitoring-dashboard)
8. [Reports & Analytics](#reports--analytics)
9. [Billing Integration](#billing-integration)
10. [Troubleshooting](#troubleshooting)

---

## Getting Started

### Accessing Telecom Module

1. Login to QalcuityERP
2. Navigate to **Telecom** in the main menu
3. You'll see the dashboard with overview metrics

### First-Time Setup

**Step 1: Add Your First Router**
- Go to **Devices → Add Device**
- Fill in router details (IP, credentials)
- Click **Test Connection** to verify
- Save device

**Step 2: Create Internet Packages**
- Go to **Packages → Create Package**
- Define speed, quota, and pricing
- Activate package

**Step 3: Register Customers**
- Go to **Customers → Add Customer**
- Link customer to subscription
- Assign package

---

## Device Management

### Adding a New Device

1. Navigate to **Telecom → Devices**
2. Click **+ Add Device**
3. Fill in form:
   - **Name**: Descriptive name (e.g., "Main Office Router")
   - **Brand**: Select from dropdown (MikroTik, Ubiquiti, etc.)
   - **Device Type**: Router, Switch, Access Point
   - **IP Address**: Router's management IP
   - **Port**: API port (default: 8728 for MikroTik)
   - **Username**: API username
   - **Password**: API password (encrypted)
   - **Location**: Physical location
   - **Notes**: Additional information
4. Click **Test Connection** to verify
5. Click **Save**

### Viewing Device Status

**Device List View:**
- Shows all devices with status indicators
- 🟢 Green = Online
- 🔴 Red = Offline
- 🟡 Yellow = Pending

**Device Detail View:**
- Click on any device to see details
- View real-time statistics:
  - CPU usage
  - Memory usage
  - Active users
  - Bandwidth utilization
  - Uptime

### Editing Device

1. Go to **Devices**
2. Click **Edit** on desired device
3. Update information
4. Click **Save**

### Deleting Device

⚠️ **Warning**: Deleting a device will remove all associated data.

1. Go to **Devices**
2. Click **Delete** on desired device
3. Confirm deletion

---

## Package Management

### Creating Internet Package

1. Navigate to **Telecom → Packages**
2. Click **+ Create Package**
3. Fill in details:
   - **Name**: Package name (e.g., "Premium 50Mbps")
   - **Download Speed**: In Mbps
   - **Upload Speed**: In Mbps
   - **Quota**: Monthly data limit (GB or Unlimited)
   - **Price**: Monthly fee
   - **Billing Cycle**: Monthly/Yearly
   - **Description**: Package features
   - **Active**: Toggle to enable/disable
4. Click **Save**

### Managing Packages

**View All Packages:**
- See list of all packages
- Filter by active/inactive
- Sort by price, speed, or name

**Edit Package:**
- Click **Edit** on package
- Update details
- Note: Changes won't affect existing subscriptions

**Deactivate Package:**
- Toggle **Active** to OFF
- Existing subscribers remain active
- New subscriptions blocked

---

## Subscription Management

### Creating Subscription

1. Go to **Telecom → Subscriptions**
2. Click **+ New Subscription**
3. Select:
   - **Customer**: Choose from customer list
   - **Package**: Select internet package
   - **Device**: Assign to router
   - **Start Date**: When subscription begins
   - **Auto-Renew**: Enable for automatic renewal
4. Click **Create Subscription**
5. System will:
   - Create hotspot user on router
   - Generate first invoice
   - Send welcome email to customer

### Viewing Subscriptions

**Subscription List:**
- All active subscriptions
- Filter by status (Active/Suspended/Cancelled)
- Search by customer name

**Subscription Details:**
- Click on subscription to view:
  - Customer information
  - Package details
  - Current usage
  - Billing history
  - Next billing date

### Suspending Subscription

Reasons to suspend:
- Non-payment
- Customer request
- Policy violation

**How to Suspend:**
1. Open subscription detail
2. Click **Suspend**
3. Enter reason
4. Confirm suspension
5. System will:
   - Disable hotspot user on router
   - Update subscription status
   - Send notification to customer

### Reactivating Subscription

1. Open suspended subscription
2. Click **Reactivate**
3. Confirm reactivation
4. System will:
   - Re-enable hotspot user
   - Update status to Active
   - Send confirmation to customer

### Cancelling Subscription

⚠️ **Warning**: Cancellation is permanent.

1. Open subscription detail
2. Click **Cancel**
3. Enter cancellation reason
4. Set end date
5. Confirm cancellation
6. System will:
   - Remove hotspot user from router
   - Mark subscription as cancelled
   - Generate final invoice
   - Send cancellation confirmation

---

## Customer Portal

### Viewing Customer Usage

1. Go to **Telecom → Customers**
2. Click on customer name
3. View usage dashboard:
   - Current period usage
   - Download/Upload breakdown
   - Quota remaining
   - Usage trend chart
   - Session history

### Customer Self-Service

Customers can access their own portal at:
`https://your-domain.com/customer/telecom/usage`

**Features:**
- View current usage
- Check quota status
- View billing history
- Download invoices
- Purchase additional quota
- Upgrade package

---

## Voucher Management

### Generating Vouchers

Vouchers are prepaid access codes for temporary internet access.

**Single Voucher:**
1. Go to **Telecom → Vouchers**
2. Click **+ Generate Voucher**
3. Select package
4. Set validity (hours/days)
5. Click **Generate**
6. Print or download voucher

**Batch Generation:**
1. Follow steps above
2. Set quantity (up to 1000)
3. Optional: Add batch number
4. Click **Generate Batch**
5. Download Excel/PDF list

### Printing Vouchers

1. Go to **Vouchers**
2. Filter by batch or status
3. Select vouchers to print
4. Click **Print Selected**
5. Choose format:
   - PDF (professional layout)
   - Thermal printer (receipt format)
   - CSV (for custom printing)

### Managing Vouchers

**View Voucher Status:**
- Unused: Available for redemption
- Used: Already redeemed
- Expired: Past validity period
- Revoked: Manually disabled

**Revoke Voucher:**
1. Find voucher in list
2. Click **Revoke**
3. Confirm revocation
4. Voucher becomes invalid immediately

**Extend Validity:**
1. Open voucher detail
2. Click **Extend**
3. Set new expiry date
4. Confirm extension

---

## Monitoring Dashboard

### Accessing Dashboard

Navigate to **Telecom → Dashboard**

### Dashboard Sections

**1. Overview Cards:**
- Total Devices (Online/Offline)
- Active Subscriptions
- Today's Revenue
- Total Data Usage

**2. Real-Time Charts:**
- Bandwidth usage (last 24 hours)
- Active users trend
- Revenue by day
- Top packages by subscription

**3. Network Topology:**
- Visual map of all devices
- Connection lines show relationships
- Color-coded status indicators
- Click device for details

**4. Recent Alerts:**
- Device offline warnings
- Quota exceeded notifications
- Payment failures
- System errors

**5. Quick Actions:**
- Add new device
- Create subscription
- Generate vouchers
- View reports

### Customizing Dashboard

**Date Range Selector:**
- Today
- Last 7 days
- Last 30 days
- Custom range

**Refresh Interval:**
- Auto-refresh every 30 seconds
- Manual refresh button
- Pause auto-refresh

---

## Reports & Analytics

### Available Reports

**1. Revenue by Package**
- Shows revenue breakdown per package
- Percentage contribution
- Subscription count
- Export to Excel

**2. Bandwidth Utilization**
- Daily/weekly/monthly trends
- Peak usage times
- Average consumption
- Capacity planning insights

**3. Customer Usage Analytics**
- Individual customer behavior
- Usage patterns
- Quota utilization rates
- Churn risk indicators

**4. Top Consumers**
- Highest bandwidth users
- Download/upload ratios
- Peak usage periods
- Potential upsell opportunities

### Generating Reports

1. Go to **Telecom → Reports**
2. Select report type
3. Choose date range
4. Apply filters (optional)
5. Click **Generate Report**
6. View on screen or export to Excel

### Exporting Reports

**Excel Export:**
1. Generate report
2. Click **Export to Excel**
3. File downloads automatically
4. Open in Microsoft Excel or LibreOffice

**PDF Export:**
1. Generate report
2. Click **Print** or **Export PDF**
3. Save or print document

---

## Billing Integration

### Automatic Invoice Generation

The system automatically generates invoices for:
- Monthly subscription renewals
- Additional quota purchases
- One-time fees

**Schedule:**
- Runs daily at 00:30 AM
- Checks all subscriptions due for billing
- Creates invoices with 7-day payment terms

### Viewing Invoices

1. Go to **Billing → Invoices**
2. Filter by:
   - Status (Paid/Unpaid/Overdue)
   - Customer
   - Date range
   - Type (Telecom/Other)

### Processing Payments

**Manual Payment Entry:**
1. Open unpaid invoice
2. Click **Record Payment**
3. Enter:
   - Amount paid
   - Payment method
   - Reference number
   - Payment date
4. Click **Process Payment**
5. System will:
   - Update invoice status
   - Reactivate suspended subscriptions
   - Send receipt to customer

**Automatic Payment:**
- Integrated with payment gateways
- Webhook triggers on payment success
- Automatic reconciliation

### Handling Overdue Invoices

**Automatic Actions:**
- Day 1: Reminder email sent
- Day 3: Second reminder + SMS
- Day 7: Subscription suspended
- Day 30: Cancellation warning

**Manual Intervention:**
1. Review overdue list
2. Contact customer
3. Arrange payment plan if needed
4. Extend due date if approved

---

## Troubleshooting

### Common Issues

#### Issue: Device Shows Offline

**Possible Causes:**
- Router powered off
- Network connectivity issue
- Firewall blocking API access
- Incorrect credentials

**Solution:**
1. Check physical router status
2. Ping router IP from server
3. Verify firewall rules
4. Test connection from device detail page
5. Update credentials if changed

---

#### Issue: Hotspot User Not Created

**Possible Causes:**
- Router connection failed
- Insufficient permissions
- Profile doesn't exist on router
- Duplicate username

**Solution:**
1. Check device connection status
2. Verify API user has hotspot write permission
3. Ensure profile exists on router
4. Try different username
5. Check error logs: **Settings → Logs**

---

#### Issue: Usage Data Not Updating

**Possible Causes:**
- Polling job not running
- Router API returning errors
- Database connection issue

**Solution:**
1. Check scheduled jobs: `php artisan schedule:list`
2. Verify queue workers running: `php artisan queue:work`
3. Check router API accessibility
4. Review logs for errors
5. Manually trigger poll: `php artisan telecom:poll-usage`

---

#### Issue: Invoice Not Generated

**Possible Causes:**
- Scheduler not configured
- Queue worker stopped
- Missing billing date

**Solution:**
1. Verify cron job setup
2. Check queue worker status
3. Ensure subscription has next_billing_date
4. Manually run: `php artisan telecom:generate-invoices`
5. Check failed jobs: **Settings → Jobs**

---

### Getting Help

**Documentation:**
- API Docs: `/docs/TELECOM_API_DOCUMENTATION.md`
- Testing Guide: `/docs/TELECOM_TESTING_GUIDE.md`
- MikroTik Setup: `/docs/TELECOM_MIKROTIK_SETUP_GUIDE.md`
- Webhook Guide: `/docs/TELECOM_WEBHOOK_GUIDE.md`

**Support Channels:**
- Email: support@qalcuity.com
- Phone: +62-xxx-xxxx-xxxx
- Live Chat: Available in admin panel
- Community Forum: https://community.qalcuity.com

**Emergency Support:**
- Critical issues: Call emergency hotline
- Response time: < 1 hour
- Available: 24/7

---

## Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl + K` | Quick search |
| `Ctrl + N` | New device/subscription |
| `Ctrl + R` | Refresh data |
| `Esc` | Close modal |
| `?` | Show help |

---

## Best Practices

### 1. Regular Monitoring
- Check dashboard daily
- Review alerts promptly
- Monitor bandwidth trends

### 2. Proactive Maintenance
- Update router firmware regularly
- Backup configurations weekly
- Test failover procedures monthly

### 3. Customer Communication
- Send usage warnings at 80%
- Notify before suspension
- Provide upgrade options

### 4. Security
- Change API passwords quarterly
- Review access logs monthly
- Enable 2FA for admin accounts

### 5. Performance
- Archive old usage data
- Optimize database queries
- Monitor server resources

---

**Last Updated:** April 4, 2026  
**Module Version:** 1.0.0  
**Guide Version:** 1.0.0
