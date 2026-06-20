@extends('themes.nest.layouts.app')

@section('title', 'Keranjang Belanja')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> Toko
            <span></span> Keranjang
        </div>
    </div>
</div>
<div class="container mb-80 mt-50 rg-cart-page">
    <div class="row">
        <div class="col-lg-8 mb-40">
            <h1 class="heading-2 mb-10">Keranjang Belanja Anda</h1>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="text-body mb-0">Ada <span class="text-brand">{{ $carts->count() }}</span> produk di keranjang Anda</h6>
                @if(!$carts->isEmpty())
                    <h6 class="text-body mb-0">
                        Total berat:
                        <span class="text-brand fw-bold js-cart-page-total-weight">{{ $totalWeightFormatted }}</span>
                    </h6>
                @endif
            </div>
            <div class="d-flex justify-content-end mt-10">
                <form action="{{ route('cart.clear') }}" method="POST" class="d-inline" onsubmit="return confirm('Kosongkan keranjang? Semua item akan dihapus.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-muted bg-transparent border-0 p-0"><i class="fi-rs-trash mr-5"></i>Kosongkan Keranjang</button>
                </form>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="background-color: rgba(22, 199, 154, 1); color: white; border: none;">
            <i class="fi-rs-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fi-rs-cross me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($carts->isEmpty())
        <div class="text-center py-5">
            <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-cart.svg') }}" alt="Keranjang Kosong" style="width: 100px; opacity: 0.5;" class="mb-4">
            <h4 class="mb-3">Keranjang Anda kosong</h4>
            <p class="text-muted mb-4">Mulai belanja dan tambahkan produk ke keranjang Anda</p>
            <a href="{{ route('products.index') }}" class="btn btn-fill-out"><i class="fi-rs-shopping-bag mr-10"></i>Mulai Belanja</a>
        </div>
    @else
        <div class="row">
            <div class="col-lg-8">
                <div class="table-responsive shopping-summery rg-cart-table-wrap">
                    <table class="table table-wishlist rg-cart-table">
                        <thead class="rg-cart-thead-desktop">
                            <tr class="main-heading">
                                <th class="custome-checkbox start pl-30">
                                    <input class="form-check-input" type="checkbox" name="checkbox" id="exampleCheckbox11" value="">
                                    <label class="form-check-label" for="exampleCheckbox11"></label>
                                </th>
                                <th scope="col" colspan="2">Produk</th>
                                <th scope="col">Harga Satuan</th>
                                <th scope="col">Jumlah</th>
                                <th scope="col">Subtotal</th>
                                <th scope="col" class="end">Hapus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($carts as $cart)
                                <tr class="pt-30 rg-cart-item" data-cart-row="{{ $cart->id }}">
                                    <td class="custome-checkbox pl-30 rg-cart-checkbox">
                                        <input class="form-check-input" type="checkbox" name="checkbox" id="exampleCheckbox{{ $loop->iteration }}" value="">
                                        <label class="form-check-label" for="exampleCheckbox{{ $loop->iteration }}"></label>
                                    </td>
                                    <td class="image product-thumbnail pt-40 pr-15 rg-cart-thumb">
                                        <img src="{{ $cart->product->image_url ? $cart->product->image_url : asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" alt="{{ $cart->product->name }}" onerror="this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}'">
                                    </td>
                                    <td class="product-des product-name pl-15 rg-cart-product">
                                        <h6 class="mb-5"><a class="product-name mb-10 text-heading" href="{{ route('products.show', $cart->product) }}">{{ $cart->product->name }} {{ $cart->product->commercial_name ? ' - ' . $cart->product->commercial_name : '' }}</a></h6>
                                        @if($cart->product->weight)
                                        <div class="product-meta mt-5">
                                            <small class="rg-cart-weight">
                                                Berat: {{ $cart->product->formatted_weight }}
                                            </small>
                                        </div>
                                        @endif
                                    </td>
                                    <td class="price rg-cart-unit-price" data-title="Harga Satuan">
                                        <h4 class="text-body js-cart-unit-price mb-0">Rp {{ number_format($cart->displayUnitPrice(), 0, ',', '.') }} </h4>
                                    </td>
                                    <td class="text-center detail-info rg-cart-qty" data-title="Jumlah">
                                        <div class="detail-extralink mr-15">
                                            <form action="{{ route('cart.update', $cart) }}" method="POST" class="cart-update-form">
                                                @csrf
                                                @method('PUT')
                                                <div class="product-qty-selector d-flex align-items-center justify-content-center rg-cart-qty-control">
                                                    <a href="#" class="qty-down-grid qty-down" data-slug="{{ $cart->product->slug }}">
                                                        <i class="fi-rs-minus"></i>
                                                    </a>
                                                    <input type="text" name="quantity" class="qty-val fw-bold" value="{{ $cart->cartQuantityInputValue() }}" min="1" inputmode="numeric" aria-label="Jumlah">
                                                    <a href="#" class="qty-up-grid qty-up" data-slug="{{ $cart->product->slug }}">
                                                        <i class="fi-rs-plus"></i>
                                                    </a>
                                                </div>
                                            </form>
                                            <span class="d-block font-xs text-muted mt-5 rg-cart-unit-label">{{ $cart->cartQuantityUnitLabel() }}</span>
                                            <span class="d-block font-xs text-muted js-cart-base-equiv rg-cart-base-equiv" style="{{ $cart->showsLargeUnitInCart() ? '' : 'display:none;' }}">@if($cart->showsLargeUnitInCart())(= {{ number_format($cart->quantity) }} {{ $cart->product->unit }})@endif</span>
                                        </div>
                                    </td>
                                    <td class="price rg-cart-subtotal" data-title="Subtotal">
                                        <h4 class="text-brand js-cart-line-subtotal mb-0">Rp {{ number_format($cart->product->price * $cart->quantity, 0, ',', '.') }} </h4>
                                    </td>
                                    <td class="action text-center rg-cart-remove" data-title="Hapus">
                                        <form action="{{ route('cart.destroy', $cart) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rg-cart-remove-btn" onclick="return confirm('Hapus item ini?')" aria-label="Hapus item">
                                                <i class="fi-rs-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="divider-2 mb-30"></div>
                <div class="cart-action text-end">
                    <form action="{{ route('cart.clear') }}" method="POST" class="d-inline" onsubmit="return confirm('Kosongkan keranjang? Semua item akan dihapus.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger"><i class="fi-rs-trash mr-5"></i>Kosongkan Keranjang</button>
                    </form>
                </div>
                <!-- Optional: Shipping Calculator Section preserved from template if needed by USER, currently placeholders -->
                
            </div>
            <div class="col-lg-4">
                <div class="border p-md-4 cart-totals ml-30 rg-cart-totals">
                    <div class="table-responsive">
                        <table class="table no-border">
                            <tbody>
                                <tr>
                                    <td class="cart_total_label">
                                        <h6 class="text-muted">Subtotal</h6>
                                    </td>
                                    <td class="cart_total_amount">
                                        <h4 class="text-brand text-end js-cart-page-total">Rp {{ number_format($total, 0, ',', '.') }}</h4>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="cart_total_label">
                                        <h6 class="text-muted">Total Berat</h6>
                                    </td>
                                    <td class="cart_total_amount">
                                        <h4 class="text-brand text-end js-cart-page-total-weight">{{ $totalWeightFormatted }}</h4>
                                    </td>
                                </tr>
                                <tr>
                                    <td scope="col" colspan="2">
                                        <div class="divider-2 mt-10 mb-10"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="cart_total_label">
                                        <h6 class="text-muted">Total</h6>
                                    </td>
                                    <td class="cart_total_amount">
                                        <h4 class="text-brand text-end js-cart-page-total">Rp {{ number_format($total, 0, ',', '.') }}</h4>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <style>
                        .cart-totals .btn-lanjut-belanja {
                            background-color: transparent !important;
                            border: 1.5px solid #6A1B1B !important;
                            color: #6A1B1B !important;
                            padding: 12px 25px !important;
                        }
                        .cart-totals .btn-lanjut-belanja:hover {
                            background-color: #6A1B1B !important;
                            color: #ffffff !important;
                        }
                    </style>
                    <a href="{{ route('products.index') }}" class="btn w-100 mb-15 btn-lanjut-belanja text-center"><i class="fi-rs-arrow-left mr-10"></i>Lanjut Belanja</a>
                    @auth
                        <a href="{{ route('checkout.index') }}" class="btn mb-20 w-100 text-center">Lanjut ke Pembayaran<i class="fi-rs-sign-out ml-15"></i></a>
                    @else
                        <a href="{{ route('login') }}" class="btn mb-20 w-100 text-center">Login untuk Melanjutkan<i class="fi-rs-sign-in ml-15"></i></a>
                    @endauth
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .rg-cart-qty-control {
        min-width: 100px;
    }

    .rg-cart-qty-control i {
        font-size: 10px;
        color: #253D4E;
        border: 1px solid #253D4E;
        border-radius: 50%;
        padding: 5px;
    }

    .rg-cart-qty-control .qty-val {
        border: none;
        width: 45px;
        text-align: center;
        background: transparent;
        font-size: 16px;
        color: #253D4E;
        outline: none;
    }

    .rg-cart-weight {
        color: #253D4E;
        font-size: 13px;
    }

    .rg-cart-remove-btn {
        color: #253D4E;
        background: transparent;
        border: 0;
        padding: 0;
    }

    @media (max-width: 991.98px) {
        .rg-cart-page .heading-2 {
            font-size: 1.5rem;
        }

        .rg-cart-page .rg-cart-table-wrap {
            overflow: visible;
        }

        .rg-cart-page .rg-cart-table,
        .rg-cart-page .rg-cart-table tbody {
            display: block;
            width: 100%;
        }

        .rg-cart-page .rg-cart-thead-desktop {
            display: none !important;
        }

        .rg-cart-page .rg-cart-item {
            display: grid !important;
            grid-template-columns: 72px 1fr auto;
            grid-template-areas:
                "thumb product remove"
                "unit-price unit-price unit-price"
                "qty qty qty"
                "subtotal subtotal subtotal";
            gap: 0 12px;
            background: #fff;
            border: 1px solid #edf2f7;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 16px;
            box-shadow: 0 4px 16px rgba(37, 61, 78, 0.06);
        }

        .rg-cart-page .rg-cart-item > td {
            display: block;
            width: 100%;
            border: none !important;
            padding: 0 !important;
        }

        .rg-cart-page .rg-cart-checkbox {
            display: none !important;
        }

        .rg-cart-page .rg-cart-thumb {
            grid-area: thumb;
        }

        .rg-cart-page .rg-cart-thumb img {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #ececec;
            margin: 0;
            max-width: none;
        }

        .rg-cart-page .rg-cart-product {
            grid-area: product;
            min-width: 0;
        }

        .rg-cart-page .rg-cart-product .product-name {
            font-size: 15px !important;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .rg-cart-page .rg-cart-remove {
            grid-area: remove;
            align-self: start;
            text-align: right;
        }

        .rg-cart-page .rg-cart-remove-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #fff5f5;
            color: #c0392b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .rg-cart-page .rg-cart-unit-price,
        .rg-cart-page .rg-cart-qty,
        .rg-cart-page .rg-cart-subtotal {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 14px;
            padding-top: 14px !important;
            border-top: 1px solid #f1f5f9;
        }

        .rg-cart-page .rg-cart-unit-price {
            grid-area: unit-price;
        }

        .rg-cart-page .rg-cart-qty {
            grid-area: qty;
            align-items: flex-start;
        }

        .rg-cart-page .rg-cart-subtotal {
            grid-area: subtotal;
            border-top: none;
            margin-top: 0;
            padding-top: 0 !important;
            background: #f8fafc;
            border-radius: 12px;
            padding: 12px 14px !important;
        }

        .rg-cart-page .rg-cart-unit-price::before,
        .rg-cart-page .rg-cart-qty::before,
        .rg-cart-page .rg-cart-subtotal::before {
            content: attr(data-title);
            font-size: 13px;
            font-weight: 600;
            color: #7E7E7E;
            flex-shrink: 0;
        }

        .rg-cart-page .rg-cart-qty .detail-extralink {
            margin: 0 !important;
            text-align: right;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .rg-cart-page .rg-cart-qty-control {
            min-width: 118px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 4px 8px;
        }

        .rg-cart-page .rg-cart-unit-label,
        .rg-cart-page .rg-cart-base-equiv {
            text-align: right;
        }

        .rg-cart-page .rg-cart-subtotal .js-cart-line-subtotal {
            font-size: 18px !important;
        }

        .rg-cart-page .cart-totals.rg-cart-totals {
            margin-left: 0 !important;
            margin-top: 8px;
            padding: 24px 20px !important;
        }

        .rg-cart-page .cart-action {
            text-align: center !important;
        }

        .rg-cart-page .cart-action .btn {
            width: 100%;
        }
    }
</style>
@endsection

@push('scripts')
<script>
    (function () {
        function csrfToken() {
            var m = document.querySelector('meta[name="csrf-token"]');
            return m ? m.getAttribute('content') : '';
        }

        function updateCartQtyAjax(form, input) {
            var action = form.getAttribute('action');
            var qty = parseInt(String(input.value).replace(/\D/g, ''), 10);
            if (!qty || qty < 1) {
                qty = 1;
                input.value = 1;
            }
            var body = '_token=' + encodeURIComponent(csrfToken()) + '&quantity=' + encodeURIComponent(qty);
            var row = form.closest('tr');
            if (row) {
                row.classList.add('opacity-50');
            }
            fetch(action, {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken()
                },
                body: body,
                credentials: 'same-origin'
            })
                .then(function (r) {
                    return r.json().then(function (j) {
                        return { ok: r.ok, status: r.status, body: j };
                    });
                })
                .then(function (res) {
                    var originalVal = input.getAttribute('data-original-val') || input.value;
                    if (row) {
                        row.classList.remove('opacity-50');
                    }
                    if (!res.ok || !res.body.success) {
                        input.value = originalVal;
                        var msg = (res.body && (res.body.message || res.body.error)) || 'Gagal memperbarui keranjang.';
                        if (res.body && res.body.errors) {
                            var parts = [];
                            Object.keys(res.body.errors).forEach(function (k) {
                                parts.push(res.body.errors[k][0]);
                            });
                            if (parts.length) {
                                msg = parts.join(' ');
                            }
                        }
                        if (typeof window.showShopToast === 'function') {
                            window.showShopToast(msg, 'error');
                        } else {
                            alert(msg);
                        }
                        return;
                    }
                    var line = res.body.line;
                    input.value = line.quantity_input;
                    if (row) {
                        var unitEl = row.querySelector('.js-cart-unit-price');
                        if (unitEl && line.display_unit_price_formatted) {
                            unitEl.textContent = line.display_unit_price_formatted;
                        }
                        var subEl = row.querySelector('.js-cart-line-subtotal');
                        if (subEl && line.line_subtotal_formatted) {
                            subEl.textContent = line.line_subtotal_formatted;
                        }
                        var eq = row.querySelector('.js-cart-base-equiv');
                        if (eq) {
                            if (line.shows_base_equiv && line.base_equiv_formatted) {
                                eq.textContent = line.base_equiv_formatted;
                                eq.style.display = 'block';
                            } else {
                                eq.textContent = '';
                                eq.style.display = 'none';
                            }
                        }
                    }
                    document.querySelectorAll('.js-cart-page-total').forEach(function (el) {
                        el.textContent = res.body.cart_total_formatted;
                    });
                    if (res.body.total_weight_formatted) {
                        document.querySelectorAll('.js-cart-page-total-weight').forEach(function (el) {
                            el.textContent = res.body.total_weight_formatted;
                        });
                    }
                    if (typeof res.body.cart_count !== 'undefined' && window.jQuery) {
                        window.jQuery('.header-action-icon-2 .mini-cart-icon .pro-count').text(res.body.cart_count);
                    }
                    if (res.body.mini_cart_html && window.jQuery) {
                        window.jQuery('.cart-dropdown-wrap.cart-dropdown-hm2:not(.account-dropdown)').html(res.body.mini_cart_html);
                    }
                })
                .catch(function () {
                    if (row) {
                        row.classList.remove('opacity-50');
                    }
                    if (typeof window.showShopToast === 'function') {
                        window.showShopToast('Koneksi gagal. Coba lagi.', 'error');
                    } else {
                        alert('Koneksi gagal.');
                    }
                });
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.cart-update-form').forEach(function (form) {
                var input = form.querySelector('.qty-val');
                if (!input) {
                    return;
                }
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    updateCartQtyAjax(form, input);
                });
                form.querySelectorAll('.qty-up, .qty-down').forEach(function (btn) {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        input.setAttribute('data-original-val', input.value);
                        var v = parseInt(String(input.value).replace(/\D/g, ''), 10) || 1;
                        if (btn.classList.contains('qty-up')) {
                            v += 1;
                        } else {
                            v = Math.max(1, v - 1);
                        }
                        input.value = v;
                        updateCartQtyAjax(form, input);
                    });
                });
                input.addEventListener('change', function () {
                    input.setAttribute('data-original-val', input.value);
                    updateCartQtyAjax(form, input);
                });
            });
        });
    })();
</script>
@endpush
