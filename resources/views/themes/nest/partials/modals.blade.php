<!-- Modal -->
@if(isset($activePopups) && $activePopups->count() > 0)
    @php $popup = $activePopups->first(); @endphp
    <div class="modal fade custom-modal" id="onloadModal" data-popup-id="{{ $popup->id }}" tabindex="-1" aria-labelledby="onloadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background: transparent; border: none; box-shadow: none;">
                <div class="modal-body" style="padding: 0; position: relative;">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup" style="position: absolute; right: 15px; top: 15px; z-index: 10; background-color: rgba(255, 255, 255, 0.95); opacity: 1; border-radius: 50%; padding: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); width: 1em; height: 1em; border: 2px solid #eaeaea;"></button>
                    @if($popup->url)
                        <a href="{{ $popup->url }}">
                    @endif
                    <img src="{{ asset('storage/' . $popup->image) }}" alt="{{ $popup->name }}" style="width: 100%; height: auto; display: block; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                    @if($popup->url)
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif


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
