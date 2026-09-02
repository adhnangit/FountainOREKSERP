<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profit & Loss - <?php echo e($data['from_date']); ?> to <?php echo e($data['to_date']); ?></title>
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

        .section-title {
            font-size: 10.5px; font-weight: 700; color: #1B3EB6; text-transform: uppercase;
            letter-spacing: 0.5px; margin: 16px 0 6px 0; border-bottom: 1px solid #1B3EB6; padding-bottom: 3px;
        }
        .section-title.expense { color: #b45309; border-bottom-color: #b45309; }

        table.data { width: 100%; border-collapse: collapse; }
        table.data td { border-bottom: 1px solid #eee; padding: 5px 8px; font-size: 9px; color: #111; }
        table.data td.num { text-align: right; font-variant-numeric: tabular-nums; }
        table.data tbody tr:nth-child(even) { background: #fafafa; }

        .subtotal-row td { border-top: 1.5px solid #333; font-weight: 700; padding-top: 6px; }

        .summary-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .summary-table td { padding: 6px 8px; font-size: 10px; }
        .summary-table td.num { text-align: right; font-variant-numeric: tabular-nums; }
        .summary-table .income td { color: #15803d; }
        .summary-table .expense td { color: #b45309; }
        .net-row td {
            border-top: 2px solid #111; border-bottom: 2px solid #111;
            font-size: 12px; font-weight: 700; padding: 8px;
        }
        .net-positive { color: #15803d; }
        .net-negative { color: #dc2626; }

        .footer { position: fixed; bottom: -24pt; left: 0; right: 0; font-size: 7.5px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <div class="accent-bar"></div>

    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width:55%">
                    <div class="company-name serif"><?php echo e(strtoupper(config('app.name', 'MEDRI'))); ?></div>
                    <div class="company-sub"><?php echo e($branchName); ?></div>
                </td>
                <td style="width:45%">
                    <div class="report-title">PROFIT &amp; LOSS</div>
                    <div class="report-meta"><?php echo e(\Carbon\Carbon::parse($data['from_date'])->format('d M Y')); ?> — <?php echo e(\Carbon\Carbon::parse($data['to_date'])->format('d M Y')); ?></div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Income</div>
    <table class="data">
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $data['income_accounts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td style="width:70%"><?php echo e($a['name']); ?> <span style="color:#999">(<?php echo e($a['code']); ?>)</span></td>
                    <td class="num" style="width:30%"><?php echo e(number_format($a['normal_balance'] === 'credit' ? $a['balance'] : -$a['balance'], 2)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td style="color:#999">No income recorded for this period.</td></tr>
            <?php endif; ?>
            <tr class="subtotal-row">
                <td>Total Income</td>
                <td class="num">Rs. <?php echo e(number_format($data['total_income'], 2)); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="section-title expense">Expenses</div>
    <table class="data">
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $data['expense_accounts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td style="width:70%"><?php echo e($a['name']); ?> <span style="color:#999">(<?php echo e($a['code']); ?>)</span></td>
                    <td class="num" style="width:30%"><?php echo e(number_format($a['normal_balance'] === 'debit' ? $a['balance'] : -$a['balance'], 2)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td style="color:#999">No expenses recorded for this period.</td></tr>
            <?php endif; ?>
            <tr class="subtotal-row">
                <td>Total Expenses</td>
                <td class="num">Rs. <?php echo e(number_format($data['total_expenses'], 2)); ?></td>
            </tr>
        </tbody>
    </table>

    <table class="summary-table">
        <tr class="income"><td>Total Income</td><td class="num">Rs. <?php echo e(number_format($data['total_income'], 2)); ?></td></tr>
        <tr class="expense"><td>Total Expenses</td><td class="num">(Rs. <?php echo e(number_format($data['total_expenses'], 2)); ?>)</td></tr>
        <tr class="net-row">
            <td><?php echo e($data['net_profit'] >= 0 ? 'NET PROFIT' : 'NET LOSS'); ?></td>
            <td class="num <?php echo e($data['net_profit'] >= 0 ? 'net-positive' : 'net-negative'); ?>">Rs. <?php echo e(number_format(abs($data['net_profit']), 2)); ?></td>
        </tr>
    </table>

    <div class="footer">Generated <?php echo e(now()->format('d M Y, h:i A')); ?> · <?php echo e(config('app.name', 'MEDRI')); ?> ERP</div>
</body>
</html>
<?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\pdf\profit-loss.blade.php ENDPATH**/ ?>