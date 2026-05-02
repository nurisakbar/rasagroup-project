@extends('themes.nest.layouts.app')

@section('title', 'Kontak')

@push('styles')
<style>
    /* Global Overrides for Contact Page */
    .contact-page {
        background-color: #F2EAE1 !important;
    }

    .contact-page .page-content {
        background-color: #F2EAE1 !important;
    }

    /* Hero Banner */
    .page-hero {
        background: linear-gradient(135deg, rgba(106, 27, 27, 0.95) 0%, rgba(77, 19, 19, 0.85) 100%), 
                    url('https://images.unsplash.com/photo-1423666639041-f56000c27a9a?w=1920');
        background-size: cover;
        background-position: center;
        padding: 100px 0;
        color: white;
        text-align: center;
        border-radius: 0 0 50px 50px;
    }
    
    .page-hero h1 {
        font-family: 'Fira Sans', sans-serif !important;
        font-size: 3.5rem;
        font-weight: 700;
        margin-bottom: 15px;
        text-shadow: 2px 2px 15px rgba(0,0,0,0.3);
    }
    
    .page-hero p {
        font-family: 'Lato', sans-serif !important;
        font-size: 1.2rem;
        opacity: 0.95;
    }
    
    /* Contact Section */
    .contact-section {
        padding: 60px 0;
    }
    
    /* Contact Info Cards */
    .contact-info-card {
        background: white;
        border-radius: 20px;
        padding: 40px 25px;
        text-align: center;
        box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        height: 100%;
        border: none !important;
    }
    
    .contact-info-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 45px rgba(106, 27, 27, 0.1);
    }
    
    .contact-icon {
        width: 70px;
        height: 70px;
        background: #6A1B1B !important;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
        font-size: 1.8rem;
        color: white;
    }
    
    .contact-info-card h4 {
        font-family: 'Fira Sans', sans-serif !important;
        font-size: 1.3rem;
        font-weight: 700;
        color: #253D4E;
        margin-bottom: 15px;
    }
    
    .contact-info-card p {
        font-family: 'Lato', sans-serif !important;
        color: #7E7E7E;
        margin-bottom: 5px;
        line-height: 1.7;
    }
    
    .contact-info-card a {
        color: #6A1B1B !important;
        text-decoration: none;
        font-weight: 600;
    }
    
    .section-title h2 {
        font-family: 'Fira Sans', sans-serif !important;
        font-size: 2.8rem;
        font-weight: 700;
        color: #253D4E;
        margin-bottom: 10px;
    }
    
    .section-title .divider {
        width: 60px;
        height: 3px;
        background: #6A1B1B !important;
        margin: 15px auto;
        border-radius: 2px;
    }
    
    /* Map Section */
    .map-container {
        height: 500px;
        border-radius: 30px;
        overflow: hidden;
        margin: 0 50px 80px;
        box-shadow: 0 15px 50px rgba(0,0,0,0.08);
    }
    
    .map-container iframe {
        width: 100%;
        height: 100%;
        border: 0;
        display: block;
    }
    
    /* Social Section */
    .social-section {
        padding: 100px 0;
        background: #6A1B1B !important;
        color: white;
        text-align: center;
        border-radius: 50px 50px 0 0;
    }
    
    .social-section h2 {
        font-family: 'Fira Sans', sans-serif !important;
        font-weight: 700;
        margin-bottom: 20px;
    }
    
    .social-link {
        width: 55px;
        height: 55px;
        background: rgba(255,255,255,0.15);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .social-link:hover {
        background: white !important;
        color: #6A1B1B !important;
        transform: translateY(-5px) rotate(8deg);
    }
    
    /* FAQ Section */
    .faq-section {
        padding: 100px 0;
    }
    
    .accordion-item {
        border: none;
        margin-bottom: 15px;
        border-radius: 20px !important;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.03);
    }
    
    .accordion-button {
        font-family: 'Fira Sans', sans-serif !important;
        font-weight: 600;
        color: #253D4E !important;
        padding: 25px !important;
        background: white !important;
    }
    
    .accordion-button:not(.collapsed) {
        background: #F2EAE1 !important;
        color: #6A1B1B !important;
        box-shadow: none !important;
    }
    
    @media (max-width: 768px) {
        .page-hero h1 {
            font-size: 2rem;
        }
        
        .section-title h2 {
            font-size: 1.8rem;
        }
    }
