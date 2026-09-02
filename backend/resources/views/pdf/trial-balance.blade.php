<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trial Balance - {{ $data['as_of'] }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9.5px;
            color: #1a1a1a;
            background: #fff;
            margin: 0 42pt 34pt 42pt;
        }
        .serif { font-family: 'DejaVu Serif', serif; }

        .accent-bar { height: 5px; background: #1B3EB6; margin: 0 -42pt 14px -42pt; }

        .header { border-bottom: 1.5px solid #111; padding-bottom: 10px; margin-bottom: 14px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .company-name { font-size: 20px; font-weight: 700; color: #111; letter-spacing: 1px; }
        .company-sub { font-size: 8.5px; color: #444; margin-top: 4px; }
        .report-title { font-size: 18px; font-weight: 700; color: #111; text-align: right; letter-spacing: 0.5px; }
        .report-meta { font-size: 9px; color: #555; text-align: right; margin-top: 4px; }

        table.data { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.data th {
            background: #f2f2f2;
            border: 1px solid #999;
            padding: 5px 8px;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #444;
            font-weight: 700;
            text-align: left;
        }
        table.data td {
            border: 1px solid #ccc;
            padding: 5px 8px;
            font-size: 9px;
            color: #111;
        }
        table.data td.num, table.data th.num { text-align: right; font-variant-numeric: tabular-nums; }
        table.data tbody tr:nth-child(even) { background: #fafafa; }

        .group-row td { background: #eef1f7; font-weight: 700; color: #1B3EB6; font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.4px; }

        .totals-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .totals-table td {
            border-top: 2px solid #111;
            border-bottom: 2px solid #111;
            padding: 7px 8px;
            font-size: 10.5px;
            font-weight: 700;
        }
        .totals-table td.num { text-align: right; font-variant-numeric: tabular-nums; }

        .balance-check { margin-top: 10px; font-size: 9.5px; font-weight: 700; }
        .balance-ok { color: #15803d; }
        .balance-bad { color: #dc2626; }

        .footer { position: fixed; bottom: -24pt; left: 0; right: 0; font-size: 7.5px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <div class="accent-bar"></div>

    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width:55%">
                    <div class="company-name serif">{{ strtoupper(config('app.name', 'MEDRI')) }}</div>
                    <div class="company-sub">{{ $branchName }}</div>
                </td>
                <td style="width:45%">
                    <div class="report-title">TRIAL BALANCE</div>
                    <div class="report-meta">As of {{ \Carbon\Carbon::parse($data['as_of'])->format('d M Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th style="width:12%">Code</th>
                <th style="width:38%">Account</th>
                <th style="width:20%">Group</th>
                <th class="num" style="width:15%">Debit</th>
                <th class="num" style="width:15%">Credit</th>
            </tr>
        </thead>
        <tbody>
            @php $currentGroup = null; @endphp
            @foreach($data['accounts'] as $a)
                @if($a['group'] !== $currentGroup)
                    @php $currentGroup = $a['group']; @endphp
                    <tr class="group-row"><td colspan="5">{{ $currentGroup ?? 'Ungrouped' }}</td></tr>
                @endif
                <tr>
                    <td>{{ $a['code'] }}</td>
                    <td>{{ $a['name'] }}</td>
                    <td>{{ $a['group'] }}</td>
                    <td class="num">{{ $a['debit_balance'] > 0 ? number_format($a['debit_balance'], 2) : '—' }}</td>
                    <td class="num">{{ $a['credit_balance'] > 0 ? number_format($a['credit_balance'], 2) : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td style="width:70%">TOTAL</td>
            <td class="num" style="width:15%">Rs. {{ number_format($data['total_debit'], 2) }}</td>
            <td class="num" style="width:15%">Rs. {{ number_format($data['total_credit'], 2) }}</td>
        </tr>
    </table>

    <div class="balance-check {{ $data['balanced'] ? 'balance-ok' : 'balance-bad' }}">
        {{ $data['balanced'] ? '✓ Debits equal credits — the ledger is in balance.' : '✗ Out of balance — debits and credits do not match.' }}
    </div>

    <div class="footer">Generated {{ now()->format('d M Y, h:i A') }} · {{ config('app.name', 'MEDRI') }} ERP</div>
</body>
</html>
