<div class="orders-list">
    @forelse($orders as $order)
        @php
            $statusLabel = match($order->order_status) {
                'pending' => 'BELUM BAYAR',
                'processing' => 'SEDANG DIKEMAS',
                'shipped' => 'DIKIRIM',
                'delivered' => 'SELESAI',
                'cancelled' => 'DIBATALKAN',
                default => strtoupper($order->order_status),
            };
            $statusColor = match($order->order_status) {
                'pending' => '#ee4d2d',
                'processing' => '#ee4d2d',
                'shipped' => '#ee4d2d',
                'delivered' => '#26aa99',
                'cancelled' => '#757575',
                default => '#ee4d2d',
            };
        @endphp
        <div class="card mb-4 border-0 shadow-sm" style="background-color: #fff; border-radius: 2px;">
            <!-- Header -->
            <div class="card-header bg-white px-4 py-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #f1f1f1;">
                <div class="d-flex align-items-center">
                    <span class="badge me-2" style="background-color: #ee4d2d; font-size: 10px; padding: 3px 5px; border-radius: 2px;">Mall</span>
                    <span class="fw-bold text-dark" style="font-size: 14px;">Pesanan #{{ $order->order_number }}</span>
                </div>
                <div>
                    <span class="fw-bold text-end" style="color: {{ $statusColor }}; font-size: 13px;">{{ $statusLabel }}</span>
                </div>
            </div>
            
            <!-- Body: Items -->
            <div class="card-body p-0">
                <a href="{{ route('buyer.orders.show', $order) }}" class="text-decoration-none text-dark d-block">
                    @foreach($order->items as $item)
                        <div class="d-flex p-4 {{ !$loop->last ? 'border-bottom' : '' }}" style="background-color: #fafafa; border-bottom-color: #f1f1f1 !important;">
                            <div class="flex-shrink-0 me-3">
                                <img src="{{ $item->product->image_url ?: asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" 
                                     alt="{{ $item->product->display_name }}"
                                     style="width: 80px; height: 80px; object-fit: cover; border: 1px solid #e1e1e1; background: #fff;">
                            </div>
                            <div class="flex-grow-1 d-flex flex-column flex-sm-row justify-content-between">
                                <div>
                                    <h6 class="mb-1 text-dark" style="font-size: 14px; font-weight: 500; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ $item->product->display_name }}
                                    </h6>
                                    <div class="mb-2 mt-1">
                                        @if($item->product->category)
                                            <span class="badge me-1 px-2 py-1" style="background-color: #e2e8f0; color: #475569; border: 1px solid #cbd5e1; font-size: 11px; font-weight: 600;">{{ $item->product->category->name }}</span>
                                        @endif
                                        @if($item->product->brand)
                                            <span class="badge px-2 py-1" style="background-color: #e2e8f0; color: #475569; border: 1px solid #cbd5e1; font-size: 11px; font-weight: 600;">{{ $item->product->brand->name }}</span>
                                        @endif
                                    </div>
                                    <p class="text-dark mb-0" style="font-size: 13px;">
                                        <span style="color: #334155; margin-right: 8px; font-weight: 500;">Harga: Rp{{ number_format($item->price, 0, ',', '.') }}</span> 
                                        <strong style="color: #0f172a;">x{{ $item->quantity }}</strong>
                                    </p>
                                </div>
                                <div class="text-start text-sm-end ms-sm-3 mt-3 mt-sm-0 d-flex flex-row flex-sm-column justify-content-between align-items-sm-end">
                                    <span class="mb-0 mb-sm-1" style="color: #334155; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Subtotal</span>
                                    <span style="color: #ee4d2d; font-size: 16px; font-weight: 700;">Rp{{ number_format($item->quantity * $item->price, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </a>
            </div>
            
            <!-- Footer -->
            <div class="card-footer bg-white px-4 py-4" style="border-top: 1px solid #f1f1f1;">
                <div class="d-flex justify-content-end align-items-center mb-4">
                    <span class="text-dark me-2" style="font-size: 14px;">Total Pesanan:</span>
                    <span class="fw-bold" style="color: #ee4d2d; font-size: 22px;">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
                
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                    <div class="mb-3 mb-md-0">
                        <span class="text-muted" style="font-size: 12px;">Dibuat pada {{ $order->created_at->format('d-m-Y H:i') }}</span>
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('buyer.orders.show', $order) }}" class="btn text-white px-4 py-2 text-center" style="background-color: #ee4d2d; font-size: 14px; min-width: 140px; border-radius: 2px;">
                            Hubungi Penjual
                        </a>
                        @if($order->order_status === 'shipped' && $order->tracking_number)
                            <a href="{{ route('buyer.orders.show', $order) }}#btn-track-order" class="btn px-4 py-2 text-center" style="background-color: #fff; color: #555; border: 1px solid #ccc; font-size: 14px; min-width: 140px; border-radius: 2px;">
                                Lacak Pesanan
                            </a>
                        @else
                            <a href="{{ route('buyer.orders.show', $order) }}" class="btn px-4 py-2 text-center" style="background-color: #fff; color: #555; border: 1px solid #ccc; font-size: 14px; min-width: 140px; border-radius: 2px;">
                                Batalkan Pesanan
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="empty-state text-center py-5 border bg-white" style="border-radius: 2px;">
            <div class="mb-4">
                <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-cart.svg') }}" alt="Empty" style="width: 100px; opacity: 0.3;">
            </div>
            <h4 class="mb-2 text-dark" style="font-size: 16px;">Belum Ada Pesanan</h4>
            <p class="text-muted mb-4 px-5" style="font-size: 14px;">Mulai perjalanan belanja Anda dan temukan berbagai produk berkualitas kami.</p>
            <a href="{{ route('products.index') }}" class="btn text-white px-4 py-2" style="background-color: #ee4d2d; border-radius: 2px; font-size: 14px;">
                Mulai Belanja
            </a>
        </div>
    @endforelse
</div>

<div class="pagination-area mt-40">
    {{ $orders->links() }}
</div>
