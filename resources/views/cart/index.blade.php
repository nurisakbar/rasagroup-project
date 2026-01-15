@extends('layouts.shop')

@section('title', 'Keranjang Belanja')

@section('content')
<div class="container py-5">
    <h2 class="mb-4"><i class="bi bi-cart3 me-2"></i>Keranjang Belanja</h2>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($carts->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-cart-x" style="font-size: 4rem; color: #ddd;"></i>
            <h4 class="mt-3 text-muted">Keranjang Anda kosong</h4>
            <p class="text-muted">Mulai belanja dan tambahkan produk ke keranjang</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary mt-2">
                <i class="bi bi-bag me-2"></i>Mulai Belanja
            </a>
        </div>
    @else
        <!-- Hub Info Banner -->
        @if($cartWarehouse)
        <div class="alert alert-info mb-4" style="background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%); border: none; border-radius: 15px;">
            <div class="d-flex align-items-center">
                <i class="bi bi-geo-alt-fill me-3" style="font-size: 1.5rem; color: #1976d2;"></i>
                <div>
                    <strong>Hub Pengirim:</strong> {{ $cartWarehouse->name }}
                    <br>
                    <small class="text-muted">{{ $cartWarehouse->full_location }}</small>
                </div>
                <div class="ms-auto">
                    <form action="{{ route('cart.clear') }}" method="POST" class="d-inline" onsubmit="return confirm('Kosongkan keranjang? Semua item akan dihapus.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-trash me-1"></i>Kosongkan Keranjang
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <div class="card shadow-sm" style="border-radius: 15px; overflow: hidden;">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th class="px-4 py-3">Produk</th>
                            <th class="py-3">Harga</th>
                            <th class="py-3 text-center">Jumlah</th>
                            <th class="py-3 text-end">Subtotal</th>
                            @auth
                            <th class="py-3 text-center px-4">Aksi</th>
                            @endauth
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($carts as $cart)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        @if($cart->product->image)
                                            <img src="{{ $cart->product->image_url }}" alt="{{ $cart->product->name }}" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                            <img src="https://via.placeholder.com/60/e74c3c/fff?text=P" alt="No Image" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                        @endif
                                        <div class="ms-3">
                                            <strong>{{ $cart->product->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $cart->product->formatted_weight }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 align-middle">Rp {{ number_format($cart->product->price, 0, ',', '.') }}</td>
                                <td class="py-3 align-middle text-center">
                                    @auth
                                        <form action="{{ route('cart.update', $cart) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="number" name="quantity" value="{{ $cart->quantity }}" min="1" class="form-control form-control-sm text-center mx-auto" style="width: 80px;" onchange="this.form.submit()">
                                        </form>
                                    @else
                                        <span>{{ $cart->quantity }}</span>
                                    @endauth
                                </td>
                                <td class="py-3 align-middle text-end fw-semibold" style="color: var(--primary-color);">
                                    Rp {{ number_format($cart->product->price * $cart->quantity, 0, ',', '.') }}
                                </td>
                                @auth
                                <td class="py-3 align-middle text-center px-4">
                                    <form action="{{ route('cart.destroy', $cart) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Hapus item ini?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                                @endauth
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background: #f8f9fa;">
                        <tr>
                            <td colspan="{{ auth()->check() ? '3' : '2' }}" class="px-4 py-3 text-end fw-bold">Total:</td>
                            <td class="py-3 text-end fw-bold" style="font-size: 1.2rem; color: var(--primary-color);">
                                Rp {{ number_format($total, 0, ',', '.') }}
                            </td>
                            @auth
                            <td></td>
                            @endauth
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Lanjut Belanja
            </a>
            @auth
                <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg px-5" style="background: var(--gradient-primary); border: none; border-radius: 10px;">
                    <i class="bi bi-credit-card me-2"></i>Checkout
                </a>
            @else
                <div>
                    <small class="text-muted d-block mb-2">Silakan login untuk melanjutkan checkout</small>
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login untuk Checkout
                    </a>
                </div>
            @endauth
        </div>
    @endif
</div>
@endsection
