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
    table td:nth-child(3) { text-align: right; color: #9ca3af; font-size: 9px; width: 50px; }
    .subtotal td { font-weight: bold; background: #f9fafb; border-top: 1px solid #e5e7eb; }
    .summary-row td { padding: 6px 8px; font-weight: bold; }
    .code { color: #9ca3af; font-size: 8px; margin-right: 4px; }
    .footer { margin-top: 20px; font-size: 8px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    .net-laba { background: #f0fdf4; border: 2px solid #86efac; }
    .net-rugi { background: #fef2f2; border: 2px solid #fca5a5; }
    .net-laba td { color: #166534; font-size: 12px; font-weight: bold; }
    .net-rugi td { color: #991b1b; font-size: 12px; font-weight: bold; }
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
        <div style="font-size:14px;font-weight:bold;color:{{ $lhColor }};text-transform:uppercase;">Laba Rugi</div>
        <div style="font-size:9px;color:#6b7280;">Income Statement</div>
    </div>
</div>
@endif
<div class="header">
    <h1>LAPORAN LABA RUGI</h1>
    <p>{{ $tenant?->name ?? 'Qalcuity ERP' }}</p>
    <p>Periode: {{ \Carbon\Carbon::parse($from)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($to)->translatedFormat('d F Y') }}</p>
</div>

@php
    $fmt = fn($n) => 'Rp ' . number_format(abs($n), 0, ',', '.');
    $pct = fn($part, $total) => $total > 0 ? round(($part / $total) * 100, 1) . '%' : '-';
@endphp

{{-- Pendapatan --}}
<div class="section-title" style="background:#f0fdf4;color:#166534;">PENDAPATAN</div>
<table>
    @foreach($data['revenue']['items'] as $acc)
    <tr>
        <td><span class="code">{{ $acc['code'] }}</span>{{ $acc['name'] }}</td>
        <td>{{ $fmt($acc['balance']) }}</td>
        <td>{{ $pct($acc['balance'], $data['revenue']['total']) }}</td>
    </tr>
    @endforeach
    <tr class="subtotal"><td>Total Pendapatan</td><td>{{ $fmt($data['revenue']['total']) }}</td><td></td></tr>
</table>

{{-- HPP --}}
@if($data['cogs']['items']->isNotEmpty())
<div class="section-title" style="background:#f9fafb;color:#374151;">HARGA POKOK PENJUALAN</div>
<table>
    @foreach($data['cogs']['items'] as $acc)
    <tr>
        <td><span class="code">{{ $acc['code'] }}</span>{{ $acc['name'] }}</td>
        <td>({{ $fmt($acc['balance']) }})</td>
        <td>{{ $pct($acc['balance'], $data['revenue']['total']) }}</td>
    </tr>
    @endforeach
    <tr class="subtotal"><td>Total HPP</td><td>({{ $fmt($data['cogs']['total']) }})</td><td></td></tr>
</table>
@endif

<table>
    <tr class="summary-row" style="background:#eff6ff;">
        <td>LABA KOTOR</td>
        <td>{{ $data['gross_profit'] < 0 ? '(' : '' }}{{ $fmt($data['gross_profit']) }}{{ $data['gross_profit'] < 0 ? ')' : '' }}</td>
        <td></td>
    </tr>
</table>

{{-- Beban Operasional --}}
@if($data['opex']['items']->isNotEmpty())
<div class="section-title" style="background:#f9fafb;color:#374151;">BEBAN OPERASIONAL</div>
<table>
    @foreach($data['opex']['items'] as $acc)
    <tr>
        <td><span class="code">{{ $acc['code'] }}</span>{{ $acc['name'] }}</td>
        <td>({{ $fmt($acc['balance']) }})</td>
        <td>{{ $pct($acc['balance'], $data['revenue']['total']) }}</td>
    </tr>
    @endforeach
    <tr class="subtotal"><td>Total Beban Operasional</td><td>({{ $fmt($data['opex']['total']) }})</td><td></td></tr>
</table>
@endif

<table>
    <tr class="summary-row" style="background:#eff6ff;">
        <td>LABA OPERASI</td>
        <td>{{ $data['operating_income'] < 0 ? '(' : '' }}{{ $fmt($data['operating_income']) }}{{ $data['operating_income'] < 0 ? ')' : '' }}</td>
        <td></td>
    </tr>
</table>

{{-- Lain-lain --}}
@if($data['other_expense']['items']->isNotEmpty())
<div class="section-title" style="background:#f9fafb;color:#374151;">BEBAN / PENDAPATAN LAIN-LAIN</div>
<table>
    @foreach($data['other_expense']['items'] as $acc)
    <tr>
        <td><span class="code">{{ $acc['code'] }}</span>{{ $acc['name'] }}</td>
        <td>({{ $fmt($acc['balance']) }})</td>
        <td></td>
    </tr>
    @endforeach
</table>
@endif

{{-- Net Income --}}
<table>
    <tr class="{{ $data['net_income'] >= 0 ? 'net-laba' : 'net-rugi' }}">
        <td>{{ $data['net_income'] >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH' }}</td>
        <td>{{ $data['net_income'] < 0 ? '(' : '' }}{{ $fmt($data['net_income']) }}{{ $data['net_income'] < 0 ? ')' : '' }}</td>
        <td></td>
    </tr>
</table>

<div class="footer">Dicetak pada {{ now()->translatedFormat('d F Y H:i') }} — Qalcuity ERP</div>
</body>
</html>
