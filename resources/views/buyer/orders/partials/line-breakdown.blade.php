{{-- Rincian per baris: harga termasuk PPN, harga sebelum PPN, diskon, dan PPN per produk.
     $breakdown dari App\Support\OrderLineBreakdown::fromOrder(). --}}
@php
    $rp = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $usesPpn = $breakdown['uses_ppn'];
    $percentLabel = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',') . '%';
@endphp
<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-borderless align-middle mb-0">
            <thead class="bg-brand-light">
                <tr>
                    <th class="ps-4 py-3 font-sm text-dark fw-bold">Produk</th>
                    <th class="py-3 font-sm text-dark fw-bold text-center">Jumlah</th>
                    <th class="py-3 font-sm text-dark fw-bold text-end">Harga Satuan<br><span class="font-xs fw-normal text-muted">{{ $usesPpn ? 'termasuk PPN' : 'tanpa PPN' }}</span></th>
                    @if($usesPpn)
                        <th class="py-3 font-sm text-dark fw-bold text-end">Harga<br><span class="font-xs fw-normal text-muted">sebelum PPN</span></th>
                    @endif
                    <th class="py-3 font-sm text-dark fw-bold text-end">Diskon<br><span class="font-xs fw-normal text-muted">{{ $usesPpn ? 'dari harga sebelum PPN' : '' }}</span></th>
                    @if($usesPpn)
                        <th class="py-3 font-sm text-dark fw-bold text-end">{{ $breakdown['tax_label'] }}</th>
                    @endif
                    <th class="pe-4 py-3 font-sm text-dark fw-bold text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($breakdown['lines'] as $line)
                    @php $item = $line['item']; @endphp
                    <tr class="border-bottom">
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="product-img-wrap mr-15">
                                    <img src="{{ $item->product?->image_url ?: asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}"
                                         alt="{{ $line['name'] }}" class="rounded"
                                         style="width: 60px; height: 60px; object-fit: cover; border: 1px solid #eee;"
                                         onerror="this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}'">
                                </div>
                                <h6 class="font-sm mb-0 text-dark">
                                    @if($item->product)
                                        {{ $line['code'] }} - {{ $line['name'] }}
                                    @else
                                        Produk dihapus
                                    @endif
                                </h6>
                            </div>
                        </td>
                        <td class="py-3 text-center font-sm text-nowrap" data-label="Jumlah">{{ $line['quantity_label'] }}</td>
                        <td class="py-3 text-end font-sm text-nowrap" data-label="Harga satuan">{{ $rp($line['unit_price_incl']) }}</td>
                        @if($usesPpn)
                            <td class="py-3 text-end font-sm text-nowrap" data-label="Harga sebelum PPN">{{ $rp($line['line_dpp']) }}</td>
                        @endif
                        <td class="py-3 text-end font-sm text-nowrap" data-label="Diskon">
                            @if($line['discount_amount'] > 0)
                                <span class="text-danger">−{{ $rp($line['discount_amount']) }}</span><br>
                                <span class="font-xs text-muted">{{ $percentLabel($line['discount_percent']) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        @if($usesPpn)
                            <td class="py-3 text-end font-sm text-nowrap" data-label="{{ $breakdown['tax_label'] }}">{{ $rp($line['ppn']) }}</td>
                        @endif
                        <td class="pe-4 py-3 text-end fw-bold text-brand text-nowrap" data-label="Total">{{ $rp($line['total']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="font-xs text-muted px-4 pt-3 mb-0">{{ $breakdown['note'] }}</p>
</div>
<div class="card-footer bg-white p-4 border-top">
    <div class="row justify-content-end">
        <div class="col-md-6 col-lg-5">
            <div class="price-summary">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted font-sm">Subtotal Produk</span>
                    <span class="font-sm text-dark fw-bold">{{ $rp($breakdown['products_total']) }}</span>
                </div>
                @if($breakdown['order_discount'] > 0)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted font-sm">{{ $breakdown['order_discount_label'] }}</span>
                        <span class="font-sm text-danger fw-bold">−{{ $rp($breakdown['order_discount']) }}</span>
                    </div>
                @endif
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted font-sm">Ongkos Kirim</span>
                    <span class="font-sm text-dark fw-bold">{{ $rp($breakdown['shipping']) }}</span>
                </div>
                @if($breakdown['service_fee'] > 0)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted font-sm">Biaya Layanan</span>
                        <span class="font-sm text-dark fw-bold">{{ $rp($breakdown['service_fee']) }}</span>
                    </div>
                @endif
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <h5 class="mb-0 text-dark fw-bold">Total Bayar</h5>
                    <h5 class="mb-0 text-brand fw-bold text-nowrap">{{ $rp($breakdown['grand_total']) }}</h5>
                </div>
            </div>
        </div>
    </div>
</div>
