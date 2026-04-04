# POS Thermal Printer Integration Guide

## 📋 Overview

Sistem POS ERP sekarang mendukung **thermal printer** dengan ESC/POS protocol untuk mencetak receipt, kitchen tickets, dan barcode labels. Sistem juga memiliki fallback ke browser printing jika thermal printer tidak tersedia.

---

## 🖨️ Supported Printers

### ESC/POS Compatible Printers:
- **Epson**: TM-T88 series, TM-T20, TM-U220
- **Star Micronics**: TSP100, TSP650, TSP700
- **Bixolon**: SRP-350, SRP-275
- **Zjiang**: ZJ-5890, ZJ-8250
- **Generic**: Any ESC/POS compatible thermal printer

### Connection Types:
1. **USB** - Direct USB connection (Windows)
2. **Network** - Ethernet/WiFi (IP address)
3. **File** - Serial port or parallel port
4. **CUPS** - Linux CUPS printing system

---

## ⚙️ Installation & Setup

### 1. Install Dependencies

Package sudah diinstall via Composer:
```bash
composer require mike42/escpos-php
```

### 2. Run Migrations

```bash
php artisan migrate
```

Ini akan membuat tabel:
- `print_jobs` - Queue untuk print jobs
- `printer_settings` - Konfigurasi printer per tenant

### 3. Configure Environment

Tambahkan ke file `.env`:

```env
# Printer Configuration
POS_PRINTER_TYPE=usb
POS_PRINTER_DESTINATION=POS-58
POS_PAPER_WIDTH=80
POS_PRINTER_AUTO_CONNECT=false

# Receipt Settings
RECEIPT_COMPANY_NAME="Your Restaurant Name"
RECEIPT_ADDRESS="Jl. Example No. 123, Jakarta"
RECEIPT_PHONE=021-12345678
RECEIPT_EMAIL=info@restaurant.com
RECEIPT_FOOTER_TEXT="Thank you for dining with us!"
RECEIPT_TAX_RATE=10
RECEIPT_SERVICE_CHARGE_RATE=5

# Kitchen Printer (Optional)
KITCHEN_PRINTER_ENABLED=true
KITCHEN_PRINTER_TYPE=network
KITCHEN_PRINTER_DESTINATION=192.168.1.101
KITCHEN_PAPER_WIDTH=80

# Barcode Printer (Optional)
BARCODE_PRINTER_ENABLED=false
BARCODE_PRINTER_TYPE=usb
BARCODE_PRINTER_DESTINATION=LABEL-PRINTER

# Print Queue
PRINT_QUEUE_ENABLED=true
PRINT_QUEUE_DRIVER=database
PRINT_QUEUE_RETRY=3
PRINT_QUEUE_RETRY_DELAY=5

# Logging
LOG_PRINT_JOBS=true
PRINT_LOG_LEVEL=info
```

### 4. Configure Queue Worker

Untuk automatic print queue processing, jalankan queue worker:

```bash
php artisan queue:work --queue=default --tries=3
```

Atau gunakan supervisor untuk production:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/worker.log
```

---

## 🔧 Printer Setup

### Option 1: Via API

```javascript
// Save printer settings
const response = await fetch('/api/pos/print/settings', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
    },
    body: JSON.stringify({
        printer_name: 'receipt_printer',
        printer_type: 'usb',
        printer_destination: 'POS-58',
        paper_width: 80,
        is_active: true,
        is_default: true,
    }),
});
```

### Option 2: Via Database

```php
use App\Models\PrinterSetting;

PrinterSetting::create([
    'tenant_id' => 1,
    'printer_name' => 'receipt_printer',
    'printer_type' => 'usb',
    'printer_destination' => 'POS-58',
    'paper_width' => 80,
    'is_active' => true,
    'is_default' => true,
]);
```

---

## 📝 Usage Examples

### 1. Print Sales Receipt

#### Via JavaScript (Frontend):
```javascript
import PosPrinter from '@/js/pos-printer';

