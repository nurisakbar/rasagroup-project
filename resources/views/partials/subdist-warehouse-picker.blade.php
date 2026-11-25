@php
    $subdistOptions = auth()->check() && auth()->user()->isSubdist()
        ? auth()->user()->subdistWarehouseOptions()->filter(fn ($w) => $w->isReadyForSubdist())->values()
        : collect();
    $subdistCurrent = auth()->check() ? auth()->user()->subdistWarehouse() : null;
@endphp
@if($subdistOptions->count() > 1 && $subdistCurrent)
    <div class="mb-15 p-15" style="background: #FAF6F1; border: 1.5px solid #E3D6C8; border-radius: 12px;">
        <form action="{{ route('buyer.subdist.warehouse.select') }}" method="POST" class="d-flex flex-wrap align-items-center gap-2">
            @csrf
            <label for="subdist-warehouse" class="mb-0 font-sm" style="color:#6A1B1B; font-weight:700;"><i class="fi-rs-building mr-5"></i>Gudang asal pesanan:</label>
            <select id="subdist-warehouse" name="warehouse_id" class="form-select form-select-sm" style="max-width: 320px; border-radius: 10px;" onchange="this.form.submit()">
                @foreach($subdistOptions as $w)
                    <option value="{{ $w->id }}" {{ $w->id === $subdistCurrent->id ? 'selected' : '' }}>{{ $w->name }}</option>
                @endforeach
            </select>
        </form>
    </div>
@endif
