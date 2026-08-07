@extends('layouts.admin')

@section('title', 'Edit Sales')
@section('page-title', 'Edit Sales')
@section('page-description', 'Ubah data Sales')

@section('breadcrumb')
    <li><a href="{{ route('admin.sales.index') }}">Sales</a></li>
    <li class="active">Edit Sales</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">Form Edit Sales</h3>
                </div>
                <!-- /.box-header -->
                <!-- form start -->
                <form role="form" action="{{ route('admin.sales.update', $sale) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="box-body">
                        <div class="form-group @error('name') has-error @enderror">
                            <label for="name">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $sale->name) }}" required>
                            @error('name')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group @error('sales_code') has-error @enderror">
                            <label for="sales_code">Code Sales</label>
                            <input type="text" class="form-control" id="sales_code" name="sales_code" value="{{ old('sales_code', $sale->sales_code) }}">
                            @error('sales_code')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('email') has-error @enderror">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $sale->email) }}" required>
                            @error('email')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group @error('monthly_target') has-error @enderror">
                            <label for="monthly_target">Target Bulanan</label>
                            <div class="input-group">
                                <span class="input-group-addon">Rp</span>
                                <input type="text" class="form-control rupiah" id="monthly_target" name="monthly_target" value="{{ old('monthly_target', $sale->monthly_target ? number_format($sale->monthly_target, 0, ',', '.') : '') }}">
                            </div>
                            @error('monthly_target')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('password') has-error @enderror">
                            <label for="password">Password (Opsional)</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah password">
                            @error('password')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Isi hanya jika Anda mengisi kolom Password">
                        </div>

                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $sale->is_active) ? 'checked' : '' }}>
                                    Status Aktif
                                </label>
                            </div>
                        </div>
                    </div>
                    <!-- /.box-body -->

                    <div class="box-footer">
                        <a href="{{ route('admin.sales.index') }}" class="btn btn-default">Batal</a>
                        <button type="submit" class="btn btn-warning pull-right">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
            <!-- /.box -->
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.rupiah').on('keyup', function(e) {
            $(this).val(formatRupiah($(this).val()));
        });

        function formatRupiah(angka, prefix) {
            var number_string = angka.toString().replace(/[^,\d]/g, ''),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                var separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return prefix == undefined ? rupiah : (rupiah ? prefix + rupiah : '');
        }
    });
</script>
@endpush
