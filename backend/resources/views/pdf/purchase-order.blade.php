<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order {{ $po->po_number }}</title>
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

        .header { border-bottom: 1.5px solid #111; padding-bottom: 10px; margin-bottom: 10px; }
        .header-table { width: 100%; border-collapse: collapse; }

        .company-name { font-size: 20px; font-weight: 700; color: #111; letter-spacing: 1px; line-height: 1; }
        .company-tagline { font-size: 8px; color: #5a6678; letter-spacing: 1.1px; margin-top: 3px; }
        .company-address { font-size: 8.5px; color: #444; margin-top: 7px; line-height: 1.55; }

        .doc-type-label { font-size: 22px; font-weight: 700; color: #111; text-align: right; letter-spacing: 0.5px; }
        .doc-meta-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .doc-meta-table th, .doc-meta-table td { border: 1px solid #999; padding: 4px 8px; font-size: 8.5px; }
        .doc-meta-table th { background: #f2f2f2; text-transform: uppercase; letter-spacing: 0.5px; color: #444; font-weight: 700; text-align: left; }
        .doc-meta-table td { color: #111; font-weight: 600; }

        .status-row-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .status-row-table td { padding: 2px 0; vertical-align: middle; }

        .badge {
            display: inline-block; vertical-align: middle; line-height: 1;
            padding: 3px 9px; font-size: 7.5px; font-weight: 700; letter-spacing: 1.2px;
            text-transform: uppercase; border: 1.2px solid #111; color: #111; background: #fff;
        }
        .badge-received, .badge-approved { border-style: solid; }
        .badge-partially_received { border-style: dashed; }
        .badge-draft, .badge-pending { border-style: dotted; }
        .badge-cancelled { background: #ededed; color: #555; border-color: #888; }

        .meta-outer { width: 100%; border-collapse: collapse; margin-bottom: 10px; page-break-inside: avoid; }
        .meta-box, .meta-box-right { vertical-align: top; padding: 8px 10px; border: 1px solid #999; }
        .meta-gap { width: 10px; }
        .meta-section-label { font-size: 7.5px; text-transform: uppercase; letter-spacing: 1.2px; color: #5a6678; font-weight: 700; margin-bottom: 4px; }
        .meta-primary-name { font-size: 11px; font-weight: 700; color: #111; }
        .meta-detail { font-size: 8.5px; color: #333; line-height: 1.5; margin-top: 1px; }
        .detail-inner { width: 100%; border-collapse: collapse; }
        .detail-inner td { padding: 1.5px 0; font-size: 8.5px; }
        .dk { color: #777; }
        .dv { text-align: right; font-weight: 600; color: #111; }

        .items-table { width: 100%; border-collapse: collapse; border: 1px solid #999; margin-bottom: 10px; }
        .items-table thead th { border: 1px solid #999; background: #f2f2f2; padding: 5px 8px; font-size: 8.5px; font-weight: 700; color: #111; text-align: left; }
        .items-table tbody td { border-left: 1px solid #999; border-right: 1px solid #999; padding: 4px 8px; font-size: 9px; vertical-align: top; color: #222; }
        .items-table tbody tr:last-child td { border-bottom: 1px solid #999; }
        .pname { font-weight: 400; color: #111; }
        .pnote { color: #888; font-size: 7.5px; margin-top: 1px; }
        .tr { text-align: right; }
        .tc { text-align: center; }

        .totals-outer { width: 100%; border-collapse: collapse; margin-bottom: 10px; page-break-inside: avoid; }
        .totals-right { width: 46%; vertical-align: top; }
        .totals-inner { width: 100%; border-collapse: collapse; }
        .totals-inner td { padding: 3px 4px; font-size: 9px; }
        .tl { color: #5a6678; }
        .tv { text-align: right; font-weight: 600; color: #111; }
        .trow-grand td { border-top: 1.5px solid #111; padding-top: 6px !important; }
        .trow-grand .tl { font-size: 15px; font-weight: 700; color: #111; }
        .trow-grand .tv { font-size: 13px; font-weight: 700; color: #111; }
        .trow-balance td { font-weight: 700; color: #111; border-top: 1px solid #333; }

        .notes-terms { border-left: 2px solid #444; padding: 6px 10px; margin-bottom: 10px; page-break-inside: avoid; }
        .notes-terms h4 { font-size: 7.5px; text-transform: uppercase; letter-spacing: 1.2px; color: #5a6678; font-weight: 700; margin-bottom: 3px; }
        .notes-terms p { font-size: 8.5px; color: #333; line-height: 1.5; }

        .signature-table { width: 100%; border-collapse: collapse; margin-top: 26px; page-break-inside: avoid; }
        .signature-cell { width: 33.33%; text-align: center; vertical-align: bottom; padding-top: 22px; }
        .signature-line { border-top: 1px solid #333; margin: 0 14px; padding-top: 4px; font-size: 8px; font-weight: 700; color: #333; }

        tr { page-break-inside: avoid; }
        thead { display: table-header-group; }
    </style>
</head>
<body>

    <div class="accent-bar"></div>

    <!-- HEADER -->
    @php
        $logoSrc = null;
        if ($po->branch?->logo_path) {
            $branchLogoPath = storage_path('app/public/' . $po->branch->logo_path);
            if (file_exists($branchLogoPath)) {
                $extMimes = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp'];
                $ext = strtolower(pathinfo($branchLogoPath, PATHINFO_EXTENSION));
                $mime = $extMimes[$ext] ?? 'image/png';
                $logoSrc = "data:{$mime};base64," . base64_encode(file_get_contents($branchLogoPath));
            }
        }
        if (!$logoSrc) {
            $logoPath = public_path('images/logo.png');
            $logoSrc  = file_exists($logoPath)
                ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
                : null;
        }
    @endphp
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="vertical-align:top; width:56%">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" style="height:42px; max-width:190px;" alt="{{ config('app.name', 'MEDRI') }}">
                    @else
                        <div class="company-name serif">{{ strtoupper(config('app.name', 'MEDRI')) }}</div>
                        <div class="company-tagline">Your Trusted Partner in Medical Supplies</div>
                    @endif
                    @if($po->branch)
                    <div class="company-address">
                        <strong style="color:#222">{{ $po->branch->name }}</strong>
                        @if($po->branch->address)<br>{{ $po->branch->address }}@endif
                        @if($po->branch->phone)<br>Tel: {{ $po->branch->phone }}@endif
                        @if($po->branch->email)<br>{{ $po->branch->email }}@endif
                    </div>
                    @endif
                </td>
                <td style="vertical-align:top; width:44%">
                    <div class="doc-type-label serif">Purchase Order</div>
                    <table class="doc-meta-table">
                        <tr>
                            <th style="width:50%">Date</th>
                            <th>PO #</th>
                        </tr>
                        <tr>
                            <td>{{ $po->order_date->format('d/m/Y') }}</td>
                            <td>{{ $po->po_number }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- STATUS ROW -->
    <table class="status-row-table">
        <tr>
            <td>
                <span class="badge badge-{{ $po->status }}">
                    {{ ucfirst(str_replace('_', ' ', $po->status)) }}
                </span>
            </td>
            <td style="text-align:right">
                @if($po->createdBy)
                    <span style="font-size:8px;color:#5a6678">Prepared by:&nbsp;</span>
                    <span style="font-size:8.5px;font-weight:700;color:#111">{{ $po->createdBy->name }}</span>
                @endif
                @if($po->payment_terms_days)
                    &nbsp;&nbsp;
                    <span style="font-size:8px;color:#5a6678">Terms:&nbsp;</span>
                    <span style="font-size:8.5px;font-weight:700;color:#111">{{ $po->payment_terms_days }} days</span>
                @endif
            </td>
        </tr>
    </table>

    <!-- SUPPLIER / PO DETAILS -->
    <table class="meta-outer">
        <tr>
            <td class="meta-box" style="width:48%">
                <div class="meta-section-label">Supplier</div>
                <div class="meta-primary-name">{{ $po->supplier->name }}</div>
                @if($po->supplier->company)
                    <div class="meta-detail">{{ $po->supplier->company }}</div>
                @endif
                @if($po->supplier->address)
                    <div class="meta-detail">{{ $po->supplier->address }}</div>
                @endif
                @if($po->supplier->phone)
                    <div class="meta-detail">Tel: {{ $po->supplier->phone }}</div>
                @endif
            </td>
            <td class="meta-gap"></td>
            <td class="meta-box-right" style="width:48%">
                <div class="meta-section-label">Order Details</div>
                <table class="detail-inner">
                    @if($po->expected_date)
                    <tr>
                        <td class="dk">Expected Date</td>
                        <td class="dv">{{ $po->expected_date->format('d M Y') }}</td>
                    </tr>
                    @endif
                    @if($po->due_date)
                    <tr>
                        <td class="dk">Due Date</td>
                        <td class="dv">{{ $po->due_date->format('d M Y') }}</td>
                    </tr>
                    @endif
                    @if($po->reference)
                    <tr>
                        <td class="dk">Reference</td>
                        <td class="dv">{{ $po->reference }}</td>
                    </tr>
                    @endif
                    @if($po->branch)
                    <tr>
                        <td class="dk">Branch</td>
                        <td class="dv">{{ $po->branch->name }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- ITEMS TABLE -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:70px">Item Code</th>
                <th>Description</th>
                <th class="tr" style="width:52px">Quantity</th>
                <th class="tr" style="width:70px">Price Each</th>
                <th class="tr" style="width:78px">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($po->items as $item)
            <tr>
                <td>{{ $item->product_code ?? '—' }}</td>
                <td>
                    <div class="pname">{{ $item->product_name }}</div>
                    @if($item->item_type === 'service')
                        <div class="pnote">Service</div>
                    @endif
                </td>
                <td class="tr">
                    {{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}
                </td>
                <td class="tr">{{ number_format($item->unit_price, 2) }}</td>
                <td class="tr">{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- TOTAL -->
    <table class="totals-outer">
        <tr>
            <td style="vertical-align:top"></td>
            <td class="totals-right">
                <table class="totals-inner">
                    @if($po->discount_amount > 0)
                    <tr>
                        <td class="tl">Subtotal</td>
                        <td class="tv">{{ number_format($po->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="tl">Discount</td>
                        <td class="tv">- {{ number_format($po->discount_amount, 2) }}</td>
                    </tr>
                    @endif
                    @if($po->tax_amount > 0)
                    <tr>
                        <td class="tl">Tax</td>
                        <td class="tv">{{ number_format($po->tax_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="trow-grand">
                        <td class="tl">Total</td>
                        <td class="tv">LKR {{ number_format($po->total, 2) }}</td>
                    </tr>
                    @if($po->paid_amount > 0)
                    <tr>
                        <td class="tl">Amount Paid</td>
                        <td class="tv">- {{ number_format($po->paid_amount, 2) }}</td>
                    </tr>
                    <tr class="trow-balance">
                        <td class="tl">Balance Due</td>
                        <td class="tv">LKR {{ number_format($po->balance_due, 2) }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- NOTES / TERMS -->
    @if($po->notes || $po->terms)
    <div class="notes-terms">
        @if($po->notes)
            <h4>Notes</h4>
            <p>{{ $po->notes }}</p>
        @endif
        @if($po->terms)
            <h4 style="margin-top:6px">Terms &amp; Conditions</h4>
            <p>{{ $po->terms }}</p>
        @endif
    </div>
    @endif

    <!-- SIGNATURE -->
    <table class="signature-table">
        <tr>
            <td class="signature-cell">
                <div class="signature-line">Prepared By</div>
            </td>
            <td class="signature-cell">
                <div class="signature-line">Approved By</div>
            </td>
            <td class="signature-cell">
                <div class="signature-line">Supplier Signature &amp; Stamp</div>
            </td>
        </tr>
    </table>

</body>
</html>
