@extends('layouts.shop')

@section('title', 'Forecast')

@section('content')
@php
    $selectedId = $selectedOutlet ? (string) $selectedOutlet->id : '';
    $openMonths = $forecast->openMonths();
    $monthLabels = \App\Models\DistributorForecast::MONTH_LABELS;
    $fmt = fn ($n) => number_format($n, 0, ',', '.');
@endphp

<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> Akun Saya
            <span></span> Forecast
        </div>
    </div>
</div>

<div class="page-content pt-50 pb-80" style="background-color: #F2EAE1;">
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                @include('buyer.partials.sidebar')
            </div>
            <div class="col-lg-9">
                <div class="fc-wrap">

                    {{-- Kepala --}}
                    <div class="fc-card fc-head">
                        <div class="fc-head-top">
                            <div>
                                <h1 class="fc-title">Forecast {{ $year }}</h1>
                                <p class="fc-help mb-0">Perkiraan kebutuhan tiap subdist rekrutan Anda per produk, dalam <strong>satuan botol/pcs</strong>. Bulan yang sedang berjalan dan sebelumnya dikunci.@if($salesName) Sales Rasa: <strong>{{ $salesName }}</strong>.@endif</p>
                            </div>
                            <form method="GET" action="{{ route('buyer.forecast.index') }}">
                                @if($selectedId)<input type="hidden" name="outlet" value="{{ $selectedId }}">@endif
                                <select name="year" class="form-select" onchange="this.form.submit()" aria-label="Tahun forecast">
                                    @foreach($years as $y)
                                        <option value="{{ $y }}" {{ $y === $year ? 'selected' : '' }}>Tahun {{ $y }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        @if($outlets->isNotEmpty())
                            <div class="fc-stats">
                                <div><span>{{ $outlets->count() }}</span>Subdist</div>
                                <div><span>{{ $outletSummary->count() }}</span>Sudah diisi</div>
                                <div><span>{{ $fmt($grandUnits) }}</span>Total unit {{ $year }}</div>
                            </div>
                        @endif
                    </div>

                    @if(session('import_errors'))
                        <div class="alert alert-danger fc-alert">
                            <strong>Kesalahan di file:</strong>
                            <ul class="mb-0 mt-5">@foreach(session('import_errors') as $message)<li>{{ $message }}</li>@endforeach</ul>
                        </div>
                    @endif

                    @if($outlets->isEmpty())
                        <div class="fc-card fc-empty">
                            <div class="fc-empty-icon"><i class="fi-rs-users"></i></div>
                            <h2>Belum ada subdist rekrutan</h2>
                            <p>Forecast diisi per subdist yang mendaftar lewat link rekrutmen Anda. Bagikan link Anda dulu, lalu kembali ke halaman ini.</p>
                            <a href="{{ route('buyer.outlet-referral.index') }}" class="fc-btn fc-btn-main">Buka Outlet dan Subdist</a>
                        </div>
                    @else
                        {{-- Langkah 1: pilih subdist --}}
                        <div class="fc-card">
                            <h2 class="fc-step"><span>1</span> Pilih subdist</h2>
                            <div class="fc-chips">
                                @foreach($outlets as $outlet)
                                    @php $sum = $outletSummary->get((string) $outlet->id); @endphp
                                    <a href="{{ route('buyer.forecast.index', ['year' => $year, 'outlet' => $outlet->id]) }}"
                                       class="fc-chip {{ (string) $outlet->id === $selectedId ? 'is-active' : '' }}">
                                        <strong>{{ $outlet->name }}</strong>
                                        <small>{{ $sum ? $sum['products'] . ' produk · ' . $fmt($sum['units']) . ' unit' : 'Belum diisi' }}</small>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        {{-- Langkah 2: isi forecast --}}
                        <div class="fc-card">
                            <div class="fc-step-row">
                                <h2 class="fc-step"><span>2</span> Isi forecast <em>{{ $selectedOutlet->name }}</em></h2>
                                <div class="fc-toolbar">
                                    <a href="{{ route('buyer.forecast.export', ['year' => $year, 'outlet' => $selectedId]) }}" class="fc-btn fc-btn-ghost" title="Unduh Excel berisi semua produk master untuk {{ $selectedOutlet->name }}, tinggal isi jumlahnya"><i class="fi-rs-download"></i> Template Excel</a>
                                    <button type="button" class="fc-btn fc-btn-out" data-bs-toggle="modal" data-bs-target="#addProductsModal"><i class="fi-rs-plus"></i> Tambah produk</button>
                                </div>
                            </div>

                            @if($lines->isEmpty())
                                <div class="fc-empty fc-empty-inline">
                                    <div class="fc-empty-icon"><i class="fi-rs-box"></i></div>
                                    <h3>Belum ada produk untuk {{ $selectedOutlet->name }}</h3>
                                    <p>Pilih produk yang diperkirakan dibeli subdist ini, lalu isi jumlah per bulan.</p>
                                    <button type="button" class="fc-btn fc-btn-main" data-bs-toggle="modal" data-bs-target="#addProductsModal"><i class="fi-rs-plus"></i> Tambah produk pertama</button>
                                </div>
                            @else
                                <p class="fc-help">Isi angka di bulan yang masih terbuka. Tombol <strong>Samakan</strong> menyalin angka bulan terbuka pertama ke semua bulan terbuka lainnya di baris itu.</p>
                                <form method="POST" action="{{ route('buyer.forecast.update') }}" id="forecast-form">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="year" value="{{ $year }}">
                                    <input type="hidden" name="outlet" value="{{ $selectedId }}">
                                    <div class="fc-scroll">
                                        <table class="fc-table">
                                            <thead>
                                                <tr>
                                                    <th class="fc-sticky">Produk</th>
                                                    @foreach($monthLabels as $m => $label)
                                                        <th class="{{ $forecast->monthLocked($m) ? 'is-locked' : '' }}">{{ $label }}@if($forecast->monthLocked($m)) <i class="fi-rs-lock"></i>@endif</th>
                                                    @endforeach
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($lines as $line)
                                                    <tr data-line="{{ $line->id }}">
                                                        <td class="fc-sticky">
                                                            <strong>{{ $line->product->name ?? '-' }}</strong>
                                                            <span class="fc-code">{{ $line->product->code ?? '' }}@if($line->product?->brand) · {{ $line->product->brand->name }}@endif</span>
                                                            <span class="fc-actions">
                                                                @if(count($openMonths) > 1)
                                                                    <button type="button" class="fc-link js-same" title="Salin angka bulan terbuka pertama ke bulan terbuka lainnya"><i class="fi-rs-copy"></i> Samakan</button>
                                                                @endif
                                                                @if($service->canDelete($line))
                                                                    <button type="submit" form="delete-{{ $line->id }}" class="fc-link fc-link-del" title="Hapus produk dari forecast"><i class="fi-rs-trash"></i> Hapus</button>
                                                                @endif
                                                            </span>
                                                        </td>
                                                        @foreach(range(1, 12) as $m)
                                                            @if($forecast->monthLocked($m))
                                                                <td class="is-locked">{{ $fmt($line->qty($m)) }}</td>
                                                            @else
                                                                <td><input type="number" min="0" step="1" inputmode="numeric" class="fc-qty" name="lines[{{ $line->id }}][{{ $m }}]" value="{{ $line->qty($m) }}" data-orig="{{ $line->qty($m) }}" data-month="{{ $m }}"></td>
                                                            @endif
                                                        @endforeach
                                                        <td class="fc-rowtotal"><strong>{{ $fmt($line->total()) }}</strong></td>

                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td class="fc-sticky">Total {{ $selectedOutlet->name }}</td>
                                                    @foreach(range(1, 12) as $m)
                                                        <td data-foot="{{ $m }}">{{ $fmt($totals[$m]) }}</td>
                                                    @endforeach
                                                    <td data-foot="all">{{ $fmt($totals->sum()) }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    {{-- Langkah 3: simpan --}}
                                    <div class="fc-savebar" id="savebar">
                                        <div class="fc-savebar-text"><span class="fc-step-mini">3</span> <span id="savebar-msg">Belum ada perubahan</span></div>
                                        <button type="submit" class="fc-btn fc-btn-main" id="savebtn" disabled>Simpan perubahan</button>
                                    </div>
                                </form>
                                @foreach($lines as $line)
                                    @if($service->canDelete($line))
                                        <form method="POST" action="{{ route('buyer.forecast.destroy', $line) }}" id="delete-{{ $line->id }}" onsubmit="return confirm('Hapus produk ini dari forecast {{ $selectedOutlet->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif
                                @endforeach
                            @endif
                        </div>

                        {{-- Excel (opsional) --}}
                        <details class="fc-card fc-excel" {{ session('import_errors') ? 'open' : '' }}>
                            <summary><i class="fi-rs-download"></i> Isi lewat Excel <small>(opsional, praktis untuk banyak produk sekaligus)</small></summary>
                            <ol class="fc-excel-steps">
                                <li><strong>Unduh template.</strong> Sudah berisi semua produk master (kode, nama, merek), tinggal isi jumlah per bulan di kolom kuning.</li>
                                <li><strong>Isi jumlahnya</strong> dalam botol/pcs. Baris yang dikosongkan diabaikan.</li>
                                <li><strong>Upload kembali</strong> file yang sama.</li>
                            </ol>
                            <div class="fc-excel-row">
                                <a href="{{ route('buyer.forecast.export', ['year' => $year, 'outlet' => $selectedId]) }}" class="fc-btn fc-btn-main"><i class="fi-rs-download"></i> Template {{ $selectedOutlet->name }}</a>
                                @if($outlets->count() > 1)
                                    <a href="{{ route('buyer.forecast.export', ['year' => $year]) }}" class="fc-btn fc-btn-out">Template semua subdist</a>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('buyer.forecast.import') }}" enctype="multipart/form-data" class="fc-upload mt-15">
                                @csrf
                                <input type="hidden" name="year" value="{{ $year }}">
                                <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-control" required>
                                <button type="submit" class="fc-btn fc-btn-main">Upload</button>
                            </form>
                            @error('file') <small class="text-danger">{{ $message }}</small> @enderror
                        </details>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($outlets->isNotEmpty())
{{-- Modal tambah produk (banyak sekaligus) --}}
<div class="modal fade" id="addProductsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form method="POST" action="{{ route('buyer.forecast.store') }}" class="modal-content fc-modal">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="outlet_id" value="{{ $selectedId }}">
            <div class="modal-header">
                <h5 class="modal-title">Tambah produk untuk {{ $selectedOutlet->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                @error('product_ids') <div class="text-danger small mb-10">{{ $message }}</div> @enderror
                @error('product_id') <div class="text-danger small mb-10">{{ $message }}</div> @enderror
                <input type="search" id="fc-product-search" class="form-control fc-search" placeholder="Cari kode atau nama produk">
                <div class="fc-product-list" id="fc-product-list">
                    @forelse($products as $product)
                        <label class="fc-product" data-text="{{ strtolower($product->code . ' ' . $product->name) }}">
                            <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" class="js-product-check">
                            <span><strong>{{ $product->name }}</strong><small>{{ $product->code }}</small></span>
                        </label>
                    @empty
                        <p class="fc-help mb-0">Semua produk sudah ditambahkan.</p>
                    @endforelse
                    <p class="fc-help d-none" id="fc-no-result">Produk tidak ditemukan.</p>
                </div>
            </div>
            <div class="modal-footer">
                <span class="fc-help me-auto" id="fc-picked">0 produk dipilih</span>
                <button type="button" class="fc-btn fc-btn-ghost" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="fc-btn fc-btn-main" id="fc-add-btn" disabled>Tambahkan</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
    .fc-wrap { display: flex; flex-direction: column; gap: 18px; }
    .fc-card { background: #fff; border: 1px solid #E3D6C8; border-radius: 16px; padding: 22px; box-shadow: 0 6px 20px rgba(106, 27, 27, 0.08); }
    .fc-title { font-size: clamp(22px, 5.5vw, 30px); font-weight: 800; color: #253D4E; margin: 0 0 6px; }
    .fc-help { font-size: 13px; color: #6b7280; line-height: 1.55; }
    .fc-head-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; flex-wrap: wrap; }
    .fc-head-top .form-select { min-width: 140px; height: 44px; border-radius: 12px; border: 1.5px solid #E3D6C8; background-color: #FAF6F1; font-weight: 700; }
    .fc-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 16px; padding-top: 16px; border-top: 1px dashed #E3D6C8; }
    .fc-stats div { background: #FAF6F1; border-radius: 12px; padding: 10px 12px; font-size: 12px; color: #8A7B70; font-weight: 600; }
    .fc-stats span { display: block; font-size: 20px; font-weight: 800; color: #6A1B1B; line-height: 1.2; }
    .fc-alert { border-radius: 12px; }
    .fc-step { font-size: 17px; font-weight: 700; color: #253D4E; margin: 0 0 14px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .fc-step span, .fc-step-mini { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; background: #6A1B1B; color: #fff; font-size: 14px; font-weight: 700; flex-shrink: 0; }
    .fc-step em { font-style: normal; color: #6A1B1B; }
    .fc-step-row { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 4px; }
    .fc-step-row .fc-step { margin-bottom: 10px; }
    .fc-chips { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 6px; -webkit-overflow-scrolling: touch; }
    .fc-chip { flex: 0 0 auto; min-width: 150px; max-width: 220px; border: 1.5px solid #E3D6C8; border-radius: 14px; padding: 10px 14px; text-decoration: none; background: #FFFDFB; display: flex; flex-direction: column; gap: 2px; }
    .fc-chip strong { color: #253D4E; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .fc-chip small { color: #8A7B70; font-size: 12px; }
    .fc-chip:hover { border-color: #6A1B1B; }
    .fc-chip.is-active { background: #6A1B1B; border-color: #6A1B1B; box-shadow: 0 6px 16px rgba(106, 27, 27, 0.25); }
    .fc-chip.is-active strong, .fc-chip.is-active small { color: #fff; }
    .fc-scroll { overflow-x: auto; border: 1px solid #F0E6DB; border-radius: 12px; }
    .fc-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px; white-space: nowrap; }
    .fc-table th, .fc-table td { padding: 8px 8px; text-align: right; vertical-align: middle; border-bottom: 1px solid #F0E6DB; background: #fff; }
    .fc-table thead th { background: #FAF6F1; color: #8A7B70; font-size: 12px; text-transform: uppercase; letter-spacing: .3px; font-weight: 700; }
    .fc-table th.is-locked, .fc-table td.is-locked { background: #F4F1EE; color: #9A9A9A; }
    .fc-table .fc-sticky { position: sticky; left: 0; z-index: 2; text-align: left; min-width: 210px; max-width: 250px; white-space: normal; background: #fff; box-shadow: 2px 0 0 #F0E6DB; }
    .fc-table thead .fc-sticky { background: #FAF6F1; }
    .fc-table tfoot td { background: #FBF5EF; font-weight: 800; color: #6A1B1B; border-bottom: 0; }
    .fc-table tfoot .fc-sticky { background: #FBF5EF; }
    .fc-table td.fc-sticky strong { display: block; color: #253D4E; line-height: 1.3; }
    .fc-code { display: block; font-size: 11px; color: #8A7B70; margin-top: 2px; }
    .fc-qty { width: 72px; height: 36px; text-align: right; padding: 0 8px; font-size: 13px; border: 1.5px solid #E3D6C8; border-radius: 8px; background: #FAF6F1; }
    .fc-qty:focus { outline: none; border-color: #6A1B1B; background: #fff; box-shadow: 0 0 0 3px rgba(106, 27, 27, 0.1); }
    .fc-qty.is-dirty { border-color: #B7791F; background: #FFF8E6; }
    .fc-rowtotal { font-weight: 800; color: #253D4E; }
    .fc-actions { display: flex; gap: 4px; margin-top: 4px; white-space: nowrap; }
    .fc-link { background: #FAF6F1 !important; border: 1px solid #E3D6C8 !important; border-radius: 8px !important; color: #6A1B1B !important; font-weight: 700 !important; font-size: 12px !important; padding: 3px 10px !important; height: auto !important; min-height: 0 !important; line-height: 1.4 !important; width: auto !important; box-shadow: none !important; cursor: pointer; display: inline-flex !important; align-items: center; gap: 4px; }
    .fc-link:hover { background: #6A1B1B !important; color: #fff !important; border-color: #6A1B1B !important; }
    .fc-link-del { color: #B42318 !important; border-color: #F2C7C2 !important; background: #FFF7F6 !important; }
    .fc-link-del:hover { background: #B42318 !important; border-color: #B42318 !important; color: #fff !important; }
    .fc-savebar { position: sticky; bottom: 0; z-index: 5; display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-top: 16px; padding: 12px 14px; background: #FAF6F1; border: 1.5px solid #E3D6C8; border-radius: 14px; }
    .fc-savebar.is-dirty { background: #FFF8E6; border-color: #E7C98A; }
    .fc-savebar-text { display: flex; align-items: center; gap: 10px; font-weight: 700; color: #253D4E; font-size: 14px; }
    .fc-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 44px; padding: 0 20px; border-radius: 12px; font-weight: 700; font-size: 14px; text-decoration: none; border: 2px solid transparent; cursor: pointer; }
    .fc-btn-main { background: #6A1B1B !important; color: #fff !important; border-color: #6A1B1B !important; box-shadow: 0 8px 18px rgba(106, 27, 27, 0.22) !important; }
    .fc-btn-main:hover { background: #531414 !important; }
    .fc-btn-main:disabled { background: #BFA9A9 !important; border-color: #BFA9A9 !important; box-shadow: none !important; cursor: not-allowed; }
    .fc-btn-out { background: #fff !important; color: #6A1B1B !important; border-color: #6A1B1B !important; }
    .fc-btn-out:hover { background: #FAF6F1 !important; }
    .fc-btn-ghost { background: #FAF6F1 !important; color: #6A1B1B !important; border-color: #D9CFC4 !important; }
    .fc-empty { text-align: center; padding: 34px 20px; }
    .fc-empty-inline { padding: 26px 10px; }
    .fc-empty-icon { width: 64px; height: 64px; border-radius: 50%; background: rgba(106, 27, 27, 0.08); color: #6A1B1B; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 12px; }
    .fc-empty h2, .fc-empty h3 { font-size: 18px; font-weight: 800; color: #253D4E; margin-bottom: 6px; }
    .fc-empty p { color: #6b7280; max-width: 420px; margin: 0 auto 16px; font-size: 14px; }
    .fc-excel summary { cursor: pointer; font-weight: 700; color: #6A1B1B; list-style: none; display: flex; align-items: center; gap: 8px; }
    .fc-excel summary::-webkit-details-marker { display: none; }
    .fc-excel summary { flex-wrap: wrap; }
    .fc-excel summary small { color: #8A7B70; font-weight: 500; flex-basis: 100%; }
    .fc-excel-row { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
    .fc-excel-steps { margin: 14px 0 16px; padding-left: 22px; list-style: decimal !important; font-size: 13px; color: #5B6B7B; line-height: 1.7; }
    .fc-excel-steps li { list-style: decimal !important; }
    .fc-toolbar { display: flex; gap: 8px; flex-wrap: wrap; }
    @media (max-width: 575px) { .fc-toolbar { width: 100%; } .fc-toolbar .fc-btn { flex: 1; padding: 0 10px; } }
    .fc-upload { display: flex; gap: 8px; flex: 1 1 280px; }
    .fc-upload .form-control { height: 44px; border-radius: 12px; }
    .fc-modal { border-radius: 18px; border: 1px solid #E3D6C8; }
    .fc-search { height: 46px; border-radius: 12px; border: 1.5px solid #E3D6C8; background: #FAF6F1; margin-bottom: 12px; }
    .fc-product-list { max-height: 340px; overflow-y: auto; border: 1px solid #F0E6DB; border-radius: 12px; }
    .fc-product { display: flex; gap: 12px; align-items: flex-start; padding: 10px 14px; border-bottom: 1px solid #F0E6DB; cursor: pointer; margin: 0; }
    .fc-product:last-of-type { border-bottom: 0; }
    .fc-product:hover { background: #FFFDFB; }
    .fc-product input { margin-top: 4px; accent-color: #6A1B1B; width: 18px; height: 18px; flex-shrink: 0; }
    .fc-product strong { display: block; color: #253D4E; font-size: 14px; line-height: 1.3; }
    .fc-product small { color: #8A7B70; font-size: 12px; }
    @media (max-width: 575px) {
        .fc-card { padding: 16px; }
        .fc-stats div { padding: 8px 8px; }
        .fc-stats span { font-size: 17px; }
        .fc-savebar { flex-direction: column; align-items: stretch; }
        .fc-savebar .fc-btn { width: 100%; }
        .fc-upload { flex-direction: column; }
        .fc-table .fc-sticky { min-width: 140px; max-width: 150px; }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var form = document.getElementById('forecast-form');
    if (form) {
        var btn = document.getElementById('savebtn'), msg = document.getElementById('savebar-msg'), bar = document.getElementById('savebar');
        var fmt = function (n) { return n.toLocaleString('id-ID'); };
        var inputs = form.querySelectorAll('.fc-qty');

        function recompute() {
            var dirty = 0, colSum = {}, all = 0;
            form.querySelectorAll('tbody tr').forEach(function (tr) {
                var rowSum = 0;
                // Bulan terkunci (teks) + bulan terbuka (input) dijumlahkan per baris dan kolom.
                tr.querySelectorAll('td').forEach(function (td, i) {
                    var m = i; // td[0] = produk, td[1..12] = bulan
                    if (m < 1 || m > 12) return;
                    var input = td.querySelector('.fc-qty');
                    var val = input ? (parseInt(input.value, 10) || 0) : (parseInt(td.textContent.replace(/\./g, ''), 10) || 0);
                    rowSum += val; colSum[m] = (colSum[m] || 0) + val;
                });
                all += rowSum;
                tr.querySelector('.fc-rowtotal strong').textContent = fmt(rowSum);
            });
            inputs.forEach(function (el) {
                var changed = String(parseInt(el.value, 10) || 0) !== el.dataset.orig;
                el.classList.toggle('is-dirty', changed);
                if (changed) dirty++;
            });
            for (var m = 1; m <= 12; m++) { var c = form.querySelector('[data-foot="' + m + '"]'); if (c) c.textContent = fmt(colSum[m] || 0); }
            var t = form.querySelector('[data-foot="all"]'); if (t) t.textContent = fmt(all);
            btn.disabled = dirty === 0;
            bar.classList.toggle('is-dirty', dirty > 0);
            msg.textContent = dirty > 0 ? dirty + ' angka berubah, belum disimpan' : 'Belum ada perubahan';
            return dirty;
        }

        form.addEventListener('input', recompute);
        form.querySelectorAll('.js-same').forEach(function (b) {
            b.addEventListener('click', function () {
                var row = b.closest('tr'), fields = row.querySelectorAll('.fc-qty');
                if (!fields.length) return;
                var v = fields[0].value;
                fields.forEach(function (f) { f.value = v; });
                recompute();
            });
        });
        // Enter pindah ke kolom bulan berikutnya, bukan mengirim form.
        form.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && e.target.classList.contains('fc-qty')) {
                e.preventDefault();
                var all = Array.prototype.slice.call(form.querySelectorAll('.fc-qty')), i = all.indexOf(e.target);
                if (all[i + 1]) { all[i + 1].focus(); all[i + 1].select(); }
            }
        });
        var submitting = false;
        form.addEventListener('submit', function () { submitting = true; });
        window.addEventListener('beforeunload', function (e) {
            if (!submitting && recompute() > 0) { e.preventDefault(); e.returnValue = ''; }
        });
        recompute();
    }

    // Modal tambah produk: cari + hitung pilihan
    var search = document.getElementById('fc-product-search');
    if (search) {
        var items = document.querySelectorAll('.fc-product'), none = document.getElementById('fc-no-result');
        var picked = document.getElementById('fc-picked'), addBtn = document.getElementById('fc-add-btn');
        function count() {
            var n = document.querySelectorAll('.js-product-check:checked').length;
            picked.textContent = n + ' produk dipilih'; addBtn.disabled = n === 0;
            addBtn.textContent = n > 0 ? 'Tambahkan ' + n + ' produk' : 'Tambahkan';
        }
        search.addEventListener('input', function () {
            var q = search.value.trim().toLowerCase(), shown = 0;
            items.forEach(function (it) { var ok = !q || it.dataset.text.indexOf(q) !== -1; it.style.display = ok ? '' : 'none'; if (ok) shown++; });
            none.classList.toggle('d-none', shown > 0 || !items.length);
        });
        document.querySelectorAll('.js-product-check').forEach(function (c) { c.addEventListener('change', count); });
        count();
    }
})();
</script>
@endpush
