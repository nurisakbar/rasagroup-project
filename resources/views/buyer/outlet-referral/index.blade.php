@extends('layouts.shop')

@section('title', 'Outlet dan Subdist')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> Akun Saya
            <span></span> Outlet dan Subdist
        </div>
    </div>
</div>

<div class="page-content pt-50 pb-80" style="background-color: #F2EAE1;">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                @include('buyer.partials.sidebar')
            </div>
            <div class="col-lg-8">
                <div class="tab-content account dashboard-content">
                    <div class="tab-pane fade show active" role="tabpanel">
                        <div class="card border-0 shadow-sm border-radius-20 overflow-hidden mb-40" style="background-color: #ffffff;">
                            <div class="card-header bg-white border-bottom-0 p-30 pb-0">
                                <h3 class="mb-0" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">Outlet dan Subdist</h3>
                                <p class="text-muted font-sm">Bagikan link di bawah ini ke calon pendaftar. Pendaftar tercatat atas nama distributor Anda, dan atas nama sales bila memakai link sales.</p>
                            </div>
                            <div class="card-body p-30 pt-10">
                                <div class="p-30 border-radius-15 mb-30" style="background-color: #F8F9FA; border: 1.5px solid #ECECEC;">
                                    <h5 class="mb-10" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #6A1B1B;">Kode Sales Distributor</h5>
                                    <div class="d-flex align-items-center">
                                        <h3 class="mb-0 me-3" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #6A1B1B;">{{ $user->distributor_sales_code }}</h3>
                                        <button class="btn btn-sm px-20 btn-copy" style="background-color: #6A1B1B; color: #fff;" data-clipboard-text="{{ $user->distributor_sales_code }}">
                                            <i class="fi-rs-copy me-1"></i>Salin Kode
                                        </button>
                                    </div>
                                </div>

                                <div class="p-30 border-radius-15 mb-30" style="background-color: #F8F9FA; border: 1.5px solid #ECECEC;">
                                    <h5 class="mb-10" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">Link Pendaftaran Subdist (Umum)</h5>
                                    <div class="input-group mb-0">
                                        <input type="text" class="form-control font-sm" value="{{ $registerLink }}" readonly aria-label="Link pendaftaran outlet">
                                        <button class="btn px-25 btn-copy" style="background-color: #6A1B1B; color: #fff;" data-clipboard-text="{{ $registerLink }}">
                                            <i class="fi-rs-copy"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="p-30 border-radius-15 mb-30" style="background-color: #F8F9FA; border: 1.5px solid #ECECEC;">
                                    <h5 class="mb-5" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">Link Pendaftaran per Sales</h5>
                                    <p class="text-muted font-xs mb-15">Setiap sales punya link untuk calon <strong>outlet</strong> dan calon <strong>subdist</strong>. Klik Salin lalu bagikan ke calon pendaftar; pendaftar tercatat atas nama sales tersebut.</p>
                                    @if($salespeople->isEmpty())
                                        <p class="font-sm mb-0">Belum ada sales aktif. <a href="{{ route('buyer.sales.create') }}" style="color: #6A1B1B; font-weight: 600;">Tambah sales</a> untuk membuat link per sales.</p>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table font-sm align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Sales</th>
                                                        <th>Link Outlet</th>
                                                        <th>Link Subdist</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($salespeople as $sales)
                                                        <tr>
                                                            <td class="text-nowrap"><strong>{{ $sales->name }}</strong><br><span class="text-muted font-xs">{{ $sales->sales_code }}</span></td>
                                                            @foreach(['outlet', 'subdist'] as $type)
                                                                @php($link = $user->registrationLink($type, $sales))
                                                                <td class="text-nowrap" data-link-type="{{ $type }}">
                                                                    <button type="button" class="btn btn-xs py-1 px-2 btn-copy" style="background-color: #6A1B1B; color: #fff;" data-clipboard-text="{{ $link }}" title="{{ $link }}">
                                                                        <i class="fi-rs-copy me-1"></i>Salin
                                                                    </button>
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>

                                <h5 class="mb-15" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">
                                    Outlet dan Subdist Terdaftar ({{ number_format($outlets->total(), 0, ',', '.') }})
                                </h5>
                                @if($outlets->isEmpty())
                                    <p class="text-muted font-sm mb-0">Belum ada outlet atau subdist yang mendaftar lewat link Anda.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table font-sm">
                                            <thead>
                                                <tr>
                                                    <th>Nama</th>
                                                    <th>Jenis</th>
                                                    <th>Sales</th>
                                                    <th>No. HP</th>
                                                    <th>Tanggal Daftar</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($outlets as $outlet)
                                                    <tr>
                                                        <td>{{ $outlet->name }}</td>
                                                        <td>{{ $outlet->isSubdist() ? 'Subdist' : 'Outlet' }}</td>
                                                        <td>{{ ($outlet->recruitedBySales ?? ($outlet->isOutlet() ? $outlet->salesPerson : null))?->name ?? '-' }}</td>
                                                        <td>{{ $outlet->phone ?: '-' }}</td>
                                                        <td>{{ $outlet->created_at->format('d M Y') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    {{ $outlets->links() }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.11/clipboard.min.js"></script>
<script>
    $(document).ready(function() {
        var clipboard = new ClipboardJS('.btn-copy');

        clipboard.on('success', function(e) {
            var originalHtml = $(e.trigger).html();
            $(e.trigger).html('<i class="fi-rs-check me-1"></i>Tersalin!');

            setTimeout(function() {
                $(e.trigger).html(originalHtml);
            }, 2000);

            e.clearSelection();
        });
    });
</script>
@endpush
