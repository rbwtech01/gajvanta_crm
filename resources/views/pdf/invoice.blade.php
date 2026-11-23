<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoiceNo }} — RAJA BHAI ENTERPRISES</title>
    <style>
        @page {
            size: A4;
            margin: 18mm 16mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #1f2937;
            font-size: 11px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td,
        th {
            vertical-align: top;
        }

        .muted {
            color: #9ca3af;
        }

        .strong {
            font-weight: bold;
            color: #111827;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .uppercase {
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .logo-box {
            width: 64px;
            height: 64px;
            background-color: #166534;
            color: #ffffff;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            border-radius: 6px;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #111827;
            padding-top: 8px;
        }

        .company-tag {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-top: 2px;
        }

        .invoice-title {
            font-size: 26px;
            font-weight: bold;
            color: #166534;
        }

        .invoice-sub {
            font-size: 11px;
            color: #9ca3af;
            padding-top: 2px;
        }

        .section-label {
            font-size: 9px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 6px;
        }

        hr.rule {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 18px 0;
        }

        /* Summary banner */
        .banner-table td {
            padding: 12px 16px;
            color: #ffffff;
        }

        .banner-green {
            background-color: #15803d;
        }

        .banner-green-dark {
            background-color: #166534;
        }

        .banner-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #dcfce7;
        }

        .banner-value {
            font-size: 15px;
            font-weight: bold;
            padding-top: 3px;
        }

        /* Badges */
        .badge {
            display: inline-block;
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            padding: 3px 10px;
            font-size: 9px;
            font-weight: bold;
            margin: 0 6px 6px 0;
        }

        /* Items table */
        .items-table th {
            border-bottom: 2px solid #111827;
            padding: 6px 4px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #111827;
            text-align: left;
        }

        .items-table td {
            border-bottom: 1px solid #e5e7eb;
            padding: 10px 4px;
            font-size: 11px;
        }

        .totals-table td {
            padding: 4px 0;
            font-size: 11px;
        }

        .totals-final td {
            border-top: 2px solid #111827;
            padding-top: 10px;
            font-size: 14px;
            font-weight: bold;
            color: #111827;
        }

        .sign-line {
            border-bottom: 1px solid #9ca3af;
            height: 30px;
            width: 180px;
        }

        .footer-rule {
            border-top: 1px solid #111827;
            margin-top: 8px;
            padding-top: 8px;
        }
    </style>
</head>

<body>

    {{-- ==================== HEADER ==================== --}}
    <table>
        <tr>
            <td style="width: 60%;">
                @if (file_exists(public_path('assets/logo.png')))
                    <img src="{{ public_path('assets/logo.png') }}" alt="RAJA BHAI ENTERPRISES" style="height:64px;">
                @else
                    <table style="width:auto;">
                        <tr>
                            <td class="logo-box">RAJA</td>
                        </tr>
                    </table>
                @endif
                <div class="company-name">RAJA BHAI ENTERPRISES</div>
                <div class="company-tag">Agricultural Machinery &amp; Equipment</div>
            </td>
            <td class="right" style="width: 40%;">
                <div class="invoice-title">Invoice #{{ $invoiceNo }}</div>
            </td>
        </tr>
    </table>

    <hr class="rule">

    {{-- ==================== BILL TO / DATES ==================== --}}
    <table>
        <tr>
            <td style="width: 55%;">
                <div class="section-label">Bill To</div>
                <div class="strong">{{ $customer->customer_name }}</div>
                <div>{{ $customer->address }}</div>
                <div>{{ $customer->district }}, {{ $customer->state }} {{ $customer->pincode }}</div>
                <div>Mobile: {{ $customer->mobile }}</div>
            </td>
            <td class="right" style="width: 45%;">
                <table style="width: auto; float: right;">
                    <tr>
                        <td class="muted right" style="padding-right: 10px;">Issue date:</td>
                        <td class="strong">{{ $customer->booking_date }}</td>
                    </tr>
                    <tr>
                        <td class="muted right" style="padding-right: 10px;">Generated on:</td>
                        <td class="strong">{{ now()->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <td class="muted right" style="padding-right: 10px;">GSTIN:</td>
                        <td class="strong">03ABGFG2951L1Z2</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ==================== SUMMARY BANNER ==================== --}}
    <table class="banner-table" style="margin-top: 24px;">
        <tr>
            <td class="banner-green" style="width: 25%;">
                <div class="banner-label">Invoice No.</div>
                <div class="banner-value">#{{ $invoiceNo }}</div>
            </td>
            <td class="banner-green" style="width: 25%; border-left: 1px solid #16a34a;">
                <div class="banner-label">Issue Date</div>
                <div class="banner-value">
                    {{ optional($invoice->invoice_date ?? now())->format('d/m/Y') ?? now()->format('d/m/Y') }}</div>
            </td>
            <td class="banner-green" style="width: 25%; border-left: 1px solid #16a34a;">
                <div class="banner-label">Machine Model</div>
                <div class="banner-value">{{ $customer->machineModel->name ?? '—' }}</div>
            </td>
            <td class="banner-green-dark" style="width: 25%;">
                <div class="banner-label">Balance Due</div>
                <div class="banner-value">Rs. {{ number_format($customer->price - $customer->advance_payment, 2) }}
                </div>
            </td>
        </tr>
    </table>

    {{-- ==================== MACHINE INFORMATION ==================== --}}
    <table style="margin-top: 20px;">
        <tr>
            <td style="width: 50%;">
                <div class="muted">Category</div>
                <div class="strong">{{ $customer->category->name ?? '—' }}</div>
            </td>
            <td style="width: 50%;">
                <div class="muted">Machine Model</div>
                <div class="strong">{{ $customer->machineModel->name ?? '—' }}</div>
            </td>
        </tr>
    </table>

    {{-- @if (isset($attachments) && count($attachments))
        <div style="margin-top: 14px;">
            <div class="muted" style="margin-bottom: 6px;">Selected Attachments</div>
            <div>
                @foreach ($attachments as $attachment)
                    <span class="badge">{{ $attachment }}</span>
                @endforeach
            </div>
        </div>
    @endif --}}

    {{-- ==================== ITEMS TABLE ==================== --}}
    {{-- <table class="items-table" style="margin-top: 24px;">
        <tr>
            <th style="width: 46%;">Description</th>
            <th class="center" style="width: 14%;">Quantity</th>
            <th class="right" style="width: 20%;">Unit price (Rs.)</th>
            <th class="right" style="width: 20%;">Amount (Rs.)</th>
        </tr>
        <tr>
            <td>
                @if (isset($customer->machineModel->name))
                    <div style="font-size: 14px; margin-top: 2px;">{{ $customer->machineModel->name }}</div>
                @endif
            </td>
            <td class="center">{{ $customer->quantity }}</td>
            <td class="right">{{ number_format($customer->price, 2) }}</td>
            <td class="right">{{ number_format($customer->price, 2) }}</td>
        </tr>
    </table> --}}
    {{-- ==================== ITEMS TABLE ==================== --}}
    <table class="items-table" style="margin-top: 24px;">
        <tr>
            <th style="width: 46%;">Description</th>
            <th class="center" style="width: 14%;">Quantity</th>
            <th class="right" style="width: 20%;">Unit price (Rs.)</th>
            <th class="right" style="width: 20%;">Amount (Rs.)</th>
        </tr>

        {{-- Main Machine/Product --}}
        <tr>
            <td>
                @if (isset($customer->machineModel->name))
                    <div style="font-size: 14px; margin-top: 2px;">
                        {{ $customer->machineModel->name }}
                    </div>
                @endif
            </td>

            <td class="center">
                {{ $customer->quantity }}
            </td>

            <td class="right">
                {{ number_format($customer->price, 2) }}
            </td>

            <td class="right">
                {{ number_format($customer->price, 2) }}
            </td>
        </tr>

        {{-- Attachments --}}
        @if (!empty($attachments))
            @foreach ($attachments as $attachment)
                <tr>
                    <td>
                        <div style="padding-left: 12px; color: #6b7280;">
                            • {{ $attachment }}
                        </div>
                    </td>

                    {{-- Same quantity as main product --}}
                    <td class="center">
                        {{ $customer->quantity }}
                    </td>

                    {{-- Attachments have no separate price --}}
                    <td class="right">
                        -
                    </td>

                    {{-- Attachments have no separate amount --}}
                    <td class="right">
                        -
                    </td>
                </tr>
            @endforeach
        @endif
    </table>

    {{-- Totals --}}
    <table style="margin-top: 12px;">
        <tr>
            <td style="width: 60%;"></td>
            <td style="width: 40%;">
                <table class="totals-table">
                    <tr>
                        <td class="muted">Subtotal:</td>
                        <td class="right strong">Rs. {{ number_format($customer->price, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="muted">Advance Paid:</td>
                        <td class="right strong">Rs. {{ number_format($customer->advance_payment, 2) }}</td>
                    </tr>
                    <tr class="totals-final">
                        <td>Balance Due:</td>
                        <td class="right">Rs. {{ number_format($customer->price - $customer->advance_payment, 2) }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ==================== CUSTOMER NOTES ==================== --}}
    {{-- <div style="margin-top: 26px;">
    <div class="section-label">Customer Notes</div>
    <div class="muted">{{ $invoice->remarks ?? 'No additional remarks.' }}</div>
  </div> --}}

    {{-- ==================== TERMS & CONDITIONS ==================== --}}
    <div style="margin-top: 18px;">
        <div class="section-label">Terms &amp; Conditions</div>
        <div class="muted">1. We are not responsible for Open Box Delivery.</div>
        <div class="muted">2. Machine once delivered cannot be exchanged without prior approval.</div>
        <div class="muted">3. Warranty applies as per company policy.</div>
        <div class="muted">4. This invoice is generated electronically and does not require a physical signature to be
            valid.</div>
    </div>

    {{-- ==================== SIGNATURE ==================== --}}
    <table style="margin-top: 46px;">
        <tr>
            <td class="right" style="width: 40%;">
                <div class="strong" style="margin: 0 0 4px 0;">
                    Issued by, signature:
                </div>

                <div
                    style="
        font-size: 14px;
        font-weight: bold;
        text-decoration: underline;
        margin: 0;
        padding: 0;
        line-height: 1.2;
    ">
                    RAJA BHAI ENTERPRISES
                </div>

                <div class="muted"
                    style="
        margin: 3px 0 0 0;
        padding: 0;
        line-height: 1.2;
    ">
                    Digital Authorized Signature
                </div>
            </td>
        </tr>
    </table>

    {{-- ==================== FOOTER ==================== --}}
    <div class="footer-rule" style="margin-top: 36px;">
        <table>
            <tr>
                <td style="width: 33%;">Ph: {{ $companyPhone ?? '+91-9815229560' }}</td>
                <td class="center" style="width: 34%;">Web: {{ 'https://rajabhaienterprises.com' }}</td>
                <td class="right" style="width: 33%;">Email: {{ 'support@rajabahienterprises.co.in' }}</td>
            </tr>
        </table>
        <div class="muted" style="margin-top: 8px;">
            RAJA BHAI ENTERPRISES —
            {{ 'Raja Bhai Enterprises, Ludhiana - Malerkotla Rd, Opp. Balbir Dhaba (141117), Nanakpur Jagera, Punjab, India' }}
        </div>
    </div>

</body>

</html>
