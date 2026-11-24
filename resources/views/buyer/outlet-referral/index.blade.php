@extends('layouts.shop')

@section('title', 'Rekrut Outlet')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> Akun Saya
            <span></span> Rekrut Outlet
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
                                <h3 class="mb-0" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">Rekrut Outlet</h3>
                                <p class="text-muted font-sm">Bagikan link di bawah ini. Outlet yang mendaftar lewat link ini tercatat atas nama distributor Anda.</p>
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
                                    <h5 class="mb-10" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">Link Pendaftaran Outlet</h5>
                                    <div class="input-group mb-15">
                                        <input type="text" class="form-control font-sm" value="{{ $registerLink }}" readonly aria-label="Link pendaftaran outlet">
                                        <button class="btn px-25 btn-copy" style="background-color: #6A1B1B; color: #fff;" data-clipboard-text="{{ $registerLink }}">
                                            <i class="fi-rs-copy"></i>
                                        </button>
                                    </div>
                                    <a class="btn btn-sm" style="background-color: #25D366; color: #fff;" target="_blank" rel="noopener noreferrer"
                                       href="https://wa.me/?text={{ rawurlencode('Daftar sebagai outlet ' . $user->name . ' melalui link berikut: ' . $registerLink) }}">
                                        Bagikan ke WhatsApp
                                    </a>
                                </div>

                                <h5 class="mb-15" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">
                                    Outlet Terdaftar ({{ number_format($outlets->total(), 0, ',', '.') }})
                                </h5>
                                @if($outlets->isEmpty())
                                    <p class="text-muted font-sm mb-0">Belum ada outlet yang mendaftar lewat link Anda.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table font-sm">
                                            <thead>
                                                <tr>
                                                    <th>Nama Outlet</th>
                                                    <th>No. HP</th>
                                                    <th>Tanggal Daftar</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($outlets as $outlet)
                                                    <tr>
                                                        <td>{{ $outlet->name }}</td>
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
