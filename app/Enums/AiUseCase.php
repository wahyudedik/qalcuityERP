<?php

namespace App\Enums;

/**
 * Enum untuk semua use case AI yang didukung di Qalcuity ERP.
 *
 * Use case dibagi menjadi dua kategori:
 * - Lightweight: operasional harian, cocok untuk Gemini Flash (cepat & murah)
 * - Heavyweight: analitik berat, cocok untuk Claude Sonnet (akurat & powerful)
 *
 * Requirements: 1.4, 1.5, 8.2
 */
enum AiUseCase: string
{
    // -------------------------------------------------------------------------
    // Lightweight Use Cases — Gemini Flash
    // Operasional harian: chatbot, CRUD AI, parsing, notifikasi
    // -------------------------------------------------------------------------

    case CHATBOT = 'chatbot';
    case CRUD_AI = 'crud_ai';
    case AUTO_REPLY = 'auto_reply';
    case INVOICE_PARSING = 'invoice_parsing';
    case DOCUMENT_PARSING = 'document_parsing';
    case NOTIFICATION_AI = 'notification_ai';
    case PRODUCT_DESCRIPTION = 'product_description';
    case EMAIL_DRAFT = 'email_draft';

    // -------------------------------------------------------------------------
    // Heavyweight Use Cases — Claude Sonnet
    // Analitik berat: laporan keuangan, forecasting, audit, rekomendasi bisnis
    // -------------------------------------------------------------------------

    case FINANCIAL_REPORT = 'financial_report';
    case FORECASTING = 'forecasting';
    case DECISION_SUPPORT = 'decision_support';
    case AUDIT_ANALYSIS = 'audit_analysis';
    case BUSINESS_RECOMMENDATION = 'business_recommendation';
    case BANK_RECONCILIATION_AI = 'bank_reconciliation_ai';
    case BUDGET_ANALYSIS = 'budget_analysis';
    case ANOMALY_DETECTION = 'anomaly_detection';

    /**
     * Menentukan apakah use case ini termasuk kategori heavyweight.
     *
     * Heavyweight use cases membutuhkan model analitik yang lebih kuat
     * (Claude Sonnet) dan umumnya dikunci di balik subscription plan tertentu.
     *
     * @return bool true jika heavyweight, false jika lightweight
     */
    public function isHeavyweight(): bool
    {
        return in_array($this, [
            self::FINANCIAL_REPORT,
            self::FORECASTING,
            self::DECISION_SUPPORT,
            self::AUDIT_ANALYSIS,
            self::BUSINESS_RECOMMENDATION,
            self::BANK_RECONCILIATION_AI,
            self::BUDGET_ANALYSIS,
            self::ANOMALY_DETECTION,
        ]);
    }
}
