<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
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

        /* ── LETTERHEAD ACCENT ──────────────────────────────────── */
        .accent-bar {
            height: 5px;
            background: #1B3EB6;
            margin: 0 -42pt 14px -42pt;
        }

        /* ── HEADER ─────────────────────────────────────────── */
        .header {
            border-bottom: 1.5px solid #111;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .header-table { width: 100%; border-collapse: collapse; }

        .company-name {
            font-size: 20px;
            font-weight: 700;
            color: #111;
            letter-spacing: 1px;
            line-height: 1;
        }
        .company-tagline {
            font-size: 8px;
            color: #5a6678;
            letter-spacing: 1.1px;
            margin-top: 3px;
        }
        .company-address {
            font-size: 8.5px;
            color: #444;
            margin-top: 7px;
            line-height: 1.55;
        }

        .inv-type-label {
            font-size: 22px;
            font-weight: 700;
            color: #111;
            text-align: right;
            letter-spacing: 0.5px;
        }
        .inv-meta-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .inv-meta-table th, .inv-meta-table td {
            border: 1px solid #999;
            padding: 4px 8px;
            font-size: 8.5px;
        }
        .inv-meta-table th {
            background: #f2f2f2;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #444;
            font-weight: 700;
            text-align: left;
        }
        .inv-meta-table td { color: #111; font-weight: 600; }

        /* ── STATUS ROW ──────────────────────────────────────── */
        .status-row-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .status-row-table td { padding: 2px 0; vertical-align: middle; }

        /* ── BADGE ───────────────────────────────────────────── */
        .badge {
            display: inline-block;
            vertical-align: middle;
            line-height: 1;
            padding: 3px 9px;
            font-size: 7.5px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            border: 1.2px solid #111;
            color: #111;
            background: #fff;
        }
        .badge-paid, .badge-confirmed { border-style: solid; }
        .badge-partially_paid { border-style: dashed; }
        .badge-draft, .badge-pending { border-style: dotted; }
        .badge-cancelled { background: #ededed; color: #555; border-color: #888; }
        .badge-proforma { border-style: double; border-width: 3px; }

        /* ── META BOXES (Bill To / Invoice Details) ─────────────── */
        .meta-outer {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        .meta-box, .meta-box-right {
            vertical-align: top;
            padding: 8px 10px;
            border: 1px solid #999;
        }
        .meta-gap { width: 10px; }
        .meta-section-label {
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #5a6678;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .meta-primary-name {
            font-size: 11px;
            font-weight: 700;
            color: #111;
        }
        .meta-detail { font-size: 8.5px; color: #333; line-height: 1.5; margin-top: 1px; }
        .detail-inner { width: 100%; border-collapse: collapse; }
        .detail-inner td { padding: 1.5px 0; font-size: 8.5px; }
        .dk { color: #777; }
        .dv { text-align: right; font-weight: 600; color: #111; }

        /* ── ITEMS TABLE — matches the reference layout exactly:
               Item Code | Description | Quantity | Price Each | Amount ── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #999;
            margin-bottom: 10px;
        }
        .items-table thead th {
            border: 1px solid #999;
            background: #f2f2f2;
            padding: 5px 8px;
            font-size: 8.5px;
            text-transform: none;
            font-weight: 700;
            color: #111;
            text-align: left;
        }
        .items-table tbody td {
            border-left: 1px solid #999;
            border-right: 1px solid #999;
            padding: 4px 8px;
            font-size: 9px;
            vertical-align: top;
            color: #222;
        }
        .items-table tbody tr:last-child td { border-bottom: 1px solid #999; }
        .pname { font-weight: 400; color: #111; }
        .pnote { color: #888; font-size: 7.5px; margin-top: 1px; }
        .tr { text-align: right; }
        .tc { text-align: center; }

        /* ── TOTAL ───────────────────────────────────────────── */
        .totals-outer {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        .totals-right { width: 46%; vertical-align: top; }
        .totals-inner { width: 100%; border-collapse: collapse; }
        .totals-inner td { padding: 3px 4px; font-size: 9px; }
        .tl { color: #5a6678; }
        .tv { text-align: right; font-weight: 600; color: #111; }
        .trow-grand td {
            border-top: 1.5px solid #111;
            padding-top: 6px !important;
        }
        .trow-grand .tl { font-size: 15px; font-weight: 700; color: #111; }
        .trow-grand .tv { font-size: 13px; font-weight: 700; color: #111; }
        .trow-balance td { font-weight: 700; color: #111; border-top: 1px solid #333; }

        /* ── FOOTER NOTE BOX ─────────────────────────────────── */
        .footer-note-box {
            border: 1px solid #111;
            padding: 6px 10px;
            margin-bottom: 10px;
            font-size: 8.5px;
            font-weight: 700;
            page-break-inside: avoid;
        }

        /* ── BANK / PAYMENT DETAILS ─────────────────────────── */
        .bank-details { font-size: 8.5px; color: #222; line-height: 1.65; margin-bottom: 8px; page-break-inside: avoid; }

        /* ── NOTES / TERMS (ad-hoc, per invoice) ───────────────── */
        .notes-terms {
            border-left: 2px solid #444;
            padding: 6px 10px;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        .notes-terms h4 {
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #5a6678;
            font-weight: 700;
            margin-bottom: 3px;
        }
        .notes-terms p { font-size: 8.5px; color: #333; line-height: 1.5; }

        /* ── SIGNATURE — 3-way, matches the reference ──────────── */
        .signature-table { width: 100%; border-collapse: collapse; margin-top: 26px; page-break-inside: avoid; }
        .signature-cell { width: 33.33%; text-align: center; vertical-align: bottom; padding-top: 22px; }
        .signature-line { border-top: 1px solid #333; margin: 0 14px; padding-top: 4px; font-size: 8px; font-weight: 700; color: #333; }

        /* ── PAGE BREAK RULES ────────────────────────────────── */
        tr { page-break-inside: avoid; }
        thead { display: table-header-group; }
    </style>
</head>
<body>

    <div class="accent-bar"></div>

    <!-- HEADER -->
    @php
        $logoSrc = null;
        if ($invoice->branch?->logo_path) {
            $branchLogoPath = storage_path('app/public/' . $invoice->branch->logo_path);
            if (file_exists($branchLogoPath)) {
                // Derived from the file extension rather than mime_content_type()/finfo —
                // those need the fileinfo PHP extension, which isn't guaranteed to be
                // enabled on every host (it wasn't on live, which 500'd every PDF that
                // had a branch logo set).
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

        // Per-branch editable invoice-print content, with sensible defaults so
        // a branch that hasn't customized anything still prints correctly.
        // array_key_exists (not ??) on purpose: a key present but set to an
        // empty string means the branch deliberately wants that section blank
        // (e.g. no returns-policy box, no bank/cheque instructions) — only a
        // genuinely absent key falls back to the default text.
        $tpl = $invoice->branch?->settings['invoice_template'] ?? [];
        $footerNote = array_key_exists('footer_note', $tpl)
            ? $tpl['footer_note']
            : 'NOTE: RETURNS WILL NOT BE ACCEPTED AFTER 14 DAYS FROM THE DATE OF DELIVERY. (T & C APPLY)';
        $bankDetails = array_key_exists('bank_details', $tpl)
            ? $tpl['bank_details']
            : 'All cheques should be drawn in favour of "'.strtoupper(config('app.name', 'MEDRI')).'" & crossed "A/C Payee Only"'."\n"
              . 'Please state your account number & invoice number when making payments by cheque';
        $sigInput = $tpl['signature_labels'] ?? [];
        $sigDefaults = ['Prepared By', 'Checked By', 'Customer Signature & Company Seal'];
        $sigLabels = [
            array_key_exists(0, $sigInput) ? $sigInput[0] : $sigDefaults[0],
            array_key_exists(1, $sigInput) ? $sigInput[1] : $sigDefaults[1],
            array_key_exists(2, $sigInput) ? $sigInput[2] : $sigDefaults[2],
        ];
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
                    @if($invoice->branch)
                    <div class="company-address">
                        <strong style="color:#222">{{ $invoice->branch->name }}</strong>
                        @if($invoice->branch->address)<br>{{ $invoice->branch->address }}@endif
                        @if($invoice->branch->phone)<br>Tel: {{ $invoice->branch->phone }}@endif
                        @if($invoice->branch->email)<br>{{ $invoice->branch->email }}@endif
                    </div>
                    @endif
                </td>
                <td style="vertical-align:top; width:44%">
                    <div class="inv-type-label serif">
                        @if($invoice->type === 'proforma') Proforma Invoice
                        @else Invoice @endif
                    </div>
                    <table class="inv-meta-table">
                        <tr>
                            <th style="width:50%">Date</th>
                            <th>Invoice #</th>
                        </tr>
                        <tr>
                            <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                            <td>{{ $invoice->invoice_number }}</td>
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
                <span class="badge badge-{{ $invoice->status }}">
                    {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                </span>
            </td>
            <td style="text-align:right">
                @if($invoice->salesRep ?? $invoice->createdBy)
                    <span style="font-size:8px;color:#5a6678">Rep:&nbsp;</span>
                    <span style="font-size:8.5px;font-weight:700;color:#111">
                        {{ ($invoice->salesRep ?? $invoice->createdBy)?->name }}
                    </span>
                @endif
                @if($invoice->payment_terms_days)
                    &nbsp;&nbsp;
                    <span style="font-size:8px;color:#5a6678">Terms:&nbsp;</span>
                    <span style="font-size:8.5px;font-weight:700;color:#111">{{ $invoice->payment_terms_days }} days</span>
                @endif
            </td>
        </tr>
    </table>

    <!-- BILL TO / INVOICE DETAILS -->
    <table class="meta-outer">
        <tr>
            <td class="meta-box" style="width:48%">
                <div class="meta-section-label">Bill To</div>
                <div class="meta-primary-name">{{ $invoice->customer->name }}</div>
                @if($invoice->customer->company)
                    <div class="meta-detail">{{ $invoice->customer->company }}</div>
                @endif
                @if($invoice->customer->address)
                    <div class="meta-detail">{{ $invoice->customer->address }}</div>
                @endif
                @if($invoice->customer->phone)
                    <div class="meta-detail">Tel: {{ $invoice->customer->phone }}</div>
                @endif
            </td>
            <td class="meta-gap"></td>
            <td class="meta-box-right" style="width:48%">
                <div class="meta-section-label">Invoice Details</div>
                <table class="detail-inner">
                    @if($invoice->due_date)
                    <tr>
                        <td class="dk">Due Date</td>
                        <td class="dv">{{ $invoice->due_date->format('d M Y') }}</td>
                    </tr>
                    @endif
                    @if($invoice->reference_number)
                    <tr>
                        <td class="dk">P.O. Number</td>
                        <td class="dv">{{ $invoice->reference_number }}</td>
                    </tr>
                    @endif
                    @if($invoice->branch)
                    <tr>
                        <td class="dk">Branch</td>
                        <td class="dv">{{ $invoice->branch->name }}</td>
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
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->product_code ?? '—' }}</td>
                <td>
                    <div class="pname">{{ $item->product_name }}</div>
                    @if($item->batch_number)
                        <div class="pnote">Batch: {{ $item->batch_number }}</div>
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
                    @if($invoice->discount_amount > 0)
                    <tr>
                        <td class="tl">Subtotal</td>
                        <td class="tv">{{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="tl">Discount</td>
                        <td class="tv">- {{ number_format($invoice->discount_amount, 2) }}</td>
                    </tr>
                    @endif
                    @if($invoice->tax_amount > 0)
                    <tr>
                        <td class="tl">Tax ({{ $invoice->tax_percent }}%)</td>
                        <td class="tv">{{ number_format($invoice->tax_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="trow-grand">
                        <td class="tl">Total</td>
                        <td class="tv">LKR {{ number_format($invoice->total, 2) }}</td>
                    </tr>
                    @if($invoice->paid_amount > 0)
                    <tr>
                        <td class="tl">Amount Paid</td>
                        <td class="tv">- {{ number_format($invoice->paid_amount, 2) }}</td>
                    </tr>
                    <tr class="trow-balance">
                        <td class="tl">Balance Due</td>
                        <td class="tv">LKR {{ number_format($invoice->balance_due, 2) }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- FOOTER NOTE -->
    @if($footerNote)
    <div class="footer-note-box">{{ $footerNote }}</div>
    @endif

    <!-- BANK / PAYMENT DETAILS -->
    @if($bankDetails)
    <div class="bank-details">{!! nl2br(e($bankDetails)) !!}</div>
    @endif

    <!-- NOTES / TERMS (ad-hoc, per invoice) -->
    @if($invoice->notes || $invoice->terms)
    <div class="notes-terms">
        @if($invoice->notes)
            <h4>Notes</h4>
            <p>{{ $invoice->notes }}</p>
        @endif
        @if($invoice->terms)
            <h4 style="margin-top:6px">Terms &amp; Conditions</h4>
            <p>{{ $invoice->terms }}</p>
        @endif
    </div>
    @endif

    <!-- SIGNATURE -->
    <table class="signature-table">
        <tr>
            <td class="signature-cell">
                <div class="signature-line">{{ $sigLabels[0] }}</div>
            </td>
            <td class="signature-cell">
                <div class="signature-line">{{ $sigLabels[1] }}</div>
            </td>
            <td class="signature-cell">
                <div class="signature-line">{{ $sigLabels[2] }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
