# AI Performance & Cost Optimization Guide

## Overview

Dokumen ini menjelaskan implementasi optimasi performa dan biaya untuk sistem AI Chat Qalcuity ERP. Optimasi ini dirancang untuk mengurangi biaya API calls ke Gemini, menurunkan latency, dan meningkatkan user experience.

## Masalah yang Diselesaikan

### Sebelum Optimasi
- ❌ Setiap chat = 1 API call ke Gemini = biaya tinggi
- ❌ Latency tinggi untuk pertanyaan sederhana
- ❌ Tidak ada caching untuk query repetitif
- ❌ User harus menunggu seluruh response selesai sebelum melihat hasil

### Setelah Optimasi
- ✅ Caching layer untuk query repetitif (mengurangi ~40-60% API calls)
- ✅ Rule-based responses untuk operasi sederhana (zero API cost)
- ✅ Batch processing untuk bulk operations
- ✅ Response streaming untuk UX lebih smooth (typewriter effect)

## Arsitektur Optimasi

```
User Message
    ↓
┌─────────────────────────┐
│  Rule-Based Handler     │ ← Cek apakah pertanyaan sederhana
│  (Zero API Cost)        │    Contoh: halo, terima kasih, help
└────────┬────────────────┘
         │ No
         ↓
┌─────────────────────────┐
│  Response Cache Check   │ ← Cek cache (Redis/File/Database)
│  (Fast Lookup)          │    Hit rate target: 40-60%
└────────┬────────────────┘
         │ Miss
         ↓
┌─────────────────────────┐
│  Gemini API Call        │ ← Hanya jika perlu
│  (With Fallback)        │    + Model fallback strategy
└────────┬────────────────┘
         ↓
┌─────────────────────────┐
│  Cache Response         │ ← Simpan untuk reuse
│  (TTL based on type)    │    Short: 5min, Default: 1hr, Long: 24hr
└────────┬────────────────┘
         ↓
    User Response
```

## Fitur Optimasi

### 1. Response Caching Layer

**File:** `app/Services/AiResponseCacheService.php`

Caching response AI berdasarkan tenant, user, dan normalized message.

#### Cara Kerja
```php
// Generate cache key unik per tenant/user/message
$cacheKey = $cacheService->generateCacheKey($tenantId, $userId, $message);

// Cek cache dulu sebelum panggil API
$cached = $cacheService->get($cacheKey);
if ($cached !== null) {
    return $cached; // Cache HIT - no API call!
}

// Panggil API dan cache hasilnya
$response = $gemini->chat($message, $history);
$cacheService->put($cacheKey, $response);
```

#### TTL Strategy
- **Short TTL (5 menit):** Data real-time seperti stok, transaksi hari ini
- **Default TTL (1 jam):** Query umum tentang laporan, produk
- **Long TTL (24 jam):** Laporan periodik (mingguan, bulanan)

#### Expected Impact
- Mengurangi 40-60% API calls untuk query repetitif
- Response time: <10ms (cache hit) vs 2-5s (API call)

### 2. Rule-Based Response Handler

**File:** `app/Services/RuleBasedResponseHandler.php`

Menangani pertanyaan sederhana dengan template response tanpa memanggil Gemini API.

#### Supported Patterns
```php
- Greetings: "halo", "hai", "hi", "hello"
- Time greetings: "selamat pagi/siang/sore/malam"
- Gratitude: "terima kasih", "thanks", "makasih"
- Farewell: "bye", "dadah", "sampai jumpa"
- Identity: "siapa kamu", "nama kamu"
- Capabilities: "apa bisa", "bisa apa", "fitur apa"
- Help: "bantuan", "help", "tolong"
```

#### Cara Kerja
```php
if ($ruleHandler->canHandle($message)) {
    $response = $ruleHandler->handle($message, $userName);
    // Return instantly - zero API cost!
    return response()->json($response);
}
```

#### Expected Impact
- Mengurangi ~10-15% API calls untuk chit-chat
- Response time: <1ms (instant)
- Zero API cost untuk pattern yang dikenali

### 3. Batch Processing

**File:** `app/Services/AiBatchProcessor.php`

Memproses multiple AI requests dalam batch untuk mengurangi overhead.

#### Use Cases
- Generate recommendations untuk banyak tenant sekaligus
- Bulk analysis data
- Scheduled AI tasks

