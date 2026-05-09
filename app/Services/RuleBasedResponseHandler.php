<?php

namespace App\Services;

/**
 * RuleBasedResponseHandler
 *
 * Menangani pertanyaan sederhana dengan response template tanpa memanggil Gemini API.
 * Ini mengurangi biaya dan latency untuk pertanyaan yang jawabannya sudah bisa diprediksi.
 */
class RuleBasedResponseHandler
{
    /**
     * Cek apakah rule-based handler di-enable
     */
    public function isEnabled(): bool
    {
        return config('gemini.optimization.rule_based_enabled', true);
    }

    /**
     * Cek apakah pesan bisa ditangani oleh rule-based handler
     */
    public function canHandle(string $message): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $message = strtolower(trim($message));

        // Pattern untuk pertanyaan sederhana yang tidak perlu AI
        $simplePatterns = [
            '/^(halo|hai|hi|hello|hey|selamat\s+(pagi|siang|sore|malam))/i',
            '/^(terima\s+kasih|thanks|thank\s+you|makasih)/i',
            '/^(bye|dadah|sampai\s+jumpa|goodbye)/i',
            '/^(siapa\s+kamu|nama\s+kamu|kamu\s+siapa)/i',
            '/^(apa\s+bisa|bisa\s+apa|fitur\s+apa)/i',
            '/^(bantuan|help|tolong)/i',
        ];

