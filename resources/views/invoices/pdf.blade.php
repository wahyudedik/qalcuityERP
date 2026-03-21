<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1f2937; background: #fff; }

        .page { padding: 32px 36px; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; }
        .brand h1 { font-size: 22px; font-weight: bold; color: #1d4ed8; letter-spacing: -0.5px; }
        .brand p  { font-size: 10px; color: #6b7280; margin-top: 2px; }
        .invoice-meta { text-align: right; }
        .invoice-meta .inv-number { font-size: 18px; font-weight: bold; color: #111827; }
        .invoice-meta .inv-label  { font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 1px; }

        /* Status badge */
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 9999px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 6px; }
        .status-unpaid  { background: #fee2e2; color: #991b1b; }
        .status-partial { background: #fef3c7; color: #92400e; }
        .status-paid    { background: #d1fae5; color: #065f46; }

        /* Divider */
        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 20px 0; }

        /* Parties */
        .parties { display: flex; gap: 32px; margin-bottom: 24px; }
        .party { flex: 1; }
        .party-label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; font-weight: 600; margin-bottom: 6px; }
        .party-name  { font-size: 13px; font-weight: 700; color: #111827; }
        .party-detail { font-size: 10px; color: #6b7280; margin-top: 2px; line-height: 1.5; }

        /* Dates */
        .dates { display: flex; gap: 16px; margin-bottom: 24px; }
        .date-box { flex: 1; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px 14px; }
        .date-box .label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; font-weight: 600; }
        .date-box .value { font-size: 12px; font-weight: 600; color: #111827; margin-top: 3px; }

        /* Items table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead tr { background: #1d4ed8; }
        th { padding: 9px 12px; text-align: left; font-size: 10px; text-transform: uppercase; color: #fff; font-weight: 600; letter-spacing: 0.5px; }
        th:last-child, td:last-child { text-align: right; }
        td { padding: 9px 12px; border-bottom: 1px solid #f3f4f6; font-size: 11px; color: #374151; }
        tr:nth-child(even) td { background: #f9fafb; }
        .no-items td { text-align: center; color: #9ca3af; padding: 20px; }

        /* Totals */
        .totals { margin-left: auto; width: 260px; }
        .totals-row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 11px; color: #6b7280; }
        .totals-row.grand { border-top: 2px solid #1d4ed8; margin-top: 6px; padding-top: 10px; font-size: 14px; font-weight: 700; color: #111827; }
        .totals-row.paid-row { color: #059669; }
        .totals-row.remaining-row { color: #dc2626; font-weight: 600; }

        /* Notes */
        .notes { background: #f9fafb; border-left: 3px solid #1d4ed8; padding: 10px 14px; border-radius: 0 6px 6px 0; margin-bottom: 24px; }
        .notes .label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; font-weight: 600; margin-bottom: 4px; }
        .notes p { font-size: 11px; color: #374151; line-height: 1.5; }

        /* Payment history */
        .payments-section { margin-bottom: 24px; }
        .section-title { font-size: 11px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        .payment-row { display: flex; justify-content: space-between; padding: 6px 10px; background: #f0fdf4; border-radius: 6px; margin-bottom: 4px; font-size: 10px; color: #065f46; }

        /* Footer */
        .footer { border-top: 1px solid #e5e7eb; padding-top: 14px; display: flex; justify-content: space-between; align-items: center; }
        .footer-left { font-size: 10px; color: #9ca3af; line-height: 1.6; }
        .footer-right { text-align: right; font-size: 10px; color: #9ca3af; }
        .footer-brand { font-size: 11px; font-weight: 700; color: #1d4ed8; }
    </style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div class="brand">
            <h1>{{ $invoice->tenant->name }}</h1>
            @if($invoice->tenant->address)
            <p>{{ $invoice->tenant->address }}</p>
            @endif
            @if($invoice->tenant->phone)
            <p>{{ $invoice->tenant->phone }}</p>
            @endif
            @if($invoice->tenant->email)
            <p>{{ $invoice->tenant->email }}</p>
            @endif
        </div>
        <div class="invoice-meta">
            <div class="inv-label">Invoice</div>
            <div class="inv-number">{{ $invoice->number }}</div>
            @php
                $statusClass = match($invoice->status) {
                    'paid'    => 'status-paid',
                    'partial' => 'status-partial',
                    default   => 'status-unpaid',
                };
                $statusLabel = match($invoice->status) {
                    'paid'    => 'Lunas',
                    'partial' => 'Sebagian',
                    default   => 'Belum Dibayar',
                };
            @endphp
            <div><span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span></div>
        </div>
    </div>

    <hr class="divider">

    {{-- Parties --}}
    <div class="parties">
        <div class="party">
            <div class="party-label">Dari</div>
            <div class="party-name">{{ $invoice->tenant->name }}</div>
            @if($invoice->tenant->address)
            <div class="party-detail">{{ $invoice->tenant->address }}</div>
            @endif
        </div>
        <div class="party">
            <div class="party-label">Kepada</div>
            <div class="party-name">{{ $invoice->customer?->name ?? '-' }}</div>
            @if($invoice->customer?->company)
            <div class="party-detail">{{ $invoice->customer->company }}</div>
            @endif
            @if($invoice->customer?->address)
            <div class="party-detail">{{ $invoice->customer->address }}</div>
            @endif
            @if($invoice->customer?->phone)
            <div class="party-detail">{{ $invoice->customer->phone }}</div>
            @endif
            @if($invoice->customer?->email)
            <div class="party-detail">{{ $invoice->customer->email }}</div>
            @endif
        </div>
    </div>

    {{-- Dates --}}
    <div class="dates">
        <div class="date-box">
            <div class="label">Tanggal Invoice</div>
            <div class="value">{{ $invoice->created_at->format('d M Y') }}</div>
        </div>
        <div class="date-box">
            <div class="label">Jatuh Tempo</div>
            <div class="value" style="{{ $invoice->status !== 'paid' && $invoice->due_date < now() ? 'color:#dc2626' : '' }}">
                {{ $invoice->due_date?->format('d M Y') ?? '-' }}
            </div>
        </div>
        @if($invoice->salesOrder)
        <div class="date-box">
            <div class="label">No. Sales Order</div>
            <div class="value">{{ $invoice->salesOrder->number }}</div>
        </div>
        @endif
    </div>

    {{-- Items from Sales Order --}}
    @if($invoice->salesOrder && $invoice->salesOrder->items->count())
    <table>
        <thead>
            <tr>
                <th style="width:40%">Produk / Layanan</th>
                <th style="width:10%;text-align:center">Qty</th>
                <th style="width:10%;text-align:center">Satuan</th>
                <th style="width:20%;text-align:right">Harga Satuan</th>
                <th style="width:20%;text-align:right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->salesOrder->items as $item)
            <tr>
                <td>{{ $item->product?->name ?? '-' }}</td>
                <td style="text-align:center">{{ $item->quantity }}</td>
                <td style="text-align:center">{{ $item->product?->unit ?? 'pcs' }}</td>
                <td style="text-align:right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                <td style="text-align:right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals">
        @if($invoice->salesOrder->discount > 0)
        <div class="totals-row">
            <span>Subtotal</span>
            <span>Rp {{ number_format($invoice->salesOrder->subtotal, 0, ',', '.') }}</span>
        </div>
        <div class="totals-row">
            <span>Diskon</span>
            <span>- Rp {{ number_format($invoice->salesOrder->discount, 0, ',', '.') }}</span>
        </div>
        @endif
        @if($invoice->salesOrder->tax > 0)
        <div class="totals-row">
            <span>Pajak</span>
            <span>Rp {{ number_format($invoice->salesOrder->tax, 0, ',', '.') }}</span>
        </div>
        @endif
        <div class="totals-row grand">
            <span>Total</span>
            <span>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
        </div>
        @if($invoice->paid_amount > 0)
        <div class="totals-row paid-row">
            <span>Terbayar</span>
            <span>- Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</span>
        </div>
        <div class="totals-row remaining-row">
            <span>Sisa Tagihan</span>
            <span>Rp {{ number_format($invoice->remaining_amount, 0, ',', '.') }}</span>
        </div>
        @endif
    </div>

    @else
    {{-- No items, just show total --}}
    <div class="totals" style="margin-bottom:20px">
        <div class="totals-row grand">
            <span>Total Tagihan</span>
            <span>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
        </div>
        @if($invoice->paid_amount > 0)
        <div class="totals-row paid-row">
            <span>Terbayar</span>
            <span>- Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</span>
        </div>
        <div class="totals-row remaining-row">
            <span>Sisa Tagihan</span>
            <span>Rp {{ number_format($invoice->remaining_amount, 0, ',', '.') }}</span>
        </div>
        @endif
    </div>
    @endif

    {{-- Payment history --}}
    @if($invoice->payments && $invoice->payments->count())
    <div class="payments-section">
        <div class="section-title">Riwayat Pembayaran</div>
        @foreach($invoice->payments as $pay)
        <div class="payment-row">
            <span>{{ $pay->payment_date?->format('d M Y') ?? '-' }} &nbsp;·&nbsp; {{ strtoupper($pay->payment_method ?? '-') }}</span>
            <span>Rp {{ number_format($pay->amount, 0, ',', '.') }}</span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Notes --}}
    @if($invoice->notes)
    <div class="notes">
        <div class="label">Catatan</div>
        <p>{{ $invoice->notes }}</p>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-left">
            Terima kasih atas kepercayaan Anda.<br>
            Harap melakukan pembayaran sebelum tanggal jatuh tempo.
        </div>
        <div class="footer-right">
            <div class="footer-brand">Qalcuity ERP</div>
            <div>Dicetak: {{ now()->format('d M Y H:i') }}</div>
        </div>
    </div>

</div>
</body>
</html>
