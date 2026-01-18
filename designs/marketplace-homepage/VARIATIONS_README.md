# Variasi Desain Marketplace Homepage

Dokumen ini menjelaskan 2 variasi desain tambahan untuk halaman utama marketplace Rasa Group.

## Variasi 1: Desain dengan Sidebar dan Filter (`variation-1.html`)

### Karakteristik:
- **Layout**: Hero section dengan sidebar kategori di sebelah kiri
- **Fitur Unik**: 
  - Sidebar kategori yang selalu terlihat di hero section
  - Filter sidebar untuk produk dengan berbagai opsi (kategori, harga, rating)
  - Promo banner dengan animasi rotasi
  - Grid produk dengan sidebar filter yang sticky
- **Warna**: Menggunakan skema warna yang sama dengan desain utama (merah/orange)
- **Font**: Poppins
- **Cocok untuk**: Pengguna yang ingin navigasi cepat ke kategori dan filter produk yang lebih detail

### Struktur Halaman:
1. Navbar (fixed top)
2. Hero Section dengan Sidebar Kategori
3. Promo Banner dengan animasi
4. Products Section dengan Filter Sidebar
5. Footer

### Keunggulan:
- ✅ Navigasi kategori yang mudah diakses
- ✅ Filter produk yang lengkap dan terorganisir
- ✅ Layout yang memanfaatkan ruang dengan baik
- ✅ Cocok untuk marketplace dengan banyak kategori produk

---

## Variasi 2: Desain Minimalis Modern (`variation-2.html`)

### Karakteristik:
- **Layout**: Clean, minimalis dengan fokus pada konten
- **Fitur Unik**:
  - Hero section centered dengan call-to-action yang jelas
  - Stats section menampilkan angka-angka penting
  - Desain card yang lebih sederhana dan bersih
  - CTA section dengan gradient background
  - Border dan shadow yang lebih subtle
- **Warna**: Skema warna biru modern (blue/teal) berbeda dari desain utama
- **Font**: Inter (lebih modern dan clean)
- **Cocok untuk**: Pengguna yang menyukai desain minimalis dan modern

### Struktur Halaman:
1. Navbar (fixed top, border bottom)
2. Hero Section (centered)
3. Stats Section (4 statistik penting)
4. Categories Section (grid minimalis)
5. Products Section (grid clean)
6. Distributors Section (card minimalis)
7. CTA Section (gradient background)
8. Footer

### Keunggulan:
- ✅ Desain yang clean dan modern
- ✅ Fokus pada konten tanpa distraksi
- ✅ Skema warna yang berbeda (biru) untuk variasi
- ✅ Typography yang lebih readable
- ✅ Cocok untuk brand yang ingin terlihat profesional dan modern

---

## Perbandingan dengan Desain Utama (`index.html`)

| Fitur | Desain Utama | Variasi 1 | Variasi 2 |
|-------|--------------|-----------|-----------|
| **Slider Hero** | ✅ Ya (3 slides) | ❌ Tidak | ❌ Tidak |
| **Sidebar Kategori** | ❌ Tidak | ✅ Ya (di hero) | ❌ Tidak |
| **Filter Produk** | ❌ Tidak | ✅ Ya (lengkap) | ❌ Tidak |
| **Stats Section** | ❌ Tidak | ❌ Tidak | ✅ Ya |
| **Promo Banner** | ❌ Tidak | ✅ Ya (animasi) | ❌ Tidak |
| **CTA Section** | ❌ Tidak | ❌ Tidak | ✅ Ya |
| **Warna Utama** | Merah/Orange | Merah/Orange | Biru/Teal |
| **Font** | Poppins | Poppins | Inter |
| **Style** | Modern dengan efek | Sidebar & Filter | Minimalis |

---

## Cara Menggunakan

### Untuk Implementasi Laravel:

1. **Variasi 1** (`variation-1.html`):
   - Cocok untuk halaman produk dengan filter
   - Bisa digunakan sebagai alternatif layout untuk `/products`
   - Sidebar kategori bisa diintegrasikan dengan data dari database

2. **Variasi 2** (`variation-2.html`):
   - Cocok sebagai alternatif homepage
   - Bisa digunakan untuk A/B testing
   - Stats section bisa diisi dengan data real-time dari database

### Integrasi dengan Laravel Blade:

Kedua variasi dapat diintegrasikan dengan Laravel menggunakan pendekatan yang sama seperti `index.html`:
- Ganti link statis dengan `route()` helper
- Ganti data dummy dengan `@foreach` loops
- Gunakan `asset()` untuk gambar produk
- Tambahkan `@push('styles')` dan `@push('scripts')` untuk CSS/JS

---

## Rekomendasi Penggunaan

1. **Desain Utama** (`index.html`): 
   - Gunakan sebagai homepage default
   - Cocok untuk first impression yang menarik dengan slider

2. **Variasi 1** (`variation-1.html`):
   - Gunakan untuk halaman produk/katalog
   - Cocok jika ingin fitur filter yang lengkap
   - Ideal untuk pengguna yang sering browsing kategori

3. **Variasi 2** (`variation-2.html`):
   - Gunakan sebagai alternatif homepage
   - Cocok untuk brand yang ingin terlihat lebih profesional
   - Ideal untuk A/B testing conversion rate

---

## Catatan Teknis

- Semua variasi menggunakan Bootstrap 5.3.2
- Semua variasi responsive dan mobile-friendly
- Semua variasi menggunakan Bootstrap Icons
- Image URLs menggunakan Unsplash dengan parameter quality tinggi
- Semua variasi siap untuk diintegrasikan dengan Laravel

---

## File Structure

```
designs/marketplace-homepage/
├── index.html          # Desain utama (dengan slider)
├── variation-1.html    # Variasi dengan sidebar & filter
├── variation-2.html    # Variasi minimalis modern
├── distributors.html   # Halaman distributor
├── README.md           # Dokumentasi desain utama
└── VARIATIONS_README.md # Dokumentasi variasi (file ini)
```

