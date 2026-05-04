<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 20px; color: #374151; }
        .container { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #1d4ed8; padding: 28px 32px; }
        .header h1 { color: #fff; font-size: 20px; margin: 0; }
        .header p  { color: rgba(255,255,255,0.75); font-size: 13px; margin: 4px 0 0; }
        .body { padding: 28px 32px; }
        .greeting { font-size: 15px; margin-bottom: 16px; }
        .invoice-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px 20px; margin: 20px 0; }
        .invoice-box .row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 13px; border-bottom: 1px solid #f3f4f6; }
        .invoice-box .row:last-child { border-bottom: none; }
        .invoice-box .row .label { color: #6b7280; }
        .invoice-box .row .value { font-weight: 600; color: #111827; }
        .invoice-box .row.total .value { color: #1d4ed8; font-size: 16px; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 9999px; font-size: 11px; font-weight: 700; }
        .status-unpaid  { background: #fee2e2; color: #991b1b; }
        .status-partial { background: #fef3c7; color: #92400e; }
        .status-paid    { background: #d1fae5; color: #065f46; }
        .note { font-size: 13px; color: #6b7280; margin-top: 16px; line-height: 1.6; }
        .footer { background: #f9fafb; padding: 16px 32px; text-align: center; font-size: 11px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>{{ $tenantName }}</h1>
        <p>Invoice untuk Anda</p>
    </div>
    <div class="body">
        <p class="greeting">Yth. <strong>{{ $invoice->customer?->name }}</strong>,</p>
        <p style="font-size:13px;line-height:1.6;color:#6b7280">
            Bersama email ini kami lampirkan invoice dari <strong>{{ $tenantName }}</strong>.
            Mohon segera melakukan pembayaran sebelum tanggal jatuh tempo.
        </p>

        <div class="invoice-box">
            <div class="row">
                <span class="label">No. Invoice</span>
                <span class="value">{{ $invoice->number }}</span>
            </div>
            <div class="row">
                <span class="label">Tanggal</span>
                <span class="value">{{ $invoice->created_at->format('d M Y') }}</span>
            </div>
            <div class="row">
                <span class="label">Jatuh Tempo</span>
                <span class="value">{{ $invoice->due_date?->format('d M Y') ?? '-' }}</span>
            </div>
            <div class="row">
                <span class="label">Status</span>
                <span class="value">
                    @php
                        $cls = match($invoice->status) { 'paid' => 'status-paid', 'partial' => 'status-partial', default => 'status-unpaid' };
                        $lbl = match($invoice->status) { 'paid' => 'Lunas', 'partial' => 'Sebagian', default => 'Belum Dibayar' };
                    @endphp
                    <span class="status-badge {{ $cls }}">{{ $lbl }}</span>
                </span>
            </div>
            <div class="row total">
                <span class="label">Total Tagihan</span>
                <span class="value">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
            </div>
            @if($invoice->remaining_amount < $invoice->total_amount)
            <div class="row">
                <span class="label">Sisa Tagihan</span>
                <span class="value" style="color:#dc2626">Rp {{ number_format($invoice->remaining_amount, 0, ',', '.') }}</span>
            </div>
            @endif
        </div>

        @if($invoice->notes)
        <p class="note"><strong>Catatan:</strong> {{ $invoice->notes }}</p>
        @endif

        <p class="note">
            Invoice PDF terlampir dalam email ini. Jika ada pertanyaan, silakan hubungi kami.
        </p>
    </div>
    <div class="footer">
        Email ini dikirim otomatis oleh <strong>Qalcuity ERP</strong> &nbsp;·&nbsp; {{ now()->format('d M Y') }}
    </div>
</div>
</body>
</html>
