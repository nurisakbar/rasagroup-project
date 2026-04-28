@php
    $miniCarts = auth()->check() 
        ? \App\Models\Cart::with('product')->where('user_id', auth()->id())->where('cart_type', 'regular')->latest()->get()
        : \App\Models\Cart::with('product')->where('session_id', session()->getId())->where('cart_type', 'regular')->latest()->get();
    $miniCartTotal = $miniCarts->sum(function($item) { return $item->product->price * $item->quantity; });
@endphp

<ul>
    @forelse($miniCarts as $cartItem)
    <li>
        <div class="shopping-cart-img">
            <a href="{{ route('products.show', $cartItem->product) }}">
                <img alt="{{ $cartItem->product->name }}" src="{{ $cartItem->product->image_url ? $cartItem->product->image_url : asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" onerror="this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}'">
            </a>
        </div>
        <div class="shopping-cart-title">
            <h4><a href="{{ route('products.show', $cartItem->product) }}">{{ Str::limit($cartItem->product->display_name, 15) }}</a></h4>
            <h4><span>{{ $cartItem->quantity }} × </span>Rp {{ number_format($cartItem->product->price, 0, ',', '.') }}</h4>
        </div>
        <div class="shopping-cart-delete">
            <form action="{{ route('cart.destroy', $cartItem) }}" method="POST" class="delete-cart-item-form">
                @csrf
                @method('DELETE')
                <button type="submit" style="background:none; border:none; padding:0; cursor:pointer;" onclick="return confirm('Hapus item ini?')"><i class="fi-rs-cross-small"></i></button>
            </form>
        </div>
    </li>
    @empty
    <li>
        <div class="shopping-cart-title">
            <h4>Keranjang masih kosong</h4>
        </div>
    </li>
    @endforelse
</ul>
<div class="shopping-cart-footer">
    <div class="shopping-cart-total">
        <h4>Total <span>Rp {{ number_format($miniCartTotal, 0, ',', '.') }}</span></h4>
    </div>
    <div class="shopping-cart-button" style="display: flex; gap: 8px; justify-content: space-between;">
        <a href="{{ route('cart.index') }}" style="flex: 1; background-color: #6B1D1D; color: #FFFFFF; border-radius: 12px; font-weight: 800; padding: 12px; border: none; text-align: center; font-size: 14px; text-transform: capitalize;">Lihat Keranjang &rarr;</a>
        <a href="{{ route('checkout.index') }}" style="flex: 1; background-color: #6B1D1D; color: #FFFFFF; border-radius: 12px; font-weight: 800; padding: 12px; border: none; text-align: center; font-size: 14px; text-transform: capitalize;">Checkout &rarr;</a>
    </div>
</div>
