<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Expenses Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html {
            margin: 24pt 32pt 28pt 32pt;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #1a1a1a;
            background: #fff;
            margin: 0;
        }

        .header { border-bottom: 1.5px solid #111; padding-bottom: 8px; margin-bottom: 12px; }
        .title { font-size: 17px; font-weight: 700; color: #111; }
        .subtitle { font-size: 8.5px; color: #5a6678; margin-top: 3px; }

        table.items { width: 100%; border-collapse: collapse; border: 1px solid #999; }
        table.items thead th {
            border: 1px solid #999;
            background: #f2f2f2;
            padding: 5px 6px;
            font-size: 8px;
            font-weight: 700;
            text-align: left;
            color: #111;
        }
        table.items tbody td {
            border-left: 1px solid #999;
            border-right: 1px solid #999;
            padding: 4px 6px;
            font-size: 8px;
            vertical-align: top;
            color: #222;
        }
        table.items tbody tr:last-child td { border-bottom: 1px solid #999; }
        .tr { text-align: right; }
        .tc { text-align: center; }

        .status-approved { color: #15803d; font-weight: 700; }
        .status-pending  { color: #b45309; font-weight: 700; }
        .status-rejected { color: #b91c1c; font-weight: 700; }

        .total-row td { border-top: 1.5px solid #111; font-weight: 700; padding-top: 6px; font-size: 9.5px; }

        tr { page-break-inside: avoid; }
        thead { display: table-header-group; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Expenses Report</div>
        <div class="subtitle">
            @if($fromDate || $toDate)
                {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d M Y') : 'Start' }}
                &nbsp;–&nbsp;
                {{ $toDate ? \Carbon\Carbon::parse($toDate)->format('d M Y') : 'Today' }}
                &nbsp;·&nbsp;
            @endif
            Generated {{ now()->format('d M Y, h:i A') }} &nbsp;·&nbsp; {{ $expenses->count() }} expenses
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Date</th>
                <th>Expense #</th>
                <th>Account / Category</th>
                <th>Description</th>
                <th>Reference</th>
                <th>Payment</th>
                <th class="tr">Amount</th>
                <th class="tc">Status</th>
                <th>Submitted By</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $e)
                <tr>
                    <td>{{ optional($e->expense_date)->format('d M Y') }}</td>
                    <td>{{ $e->expense_number }}</td>
                    <td>{{ $e->account?->name ?? $e->category?->name ?? '—' }}</td>
                    <td>{{ $e->description }}</td>
                    <td>{{ $e->reference_number ?? '—' }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $e->payment_method)) }}</td>
                    <td class="tr">{{ number_format((float) $e->amount, 2) }}</td>
                    <td class="tc status-{{ $e->status }}">{{ ucfirst($e->status) }}</td>
                    <td>{{ $e->createdBy?->name ?? '—' }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="6" class="tr">Total</td>
                <td class="tr">{{ number_format((float) $totalAmount, 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
