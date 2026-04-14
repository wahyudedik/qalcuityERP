/**
 * Qalcuity ERP — ESP32 Firmware Example
 * Kirim data sensor ke ERP secara otomatis setiap interval
 *
 * Library yang dibutuhkan (install via Arduino Library Manager):
 * - ArduinoJson (Benoit Blanchon)
 * - DHT sensor library (Adafruit)
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <DHT.h>

// ── Konfigurasi WiFi ──────────────────────────────────────────
const char* WIFI_SSID     = "NAMA_WIFI_ANDA";
const char* WIFI_PASSWORD = "PASSWORD_WIFI";

// ── Konfigurasi ERP ───────────────────────────────────────────
const char* DEVICE_TOKEN  = "GANTI_DENGAN_TOKEN_DARI_ERP";
const char* ERP_TELEMETRY = "https://erp.domain.com/api/webhooks/iot/telemetry";
const char* ERP_HEARTBEAT = "https://erp.domain.com/api/webhooks/iot/heartbeat";
const char* FIRMWARE_VER  = "v1.0.0";

// ── Konfigurasi Sensor ────────────────────────────────────────
#define DHT_PIN    4
#define DHT_TYPE   DHT22
#define SEND_INTERVAL_MS 30000  // kirim setiap 30 detik

DHT dht(DHT_PIN, DHT_TYPE);

// ── Setup ─────────────────────────────────────────────────────
void setup() {
    Serial.begin(115200);
    dht.begin();

    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
    Serial.print("Connecting to WiFi");
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\nWiFi connected: " + WiFi.localIP().toString());
}

// ── Loop ──────────────────────────────────────────────────────
unsigned long lastSend = 0;

void loop() {
    if (millis() - lastSend >= SEND_INTERVAL_MS) {
        float temperature = dht.readTemperature();
        float humidity    = dht.readHumidity();

        if (!isnan(temperature) && !isnan(humidity)) {
            sendTelemetry(temperature, humidity);
        } else {
            Serial.println("Gagal baca sensor DHT22");
        }

        lastSend = millis();
    }
}

// ── Kirim Telemetry ke ERP ────────────────────────────────────
void sendTelemetry(float temp, float hum) {
    if (WiFi.status() != WL_CONNECTED) return;

    HTTPClient http;
    http.begin(ERP_TELEMETRY);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("X-Device-Token", DEVICE_TOKEN);

    // Build JSON payload
    StaticJsonDocument<512> doc;
    doc["firmware"] = FIRMWARE_VER;

    JsonArray sensors = doc.createNestedArray("sensors");

    JsonObject s1 = sensors.createNestedObject();
    s1["type"]  = "temperature";
    s1["value"] = temp;
    s1["unit"]  = "C";

    JsonObject s2 = sensors.createNestedObject();
    s2["type"]  = "humidity";
    s2["value"] = hum;
    s2["unit"]  = "%";

    String body;
    serializeJson(doc, body);

    int httpCode = http.POST(body);
    if (httpCode == 200) {
        Serial.printf("Terkirim: Suhu=%.1f°C Kelembaban=%.1f%%\n", temp, hum);
    } else {
        Serial.printf("Gagal kirim, HTTP %d\n", httpCode);
    }
    http.end();
}