#### Cara Kerja
```php
$messages = [
    ['tenant_id' => 1, 'user_id' => 1, 'message' => '...'],
    ['tenant_id' => 2, 'user_id' => 5, 'message' => '...'],
    // ... up to 10 messages per batch
];

$responses = $batchProcessor->processBatch($messages);
```

#### Features
- Auto cache checking untuk setiap message dalam batch
- Chunking otomatis jika batch > 10 messages
- Queue-based async processing untuk background jobs
- Error isolation (1 failure tidak gagalkan seluruh batch)

#### Expected Impact
- Mengurangi overhead connection setup
- Better resource utilization
- Ideal untuk scheduled tasks

### 4. Response Streaming (SSE)

**File:** `app/Services/AiStreamingService.php`

Server-Sent Events (SSE) untuk streaming response secara real-time.

#### Endpoint Baru
```
POST /chat/stream
Content-Type: application/json

{
    "message": "Berapa omzet bulan ini?",
    "session_id": 123
}
```

#### Response Format (SSE)
```
event: start
data: {"message":"Processing your request..."}

event: chunk
data: {"text":"Omzet","progress":5.0,"is_final":false}

event: chunk
data: {"text":"bulan ini","progress":10.0,"is_final":false}

...

event: complete
data: {"full_text":"Omzet bulan ini adalah Rp 50.000.000","model":"gemini-2.5-flash"}
```

#### Frontend Integration Example
```javascript
const eventSource = new EventSource('/chat/stream', {
    // Note: EventSource only supports GET, use fetch for POST
});

// Or with fetch:
const response = await fetch('/chat/stream', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ message: '...' })
});

const reader = response.body.getReader();
const decoder = new TextDecoder();

while (true) {
    const { done, value } = await reader.read();
    if (done) break;
    
    const text = decoder.decode(value);
    // Parse SSE events and update UI
}
```

#### Expected Impact
- Perceived latency berkurang drastis (user lihat text muncul bertahap)
- Better UX untuk response panjang
- Typewriter effect yang smooth

## Konfigurasi

### Environment Variables

Tambahkan ke `.env`:

```env
# Cache Configuration (Redis recommended)
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# AI Optimization Settings
AI_RESPONSE_CACHE_ENABLED=true
AI_RESPONSE_CACHE_TTL=3600

AI_RULE_BASED_ENABLED=true

AI_BATCH_SIZE=10
AI_BATCH_QUEUE=ai

AI_STREAMING_ENABLED=true
AI_STREAM_CHUNK_DELAY=50
```

### Install Redis (Production)

```bash
# Ubuntu/Debian
sudo apt-get install redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server

# macOS
brew install redis
brew services start redis

# Windows (WSL or Docker)
docker run --name redis -p 6379:6379 -d redis
```

### Queue Worker Setup

Untuk batch processing async:

```bash
# Start queue worker
php artisan queue:work --queue=ai --tries=2 --timeout=180

# Or use supervisor for production
# /etc/supervisor/conf.d/laravel-worker.conf
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=ai --sleep=3 --tries=2 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/worker.log
```

## Monitoring & Metrics

### Cache Statistics

```php
// Get cache stats
$stats = $cacheService->getStats();
// Returns: ['driver' => 'redis', 'prefix' => 'qalcuity-erp-cache-']
```

### Logging

Semua cache operations di-log ke Laravel log:

```
[INFO] AI Response Cache HIT - key: ai_response:a3f2b1c...
[INFO] AI Response Cache PUT - key: ai_response:d4e5f6g..., ttl: 3600
[INFO] AiBatchProcessor: Processed batch - total: 10, cached: 6, api_calls: 4
```

### Key Metrics to Track

1. **Cache Hit Rate**
   - Target: 40-60%
   - Formula: (cache_hits / total_requests) * 100

2. **Rule-Based Response Rate**
   - Target: 10-15%
   - Pattern: (rule_based_responses / total_requests) * 100

3. **Average Response Time**
   - Before optimization: 2-5 seconds
   - After optimization: 
     - Cache hit: <10ms
     - Rule-based: <1ms
     - API call: 2-5s (same as before)
   - Weighted average should drop 40-50%

4. **API Cost Reduction**
   - Estimate: 50-70% reduction in API calls
   - Track: Total API calls per day/week/month

