@extends('layouts.shop')

@section('title', 'Tentang Kami')

@push('styles')
<style>
    /* Hero Banner */
    .page-hero {
        background: linear-gradient(135deg, rgba(231, 76, 60, 0.9) 0%, rgba(243, 156, 18, 0.8) 100%), 
                    url('https://images.unsplash.com/photo-1558642452-9d2a7deb7f62?w=1920');
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
    
    /* About Section */
    .about-section {
        padding: 80px 0;
    }
    
    .about-content h2 {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 25px;
    }
    
    .about-content p {
        color: #6c757d;
        font-size: 1.1rem;
        line-height: 1.9;
        margin-bottom: 20px;
    }
    
    .about-image {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    }
    
    .about-image img {
        width: 100%;
        height: 400px;
        object-fit: cover;
    }
    
    /* Vision Mission */
    .vision-mission {
        background: var(--light-color);
        padding: 80px 0;
    }
    
    .vm-card {
        background: white;
        border-radius: 20px;
        padding: 40px 30px;
        height: 100%;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        transition: transform 0.3s ease;
    }
    
    .vm-card:hover {
        transform: translateY(-10px);
    }
    
    .vm-icon {
        width: 80px;
        height: 80px;
        background: var(--gradient-primary);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: white;
        margin-bottom: 25px;
    }
    
    .vm-card h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 15px;
    }
    
    .vm-card p {
        color: #6c757d;
        line-height: 1.8;
        margin-bottom: 0;
    }
    
    /* Stats Section */
    .stats-section {
        padding: 80px 0;
        background: var(--gradient-primary);
        color: white;
    }
    
    .stat-box {
        text-align: center;
        padding: 30px 20px;
    }
    
    .stat-box .stat-number {
        font-size: 3.5rem;
        font-weight: 700;
        display: block;
        margin-bottom: 10px;
    }
    
    .stat-box .stat-label {
        font-size: 1.1rem;
        opacity: 0.9;
    }
    
    /* Values Section */
    .values-section {
        padding: 80px 0;
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
    
    .value-card {
        text-align: center;
        padding: 40px 25px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 5px 30px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .value-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 50px rgba(0,0,0,0.15);
    }
    
    .value-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
        font-size: 2.5rem;
        color: var(--primary-color);
    }
    
    .value-card h4 {
        font-size: 1.3rem;
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 15px;
    }
    
    .value-card p {
        color: #6c757d;
        line-height: 1.7;
        margin-bottom: 0;
    }
    
    /* Team Section */
    .team-section {
        padding: 80px 0;
        background: var(--light-color);
    }
    
    .team-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }
    
    .team-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    }
    
    .team-card img {
        width: 100%;
        height: 280px;
        object-fit: cover;
    }
    
    .team-info {
        padding: 25px;
        text-align: center;
    }
    
    .team-info h5 {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 5px;
    }
    
    .team-info .position {
        color: var(--primary-color);
        font-weight: 500;
        margin-bottom: 15px;
    }
    
    .team-social {
        display: flex;
        justify-content: center;
        gap: 10px;
    }
    
    .team-social a {
        width: 35px;
        height: 35px;
        background: var(--light-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--dark-color);
        transition: all 0.3s ease;
    }
    
    .team-social a:hover {
        background: var(--primary-color);
        color: white;
    }
    
    /* CTA Section */
    .cta-section {
        padding: 80px 0;
        background: var(--dark-color);
        color: white;
        text-align: center;
    }
    
    .cta-section h2 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 15px;
    }
    
    .cta-section p {
        font-size: 1.2rem;
        opacity: 0.9;
        margin-bottom: 30px;
    }
    
    .btn-cta {
        background: var(--gradient-primary);
        color: white;
        border: none;
        padding: 15px 40px;
        border-radius: 50px;
        font-size: 1.1rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
    }
    
    .btn-cta:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(231, 76, 60, 0.4);
        color: white;
    }
    
    @media (max-width: 768px) {
        .page-hero h1 {
            font-size: 2rem;
        }
        
        .about-content h2,
        .section-title h2,
        .cta-section h2 {
            font-size: 1.8rem;
        }
        
        .stat-box .stat-number {
            font-size: 2.5rem;
        }
    }
</style>
@endpush

