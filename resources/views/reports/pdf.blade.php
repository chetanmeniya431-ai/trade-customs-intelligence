<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Helvetica, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 18px; margin-bottom: 2px; }
        .muted { color: #6b7280; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #e5e7eb; padding: 5px 7px; text-align: left; }
        th { background: #f3f4f6; }
        h2 { font-size: 13px; margin-top: 22px; margin-bottom: 6px; border-bottom: 2px solid #0d9488; padding-bottom: 3px; }
        .sev-high, .sev-critical { color: #b91c1c; font-weight: bold; }
        .sev-medium { color: #92400e; }
        .sev-low { color: #6b7280; }
    </style>
</head>
<body>
    <h1>Compliance Report</h1>
    <p class="muted">Generated {{ $generatedAt->format('d M Y, H:i') }} by {{ $generatedBy->name }}</p>

    <h2>Shipments ({{ $shipments->count() }})</h2>
    <table>
        <thead>
            <tr><th>Reference</th><th>Route</th><th>Status</th><th>Declared value</th><th>Filing deadline</th></tr>
        </thead>
        <tbody>
            @foreach ($shipments as $shipment)
                <tr>
                    <td>{{ $shipment->reference }}</td>
                    <td>{{ $shipment->origin_country }} → {{ $shipment->destination_country }}</td>
                    <td>{{ ucwords(str_replace('_',' ',$shipment->status)) }}</td>
                    <td>{{ $shipment->declared_currency }} {{ number_format($shipment->declared_value, 2) }}</td>
                    <td>{{ $shipment->filing_deadline?->format('d M Y H:i') ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Open findings ({{ $findings->count() }})</h2>
    <table>
        <thead>
            <tr><th>Shipment</th><th>Type</th><th>Severity</th><th>Description</th></tr>
        </thead>
        <tbody>
            @foreach ($findings as $finding)
                <tr>
                    <td>{{ $finding->shipment->reference }}</td>
                    <td>{{ ucwords(str_replace('_',' ',$finding->finding_type)) }}</td>
                    <td class="sev-{{ $finding->severity }}">{{ ucfirst($finding->severity) }}</td>
                    <td>{{ $finding->description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
