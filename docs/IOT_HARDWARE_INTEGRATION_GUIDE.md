# IoT & Hardware Integration Guide

## 📋 Overview

Sistem Qalcuity ERP mendukung integrasi dengan berbagai perangkat IoT dan hardware untuk otomasi operasional warehouse, inventory, attendance, dan security.

### Fitur yang Tersedia

| Fitur | Status | Keterangan |
|-------|--------|------------|
| **Smart Scale** | ✅ Implemented | Timbangan digital untuk warehouse (Serial/USB/Bluetooth/Network) |
| **RFID/NFC Tracking** | ✅ Implemented | Tag RFID/NFC untuk tracking asset & inventory |
| **Thermal Printer** | ✅ Implemented | ESC/POS printer untuk receipt & label (sudah ada) |
| **Barcode Scanner** | ⚠️ Partial | Camera-based scanner sudah ada, Bluetooth perlu enhancement |
| **Fingerprint Attendance** | ✅ Implemented | Biometric attendance dengan multi-vendor support (sudah ada) |
| **Face Recognition** | ❌ Not Implemented | Perlu development tambahan |
| **CCTV Integration** | ❌ Not Implemented | Perlu development tambahan |

---

## 1. Smart Scale Integration (Timbangan Digital)

### 🎯 Use Cases
- Goods Receipt: Menimbang barang masuk otomatis
- Stock Opname: Verifikasi stok berdasarkan berat
- Production: Tracking material usage by weight
- Quality Control: Memastikan berat produk sesuai standar

### 🖥️ Supported Vendors
- **Mettler Toledo** - Industrial scales
- **Ohaus** - Precision scales
- **CAS** - Commercial scales
- **A&D Weighing** - Laboratory scales
- **Generic** - Any scale with serial/network output

### 🔌 Connection Types
1. **Serial (RS232)** - Direct COM port connection
2. **USB** - Virtual COM port via USB
3. **Bluetooth** - Wireless serial connection
4. **Network (TCP/IP)** - Ethernet/WiFi scales

### 📦 Installation

#### 1. Run Migrations
```bash
php artisan migrate
```

Ini akan membuat tabel:
- `smart_scales` - Konfigurasi timbangan
- `scale_weigh_logs` - Log penimbangan

#### 2. Install PHP DIO Extension (Optional - untuk Serial/USB)
```bash
# Linux
sudo apt-get install php-dio

# Windows: Uncomment extension di php.ini
extension=dio
```

**Note:** Jika DIO extension tidak tersedia, gunakan Network mode atau middleware Python script.

### ⚙️ Configuration

#### Tambah Timbangan Baru
1. Navigasi ke **Inventory → Smart Scales**
2. Klik **"Tambah Timbangan"**
3. Isi konfigurasi:
   - **Nama**: "Timbangan Gudang A"
   - **Device ID**: "SCALE001" (unique)
   - **Vendor**: Pilih vendor
   - **Connection Type**: serial/usb/bluetooth/network
   - **Port**: 
     - Serial/USB: "COM3" (Windows) atau "/dev/ttyUSB0" (Linux)
     - Network: IP address "192.168.1.100"
   - **Baud Rate**: 9600 (default, sesuaikan dengan device)
   - **Max Capacity**: Kapasitas maksimal dalam gram
   - **Unit**: g/kg/lb/oz

#### Test Koneksi
```javascript
// Via API
const response = await fetch('/api/inventory/smart-scales/{id}/test', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': csrfToken,
    },
});

const result = await response.json();
console.log(result.message); // "Koneksi berhasil! Berat terbaca: 1234.5 g"
```

### 💻 Usage Examples

#### Read Weight
```javascript
// Read current weight from scale
const response = await fetch('/api/inventory/smart-scales/{id}/read-weight', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': csrfToken },
});

const data = await response.json();
if (data.success) {
    console.log(`Weight: ${data.weight} ${data.unit}`);
    console.log(`Stable: ${data.stable}`);
}
```

#### Tare Scale (Set Zero)
```javascript
await fetch('/api/inventory/smart-scales/{id}/tare', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': csrfToken },
});
```

#### Record Weigh Operation
```javascript
// Record weigh for goods receipt
const response = await fetch('/api/inventory/smart-scales/weigh', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
    },
    body: JSON.stringify({
        scale_id: 1,
        product_id: 123,
        warehouse_id: 1,
        weight: 1500.5,
        tare_weight: 50.0,
        unit: 'g',
        reference_type: 'goods_receipt',
        reference_id: 456,
    }),
});

const result = await response.json();
console.log(`Net weight: ${result.net_weight}g`);
```

