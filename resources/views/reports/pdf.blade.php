<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1f2937; }
        .header { background: #1d4ed8; color: white; padding: 20px 24px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; font-weight: bold; }
        .header p  { font-size: 11px; opacity: 0.8; margin-top: 4px; }
        .meta { padding: 0 24px 16px; display: flex; gap: 24px; }
        .meta-item label { font-size: 10px; color: #6b7280; text-transform: uppercase; }
        .meta-item span  { font-size: 12px; font-weight: 600; color: #111827; display: block; }
        .summary { margin: 0 24px 20px; display: flex; gap: 12px; }
        .summary-card { flex: 1; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; }
        .summary-card .label { font-size: 10px; color: #6b7280; }
        .summary-card .value { font-size: 15px; font-weight: bold; color: #1d4ed8; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin: 0 24px; width: calc(100% - 48px); }
        thead tr { background: #dbeafe; }
        th { padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; color: #1e40af; font-weight: 600; }
        td { padding: 7px 10px; border-bottom: 1px solid #f3f4f6; font-size: 11px; }
        tr:nth-child(even) td { background: #f9fafb; }
        .footer { margin-top: 24px; padding: 12px 24px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 10px; color: #9ca3af; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 10px; font-weight: 600; }
        .badge-green  { background: #d1fae5; color: #065f46; }
        .badge-red    { background: #fee2e2; color: #991b1b; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>{{ $tenant_name }} &nbsp;·&nbsp; Periode: {{ $period }}</p>
    </div>

    @if(!empty($summary))
    <div class="summary">
        @foreach($summary as $item)
        <div class="summary-card">
            <div class="label">{{ $item['label'] }}</div>
            <div class="value">{{ $item['value'] }}</div>
        </div>
        @endforeach
    </div>
    @endif

    <table>
        <thead>
            <tr>
                @foreach($headers as $h)
                <th>{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            <tr>
                @foreach($row as $cell)
                <td>{{ $cell }}</td>
                @endforeach
            </tr>
            @empty
            <tr><td colspan="{{ count($headers) }}" style="text-align:center;color:#9ca3af;padding:20px">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada {{ now()->format('d M Y H:i') }} &nbsp;·&nbsp; Qalcuity ERP
    </div>
</body>
</html>
