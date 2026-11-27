@extends('layouts.shop')

@section('title', 'Data Sales')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> Data Sales
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
                <div class="card border-0 shadow-sm border-radius-20 overflow-hidden mb-40" style="background-color: #ffffff;">
                    <div class="card-header bg-white border-bottom-0 p-30 pb-0">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h3 class="mb-0" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">Data Sales</h3>
                                <p class="text-muted font-sm mb-0">Sales milik distributor <strong>{{ $distributor->name }}</strong>{{ $distributor->distributor_sales_code ? ' (' . $distributor->distributor_sales_code . ')' : '' }}.</p>
                            </div>
                            <a href="{{ route('buyer.sales.create') }}" class="btn btn-sm px-20" style="background-color: #6A1B1B; color: #fff;">
                                <i class="fi-rs-plus mr-5"></i>Tambah Sales
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-30">
                        <form method="GET" action="{{ route('buyer.sales.index') }}" class="row g-2 mb-20">
                            <div class="col-md-10">
                                <input type="text" name="q" value="{{ $search }}" class="form-control font-sm" placeholder="Cari kode, nama, no. HP, atau email">
                            </div>
                            <div class="col-md-2 d-grid">
                                <button type="submit" class="btn btn-sm" style="background-color: #253D4E; color: #fff;"><i class="fi-rs-search mr-5"></i>Cari</button>
                            </div>
                        </form>

                        @if($salespeople->isEmpty())
                            <p class="text-muted font-sm mb-0">
                                {{ $search !== '' ? 'Tidak ada sales yang cocok dengan pencarian.' : 'Belum ada data sales. Klik "Tambah Sales" untuk menambahkan.' }}
                            </p>
                        @else
                            <div class="table-responsive">
                                <table class="table font-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">No</th>
                                            <th>Kode Sales</th>
                                            <th>Nama</th>
                                            <th>No. HP</th>
                                            <th>Email</th>
                                            <th>Ditambahkan</th>
                                            <th class="text-end">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($salespeople as $sales)
                                            <tr>
                                                <td>{{ $salespeople->firstItem() + $loop->index }}</td>
                                                <td><strong style="color: #6A1B1B;">{{ $sales->sales_code ?: '-' }}</strong></td>
                                                <td><strong>{{ $sales->name }}</strong></td>
                                                <td>{{ $sales->phone }}</td>
                                                <td>{{ $sales->hasInternalEmail() ? '-' : $sales->email }}</td>
                                                <td>{{ $sales->created_at->timezone(\App\Support\Wib::TZ)->format('d M Y') }}</td>
                                                <td class="text-end text-nowrap">
                                                    <a href="{{ route('buyer.sales.edit', $sales) }}" class="btn btn-xs btn-outline-secondary py-1 px-2" title="Ubah"><i class="fi-rs-edit"></i></a>
                                                    <form action="{{ route('buyer.sales.destroy', $sales) }}" method="POST" class="d-inline js-delete-sales" data-name="{{ $sales->name }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-outline-danger py-1 px-2" title="Hapus"><i class="fi-rs-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{ $salespeople->links() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).on('submit', '.js-delete-sales', function (e) {
        if (!confirm('Hapus sales ' + $(this).data('name') + '?')) {
            e.preventDefault();
        }
    });
</script>
@endpush