const printer = new PosPrinter({
    apiBaseUrl: '/api',
    method: 'auto', // 'thermal', 'browser', or 'auto'
});

// Print receipt after order completion
async function completeOrder(orderId) {
    try {
        const result = await printer.printReceipt({
            id: orderId,
            order_number: 'ORD-20260404-001',
            date: '2026-04-04 14:30:00',
            cashier: 'John Doe',
            items: [
                { name: 'Nasi Goreng', quantity: 2, price: 25000, total: 50000 },
                { name: 'Es Teh Manis', quantity: 2, price: 5000, total: 10000 },
            ],
            subtotal: 60000,
            tax: 6000,
            grand_total: 66000,
            payment_method: 'cash',
        });
        
        if (result.success) {
            alert('Receipt printed successfully!');
        } else {
            alert('Print failed: ' + result.error);
        }
    } catch (error) {
        console.error('Print error:', error);
    }
}
```

#### Via PHP (Backend):
```php
use App\Services\PosPrinterService;
use App\Models\SalesOrder;

$order = SalesOrder::find($orderId);
$printerService = new PosPrinterService();

// Connect to printer
$connected = $printerService->connect('usb', 'POS-58');

if ($connected) {
    // Prepare receipt data
    $receiptData = [
        'company_name' => 'My Restaurant',
        'address' => 'Jl. Example No. 123',
        'phone' => '021-12345678',
        'order_number' => $order->order_number,
        'date' => $order->created_at->format('Y-m-d H:i:s'),
        'cashier' => auth()->user()->name,
        'items' => $order->items->map(fn($item) => [
            'name' => $item->product->name,
            'quantity' => $item->quantity,
            'price' => $item->unit_price,
            'total' => $item->total_price,
        ])->toArray(),
        'subtotal' => $order->subtotal,
        'tax' => $order->tax_amount,
        'grand_total' => $order->grand_total,
        'payment_method' => $order->payment_method,
    ];
    
    // Print
    $result = $printerService->printSalesReceipt($receiptData, 80);
    
    if ($result['success']) {
        return response()->json(['message' => 'Printed successfully']);
    } else {
        return response()->json(['error' => $result['error']], 500);
    }
}
```

### 2. Print Kitchen Ticket

```javascript
// Send order to kitchen printer
const result = await printer.printKitchenTicket(orderId);

if (result.success) {
    console.log('Kitchen ticket sent to printer');
}
```

### 3. Print Barcode Label

```javascript
// Print product barcode label
const result = await printer.printBarcodeLabel(
    'PROD-12345',  // Barcode code
    'Nasi Goreng', // Product name
    25000          // Price
);
```

### 4. Test Printer Connection

```javascript
// Test printer before saving settings
const result = await printer.testPrinter('usb', 'POS-58');

if (result.success) {
    alert('Printer test successful!');
} else {
    alert('Printer test failed: ' + result.error);
}
```

### 5. Monitor Print Queue

```javascript
// Get pending print jobs
const queue = await printer.getPrintQueue('pending');

console.log(`Pending jobs: ${queue.data.total}`);

// Retry failed job
await fetch(`/api/pos/print/queue/${jobId}/retry`, {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': csrfToken,
    },
});
```

---

## 🎨 Customization

### Receipt Template Customization

Edit file `app/Services/PosPrinterService.php` untuk customize receipt layout:

```php
private function printHeader(Printer $p, array $orderData, int $paperWidth): void
{
    // Add logo
    if (config('pos_printer.receipt.show_logo') && file_exists($logoPath)) {
        $img = EscposImage::load($logoPath);
        $p->bitImage($img);
    }
    
    // Customize header text
    $p->setJustification(Printer::JUSTIFY_CENTER);
    $p->setTextSize(2, 2);
    $p->text($orderData['company_name'] . "\n");
    // ... more customization
}
```

### Paper Size Support

Support untuk 2 ukuran kertas:
- **58mm** - Compact receipts
- **80mm** - Standard receipts (recommended)

Set di configuration atau saat print:

```php
$result = $printerService->printSalesReceipt($data, 58); // 58mm paper
```

---

## 🔍 Troubleshooting

### Problem: Printer not connecting

**Solution:**
1. Check printer is powered on and connected
2. Verify printer name in Windows:
   - Open Control Panel → Devices and Printers
   - Note exact printer name (case-sensitive)
3. Test with Windows Notepad first
4. Check printer driver is installed

### Problem: Print job stuck in queue

**Solution:**
```bash
# Check queue status
php artisan queue:monitor

