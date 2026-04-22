

<?php $__env->startSection('title', 'Tambah Device IoT'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="mb-4">
        <a href="<?php echo e(route('iot.devices.index')); ?>" class="text-muted text-decoration-none">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
        <h4 class="mt-2 mb-0">Tambah Device IoT</h4>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="<?php echo e(route('iot.devices.store')); ?>" method="POST">
                        <?php echo csrf_field(); ?>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Device <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                value="<?php echo e(old('name')); ?>" placeholder="cth: Sensor Gudang A, ESP32 Kolam 1">
                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tipe Device <span class="text-danger">*</span></label>
                                <select name="device_type" class="form-select <?php $__errorArgs = ['device_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <?php $__currentLoopData = $deviceTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($val); ?>" <?php echo e(old('device_type') == $val ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['device_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Target Module <span class="text-danger">*</span></label>
                                <select name="target_module" class="form-select <?php $__errorArgs = ['target_module'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <?php $__currentLoopData = $targetModules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($val); ?>" <?php echo e(old('target_module') == $val ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['target_module'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Lokasi Fisik</label>
                            <input type="text" name="location" class="form-control"
                                value="<?php echo e(old('location')); ?>" placeholder="cth: Gudang A Lantai 2, Kolam Ikan No.3">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tipe Sensor</label>
                            <div class="row g-2">
                                <?php $__currentLoopData = $sensorTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="sensor_types[]"
                                            value="<?php echo e($val); ?>" id="sensor_<?php echo e($val); ?>"
                                            <?php echo e(in_array($val, old('sensor_types', [])) ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="sensor_<?php echo e($val); ?>"><?php echo e($label); ?></label>
                                    </div>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Versi Firmware</label>
                            <input type="text" name="firmware_version" class="form-control"
                                value="<?php echo e(old('firmware_version')); ?>" placeholder="cth: v1.0.0">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Catatan</label>
                            <textarea name="notes" class="form-control" rows="2"><?php echo e(old('notes')); ?></textarea>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                <?php echo e(old('is_active', true) ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="is_active">Device Aktif</label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Simpan Device</button>
                            <a href="<?php echo e(route('iot.devices.index')); ?>" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm bg-dark text-light">
                <div class="card-header bg-dark border-secondary">
                    <i class="fas fa-code me-1"></i> Contoh Kode Firmware
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-dark mb-3" id="firmwareTabs">
                        <li class="nav-item"><a class="nav-link active text-light" data-bs-toggle="tab" href="#tab-esp32">ESP32</a></li>
                        <li class="nav-item"><a class="nav-link text-light" data-bs-toggle="tab" href="#tab-arduino">Arduino</a></li>
                        <li class="nav-item"><a class="nav-link text-light" data-bs-toggle="tab" href="#tab-rpi">Raspberry Pi</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-esp32">
<pre class="text-success small mb-0"><code>// ESP32 — kirim sensor ke ERP
#include &lt;WiFi.h&gt;
#include &lt;HTTPClient.h&gt;
#include &lt;ArduinoJson.h&gt;

const char* DEVICE_TOKEN = "YOUR_TOKEN_HERE";
const char* ERP_URL =
  "<?php echo e(url('/api/webhooks/iot/telemetry')); ?>";

void sendTelemetry(float temp, float hum) {
  HTTPClient http;
  http.begin(ERP_URL);
  http.addHeader("Content-Type","application/json");
  http.addHeader("X-Device-Token", DEVICE_TOKEN);

  StaticJsonDocument&lt;256&gt; doc;
  JsonArray sensors = doc.createNestedArray("sensors");

  JsonObject s1 = sensors.createNestedObject();
  s1["type"] = "temperature";
  s1["value"] = temp;
  s1["unit"] = "C";

  JsonObject s2 = sensors.createNestedObject();
  s2["type"] = "humidity";
  s2["value"] = hum;
  s2["unit"] = "%";

  String body;
  serializeJson(doc, body);
  http.POST(body);
  http.end();
}</code></pre>
                        </div>
                        <div class="tab-pane fade" id="tab-arduino">
<pre class="text-success small mb-0"><code>// Arduino + ESP8266 Shield
#include &lt;ESP8266WiFi.h&gt;
#include &lt;ESP8266HTTPClient.h&gt;

const char* TOKEN = "YOUR_TOKEN_HERE";

void sendCounter(int count) {
  HTTPClient http;
  WiFiClient client;
  http.begin(client,
    "http://<?php echo e(request()->getHost()); ?>"
    "/api/webhooks/iot/telemetry");
  http.addHeader("Content-Type","application/json");
  http.addHeader("X-Device-Token", TOKEN);

  String body = "{\"sensors\":[{"
    "\"type\":\"counter\","
    "\"value\":" + String(count) + ","
    "\"unit\":\"pcs\"}]}";

  http.POST(body);
  http.end();
}</code></pre>
                        </div>
                        <div class="tab-pane fade" id="tab-rpi">
<pre class="text-success small mb-0"><code># Raspberry Pi — Python
import requests, json
from datetime import datetime

TOKEN = "YOUR_TOKEN_HERE"
URL = "<?php echo e(url('/api/webhooks/iot/telemetry')); ?>"

def send_telemetry(sensors: list):
    resp = requests.post(URL,
        headers={
            "X-Device-Token": TOKEN,
            "Content-Type": "application/json"
        },
        json={
            "sensors": sensors,
            "recorded_at": datetime.utcnow()
                .isoformat() + "Z"
        }
    )
    return resp.json()

# Contoh penggunaan:
send_telemetry([
    {"type": "temperature", "value": 28.5, "unit": "C"},
    {"type": "humidity",    "value": 72.0, "unit": "%"},
    {"type": "ph",          "value": 7.2,  "unit": "pH"},
])</code></pre>
                        </div>
                    </div>
                </div>
            </div>
            <div class="alert alert-info mt-3 small">
                <i class="fas fa-info-circle me-1"></i>
                Token akan ditampilkan setelah device disimpan. Salin dan masukkan ke firmware sebelum menutup halaman.
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\iot\devices\create.blade.php ENDPATH**/ ?>