        foreach ($simplePatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate response berdasarkan rule
     */
    public function handle(string $message, ?string $userName = null): array
    {
        $message = strtolower(trim($message));
        $name = $userName ?? 'User';

        // Greeting patterns
        if (preg_match('/^(halo|hai|hi|hello|hey)/i', $message)) {
            return [
                'text' => "Halo {$name}! 👋 Saya Qalcuity AI, asisten ERP cerdas Anda. Ada yang bisa saya bantu hari ini?",
                'model' => 'rule-based',
                'cached' => false,
                'function_calls' => [],
            ];
        }

        // Selamat pagi/siang/sore/malam
        if (preg_match('/^selamat\s+(pagi|siang|sore|malam)/i', $message, $matches)) {
            $timeGreeting = ucfirst($matches[1]);

            return [
                'text' => "Selamat {$timeGreeting}, {$name}! Semoga harimu menyenangkan. Ada yang bisa saya bantu terkait bisnis Anda?",
                'model' => 'rule-based',
                'cached' => false,
                'function_calls' => [],
            ];
        }

        // Terima kasih
        if (preg_match('/^(terima\s+kasih|thanks|thank\s+you|makasih)/i', $message)) {
            return [
                'text' => "Sama-sama, {$name}! 😊 Senang bisa membantu. Jangan ragu untuk bertanya lagi jika ada yang dibutuhkan.",
                'model' => 'rule-based',
                'cached' => false,
                'function_calls' => [],
            ];
        }

        // Bye
        if (preg_match('/^(bye|dadah|sampai\s+jumpa|goodbye)/i', $message)) {
            return [
                'text' => "Sampai jumpa, {$name}! 👋 Semoga bisnis Anda semakin sukses. Hubungi saya kapan saja jika butuh bantuan!",
                'model' => 'rule-based',
                'cached' => false,
                'function_calls' => [],
            ];
        }

        // Siapa kamu
        if (preg_match('/^(siapa\s+kamu|nama\s+kamu|kamu\s+siapa)/i', $message)) {
            return [
                'text' => "Saya **Qalcuity AI**, asisten cerdas untuk sistem ERP Qalcuity. 🤖\n\n".
                    "Saya dapat membantu Anda:\n".
                    "- 📊 Melihat laporan penjualan, keuangan, dan inventory\n".
                    "- 📦 Mengelola produk, stok, dan pesanan\n".
                    "- 👥 Mengelola pelanggan, supplier, dan karyawan\n".
                    "- 💰 Mencatat transaksi dan pengeluaran\n".
                    "- 📈 Menganalisis tren bisnis dan memberikan rekomendasi\n".
                    "- 📸 Menganalisis gambar struk, nota, atau foto produk\n\n".
                    'Cukup ketik pertanyaan Anda dalam bahasa natural, dan saya akan membantu!',
                'model' => 'rule-based',
                'cached' => false,
                'function_calls' => [],
            ];
        }

        // Apa bisa / fitur apa
        if (preg_match('/^(apa\s+bisa|bisa\s+apa|fitur\s+apa)/i', $message)) {
            return [
                'text' => "Berikut adalah beberapa hal yang bisa saya lakukan:\n\n".
                    "**📊 Dashboard & Laporan:**\n".
                    "- \"kondisi bisnis hari ini\"\n".
                    "- \"rekap penjualan minggu ini\"\n".
                    "- \"laporan keuangan bulan ini\"\n\n".
                    "**📦 Inventory & Produk:**\n".
                    "- \"tambah produk Kopi Latte harga 25000\"\n".
                    "- \"cek stok produk A\"\n".
                    "- \"produk apa saja yang stoknya menipis?\"\n\n".
                    "**💰 Transaksi:**\n".
                    "- \"jual kopi 2 gelas 30000 cash\"\n".
                    "- \"catat pengeluaran listrik 500000\"\n".
                    "- \"buat invoice untuk customer Budi\"\n\n".
                    "**👥 Manajemen Kontak:**\n".
                    "- \"tambah pelanggan Siti nomor 08123456789\"\n".
                    "- \"daftar semua supplier\"\n\n".
                    "**📸 Analisis Gambar:**\n".
                    "- Upload foto struk → otomatis catat pengeluaran\n".
                    "- Upload foto produk → simpan ke database\n\n".
                    'Dan masih banyak lagi! Coba tanyakan apa saja tentang bisnis Anda. 😊',
                'model' => 'rule-based',
                'cached' => false,
                'function_calls' => [],
            ];
        }

        // Bantuan / help
        if (preg_match('/^(bantuan|help|tolong)/i', $message)) {
            return [
                'text' => "Tentu, saya siap membantu! 🙋‍♂️\n\n".
                    "**Cara menggunakan Qalcuity AI:**\n\n".
                    "1️⃣ **Tanya dalam bahasa natural** - Tidak perlu format khusus\n".
                    "   Contoh: \"berapa omzet bulan ini?\" atau \"stok kopi tinggal berapa?\"\n\n".
                    "2️⃣ **Perintah langsung** - Untuk aksi cepat\n".
                    "   Contoh: \"jual kopi 2 gelas 30000\" atau \"tambah produk Teh Botol\"\n\n".
                    "3️⃣ **Upload file/gambar** - Drag & drop atau klik upload\n".
                    "   - Foto struk/nota → otomatis ekstrak data\n".
                    "   - Foto produk → simpan ke database\n".
                    "   - PDF/CSV → analisis dan import\n\n".
                    "4️⃣ **Follow-up questions** - Saya ingat konteks percakapan\n".
                    "   Contoh: Setelah tanya omzet, bisa lanjut \"bandingkan dengan bulan lalu\"\n\n".
                    "**Tips:**\n".
                    "- Semakin spesifik pertanyaan, semakin akurat jawaban\n".
                    "- Gunakan kata kunci seperti \"hari ini\", \"minggu ini\", \"bulan ini\" untuk periode\n".
                    "- Jika ragu, coba \"kondisi bisnis\" untuk ringkasan umum\n\n".
                    'Ada yang ingin dicoba? 😊',
                'model' => 'rule-based',
                'cached' => false,
                'function_calls' => [],
            ];
        }

        // Fallback - seharusnya tidak sampai sini
        return [
            'text' => '',
            'model' => 'rule-based',
            'cached' => false,
            'function_calls' => [],
        ];
    }

    /**
     * Get list of supported patterns (untuk debugging/monitoring)
     */
    public function getSupportedPatterns(): array
    {
        return [
            'greetings' => ['halo', 'hai', 'hi', 'hello', 'hey'],
            'time_greetings' => ['selamat pagi', 'selamat siang', 'selamat sore', 'selamat malam'],
            'gratitude' => ['terima kasih', 'thanks', 'thank you', 'makasih'],
            'farewell' => ['bye', 'dadah', 'sampai jumpa', 'goodbye'],
            'identity' => ['siapa kamu', 'nama kamu', 'kamu siapa'],
            'capabilities' => ['apa bisa', 'bisa apa', 'fitur apa'],
            'help' => ['bantuan', 'help', 'tolong'],
        ];
    }
}
