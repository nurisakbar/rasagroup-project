<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Jalan - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .header-table td {
            vertical-align: bottom;
        }
        .logo {
            width: 120px;
            margin-bottom: 15px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-spacing: 0;
        }
        .info-table td {
            vertical-align: top;
            padding: 0;
        }
        .title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 20px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            border: 1px solid #333;
            padding: 6px 8px;
        }
        .items-table th {
            text-align: left;
            background-color: #eaeaea;
            text-transform: uppercase;
            font-size: 11px;
        }
        .footer-table {
            width: 100%;
        }
        .footer-table td {
            vertical-align: top;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td width="50%">
                <img src="{{ public_path('logorasa.png') }}" class="logo" alt="Rasa Group">
                <table cellpadding="0" cellspacing="0" style="margin-top: 10px;">
                    <tr>
                        <td width="100"><strong>Tanggal</strong></td>
                        <td>: {{ $order->created_at->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>No. Surat Jalan</strong></td>
                        <td>: INV-{{ str_replace('INV-', '', $order->order_number) }}</td>
                    </tr>
                </table>
            </td>
            <td width="50%" align="right">
                <table cellpadding="0" cellspacing="0" style="width: 100%; text-align: right;">
                    <tr>
                        <td>
                            <strong>No. Ref</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $order->qad_so_number ?? $order->jubelio_salesorder_no ?? $order->order_number }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    
    <hr style="border-top: 1px solid #333; margin-top: 0; margin-bottom: 15px;">

    <table class="info-table">
        <tr>
            <td width="60%">
                @if(isset($warehouse) && $warehouse)
                    <div style="font-size: 13px; font-weight: bold; margin-bottom: 5px;">{{ $warehouse->name }}</div>
                    <div style="line-height: 1.4;">
                        {{ $warehouse->address }}<br>
                        @if($warehouse->district) Kec. {{ $warehouse->district->name }}, @endif
                        @if($warehouse->city) {{ $warehouse->city->name }}, @endif
                        @if($warehouse->province) {{ $warehouse->province->name }} @endif
                        <br>
                        {{ $warehouse->postal_code }}<br>
                        {{ $warehouse->phone }}
                    </div>
                @else
                    <div style="font-size: 13px; font-weight: bold; margin-bottom: 5px;">PT Multi Citra Rasa (Rasa Group)</div>
                    <div style="line-height: 1.4;">
                    Jl. Mandala Timur No.11, RT.12/RW.4, Tomang,<br>
                    RT.12/RW.4, Kota, Kec. Grogol petamburan,<br>
                    Kota Jakarta Barat, Daerah Khusus Ibukota<br>
                    Jakarta 11440<br>
                    Jakarta<br>
                    Jakarta, 11440<br>
                    Indonesia
                    </div>
                @endif
            </td>
            <td width="40%">
                @php
                    $addressLines = explode("\n", str_replace("\r\n", "\n", $order->shipping_address));
                    $recipientName = array_shift($addressLines);
                    $remainingAddress = implode("\n", $addressLines);
                @endphp
                <div style="font-size: 13px; font-weight: bold; margin-bottom: 5px;">Kepada Yth: {{ $recipientName }}</div>
                <div style="line-height: 1.4;">
                    {!! nl2br(e($remainingAddress)) !!}
                </div>
            </td>
        </tr>
    </table>

    <div class="title">Surat Jalan</div>

    <table class="items-table">
        <thead>
            <tr>
                <th width="5%" style="text-align: center;">NO</th>
                <th width="15%">SKU</th>
                <th width="45%">KETERANGAN</th>
                <th width="15%">Variant</th>
                <th width="10%" style="text-align: center;">QTY</th>
                <th width="10%">UNIT</th>
            </tr>
        </thead>
        <tbody>
            @php $totalQty = 0; @endphp
            @foreach($order->items as $index => $item)
            @php $totalQty += $item->quantity; @endphp
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $item->product->code ?? '-' }}</td>
                <td>{{ $item->product->display_name ?? $item->product->name ?? 'Produk dihapus' }}</td>
                <td>-</td>
                <td style="text-align: center;">{{ $item->quantity }}</td>
                <td>{{ $item->product->unit ?? 'Buah' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="footer-table">
        <tr>
            <td width="70%">
                <strong>Catatan Pembeli:</strong><br>
                <span style="white-space: pre-line;">{{ $order->notes ?? '-' }}</span>
            </td>
            <td width="30%" align="right" style="font-size: 13px;">
                <strong>Total Qty &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $totalQty }}</strong>
            </td>
        </tr>
    </table>
</body>
</html>