@section('content')
    <!-- Hero Banner -->
    <section class="page-hero">
        <div class="container">
            <h1><i class="bi bi-info-circle me-2"></i>Tentang Kami</h1>
            <p>Mengenal lebih dekat Rasa Group - Produsen Sirup Berkualitas</p>
            <div class="breadcrumb-nav">
                <a href="{{ route('home') }}"><i class="bi bi-house me-1"></i> Beranda</a>
                <span>/</span>
                <span>Tentang Kami</span>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="about-image">
                        <img src="https://images.unsplash.com/photo-1558642452-9d2a7deb7f62?w=800" alt="Rasa Group Factory">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-content">
                        <h2>Sejarah Rasa Group</h2>
                        <p>
                            <strong>Rasa Group</strong> didirikan pada tahun 2010 dengan visi menjadi produsen sirup berkualitas tinggi 
                            yang mampu menjangkau seluruh pelosok Indonesia. Berawal dari sebuah usaha kecil di Jakarta, 
                            kami terus berkembang hingga kini memiliki fasilitas produksi modern dengan standar kualitas internasional.
                        </p>
                        <p>
                            Selama lebih dari 14 tahun perjalanan, kami telah melayani ribuan pelanggan dari berbagai kalangan, 
                            mulai dari pengusaha minuman, hotel, restoran, hingga konsumen rumah tangga. Kepercayaan pelanggan 
                            adalah motivasi kami untuk terus berinovasi dan meningkatkan kualitas produk.
                        </p>
                        <p>
                            Dengan lebih dari 20 varian rasa yang kami tawarkan, Rasa Group berkomitmen untuk selalu menghadirkan 
                            produk dengan cita rasa terbaik yang dibuat dari bahan-bahan pilihan berkualitas tinggi.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Vision Mission -->
    <section class="vision-mission">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="vm-card">
                        <div class="vm-icon">
                            <i class="bi bi-eye"></i>
                        </div>
                        <h3>Visi Kami</h3>
                        <p>
                            Menjadi produsen sirup terkemuka di Indonesia yang dikenal karena kualitas produk yang unggul, 
                            inovasi rasa yang kreatif, dan pelayanan terbaik kepada pelanggan. Kami bercita-cita untuk 
                            membawa kesegaran dan kebahagiaan ke setiap rumah di seluruh nusantara.
                        </p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="vm-card">
                        <div class="vm-icon">
                            <i class="bi bi-flag"></i>
                        </div>
                        <h3>Misi Kami</h3>
                        <p>
                            Menghasilkan produk sirup berkualitas tinggi dengan standar keamanan pangan yang ketat. 
                            Terus berinovasi dalam pengembangan varian rasa baru yang sesuai dengan selera masyarakat. 
                            Memberikan pelayanan terbaik dan harga yang kompetitif untuk kepuasan pelanggan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-6">
                    <div class="stat-box">
                        <span class="stat-number">14+</span>
                        <span class="stat-label">Tahun Pengalaman</span>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="stat-box">
                        <span class="stat-number">20+</span>
                        <span class="stat-label">Varian Rasa</span>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="stat-box">
                        <span class="stat-number">1000+</span>
                        <span class="stat-label">Pelanggan Puas</span>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="stat-box">
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Kota Terjangkau</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="values-section">
        <div class="container">
            <div class="section-title">
                <h2>Nilai-Nilai Kami</h2>
                <div class="divider"></div>
                <p class="text-muted">Prinsip yang menjadi landasan kami dalam berkarya</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="bi bi-award"></i>
                        </div>
                        <h4>Kualitas</h4>
                        <p>Selalu mengutamakan kualitas produk tanpa kompromi untuk kepuasan pelanggan</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="bi bi-lightbulb"></i>
                        </div>
                        <h4>Inovasi</h4>
                        <p>Terus berinovasi mengembangkan varian rasa baru sesuai kebutuhan pasar</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="bi bi-heart"></i>
                        </div>
                        <h4>Integritas</h4>
                        <p>Menjunjung tinggi kejujuran dan transparansi dalam setiap aspek bisnis</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <h4>Kepedulian</h4>
                        <p>Peduli terhadap pelanggan, karyawan, dan lingkungan sekitar</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <div class="section-title">
                <h2>Tim Kami</h2>
                <div class="divider"></div>
                <p class="text-muted">Orang-orang hebat di balik kesuksesan Rasa Group</p>
            </div>
            
            <div class="row g-4 justify-content-center">
                <div class="col-lg-3 col-md-6">
                    <div class="team-card">
                        <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400" alt="CEO">
                        <div class="team-info">
                            <h5>Ahmad Susanto</h5>
                            <p class="position">Chief Executive Officer</p>
                            <div class="team-social">
                                <a href="#"><i class="bi bi-linkedin"></i></a>
                                <a href="#"><i class="bi bi-twitter"></i></a>
                                <a href="#"><i class="bi bi-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="team-card">
                        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400" alt="COO">
                        <div class="team-info">
                            <h5>Siti Rahayu</h5>
                            <p class="position">Chief Operating Officer</p>
                            <div class="team-social">
                                <a href="#"><i class="bi bi-linkedin"></i></a>
                                <a href="#"><i class="bi bi-twitter"></i></a>
                                <a href="#"><i class="bi bi-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="team-card">
                        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400" alt="Production Manager">
                        <div class="team-info">
                            <h5>Budi Pratama</h5>
                            <p class="position">Production Manager</p>
                            <div class="team-social">
                                <a href="#"><i class="bi bi-linkedin"></i></a>
                                <a href="#"><i class="bi bi-twitter"></i></a>
                                <a href="#"><i class="bi bi-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="team-card">
                        <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?w=400" alt="Marketing Manager">
                        <div class="team-info">
                            <h5>Dewi Anggraini</h5>
                            <p class="position">Marketing Manager</p>
                            <div class="team-social">
                                <a href="#"><i class="bi bi-linkedin"></i></a>
                                <a href="#"><i class="bi bi-twitter"></i></a>
                                <a href="#"><i class="bi bi-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Siap Bermitra dengan Kami?</h2>
            <p>Hubungi kami untuk informasi kerjasama dan pemesanan dalam jumlah besar</p>
            <a href="{{ route('contact') }}" class="btn-cta">
                <i class="bi bi-chat-dots me-2"></i>Hubungi Kami
            </a>
        </div>
    </section>
@endsection

