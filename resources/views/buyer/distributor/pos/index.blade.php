@extends('layouts.shop')

@section('title', 'Point of Sales')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> POS (Penjualan Toko)
        </div>
    </div>
</div>

<div class="container-fluid mb-80 mt-20 px-4">
    <div class="row">
        <!-- Products Column -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm border-radius-15 h-100">
                <div class="card-header bg-white border-bottom p-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="mb-0">Produk Warehouse</h4>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group input-group-sm">
                                <input type="text" id="search-input" class="form-control font-sm" placeholder="Cari nama atau kode produk..." autofocus>
                                <button class="btn btn-brand btn-sm" type="button" id="search-btn"><i class="fi-rs-search"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4 overflow-auto" style="height: 600px;">
                    <div id="products-container" class="row g-3">
                        <div class="col-12 text-center py-5">
                            <div class="spinner-border text-brand" role="status"></div>
                            <p class="text-muted mt-2">Memuat produk...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart Column -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm border-radius-15 h-100 d-flex flex-column">
                <div class="card-header bg-white border-bottom p-4">
                    <h4 class="mb-0 d-flex justify-content-between align-items-center">
                        <span>Keranjang</span>
                        <span class="badge bg-brand rounded-pill fs-tiny" id="cart-count">0</span>
                    </h4>
                </div>
                <div class="card-body p-4 overflow-auto flex-grow-1" style="max-height: 400px;" id="cart-items">
                    <div class="text-center text-muted py-5">
                        <i class="fi-rs-shopping-cart fs-1 mb-2 d-block"></i>
                        <p>Keranjang masih kosong</p>
                    </div>
                </div>
                <div class="card-footer bg-light border-top p-4 mt-auto">
                    <div class="totals mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted font-sm">Subtotal</span>
                            <span class="font-sm fw-bold" id="cart-subtotal">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <h5 class="mb-0">Total</h5>
                            <h4 class="mb-0 text-brand" id="cart-total">Rp 0</h4>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-xs fw-bold">Metode Pembayaran</label>
                        <select id="payment-method" class="form-select font-sm border-radius-10">
                            <option value="cash">Tunai (Cash)</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="qris">QRIS</option>
                            <option value="debit">Kartu Debit</option>
                        </select>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="general-customer">
                        <label class="form-check-label font-sm" for="general-customer">
                            Pelanggan Umum
                        </label>
                    </div>

                    <div id="customer-info-section">
                        <div class="mb-2">
                            <input type="text" id="customer-name" class="form-control font-sm border-radius-10" placeholder="Nama Pelanggan">
                        </div>
                        <div class="mb-3">
                            <input type="text" id="customer-phone" class="form-control font-sm border-radius-10" placeholder="No. Telepon">
                        </div>
                    </div>

                    <button type="button" id="checkout-btn" class="btn btn-brand rounded-pill w-100 py-3 mt-2" disabled>
                        Selesaikan Transaksi <i class="fi-rs-check ml-10"></i>
                    </button>
                    <button type="button" id="clear-cart-btn" class="btn btn-link btn-sm w-100 text-muted mt-2">Kosongkan Keranjang</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 border-radius-15 shadow">
            <div class="modal-body p-5 text-center">
                <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-check-big.svg') }}" class="mb-4" style="width: 80px;">
                <h3 class="mb-2">Berhasil!</h3>
                <h5 class="text-brand mb-4" id="success-order-number"></h5>
                <div class="bg-light p-3 border-radius-10 mb-4 text-start">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="font-xs">Total Pembayaran</span>
                        <span class="font-xs fw-bold" id="success-total"></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="font-xs">Metode</span>
                        <span class="font-xs fw-bold" id="success-payment-method"></span>
                    </div>
                </div>
                <button type="button" class="btn btn-brand rounded-pill px-5" data-bs-dismiss="modal">Selesai</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .pos-product-card { 
        cursor: pointer; transition: all 0.3s; height: 100%; border-radius: 12px;
        position: relative; overflow: hidden;
    }
    .pos-product-card:hover { transform: translateY(-3px); border-color: #3BB77E !important; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    .pos-product-img { height: 120px; object-fit: cover; }
    .stock-badge { position: absolute; top: 10px; right: 10px; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 700; color: white; }
    .stock-low { background-color: #fd3d11; }
    .stock-medium { background-color: #ffc107; color: #212529; }
    .stock-high { background-color: #3bb77e; }
    
    .cart-item { border-bottom: 1px solid #f2f2f2; padding-bottom: 15px; margin-bottom: 15px; }
    .cart-item:last-child { border-bottom: 0; }
    .btn-qty-sm { width: 25px; height: 25px; padding: 0; line-height: 25px; border-radius: 5px; background: #f2f2f2; border: 0; }
    
    #customer-info-section.disabled { opacity: 0.5; pointer-events: none; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let searchTimeout;
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    function searchProducts(keyword = '') {
        $('#products-container').html('<div class="col-12 text-center py-5"><div class="spinner-border text-brand" role="status"></div></div>');

        $.ajax({
            url: "{{ route('distributor.pos.search-products') }}",
            method: 'GET',
            data: { q: keyword, limit: 100 },
            success: function(products) {
                if (products.length === 0) {
                    $('#products-container').html('<div class="col-12 text-center py-5"><p class="text-muted">Produk tidak ditemukan</p></div>');
                    return;
                }

                let html = '';
                products.forEach(function(product) {
                    const stockClass = product.stock <= 10 ? 'stock-low' : product.stock <= 50 ? 'stock-medium' : 'stock-high';
                    html += `
                        <div class="col-md-3 col-sm-4 col-6">
                            <div class="card pos-product-card border h-100" data-product-id="${product.id}">
                                <span class="stock-badge ${stockClass}">Stok: ${product.stock}</span>
                                ${product.image ? `<img src="${product.image}" class="card-img-top pos-product-img" alt="${product.name}">` : 
                                  `<div class="bg-light pos-product-img d-flex align-items-center justify-content-center text-muted"><i class="fi-rs-shopping-bag fs-1"></i></div>`}
                                <div class="card-body p-2">
                                    <h6 class="font-xs mb-1 text-heading">${product.name}</h6>
                                    <strong class="text-brand font-sm">${product.formatted_price}</strong>
                                </div>
                            </div>
                        </div>
                    `;
                });
                $('#products-container').html(html);

                $('.pos-product-card').on('click', function() {
                    addToCart($(this).data('product-id'), 1);
                });
            }
        });
    }

    function addToCart(productId, quantity) {
        $.ajax({
            url: "{{ route('distributor.pos.add-to-cart') }}",
            method: 'POST',
            data: { _token: csrfToken, product_id: productId, quantity: quantity },
            success: function(response) {
                if (response.success) updateCartDisplay(response.cart);
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.error || 'Gagal menambahkan produk');
            }
        });
    }

    function updateCartDisplay(cart) {
        if (!cart.items || cart.items.length === 0) {
            $('#cart-items').html('<div class="text-center text-muted py-5"><i class="fi-rs-shopping-cart fs-1 mb-2 d-block"></i><p>Keranjang masih kosong</p></div>');
            $('#checkout-btn').prop('disabled', true);
        } else {
            let html = '';
            cart.items.forEach(function(item) {
                html += `
                    <div class="cart-item">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="font-xs mb-1 text-heading">${item.name}</h6>
                                <p class="font-xs text-muted mb-0">Rp ${item.price.toLocaleString('id-ID')} x ${item.quantity}</p>
                            </div>
                            <div class="text-end">
                                <h6 class="font-sm text-brand mb-1">Rp ${item.subtotal.toLocaleString('id-ID')}</h6>
                                <div class="btn-group">
                                    <button class="btn btn-qty-sm remove-item" data-product-id="${item.product_id}"><i class="fi-rs-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#cart-items').html(html);
            $('#checkout-btn').prop('disabled', false);

            $('.remove-item').on('click', function() {
                removeFromCart($(this).data('product-id'));
            });
        }

        $('#cart-count').text(cart.item_count);
        $('#cart-subtotal').text('Rp ' + cart.subtotal.toLocaleString('id-ID'));
        $('#cart-total').text('Rp ' + cart.total.toLocaleString('id-ID'));
    }

    function removeFromCart(productId) {
        $.ajax({
            url: "{{ route('distributor.pos.remove-from-cart', ':id') }}".replace(':id', productId),
            method: 'POST',
            data: { _token: csrfToken },
            success: function(response) {
                if (response.success) updateCartDisplay(response.cart);
            }
        });
    }

    $('#checkout-btn').on('click', function() {
        const paymentMethod = $('#payment-method').val();
        const isGeneralCustomer = $('#general-customer').is(':checked');
        const customerName = isGeneralCustomer ? 'Pembeli Umum' : $('#customer-name').val();
        const customerPhone = isGeneralCustomer ? '' : $('#customer-phone').val();

        if (!isGeneralCustomer && !customerName) {
            alert('Harap isi nama pelanggan atau pilih Pelanggan Umum');
            return;
        }

        $(this).prop('disabled', true).html('<div class="spinner-border spinner-border-sm" role="status"></div> Prosess...');

        $.ajax({
            url: "{{ route('distributor.pos.checkout') }}",
            method: 'POST',
            data: {
                _token: csrfToken,
                payment_method: paymentMethod,
                customer_name: customerName,
                customer_phone: customerPhone
            },
            success: function(response) {
                if (response.success) {
                    $('#success-order-number').text(response.order_number);
                    $('#success-total').text($('#cart-total').text());
                    $('#success-payment-method').text($('#payment-method option:selected').text());
                    $('#successModal').modal('show');
                    
                    // Reset
                    updateCartDisplay({ items: [], subtotal: 0, total: 0, item_count: 0 });
                    $('#customer-name').val('');
                    $('#customer-phone').val('');
                    $('#general-customer').prop('checked', false).trigger('change');
                    searchProducts(); // Refresh stocks
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.error || 'Gagal memproses transaksi');
            },
            complete: function() {
                $('#checkout-btn').prop('disabled', false).html('Selesaikan Transaksi <i class="fi-rs-check ml-10"></i>');
            }
        });
    });

    $('#general-customer').on('change', function() {
        if ($(this).is(':checked')) {
            $('#customer-info-section').addClass('disabled');
        } else {
            $('#customer-info-section').removeClass('disabled');
        }
    });

    $('#search-input').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => searchProducts($(this).val()), 500);
    });

    $('#clear-cart-btn').on('click', function() {
        if (confirm('Kosongkan keranjang?')) {
            $.ajax({
                url: "{{ route('distributor.pos.clear-cart') }}",
                method: 'POST',
                data: { _token: csrfToken },
                success: function(response) {
                    if (response.success) updateCartDisplay(response.cart);
                }
            });
        }
    });

    // Initial load
    searchProducts();
    $.ajax({ url: "{{ route('distributor.pos.get-cart') }}", success: updateCartDisplay });
});
</script>
@endpush