### 🔧 Advanced: Custom Protocol Parsing

Untuk vendor dengan format data custom, extend `SmartScaleService`:

```php
class CustomScaleService extends SmartScaleService
{
    protected function parseWeightData(string $data, string $vendor): array
    {
        // Custom parsing logic for specific vendor
        if ($vendor === 'custom_vendor') {
            // Parse custom format
            preg_match('/WEIGHT:(\d+\.\d+)/', $data, $matches);
            return [
                'weight' => floatval($matches[1]),
                'unit' => 'g',
                'stable' => true,
                'raw_data' => $data,
            ];
        }
        
        return parent::parseWeightData($data, $vendor);
    }
}
```

---

## 2. RFID/NFC Tracking System

### 🎯 Use Cases
- **Asset Tracking**: Track lokasi dan movement aset
- **Inventory Management**: Scan inventory items cepat
- **Warehouse Operations**: Check-in/check-out barang
- **Access Control**: NFC card untuk akses area tertentu

### 🏷️ Tag Types
- **RFID LF (125 kHz)** - Jarak pendek, anti-metal
- **RFID HF (13.56 MHz)** - ISO14443, Mifare, jarak menengah
- **RFID UHF (860-960 MHz)** - Jarak jauh (hingga 10m), bulk scanning
- **NFC** - Near Field Communication, smartphone compatible

### 📦 Installation

#### Run Migrations
```bash
php artisan migrate
```

Tabel yang dibuat:
- `rfid_tags` - Data tag RFID/NFC
- `rfid_scan_logs` - Log scan events
- `rfid_scanner_devices` - Konfigurasi scanner devices

### ⚙️ Setup

#### 1. Register RFID Tag
```php
use App\Models\RfidTag;

$tag = RfidTag::create([
    'tenant_id' => 1,
    'tag_uid' => 'E28011700000020DCBF4A5E6', // UID dari tag
    'tag_type' => 'rfid', // rfid, nfc, barcode_qr
    'frequency' => 'UHF', // LF, HF, UHF
    'protocol' => 'ISO18000-6C', // EPC Gen2
    'status' => 'active',
]);
```

#### 2. Assign Tag to Product/Asset
```php
// Assign ke Product
$product = Product::find(123);
$tag->assignTo($product);

// Assign ke Asset
$asset = Asset::find(456);
$tag->assignTo($asset);
```

#### 3. Register Scanner Device
```php
use App\Models\RfidScannerDevice;

$scanner = RfidScannerDevice::create([
    'tenant_id' => 1,
    'name' => 'Handheld Scanner A',
    'device_id' => 'SCANNER001',
    'vendor' => 'Zebra',
    'model' => 'MC3300',
    'scanner_type' => 'handheld', // handheld, fixed, portal, mobile
    'frequency' => 'UHF',
    'connection_type' => 'bluetooth',
    'is_active' => true,
]);
```

### 💻 Usage Examples

#### Scan RFID Tag
```php
use App\Services\RfidScannerService;

$service = new RfidScannerService();

// Scan tag
$result = $service->scanTag('E28011700000020DCBF4A5E6', [
    'scanner_device_id' => 1,
    'warehouse_id' => 1,
    'location_id' => 12,
    'scan_type' => 'check_in', // check_in, check_out, transfer, audit
    'latitude' => -6.2088,
    'longitude' => 106.8456,
]);

if ($result['success']) {
    echo "Tag ditemukan: " . $result['taggable_type'] . " #" . $result['taggable_id'];
}
```

#### Bulk Scan (UHF Portal)
```php
// Scan multiple tags sekaligus (UHF portal scanner)
$tags = $service->bulkScan([
    'scanner_device_id' => 2,
    'warehouse_id' => 1,
    'scan_type' => 'audit',
]);

foreach ($tags as $tagData) {
    echo "Scanned: {$tagData['tag_uid']} - {$tagData['scanned_at']}\n";
}
```

#### Query Scan History
```php
use App\Models\RfidScanLog;

// Get all scans for a tag
$scans = RfidScanLog::where('tag_id', $tagId)
    ->with(['scannedBy', 'warehouse', 'location'])
    ->orderBy('scan_time', 'desc')
    ->get();

// Get movement history for an asset
$asset = Asset::find(123);
$movementHistory = $asset->rfidTag->scans()
    ->with('warehouse')
    ->orderBy('scan_time', 'desc')
    ->get();
```

### 🔐 Security Features

#### Encoded Data Encryption
Sensitive data pada tag dapat dienkripsi otomatis:

