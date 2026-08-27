<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $snapshot->metadata['title'] }}</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 14px;
            font-weight: bold;
            margin: 0 0 2px 0;
            color: #0f172a;
            letter-spacing: 1px;
        }
        .header h2 {
            font-size: 11px;
            font-weight: 600;
            margin: 0 0 2px 0;
            color: #475569;
        }
        .header p {
            font-size: 9px;
            color: #64748b;
            margin: 0;
        }
        .report-title {
            margin: 12px 0 6px 0;
            font-size: 13px;
            font-weight: bold;
            color: #047857;
            text-transform: uppercase;
        }
        .meta-bar {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 15px;
            font-size: 9px;
        }
        .meta-bar table {
            width: 100%;
        }
        .meta-bar td {
            padding: 2px 0;
        }
        .highlights {
            background-color: #f8fafc;
            border-left: 3px solid #10b981;
            padding: 8px 12px;
            margin-bottom: 15px;
        }
        .highlights h4 {
            margin: 0 0 4px 0;
            font-size: 10px;
            color: #065f46;
            text-transform: uppercase;
        }
        .highlights ul {
            margin: 0;
            padding-left: 15px;
            font-size: 9px;
        }
        .highlights li {
            margin-bottom: 2px;
        }
        .metrics-grid {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .metrics-grid td {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            text-align: center;
            width: 25%;
        }
        .metric-label {
            font-size: 8px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
        }
        .metric-value {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 5px;
            border: 1px solid #0f172a;
            text-align: left;
        }
        .data-table td {
            border: 1px solid #cbd5e1;
            padding: 5px;
            font-size: 8.5px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .signatures {
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .signatures td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }
        .sig-line {
            border-bottom: 1px solid #0f172a;
            margin: 35px 15px 5px 15px;
        }
        .sig-name {
            font-weight: bold;
            font-size: 9.5px;
            text-transform: uppercase;
        }
        .sig-title {
            font-size: 8.5px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <!-- Official LGU Header -->
    <div class="header">
        <p>Republic of the Philippines</p>
        <h2>{{ $provinceName }}</h2>
        <h1>{{ $municipalityName }}</h1>
        <p>Municipal Social Welfare and Development Office • eKalinga+ Ayuda Management System</p>
        <div class="report-title">{{ $snapshot->metadata['title'] }}</div>
    </div>

    <!-- Metadata Banner -->
    <div class="meta-bar">
        <table>
            <tr>
                <td><strong>Covered Period:</strong> {{ $snapshot->metadata['date_range_label'] }}</td>
                <td><strong>Program Filter:</strong> {{ $snapshot->metadata['program_label'] }}</td>
                <td><strong>Generated:</strong> {{ $snapshot->metadata['generated_at'] }}</td>
            </tr>
        </table>
    </div>

    <!-- Executive Summary Highlights -->
    @if(!empty($snapshot->highlights))
        <div class="highlights">
            <h4>Executive Summary Highlights</h4>
            <ul>
                @foreach($snapshot->highlights as $bullet)
                    <li>{{ $bullet }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- KPI Metrics -->
    @if(!empty($snapshot->metrics))
        <table class="metrics-grid">
            <tr>
                @foreach($snapshot->metrics as $metric)
                    <td>
                        <div class="metric-label">{{ $metric['label'] }}</div>
                        <div class="metric-value">{{ $metric['value'] }}</div>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    <!-- Typed Detailed Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                @foreach($snapshot->headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($snapshot->rows as $row)
                <tr>
                    @foreach($row as $val)
                        <td>{{ $val }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($snapshot->headers) }}" style="text-align: center; padding: 15px; color: #94a3b8;">
                        No records found for the selected reporting parameters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signature Blocks -->
    <table class="signatures">
        <tr>
            <td>
                <p class="sig-title">Prepared by:</p>
                <div class="sig-line"></div>
                <p class="sig-name">{{ $snapshot->signatures['prepared_by'] ?? 'System Operator' }}</p>
                <p class="sig-title">Ayuda Operations Officer</p>
            </td>
            <td>
                <p class="sig-title">Reviewed by:</p>
                <div class="sig-line"></div>
                <p class="sig-name">{{ $snapshot->signatures['reviewed_by'] ?? 'Grace T. Manalo, CPA' }}</p>
                <p class="sig-title">Municipal Budget Officer / Accountant</p>
            </td>
            <td>
                <p class="sig-title">Approved by:</p>
                <div class="sig-line"></div>
                <p class="sig-name">{{ $snapshot->signatures['approved_by'] ?? 'Hon. Jose Jimmy S. Sagarino' }}</p>
                <p class="sig-title">Municipal Mayor</p>
            </td>
        </tr>
    </table>
</body>
</html>