# Restart queue worker
php artisan queue:restart

# Clear failed jobs
php artisan queue:flush
```

### Problem: Garbled text on receipt

**Solution:**
1. Check printer capability profile
2. Try different character encoding
3. Update printer firmware

```php
// In PosPrinterService constructor
$this->profile = CapabilityProfile::load('simple'); // or 'default', 'SP2000', etc.
```

### Problem: QR code not printing

**Solution:**
1. Ensure printer supports QR codes
2. Check QR data length (max ~200 characters)
3. Use smaller QR size:

```php
$printer->qrCode($data, Printer::QR_ECLEVEL_L, 6); // Smaller size
```

---

## 📊 Print Queue Management

### View Queue Dashboard

Access via API:
```bash
GET /api/pos/print/queue?status=pending&limit=50
```

Response:
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "job_type": "receipt",
                "reference_number": "ORD-20260404-001",
                "status": "pending",
                "retry_count": 0,
                "created_at": "2026-04-04T14:30:00.000000Z"
            }
        ],
        "total": 5
    }
}
```

### Retry Failed Jobs

```bash
POST /api/pos/print/queue/{job_id}/retry
```

### Cancel Pending Jobs

```bash
POST /api/pos/print/queue/{job_id}/cancel
```

---

## 🔐 Security

### API Authentication

Semua print endpoints memerlukan authentication via Sanctum:

```javascript
fetch('/api/pos/print/receipt/123', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer YOUR_API_TOKEN',
        'X-CSRF-TOKEN': csrfToken,
    },
});
```

### Tenant Isolation

Print jobs otomatis di-filter berdasarkan `tenant_id` untuk memastikan data isolation.

---

## 🚀 Performance Tips

1. **Use Queue for Production**
   - Prevents blocking UI during print
   - Automatic retry on failure
   - Better error handling

2. **Optimize Print Data**
   - Minimize receipt content
   - Use shorter company names
   - Limit item descriptions

3. **Network Printer Best Practices**
   - Use static IP addresses
   - Ensure stable network connection
   - Set appropriate timeout values

4. **Monitor Queue Health**
   ```bash
   php artisan queue:monitor
   php artisan queue:failed
   ```

---

## 📚 Additional Resources

- [ESC/POS PHP Documentation](https://github.com/mike42/escpos-php)
- [ESC/POS Command Reference](https://www.epson-biz.com/modules/ref_escpos/)
- [Laravel Queue Documentation](https://laravel.com/docs/queues)

---

## ✅ Checklist Implementation

- [x] Install mike42/escpos-php package
- [x] Create PosPrinterService with full ESC/POS support
- [x] Build print queue system with retry logic
- [x] Create controller endpoints for all print operations
- [x] Add JavaScript integration with browser fallback
- [x] Support multiple printer types (USB, Network, File, CUPS)
- [x] Support multiple paper sizes (58mm, 80mm)
- [x] Implement receipt, kitchen ticket, and barcode printing
- [x] Add printer settings management
- [x] Create comprehensive documentation

---

## 🎯 Next Steps

Untuk complete implementation:

1. **Setup printer hardware** sesuai guide di atas
2. **Configure printer settings** via API atau database
3. **Test printer connection** menggunakan endpoint `/api/pos/print/test`
4. **Integrate dengan POS flow** untuk auto-print setelah checkout
5. **Setup queue worker** untuk production environment
6. **Train staff** cara menggunakan printer dan troubleshooting basic

Sistem siap digunakan! 🎉
