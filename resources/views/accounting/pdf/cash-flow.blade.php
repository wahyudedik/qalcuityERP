<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1a1a; background: #fff; }
    .header { text-align: center; border-bottom: 2px solid #1e40af; padding-bottom: 10px; margin-bottom: 16px; }
    .header h1 { font-size: 16px; font-weight: bold; color: #1e40af; }
    .header p { font-size: 10px; color: #555; margin-top: 2px; }
    .section-title { font-size: 9px; font-weight: bold; text-transform: uppercase; padding: 5px 8px; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; margin-bottom: 0; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    table td { padding: 4px 8px; border-bottom: 1px solid #f3f4f6; }
    table td:last-child { text-align: right; }
    .subtotal td { font-weight: bold; background: #f9fafb; border-top: 1px solid #e5e7eb; }
    .indent td:first-child { padding-left: 20px; color: #6b7280; font-size: 9px; }
    .summary-row td { padding: 6px 8px; font-weight: bold; }
    .total-row { background: #1e40af; color: #fff; }
    .total-row td { padding: 8px; font-weight: bold; font-size: 12px; }
    .footer { margin-top: 20px; font-size: 8px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    .neg { color: #dc2626; }
</style>
</head>
<body>
@php
    $logoUrl = $tenant?->logo ? Storage::disk('public')->url($tenant->logo) : null;
    $lhColor = $tenant?->letter_head_color ?? '#1e40af';
@endphp
@if($logoUrl || ($tenant?->npwp) || ($tenant?->tagline))
<div style="border-bottom:3px solid {{ $lhColor }};padding-bottom:10px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:flex-start;">
    <div style="display:flex;align-items:flex-start;">
        @if($logoUrl)<img src="{{ $logoUrl }}" style="max-height:50px;max-width:130px;object-fit:contain;" alt="{{ $tenant->name }}">@endif
        <div style="padding-left:{{ $logoUrl ? '12px' : '0' }};">
            <div style="font-size:14px;font-weight:bold;color:{{ $lhColor }};">{{ $tenant->name }}</div>
            @if($tenant->tagline)<div style="font-size:8px;color:#6b7280;">{{ $tenant->tagline }}</div>@endif
            <div style="font-size:8px;color:#374151;margin-top:3px;line-height:1.5;">
                @if($tenant->address){{ $tenant->address }}@if($tenant->city), {{ $tenant->city }}@endif<br>@endif
                @if($tenant->phone)Telp: {{ $tenant->phone }}@endif
                @if($tenant->email) | {{ $tenant->email }}@endif
            </div>
            @if($tenant->npwp)<div style="font-size:8px;color:#6b7280;">NPWP: {{ $tenant->npwp }}</div>@endif
        </div>
    </div>
    <div style="text-align:right;">
        <div style="font-size:14px;font-weight:bold;color:{{ $lhColor }};text-transform:uppercase;">Arus Kas</div>
        <div style="font-size:9px;color:#6b7280;">Cash Flow Statement</div>
    </div>
</div>
@endif
<div class="header">
    <h1>LAPORAN ARUS KAS</h1>
    <p>{{ $tenant?->name ?? 'Qalcuity ERP' }}</p>
    <p>Periode: {{ \Carbon\Carbon::parse($from)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($to)->translatedFormat('d F Y') }}</p>
    <p style="margin-top:4px;font-size:9px;color:#6b7280;">Metode Tidak Langsung (Indirect Method)</p>
</div>

@php
    $fmt = fn($n) => ($n < 0 ? '(' : '') . 'Rp ' . number_format(abs($n), 0, ',', '.') . ($n < 0 ? ')' : '');
    $cls = fn($n) => $n < 0 ? 'neg' : '';
@endphp

{{-- Saldo Awal --}}
<table>
    <tr class="summary-row" style="background:#f3f4f6;">
        <td>Saldo Kas Awal Periode</td>
        <td class="{{ $cls($data['opening_cash']) }}">{{ $fmt($data['opening_cash']) }}</td>
    </tr>
</table>

{{-- Operasi --}}
<div class="section-title" style="background:#eff6ff;color:#1d4ed8;">I. ARUS KAS DARI AKTIVITAS OPERASI</div>
<table>
    <tr><td>Laba/Rugi Bersih</td><td class="{{ $cls($data['operating']['net_income']) }}">{{ $fmt($data['operating']['net_income']) }}</td></tr>
    @foreach($data['operating']['wc_adjustments'] as $item)
    <tr class="indent"><td>{{ $item['label'] }}</td><td class="{{ $cls($item['amount']) }}">{{ $fmt($item['amount']) }}</td></tr>
    @endforeach
    <tr class="subtotal"><td>Arus Kas Bersih dari Operasi</td><td class="{{ $cls($data['operating']['total']) }}">{{ $fmt($data['operating']['total']) }}</td></tr>
</table>

{{-- Investasi --}}
<div class="section-title" style="background:#faf5ff;color:#7e22ce;">II. ARUS KAS DARI AKTIVITAS INVESTASI</div>
<table>
    @forelse($data['investing']['items'] as $item)
    <tr><td>{{ $item['label'] }}</td><td class="{{ $cls($item['amount']) }}">{{ $fmt($item['amount']) }}</td></tr>
    @empty
    <tr><td colspan="2" style="color:#9ca3af;text-align:center;padding:6px;">Tidak ada aktivitas investasi</td></tr>
    @endforelse
    <tr class="subtotal"><td>Arus Kas Bersih dari Investasi</td><td class="{{ $cls($data['investing']['total']) }}">{{ $fmt($data['investing']['total']) }}</td></tr>
</table>

{{-- Pendanaan --}}
<div class="section-title" style="background:#fff7ed;color:#c2410c;">III. ARUS KAS DARI AKTIVITAS PENDANAAN</div>
<table>
    @forelse($data['financing']['items'] as $item)
    <tr><td>{{ $item['label'] }}</td><td class="{{ $cls($item['amount']) }}">{{ $fmt($item['amount']) }}</td></tr>
    @empty
    <tr><td colspan="2" style="color:#9ca3af;text-align:center;padding:6px;">Tidak ada aktivitas pendanaan</td></tr>
    @endforelse
    <tr class="subtotal"><td>Arus Kas Bersih dari Pendanaan</td><td class="{{ $cls($data['financing']['total']) }}">{{ $fmt($data['financing']['total']) }}</td></tr>
</table>

{{-- Ringkasan --}}
<table>
    <tr class="summary-row" style="background:#f3f4f6;">
        <td>Kenaikan (Penurunan) Kas Bersih</td>
        <td class="{{ $cls($data['net_change']) }}">{{ $fmt($data['net_change']) }}</td>
    </tr>
    <tr class="summary-row" style="background:#f3f4f6;">
        <td>Saldo Kas Awal Periode</td>
        <td>{{ $fmt($data['opening_cash']) }}</td>
    </tr>
    <tr class="total-row">
        <td>SALDO KAS AKHIR PERIODE</td>
        <td>{{ $fmt($data['closing_cash']) }}</td>
    </tr>
</table>

<div class="footer">Dicetak pada {{ now()->translatedFormat('d F Y H:i') }} — Qalcuity ERP</div>
</body>
</html>
