<!DOCTYPE html>
<html>
<head>
    <title>Konfirmasi Pesanan - {{ $order->order_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2>Terima Kasih Atas Pesanan Anda!</h2>
    <p>Halo {{ $order->user->name ?? 'Pelanggan' }},</p>
    <p>Pesanan Anda dengan nomor <strong>{{ $order->order_number }}</strong> telah berhasil kami terima dan sedang menunggu pembayaran.</p>
    
    <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <h3 style="margin-top: 0;">Detail Pesanan</h3>
        <p><strong>Total Tagihan:</strong> Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
        <p><strong>Metode Pembayaran:</strong> {{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}</p>
        @if($order->virtual_account_no)
            <p><strong>Nomor VA / Pembayaran:</strong> {{ $order->virtual_account_no }}</p>
        @endif
        @if($order->faspay_redirect_url)
            <p><strong>Link Pembayaran:</strong> <a href="{{ $order->faspay_redirect_url }}">Klik disini untuk membayar</a></p>
        @endif
    </div>

    @php
        $breakdown = \App\Support\OrderLineBreakdown::fromOrder($order);
        $rp = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        $th = 'text-align: right; padding: 8px 6px; border-bottom: 2px solid #333; font-size: 12px;';
        $td = 'text-align: right; padding: 8px 6px; border-bottom: 1px solid #eee; font-size: 13px; white-space: nowrap; vertical-align: top;';
    @endphp
    @if($breakdown['reliable'])
        <h3 style="margin-bottom: 8px;">Rincian Produk</h3>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="{{ $th }} text-align: left;">Produk</th>
                    <th style="{{ $th }}">Diskon</th>
                    @if($breakdown['uses_ppn'])
                        <th style="{{ $th }}">{{ $breakdown['tax_label'] }}</th>
                    @endif
                    <th style="{{ $th }}">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($breakdown['lines'] as $line)
                    <tr>
                        <td style="{{ $td }} text-align: left; white-space: normal;">
                            <strong>{{ $line['name'] }}</strong><br>
                            <span style="color: #777; font-size: 12px;">{{ $line['quantity_label'] }} × {{ $rp($line['unit_price_incl']) }} ({{ $breakdown['uses_ppn'] ? 'termasuk PPN' : 'tanpa PPN' }})</span>
                        </td>
                        <td style="{{ $td }}">
                            @if($line['discount_amount'] > 0)
                                <span style="color: #c0392b;">−{{ $rp($line['discount_amount']) }}</span><br>
                                <span style="color: #777; font-size: 12px;">{{ rtrim(rtrim(number_format($line['discount_percent'], 2, ',', '.'), '0'), ',') }}%</span>
                            @else
                                —
                            @endif
                        </td>
                        @if($breakdown['uses_ppn'])
                            <td style="{{ $td }}">{{ $rp($line['ppn']) }}</td>
                        @endif
                        <td style="{{ $td }} font-weight: bold;">{{ $rp($line['total']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin-top: 8px;">
            <tr><td style="padding: 4px 6px;">Subtotal produk</td><td style="padding: 4px 6px; text-align: right;">{{ $rp($breakdown['products_total']) }}</td></tr>
            @if($breakdown['order_discount'] > 0)
                <tr><td style="padding: 4px 6px;">{{ $breakdown['order_discount_label'] }}</td><td style="padding: 4px 6px; text-align: right;">−{{ $rp($breakdown['order_discount']) }}</td></tr>
            @endif
            <tr><td style="padding: 4px 6px;">Ongkos kirim</td><td style="padding: 4px 6px; text-align: right;">{{ $rp($breakdown['shipping']) }}</td></tr>
            @if($breakdown['service_fee'] > 0)
                <tr><td style="padding: 4px 6px;">Biaya layanan</td><td style="padding: 4px 6px; text-align: right;">{{ $rp($breakdown['service_fee']) }}</td></tr>
            @endif
            <tr><td style="padding: 8px 6px; font-weight: bold; border-top: 1px solid #333;">Total bayar</td><td style="padding: 8px 6px; text-align: right; font-weight: bold; border-top: 1px solid #333;">{{ $rp($breakdown['grand_total']) }}</td></tr>
        </table>
        <p style="font-size: 12px; color: #777;">{{ $breakdown['note'] }}</p>
    @endif

    <p>Silakan lakukan pembayaran sebelum batas waktu agar pesanan Anda dapat segera kami proses.</p>

    <p>Terima kasih telah berbelanja di Rasaconnect!</p>
    <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
    <p style="font-size: 12px; color: #888; text-align: center;">Ini adalah email otomatis, mohon tidak membalas email ini.</p>
</body>
</html>
