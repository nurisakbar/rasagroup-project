# Setup AdminLTE 2 - Dokumentasi

## Instalasi

AdminLTE 2 sudah terinstall dan dikonfigurasi untuk project ini.

### Package yang Terinstall

- `admin-lte@^2.4` - Template AdminLTE 2
- `bootstrap@3.4.1` - Bootstrap 3 (required oleh AdminLTE 2)
- `jquery@3.6.0` - jQuery (required oleh AdminLTE 2)

### Lokasi File

File AdminLTE 2 berada di:
- **Public Assets**: `/public/adminlte/`
  - CSS: `/public/adminlte/css/`
  - JS: `/public/adminlte/js/`
  - Images: `/public/adminlte/img/`
  - Plugins: `/public/adminlte/plugins/`

### Layout Admin

Layout admin sudah dibuat di:
- **Blade Layout**: `/resources/views/layouts/admin.blade.php`

## Cara Penggunaan

### 1. Menggunakan Layout Admin di View

```blade
@extends('layouts.admin')

@section('title', 'Judul Halaman')
@section('page-title', 'Judul Halaman')
@section('page-description', 'Deskripsi singkat')

@section('breadcrumb')
    <li class="active">Halaman Saat Ini</li>
@endsection

@section('content')
    <!-- Konten halaman di sini -->
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Judul Box</h3>
        </div>
        <div class="box-body">
            Konten box di sini
        </div>
    </div>
@endsection
```

### 2. Komponen AdminLTE yang Tersedia

#### Box
```blade
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title">Judul Box</h3>
    </div>
    <div class="box-body">
        Konten box
    </div>
    <div class="box-footer">
        Footer box
    </div>
</div>
```

#### Table
```blade
<div class="box">
    <div class="box-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Data</td>
                    <td>
                        <a href="#" class="btn btn-primary btn-sm">Edit</a>
                        <a href="#" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
```

#### Form
```blade
<div class="box">
    <div class="box-header">
        <h3 class="box-title">Form</h3>
    </div>
    <form method="POST" action="{{ route('admin.products.store') }}">
        @csrf
        <div class="box-body">
            <div class="form-group">
                <label for="name">Nama</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}">
            </div>
        </div>
        <div class="box-footer">
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>
```

#### Alert
```blade
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <h4><i class="icon fa fa-check"></i> Success!</h4>
    Pesan sukses
</div>
```

### 3. Menu Sidebar

Menu sidebar sudah dikonfigurasi di `layouts/admin.blade.php`. Untuk menambahkan menu baru:

```blade
<li class="{{ request()->routeIs('admin.menu.*') ? 'active' : '' }}">
    <a href="{{ route('admin.menu.index') }}">
        <i class="fa fa-icon"></i> <span>Menu Name</span>
    </a>
</li>
```

### 4. Skin/Warna Tema

AdminLTE 2 menyediakan beberapa skin. Skin default adalah `skin-blue`. Untuk mengubah skin, edit di `layouts/admin.blade.php`:

```blade
<body class="hold-transition skin-blue sidebar-mini">
```

Skin yang tersedia:
- `skin-blue` (default)
- `skin-black`
- `skin-green`
- `skin-purple`
- `skin-red`
- `skin-yellow`

File CSS skin ada di: `/public/adminlte/css/skins/`

### 5. Menambahkan Custom CSS/JS

Gunakan stack di view:

```blade
@push('styles')
<link rel="stylesheet" href="{{ asset('path/to/custom.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('path/to/custom.js') }}"></script>
@endpush
```

## Struktur Menu Sidebar Saat Ini

1. **Dashboard** - `/admin/dashboard`
2. **Produk** - `/admin/products`
3. **Pesanan** - `/admin/orders`

## Dependencies

- **Bootstrap 3.4.1**: `/public/adminlte/plugins/bootstrap/`
- **jQuery 3.6.0**: `/public/adminlte/plugins/jquery/`
- **Font Awesome 4.7.0**: CDN (via layout)
- **Ionicons 2.0.1**: CDN (via layout)

## Referensi

- [AdminLTE 2 Documentation](https://adminlte.io/themes/v2/)
- [AdminLTE 2 GitHub](https://github.com/ColorlibHQ/AdminLTE/tree/v2.4.18)

## Catatan

- AdminLTE 2 menggunakan Bootstrap 3, bukan Bootstrap 4/5
- Pastikan menggunakan class Bootstrap 3 saat membuat komponen
- File AdminLTE sudah di-copy ke public folder untuk akses langsung via asset()









