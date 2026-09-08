<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Label Pesanan</title>
    <style>
        @page {
            size: 100mm 150mm; /* Standar ukuran resi thermal A6 */
            margin: 0;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #000;
            font-size: 11px;
            line-height: 1.3;
        }
        .print-container {
            width: 100mm;
            height: 148mm;
            margin: 0 auto;
            padding: 4mm;
            box-sizing: border-box;
            border: 1px solid #000;
        }
        .header-row {
            display: flex;
            border: 1px solid #000;
            border-bottom: none;
            align-items: center;
            text-align: center;
            font-weight: bold;
        }
        .header-row > div {
            padding: 4px;
        }
        .header-platforms {
            width: 35%;
            border-right: 1px solid #000;
            font-size: 10px;
        }
        .header-courier {
            width: 40%;
            border-right: 1px solid #000;
            font-size: 14px;
            font-weight: 900;
        }
        .header-service {
            width: 25%;
            font-size: 12px;
        }
        
        .barcode-section {
            border: 1px solid #000;
            border-bottom: none;
            text-align: center;
            padding: 10px 0 5px 0;
        }
        .barcode-img {
            height: 60px;
            width: 85%;
            object-fit: cover;
            /* Placeholder styling for barcode */
            background: repeating-linear-gradient(90deg, #000, #000 3px, #fff 3px, #fff 5px, #000 5px, #000 6px, #fff 6px, #fff 9px);
            margin: 0 auto;
        }
        .tracking-number {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 5px;
        }

        .address-section {
            display: flex;
            border: 1px solid #000;
            border-bottom: none;
        }
        .address-left {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .address-right {
            width: 80px;
            border-left: 1px solid #000;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 5px;
        }
        
        .sender-info, .recipient-info {
            padding: 4px 6px;
        }
        .recipient-info {
            border-top: 1px solid #000;
        }
        .info-title {
            margin-bottom: 2px;
        }
        .info-name-phone {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .info-address {
            font-size: 10px;
        }

        .qr-placeholder {
            width: 60px;
            height: 60px;
            border: 1px solid #000;
            /* Mockup QR Code pattern */
            background: 
                linear-gradient(45deg, #000 25%, transparent 25%, transparent 75%, #000 75%, #000), 
                linear-gradient(45deg, #000 25%, transparent 25%, transparent 75%, #000 75%, #000);
            background-size: 10px 10px;
            background-position: 0 0, 5px 5px;
            margin-bottom: 15px;
        }
        
        .cod-text {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 5px;
        }
        .cod-amount {
            font-weight: bold;
            text-align: center;
        }

        .extra-info-section {
            border: 1px solid #000;
            border-bottom: none;
            padding: 4px 6px;
            font-size: 10px;
        }
        
        .tt-order {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 10px;
            margin-bottom: 5px;
        }

        .transit-info {
            font-size: 10px;
            padding: 0 4px;
            margin-bottom: 2px;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 5px;
        }
        .products-table th, .products-table td {
            text-align: left;
            padding: 3px 4px;
        }
        .products-table th {
            border-bottom: 1px solid #000;
            font-weight: bold;
        }
        .products-table td {
            border-bottom: 1px solid #ccc;
        }
        .products-table .col-name { width: 50%; }
        .products-table .col-sku { width: 15%; }
        .products-table .col-seller { width: 25%; }
        .products-table .col-qty { width: 10%; text-align: center; }
        
        .products-table th.col-qty { text-align: center; }

        .qty-total {
            text-align: right;
            font-weight: bold;
            font-size: 10px;
            padding-right: 15px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }

        .footer-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 4px;
        }
        
        .footer-logos {
            font-weight: bold;
            font-size: 14px;
        }
        .footer-order-info {
            text-align: right;
            font-size: 9px;
        }

        /* Mockup for actual printing to hide unnecessary elements */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <!-- Wrapper for actual order data. Here it is hardcoded to match the provided image -->
    <div class="print-container">
        
        <!-- Header -->
        <div class="header-row">
            <div class="header-platforms">
                <img src="{{ asset('logorasa.png') }}" alt="Rasaconnect" style="height: 20px; filter: grayscale(100%);">
            </div>
            <div class="header-courier">
                SICEPAT
            </div>
            <div class="header-service">
                REG
            </div>
        </div>

        <!-- Barcode -->
        <div class="barcode-section">
            <img src="https://barcode.tec-it.com/barcode.ashx?data=002970353464&code=Code128&dpi=96" alt="Barcode" class="barcode-img" style="background: none; border: none; height: 60px;">
            <div class="tracking-number">002970353464</div>
        </div>

        <!-- Address & QR -->
        <div class="address-section">
            <div class="address-left">
                <div class="sender-info">
                    <div class="info-title">Dari(pengirim)</div>
                    <div class="info-name-phone">
                        <span>DRiPP FLAVOUR</span>
                        <span>(162)87832873190</span>
                    </div>
                    <div class="info-address">
                        DKI JAKARTA, Kota Administrasi Jakarta Barat
                    </div>
                </div>
                <div class="recipient-info">
                    <div class="info-title">Ke(penerima)</div>
                    <div class="info-name-phone">
                        <span>Rauf Indra</span>
                        <span>081234567890</span>
                    </div>
                    <div class="info-address">
                        JAWA TENGAH, Kab. Kendal, Kendal, Jalan Johar, (Depan Penjahit Manis)
                    </div>
                </div>
            </div>
            <div class="address-right">
                <!-- Ganti src img ini dengan library QR generation (seperti SimpleSoftwareIO/QrCode) jika di Laravel -->
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=002970353464" alt="QR Code" class="qr-img" style="width: 60px; height: 60px; margin-bottom: 15px;">
                <div class="cod-text">COD :</div>
                <div class="cod-amount">0</div>
            </div>
        </div>

        <!-- Extra info -->
        <div class="extra-info-section">
            <div style="margin-bottom: 2px;">Komentar :</div>
            <div>Asuransi: <span style="margin-left: 10px;">0</span></div>
            <div>Berat: <span style="margin-left: 10px;">1.3 kg</span></div>
        </div>
        
        <div class="tt-order">
            TT Order ID : 585471307600659642
        </div>

        <!-- Table & Transit -->
        <div class="transit-info">
            In transit by: 10/08/2026 23:59
        </div>
        
        <table class="products-table">
            <thead>
                <tr>
                    <th class="col-name">Product Name</th>
                    <th class="col-sku">SKU</th>
                    <th class="col-seller">Seller SKU</th>
                    <th class="col-qty">Qty</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="col-name">DRiPP Butterscotch Syrup - Perasa Minuman Berbentuk Sirup Butterscotch Untuk HORECA</td>
                    <td class="col-sku">Default</td>
                    <td class="col-seller">10000117</td>
                    <td class="col-qty">1</td>
                </tr>
                <tr>
                    <td class="col-name">DRiPP Caramel Syrup - Perasa Minuman Berbentuk Sirup Caramel Untuk HORECA</td>
                    <td class="col-sku">Default</td>
                    <td class="col-seller">10000118</td>
                    <td class="col-qty">2</td>
                </tr>
                <tr>
                    <td class="col-name">DRiPP Hazelnut Syrup - Perasa Minuman Berbentuk Sirup Hazelnut Untuk HORECA</td>
                    <td class="col-sku">Default</td>
                    <td class="col-seller">10000119</td>
                    <td class="col-qty">1</td>
                </tr>
            </tbody>
        </table>

        <div class="qty-total">
            Qty Total: 4
        </div>

        <!-- Footer -->
        <div class="footer-section">
            <div class="footer-logos">
                <img src="{{ asset('logorasa.png') }}" alt="Rasaconnect" style="height: 20px; filter: grayscale(100%);">
            </div>
            <div class="footer-order-info">
                Order ID: 585471307600659642<br>
                NickName: kamusiapa
            </div>
        </div>

    </div>
</body>
</html>
