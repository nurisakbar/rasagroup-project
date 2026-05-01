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
    
    /* Contact Form */
    .contact-form-section {
        padding: 80px 0;
        background: transparent;
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
    
    .contact-form-card {
        background: white;
        border-radius: 30px;
        padding: 60px;
        box-shadow: 0 15px 50px rgba(0,0,0,0.05);
    }
    
    .form-label {
        font-family: 'Fira Sans', sans-serif !important;
        font-weight: 600;
        color: #253D4E;
        margin-bottom: 8px;
    }
    
    .form-control {
        background: #F8F9FA !important;
        border: 1px solid #ECECEC !important;
        border-radius: 12px !important;
        padding: 15px 20px !important;
        font-family: 'Lato', sans-serif !important;
        transition: all 0.3s ease !important;
    }
    
    .form-control:focus {
        background: #ffffff !important;
        border-color: #6A1B1B !important;
        box-shadow: 0 0 0 4px rgba(106, 27, 27, 0.05) !important;
    }
    
    .btn-submit {
        background: #6A1B1B !important;
        color: white !important;
        border: none;
        border-radius: 12px;
        padding: 18px 45px;
        font-family: 'Fira Sans', sans-serif !important;
        font-size: 1.1rem;
        font-weight: 700;
        transition: all 0.3s ease;
    }
    
    .btn-submit:hover {
        background-color: #4D1313 !important;
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(106, 27, 27, 0.3);
    }
    
    /* Map Section */
    .map-container {
        height: 500px;
        border-radius: 30px;
        overflow: hidden;
        margin: 0 50px 80px;
        box-shadow: 0 15px 50px rgba(0,0,0,0.08);
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
    
    /* Alert */
    .alert-success-custom {
        background: #27ae60 !important;
        color: white;
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
    <div class="contact-page pages">
        <div class="page-header breadcrumb-wrap">
            <div class="container">
                <div class="breadcrumb">
                    <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
                    <span></span> Hubungi Kami
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

        </div>
    </div>
@endsection

