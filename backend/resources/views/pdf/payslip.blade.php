<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip {{ $payslip->employee->employee_code }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5px; color: #1a1a1a; background: #fff; margin: 0 42pt 34pt 42pt; }
        .serif { font-family: 'DejaVu Serif', serif; }
        .accent-bar { height: 5px; background: #1B3EB6; margin: 0 -42pt 14px -42pt; }
        .header { border-bottom: 1.5px solid #111; padding-bottom: 10px; margin-bottom: 14px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .company-name { font-size: 20px; font-weight: 700; color: #111; letter-spacing: 1px; }
        .company-address { font-size: 8.5px; color: #444; margin-top: 7px; line-height: 1.55; }
        .doc-type-label { font-size: 20px; font-weight: 700; color: #111; text-align: right; letter-spacing: 0.5px; }
        .period-label { font-size: 11px; text-align: right; color: #444; margin-top: 4px; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .info-table td { font-size: 9px; padding: 2px 0; vertical-align: top; }
        .info-label { color: #667; width: 90px; }

        .money-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .money-table th { background: #f2f2f2; text-align: left; padding: 5px 8px; font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.4px; color: #444; border: 1px solid #ccc; }
        .money-table td { padding: 5px 8px; border: 1px solid #ddd; font-size: 9px; }
        .money-table td.amt { text-align: right; }
        .money-table tfoot td { font-weight: 700; background: #f8f8f8; }

        .cols { width: 100%; border-collapse: collapse; }
        .cols > tr > td { vertical-align: top; width: 50%; padding-right: 10px; }

        .net-pay-box { margin-top: 10px; border: 1.5px solid #111; border-radius: 4px; padding: 10px 14px; background: #f7f9ff; }
        .net-pay-box .lbl { font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #444; }
        .net-pay-box .val { font-size: 18px; font-weight: 700; color: #111; float: right; }

        .employer-note { margin-top: 12px; font-size: 8px; color: #667; border-top: 1px solid #eee; padding-top: 6px; }
        .footer { margin-top: 26px; font-size: 7.5px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <div class="accent-bar"></div>

    @php
        $logoSrc = null;
        $branch = $payslip->employee->branch;
        if ($branch?->logo_path) {
            $branchLogoPath = storage_path('app/public/' . $branch->logo_path);
            if (file_exists($branchLogoPath)) {
                $extMimes = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp'];
                $ext = strtolower(pathinfo($branchLogoPath, PATHINFO_EXTENSION));
                $mime = $extMimes[$ext] ?? 'image/png';
                $logoSrc = "data:{$mime};base64," . base64_encode(file_get_contents($branchLogoPath));
            }
        }
        if (!$logoSrc) {
            $logoPath = public_path('images/logo.png');
            $logoSrc = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
        }
        $monthNames = ['','January','February','March','April','May','June','July','August','September','October','November','December'];
        $periodLabel = $monthNames[$payslip->payrollRun->month] . ' ' . $payslip->payrollRun->year;
    @endphp

    <div class="header">
        <table class="header-table">
            <tr>
                <td style="vertical-align:top; width:56%">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" style="height:42px; max-width:190px;" alt="{{ config('app.name', 'MEDRI') }}">
                    @else
                        <div class="company-name serif">{{ strtoupper(config('app.name', 'MEDRI')) }}</div>
                    @endif
                    @if($branch)
                    <div class="company-address">
                        <strong style="color:#222">{{ $branch->name }}</strong>
                        @if($branch->address)<br>{{ $branch->address }}@endif
                    </div>
                    @endif
                </td>
                <td style="vertical-align:top; width:44%">
                    <div class="doc-type-label serif">Payslip</div>
                    <div class="period-label">{{ $periodLabel }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="info-table">
        <tr>
            <td class="info-label">Employee</td>
            <td><strong>{{ $payslip->employee->full_name }}</strong> ({{ $payslip->employee->employee_code }})</td>
            <td class="info-label">Department</td>
            <td>{{ $payslip->employee->department?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="info-label">Designation</td>
            <td>{{ $payslip->employee->designation?->name ?? '—' }}</td>
            <td class="info-label">Join Date</td>
            <td>{{ $payslip->employee->join_date?->format('d/m/Y') ?? '—' }}</td>
        </tr>
        @if($payslip->employee->bank_name)
        <tr>
            <td class="info-label">Bank Account</td>
            <td colspan="3">{{ $payslip->employee->bank_name }} @if($payslip->employee->bank_account_number) — {{ $payslip->employee->bank_account_number }} @endif</td>
        </tr>
        @endif
    </table>

    <table class="cols">
        <tr>
            <td>
                <table class="money-table">
                    <thead><tr><th>Earnings</th><th style="text-align:right">Amount</th></tr></thead>
                    <tbody>
                        <tr><td>Basic Salary</td><td class="amt">{{ number_format($payslip->basic_salary, 2) }}</td></tr>
                        @foreach(($payslip->components ?? []) as $c)
                            @if($c['type'] === 'allowance')
                            <tr><td>{{ $c['name'] }}</td><td class="amt">{{ number_format($c['amount'], 2) }}</td></tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><td>Gross Pay</td><td class="amt">{{ number_format($payslip->gross_pay, 2) }}</td></tr>
                    </tfoot>
                </table>
            </td>
            <td>
                <table class="money-table">
                    <thead><tr><th>Deductions</th><th style="text-align:right">Amount</th></tr></thead>
                    <tbody>
                        @foreach(($payslip->components ?? []) as $c)
                            @if($c['type'] === 'deduction')
                            <tr><td>{{ $c['name'] }}</td><td class="amt">{{ number_format($c['amount'], 2) }}</td></tr>
                            @endif
                        @endforeach
                        @if($payslip->unpaid_leave_days > 0)
                        <tr><td>Unpaid Leave ({{ rtrim(rtrim(number_format($payslip->unpaid_leave_days, 1), '0'), '.') }} day(s))</td><td class="amt">{{ number_format($payslip->unpaid_leave_deduction, 2) }}</td></tr>
                        @endif
                        @if($payslip->epf_employee > 0)
                        <tr><td>EPF Contribution (Employee 8%)</td><td class="amt">{{ number_format($payslip->epf_employee, 2) }}</td></tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr><td>Total Deductions</td><td class="amt">{{ number_format($payslip->total_deductions + $payslip->unpaid_leave_deduction + $payslip->epf_employee, 2) }}</td></tr>
                    </tfoot>
                </table>
            </td>
        </tr>
    </table>

    <div class="net-pay-box">
        <span class="lbl">Net Pay</span>
        <span class="val">Rs. {{ number_format($payslip->net_pay, 2) }}</span>
        <div style="clear:both"></div>
    </div>

    @if($payslip->epf_employer > 0 || $payslip->etf_employer > 0)
    <div class="employer-note">
        Employer contributions (not deducted from net pay): EPF {{ number_format($payslip->epf_employer, 2) }}, ETF {{ number_format($payslip->etf_employer, 2) }}.
    </div>
    @endif

    <div class="footer">This is a system-generated payslip and does not require a signature.</div>
</body>
</html>
