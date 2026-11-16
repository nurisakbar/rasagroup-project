@extends('layouts.shop')

@section('title', 'Forecast')


@section('content')
<style>
    .forecast-table { font-size: 13px; white-space: nowrap; }
    .forecast-table th, .forecast-table td { vertical-align: middle; padding: 6px 8px; }
    .forecast-table thead th { background: #F8F9FA; color: #253D4E; font-weight: 700; }
    .forecast-table .sticky-col { position: sticky; left: 0; background: #fff; z-index: 2; min-width: 220px; max-width: 260px; white-space: normal; }
    .forecast-table thead .sticky-col { background: #F8F9FA; }
    .forecast-table input.qty { width: 80px; text-align: right; padding: 4px 6px; height: 32px; font-size: 13px; border: 1px solid #ddd; border-radius: 6px; }
    .forecast-table td.locked { background: #f3f3f3; color: #888; text-align: right; }
    .forecast-table th.locked { color: #999; }
    .forecast-table tfoot td { font-weight: 700; background: #FBF5EF; text-align: right; }
    .forecast-help { font-size: 13px; color: #6b7280; }
</style>
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
                <div class="card border-0 shadow-sm border-radius-20 overflow-hidden mb-30" style="background-color: #ffffff;">
                    <div class="card-header bg-white border-bottom-0 p-30 pb-0 d-flex flex-wrap justify-content-between align-items-start">
                        <div>
                            <h3 class="mb-5" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">Forecast {{ $year }}</h3>
                            <p class="forecast-help mb-0">
                                Perkiraan kebutuhan per outlet rekrutan dan produk, dalam <strong>satuan botol/pcs</strong>.
                                Bulan yang sudah berjalan dikunci.
                                @if($salesName) Sales Rasa: <strong>{{ $salesName }}</strong>. @endif
                            </p>
                        </div>
                        <form method="GET" action="{{ route('buyer.forecast.index') }}" class="mt-10">
                            <select name="year" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 110px;">
                                @foreach($years as $y)
                                    <option value="{{ $y }}" {{ $y === $year ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <div class="card-body p-30 pt-20">
                        @if(session('import_errors'))
                            <div class="alert alert-danger">
                                <strong>Kesalahan di file:</strong>
                                <ul class="mb-0 mt-5">
                                    @foreach(session('import_errors') as $message)
                                        <li>{{ $message }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if($outlets->isEmpty())
                            <div class="alert alert-warning mb-0">
                                Belum ada outlet rekrutan. Forecast diisi per outlet yang mendaftar lewat link
                                <a href="{{ route('buyer.outlet-referral.index') }}">Rekrut Outlet</a>.
                            </div>
                        @else
                            <div class="row g-3 mb-20">
                                <div class="col-md-6">
                                    <div class="p-20 border-radius-15 h-100" style="background-color: #F8F9FA; border: 1.5px solid #ECECEC;">
                                        <h6 class="mb-10" style="color: #6A1B1B; font-weight: 700;">Upload Excel</h6>
                                        <p class="forecast-help mb-10">Unduh file, isi kolom Item Number dan jumlah per bulan, lalu upload kembali. Baris di file akan ditambahkan/diperbarui.</p>
                                        <a href="{{ route('buyer.forecast.export', ['year' => $year]) }}" class="btn btn-sm btn-outline-secondary mb-10"><i class="fi-rs-download me-1"></i>Unduh Template / Data</a>
                                        <form method="POST" action="{{ route('buyer.forecast.import') }}" enctype="multipart/form-data" class="d-flex gap-2">
                                            @csrf
                                            <input type="hidden" name="year" value="{{ $year }}">
                                            <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-control form-control-sm" required>
                                            <button type="submit" class="btn btn-sm" style="background-color: #6A1B1B; color: #fff;">Upload</button>
                                        </form>
                                        @error('file') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-20 border-radius-15 h-100" style="background-color: #F8F9FA; border: 1.5px solid #ECECEC;">
                                        <h6 class="mb-10" style="color: #6A1B1B; font-weight: 700;">Tambah Baris</h6>
                                        <form method="POST" action="{{ route('buyer.forecast.store') }}">
                                            @csrf
                                            <input type="hidden" name="year" value="{{ $year }}">
                                            <select name="outlet_id" class="form-select form-select-sm mb-10" required>
                                                <option value="">Pilih outlet</option>
                                                @foreach($outlets as $outlet)
                                                    <option value="{{ $outlet->id }}" {{ old('outlet_id') == $outlet->id ? 'selected' : '' }}>{{ $outlet->name }} ({{ \App\Services\DistributorForecastService::billTo($outlet) }})</option>
                                                @endforeach
                                            </select>
                                            <select name="product_id" class="form-select form-select-sm mb-10" required>
                                                <option value="">Pilih produk</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>{{ $product->code }} — {{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('outlet_id') <small class="text-danger d-block">{{ $message }}</small> @enderror
                                            @error('product_id') <small class="text-danger d-block">{{ $message }}</small> @enderror
                                            <button type="submit" class="btn btn-sm" style="background-color: #6A1B1B; color: #fff;">Tambah</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            @if($lines->isEmpty())
                                <p class="forecast-help mb-0">Belum ada baris forecast untuk {{ $year }}. Tambah baris atau upload Excel.</p>
                            @else
                                <form method="POST" action="{{ route('buyer.forecast.update') }}" id="forecast-form">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="year" value="{{ $year }}">
                                    <div class="table-responsive">
                                        <table class="table table-bordered forecast-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="sticky-col">Outlet / Produk</th>
                                                    @foreach(\App\Models\DistributorForecast::MONTH_LABELS as $m => $label)
                                                        <th class="text-end {{ $forecast->monthLocked($m) ? 'locked' : '' }}">{{ $label }}{!! $forecast->monthLocked($m) ? ' <i class="fi-rs-lock"></i>' : '' !!}</th>
                                                    @endforeach
                                                    <th class="text-end">Total</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($lines as $line)
                                                    <tr>
                                                        <td class="sticky-col">
                                                            <strong>{{ $line->outlet->name ?? '-' }}</strong>
                                                            <div class="text-muted" style="font-size: 12px;">{{ $line->product->code ?? '' }} · {{ $line->product->name ?? '' }}@if($line->product?->brand) · {{ $line->product->brand->name }}@endif</div>
                                                        </td>
                                                        @foreach(range(1, 12) as $m)
                                                            @if($forecast->monthLocked($m))
                                                                <td class="locked">{{ number_format($line->qty($m), 0, ',', '.') }}</td>
                                                            @else
                                                                <td><input type="number" min="0" step="1" class="qty" name="lines[{{ $line->id }}][{{ $m }}]" value="{{ $line->qty($m) }}"></td>
                                                            @endif
                                                        @endforeach
                                                        <td class="text-end"><strong>{{ number_format($line->total(), 0, ',', '.') }}</strong></td>
                                                        <td class="text-center">
                                                            @if($service->canDelete($line))
                                                                <button type="submit" form="delete-{{ $line->id }}" class="btn btn-sm btn-link text-danger p-0" title="Hapus baris"><i class="fi-rs-trash"></i></button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td class="sticky-col text-start">Total</td>
                                                    @foreach(range(1, 12) as $m)
                                                        <td>{{ number_format($totals[$m], 0, ',', '.') }}</td>
                                                    @endforeach
                                                    <td>{{ number_format($totals->sum(), 0, ',', '.') }}</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div class="text-end mt-20">
                                        <button type="submit" class="btn" style="background-color: #6A1B1B; color: #fff;">Simpan Perubahan</button>
                                    </div>
                                </form>
                                @foreach($lines as $line)
                                    @if($service->canDelete($line))
                                        <form method="POST" action="{{ route('buyer.forecast.destroy', $line) }}" id="delete-{{ $line->id }}" onsubmit="return confirm('Hapus baris forecast ini?');">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif
                                @endforeach
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
