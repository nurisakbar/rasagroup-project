@if($item->hasUnitDiscount())
    <div class="text-muted" style="text-decoration: line-through; font-weight: normal;">
        Rp {{ number_format($item->catalogUnitPrice(), 0, ',', '.') }}
    </div>
    <div>
        <strong>Rp {{ number_format($item->discountedUnitPrice(), 0, ',', '.') }}</strong>
        <div><small class="text-success">Diskon {{ rtrim(rtrim(number_format($item->unitDiscountPercent(), 1, ',', '.'), '0'), ',') }}%</small></div>
    </div>
@else
    Rp {{ number_format($item->discountedUnitPrice(), 0, ',', '.') }}
@endif
