@extends('layouts.distributor')

@section('title', 'Point of Sales')
@section('page-title', 'Point of Sales (POS)')
@section('page-description', 'Penjualan Offline')

@section('breadcrumb')
    <li class="active">Point of Sales</li>
@endsection

@push('styles')
<style>
    .pos-container {
        display: flex;
        height: calc(100vh - 150px);
        gap: 15px;
    }
    .pos-products {
        flex: 1;
        overflow-y: auto;
        background: #fff;
        border-radius: 5px;
        padding: 15px;
    }
    .pos-cart {
        width: 400px;
        background: #fff;
        border-radius: 5px;
        padding: 15px;
        display: flex;
        flex-direction: column;
    }
    .product-card {
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 10px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .product-card:hover {
        border-color: #f39c12;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .product-card.selected {
        border-color: #f39c12;
        background: #fffbf0;
    }
    .cart-item {
        border-bottom: 1px solid #eee;
        padding: 10px 0;
    }
    .cart-item:last-child {
        border-bottom: none;
    }
    .search-box {
        margin-bottom: 15px;
    }
    .cart-footer {
        margin-top: auto;
        padding-top: 15px;
        border-top: 2px solid #eee;
    }
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }
    .product-item {
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 10px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }
    .product-item:hover {
        border-color: #f39c12;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .product-item img {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 5px;
        margin-bottom: 10px;
        display: block;
    }
    .cart-item img {
        width: 100%;
        height: 60px;
        object-fit: cover;
        border-radius: 3px;
        display: block;
    }
    .stock-badge {
        position: absolute;
        top: 5px;
        right: 5px;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: bold;
    }
    .stock-low {
        background: #f56954;
        color: #fff;
    }
    .stock-medium {
        background: #f39c12;
        color: #fff;
    }
    .stock-high {
        background: #00a65a;
        color: #fff;
    }
    #customer-info-section.text-muted {
        opacity: 0.6;
    }
    #customer-info-section.text-muted input {
        background-color: #f5f5f5;
        cursor: not-allowed;
    }
</style>
@endpush

