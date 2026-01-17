# Desain Halaman Utama Marketplace Sirup

Desain modern dan responsif untuk halaman utama marketplace yang menjual sirup menggunakan Bootstrap 5.

## Fitur

### 1. **Hero Slider (Carousel)**
- Slider dengan 3 slide promo
- Auto-play setiap 5 detik
- Navigation controls (prev/next)
- Caption dengan call-to-action buttons
- Responsif untuk semua ukuran layar

### 2. **Kategori Produk**
- Grid layout dengan 6 kategori utama
- Icon yang menarik untuk setiap kategori
- Hover effect dengan animasi
- Responsif: 2 kolom di mobile, 6 kolom di desktop

### 3. **List Produk**
- Grid layout produk dengan card design
- Informasi lengkap: gambar, kategori, nama, rating, harga
- Badge untuk produk baru dan diskon
- Hover effect dengan zoom pada gambar
- Responsif: 2 kolom di mobile, 4 kolom di desktop

### 4. **Navbar**
- Fixed top navigation
- Responsive hamburger menu untuk mobile
- Shopping cart icon dengan badge

### 5. **Footer**
- Informasi perusahaan
- Link navigasi
- Social media icons
- Kontak informasi

## Teknologi yang Digunakan

- **Bootstrap 5.3.2** - Framework CSS
- **Bootstrap Icons** - Icon library
- **Google Fonts (Poppins)** - Typography
- **Vanilla JavaScript** - Interaktivitas

## Struktur File

```
designs/marketplace-homepage/
├── index.html          # File HTML utama
└── README.md          # Dokumentasi
```

## Cara Menggunakan

1. Buka file `index.html` di browser
2. Atau serve melalui web server lokal:
   ```bash
   # Menggunakan PHP
   php -S localhost:8000
   
   # Menggunakan Python
   python -m http.server 8000
   ```

## Warna Tema

- **Primary**: `#E63946` (Merah)
- **Secondary**: `#F77F00` (Orange)
- **Accent**: `#FCBF49` (Kuning)
- **Dark**: `#1D3557` (Biru gelap)
- **Light BG**: `#F8F9FA` (Abu-abu terang)

## Responsive Breakpoints

- **Mobile**: < 576px
- **Tablet**: 576px - 768px
- **Desktop**: > 768px

## Fitur Responsif

- Navbar dengan hamburger menu di mobile
- Slider dengan tinggi yang disesuaikan
- Grid layout yang adaptif
- Font size yang responsif
- Touch-friendly buttons di mobile

## Customization

### Mengubah Gambar Slider
Edit URL gambar di bagian carousel:
```html
<img src="URL_GAMBAR_ANDA" class="d-block w-100" alt="Promo">
```

### Menambah Produk
Copy struktur product card dan paste di dalam `.row` di section products:
```html
<div class="col-6 col-md-4 col-lg-3">
    <a href="#" class="product-card">
        <!-- Product content -->
    </a>
</div>
```

### Menambah Kategori
Copy struktur category card dan paste di dalam `.row` di section categories:
```html
<div class="col-6 col-md-4 col-lg-2">
    <a href="#" class="category-card">
        <!-- Category content -->
    </a>
</div>
```

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Catatan

- Gambar menggunakan placeholder dari Unsplash
- Semua link masih menggunakan `#` (placeholder)
- Functionality add to cart masih menggunakan alert (placeholder)
- Integrasikan dengan backend sesuai kebutuhan

## Next Steps

1. Integrasikan dengan backend Laravel
2. Ganti placeholder images dengan gambar produk asli
3. Implementasikan fungsi add to cart yang sebenarnya
4. Tambahkan filter dan search functionality
5. Tambahkan pagination untuk produk
6. Integrasikan dengan sistem authentication