## Testing

### Test Caching

```bash
# Send same message twice
curl -X POST http://localhost/chat/send \
  -H "Content-Type: application/json" \
  -d '{"message":"halo","session_id":1}'

# First call: API called, response cached
# Second call: Cache hit, instant response

# Check logs for "AI Response Cache HIT"
tail -f storage/logs/laravel.log | grep "Cache HIT"
```

### Test Rule-Based Handler

```bash
# These should return instantly without API call
curl -X POST http://localhost/chat/send \
  -H "Content-Type: application/json" \
  -d '{"message":"terima kasih","session_id":1}'

curl -X POST http://localhost/chat/send \
  -H "Content-Type: application/json" \
  -d '{"message":"siapa kamu","session_id":1}'
```

### Test Streaming

```bash
curl -X POST http://localhost/chat/stream \
  -H "Content-Type: application/json" \
  -H "Accept: text/event-stream" \
  -d '{"message":"berapa omzet bulan ini?","session_id":1}'
```

## Migration Guide

### Untuk Existing Deployments

1. **Backup database**
   ```bash
   php artisan backup:run
   ```

2. **Update dependencies** (jika perlu Redis)
   ```bash
   composer require predis/predis
   # or
   composer require phpredis/phpredis
   ```

3. **Update .env**
   ```env
   CACHE_STORE=redis  # atau tetap database untuk dev
   ```

4. **Run migrations** (tidak ada migration baru, semua via service)
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

5. **Deploy code**
   ```bash
   git pull
   composer install --optimize-autoloader
   php artisan optimize
   ```

6. **Start queue workers** (untuk batch processing)
   ```bash
   php artisan queue:restart
   ```

## Troubleshooting

### Cache Not Working

1. Check cache driver:
   ```php
   dd(config('cache.default')); // Should be 'redis' or 'database'
   ```

2. Test cache manually:
   ```php
   Cache::put('test', 'value', 60);
   dd(Cache::get('test')); // Should return 'value'
   ```

3. Check Redis connection:
   ```bash
   redis-cli ping  # Should return PONG
   ```

### Streaming Not Working

1. Check nginx config (disable buffering):
   ```nginx
   location /chat/stream {
       proxy_buffering off;
       proxy_cache off;
       tcp_nodelay on;
   }
   ```

2. Check PHP output buffering:
   ```php
   // In php.ini
   output_buffering = Off
   implicit_flush = On
   ```

### High Memory Usage

1. Reduce batch size:
   ```env
   AI_BATCH_SIZE=5  # Default 10
   ```

2. Clear old cache:
   ```bash
   php artisan cache:clear
   ```

## Best Practices

### Do's
✅ Gunakan Redis untuk production cache  
✅ Monitor cache hit rate secara regular  
✅ Adjust TTL berdasarkan tipe data  
✅ Gunakan rule-based untuk common patterns  
✅ Implement proper error handling  

### Don'ts
❌ Jangan cache data yang sangat dinamis (real-time stock)  
❌ Jangan set TTL terlalu panjang untuk data yang sering berubah  
❌ Jangan gunakan batch processing untuk single user request  
❌ Jangan lupa flush cache setelah major data changes  

## Future Enhancements

Potensi improvement selanjutnya:

1. **Semantic Caching**
   - Cache berdasarkan semantic similarity, bukan exact match
   - Contoh: "berapa omzet?" dan "omzet berapa?" dianggap sama

2. **Predictive Pre-fetching**
   - Predict kemungkinan follow-up questions
   - Pre-fetch dan cache responses

3. **Adaptive TTL**
   - Auto-adjust TTL berdasarkan query frequency dan data volatility

4. **Multi-level Cache**
   - L1: In-memory (APCu) - microseconds
   - L2: Redis - milliseconds
   - L3: Database - fallback

5. **Cost Analytics Dashboard**
   - Real-time tracking API costs
   - Savings from optimizations
   - Per-tenant usage breakdown

## Support

Untuk pertanyaan atau issue terkait optimasi ini:
- Check logs: `storage/logs/laravel.log`
- Monitor cache: `php artisan cache:table` (untuk database driver)
- Review metrics: Custom dashboard (to be implemented)

---

**Last Updated:** April 2026  
**Version:** 1.0  
**Author:** Qalcuity AI Team
