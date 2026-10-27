@php
    $invoice = $invoice ?? \App\Support\ProformaInvoice::fromOrder($order);
    $money = fn ($amount) => 'Rp' . number_format((float) $amount, 0, ',', '.');
    $isPaid = $order->isPaidOrTopApproved();
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ ($isPaid ? 'Invoice ' : 'Proforma Invoice ') . $invoice['invoice_no'] }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 32px 38px 32px 38px;
        }
        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #111827;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        table { border-collapse: collapse; }
        .w-100 { width: 100%; }
        .title {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 2px;
            line-height: 1.15;
            color: #111827;
            text-transform: uppercase;
        }
        .logo { width: 156px; height: auto; margin-top: 16px; }
        .barcode { text-align: right; padding-bottom: 6px; }
        .from {
            width: 250px;
            margin-left: auto;
            text-align: right;
            font-size: 11px;
            line-height: 1.45;
            padding-top: 14px;
            word-wrap: break-word;
        }
        .from strong, .bill-to strong {
            font-weight: 700;
        }
        .bill-to {
            font-size: 11px;
            line-height: 1.45;
        }
        .invoice-no {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.2px;
            line-height: 1.3;
            text-align: right;
            white-space: nowrap;
        }
        .order-date {
            font-size: 12px;
            text-align: right;
            margin-top: 4px;
        }
        .items {
            width: 100%;
            margin-top: 22px;
        }
        .items tr {
            page-break-inside: avoid;
        }
        .items th {
            text-align: left;
            font-weight: 700;
            font-size: 11px;
            padding: 0 6px 8px 6px;
            border-bottom: 1px solid #111827;
        }
        .items td {
            padding: 6px 6px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
            font-size: 11px;
        }
        .items .num, .items .qty, .items .price, .items .total {
            text-align: right;
            white-space: nowrap;
        }
        .items .qty { text-align: center; }
        .items th.num { width: 6%; text-align: left; }
        .items th.product { width: 34%; }
        .items th.qty { width: 12%; text-align: center; }
        .items th.price, .items th.total { width: 16%; text-align: right; }
        .totals {
            width: 46%;
            margin-left: 54%;
            margin-top: 8px;
            page-break-inside: avoid;
        }
        .totals tr {
            page-break-inside: avoid;
        }
        .totals td {
            padding: 5px 6px;
            font-size: 11px;
        }
        .totals .amount { text-align: right; white-space: nowrap; }
        .totals .grand td {
            font-weight: 700;
            font-size: 12px;
            padding-top: 8px;
        }
    </style>
</head>
<body>
    <table class="w-100">
        <tr>
            <td width="50%" valign="top">
                <div class="title">
                    @if($isPaid)
                        INVOICE
                    @else
                        PROFORMA<br>INVOICE
                    @endif
                </div>
                <img src="{{ public_path('logorasa.png') }}" class="logo" alt="Rasa Group">
            </td>
            <td width="50%" valign="top">
                <div class="barcode">{!! \App\Support\InvoiceBarcode::html($invoice['invoice_no']) !!}</div>
                <div class="from">
                    <strong>From</strong><br>
                    @foreach($invoice['from_lines'] as $line)
                        {{ $line }}<br>
                    @endforeach
                </div>
            </td>
        </tr>
    </table>

    <table class="w-100" style="margin-top: 22px;">
        <tr>
            <td width="50%" valign="top" class="bill-to">
                <strong>Bill to</strong><br>
                {{ $invoice['bill_name'] }}<br>
                @foreach($invoice['bill_lines'] as $line)
                    {{ $line }}<br>
                @endforeach
            </td>
            <td width="50%" valign="top">
                <div class="invoice-no">Invoice no: {{ $invoice['invoice_no'] }}</div>
                <div class="order-date">Order date: {{ $invoice['order_date'] }}</div>
                @if($order->sales)
                    <div class="order-date" style="margin-top: 10px; text-align: right;">
                        <strong>Sales:</strong> {{ $order->sales->name }}<br>
                        <strong>Email:</strong> {{ $order->sales->email ?? '-' }}
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th class="num">S.No</th>
                <th class="product">Product</th>
                <th class="qty">Quantity</th>
                <th class="price">Harga katalog</th>
                <th class="price">Harga jual</th>
                <th class="total">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice['items'] as $item)
                <tr>
                    <td class="num">{{ $item['no'] }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td class="qty">{{ $item['quantity'] }}</td>
                    <td class="price">{{ $money($item['unit_price_before'] ?? $item['unit_price']) }}</td>
                    <td class="price">{{ $money($item['unit_price_after'] ?? $item['unit_price']) }}</td>
                    <td class="total">{{ $money($item['total_price']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Tidak ada item pada pesanan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td class="amount">{{ $money($invoice['subtotal_before'] ?? $invoice['subtotal']) }}</td>
        </tr>
        @php
            $totalDiscount = (float) (($invoice['item_discount'] ?? 0) + ($invoice['discount'] ?? 0));
        @endphp
        <tr>
            <td>Diskon</td>
            <td class="amount">{{ $totalDiscount > 0 ? '-' : '' }}{{ $money($totalDiscount) }}</td>
        </tr>
        <tr>
            <td>{{ $invoice['ppn_label'] }}</td>
            <td class="amount">{{ $money($invoice['ppn']) }}</td>
        </tr>
        <tr>
            <td>Ongkos Kirim</td>
            <td class="amount">{{ $money($invoice['shipping'] ?? 0) }}</td>
        </tr>
        <tr class="grand">
            <td>Total</td>
            <td class="amount">{{ $money($invoice['total']) }}</td>
        </tr>
    </table>
</body>
</html>
