<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Balance Sheet - {{ $data['as_of'] }}</title>
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

        .columns { width: 100%; border-collapse: collapse; }
        .columns > tr > td { vertical-align: top; width: 50%; padding-right: 14px; }
        .columns > tr > td + td { padding-right: 0; padding-left: 14px; border-left: 1px solid #ddd; }

        .section-title {
            font-size: 10.5px; font-weight: 700; color: #1B3EB6; text-transform: uppercase;
            letter-spacing: 0.5px; margin: 0 0 6px 0; border-bottom: 1px solid #1B3EB6; padding-bottom: 3px;
        }
        .section-title.liab { color: #b45309; border-bottom-color: #b45309; }
        .section-title.equity { color: #6d28d9; border-bottom-color: #6d28d9; margin-top: 16px; }

        table.data { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        table.data td { border-bottom: 1px solid #eee; padding: 4px 6px; font-size: 8.8px; color: #111; }
        table.data td.num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }

        .subtotal-row td { border-top: 1.5px solid #333; font-weight: 700; padding-top: 5px; }

        .grand-table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        .grand-table td {
            border-top: 2px solid #111; border-bottom: 2px solid #111;
            padding: 7px 8px; font-size: 10.5px; font-weight: 700;
        }
        .grand-table td.num { text-align: right; font-variant-numeric: tabular-nums; }

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
                    <div class="report-title">BALANCE SHEET</div>
                    <div class="report-meta">As of {{ \Carbon\Carbon::parse($data['as_of'])->format('d M Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="columns">
        <tr>
            <td>
                <div class="section-title">Assets</div>
                <table class="data">
                    <tbody>
                        @forelse($data['asset_accounts'] as $a)
                            <tr>
                                <td style="width:70%">{{ $a['name'] }}</td>
                                <td class="num" style="width:30%">{{ number_format($a['balance'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td style="color:#999">No asset accounts.</td></tr>
                        @endforelse
                        <tr class="subtotal-row">
                            <td>Total Assets</td>
                            <td class="num">Rs. {{ number_format($data['total_assets'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </td>
            <td>
                <div class="section-title liab">Liabilities</div>
                <table class="data">
                    <tbody>
                        @forelse($data['liability_accounts'] as $a)
                            <tr>
                                <td style="width:70%">{{ $a['name'] }}</td>
                                <td class="num" style="width:30%">{{ number_format($a['balance'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td style="color:#999">No liability accounts.</td></tr>
                        @endforelse
                        <tr class="subtotal-row">
                            <td>Total Liabilities</td>
                            <td class="num">Rs. {{ number_format($data['total_liabilities'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="section-title equity">Equity</div>
                <table class="data">
                    <tbody>
                        @forelse($data['equity_accounts'] as $a)
                            <tr>
                                <td style="width:70%">{{ $a['name'] }}</td>
                                <td class="num" style="width:30%">{{ number_format($a['balance'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td style="color:#999">No equity accounts.</td></tr>
                        @endforelse
                        <tr>
                            <td style="width:70%">Retained Earnings (all-time)</td>
                            <td class="num" style="width:30%">{{ number_format($data['retained_earnings'], 2) }}</td>
                        </tr>
                        <tr class="subtotal-row">
                            <td>Total Equity</td>
                            <td class="num">Rs. {{ number_format($data['total_equity'] + $data['retained_earnings'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <table class="grand-table">
        <tr>
            <td style="width:50%">TOTAL ASSETS</td>
            <td class="num" style="width:50%">Rs. {{ number_format($data['total_assets'], 2) }}</td>
        </tr>
        <tr>
            <td>TOTAL LIABILITIES + EQUITY</td>
            <td class="num">Rs. {{ number_format($data['total_liabilities_equity'], 2) }}</td>
        </tr>
    </table>

    @php $diff = round($data['total_assets'] - $data['total_liabilities_equity'], 2); @endphp
    <div class="balance-check {{ abs($diff) < 0.01 ? 'balance-ok' : 'balance-bad' }}">
        {{ abs($diff) < 0.01 ? '✓ Assets equal Liabilities + Equity — the books balance.' : '✗ Out of balance by Rs. ' . number_format(abs($diff), 2) . '.' }}
    </div>

    <div class="footer">Generated {{ now()->format('d M Y, h:i A') }} · {{ config('app.name', 'MEDRI') }} ERP</div>
</body>
</html>
