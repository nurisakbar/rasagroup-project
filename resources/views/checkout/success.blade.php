@extends('layouts.shop')

@section('title', 'Pesanan Berhasil')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> Checkout
            <span></span> Selesai
        </div>
    </div>
</div>

<div class="page-content pt-100 pb-100 checkout-success-page" style="background-color: #F2EAE1;">
        <style>
        @media (max-width: 576px) {
            .checkout-page-title {
                font-size: 1.6rem !important;
            }
            .va-number {
                font-size: 1.4rem !important;
                letter-spacing: 0.5px !important;
            }
        }
        .checkout-success-section-title {
            font-weight: 700;
            color: #253D4E;
            font-size: 1rem;
            margin-bottom: 0.85rem;
        }
        .checkout-success-name {
            color: #253D4E;
            font-size: 1.05rem;
            font-weight: 700;
            line-height: 1.4;
        }
        .checkout-success-phone {
            color: #253D4E;
            font-size: 15px;
            font-weight: 600;
            line-height: 1.5;
            margin: 0;
        }
        .checkout-success-address-line {
            color: #253D4E;
            font-size: 15px;
            font-weight: 500;
            line-height: 1.65;
            margin: 0;
        }
    </style>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm p-40 border-radius-30" style="background-color: #ffffff;">
                    <div class="card-body text-center">
                        <div class="mb-30">
                            <i class="fi-rs-check-circle" style="font-size: 80px; color: #6A1B1B;"></i>
                        </div>
                        <h2 class="mb-20 checkout-page-title" style="font-weight: 700; color: #253D4E;">Pesanan Berhasil Dibuat!</h2>
                                                @if($order->payment_status === 'pending')
                            <p class="text-dark mb-40">Terima kasih atas pembelian Anda. Silakan <strong>selesaikan pembayaran</strong> agar pesanan Anda dapat segera kami proses.</p>
                        @else
                            <p class="text-dark mb-40">Terima kasih atas pembelian Anda. Pesanan Anda sedang kami proses.</p>
                        @endif
                        
                        <div class="mb-40 p-30 border-radius-20" style="background-color: #F8F9FA; border: 1.5px dashed #ECECEC;">
                            <div class="row align-items-center">
                                <div class="col-md-6 text-md-start text-center mb-md-0 mb-3">
                                    <span class="font-md d-block mb-1" style="color: #4b5563;">Nomor Pesanan</span>
                                    <h4 style="font-weight: 700; color: #6A1B1B; margin: 0;">{{ $order->order_number }}</h4>
                                </div>
                                <div class="col-md-6 text-md-end text-center">
                                    <span class="font-md d-block mb-1" style="color: #4b5563;">Total Pembayaran</span>
                                    <h4 style="font-weight: 700; color: #6A1B1B; margin: 0;">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</h4>
                                </div>
                            </div>
                        </div>

                        @if($order->sourceWarehouse)
                            @php $hub = $order->sourceWarehouse; @endphp
                            <div class="mb-40 p-25 border-radius-20 text-start checkout-success-address-block" style="background-color: #fffaf8; border: 1.5px solid #edd6d0;">
                                <h5 class="mb-15 checkout-success-section-title">
                                    <i class="fi-rs-shop mr-10"></i>Barang dikirim dari
                                </h5>
                                <strong class="d-block mb-10 checkout-success-name">{{ $hub->name }}</strong>
                                @if($hub->address)
                                    <p class="checkout-success-address-line mb-5">{{ $hub->address }}</p>
                                @endif
                                <p class="checkout-success-address-line mb-0">
                                    @if($hub->district)
                                        Kec. {{ $hub->district->name }},
                                    @endif
                                    {{ $hub->full_location }}
                                    @if($hub->postal_code)
                                        &nbsp;{{ $hub->postal_code }}
                                    @endif
                                </p>
                                @if($hub->phone)
                                    <p class="checkout-success-phone mt-10 mb-0">
                                        <i class="fi-rs-headset mr-5"></i>{{ $hub->phone }}
                                    </p>
                                @endif
                            </div>
                        @endif

                        <div class="row text-start mb-40 g-4">
                            <!-- Shipping Address -->
                            <div class="col-md-6">
                                <div class="p-25 h-100 border-radius-20 checkout-success-address-block" style="background-color: #ffffff; border: 1.5px solid #ECECEC;">
                                    <h5 class="mb-15 checkout-success-section-title"><i class="fi-rs-marker mr-10"></i>Alamat Penerima</h5>
                                    @if($order->address)
                                        <strong class="d-block mb-8 checkout-success-name">{{ $order->address->recipient_name }}</strong>
                                        <p class="checkout-success-phone mb-8">{{ $order->address->phone }}</p>
                                        <p class="checkout-success-address-line mb-0">
                                            {{ $order->address->address_detail }}<br>
                                            @if($order->address->village?->name)
                                                {{ $order->address->village->name }},
                                            @endif
                                            Kec. {{ $order->address->district?->name }}<br>
                                            {{ $order->address->regency?->name }}, {{ $order->address->province?->name }}
                                            @if($order->address->postal_code)
                                                <br>{{ $order->address->postal_code }}
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Shipping Method -->
                            <div class="col-md-6">
                                <div class="p-25 h-100 border-radius-20 checkout-success-address-block" style="background-color: #ffffff; border: 1.5px solid #ECECEC;">
                                    <h5 class="mb-15 checkout-success-section-title"><i class="fi-rs-truck-side mr-10"></i>Pengiriman</h5>
                                    @if($order->expedition)
                                        <strong class="d-block mb-8 checkout-success-name">{{ $order->expedition->name }}</strong>
                                        <p class="checkout-success-address-line mb-0">
                                            Layanan: {{ $order->expedition_service }}<br>
                                            Estimasi: {{ $order->expedition->estimated_delivery }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Payment Information -->
                        @if($order->payment_method !== 'term_of_payment')
                            @if($order->payment_status === 'pending')
                                <div class="text-start mb-50">
                                    <h4 class="mb-20" style="font-weight: 700; color: #253D4E;">Instruksi Pembayaran</h4>
                                
                                @if($order->payment_status === 'pending')
                                    <div class="alert mb-30 p-30 text-center" style="border-radius: 15px; border: 1.5px solid #ffedd5; background: linear-gradient(145deg, #fffcf8 0%, #fff7ed 100%); box-shadow: 0 4px 6px -1px rgba(234, 88, 12, 0.05);">
                                        <div class="icon-wrap mx-auto mb-15" style="width: 50px; height: 50px; border-radius: 50%; background: #ffedd5; display: flex; align-items: center; justify-content: center;">
                                            <i class="fi-rs-clock" style="font-size: 24px; color: #ea580c;"></i>
                                        </div>
                                        <h5 class="mb-10" style="color: #9a3412; font-weight: 700;">Segera Selesaikan Pembayaran Anda</h5>
                                        <p class="mb-20 font-sm" style="color: #c2410c;">Waktu Anda tersisa sebelum pesanan dibatalkan otomatis:</p>
                                        
                                        <div class="d-flex justify-content-center align-items-center mb-20">
                                            <div class="text-center mx-2">
                                                <div class="px-3 py-2 mb-1" style="background-color: #ea580c; border-radius: 8px; color: white; font-weight: 800; font-size: 24px; min-width: 60px; box-shadow: 0 2px 4px rgba(234,88,12,0.2);" id="countdown-hours">00</div>
                                                <span style="font-size: 11px; color: #ea580c; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Jam</span>
                                            </div>
                                            <div class="pb-3 px-1" style="color: #ea580c; font-weight: 800; font-size: 20px;">:</div>
                                            <div class="text-center mx-2">
                                                <div class="px-3 py-2 mb-1" style="background-color: #ea580c; border-radius: 8px; color: white; font-weight: 800; font-size: 24px; min-width: 60px; box-shadow: 0 2px 4px rgba(234,88,12,0.2);" id="countdown-minutes">00</div>
                                                <span style="font-size: 11px; color: #ea580c; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Menit</span>
                                            </div>
                                            <div class="pb-3 px-1" style="color: #ea580c; font-weight: 800; font-size: 20px;">:</div>
                                            <div class="text-center mx-2">
                                                <div class="px-3 py-2 mb-1" style="background-color: #ea580c; border-radius: 8px; color: white; font-weight: 800; font-size: 24px; min-width: 60px; box-shadow: 0 2px 4px rgba(234,88,12,0.2);" id="countdown-seconds">00</div>
                                                <span style="font-size: 11px; color: #ea580c; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Detik</span>
                                            </div>
                                        </div>
                                        
                                        <p class="mb-0 font-sm" style="color: #9a3412;">Jatuh Tempo: <strong>{{ $order->created_at->addMinutes(30)->format('d M Y, H:i') }} WIB</strong></p>
                                    </div>
                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            // Parse the PHP created_at date safely
                                            var targetDate = {{ $order->created_at->addMinutes(30)->timestamp * 1000 }};
                                            
                                            var countdownTimer = setInterval(function() {
                                                var now = new Date().getTime();
                                                var distance = targetDate - now;
                                                
                                                if (distance < 0) {
                                                    clearInterval(countdownTimer);
                                                    document.getElementById('countdown-hours').innerHTML = '00';
                                                    document.getElementById('countdown-minutes').innerHTML = '00';
                                                    document.getElementById('countdown-seconds').innerHTML = '00';
                                                    
                                                    return;
                                                }
                                                
                                                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                                var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                                                
                                                // Format with leading zero
                                                hours = hours < 10 ? "0" + hours : hours;
                                                minutes = minutes < 10 ? "0" + minutes : minutes;
                                                seconds = seconds < 10 ? "0" + seconds : seconds;
                                                
                                                document.getElementById("countdown-hours").innerHTML = hours;
                                                document.getElementById("countdown-minutes").innerHTML = minutes;
                                                document.getElementById("countdown-seconds").innerHTML = seconds;
                                            }, 1000);
                                        });
                                    </script>
                                @endif

                                @if(in_array($order->payment_method, ['manual_transfer', 'transfer']))
                                    <div class="p-30 border-radius-20 bg-white shadow-sm" style="border: 1px solid #ECECEC;">
                                        <div class="d-flex align-items-center mb-25">
                                            <div class="icon-wrap mr-15" style="width: 45px; height: 45px; border-radius: 50%; background: rgba(106, 27, 27, 0.05); display: flex; align-items: center; justify-content: center;">
                                                <i class="fi-rs-bank" style="font-size: 22px; color: #6A1B1B;"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-1" style="color: #253D4E;">Bank BCA</h5>
                                                <span class="font-sm" style="color: #4b5563;">KCP MUARA KARANG 2</span>
                                            </div>
                                        </div>
                                        
                                        <div class="p-25 border-radius-12 mb-20 text-center" style="background: #F8F9FA; border: 1.5px dashed #E2E2E2;">
                                            <div class="mb-15">
                                                <span class="font-sm d-block mb-5" style="color: #4b5563;">Nomor Rekening</span>
                                                <h2 class="mb-0" style="color: #6A1B1B; letter-spacing: 1.5px; font-weight: 700;">6371 7598 99</h2>
                                            </div>
                                            <div>
                                                <span class="font-sm d-block mb-5" style="color: #4b5563;">Atas Nama</span>
                                                <h5 class="mb-0" style="color: #253D4E;">{{ strtoupper(\App\Services\FaspayConfig::getCompanyName($order->company ?? 'rdi')) }}</h5>
                                            </div>
                                        </div>
                                        <div class="mt-30 text-center">
                                            <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('6371759899', this)">
                                                <i class="fi-rs-copy mr-5"></i> Salin Nomor Rekening
                                            </button>
                                        </div>
                                    </div>

                                    <div class="accordion mt-30" id="accordionPayment">
                                        <div class="accordion-item" style="border: 1px solid #ECECEC; border-radius: 12px; margin-bottom: 10px; overflow: hidden;">
                                            <h2 class="accordion-header" id="headingOne">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne" style="background: white; color: #253D4E; font-weight: 600;">
                                                    <i class="fi-rs-smartphone mr-10" style="color: #6A1B1B;"></i> Cara Pembayaran via M-Banking BCA
                                                </button>
                                            </h2>
                                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionPayment">
                                                <div class="accordion-body" style="background: #FAFAFA; font-size: 14px; line-height: 1.8;">
                                                    <ol class="mb-0" style="list-style-type: decimal; padding-left: 1.5rem;">
                                                        <li>Buka aplikasi BCA mobile dan login ke akun Anda.</li>
                                                        <li>Pilih menu <strong>m-Transfer</strong>.</li>
                                                        <li>Pilih <strong>Transfer Antar Rekening / BCA</strong>.</li>
                                                        <li>Masukkan nomor rekening <strong>6371759899</strong>.</li>
                                                        <li>Masukkan jumlah pembayaran <strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong> dengan tepat.</li>
                                                        <li>Periksa kembali data transaksi (Pastikan nama penerima <strong>{{ strtoupper(\App\Services\FaspayConfig::getCompanyName($order->company ?? 'rdi')) }}</strong>).</li>
                                                        <li>Masukkan PIN m-BCA Anda lalu tap <strong>OK</strong>.</li>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="accordion-item" style="border: 1px solid #ECECEC; border-radius: 12px; margin-bottom: 10px; overflow: hidden;">
                                            <h2 class="accordion-header" id="headingTwo">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo" style="background: white; color: #253D4E; font-weight: 600;">
                                                    <i class="fi-rs-computer mr-10" style="color: #6A1B1B;"></i> Cara Pembayaran via ATM BCA
                                                </button>
                                            </h2>
                                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionPayment">
                                                <div class="accordion-body" style="background: #FAFAFA; font-size: 14px; line-height: 1.8;">
                                                    <ol class="mb-0" style="list-style-type: decimal; padding-left: 1.5rem;">
                                                        <li>Masukkan kartu ATM BCA dan PIN Anda.</li>
                                                        <li>Pilih menu <strong>Transaksi Lainnya</strong>.</li>
                                                        <li>Pilih menu <strong>Transfer</strong> dan kemudian <strong>Ke Rek BCA</strong>.</li>
                                                        <li>Masukkan nomor rekening <strong>6371759899</strong>.</li>
                                                        <li>Masukkan nominal transfer sebesar <strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong> dengan tepat.</li>
                                                        <li>Layar ATM akan menampilkan detail transaksi. Pastikan penerima adalah <strong>{{ strtoupper(\App\Services\FaspayConfig::getCompanyName($order->company ?? 'rdi')) }}</strong>.</li>
                                                        <li>Pilih <strong>Ya</strong> untuk menyelesaikan transaksi.</li>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @elseif(str_starts_with($order->payment_method, 'faspay_'))
                                    <!-- Faspay SNAP UI -->
                                    <div class="p-30 border-radius-20 bg-white shadow-sm" style="border: 1px solid #ECECEC;">
                                        @if($order->payment_method === 'faspay_qris')
                                            <div class="text-center mb-20">
                                                <h5 class="mb-15" style="color: #253D4E;">Scan QRIS untuk Membayar</h5>
                                                @if($order->virtual_account_no)
                                                    <img src="{{ $order->virtual_account_no }}" alt="QRIS Barcode" style="max-width: 250px; border: 2px solid #ECECEC; padding: 10px; border-radius: 10px;">
                                                @else
                                                    <p class="text-danger">Gagal memuat QRIS. Silakan hubungi admin.</p>
                                                @endif
                                            </div>
                                        @elseif($order->payment_method === 'faspay_direct_debit')
                                            <div class="text-center mb-20">
                                                <div class="icon-wrap mx-auto mb-15" style="width: 65px; height: 65px; border-radius: 50%; background: rgba(106, 27, 27, 0.05); display: flex; align-items: center; justify-content: center;">
                                                    <i class="fi-rs-credit-card" style="font-size: 28px; color: #6A1B1B;"></i>
                                                </div>
                                                <h5 class="mb-15" style="color: #253D4E;">Lanjutkan Pembayaran Direct Debit</h5>
                                                @if($order->faspay_redirect_url)
                                                    <p class="text-muted mb-25" style="font-size: 15px;">Silakan klik tombol di bawah ini untuk dialihkan ke portal pembayaran bank Anda dengan aman.</p>
                                                    <a href="{{ $order->faspay_redirect_url }}" class="btn btn-primary px-5 py-3" style="border-radius: 8px; font-weight: 600;" target="_blank" rel="noopener">
                                                        <i class="fi-rs-sign-out mr-10"></i> Bayar Sekarang
                                                    </a>
                                                @else
                                                    <p class="text-danger mt-10">Tautan pembayaran sedang diproses atau gagal dimuat. Harap periksa email Anda atau hubungi admin.</p>
                                                @endif
                                            </div>
                                        @else
                                            <div class="d-flex align-items-center mb-25">
                                                <div class="icon-wrap mr-15" style="width: 45px; height: 45px; border-radius: 50%; background: rgba(106, 27, 27, 0.05); display: flex; align-items: center; justify-content: center;">
                                                    <i class="fi-rs-bank" style="font-size: 22px; color: #6A1B1B;"></i>
                                                </div>
                                                <div>
                                                    @php
                                                        $bankNames = [
                                                            'faspay_mandiri_va'  => 'Bank Mandiri',
                                                            'faspay_sinarmas_va' => 'Bank Sinarmas',
                                                            'faspay_permata_va'  => 'Bank Permata',
                                                            'faspay_maybank_va'  => 'Bank Maybank',
                                                            'faspay_danamon_va'  => 'Bank Danamon',
                                                            'faspay_bsi_va'      => 'Bank BSI',
                                                            'faspay_cimb_va'     => 'Bank CIMB Niaga',
                                                            'faspay_bca_va'      => 'Bank BCA',
                                                            'faspay_bri_va'      => 'Bank BRI',
                                                            'faspay_bni_va'      => 'Bank BNI',
                                                        ];
                                                        $bankName = $bankNames[$order->payment_method] ?? 'Virtual Account';
                                                    @endphp
                                                    <h5 class="mb-1" style="color: #253D4E;">{{ $bankName }}</h5>
                                                    <span class="font-sm" style="color: #4b5563;">Virtual Account</span>
                                                </div>
                                            </div>
                                            
                                            @if($order->virtual_account_no)
                                                <div class="p-25 border-radius-12 mb-20 text-center" style="background: #F8F9FA; border: 1.5px dashed #E2E2E2;">
                                                    <div class="mb-15">
                                                        <span class="font-sm d-block mb-5" style="color: #4b5563;">Nomor Virtual Account</span>
                                                        <h2 class="mb-0 va-number" style="color: #6A1B1B; letter-spacing: 1.5px; font-weight: 700; word-break: break-all;">{{ $order->virtual_account_no }}</h2>
                                                    </div>
                                                </div>
                                                <div class="text-center mt-20">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('{{ $order->virtual_account_no }}', this)">
                                                        <i class="fi-rs-copy mr-5"></i> Salin Nomor VA
                                                    </button>
                                                </div>
                                            @else
                                                <div class="p-25 border-radius-12 mb-20 text-center" style="background: #F8F9FA; border: 1.5px dashed #E2E2E2;">
                                                    <div class="mb-15">
                                                        <p class="mb-0 text-muted">Nomor Virtual Account akan ditampilkan di halaman pembayaran BCA.</p>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif
                                        <div class="text-center mt-25 pt-20 border-top">
                                            <p class="mb-0 font-sm" style="color: #4b5563;">
                                                Status Pembayaran saat ini: <strong style="color: #ea580c;">{{ strtoupper($order->payment_status) }}</strong>
                                            </p>
                                            <p class="mt-10 font-sm text-dark">Sistem akan secara otomatis memverifikasi pembayaran Anda dalam hitungan menit setelah transfer berhasil.</p>
                                        </div>
                                    </div>

                                    @if($order->payment_method !== 'faspay_qris')
                                    <div class="accordion mt-30" id="accordionVAPayment">
                                        <div class="accordion-item" style="border: 1px solid #ECECEC; border-radius: 12px; margin-bottom: 10px; overflow: hidden;">
                                            <h2 class="accordion-header" id="headingVAOne">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVAOne" aria-expanded="false" aria-controls="collapseVAOne" style="background: white; color: #253D4E; font-weight: 600;">
                                                    <i class="fi-rs-smartphone mr-10" style="color: #6A1B1B;"></i> Panduan Transfer via Mobile Banking
                                                </button>
                                            </h2>
                                            <div id="collapseVAOne" class="accordion-collapse collapse" aria-labelledby="headingVAOne" data-bs-parent="#accordionVAPayment">
                                                <div class="accordion-body" style="background: #FAFAFA; font-size: 14px; line-height: 1.8;">
                                                    <ol class="mb-0" style="list-style-type: decimal; padding-left: 1.5rem;">
                                                        <li>Masuk ke aplikasi Mobile Banking Anda.</li>
                                                        <li>Pilih menu <strong>Transfer</strong> atau <strong>Bayar/Beli</strong>.</li>
                                                        <li>Pilih menu <strong>Virtual Account (VA)</strong> atau sejenisnya.</li>
                                                        <li>Masukkan Nomor Virtual Account: <strong>{{ $order->virtual_account_no }}</strong>.</li>
                                                        <li>Masukkan jumlah tagihan: <strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong> jika tidak terisi otomatis.</li>
                                                        <li>Pastikan detail transaksi sesuai (Nama Merchant/Penerima), lalu masukkan PIN Anda.</li>
                                                        <li>Simpan bukti pembayaran jika diperlukan.</li>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="accordion-item" style="border: 1px solid #ECECEC; border-radius: 12px; margin-bottom: 10px; overflow: hidden;">
                                            <h2 class="accordion-header" id="headingVATwo">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVATwo" aria-expanded="false" aria-controls="collapseVATwo" style="background: white; color: #253D4E; font-weight: 600;">
                                                    <i class="fi-rs-computer mr-10" style="color: #6A1B1B;"></i> Panduan Transfer via ATM
                                                </button>
                                            </h2>
                                            <div id="collapseVATwo" class="accordion-collapse collapse" aria-labelledby="headingVATwo" data-bs-parent="#accordionVAPayment">
                                                <div class="accordion-body" style="background: #FAFAFA; font-size: 14px; line-height: 1.8;">
                                                    <ol class="mb-0" style="list-style-type: decimal; padding-left: 1.5rem;">
                                                        <li>Masukkan kartu ATM dan PIN Anda.</li>
                                                        <li>Pilih menu <strong>Transaksi Lainnya</strong> atau <strong>Bayar/Beli</strong>.</li>
                                                        <li>Pilih menu <strong>Transfer</strong> lalu <strong>Virtual Account</strong>.</li>
                                                        <li>Masukkan Nomor Virtual Account: <strong>{{ $order->virtual_account_no }}</strong>.</li>
                                                        <li>Periksa kembali rincian yang muncul di layar.</li>
                                                        <li>Jika benar, tekan <strong>Ya</strong> atau <strong>Lanjut</strong>.</li>
                                                        <li>Ambil struk ATM sebagai bukti pembayaran yang sah.</li>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                        </div>
                                        
                                        
                                @elseif($order->payment_method === 'xendit' || $order->payment_method === 'faspay')
                                    <div id="checkout-xendit-root">
                                        <p id="checkout-payment-sync-hint" class="text-center font-sm mb-20" style="color: #4b5563; display: none;">
                                            <span class="d-inline-block animate-pulse" style="animation: checkoutPulse 1.2s ease-in-out infinite;">Memverifikasi status pembayaran…</span>
                                        </p>
                                        <div id="checkout-xendit-paid-pane" class="p-30 border-radius-20 text-center" style="background-color: #e8f5e9; border: 1px solid #c8e6c9; {{ $order->payment_status === 'paid' ? '' : 'display: none;' }}">
                                            <h6 class="mb-10" style="color: #2e7d32;"><i class="fi-rs-check-circle mr-10"></i>Pembayaran Berhasil!</h6>
                                            <p class="mb-0" style="color: #2e7d32;">Terima kasih, pembayaran Anda telah kami terima secara otomatis.</p>
                                        </div>
                                        <div id="checkout-xendit-failed-pane" class="p-30 border-radius-20 text-center" style="background-color: #ffebee; border: 1px solid #ffcdd2; {{ in_array($order->payment_status, ['failed', 'refunded'], true) ? '' : 'display: none;' }}">
                                            <h6 class="mb-10" style="color: #c62828;"><i class="fi-rs-close-circle mr-10"></i>Pembayaran tidak berhasil</h6>
                                            <p class="mb-0" style="color: #5d4037;">Status: {{ strtoupper($order->payment_status) }}. Silakan buat pesanan baru atau hubungi kami jika Anda sudah membayar.</p>
                                        </div>
                                        @php $paymentUrl = $order->faspay_redirect_url ?? $order->xendit_invoice_url; @endphp
                                        <div id="checkout-xendit-pending-pane" class="p-30 border-radius-20 text-center" style="background-color: rgba(106, 27, 27, 0.03); border: 1px solid rgba(106, 27, 27, 0.1); {{ ($order->payment_status === 'paid' || in_array($order->payment_status, ['failed', 'refunded'], true) || ! $paymentUrl) ? 'display: none;' : '' }}">
                                            <p class="mb-15 text-brand" style="font-size: 16px;"><strong>Selesaikan Pembayaran Anda</strong></p>
                                            <p class="mb-20 text-dark">Pesanan ini sedang menunggu pembayaran. Silakan klik tombol di bawah untuk melanjutkan ke halaman pembayaran.</p>
                                            <a id="checkout-xendit-pay-link" href="{{ $paymentUrl }}" class="btn" target="_blank">Bayar Sekarang</a>
                                        </div>
                                    </div>
                                    @if($order->payment_status === 'pending')
                                            <style>
        @media (max-width: 576px) {
            .checkout-page-title {
                font-size: 1.6rem !important;
            }
            .va-number {
                font-size: 1.4rem !important;
                letter-spacing: 0.5px !important;
            }
        }
                                            @keyframes checkoutPulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.45; } }
                                        </style>
                                        <script>
                                            (function () {
                                                var url = @json(route('checkout.success.payment-status', $order));
                                                var pollMs = 2500;
                                                var maxTicks = 120;
                                                var token = document.querySelector('meta[name="csrf-token"]');
                                                token = token ? token.getAttribute('content') : '';
                                                var syncHint = document.getElementById('checkout-payment-sync-hint');
                                                var paidPane = document.getElementById('checkout-xendit-paid-pane');
                                                var pendingPane = document.getElementById('checkout-xendit-pending-pane');
                                                var failedPane = document.getElementById('checkout-xendit-failed-pane');
                                                var payLink = document.getElementById('checkout-xendit-pay-link');
                                                var ticks = 0;
                                                function show(el, on) { if (el) el.style.display = on ? '' : 'none'; }
                                                function apply(data) {
                                                    var st = data.payment_status;
                                                    if (st === 'paid') {
                                                        show(syncHint, false);
                                                        show(paidPane, true);
                                                        show(pendingPane, false);
                                                        show(failedPane, false);
                                                        return true;
                                                    }
                                                    if (st === 'failed' || st === 'refunded') {
                                                        show(syncHint, false);
                                                        show(paidPane, false);
                                                        show(pendingPane, false);
                                                        show(failedPane, true);
                                                        if (failedPane) failedPane.querySelector('p').textContent = 'Status: ' + String(st).toUpperCase() + '. Silakan buat pesanan baru atau hubungi kami jika Anda sudah membayar.';
                                                        return true;
                                                    }
                                                    if (data.payment_url && payLink) payLink.href = data.payment_url;
                                                    show(paidPane, false);
                                                    show(failedPane, false);
                                                    show(pendingPane, !!data.payment_url);
                                                    return false;
                                                }
                                                function tick() {
                                                    ticks++;
                                                    if (ticks > maxTicks) {
                                                        show(syncHint, false);
                                                        return;
                                                    }
                                                    fetch(url, {
                                                        headers: {
                                                            'Accept': 'application/json',
                                                            'X-Requested-With': 'XMLHttpRequest',
                                                            'X-CSRF-TOKEN': token
                                                        },
                                                        credentials: 'same-origin'
                                                    })
                                                        .then(function (r) {
                                                            if (!r.ok) throw new Error('HTTP ' + r.status);
                                                            return r.json();
                                                        })
                                                        .then(function (data) {
                                                            if (apply(data)) return;
                                                            setTimeout(tick, pollMs);
                                                        })
                                                        .catch(function () {
                                                            setTimeout(tick, pollMs);
                                                        });
                                                }
                                                show(syncHint, true);
                                                tick();
                                            })();
                                        </script>
                                    @endif
                                @endif
                                </div>
                            @elseif($order->payment_status === 'paid')
                                <div class="text-start mb-50">
                                    <div class="p-40 border-radius-20 bg-white shadow-sm text-center" style="border: 1px solid #ECECEC;">
                                        <div class="icon-wrap mx-auto mb-20" style="width: 80px; height: 80px; border-radius: 50%; background: rgba(34, 197, 94, 0.1); display: flex; align-items: center; justify-content: center;">
                                            <i class="fi-rs-check" style="font-size: 36px; color: #22c55e;"></i>
                                        </div>
                                        <h3 class="mb-15" style="color: #253D4E; font-weight: 700;">Pembayaran Berhasil!</h3>
                                        <p class="mb-25 font-md text-muted">Terima kasih, pembayaran pesanan Anda telah kami terima.</p>
                                        <div class="d-inline-block px-4 py-2" style="background: rgba(34, 197, 94, 0.1); border-radius: 8px; border: 1px solid rgba(34, 197, 94, 0.2);">
                                            Status Pesanan: <strong style="color: #22c55e; letter-spacing: 1px;">PAID</strong>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                                                <div class="d-flex flex-column flex-md-row justify-content-center align-items-stretch align-items-md-center gap-3 mt-10">
                            @if($order->payment_status === 'pending')
                            <button type="button" class="btn text-white shadow-sm px-4 py-3" style="background-color: #6A1B1B; border-radius: 12px; min-width: 220px; font-weight: 600; border: none; letter-spacing: 0.5px;" data-bs-toggle="modal" data-bs-target="#changePaymentModal">
                                <i class="fi-rs-refresh mr-5"></i> Ganti Metode Pembayaran
                            </button>
                            @endif
                            <a href="{{ route('buyer.orders.show', $order) }}" class="btn text-white shadow-sm px-4 py-3" style="background-color: #6A1B1B; border-radius: 12px; min-width: 220px; font-weight: 600; border: none; letter-spacing: 0.5px;">
                                <i class="fi-rs-file-text mr-5"></i> Detail Pesanan
                            </a>
                            <a href="{{ route('products.index') }}" class="btn text-white shadow-sm px-4 py-3" style="background-color: #6A1B1B; border-radius: 12px; min-width: 220px; font-weight: 600; border: none; letter-spacing: 0.5px;">
                                <i class="fi-rs-shopping-bag mr-5"></i> Lanjut Belanja
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function copyToClipboard(text, btn) {
        navigator.clipboard.writeText(text).then(function() {
            var originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fi-rs-check mr-5"></i> Tersalin!';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-outline-primary');
            setTimeout(function() {
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
            }, 2000);
        }, function(err) {
            console.error('Could not copy text: ', err);
        });
    }
