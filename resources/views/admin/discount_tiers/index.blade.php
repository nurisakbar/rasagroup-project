@extends('layouts.admin')

@section('title', 'Pengaturan Potongan Harga')
@section('page-title', 'Pengaturan Potongan Harga')

@section('breadcrumb')
    <li class="active">Potongan Harga</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Daftar Potongan Harga Berdasarkan Total Belanja</h3>
                <div class="box-tools">
                    <a href="{{ route('admin.discount-tiers.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Tambah Potongan Harga
                    </a>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Min. Pembelian (Subtotal)</th>
                            <th>Potongan (%)</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($discountTiers as $index => $tier)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>Rp {{ number_format($tier->min_purchase, 0, ',', '.') }}</td>
                            <td>{{ $tier->discount_percent }} %</td>
                            <td>
                                @if($tier->is_active)
                                    <span class="label label-success">Aktif</span>
                                @else
                                    <span class="label label-danger">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.discount-tiers.edit', $tier) }}" class="btn btn-warning btn-xs">
                                    <i class="fa fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('admin.discount-tiers.destroy', $tier) }}" method="POST" class="delete-form" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs">
                                        <i class="fa fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada data potongan harga.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
