@extends('layouts.shop')

@section('title', 'Kontak')

@push('styles')
<style>
    /* Hero Banner */
    .page-hero {
        background: linear-gradient(135deg, rgba(231, 76, 60, 0.9) 0%, rgba(243, 156, 18, 0.8) 100%), 
                    url('https://images.unsplash.com/photo-1423666639041-f56000c27a9a?w=1920');
        background-size: cover;
        background-position: center;
        padding: 100px 0;
        color: white;
        text-align: center;
    }
    
    .page-hero h1 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 15px;
        text-shadow: 2px 2px 10px rgba(0,0,0,0.2);
    }
    
    .page-hero p {
        font-size: 1.2rem;
        opacity: 0.95;
    }
    
    .breadcrumb-nav {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 20px;
    }
    
    .breadcrumb-nav a {
        color: white;
        text-decoration: none;
        opacity: 0.8;
    }
    
    .breadcrumb-nav a:hover {
        opacity: 1;
    }
    
    .breadcrumb-nav span {
        opacity: 0.6;
    }
    
    /* Contact Section */
    .contact-section {
        padding: 80px 0;
    }
    
    /* Contact Info Cards */
    .contact-info-card {
        background: white;
        border-radius: 20px;
        padding: 35px 25px;
        text-align: center;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .contact-info-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    }
    
    .contact-icon {
        width: 80px;
        height: 80px;
        background: var(--gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 2rem;
        color: white;
    }
    
    .contact-info-card h4 {
        font-size: 1.3rem;
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 15px;
    }
    
    .contact-info-card p {
        color: #6c757d;
        margin-bottom: 5px;
        line-height: 1.7;
    }
    
    .contact-info-card a {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 500;
    }
    
    .contact-info-card a:hover {
        text-decoration: underline;
    }
    
    /* Contact Form */
    .contact-form-section {
        padding: 80px 0;
        background: var(--light-color);
    }
    
    .section-title {
        text-align: center;
        margin-bottom: 50px;
    }
    
    .section-title h2 {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 15px;
    }
    
    .section-title .divider {
        width: 80px;
        height: 4px;
        background: var(--gradient-primary);
        margin: 15px auto;
        border-radius: 2px;
    }
    
    .contact-form-card {
        background: white;
        border-radius: 25px;
        padding: 50px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.1);
    }
    
    .form-label {
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 10px;
    }
    
    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 15px 20px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(231, 76, 60, 0.1);
    }
    
    textarea.form-control {
        min-height: 150px;
        resize: vertical;
    }
    
    .btn-submit {
        background: var(--gradient-primary);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 15px 40px;
        font-size: 1.1rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-submit:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(231, 76, 60, 0.4);
        color: white;
    }
    
    /* Map Section */
    .map-section {
        padding: 0;
    }
    
    .map-container {
        height: 450px;
        border-radius: 0;
        overflow: hidden;
    }
    
    .map-container iframe {
        width: 100%;
        height: 100%;
        border: none;
    }
    
    /* Social Section */
    .social-section {
        padding: 80px 0;
        background: var(--dark-color);
        color: white;
        text-align: center;
    }
    
    .social-section h2 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 15px;
    }
    
    .social-section p {
        opacity: 0.9;
        margin-bottom: 30px;
    }
    
    .social-links {
        display: flex;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
    }
    
    .social-link {
        width: 60px;
        height: 60px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .social-link:hover {
        background: var(--primary-color);
        color: white;
        transform: translateY(-5px);
    }
    
    /* FAQ Section */
    .faq-section {
        padding: 80px 0;
    }
    
    .accordion-item {
        border: none;
        margin-bottom: 15px;
        border-radius: 15px !important;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
    
    .accordion-button {
        font-weight: 600;
        color: var(--dark-color);
        padding: 20px 25px;
        background: white;
    }
    
    .accordion-button:not(.collapsed) {
        background: var(--gradient-primary);
        color: white;
        box-shadow: none;
    }
    
    .accordion-button:focus {
        box-shadow: none;
    }
    
    .accordion-button::after {
        background-size: 1rem;
    }
    
    .accordion-button:not(.collapsed)::after {
        filter: brightness(0) invert(1);
    }
    
    .accordion-body {
        padding: 20px 25px;
        color: #6c757d;
        line-height: 1.8;
    }
    
    /* Alert */
    .alert-success-custom {
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
        color: white;
        border: none;
        border-radius: 15px;
        padding: 20px 25px;
    }
    
    @media (max-width: 768px) {
        .page-hero h1 {
            font-size: 2rem;
        }
        
        .section-title h2 {
            font-size: 1.8rem;
        }
        
        .contact-form-card {
            padding: 30px 20px;
        }
    }
</style>
@endpush

@section('content')
    <!-- Hero Banner -->
    <section class="page-hero">
        <div class="container">
            <h1><i class="bi bi-chat-dots me-2"></i>Hubungi Kami</h1>
            <p>Kami siap membantu dan menjawab pertanyaan Anda</p>
            <div class="breadcrumb-nav">
                <a href="{{ route('home') }}"><i class="bi bi-house me-1"></i> Beranda</a>
                <span>/</span>
                <span>Kontak</span>
            </div>
        </div>
    </section>

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
                        <p>Jl. Rasa Manis No. 123</p>
                        <p>Jakarta Selatan, Indonesia 12345</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="contact-info-card">
                        <div class="contact-icon">
                            <i class="bi bi-telephone"></i>
                        </div>
                        <h4>Telepon</h4>
                        <p><a href="tel:+628123456789">+62 812-3456-7890</a></p>
                        <p><a href="tel:+622112345678">(021) 1234-5678</a></p>
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

    <!-- Contact Form -->
    <section class="contact-form-section">
        <div class="container">
            <div class="section-title">
                <h2>Kirim Pesan</h2>
                <div class="divider"></div>
                <p class="text-muted">Ada pertanyaan atau ingin berkolaborasi? Isi form di bawah ini</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="contact-form-card">
                        @if(session('success'))
                            <div class="alert alert-success-custom mb-4">
                                <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                            </div>
                        @endif
                        
                        <form action="{{ route('contact.send') }}" method="POST">
                            @csrf
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">
                                        <i class="bi bi-person me-1"></i> Nama Lengkap
                                    </label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" 
                                           placeholder="Masukkan nama lengkap" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">
                                        <i class="bi bi-envelope me-1"></i> Email
                                    </label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" 
                                           placeholder="Masukkan email" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">
                                        <i class="bi bi-telephone me-1"></i> No. Telepon
                                    </label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}" 
                                           placeholder="Contoh: 08123456789">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="subject" class="form-label">
                                        <i class="bi bi-tag me-1"></i> Subjek
                                    </label>
                                    <select class="form-control @error('subject') is-invalid @enderror" 
                                            id="subject" name="subject" required>
                                        <option value="">-- Pilih Subjek --</option>
                                        <option value="Pertanyaan Umum" {{ old('subject') == 'Pertanyaan Umum' ? 'selected' : '' }}>Pertanyaan Umum</option>
                                        <option value="Pemesanan" {{ old('subject') == 'Pemesanan' ? 'selected' : '' }}>Pemesanan</option>
                                        <option value="Kerjasama" {{ old('subject') == 'Kerjasama' ? 'selected' : '' }}>Kerjasama / Partnership</option>
                                        <option value="Komplain" {{ old('subject') == 'Komplain' ? 'selected' : '' }}>Komplain</option>
                                        <option value="Lainnya" {{ old('subject') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                    </select>
                                    @error('subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="message" class="form-label">
                                        <i class="bi bi-chat-text me-1"></i> Pesan
                                    </label>
                                    <textarea class="form-control @error('message') is-invalid @enderror" 
                                              id="message" name="message" rows="5" 
                                              placeholder="Tulis pesan Anda di sini..." required>{{ old('message') }}</textarea>
                                    @error('message')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-submit">
                                        <i class="bi bi-send me-2"></i> Kirim Pesan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="map-container">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.2904453128064!2d106.82646307486942!3d-6.224751793773457!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f3e97e44fa45%3A0x7d06c08ee4e0a9d4!2sMonumen%20Nasional!5e0!3m2!1sid!2sid!4v1703142000000!5m2!1sid!2sid" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
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

    <!-- Social Section -->
    <section class="social-section">
        <div class="container">
            <h2>Ikuti Kami di Media Sosial</h2>
            <p>Dapatkan info terbaru, promo, dan konten menarik lainnya</p>
            <div class="social-links">
                <a href="#" class="social-link" title="Facebook">
                    <i class="bi bi-facebook"></i>
                </a>
                <a href="#" class="social-link" title="Instagram">
                    <i class="bi bi-instagram"></i>
                </a>
                <a href="#" class="social-link" title="Twitter">
                    <i class="bi bi-twitter-x"></i>
                </a>
                <a href="#" class="social-link" title="YouTube">
                    <i class="bi bi-youtube"></i>
                </a>
                <a href="#" class="social-link" title="WhatsApp">
                    <i class="bi bi-whatsapp"></i>
                </a>
                <a href="#" class="social-link" title="TikTok">
                    <i class="bi bi-tiktok"></i>
                </a>
            </div>
        </div>
    </section>
@endsection

