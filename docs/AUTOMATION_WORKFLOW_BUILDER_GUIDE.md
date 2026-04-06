# Automation & Workflow Builder - Complete Guide

## Overview

Sistem Automation & Workflow Builder memungkinkan Anda membuat otomatisasi bisnis tanpa coding (no-code) dengan trigger dan action yang dapat dikustomisasi.

## Features

### ✅ Implemented Features

1. **Event-Driven Triggers**
   - Inventory events (stock low, stock updated)
   - Sales events (order completed, payment received)
   - Invoice events (overdue, paid, created)
   - Custom events via API

2. **Scheduled Triggers**
   - Every minute
   - Hourly
   - Daily (specific time)
   - Weekly (specific day)
   - Monthly (specific date)

3. **Predefined Actions**
   - `create_purchase_order` - Auto-create PO when stock low
   - `send_whatsapp` - Send WhatsApp notifications
   - `send_email` - Send email notifications
   - `send_notification` - In-app notifications
   - `calculate_bonus` - Employee bonus calculation
   - `update_inventory` - Stock adjustments
   - `create_invoice` - Auto-invoice generation
   - `webhook_call` - External API calls
   - `update_record` - Generic record updates

4. **Condition Engine**
   - Comparison operators: `<`, `<=`, `>`, `>=`, `=`, `!=`
   - String matching: `contains`
   - Dynamic field evaluation from context

5. **Execution Tracking**
   - Full execution logs with status
   - Duration tracking (milliseconds)
   - Error message capture
   - Context data storage

---

## Architecture

```
┌─────────────────────┐
│  Trigger Source     │
│  - Event            │
│  - Schedule         │
│  - Condition        │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Workflow Engine    │
│  - Match triggers   │
│  - Execute actions  │
│  - Log results      │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Workflow Actions   │
│  1. Create PO       │
│  2. Send WA         │
│  3. Calculate Bonus │
│  ...                │
└─────────────────────┘
```

---

## Database Schema

### Tables Created

1. **workflows**
   - `id`, `tenant_id`, `name`, `description`
   - `trigger_type` (event/schedule/condition)
   - `trigger_config` (JSON)
   - `is_active`, `priority`
   - `execution_count`, `last_executed_at`
   - `created_by`, timestamps

2. **workflow_actions**
   - `id`, `tenant_id`, `workflow_id`
   - `action_type` (create_po, send_whatsapp, etc)
   - `action_config` (JSON)
   - `order`, `condition` (JSON, optional)
   - `is_active`, timestamps

3. **workflow_execution_logs**
   - `id`, `tenant_id`, `workflow_id`
   - `triggered_by`, `context_data` (JSON)
   - `status` (running/success/failed)
   - `error_message`
   - `started_at`, `completed_at`, `duration_ms`
   - timestamps

---

## Usage Examples

### 1. Auto-Create PO When Stock < Minimum

**Trigger**: Event-based (`inventory.stock_low`)

**Configuration**:
```json
{
  "trigger_type": "event",
  "trigger_config": {
    "event": "inventory.stock_low"
  }
}
```

**Actions**:
1. Create Purchase Order
   ```json
   {
     "action_type": "create_purchase_order",
     "action_config": {
       "supplier_id": 5,
       "quantity": 100,
       "auto_approve": false
     }
   }
   ```

2. Send Notification
   ```json
   {
     "action_type": "send_notification",
     "action_config": {
       "title": "PO Created Automatically",
       "message_template": "PO {{order_number}} created for {{product_name}}"
     }
   }
   ```

**How to Fire Event**:
```php
use App\Services\WorkflowEngine;

$engine = app(WorkflowEngine::class);

// From Product model observer or service
$engine->fireEvent('inventory.stock_low', [
    'product_id' => $product->id,
    'product_name' => $product->name,
    'stock_quantity' => $product->stock,
    'minimum_stock' => $product->minimum_stock,
]);
```

---

### 2. Invoice Overdue Reminder via WhatsApp

**Trigger**: Scheduled (daily at 9 AM)

**Configuration**:
```json
{
  "trigger_type": "schedule",
  "trigger_config": {
    "schedule": "invoice_overdue_check"
  }
}
```

**Actions**:
1. Send WhatsApp (with condition)
   ```json
   {
     "action_type": "send_whatsapp",
     "action_config": {
       "message_template": "Halo {{customer_name}}, invoice {{invoice_number}} telah jatuh tempo sejak {{due_date}}."
     },
     "condition": {
       "field": "days_overdue",
       "operator": ">=",
       "value": 7
     }
   }
   ```

