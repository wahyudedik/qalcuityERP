<x-app-layout>

<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('iot.devices.index') }}" class="text-gray-500 no-underline">
            <i class="fas fa-argrid grid-cols-1 md:grid-cols-2 gap-6-left mr-1"></i> Kembali
        </a>
        <h4 class="mt-2 mb-0">Tambah Device IoT</h4>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="col-lg-7">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm">
                <div class="p-5">
                    <form action="{{ route('iot.devices.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label font-semibold">Nama Device <span class="text-red-600">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" placeholder="cth: Sensor Gudang A, ESP32 Kolam 1">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 g-3 mb-3">
                            <div class="w-full md:w-1/2">
                                <label class="form-label font-semibold">Tipe Device <span class="text-red-600">*</span></label>
                                <select name="device_type" class="form-select @error('device_type') is-invalid @enderror">
                                    @foreach($deviceTypes ?? [] as $val => $label)
                                        <option value="{{ $val }}" {{ old('device_type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('device_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="form-label font-semibold">Target Module <span class="text-red-600">*</span></label>
                                <select name="target_module" class="form-select @error('target_module') is-invalid @enderror">
                                    @foreach($targetModules ?? [] as $val => $label)
                                        <option value="{{ $val }}" {{ old('target_module') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('target_module')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-semibold">Lokasi Fisik</label>
                            <input type="text" name="location" class="form-control"
                                value="{{ old('location') }}" placeholder="cth: Gudang A Lantai 2, Kolam Ikan No.3">
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-semibold">Tipe Sensor</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 g-2">
                                @foreach($sensorTypes ?? [] as $val => $label)
                                <div class="w-full md:w-1/3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="sensor_types[]"
                                            value="{{ $val }}" id="sensor_{{ $val }}"
                                            {{ in_array($val, old('sensor_types', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="sensor_{{ $val }}">{{ $label }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-semibold">Versi Firmware</label>
                            <input type="text" name="firmware_version" class="form-control"
                                value="{{ old('firmware_version') }}" placeholder="cth: v1.0.0">
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-semibold">Catatan</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Device Aktif</label>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">Simpan Device</button>
                            <a href="{{ route('iot.devices.index') }}" class="px-4 py-2 border border-gray-400 text-gray-600 hover:bg-gray-50 rounded-xl text-sm transition">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="bg-gray-900 text-gray-100 rounded-2xl border border-gray-700">
                <div class="px-5 py-4 border-b border-gray-700 bg-gray-800">
                    <i class="fas fa-code mr-1"></i> Contoh Kode Firmware
                </div>
                <div class="p-5">
                    <ul class="nav nav-tabs nav-tabs-dark mb-3" id="firmwareTabs">
                        <li class="nav-item"><a class="text-gray-600 hover:text-gray-900 active text-gray-100" data-bs-toggle="tab" href="#tab-esp32">ESP32</a></li>
                        <li class="nav-item"><a class="text-gray-600 hover:text-gray-900 text-gray-100" data-bs-toggle="tab" href="#tab-arduino">Arduino</a></li>
                        <li class="nav-item"><a class="text-gray-600 hover:text-gray-900 text-gray-100" data-bs-toggle="tab" href="#tab-rpi">Raspberry Pi</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-esp32">
<pre class="text-emerald-600 text-sm mb-0"><code>// ESP32 — kirim sensor ke ERP
#include &lt;WiFi.h&gt;
#include &lt;HTTPClient.h&gt;
#include &lt;ArduinoJson.h&gt;

const char* DEVICE_TOKEN = "YOUR_TOKEN_HERE";
const char* ERP_URL =
  "{{ url('/api/webhooks/iot/telemetry') }}";

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
<pre class="text-emerald-600 text-sm mb-0"><code>// Arduino + ESP8266 Shield
#include &lt;ESP8266WiFi.h&gt;
#include &lt;ESP8266HTTPClient.h&gt;

const char* TOKEN = "YOUR_TOKEN_HERE";

void sendCounter(int count) {
  HTTPClient http;
  WiFiClient client;
  http.begin(client,
    "http://{{ request()->getHost() }}"
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
<pre class="text-emerald-600 text-sm mb-0"><code># Raspberry Pi — Python
import requests, json
from datetime import datetime

TOKEN = "YOUR_TOKEN_HERE"
URL = "{{ url('/api/webhooks/iot/telemetry') }}"

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
            <div class="alert alert-info mt-3 text-sm">
                <i class="fas fa-info-circle mr-1"></i>
                Token akan ditampilkan setelah device disimpan. Salin dan masukkan ke firmware sebelum menutup halaman.
            </div>
        </div>
    </div>
</div>
</x-app-layout>
