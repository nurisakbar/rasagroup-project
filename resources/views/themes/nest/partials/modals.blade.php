<!-- Modal -->
@if(isset($activePopups) && $activePopups->count() > 0)
    @php $popup = $activePopups->first(); @endphp
    <div class="modal fade custom-modal" id="onloadModal" tabindex="-1" aria-labelledby="onloadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

<!-- Quick view -->
<div class="modal fade custom-modal" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-body">
                <div class="text-center p-30">
                    <div class="spinner-border text-brand" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
