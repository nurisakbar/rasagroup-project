@extends('layouts.shop')

@section('title', 'Keranjang Belanja Distributor')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> <a href="{{ route('distributor.orders.products') }}">Pesan ke Pusat</a>
            <span></span> Keranjang
        </div>
    </div>
</div>

<div class="container mb-80 mt-50">
    <div class="row">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-4">
                    @include('buyer.partials.sidebar')
                </div>
                <div class="col-lg-8">
                    <div class="tab-content account dashboard-content pl-50">
                        <div class="tab-pane fade show active" role="tabpanel">
                            <div class="card border-0 shadow-sm border-radius-10">
                                <div class="card-header bg-white border-bottom p-4">
                                    <h3 class="mb-0">Keranjang Belanja Distributor</h3>
                                    <p class="text-muted font-sm">Item yang Anda pilih untuk dipesan ke pusat.</p>
                                </div>
                                <div class="p-4 bg-white border-bottom">
                                    @include('buyer.distributor.orders.partials.step-wizard', ['step' => 2])
                                </div>
                                <div class="card-body p-4">
                                    @if($carts->isEmpty())
                                        <div class="text-center py-5">
                                            <i class="fi-rs-shopping-cart text-muted fs-1 mb-3 d-block"></i>
                                            <h5>Keranjang Anda kosong.</h5>
                                            <a href="{{ route('distributor.orders.products') }}" class="btn btn-brand btn-sm rounded-pill mt-3">Mulai Belanja</a>
                                        </div>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table font-sm">
                                                <thead>
                                                    <tr class="main-heading">
                                                        <th class="pl-20" colspan="2">Produk</th>

                                                        <th>Jumlah</th>
                                                        <th>Subtotal</th>
                                                        <th class="text-end pr-20">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($carts as $cart)
                                                        <tr id="cart-row-{{ $cart->id }}">
                                                            <td class="image product-thumbnail pl-20" width="80">
                                                                <img src="{{ asset($cart->product->image_url) }}" alt="{{ $cart->product->display_name }}" class="border-radius-10">
                                                            </td>
                                                            <td class="product-des product-name">
                                                                <h6 class="mb-5"><a href="#" class="text-heading">{{ $cart->product->display_name }}</a></h6>
                                                                <p class="font-xs text-muted mb-1">{{ $cart->product->code }}</p>
                                                                <p class="font-sm fw-bold text-brand">Rp {{ number_format($cart->display_price, 0, ',', '.') }}</p>
                                                            </td>
                                                            <td>
                                                                <form action="{{ route('distributor.orders.update-cart', $cart) }}" method="POST" class="update-cart-form" data-cart-id="{{ $cart->id }}">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <input type="number" name="quantity" value="{{ $cart->quantity }}" min="1" class="form-control form-control-sm text-center cart-qty-input" style="width: 80px;">
                                                                </form>
                                                            </td>
                                                            <td>
                                                                <p class="font-sm text-brand fw-bold item-subtotal-display" id="item-subtotal-{{ $cart->id }}">Rp {{ number_format($cart->display_subtotal, 0, ',', '.') }}</p>
                                                            </td>
                                                            <td class="text-end pr-20">
                                                                <form action="{{ route('distributor.orders.remove-from-cart', $cart) }}" method="POST" class="remove-cart-form" data-cart-id="{{ $cart->id }}">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="button" class="text-danger btn-remove-cart" style="background: none; border: none; padding: 0; font-size: 14px;" aria-label="Hapus item">
                                                                        <i class="fi-rs-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="divider-2 mt-20 mb-20"></div>

                                        <div class="row">
                                            <div class="col-lg-6 col-md-12">
                                                <div class="p-3 bg-light border-radius-10">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span class="font-sm">Total Unit</span>
                                                        <span class="font-sm fw-bold" id="cart-total-items">{{ number_format($totalItems) }} unit</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 col-md-12">
                                                <div class="p-3 bg-light border-radius-10 text-end">
                                                    <h6 class="text-muted mb-2 font-sm">Subtotal</h6>
                                                    <h5 class="text-muted mb-2" style="text-decoration: line-through;" id="cart-subtotal">Rp {{ number_format($subtotal, 0, ',', '.') }}</h5>
                                                    @if(isset($discountAmount) && $discountAmount > 0)
                                                    <h6 class="text-success mb-2 font-sm">Diskon ({{ floatval($discountPercent) }}%)</h6>
                                                    <h5 class="text-success mb-2" id="cart-discount">- Rp {{ number_format($discountAmount, 0, ',', '.') }}</h5>
                                                    @endif
                                                    <h3 class="text-brand mb-4" id="cart-total-after-discount">Rp {{ number_format($totalAfterDiscount ?? $subtotal, 0, ',', '.') }}</h3>
                                                    <a href="{{ route('distributor.orders.checkout') }}" class="btn btn-brand rounded-pill w-100">Lanjut ke Checkout <i class="fi-rs-arrow-right ml-10"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-footer bg-white border-top p-4">
                                    <a href="{{ route('distributor.orders.products') }}" class="btn btn-secondary btn-sm rounded-pill px-4"><i class="fi-rs-arrow-small-left mr-5"></i> Lanjut Belanja</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let debounceTimer;
    
    $('.cart-qty-input').on('change keyup', function() {
        clearTimeout(debounceTimer);
        
        let input = $(this);
        let form = input.closest('form');
        let cartId = form.data('cart-id');
        let quantity = input.val();
        
        if (quantity < 1) return;
        
        debounceTimer = setTimeout(function() {
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#item-subtotal-' + cartId).text(response.item_subtotal_formatted);
                        $('#cart-total-items').text(response.cart_total_items + ' unit');
                        $('#cart-subtotal').text(response.cart_subtotal_formatted);
                        
                        if (response.cart_discount_formatted) {
                            $('#cart-discount').text(response.cart_discount_formatted);
                            $('#cart-total-after-discount').text(response.cart_total_after_discount_formatted);
                        } else {
                            $('#cart-total-after-discount').text(response.cart_subtotal_formatted);
                        }
                        
                        // Optional visual feedback
                        $('#item-subtotal-' + cartId).fadeOut(100).fadeIn(100);
                        $('#cart-total-after-discount').fadeOut(100).fadeIn(100);
                    }
                },
                error: function(xhr) {
                    console.error('Update cart failed');
                    // Reload on error as fallback
                    location.reload();
                }
            });
        }, 500); // 500ms debounce
    });

    $('.btn-remove-cart').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Hapus item ini?')) return;
        
        let btn = $(this);
        let form = btn.closest('form');
        let cartId = form.data('cart-id');
        let row = $('#cart-row-' + cartId);
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    if (response.is_empty) {
                        location.reload();
                    } else {
                        row.fadeOut(300, function() {
                            $(this).remove();
                            $('#cart-total-items').text(response.cart_total_items + ' unit');
                            $('#cart-subtotal').text(response.cart_subtotal_formatted);
                            
                            if (response.cart_discount_formatted) {
                                $('#cart-discount').text(response.cart_discount_formatted);
                                $('#cart-total-after-discount').text(response.cart_total_after_discount_formatted);
                            } else {
                                $('#cart-total-after-discount').text(response.cart_subtotal_formatted);
                            }
                            
                            $('#cart-total-after-discount').fadeOut(100).fadeIn(100);
                        });
                    }
                }
            },
            error: function(xhr) {
                console.error('Remove cart failed');
                location.reload();
            }
        });
    });
});
</script>
@endpush
