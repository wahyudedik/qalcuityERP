<?php

namespace Database\Seeders;

use App\Models\AiUseCaseRoute;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk mengisi 16 routing rules default AI use case.
 *
 * - 8 lightweight: provider=gemini, model=gemini-2.5-flash, min_plan=null
 * - 8 heavyweight: provider=anthropic, model=claude-3-5-sonnet-20241022, min_plan=professional
 *
 * Menggunakan updateOrCreate agar idempotent (aman dijalankan berulang kali).
 *
 * Requirements: 1.3, 1.4, 1.5, 3.6
 */
class AiUseCaseRouteSeeder extends Seeder
{
    public function run(): void
    {
        // -------------------------------------------------------------------------
        // Lightweight Use Cases — Gemini Flash
        // Operasional harian: chatbot, CRUD AI, parsing, notifikasi
        // -------------------------------------------------------------------------
        $lightweightUseCases = [
            'chatbot'             => 'Chatbot interaktif untuk percakapan umum dengan pengguna',
            'crud_ai'             => 'Bantuan AI untuk operasi CRUD dan pengisian data otomatis',
            'auto_reply'          => 'Balasan otomatis untuk pesan masuk (WhatsApp, email, dll)',
            'invoice_parsing'     => 'Ekstraksi data dari dokumen invoice secara otomatis',
            'document_parsing'    => 'Parsing dan ekstraksi informasi dari dokumen umum',
            'notification_ai'     => 'Pembuatan konten notifikasi yang dipersonalisasi',
            'product_description' => 'Pembuatan deskripsi produk untuk katalog dan marketplace',
            'email_draft'         => 'Pembuatan draft email bisnis secara otomatis',
        ];

        foreach ($lightweightUseCases as $useCase => $description) {
            AiUseCaseRoute::updateOrCreate(
                ['use_case' => $useCase, 'tenant_id' => null],
                [
                    'provider'    => 'gemini',
                    'model'       => 'gemini-2.5-flash',
                    'min_plan'    => null,
                    'is_active'   => true,
                    'description' => $description,
                ]
            );
        }

        // -------------------------------------------------------------------------
        // Heavyweight Use Cases — Claude Sonnet
        // Analitik berat: laporan keuangan, forecasting, audit, rekomendasi bisnis
        // -------------------------------------------------------------------------
        $heavyweightUseCases = [
            'financial_report'        => 'Pembuatan laporan keuangan komprehensif (neraca, laba rugi, arus kas)',
            'forecasting'             => 'Prediksi dan proyeksi bisnis berbasis data historis',
            'decision_support'        => 'Analisis mendalam untuk mendukung pengambilan keputusan strategis',
            'audit_analysis'          => 'Analisis audit trail dan deteksi anomali transaksi keuangan',
            'business_recommendation' => 'Rekomendasi bisnis berbasis analisis data menyeluruh',
            'bank_reconciliation_ai'  => 'Rekonsiliasi bank otomatis dengan pencocokan transaksi cerdas',
            'budget_analysis'         => 'Analisis anggaran dan varians antara realisasi dan rencana',
            'anomaly_detection'       => 'Deteksi anomali pada data transaksi, stok, dan aktivitas pengguna',
        ];

        foreach ($heavyweightUseCases as $useCase => $description) {
            AiUseCaseRoute::updateOrCreate(
                ['use_case' => $useCase, 'tenant_id' => null],
                [
                    'provider'    => 'anthropic',
                    'model'       => 'claude-3-5-sonnet-20241022',
                    'min_plan'    => 'professional',
                    'is_active'   => true,
                    'description' => $description,
                ]
            );
        }
    }
}