</style>
@endpush

@section('content')
    <div class="contact-page pages">
        <div class="page-header mt-30 mb-75 rg-archive-header-maroon">
            <div class="container">
                <div class="archive-header">
                    <div class="row align-items-center">
                        <div class="col-xl-12">
                            <h1 class="mb-15">Hubungi Kami</h1>
                            <div class="breadcrumb">
                                <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Halaman Utama</a>
                                <span></span> Hubungi Kami
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="page-content pt-50">

    <!-- Contact Info Cards -->
    <section class="contact-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="contact-info-card">
                        <div class="contact-icon">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <h4>Alamat</h4>
                        <p><strong>Rasa Group Headquarters</strong></p>
                        <p>Cikarang–Cibitung, Kabupaten Bekasi, Jawa Barat</p>
                        <p class="mb-0"><a href="https://www.google.com/maps/place/Rasa+Group+Headquarters/@-6.3163819,107.1104067,17z/data=!3m1!4b1!4m6!3m5!1s0x2e699bde0142aab3:0x3b89b0730a884347!8m2!3d-6.3163819!4d107.1104067!16s%2Fg%2F11vb0rtyjl?entry=ttu" target="_blank" rel="noopener noreferrer">Lihat di Google Maps</a></p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="contact-info-card">
                        <div class="contact-icon">
                            <i class="bi bi-telephone"></i>
                        </div>
                        <h4>Telepon</h4>
                        <p><a href="tel:+628118003357">+62 811-8003-357</a></p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="contact-info-card">
                        <div class="contact-icon">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <h4>Email</h4>
                        <p><a href="mailto:info@rasagroup.com">info@rasagroup.com</a></p>
                        <p><a href="mailto:order@rasagroup.com">order@rasagroup.com</a></p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="contact-info-card">
                        <div class="contact-icon">
                            <i class="bi bi-clock"></i>
                        </div>
                        <h4>Jam Operasional</h4>
                        <p>Senin - Jumat: 08.00 - 17.00</p>
                        <p>Sabtu: 08.00 - 14.00</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <div class="section-title">
                <h2>Pertanyaan Umum</h2>
                <div class="divider"></div>
                <p class="text-muted">Jawaban untuk pertanyaan yang sering diajukan</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    <i class="bi bi-question-circle me-2"></i> Berapa minimal pemesanan produk?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Untuk pemesanan retail, tidak ada minimal order. Namun untuk pemesanan grosir dengan harga khusus, 
                                    minimal pemesanan adalah 10 karton per varian rasa.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    <i class="bi bi-question-circle me-2"></i> Bagaimana cara menjadi reseller?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Untuk menjadi reseller, Anda bisa menghubungi tim marketing kami melalui email atau WhatsApp. 
                                    Kami akan memberikan informasi lengkap mengenai syarat dan keuntungan menjadi reseller Rasa Group.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    <i class="bi bi-question-circle me-2"></i> Berapa lama pengiriman ke luar Jakarta?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Waktu pengiriman tergantung pada lokasi tujuan. Untuk area Jabodetabek biasanya 1-2 hari kerja, 
                                    Pulau Jawa 2-4 hari kerja, dan luar Pulau Jawa 4-7 hari kerja.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    <i class="bi bi-question-circle me-2"></i> Apakah produk memiliki sertifikasi BPOM dan Halal?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Ya, semua produk Rasa Group telah terdaftar di BPOM dan memiliki sertifikasi Halal dari MUI. 
                                    Kami selalu mengutamakan keamanan dan kualitas produk untuk konsumen.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    <i class="bi bi-question-circle me-2"></i> Metode pembayaran apa saja yang tersedia?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Kami menerima pembayaran melalui transfer bank (BCA, Mandiri, BNI, BRI), e-wallet (GoPay, OVO, Dana), 
                                    dan kartu kredit/debit. Untuk pemesanan dalam jumlah besar, tersedia opsi pembayaran tempo untuk reseller.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="map-container">
            <iframe
                title="Peta Rasa Group Headquarters"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3965.598327932857!2d107.10783177538464!3d-6.316376561811204!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e699bde0142aab3%3A0x3b89b0730a884347!2sRasa%20Group%20Headquarters!5e0!3m2!1sid!2sid!4v1777757273799!5m2!1sid!2sid"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </section>

        </div>
    </div>
@endsection