2. Send Email (backup)
   ```json
   {
     "action_type": "send_email",
     "action_config": {
       "subject": "Payment Reminder - Invoice {{invoice_number}}",
       "message_template": "Dear {{customer_name}}, your invoice is overdue..."
     }
   }
   ```

**Schedule Configuration** (routes/console.php):
```php
Schedule::call(function () {
    Workflow::where('trigger_type', 'schedule')
        ->where('is_active', true)
        ->whereJsonContains('trigger_config->schedule', 'invoice_overdue_check')
        ->each(fn($wf) => $wf->execute(['triggered_by' => 'schedule']));
})->dailyAt('09:00');
```

---

### 3. Monthly Sales Bonus Calculator

**Trigger**: Scheduled (1st of every month)

**Configuration**:
```json
{
  "trigger_type": "schedule",
  "trigger_config": {
    "schedule": "monthly_bonus_calculation"
  }
}
```

**Actions**:
1. Calculate Bonus
   ```json
   {
     "action_type": "calculate_bonus",
     "action_config": {
       "target": 50000000,
       "bonus_percentage": 5,
       "max_bonus": 10000000
     }
   }
   ```

2. Notify HR
   ```json
   {
     "action_type": "send_notification",
     "action_config": {
       "title": "Monthly Bonus Calculated",
       "message_template": "Bonus for {{employee_name}}: Rp {{bonus_amount}}"
     }
   }
   ```

3. Notify Employee
   ```json
   {
     "action_type": "send_notification",
     "action_config": {
       "title": "Congratulations! You Earned a Bonus",
       "message_template": "Selamat! Anda mendapatkan bonus Rp {{bonus_amount}}"
     }
   }
   ```

---

## API Endpoints

### Workflows

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/automation` | Dashboard with statistics |
| GET | `/automation/workflows` | List all workflows |
| GET | `/automation/workflows/create` | Create workflow form |
| POST | `/automation/workflows` | Store new workflow |
| GET | `/automation/workflows/{id}` | Show workflow details |
| PUT | `/automation/workflows/{id}` | Update workflow |
| DELETE | `/automation/workflows/{id}` | Delete workflow |
| POST | `/automation/workflows/{id}/test` | Test workflow execution |
| POST | `/automation/workflows/{id}/toggle` | Toggle active status |
| GET | `/automation/workflows/{id}/logs` | View execution logs |

### Actions

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/automation/workflows/{id}/actions` | Add action to workflow |
| PUT | `/automation/actions/{id}` | Update action |
| DELETE | `/automation/actions/{id}` | Delete action |

---

## Console Commands

### Process Scheduled Workflows

Runs every minute to check and execute scheduled workflows:

```bash
php artisan workflows:process-scheduled
```

This command is automatically scheduled in `routes/console.php`:

```php
Schedule::command('workflows:process-scheduled')->everyMinute();
```

---

## Integration Points

### Hook into Existing Events

Add these event fires to your existing code:

#### 1. Inventory Stock Updates

In `Product` model observer or service:

```php
use App\Services\WorkflowEngine;

public function stockUpdated(Product $product, int $oldStock, int $newStock)
{
    $engine = app(WorkflowEngine::class);
    
    // Check if stock is below minimum
    if ($newStock < $product->minimum_stock) {
        $engine->fireEvent('inventory.stock_low', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'stock_quantity' => $newStock,
            'minimum_stock' => $product->minimum_stock,
            'warehouse_id' => $product->warehouse_id ?? null,
        ]);
    }
    
    // General stock update event
    $engine->fireEvent('inventory.stock_updated', [
        'product_id' => $product->id,
        'old_stock' => $oldStock,
        'new_stock' => $newStock,
        'change' => $newStock - $oldStock,
    ]);
}
```

#### 2. Sales Order Completed

In `SalesOrder` service:

```php
public function completeOrder(SalesOrder $order)
{
    // ... order completion logic ...
    
    $engine = app(WorkflowEngine::class);
    $engine->fireEvent('sales.order_completed', [
        'order_id' => $order->id,
        'order_number' => $order->order_number,
        'customer_id' => $order->customer_id,
        'total_amount' => $order->total,
        'employee_id' => $order->sales_rep_id ?? null,
        'sales_amount' => $order->total,
    ]);
}
```

#### 3. Invoice Overdue Detection

In scheduled task or invoice service:

