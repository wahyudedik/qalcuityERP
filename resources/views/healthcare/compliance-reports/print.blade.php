<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Compliance Report - {{ $report->report_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            line-height: 1.6;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            color: #1e40af;
            font-size: 24px;
        }

        .header p {
            margin: 5px 0 0;
            color: #6b7280;
            font-size: 14px;
        }

        .section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-item {
            margin-bottom: 15px;
        }

        .info-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
        }

        .info-value {
            font-size: 14px;
            color: #111827;
            margin-top: 5px;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-blue {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-green {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-yellow {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-purple {
            background: #e9d5ff;
            color: #6b21a8;
        }

        .badge-orange {
            background: #fed7aa;
            color: #9a3412;
        }

        .badge-gray {
            background: #f3f4f6;
            color: #374151;
        }

        .finding-item {
            margin-bottom: 15px;
            padding-left: 25px;
            position: relative;
        }

        .finding-number {
            position: absolute;
            left: 0;
            width: 20px;
            height: 20px;
            background: #fed7aa;
            color: #9a3412;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 600;
        }

        .finding-text {
            font-size: 14px;
            color: #374151;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }

        @media print {
            body {
                margin: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>COMPLIANCE REPORT</h1>
        <p>{{ $report->report_number }} | {{ strtoupper($report->report_type) }} |
            {{ $report->report_date->format('d F Y') }}</p>
    </div>

    <div class="section">
        <div class="section-title">Report Information</div>
        <div class="info-grid">
            <div>
                <div class="info-item">
                    <div class="info-label">Report Number</div>
                    <div class="info-value">{{ $report->report_number }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Report Type</div>
                    <div class="info-value">
                        <span
                            class="badge {{ $report->report_type === 'hipaa' ? 'badge-blue' : ($report->report_type === 'jci' ? 'badge-green' : ($report->report_type === 'iso' ? 'badge-purple' : ($report->report_type === 'regulatory' ? 'badge-orange' : 'badge-gray'))) }}">{{ strtoupper($report->report_type) }}</span>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Report Date</div>
                    <div class="info-value">{{ $report->report_date->format('d/m/Y') }}</div>
                </div>
            </div>
            <div>
                <div class="info-item">
                    <div class="info-label">Reporting Period</div>
                    <div class="info-value">{{ $report->reporting_period_start->format('d/m/Y') }} -
                        {{ $report->reporting_period_end->format('d/m/Y') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span
                            class="badge {{ $report->status === 'draft' ? 'badge-yellow' : ($report->status === 'pending_review' ? 'badge-purple' : 'badge-green') }}">{{ ucfirst(str_replace('_', ' ', $report->status)) }}</span>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Created By</div>
                    <div class="info-value">{{ $report->createdBy?->name ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>

    @if ($report->findings)
        <div class="section">
            <div class="section-title">Findings</div>
            @if (is_array($report->findings))
                @foreach ($report->findings as $index => $finding)
                    <div class="finding-item">
                        <span class="finding-number">{{ $index + 1 }}</span>
                        <div class="finding-text">{{ $finding }}</div>
                    </div>
                @endforeach
            @else
                <div class="finding-item">
                    <div class="finding-text">{{ $report->findings }}</div>
                </div>
            @endif
        </div>
    @endif

    @if ($report->recommendations)
        <div class="section">
            <div class="section-title">Recommendations</div>
            <p style="white-space: pre-line; color: #374151;">{{ $report->recommendations }}</p>
        </div>
    @endif

    @if ($report->notes)
        <div class="section">
            <div class="section-title">Additional Notes</div>
            <p style="white-space: pre-line; color: #374151;">{{ $report->notes }}</p>
        </div>
    @endif

    @if ($report->review_notes)
        <div class="section">
            <div class="section-title">Review Notes</div>
            <p style="white-space: pre-line; color: #374151;">{{ $report->review_notes }}</p>
        </div>
    @endif

    @if ($report->approved_at)
        <div class="section">
            <div class="section-title">Approval Information</div>
            <div class="info-grid">
                <div>
                    <div class="info-item">
                        <div class="info-label">Approved By</div>
                        <div class="info-value">{{ $report->reviewer?->name ?? 'N/A' }}</div>
                    </div>
                </div>
                <div>
                    <div class="info-item">
                        <div class="info-label">Approved At</div>
                        <div class="info-value">{{ $report->approved_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="footer">
        <p>Generated on {{ now()->format('d/m/Y H:i') }} | This is an official compliance document</p>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>
