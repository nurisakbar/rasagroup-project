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

    <p>Silakan lakukan pembayaran sebelum batas waktu agar pesanan Anda dapat segera kami proses.</p>

    <p>Terima kasih telah berbelanja di Rasaconnect!</p>
    <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
    <p style="font-size: 12px; color: #888; text-align: center;">Ini adalah email otomatis, mohon tidak membalas email ini.</p>
</body>
</html>