</script>


<!-- Modal Ganti Metode Pembayaran -->
<div class="modal fade" id="changePaymentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 20px;">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title">Ganti Metode Pembayaran</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('buyer.orders.change-payment', $order) }}" method="POST">
        @csrf
        <div class="modal-body p-4">
            @php $activeGateway = config('services.active_payment_gateway', 'xendit'); @endphp
            
            @if($activeGateway === 'xendit')
                <div class="form-check mb-3 p-3 border rounded" style="border-color: #ECECEC !important; border-radius: 15px !important;">
                    <input class="form-check-input ms-1" type="radio" name="payment_method" id="pay_xendit" value="xendit" {{ $order->payment_method === 'xendit' ? 'checked' : '' }} required>
                    <label class="form-check-label fw-bold w-100 ms-2" for="pay_xendit" style="cursor: pointer;">
                        <i class="fi-rs-credit-card mr-5"></i> Xendit (Virtual Account, QRIS, E-Wallet)
                    </label>
                </div>
            @endif

            @if($activeGateway === 'faspay')
                <div class="form-check mb-3 p-3 border rounded" style="border-color: #ECECEC !important; border-radius: 15px !important;">
                    <input class="form-check-input ms-1" type="radio" name="payment_method" id="pay_faspay_qris" value="faspay_qris" {{ $order->payment_method === 'faspay_qris' ? 'checked' : '' }} required>
                    <label class="form-check-label fw-bold w-100 ms-2" for="pay_faspay_qris" style="cursor: pointer;">
                        <i class="fi-rs-smartphone mr-5"></i> QRIS (Faspay)
                    </label>
                </div>
                <div class="form-check mb-3 p-3 border rounded" style="border-color: #ECECEC !important; border-radius: 15px !important;">
                    <input class="form-check-input ms-1" type="radio" name="payment_method" id="pay_faspay_bca" value="faspay_bca_va" {{ $order->payment_method === 'faspay_bca_va' ? 'checked' : '' }} required>
                    <label class="form-check-label fw-bold w-100 ms-2" for="pay_faspay_bca" style="cursor: pointer;">
                        <i class="fi-rs-bank mr-5"></i> BCA Virtual Account (Faspay)
                    </label>
                </div>
                <div class="form-check mb-3 p-3 border rounded" style="border-color: #ECECEC !important; border-radius: 15px !important;">
                    <input class="form-check-input ms-1" type="radio" name="payment_method" id="pay_faspay_mandiri" value="faspay_mandiri_va" {{ $order->payment_method === 'faspay_mandiri_va' ? 'checked' : '' }} required>
                    <label class="form-check-label fw-bold w-100 ms-2" for="pay_faspay_mandiri" style="cursor: pointer;">
                        <i class="fi-rs-bank mr-5"></i> Mandiri Virtual Account (Faspay)
                    </label>
                </div>
                <div class="form-check mb-3 p-3 border rounded" style="border-color: #ECECEC !important; border-radius: 15px !important;">
                    <input class="form-check-input ms-1" type="radio" name="payment_method" id="pay_faspay_bri" value="faspay_bri_va" {{ $order->payment_method === 'faspay_bri_va' ? 'checked' : '' }} required>
                    <label class="form-check-label fw-bold w-100 ms-2" for="pay_faspay_bri" style="cursor: pointer;">
                        <i class="fi-rs-bank mr-5"></i> BRI Virtual Account (Faspay)
                    </label>
                </div>
                <div class="form-check mb-3 p-3 border rounded" style="border-color: #ECECEC !important; border-radius: 15px !important;">
                    <input class="form-check-input ms-1" type="radio" name="payment_method" id="pay_faspay_bni" value="faspay_bni_va" {{ $order->payment_method === 'faspay_bni_va' ? 'checked' : '' }} required>
                    <label class="form-check-label fw-bold w-100 ms-2" for="pay_faspay_bni" style="cursor: pointer;">
                        <i class="fi-rs-bank mr-5"></i> BNI Virtual Account (Faspay)
                    </label>
                </div>
            @endif

            <div class="form-check mb-3 p-3 border rounded" style="border-color: #ECECEC !important; border-radius: 15px !important;">
                <input class="form-check-input ms-1" type="radio" name="payment_method" id="pay_manual" value="manual_transfer" {{ in_array($order->payment_method, ['manual_transfer', 'transfer']) ? 'checked' : '' }} required>
                <label class="form-check-label fw-bold w-100 ms-2" for="pay_manual" style="cursor: pointer;">
                    <i class="fi-rs-document-text mr-5"></i> Transfer Bank Manual (Upload Bukti)
                </label>
            </div>
            
            <p class="text-danger font-sm mt-3 mb-0"><i class="fi-rs-info mr-5"></i><strong>Perhatian:</strong> Mengganti metode pembayaran akan membatalkan kode bayar yang lama dan membuat instruksi bayar baru.</p>
        </div>
        <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-brand rounded-pill">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
