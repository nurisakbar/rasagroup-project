@extends('layouts.admin')

@section('title', 'Tambah Outlet')
@section('page-title', 'Tambah Outlet')

@section('breadcrumb')
    <li><a href="{{ route('admin.outlets.index') }}">Data Outlet</a></li>
    <li class="active">Tambah</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Form Tambah Outlet</h3>
            </div>
            <form action="{{ route('admin.outlets.store') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="form-group @error('qad_customer_code') has-error @enderror">
                        <label for="qad_customer_code">Kode Customer QAD (opsional)</label>
                        <input type="text" class="form-control" id="qad_customer_code" name="qad_customer_code" value="{{ old('qad_customer_code') }}" placeholder="Masukkan kode QAD">
                        @error('qad_customer_code') <span class="help-block">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group @error('name') has-error @enderror">
                        <label for="name">Nama Outlet</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required placeholder="Masukkan nama outlet">
                        @error('name') <span class="help-block">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group @error('email') has-error @enderror">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required placeholder="email@example.com">
                        @error('email') <span class="help-block">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group @error('phone') has-error @enderror">
                        <label for="phone">No. Telepon</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx">
                        @error('phone') <span class="help-block">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group @error('password') has-error @enderror">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Minimal 8 karakter">
                        @error('password') <span class="help-block">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="Ulangi password">
                    </div>

                    <div class="form-group @error('sales_code') has-error @enderror">
                        <label for="sales_code">Nama Sales (opsional)</label>
                        <select class="form-control select2" style="width: 100%;" id="sales_code" name="sales_code">
                            <option value="">-- Pilih Sales --</option>
                            @foreach($salesList as $sales)
                                <option value="{{ $sales->sales_code }}" {{ old('sales_code') == $sales->sales_code ? 'selected' : '' }}>
                                    {{ $sales->name }} ({{ $sales->sales_code }})
                                </option>
                            @endforeach
                        </select>
                        @error('sales_code') <span class="help-block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="box-footer">
                    <a href="{{ route('admin.outlets.index') }}" class="btn btn-default">Kembali</a>
                    <button type="submit" class="btn btn-primary pull-right">Simpan User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Pilih Sales",
            allowClear: true
        });
    });
</script>
@endpush