```php
public function checkOverdueInvoices()
{
    $overdueInvoices = Invoice::where('status', '!=', 'paid')
        ->where('due_date', '<', now()->subDays(7))
        ->get();
    
    $engine = app(WorkflowEngine::class);
    
    foreach ($overdueInvoices as $invoice) {
        $engine->fireEvent('invoice.overdue', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'customer_id' => $invoice->customer_id,
            'customer_name' => $invoice->customer->name,
            'amount' => $invoice->total,
            'due_date' => $invoice->due_date->format('Y-m-d'),
            'days_overdue' => now()->diffInDays($invoice->due_date),
            'phone' => $invoice->customer->phone ?? null,
            'email' => $invoice->customer->email ?? null,
        ]);
    }
}
```

---

## Creating Custom Actions

To add a new action type, extend `WorkflowAction` model:

```php
// In WorkflowAction.php, add to match statement:
match ($this->action_type) {
    // ... existing actions ...
    'send_sms' => $this->sendSMS($context),
    'create_task' => $this->createTask($context),
    default => throw new \Exception("Unknown action type"),
}

// Then implement the method:
private function sendSMS(array $context): array
{
    $phoneNumber = $this->action_config['phone'] ?? null;
    $message = $this->buildMessage($context);
    
    // Integrate with SMS provider (Twilio, Nexmo, etc)
    $response = Http::post('https://api.sms-provider.com/send', [
        'to' => $phoneNumber,
        'message' => $message,
    ]);
    
    return ['sent' => true, 'response' => $response->json()];
}
```

---

## Testing Workflows

### Manual Test via Controller

```bash
curl -X POST http://qalcuityerp.test/automation/workflows/1/test \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 1,
    "stock_quantity": 5,
    "minimum_stock": 10
  }'
```

### Test via Tinker

```php
php artisan tinker

$workflow = \App\Models\Workflow::find(1);
$result = $workflow->execute([
    'product_id' => 1,
    'stock_quantity' => 5,
    'minimum_stock' => 10,
]);

// Check execution logs
$logs = $workflow->logs()->latest()->take(5)->get();
```

---

## Monitoring & Debugging

### View Execution Logs

```php
// Get recent failed executions
$failedLogs = \App\Models\WorkflowExecutionLog::where('status', 'failed')
    ->whereDate('started_at', today())
    ->with('workflow')
    ->get();

foreach ($failedLogs as $log) {
    echo "Workflow: {$log->workflow->name}\n";
    echo "Error: {$log->error_message}\n";
    echo "Context: " . json_encode($log->context_data) . "\n\n";
}
```

### Statistics Dashboard

Access via: `/automation`

Shows:
- Total workflows
- Active workflows
- Today's executions
- Success rate percentage

---

## Performance Considerations

1. **Priority System**: Higher priority workflows execute first
2. **Async Execution**: For heavy actions, consider queuing:
   ```php
   WorkflowAction::dispatch($action, $context)->onQueue('workflows');
   ```

3. **Rate Limiting**: Avoid infinite loops by checking execution count:
   ```php
   if ($workflow->execution_count > 1000) {
       $workflow->update(['is_active' => false]);
   }
   ```

4. **Log Retention**: Clean old logs periodically:
   ```php
   WorkflowExecutionLog::where('started_at', '<', now()->subDays(30))->delete();
   ```

---

## Security

- All workflows are tenant-scoped (`tenant_id`)
- Only admin/manager roles can create/edit workflows
- Actions validate permissions before execution
- Sensitive data in `action_config` should be encrypted if needed

---

## Future Enhancements

Potential additions:
- [ ] Visual workflow builder (drag-and-drop UI)
- [ ] More action types (Slack, Telegram, SMS)
- [ ] Advanced conditions (AND/OR logic)
- [ ] Workflow templates marketplace
- [ ] A/B testing for workflows
- [ ] Workflow versioning
- [ ] Rollback on failure
- [ ] Webhook listener endpoints
- [ ] Real-time execution monitoring (WebSockets)

---

## Troubleshooting

### Workflow Not Executing

1. Check if workflow is active: `$workflow->is_active`
2. Verify trigger configuration matches event name
3. Check execution logs for errors
4. Ensure schedule is correct (for scheduled workflows)

### Action Failing

1. Review `error_message` in execution log
2. Verify `action_config` has required fields
3. Check if external services are accessible (WhatsApp API, email SMTP)
4. Test action individually with test mode

### Performance Issues

1. Check number of workflows per tenant
2. Review execution duration in logs
3. Consider moving heavy actions to queue jobs
4. Optimize database queries in action implementations

---

## Support

For issues or feature requests, check:
- Laravel logs: `storage/logs/laravel.log`
- Execution logs: Database table `workflow_execution_logs`
- Queue logs (if using queues): `storage/logs/laravel-worker.log`
