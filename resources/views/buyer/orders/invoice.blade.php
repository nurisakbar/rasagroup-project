<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
        }
        .header {
            width: 100%;
            margin-bottom: 30px;
        }
        .header table {
            width: 100%;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #6A1B1B;
            text-transform: uppercase;
        }
        .invoice-title {
            text-align: right;
            font-size: 24px;
            color: #777;
        }
        .info-section {
            width: 100%;
            margin-bottom: 30px;
        }
        .info-section table {
            width: 100%;
        }
        .info-section td {
            vertical-align: top;
            width: 50%;
        }
        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            color: #777;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background: #f9f9f9;
            border-bottom: 2px solid #eee;
            text-align: left;
            padding: 10px;
            font-weight: bold;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .total-section {
            width: 100%;
        }
        .total-section table {
            width: 40%;
            margin-left: 60%;
        }
        .total-section td {
            padding: 5px 10px;
        }
        .total-row {
            font-weight: bold;
            font-size: 14px;
            color: #6A1B1B;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #777;
            font-size: 10px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .status-paid { background: #e6fcf5; color: #0ca678; }
        .status-pending { background: #fff9db; color: #f08c00; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <table>
                <tr>
                    <td class="logo">Rasa Group</td>
                    <td class="invoice-title">INVOICE</td>
                </tr>
                <tr>
                    <td>
                        Order #{{ $order->order_number }}<br>
                        Tanggal: {{ $order->created_at->format('d/m/Y') }}
                    </td>
                    <td style="text-align: right;">
                        Status Pembayaran: 
                        <span class="status-badge {{ $order->payment_status === 'paid' ? 'status-paid' : 'status-pending' }}">
                            {{ strtoupper($order->payment_status) }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="info-section">
            <table>
                <tr>
                    <td>
                        <div class="section-title">Penerima</div>
                        <strong>{{ $order->user->name }}</strong><br>
                        {!! nl2br(e($order->shipping_address)) !!}
                    </td>
                    <td>
                        <div class="section-title">Pengirim</div>
                        <strong>{{ $order->sourceWarehouse->name ?? 'Rasa Group Central' }}</strong><br>
                        @if($order->sourceWarehouse)
                            {{ $order->sourceWarehouse->address }}<br>
                            {{ $order->sourceWarehouse->full_location }}
                        @else
                            Jl. Raya Industri No. 1, Jakarta
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="info-section">
            <table>
                <tr>
                    <td>
                        <div class="section-title">Metode Pembayaran</div>
                        {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}
                    </td>
                    <td>
                        <div class="section-title">Pengiriman</div>
                        {{ $order->expedition->name ?? '-' }} ({{ $order->expedition_service ?? 'Standard' }})
                    </td>
                </tr>
            </table>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th style="text-align: center;">Harga</th>
                    <th style="text-align: center;">Jumlah</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->product->display_name }}</strong><br>
                            <span style="color: #777; font-size: 10px;">SKU: {{ $item->sku ?? '-' }}</span>
                        </td>
                        <td style="text-align: center;">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td style="text-align: center;">{{ $item->quantity }} {{ $item->product->unit ?? 'pcs' }}</td>
                        <td style="text-align: right;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-section">
            <table>
                <tr>
                    <td>Subtotal</td>
                    <td style="text-align: right;">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                </tr>
                @if($order->discount_amount > 0)
                <tr>
                    <td>Diskon ({{ $order->discount_percent }}%)</td>
                    <td style="text-align: right; color: red;">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr>
                    <td>Ongkos Kirim</td>
                    <td style="text-align: right;">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td style="text-align: right;">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        @if($order->notes)
        <div style="margin-top: 20px; padding: 10px; background: #f9f9f9; border-radius: 4px;">
            <strong>Catatan:</strong><br>
            {{ $order->notes }}
        </div>
        @endif

        <div class="footer">
            Terima kasih telah berbelanja di Rasa Group.<br>
            Halaman ini adalah bukti transaksi yang sah.
        </div>
    </div>
</body>
</html>
