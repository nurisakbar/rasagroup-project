@extends('themes.nest.layouts.app')

@section('title', 'Cart')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
            <span></span> Shop
            <span></span> Cart
        </div>
    </div>
</div>
<div class="container mb-80 mt-50">
    <div class="row">
        <div class="col-lg-8 mb-40">
            <h1 class="heading-2 mb-10">Your Cart</h1>
            <div class="d-flex justify-content-between">
                <h6 class="text-body">There are <span class="text-brand">{{ $carts->count() }}</span> products in your cart</h6>
                <h6 class="text-body">
                    <form action="{{ route('cart.clear') }}" method="POST" class="d-inline" onsubmit="return confirm('Clear cart? All items will be removed.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-muted bg-transparent border-0 p-0"><i class="fi-rs-trash mr-5"></i>Clear Cart</button>
                    </form>
                </h6>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fi-rs-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
            <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-cart.svg') }}" alt="Empty Cart" style="width: 100px; opacity: 0.5;" class="mb-4">
            <h4 class="mb-3">Your cart is empty</h4>
            <p class="text-muted mb-4">Start shopping and add products to your cart</p>
            <a href="{{ route('products.index') }}" class="btn btn-fill-out"><i class="fi-rs-shopping-bag mr-10"></i>Start Shopping</a>
        </div>
    @else
        <!-- Hub Info Banner -->
        @if(isset($cartWarehouse) && $cartWarehouse)
        <div class="alert alert-info mb-30" style="border-radius: 15px;">
            <div class="d-flex align-items-center">
                <i class="fi-rs-marker mr-15" style="font-size: 20px;"></i>
                <div>
                    <strong>Shipping from Hub:</strong> {{ $cartWarehouse->name }}
                    <br>
                    <small>{{ $cartWarehouse->full_location }}</small>
                </div>
            </div>
        </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="table-responsive shopping-summery">
                    <table class="table table-wishlist">
                        <thead>
                            <tr class="main-heading">
                                <th class="custome-checkbox start pl-30">
                                    <input class="form-check-input" type="checkbox" name="checkbox" id="exampleCheckbox11" value="">
                                    <label class="form-check-label" for="exampleCheckbox11"></label>
                                </th>
                                <th scope="col" colspan="2">Product</th>
                                <th scope="col">Unit Price</th>
                                <th scope="col">Quantity</th>
                                <th scope="col">Subtotal</th>
                                <th scope="col" class="end">Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($carts as $cart)
                                <tr class="pt-30">
                                    <td class="custome-checkbox pl-30">
                                        <input class="form-check-input" type="checkbox" name="checkbox" id="exampleCheckbox{{ $loop->iteration }}" value="">
                                        <label class="form-check-label" for="exampleCheckbox{{ $loop->iteration }}"></label>
                                    </td>
                                    <td class="image product-thumbnail pt-40">
                                        <img src="{{ $cart->product->image_url ? $cart->product->image_url : asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" alt="#" onerror="this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}'">
                                    </td>
                                    <td class="product-des product-name">
                                        <h6 class="mb-5"><a class="product-name mb-10 text-heading" href="{{ route('products.show', $cart->product) }}">{{ $cart->product->name }} {{ $cart->product->commercial_name ? ' - ' . $cart->product->commercial_name : '' }}</a></h6>
                                        <div class="product-rate-cover">
                                            <div class="product-rate d-inline-block">
                                                <div class="product-rating" style="width:90%">
                                                </div>
                                            </div>
                                            <span class="font-small ml-5 text-muted"> (4.0)</span>
                                        </div>
                                        @if($cart->product->weight)
                                        <small class="text-muted">Weight: {{ $cart->product->formatted_weight }}</small>
                                        @endif
                                    </td>
                                    <td class="price" data-title="Price">
                                        <h4 class="text-body">Rp {{ number_format($cart->product->price, 0, ',', '.') }} </h4>
                                    </td>
                                    <td class="text-center detail-info" data-title="Stock">
                                        <div class="detail-extralink mr-15">
                                            <form action="{{ route('cart.update', $cart) }}" method="POST" class="cart-update-form">
                                                @csrf
                                                @method('PUT')
                                                <div class="detail-qty border radius">
                                                    <a href="#" class="qty-down"><i class="fi-rs-angle-small-down"></i></a>
                                                    <input type="text" name="quantity" class="qty-val" value="{{ $cart->quantity }}" min="1">
                                                    <a href="#" class="qty-up"><i class="fi-rs-angle-small-up"></i></a>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                    <td class="price" data-title="Price">
                                        <h4 class="text-brand">Rp {{ number_format($cart->product->price * $cart->quantity, 0, ',', '.') }} </h4>
                                    </td>
                                    <td class="action text-center" data-title="Remove">
                                        <form action="{{ route('cart.destroy', $cart) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-body bg-transparent border-0 p-0" onclick="return confirm('Remove this item?')"><i class="fi-rs-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="divider-2 mb-30"></div>
                <div class="cart-action d-flex justify-content-between">
                    <a class="btn " href="{{ route('products.index') }}"><i class="fi-rs-arrow-left mr-10"></i>Continue Shopping</a>
                    
                </div>
                <!-- Optional: Shipping Calculator Section preserved from template if needed by USER, currently placeholders -->
                
            </div>
            <div class="col-lg-4">
                <div class="border p-md-4 cart-totals ml-30">
                    <div class="table-responsive">
                        <table class="table no-border">
                            <tbody>
                                <tr>
                                    <td class="cart_total_label">
                                        <h6 class="text-muted">Subtotal</h6>
                                    </td>
                                    <td class="cart_total_amount">
                                        <h4 class="text-brand text-end">Rp {{ number_format($total, 0, ',', '.') }}</h4>
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
                                        <h4 class="text-brand text-end">Rp {{ number_format($total, 0, ',', '.') }}</h4>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @auth
                        <a href="{{ route('checkout.index') }}" class="btn mb-20 w-100">Proceed To CheckOut<i class="fi-rs-sign-out ml-15"></i></a>
                    @else
                        <a href="{{ route('login') }}" class="btn mb-20 w-100">Login to Checkout<i class="fi-rs-sign-in ml-15"></i></a>
                    @endauth
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Auto-submit form when quantity changes via buttons
        $('.qty-up, .qty-down').on('click', function(e) {
            e.preventDefault();
            // The shop.js handles the increment/decrement visual
            // We need to wait a tiny bit for the input value to update, then submit
            var form = $(this).closest('form');
            setTimeout(function() {
                form.submit();
            }, 300); // Increased delay slightly to ensure value update
        });

        // Also submit on manual input change
        $('.qty-val').on('change', function() {
            $(this).closest('form').submit();
        });
    });
</script>
@endpush