```php
$tag = RfidTag::create([
    'tag_uid' => 'ABC123',
    'encoded_data' => json_encode([
        'owner' => 'John Doe',
        'department' => 'IT',
        'access_level' => 'restricted',
    ]),
    'is_encrypted' => true, // Auto-encrypt menggunakan Laravel encryption
]);
```

---

## 3. Thermal Printer Optimization

### ✅ Sudah Terimplementasi
Lihat dokumentasi lengkap di: [`docs/POS_PRINTER_GUIDE.md`](POS_PRINTER_GUIDE.md)

### 🆕 Enhancements yang Ditambahkan

#### Advanced Configuration
File: `config/pos_printer.php`

```php
return [
    // Multiple printer support
    'printers' => [
        'receipt' => [
            'type' => env('RECEIPT_PRINTER_TYPE', 'usb'),
            'destination' => env('RECEIPT_PRINTER_DESTINATION', 'POS-58'),
            'paper_width' => 80,
        ],
        'kitchen' => [
            'type' => env('KITCHEN_PRINTER_TYPE', 'network'),
            'destination' => env('KITCHEN_PRINTER_DESTINATION', '192.168.1.101'),
            'paper_width' => 80,
        ],
        'barcode_label' => [
            'type' => env('LABEL_PRINTER_TYPE', 'usb'),
            'destination' => env('LABEL_PRINTER_DESTINATION', 'LABEL-PRINTER'),
            'paper_width' => 58,
        ],
    ],
    
    // Print queue settings
    'queue' => [
        'enabled' => true,
        'driver' => 'database',
        'retry_attempts' => 3,
        'retry_delay' => 5, // seconds
    ],
];
```

#### Custom Receipt Templates
```php
use App\Services\PosPrinterService;

$printer = new PosPrinterService();

// Custom template
$printer->setTemplate('restaurant_receipt', [
    'show_logo' => true,
    'show_qr_code' => true,
    'qr_data' => 'https://feedback.restaurant.com/order/123',
    'footer_text' => 'Thank you for dining with us!',
    'show_tax_breakdown' => true,
]);

$printer->printSalesReceipt($orderData);
```

---

## 4. Barcode Scanner Support

### ✅ Camera-Based Scanner (Sudah Ada)
- Component: `resources/views/components/barcode-scanner.blade.php`
- Support: QR code, Code128, EAN-13, UPC-A
- Usage: POS, Manufacturing, Asset management

### 🆕 Bluetooth Scanner Enhancement

#### Web Bluetooth API Integration
```javascript
// Connect to Bluetooth barcode scanner
async function connectBluetoothScanner() {
    try {
        const device = await navigator.bluetooth.requestDevice({
            filters: [{ services: ['000018f0-0000-1000-8000-00805f9b34fb'] }],
        });
        
        const server = await device.gatt.connect();
        const service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
        const characteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');
        
        // Listen for barcode scans
        characteristic.addEventListener('characteristicvaluechanged', (event) => {
            const decoder = new TextDecoder('utf-8');
            const barcode = decoder.decode(event.target.value);
            handleBarcodeScan(barcode);
        });
        
        await characteristic.startNotifications();
        console.log('Bluetooth scanner connected');
    } catch (error) {
        console.error('Connection failed:', error);
    }
}

function handleBarcodeScan(barcode) {
    // Trigger action based on context
    document.dispatchEvent(new CustomEvent('barcode-scanned', {
        detail: { barcode }
    }));
}
```

#### HID Mode (Plug & Play)
Sebagian besar Bluetooth scanner bekerja dalam HID mode dan langsung terdeteksi sebagai keyboard input:

```javascript
// Auto-detect barcode scanner input
let barcodeBuffer = '';
let lastKeyTime = 0;

document.addEventListener('keydown', (e) => {
    const currentTime = new Date().getTime();
    
    // If time between keystrokes is very short (< 50ms), likely from scanner
    if (currentTime - lastKeyTime < 50) {
        if (e.key === 'Enter') {
            // End of barcode
            if (barcodeBuffer.length > 0) {
                handleBarcodeScan(barcodeBuffer);
                barcodeBuffer = '';
            }
        } else {
            barcodeBuffer += e.key;
        }
    } else {
        // Reset buffer if typing normally
        barcodeBuffer = '';
    }
    
    lastKeyTime = currentTime;
});
```

---

## 5. Fingerprint/Face Recognition Attendance

