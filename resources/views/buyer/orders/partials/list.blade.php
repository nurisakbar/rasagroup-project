<div class="orders-list">
    @forelse($orders as $order)
        @php
            $statusClass = match($order->order_status) {
                'pending' => 'bg-warning',
                'processing' => 'bg-info',
                'shipped' => 'bg-primary',
                'delivered' => 'bg-success',
                'cancelled' => 'bg-danger',
                default => 'bg-secondary',
            };
            $statusLabel = match($order->order_status) {
                'pending' => 'Menunggu Pembayaran',
                'processing' => 'Dikemas',
                'shipped' => 'Dikirim',
                'delivered' => 'Selesai',
                'cancelled' => 'Batal',
                default => ucfirst($order->order_status),
            };
            
            // Get the first item to show in preview
            $firstItem = $order->items->first();
            $remainingCount = $order->items->count() - 1;
        @endphp
        <div class="order-card mb-20">
            <a href="{{ route('buyer.orders.show', $order) }}" class="stretched-link order-card-stretched-link" aria-label="Lihat detail pesanan #{{ $order->order_number }}"></a>
            <div class="order-header p-25 d-flex justify-content-between align-items-center">
                <div class="order-meta d-flex align-items-center">
                    <div class="mr-30">
                        <span class="d-block text-muted font-xs mb-1">NO. PESANAN</span>
                        <span class="fw-bold text-dark font-sm">#{{ $order->order_number }}</span>
                    </div>
                    <div class="mr-30">
                        <span class="d-block text-muted font-xs mb-1">TANGGAL</span>
                        <span class="fw-bold text-dark font-sm">{{ $order->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="d-none d-md-block">
                        <span class="d-block text-muted font-xs mb-1">TOTAL</span>
                        <span class="fw-bold text-maroon font-sm">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="order-status d-none d-md-block">
                    <span class="badge rounded-pill {{ $statusClass }} py-2 px-3 text-white font-xs">{{ $statusLabel }}</span>
                </div>
            </div>
            <div class="order-body p-25">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        @if($firstItem)
                            <div class="d-flex align-items-center">
                                <div class="product-thumb mr-20">
                                    <img src="{{ $firstItem->product->image_url ?: asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" 
                                         alt="{{ $firstItem->product->display_name }}"
                                         class="rounded-12" style="width: 75px; height: 75px; object-fit: cover; border: 1.5px solid #f1f1f1;">
                                </div>
                                <div class="product-info">
                                    <h6 class="mb-1 text-dark truncate-1">{{ $firstItem->product->display_name }}</h6>
                                    <p class="font-sm text-muted mb-0">{{ $firstItem->quantity }} item x Rp {{ number_format($firstItem->price, 0, ',', '.') }}</p>
                                    @if($remainingCount > 0)
                                        <p class="font-xs text-maroon mt-1 fw-bold">+{{ $remainingCount }} produk lainnya</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <!-- Mobile only total and status info -->
                        <div class="d-flex d-md-none mb-15 justify-content-between align-items-center">
                            <div class="text-start">
                                <span class="d-block text-muted font-xs mb-1">TOTAL</span>
                                <span class="fw-bold text-maroon font-sm">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="badge rounded-pill {{ $statusClass }} py-2 px-3 text-white font-xs">{{ $statusLabel }}</span>
                            </div>
                        </div>

                        <div class="d-grid d-md-block gap-2">
                            <a href="{{ route('buyer.orders.show', $order) }}" class="btn btn-sm btn-outline rounded-pill">
                                <i class="fi-rs-eye mr-5"></i> Lihat Detail
                            </a>
                            @if($order->order_status === 'shipped' && $order->tracking_number)
                                <a href="{{ route('buyer.orders.show', $order) }}#btn-track-order" class="btn btn-sm rounded-pill ms-md-2">
                                    <i class="fi-rs-truck mr-5"></i> Lacak
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="empty-state text-center py-5 border rounded-10 bg-white">
            <div class="mb-4">
                <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-cart.svg') }}" alt="Empty" style="width: 100px; opacity: 0.3;">
            </div>
            <h4 class="mb-2">Belum Ada Pesanan</h4>
            <p class="text-muted mb-4 px-5">Mulai perjalanan belanja Anda dan temukan berbagai produk berkualitas kami.</p>
            <a href="{{ route('products.index') }}" class="btn rounded-pill">
                <i class="fi-rs-shopping-bag mr-10"></i>Mulai Belanja
            </a>
        </div>
    @endforelse
</div>

<div class="pagination-area mt-40">
    {{ $orders->links() }}
</div>