@section('content')
    <div class="pos-container">
        <!-- Products Section -->
        <div class="pos-products">
            <div class="search-box">
                <div class="input-group">
                    <input type="text" id="search-input" class="form-control input-lg" placeholder="Cari produk (nama atau kode)..." autofocus>
                    <span class="input-group-btn">
                        <button class="btn btn-warning btn-lg" type="button" id="search-btn">
                            <i class="fa fa-search"></i> Cari
                        </button>
                    </span>
                </div>
            </div>
            
            <div id="products-container">
                <div class="text-center" style="padding: 50px;">
                    <i class="fa fa-spinner fa-spin fa-3x text-muted"></i>
                    <p class="text-muted">Memuat produk...</p>
                </div>
            </div>
        </div>

        <!-- Cart Section -->
        <div class="pos-cart">
            <h3 class="box-title">
                <i class="fa fa-shopping-cart"></i> Cart
                <span class="badge bg-yellow" id="cart-count">0</span>
            </h3>
            
            <div id="cart-items" style="flex: 1; overflow-y: auto; margin: 15px 0;">
                <div class="text-center text-muted" style="padding: 50px 0;">
                    <i class="fa fa-shopping-cart fa-3x"></i>
                    <p>Cart kosong</p>
                </div>
            </div>

            <div class="cart-footer">
                <table class="table table-condensed">
                    <tr>
                        <th>Subtotal:</th>
                        <td class="text-right"><strong id="cart-subtotal">Rp 0</strong></td>
                    </tr>
                    <tr style="font-size: 18px;">
                        <th>Total:</th>
                        <td class="text-right"><strong id="cart-total" class="text-success">Rp 0</strong></td>
                    </tr>
                </table>

                <div class="form-group">
                    <label>Metode Pembayaran <span class="text-red">*</span></label>
                    <select id="payment-method" class="form-control" required>
                        <option value="cash">Cash (Tunai)</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="qris">QRIS</option>
                        <option value="debit">Kartu Debit</option>
                        <option value="credit">Kartu Kredit</option>
                    </select>
                </div>

                <div class="form-group">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="general-customer"> <strong>Pembeli Umum</strong>
                        </label>
                    </div>
                </div>

                <div id="customer-info-section">
                    <div class="form-group">
                        <label>Nama Pelanggan</label>
                        <input type="text" id="customer-name" class="form-control" placeholder="Nama pelanggan...">
                    </div>

                    <div class="form-group">
                        <label>No. Telepon</label>
                        <input type="text" id="customer-phone" class="form-control" placeholder="No. telepon...">
                    </div>
                </div>

                <div class="form-group">
                    <label>Catatan (Opsional)</label>
                    <textarea id="notes" class="form-control" rows="2" placeholder="Catatan..."></textarea>
                </div>

                <button type="button" id="checkout-btn" class="btn btn-success btn-lg btn-block" disabled>
                    <i class="fa fa-check"></i> CHECKOUT
                </button>
                <button type="button" id="clear-cart-btn" class="btn btn-default btn-block">
                    <i class="fa fa-trash"></i> Clear Cart
                </button>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-green">
                    <button type="button" class="close" data-dismiss="modal" style="color: #fff;">&times;</button>
                    <h4 class="modal-title" style="color: #fff;"><i class="fa fa-check"></i> Transaksi Berhasil!</h4>
                </div>
                <div class="modal-body text-center">
                    <i class="fa fa-check-circle fa-4x text-green" style="margin-bottom: 20px;"></i>
                    <h3 id="success-order-number"></h3>
                    <p class="text-muted">Total: <strong id="success-total"></strong></p>
                    <p class="text-muted">Pembayaran: <strong id="success-payment-method"></strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let searchTimeout;
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Load products on page load
    function loadAllProducts() {
        searchProducts('');
    }

    // Search products
    $('#search-input').on('keyup', function() {
        clearTimeout(searchTimeout);
        const keyword = $(this).val().trim();
        
        if (keyword.length >= 1) {
            searchTimeout = setTimeout(() => {
                searchProducts(keyword);
            }, 500);
        } else {
            // If search is cleared, show all products
            searchTimeout = setTimeout(() => {
                searchProducts('');
            }, 300);
        }
    });

    $('#search-btn').on('click', function() {
        const keyword = $('#search-input').val().trim();
        searchProducts(keyword);
    });

    function searchProducts(keyword) {
        // Show loading
        $('#products-container').html(`
            <div class="text-center" style="padding: 50px;">
                <i class="fa fa-spinner fa-spin fa-3x text-muted"></i>
                <p class="text-muted">Memuat produk...</p>
            </div>
        `);

        $.ajax({
            url: "{{ route('distributor.pos.search-products') }}",
            method: 'GET',
            data: { q: keyword, limit: 100 },
            success: function(products) {
                if (products.length === 0) {
                    $('#products-container').html(`
                        <div class="text-center" style="padding: 50px;">
                            <i class="fa fa-inbox fa-3x text-muted"></i>
                            <p class="text-muted">Produk tidak ditemukan</p>
                        </div>
                    `);
                    return;
                }

                let html = '<div class="product-grid">';
                products.forEach(function(product) {
                    const stockClass = product.stock <= 10 ? 'stock-low' : 
                                     product.stock <= 50 ? 'stock-medium' : 'stock-high';
                    html += `
                        <div class="product-item" data-product-id="${product.id}" style="position: relative;">
                            <span class="stock-badge ${stockClass}">Stock: ${product.stock}</span>
                            ${product.image ? `<img src="${product.image}" alt="${product.name}" style="width: 100%; height: 120px; object-fit: cover; border-radius: 5px; margin-bottom: 10px;" onerror="this.onerror=null; this.src=''; this.outerHTML='<div style=\\'height: 120px; background: #ddd; border-radius: 5px; display: flex; align-items: center; justify-content: center;\\'><i class=\\'fa fa-image fa-3x text-muted\\'></i></div>';" />` : 
                              `<div style="height: 120px; background: #ddd; border-radius: 5px; display: flex; align-items: center; justify-content: center;"><i class="fa fa-image fa-3x text-muted"></i></div>`}
                            <h5 style="margin: 10px 0 5px; font-size: 14px;">${product.name}</h5>
                            ${product.code ? `<small class="text-muted">${product.code}</small><br>` : ''}
                            <strong class="text-warning">${product.formatted_price}</strong>
                        </div>
                    `;
                });
                html += '</div>';
                $('#products-container').html(html);

                // Add click handler
                $('.product-item').on('click', function() {
                    const productId = $(this).data('product-id');
                    addToCart(productId, 1);
                });
            },
            error: function(xhr) {
                console.error('Search Products Error:', xhr);
                let errorMsg = 'Terjadi kesalahan saat mencari produk';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                $('#products-container').html(`
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle"></i> ${errorMsg}
                    </div>
                `);
            }
        });
    }

    function addToCart(productId, quantity) {
        $.ajax({
            url: "{{ route('distributor.pos.add-to-cart') }}",
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: {
                product_id: productId,
                quantity: quantity
            },
            success: function(response) {
                if (response.success) {
                    updateCartDisplay(response.cart);
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Terjadi kesalahan';
                alert(error);
            }
        });
    }

    function updateCartDisplay(cart) {
        if (cart.items.length === 0) {
            $('#cart-items').html(`
                <div class="text-center text-muted" style="padding: 50px 0;">
                    <i class="fa fa-shopping-cart fa-3x"></i>
                    <p>Cart kosong</p>
                </div>
            `);
            $('#checkout-btn').prop('disabled', true);
        } else {
            let html = '';
            cart.items.forEach(function(item) {
                const imageHtml = item.image && item.image !== 'null' && item.image !== '' 
                    ? `<img src="${item.image}" alt="${item.name}" class="cart-item-image" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">`
                    : '';
                const placeholderHtml = `<div class="cart-item-placeholder" style="${item.image && item.image !== 'null' && item.image !== '' ? 'display: none;' : ''} width: 100%; height: 60px; background: #ddd; border-radius: 3px; display: flex; align-items: center; justify-content: center;"><i class="fa fa-image text-muted"></i></div>`;
                
                html += `
                    <div class="cart-item" data-product-id="${item.product_id}">
                        <div class="row">
                            <div class="col-xs-3" style="position: relative;">
                                ${imageHtml}
                                ${placeholderHtml}
                            </div>
                            <div class="col-xs-9">
                                <strong style="font-size: 12px;">${item.name}</strong><br>
                                <small class="text-muted">${item.formatted_price || 'Rp ' + item.price.toLocaleString('id-ID')} x 
                                <input type="number" class="cart-quantity" value="${item.quantity}" min="1" max="${item.stock}" 
                                       style="width: 50px; text-align: center; padding: 2px;" 
                                       data-product-id="${item.product_id}"></small><br>
                                <strong class="text-warning">Rp ${item.subtotal.toLocaleString('id-ID')}</strong>
                                <button class="btn btn-danger btn-xs pull-right remove-item" data-product-id="${item.product_id}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#cart-items').html(html);
            $('#checkout-btn').prop('disabled', false);

            // Add event handlers
            $('.cart-quantity').on('change', function() {
                const productId = $(this).data('product-id');
                const quantity = parseInt($(this).val());
                updateCartItem(productId, quantity);
            });

            $('.remove-item').on('click', function() {
                const productId = $(this).data('product-id');
                removeFromCart(productId);
            });
        }

        $('#cart-count').text(cart.item_count);
        $('#cart-subtotal').text('Rp ' + cart.subtotal.toLocaleString('id-ID'));
        $('#cart-total').text('Rp ' + cart.total.toLocaleString('id-ID'));
    }

    function updateCartItem(productId, quantity) {
        $.ajax({
            url: "{{ route('distributor.pos.update-cart', ':id') }}".replace(':id', productId),
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: { quantity: quantity },
            success: function(response) {
                if (response.success) {
                    updateCartDisplay(response.cart);
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Terjadi kesalahan';
                alert(error);
                loadCart(); // Reload cart
            }
        });
    }

    function removeFromCart(productId) {
        $.ajax({
            url: "{{ route('distributor.pos.remove-from-cart', ':id') }}".replace(':id', productId),
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(response) {
                if (response.success) {
                    updateCartDisplay(response.cart);
                }
            }
        });
    }

    function loadCart() {
        $.ajax({
            url: "{{ route('distributor.pos.get-cart') }}",
            method: 'GET',
            success: function(response) {
                updateCartDisplay(response);
            }
        });
    }

    $('#clear-cart-btn').on('click', function() {
        if (confirm('Kosongkan cart?')) {
            $.ajax({
                url: "{{ route('distributor.pos.clear-cart') }}",
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function(response) {
                    if (response.success) {
                        updateCartDisplay(response.cart);
                    }
                }
            });
        }
    });

    $('#checkout-btn').on('click', function() {
        const paymentMethod = $('#payment-method').val();
        const isGeneralCustomer = $('#general-customer').is(':checked');
        const customerName = isGeneralCustomer ? 'Pembeli Umum' : $('#customer-name').val();
        const customerPhone = isGeneralCustomer ? '' : $('#customer-phone').val();
        const notes = $('#notes').val();

        if (!paymentMethod) {
            alert('Pilih metode pembayaran terlebih dahulu');
            return;
        }

        if (!confirm('Proses checkout transaksi ini?')) {
            return;
        }

        $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

        $.ajax({
            url: "{{ route('distributor.pos.checkout') }}",
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: {
                payment_method: paymentMethod,
                customer_name: customerName,
                customer_phone: customerPhone,
                notes: notes
            },
            success: function(response) {
                if (response.success) {
                    $('#success-order-number').text(response.order_number);
                    $('#success-total').text('Rp ' + $('#cart-total').text().replace('Rp ', ''));
                    
                    // Format payment method
                    const paymentMethods = {
                        'cash': 'Cash (Tunai)',
                        'transfer': 'Transfer Bank',
                        'qris': 'QRIS',
                        'debit': 'Kartu Debit',
                        'credit': 'Kartu Kredit'
                    };
                    $('#success-payment-method').text(paymentMethods[paymentMethod] || paymentMethod);
                    
                    $('#successModal').modal('show');
                    
                    // Clear form
                    $('#customer-name').val('');
                    $('#customer-phone').val('');
                    $('#notes').val('');
                    $('#payment-method').val('cash');
                    $('#general-customer').prop('checked', false).trigger('change');
                    
                    // Clear cart display
                    updateCartDisplay({ items: [], subtotal: 0, total: 0, item_count: 0 });
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Terjadi kesalahan saat checkout';
                alert(error);
            },
            complete: function() {
                $('#checkout-btn').prop('disabled', false).html('<i class="fa fa-check"></i> CHECKOUT');
            }
        });
    });

    // Handle "Pembeli Umum" checkbox
    $('#general-customer').on('change', function() {
        if ($(this).is(':checked')) {
            $('#customer-name').val('').prop('disabled', true);
            $('#customer-phone').val('').prop('disabled', true);
            $('#customer-info-section').addClass('text-muted');
        } else {
            $('#customer-name').prop('disabled', false);
            $('#customer-phone').prop('disabled', false);
            $('#customer-info-section').removeClass('text-muted');
        }
    });

    // Load cart and products on page load
    loadCart();
    loadAllProducts();

    // Enter key on search
    $('#search-input').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#search-btn').click();
        }
    });
});
</script>
@endpush

