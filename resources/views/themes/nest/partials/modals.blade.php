<!-- Modal -->
@if(isset($activePopups) && $activePopups->count() > 0)
    @php $popup = $activePopups->first(); @endphp
    <div class="modal fade custom-modal" id="onloadModal" tabindex="-1" aria-labelledby="onloadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                <div class="modal-body" style="padding: 0;">
                    @if($popup->url)
                        <a href="{{ $popup->url }}">
                    @endif
                    <img src="{{ asset('storage/' . $popup->image) }}" alt="{{ $popup->name }}" style="width: 100%; height: auto; display: block; border-radius: 10px;">
                    @if($popup->url)
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Pilih satuan (add to cart — produk dual UoM) -->
<div class="modal fade custom-modal" id="modalChooseCartUom" tabindex="-1" aria-labelledby="modalChooseCartUomLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-radius-20">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="modalChooseCartUomLabel" style="font-weight: 700; color: #253D4E;">Pilih satuan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body pt-15 pb-30">
                <p class="font-heading mb-5" id="modalChooseCartUomProduct" style="color: #6A1B1B;"></p>
                <p class="text-muted font-sm mb-25">Tambahkan ke keranjang dalam satuan terkecil atau terbesar?</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn" id="modalChooseCartUomBase">Satuan terkecil</button>
                    <button type="button" class="btn btn-outline-rasa" id="modalChooseCartUomLarge" style="border-width: 2px;">Satuan terbesar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick view -->
<div class="modal fade custom-modal" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            <div class="modal-body">
                <div class="text-center p-30">
                    <div class="spinner-border text-brand" role="status">
                        <span class="visually-hidden">Memuat...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
