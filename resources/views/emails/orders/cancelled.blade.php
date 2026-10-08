<!DOCTYPE html>
<html>
<head>
    <title>Pesanan Dibatalkan - {{ $order->order_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2>Pesanan Anda Telah Dibatalkan</h2>
    <p>Halo {{ $order->user->name ?? 'Pelanggan' }},</p>
    <p>Mohon maaf, pesanan Anda dengan nomor <strong>{{ $order->order_number }}</strong> telah dibatalkan secara otomatis karena waktu pembayaran telah melewati batas waktu yang ditentukan (Expired).</p>
    
    <div style="background-color: #fce8e6; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <h3 style="margin-top: 0; color: #d93025;">Detail Pesanan</h3>
        <p><strong>Total Tagihan:</strong> Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
        <p><strong>Status:</strong> Batal / Kadaluarsa</p>
    </div>

    <p>Jika Anda masih ingin melakukan pembelian, silakan melakukan pemesanan ulang (checkout ulang) melalui website atau aplikasi Rasaconnect.</p>

    <p>Terima kasih.</p>
    <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
    <p style="font-size: 12px; color: #888; text-align: center;">Ini adalah email otomatis, mohon tidak membalas email ini.</p>
</body>
</html>