### ✅ Fingerprint (Sudah Ada)
Lihat dokumentasi lengkap di: [`docs/FINGERPRINT_ATTENDANCE_GUIDE.md`](FINGERPRINT_ATTENDANCE_GUIDE.md)

### 🆕 Face Recognition Enhancement (TODO)

#### Planned Features
- Face detection & recognition using OpenCV/DLib
- Integration dengan camera CCTV atau webcam
- Liveness detection untuk mencegah spoofing
- Multi-face detection untuk group attendance

#### Implementation Approach
```python
# Python middleware untuk face recognition (akan dijalankan sebagai separate service)
import cv2
import face_recognition
import requests

def recognize_face(image_path, known_faces):
    """Recognize face from image"""
    unknown_image = face_recognition.load_image_file(image_path)
    unknown_encoding = face_recognition.face_encodings(unknown_image)[0]
    
    results = face_recognition.compare_faces(known_faces, unknown_encoding)
    
    if True in results:
        employee_id = known_faces[results.index(True)]['employee_id']
        return {'success': True, 'employee_id': employee_id}
    else:
        return {'success': False, 'message': 'Face not recognized'}

# Flask API endpoint
@app.route('/api/face-recognition/scan', methods=['POST'])
def scan_face():
    image = request.files['image']
    image.save('temp.jpg')
    
    result = recognize_face('temp.jpg', load_known_faces())
    return jsonify(result)
```

---

## 6. CCTV Integration (TODO)

### Planned Features
- Live streaming dari kamera CCTV
- Motion detection alerts
- Recording & playback
- Integration dengan access control
- AI-powered anomaly detection

### Architecture
```
┌──────────────┐
│ CCTV Cameras │ (RTSP stream)
└──────┬───────┘
       │
       ▼
┌──────────────────┐
│ Media Server     │ (FFmpeg + HLS/DASH)
│ - Stream relay   │
│ - Recording      │
└──────┬───────────┘
       │
       ▼
┌──────────────────┐
│ Laravel Backend  │
│ - Access control │
│ - Event logging  │
│ - Alerts         │
└──────┬───────────┘
       │
       ▼
┌──────────────────┐
│ Frontend UI      │
│ - Live view      │
│ - Playback       │
│ - Alerts panel   │
└──────────────────┘
```

### Implementation Steps (Future)
1. Install FFmpeg untuk stream processing
2. Setup RTSP to HLS converter
3. Create CCTV management module
4. Integrate motion detection (OpenCV)
5. Build live streaming player (HLS.js)

---

## 🔧 Troubleshooting

### Smart Scale Issues

**Problem:** Cannot connect to serial scale
```
Solution:
1. Verify COM port is correct (Check Device Manager on Windows)
2. Ensure no other application is using the port
3. Try different baud rate (common: 9600, 19200, 38400)
4. Install proper drivers for USB-to-serial adapter
```

**Problem:** Weight readings are unstable
```
Solution:
1. Ensure scale is on stable, level surface
2. Wait for stability indicator before reading
3. Check for vibrations or air currents
4. Calibrate scale according to manufacturer instructions
```

### RFID/NFC Issues

**Problem:** Tag not detected
```
Solution:
1. Check tag frequency matches scanner (LF/HF/UHF)
2. Ensure tag is within range (varies by frequency)
3. Check for metal interference (use anti-metal tags)
4. Verify tag is not damaged
```

**Problem:** Multiple tags read simultaneously (collision)
```
Solution:
1. Use anti-collision protocol (built-in for UHF)
2. Reduce scanner power/distance
3. Implement singulation algorithm
4. Space tags further apart
```

---

## 📊 Monitoring & Reporting

### Dashboard Metrics
- Total active devices (scales, scanners, printers)
- Daily scan/weigh operations
- Device health status
- Error rates & alerts

### Reports
- Weigh logs by date/product/warehouse
- RFID movement history
- Device utilization statistics
- Maintenance schedules

---

## 🔐 Security Best Practices

1. **Network Isolation**: Pisahkan network IoT devices dari network utama
2. **Authentication**: Gunakan API keys untuk device communication
3. **Encryption**: Encrypt sensitive data pada RFID tags
4. **Access Control**: Role-based access untuk device management
5. **Audit Logs**: Log semua device operations untuk compliance

---

## 📞 Support

Untuk bantuan teknis:
1. Cek log di `storage/logs/laravel.log`
2. Verifikasi device configuration
3. Test connection via UI
4. Hubungi support dengan menyertakan:
   - Device vendor & model
   - Error messages dari logs
   - Screenshot configuration

---

**Version:** 1.0.0  
**Last Updated:** April 5, 2026
